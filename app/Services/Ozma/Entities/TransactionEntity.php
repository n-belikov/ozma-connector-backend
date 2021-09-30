<?php

namespace App\Services\Ozma\Entities;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class TransactionEntity
 * @package App\Services\Ozma\Entities
 */
class TransactionEntity implements Arrayable
{
    const UPDATE = "update";

    const INSERT = "insert";

    const DELETE = "delete";

    private string $type;

    /** @var array */
    private ?array $entries;

    /**
     * @var array = [
     *     "schema" => "schema",
     *     "name" => "name"
     * ]
     */
    private array $entry;

    private ?int $id;

    public function __construct(string $type, string $schema, string $name)
    {
        if (!in_array($type, [self::UPDATE, self::INSERT, self::DELETE], true)) {
            throw new \InvalidArgumentException("Type mismatched");
        }

        $this->type = $type;
        $this->entry = [
            "schema" => $schema,
            "name" => $name
        ];
    }

    /**
     * @param int|null $id
     * @return TransactionEntity
     */
    public function setId(?int $id): TransactionEntity
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            "type" => $this->type,
            "entity" => $this->entry,
        ];
        if ($this->id ?? false) {
            $array["id"] = $this->id;
        }
        if ($this->entries ?? false) {
            $array["entries"] = $this->entries;
        }

        return $array;
    }

    /**
     * @param array $entries
     * @return TransactionEntity
     */
    public function setEntries(array $entries): TransactionEntity
    {
        $this->entries = $entries;
        return $this;
    }
}