<?php

namespace StreamTest;

use Psr\Http\Message\StreamInterface;
use \Generator;

class StreamScanner {
    private const int CHUNK_SIZE = 8192;

    /**
    * @return Generator<object>
    *
    * Read each line from the stream and return as object
    * Each line will be a JSON object
    */
    public function __invoke(StreamInterface $stream): Generator
    {
        foreach($this->nextLine($stream) as $line) {
            yield json_decode($line);
        }
    }

    /**
     * @return Generator<string>
     *
     * Read each line from the stream
     * */
    private function nextLine(StreamInterface $stream): Generator
    {
        $buffer = '';

        // read chunks until EOF
        while (!$stream->eof()) {
            $buffer .= $stream->read(self::CHUNK_SIZE);

            // if buffer has new line, yield a JSON line
            while (
                ($pos = strpos($buffer, "\n")) !== false
            ) {
                // get the complete JSON
                $line = substr($buffer, 0, $pos + 1);
                $buffer = substr($buffer, $pos + 1, strlen($buffer));
                yield $line;
            }
        }

        // process the remaining buffer
        if ($buffer !== '') {
            yield $buffer;
        }
    }
}

