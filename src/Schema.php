<?php

declare(strict_types=1);

namespace Compwright\DomoUploaderPhp;

use Countable;
use InvalidArgumentException;
use JsonSerializable;

class Schema implements Countable, JsonSerializable
{
    public const TYPES = [
        'STRING',
        'DECIMAL',
        'LONG',
        'DOUBLE',
        'DATE',
        'DATETIME'
    ];

    /**
     * @param array<int, array{name:string, type:string}> $columns
     */
    public function __construct(private $columns = [])
    {
    }

    public function count(): int
    {
        return count($this->columns);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addColumn(string $name, string $type): self
    {
        $type = strtoupper($type);
        if (!in_array($type, self::TYPES)) {
            throw new InvalidArgumentException('Unrecognized type: ' . $type);
        }
        $this->columns[] = compact('name', 'type');
        return $this;
    }

    /**
     * @return array<int, array{name:string,type:string}>
     */
    public function jsonSerialize(): array
    {
        return $this->columns;
    }

    /**
     * @param array<string, string> $columnsToTypes
     */
    public static function fromMap(array $columnsToTypes): self
    {
        $schema = new self();
        array_map(
            [$schema, 'addColumn'],
            array_keys($columnsToTypes),
            array_values($columnsToTypes)
        );
        return $schema;
    }

    /**
     * @return array<string, string>
     */
    public static function toMap(self $schema): array
    {
        return array_reduce(
            $schema->jsonSerialize(),
            function (array $map, array $column): array {
                $map[$column['name']] = $column['type'];
                return $map;
            },
            []
        );
    }
}
