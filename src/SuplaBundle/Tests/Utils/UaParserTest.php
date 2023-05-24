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

namespace SuplaBundle\Tests\Utils;

use PHPUnit\Framework\TestCase;
use UAParser\Parser;

class UaParserTest extends TestCase {
    public function testParsing() {
        $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/109.0.0.0 Safari/537.36';
        $parsed = Parser::create()->parse($userAgent);
        $this->assertEquals('Chrome', $parsed->ua->family);
        $this->assertEquals('109', $parsed->ua->major);
        $this->assertEquals('Linux', $parsed->os->family);
    }

    public function testParsingMobile() {
        // @codingStandardsIgnoreLine
        $userAgent = 'Mozilla/5.0 (Linux; Android 7.0; SM-G930VC Build/NRD90M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/58.0.3029.83 Mobile Safari/537.36';
        $parsed = Parser::create()->parse($userAgent);
        $this->assertEquals('Chrome Mobile WebView', $parsed->ua->family);
        $this->assertEquals('58', $parsed->ua->major);
        $this->assertEquals('Android', $parsed->os->family);
        $this->assertEquals('Samsung SM-G930VC', $parsed->device->family);
    }
}
