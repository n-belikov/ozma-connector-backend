<?php

namespace App\Domain\Ozma;

use Illuminate\Support\Collection;

/**
 * Class OzmaEntity
 * @package App\Domain\Ozma
 */
abstract class OzmaEntity
{
    public function __construct(?\stdClass $class = null)
    {
        if ($class) {
            $vars = array_keys(get_class_vars(static::class));
            foreach ((array)$class as $var => $value) {
                if (in_array($var, $vars, true)) {
                    $this->{$var} = $value;
                }
            }
        }
    }

    /**
     * @param \stdClass $object
     * @return static[]
     */
    public static function collection(\stdClass $object): Collection
    {
        $items = new Collection();
        $columns = $object->info->columns;
        foreach ($object->result->rows as $row) {
            $values = $row->values;
            $result = new \stdClass();
            foreach ($columns as $index => $column) {
                if (preg_match("/_table$/", $column->name)) {
                    continue;
                }
                $result->{$column->name} = $values[$index]->value;
            }
            $result->entityId = $row->entityIds->{"1"}->id;
            $items->push(new static($result));
        }

        return $items;
    }

    public static function make(\stdClass $object): self
    {
        $columns = $object->info->columns;
        $values = $object->result->rows[0]->values;

        $result = new \stdClass();
        foreach ($columns as $index => $column) {
            if (preg_match("/_table$/", $column->name)) {
                continue;
            }
            $result->{$column->name} = $values[$index]->value;
        }

        return new static($result);
    }
}