<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace App\Supla;

class SuplaServerReal extends SuplaServer {
    // Far above any CALCFG acceptance line, but finite to protect the control process from a missing delimiter.
    private const CALCFG_RESPONSE_MAX_BYTES = 1048576;
    private const CALCFG_RESPONSE_TIMEOUT_SECONDS = 5;
    private $socket = null;

    public function __destruct() {
        $this->disconnect();
    }

    protected function connect() {
        if ($this->socket) {
            return $this->socket;
        }
        $this->socket = @stream_socket_client('unix://' . $this->socketPath, $errno, $errstr);
        if (!$this->socket) {
            return false;
        }
        $hello = fread($this->socket, 4096);
        if (preg_match("/^SUPLA SERVER CTRL\n/", $hello) !== 1) {
            $this->disconnect();
        }
        return $this->socket;
    }

    protected function disconnect() {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    protected function command($command) {
        if ($this->socket) {
            $deadline = $this->calcfgActionInProgress ? microtime(true) + self::CALCFG_RESPONSE_TIMEOUT_SECONDS : null;
            if (!fwrite($this->socket, $command . "\n")) {
                $this->disconnect();
                throw new SuplaSocketWriteException();
            }
            if ($deadline !== null) {
                return $this->readCalcfgResponse($deadline);
            }
            $result = '';
            while (!feof($this->socket)) {
                $chunk = fgets($this->socket, 4096);
                if ($chunk === false || $chunk === '') {
                    $metadata = stream_get_meta_data($this->socket);
                    if (!empty($metadata['timed_out'])) {
                        $this->disconnect();
                    }
                    return false;
                }
                $result .= $chunk;
                if (str_contains($result, "\n")) {
                    return $result;
                }
            }
            return false;
        }
        return false;
    }

    private function readCalcfgResponse(float $deadline): string|false {
        $metadata = stream_get_meta_data($this->socket);
        $originalTimeout = $metadata['timeout'] ?? (float) ini_get('default_socket_timeout');
        $wasBlocking = $metadata['blocked'] ?? true;
        $result = '';
        stream_set_blocking($this->socket, false);
        try {
            while (strlen($result) <= self::CALCFG_RESPONSE_MAX_BYTES) {
                $remaining = $deadline - microtime(true);
                if ($remaining <= 0) {
                    break;
                }
                $read = [$this->socket];
                $write = null;
                $except = null;
                $seconds = (int) $remaining;
                $microseconds = (int) (($remaining - $seconds) * 1000000);
                $ready = @stream_select($read, $write, $except, $seconds, $microseconds);
                if ($ready === false) {
                    break;
                }
                if ($ready === 0) {
                    break;
                }
                // One byte at a time keeps the next command's response in the socket,
                // rather than accidentally consuming it along with this line.
                $byte = fread($this->socket, 1);
                if ($byte === false || $byte === '') {
                    break;
                }
                $result .= $byte;
                if ($byte === "\n") {
                    return $result;
                }
            }
            return false;
        } finally {
            if (is_resource($this->socket)) {
                stream_set_blocking($this->socket, $wasBlocking);
                $seconds = (int) $originalTimeout;
                $microseconds = (int) (($originalTimeout - $seconds) * 1000000);
                stream_set_timeout($this->socket, $seconds, $microseconds);
                if ($result === '' || !str_ends_with($result, "\n") || strlen($result) > self::CALCFG_RESPONSE_MAX_BYTES) {
                    $this->disconnect();
                }
            }
        }
    }

    protected function ensureCanConnect(): void {
        $server = $this->localSuplaCloud->getHost(false) ?: 'localhost';
        $context = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $socket = @stream_socket_client("tls://{$server}:2016", $errorCode, $errorMessage, 3, STREAM_CLIENT_CONNECT, $context);
        if ($socket) {
            fclose($socket);
        } else {
            throw new SuplaServerIsDownException("CANNOT_CONNECT_2016: $errorMessage ($errorCode)");
        }
        $socket = @stream_socket_client($server . ":2015", $errorCode, $errorMessage, 3);
        if ($socket) {
            fclose($socket);
        } else {
            throw new SuplaServerIsDownException("CANNOT_CONNECT_2015: $errorMessage ($errorCode)");
        }
        if ($this->connect() === false) {
            throw new SuplaServerIsDownException("CANNOT_CONNECT");
        }
    }
}
