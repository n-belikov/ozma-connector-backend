<?php

namespace App\Domain\Enum;

use Illuminate\Support\Collection;

/**
 * Class BaseEnum
 * @package App\Domain\Enum
 */
abstract class BaseEnum
{
    private static Collection $values;

    private $value;

    public function __construct($value)
    {
        $values = static::allValues();
        if (!$values->contains($value)) {
            throw new \InvalidArgumentException("Available: " . $values->implode(","));
        }
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @return Collection
     */
    final public static function allValues(): Collection
    {
        if (self::$values[static::class] ?? false) {
            return self::$values[static::class];
        }
        try {
            $reflectionClass = new \ReflectionClass(static::class);
        } catch (\Exception $e) {
            return collect();
        }
        if (!(self::$values ?? false)) {
            self::$values = collect();
        }
        return self::$values[static::class] = collect($reflectionClass->getConstants())->values();
    }

    /**
     * @return Collection<static>
     */
    final public static function all(): Collection
    {
        return self::allValues()->map(
            fn($key) => new static($key)
        );
    }
}