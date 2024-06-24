<?php

namespace Fyre\Entity;

use ArrayAccess;
use Fyre\DateTime\DateTime;
use Fyre\Entity\Traits\ErrorTrait;
use Fyre\Entity\Traits\FieldTrait;
use JsonSerializable;

use function array_combine;
use function array_map;
use function is_array;
use function is_object;
use function json_encode;
use function method_exists;

use const JSON_PRETTY_PRINT;

/**
 * Entity
 */
class Entity implements ArrayAccess, JsonSerializable
{
    use ErrorTrait;
    use FieldTrait;

    protected bool $new = false;
    protected string|null $source = null;

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
     * Determine if an entity value is set.
     * @param string $field The field name.
     * @return bool TRUE if the value is set, otherwise FALSE.
     */
    public function __isset(string $field): bool
    {
        return $this->has($field);
    }

    /**
     * Set an entity value.
     * @param string $field The field name.
     * @param mixed $value The value.
     */
    public function __set(string $field, mixed $value): void
    {
        $this->set($field, $value);
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
     * Get an entity value.
     * @param string $field The field name.
     * @return mixed The value.
     */
    public function &__get(string $field): mixed
    {
        return $this->get($field);
    }

    /**
     * Get an entity value.
     * @param mixed $field The field name.
     * @return mixed The value.
     */
    public function &offsetGet(mixed $field): mixed
    {
        return $this->get($field);
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
     * Convert the entity to an array for JSON serializing.
     * @return array The array for serializing.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray(true);
    }

    /**
     * Determine if an entity value is set.
     * @param mixed $field The field name.
     * @return bool TRUE if the value is set, otherwise FALSE.
     */
    public function offsetExists(mixed $field): bool
    {
        return $this->has($field);
    }

    /**
     * Set an entity value.
     * @param mixed $field The field name.
     * @param mixed $value The value.
     */
    public function offsetSet(mixed $field, mixed $value): void
    {
        $this->set($field, $value);
    }

    /**
     * Unset an entity value.
     * @param mixed $field The field name.
     */
    public function offsetUnset(mixed $field): void
    {
        $this->unset($field);
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
            function(string $field) use ($convertObjects): mixed {
                $value = $this->get($field);

                if ($value instanceof Entity) {
                    return $value->toArray($convertObjects);
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
                        function(mixed $val) use ($convertObjects): mixed {
                            if ($val instanceof Entity) {
                                return $val->toArray($convertObjects);
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
        return json_encode($this, JSON_PRETTY_PRINT) ?: '';
    }
}
