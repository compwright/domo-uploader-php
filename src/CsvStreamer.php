<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp;

use Generator;
use RuntimeException;

class CsvStreamer
{
    /**
     * @param iterable<int, object|array<string, mixed>> $data
     *
     * @return resource
     *
     * @throws RuntimeException
     */
    public function stream(iterable $data)
    {
        $f = fopen('php://memory', 'r+');

        if ($f === false) {
            throw new RuntimeException('Could not open memory stream');
        }

        $first = true;
        foreach ($data as $row) {
            $row = (array) $row;
            if ($first) {
                fputcsv($f, array_keys($row), ',', '"', "\\");
                $first = false;
            }
            fputcsv($f, $row, ',', '"', "\\");
        }

        rewind($f);
        return $f;
    }

    /**
     * @param iterable<int, object|array<string, mixed>> $data
     *
     * @return Generator<int, resource>
     *
     * @throws RuntimeException
     */
    public function streamChunks(iterable $data, int $chunkSize): Generator
    {
        $buffer = [];
        foreach ($data as $row) {
            $buffer[] = $row;
            if (count($buffer) === $chunkSize) {
                yield $this->stream($buffer);
                $buffer = [];
            }
        }
        if (count($buffer) > 0) {
            yield $this->stream($buffer);
        }
    }
}
