<?php

namespace Fyre\Entity;

use
    Fyre\DateTime\DateTime,
    Fyre\Entity\Traits\ErrorTrait,
    Fyre\Entity\Traits\FieldTrait;

use const
    JSON_PRETTY_PRINT;

use function
    array_combine,
    array_map,
    is_array,
    is_object,
    json_encode,
    method_exists;

/**
 * Entity
 */
class Entity
{

    protected string|null $source = null;

    protected bool $new = false;

    use
        ErrorTrait,
        FieldTrait;

    /**
     * New Entity constructor.
     * @param array $data The data for populating the entity.
     * @param array $options The options for creating the entity.
     */
    public function __construct(array $data = [], array $options = [])
    {
        $options['source'] ??= null;
        $options['new'] ??= true;
        $options['clean'] ??= true;

        if ($options['source']) {
            $this->setSource($options['source']);
        }

        if ($options['new']) {
            $this->setNew($options['new']);
        }

        if ($data !== []) {
            $this->fill($data);
        }

        if ($options['clean']) {
            $this->clean();
        }
    }

    /**
     * Get an entity value.
     * @param string $field The field name.
     * @return mixed The value.
     */
    public function &__get(string $field)
    {
        return $this->get($field);
    }

    /**
     * Set an entity value.
     * @param string $field The field name.
     * @param mixed $value The value.
     */
    public function __set(string $field, $value): void
    {
        $this->set($field, $value);
    }

    /**
     * Determine if an entity value is set.
     * @param string $field The field name.
     * @return bool TRUE if the value is set, otherwise FALSE.
     */
    public function __isset(string $field): bool
    {
        return $this->has($field);
    }

    /**
     * Convert the entity to a JSON encoded string.
     * @return string The JSON encoded string.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Unset an entity value.
     * @param string $field The field name.
     */
    public function __unset(string $field): void
    {
        $this->unset($field);
    }

    /**
     * Clean the entity.
     * @return Entity The Entity.
     */
    public function clean(): static
    {
        $this->original = [];
        $this->dirty = [];
        $this->errors = [];
        $this->invalid = [];

        return $this;
    }

    /**
     * Get the entity source.
     * @return string|null The source.
     */
    public function getSource(): string|null
    {
        return $this->source;
    }

    /**
     * Determine if the entity is new.
     * @return bool TRUE if the entity is new, otherwise FALSE.
     */
    public function isNew(): bool
    {
        return $this->new;
    }

    /**
     * Set whether the entity is new.
     * @param bool $new Whether the entity is new.
     * @return Entity The Entity.
     */
    public function setNew(bool $new = true): static
    {
        $this->new = $new;

        return $this;
    }

    /**
     * Set the entity source.
     * @param string $source The source.
     * @return Entity The Entity.
     */
    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Convert the entity to an array.
     * @param bool $convertObjects Whether to convert objects to strings where possible.
     * @return array The array.
     */
    public function toArray(bool $convertObjects = false): array
    {
        $fields = $this->getVisible();

        $values = array_map(
            function($field) use ($convertObjects) {
                $value = $this->get($field);

                if ($value instanceof Entity) {
                    return $value->toArray();
                }

                if ($convertObjects) {
                    if ($value instanceof DateTime) {
                        return $value->toISOString();
                    }

                    if (is_object($value) && method_exists($value, '__toString')) {
                        return (string) $value;
                    }
                }

                if (is_array($value)) {
                    return array_map(
                        function($val) {
                            if ($val instanceof Entity) {
                                return $val->toArray();
                            }

                            return $val;
                        },
                        $value
                    );
                }

                return $value;
            },
            $fields
        );

        return array_combine($fields, $values);
    }

    /**
     * Convert the entity to a JSON encoded string.
     * @return string The JSON encoded string.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(true), JSON_PRETTY_PRINT) ?: '';
    }

}
