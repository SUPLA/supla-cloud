<?php

namespace SuplaBundle\Utils;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Traversable;

/**
 * @see https://gist.github.com/francislavoie/3bbb0711ceddaea5651d288212daeded
 */
class TimestampConsoleOutput implements OutputInterface {
    private bool $lastWasNewline = true;

    public function __construct(
        private readonly OutputInterface $output,
        private readonly string $format = '[Y-m-d H:i:s] ',
        private readonly string $eol = PHP_EOL,
    ) {
    }

    public function write($messages, $newline = false, $options = 0): void {
        // Normalize the messages to an array
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }
        if ($messages instanceof Traversable) {
            $messages = iterator_to_array($messages);
        }

        // Add newline to the last message
        if ($newline) {
            $messages[array_key_last($messages)] = $messages[array_key_last($messages)] . $this->eol;
        }

        // Write the timestamp if the last message ended with a newline (or is the first message)
        if ($this->lastWasNewline) {
            $this->output->write(date($this->format), false, $options);
        }

        // Check if the current message ends with a newline, so we can insert the newline
        // for the next message if we know it will start on a new line
        $this->lastWasNewline = str_ends_with($messages[array_key_last($messages)], $this->eol);

        // Add the timestamps after each newline in the message,
        // _except_ for the last newline, because we want to let
        // the next write to add its own with the correct time
        // if it will start on a new line.
        foreach ($messages as $i => $message) {
            // Count the number of newlines in the message
            $newlineCount = substr_count($message, $this->eol);

            // If this is the last message in this write, skip the last newline
            if ($i === count($messages) - 1) {
                $newlineCount--;
            }

            // If there are no newlines, skip this message
            if ($newlineCount < 1) {
                continue;
            }

            // Perform the replacement, with a count limit to skip the last newline
            $messages[$i] = preg_replace(
                "/{$this->eol}/",
                $this->eol . date($this->format),
                $message,
                $newlineCount
            );
        }

        // Finally, write the messages
        $this->output->write($messages, false, $options);
    }

    public function writeln($messages, $options = 0): void {
        $this->write($messages, true, $options);
    }

    public function setVerbosity($level): void {
        $this->output->setVerbosity($level);
    }

    public function getVerbosity(): int {
        return $this->output->getVerbosity();
    }

    public function isQuiet(): bool {
        return $this->output->isQuiet();
    }

    public function isVerbose(): bool {
        return $this->output->isVerbose();
    }

    public function isVeryVerbose(): bool {
        return $this->output->isVeryVerbose();
    }

    public function isDebug(): bool {
        return $this->output->isDebug();
    }

    public function setDecorated($decorated): void {
        $this->output->setDecorated($decorated);
    }

    public function isDecorated(): bool {
        return $this->output->isDecorated();
    }

    public function setFormatter(OutputFormatterInterface $formatter) {
        return $this->output->setFormatter($formatter);
    }

    public function getFormatter(): OutputFormatterInterface {
        return $this->output->getFormatter();
    }
}
