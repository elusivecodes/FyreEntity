<?php

namespace Fyre\Entity;

use ArrayAccess;
use Fyre\DateTime\DateTime;
use JsonSerializable;

use function array_combine;
use function array_diff;
use function array_diff_key;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_shift;
use function array_unique;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_object;
use function json_encode;
use function lcfirst;
use function method_exists;
use function str_replace;
use function strpos;
use function ucwords;

use const JSON_PRETTY_PRINT;

/**
 * Entity
 */
class Entity implements ArrayAccess, JsonSerializable
{
    protected array $accessible = [
        '*' => true,
    ];

    protected array $dirty = [];

    protected array $errors = [];

    protected array $fields = [];

    protected array $hidden = [];

    protected array $invalid = [];

    protected bool $new = false;

    protected array $original = [];

    protected array $originalFields = [];

    protected array $savedState = [];

    protected string|null $source = null;

    protected array $virtual = [];

    /**
     * New Entity constructor.
     *
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
     *
     * @param string $field The field name.
     * @return bool TRUE if the value is set, otherwise FALSE.
     */
    public function __isset(string $field): bool
    {
        return $this->has($field);
    }

    /**
     * Set an entity value.
     *
     * @param string $field The field name.
     * @param mixed $value The value.
     */
    public function __set(string $field, mixed $value): void
    {
        $this->set($field, $value);
    }

    /**
     * Convert the entity to a JSON encoded string.
     *
     * @return string The JSON encoded string.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Unset an entity value.
     *
     * @param string $field The field name.
     */
    public function __unset(string $field): void
    {
        $this->unset($field);
    }

    /**
     * Get an entity value.
     *
     * @param string $field The field name.
     * @return mixed The value.
     */
    public function &__get(string $field): mixed
    {
        return $this->get($field);
    }

    /**
     * Get an entity value.
     *
     * @param mixed $field The field name.
     * @return mixed The value.
     */
    public function &offsetGet(mixed $field): mixed
    {
        return $this->get($field);
    }

    /**
     * Get a value from the entity.
     *
     * @param string $field The field name.
     * @return mixed The value.
     */
    public function &get(string $field): mixed
    {
        $value = null;

        if (array_key_exists($field, $this->fields)) {
            $value = &$this->fields[$field];
        }

        $method = static::mutateMethod($field, 'get');

        if ($method) {
            $value = $this->$method($value);
        }

        return $value;
    }

    /**
     * Clean the entity.
     *
     * @return Entity The Entity.
     */
    public function clean(): static
    {
        $this->original = [];
        $this->originalFields = array_keys($this->fields);
        $this->dirty = [];
        $this->errors = [];
        $this->invalid = [];
        $this->savedState = [];

        return $this;
    }

    /**
     * Clear values from the entity.
     *
     * @param array $fields The fields to clear.
     * @return Entity The Entity.
     */
    public function clear(array $fields): static
    {
        foreach ($fields as $field) {
            $this->unset($field);
        }

        return $this;
    }

    /**
     * Extract values from the entity.
     *
     * @param array $fields The fields to extract.
     * @return array The extracted values.
     */
    public function extract(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = $this->get($field);
        }

        return $result;
    }

    /**
     * Extract dirty values from the entity.
     *
     * @param array|null $fields The fields to extract.
     * @return array The extracted values.
     */
    public function extractDirty(array|null $fields = null): array
    {
        $fields ??= $this->getDirty();

        $result = [];
        foreach ($fields as $field) {
            if (!$this->isDirty($field)) {
                continue;
            }

            $result[$field] = $this->get($field);
        }

        return $result;
    }

    /**
     * Extract original values from the entity.
     *
     * @param array $fields The fields to extract.
     * @return array The extracted values.
     */
    public function extractOriginal(array $fields): array
    {
        $result = [];
        foreach ($fields as $field) {
            $result[$field] = $this->getOriginal($field);
        }

        return $result;
    }

    /**
     * Fill the entity with values.
     *
     * @param array $data The data to fill.
     * @param array $options The options for filling the entity.
     * @return Entity The Entity.
     */
    public function fill(array $data, array $options = []): static
    {
        $options['guard'] ??= true;

        foreach ($data as $field => $value) {
            $this->set($field, $value, $options);
        }

        return $this;
    }

    /**
     * Fill the entity with invalid values.
     *
     * @param array $data The data to fill.
     * @param bool $overwrite Whether to overwrite existing values.
     * @return Entity The Entity.
     */
    public function fillInvalid(array $data, bool $overwrite = false): static
    {
        foreach ($data as $field => $value) {
            $this->setInvalid($field, $value, $overwrite);
        }

        return $this;
    }

    /**
     * Get the accessible fields from the entity.
     *
     * @return array The accessible fields.
     */
    public function getAccessible(): array
    {
        return $this->accessible;
    }

    /**
     * Get the dirty fields from the entity.
     *
     * @return array The dirty fields.
     */
    public function getDirty(): array
    {
        return array_keys($this->dirty);
    }

    /**
     * Get the errors for an entity field.
     *
     * @param string $field The field name.
     * @return array The errors.
     */
    public function getError(string $field): array
    {
        if (array_key_exists($field, $this->errors)) {
            return $this->errors[$field];
        }

        if (strpos($field, '.') === false) {
            $value = $this->get($field);

            return static::readError($value);
        }

        return static::readNestedErrors($this, $field);
    }

    /**
     * Get all errors for the entity.
     *
     * @return array The errors.
     */
    public function getErrors(): array
    {
        $diff = array_diff_key($this->fields, $this->errors);

        $fields = array_map(
            fn(mixed $value): array => static::readError($value),
            $diff
        );

        $fields = array_filter($fields, fn(array $errors): bool => $errors !== []);

        return array_merge($this->errors, $fields);
    }

    /**
     * Get the hidden fields from the entity.
     *
     * @return array The hidden fields.
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * Get invalid value(s) from the entity.
     *
     * @param string|null $field The field name.
     * @return mixed The value.
     */
    public function getInvalid(string|null $field = null): mixed
    {
        if (!$field) {
            return $this->invalid;
        }

        return $this->invalid[$field] ?? null;
    }

    /**
     * Get an original value from the entity.
     *
     * @param string $field The field name.
     * @return mixed The value.
     */
    public function getOriginal(string|null $field = null): mixed
    {
        if (!$field) {
            return array_merge($this->fields, $this->original);
        }

        if (array_key_exists($field, $this->original)) {
            return $this->original[$field];
        }

        return $this->fields[$field] ?? null;
    }

    /**
     * Get the entity source.
     *
     * @return string|null The source.
     */
    public function getSource(): string|null
    {
        return $this->source;
    }

    /**
     * Get the virtual fields from the entity.
     *
     * @return array The virtual fields.
     */
    public function getVirtual(): array
    {
        return $this->virtual;
    }

    /**
     * Get the visible fields from the entity.
     *
     * @return array The visible fields.
     */
    public function getVisible(): array
    {
        $fields = array_keys($this->fields);
        $fields = array_merge($fields, $this->virtual);

        return array_diff($fields, $this->hidden);
    }

    /**
     * Determine if an entity value is set.
     *
     * @param string $field The field name.
     * @return bool TRUE if the value is set, otherwise FALSE.
     */
    public function has(string $field): bool
    {
        return array_key_exists($field, $this->fields);
    }

    /**
     * Determine if the entity has errors.
     *
     * @param bool $includeNested Whether to include nested entity errors.
     * @return bool TRUE if the entity has errors, otherwise FALSE.
     */
    public function hasErrors(bool $includeNested = true): bool
    {
        if ($this->errors !== []) {
            return true;
        }

        if (!$includeNested) {
            return false;
        }

        foreach ($this->fields as $value) {
            if (static::checkError($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if an entity value is not empty.
     *
     * @param string $field The field name.
     * @return bool TRUE if the value is not empty, otherwise FALSE.
     */
    public function hasValue(string $field): bool
    {
        return !$this->isEmpty($field);
    }

    /**
     * Determine if an entity field is accessible.
     *
     * @param string $field The field name.
     * @return bool TRUE if the field is accessible otherwise FALSE.
     */
    public function isAccessible(string $field): bool
    {
        return $this->accessible[$field] ?? $this->accessible['*'] ?? false;
    }

    /**
     * Determine if an entity field is dirty.
     *
     * @param string|null $field The field name.
     * @return bool TRUE if the entity field is dirty, otherwise FALSE.
     */
    public function isDirty(string|null $field = null): bool
    {
        if (!$field) {
            return $this->dirty !== [];
        }

        return $this->dirty[$field] ?? false;
    }

    /**
     * Determine if an entity value is empty.
     *
     * @param string|null $field The field name.
     * @return bool TRUE if the value is empty, otherwise FALSE.
     */
    public function isEmpty(string|null $field = null): bool
    {
        if ($field !== null) {
            return !array_key_exists($field, $this->fields) || in_array($this->fields[$field], [null, '', []]);
        }

        $fields = array_keys($this->fields);

        foreach ($fields as $field) {
            if (!$this->isEmpty($field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the entity is new.
     *
     * @return bool TRUE if the entity is new, otherwise FALSE.
     */
    public function isNew(): bool
    {
        return $this->new;
    }

    /**
     * Convert the entity to an array for JSON serializing.
     *
     * @return array The array for serializing.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray(true);
    }

    /**
     * Determine if an entity value is set.
     *
     * @param mixed $field The field name.
     * @return bool TRUE if the value is set, otherwise FALSE.
     */
    public function offsetExists(mixed $field): bool
    {
        return $this->has($field);
    }

    /**
     * Set an entity value.
     *
     * @param mixed $field The field name.
     * @param mixed $value The value.
     */
    public function offsetSet(mixed $field, mixed $value): void
    {
        $this->set($field, $value);
    }

    /**
     * Unset an entity value.
     *
     * @param mixed $field The field name.
     */
    public function offsetUnset(mixed $field): void
    {
        $this->unset($field);
    }

    /**
     * Restore the saved entity state.
     *
     * @param bool $restoreErrors Whether to restore the errors.
     * @return static The Entity.
     */
    public function restoreState(bool $restoreErrors = true): static
    {
        if (count($this->savedState) === 6) {
            [$this->fields, $this->original, $this->dirty, $errors, $this->invalid, $this->new] = $this->savedState;

            if ($restoreErrors) {
                $this->errors = $errors;
            }
        }

        return $this;
    }

    /**
     * Save the current entity state.
     *
     * @return static The Entity.
     */
    public function saveState(): static
    {
        $this->savedState = [$this->fields, $this->original, $this->dirty, $this->errors, $this->invalid, $this->new];

        return $this;
    }

    /**
     * Set an entity value.
     *
     * @param string $field The field name.
     * @param mixed $value The value.
     * @param array $options The options for setting the value.
     * @return Entity The Entity.
     */
    public function set(string $field, mixed $value, array $options = []): static
    {
        $options['guard'] ??= false;
        $options['mutate'] ??= true;

        if ($options['guard'] && !$this->isAccessible($field)) {
            return $this;
        }

        if ($options['mutate']) {
            $method = static::mutateMethod($field, 'set');

            if ($method) {
                $value = $this->$method($value);
            }
        }

        $hasField = array_key_exists($field, $this->fields);

        if ($hasField && $value === $this->fields[$field]) {
            return $this;
        }

        $this->setDirty($field, true);

        if ($hasField && !array_key_exists($field, $this->original) && in_array($field, $this->originalFields)) {
            $this->original[$field] = $this->fields[$field];
        }

        $this->fields[$field] = $value;

        return $this;
    }

    /**
     * Set whether a field is accessible.
     *
     * @param string $field The field name.
     * @param bool $accessible Whether the field is accessible.
     * @return Entity The Entity.
     */
    public function setAccess(string $field, bool $accessible): static
    {
        if ($field === '*') {
            $this->accessible = [];
        }

        $this->accessible['*'] ??= true;

        if ($accessible !== $this->accessible['*']) {
            $this->accessible[$field] = $accessible;
        }

        return $this;
    }

    /**
     * Set whether a field is dirty.
     *
     * @param string $field The field name.
     * @param bool $dirty Whether the field is dirty.
     * @return Entity The Entity.
     */
    public function setDirty(string $field, bool $dirty = true): static
    {
        if ($dirty === false) {
            unset($this->dirty[$field]);
        } else {
            $this->dirty[$field] = true;

            unset($this->errors[$field]);
            unset($this->invalid[$field]);
        }

        return $this;
    }

    /**
     * Set errors for an entity field.
     *
     * @param string $field The field name.
     * @param array|string $error The error(s).
     * @param bool $overwrite Whether to overwrite existing errors.
     * @return Entity The Entity.
     */
    public function setError(string $field, array|string $error, bool $overwrite = false): static
    {
        return $this->setErrors([$field => $error], $overwrite);
    }

    /**
     * Set all errors for the entity.
     *
     * @param array $errors The errors.
     * @param bool $overwrite Whether to overwrite existing errors.
     */
    public function setErrors(array $errors, bool $overwrite = false): static
    {
        foreach ($errors as $field => $error) {
            $error = (array) $error;

            if ($overwrite) {
                $this->errors[$field] = $error;
            } else {
                $this->errors[$field] ??= [];
                $this->errors[$field] = array_merge($this->errors[$field], $error);
            }
        }

        return $this;
    }

    /**
     * Set hidden fields.
     *
     * @param array $fields The fields.
     * @param bool $merge Whether to merge with existing fields.
     * @return Entity The Entity.
     */
    public function setHidden(array $fields, bool $merge = false): static
    {
        if ($merge) {
            $fields = array_merge($this->hidden, $fields);
        }

        $this->hidden = array_unique($fields);

        return $this;
    }

    /**
     * Set an invalid value.
     *
     * @param string $field The field name.
     * @param mixed $value The value.
     * @param bool $overwrite Whether to overwrite an existing value.
     * @return Entity The Entity.
     */
    public function setInvalid(string $field, mixed $value, bool $overwrite = true): static
    {
        if ($overwrite) {
            $this->invalid[$field] = $value;
        } else {
            $this->invalid[$field] ??= $value;
        }

        return $this;
    }

    /**
     * Set whether the entity is new.
     *
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
     *
     * @param string $source The source.
     * @return Entity The Entity.
     */
    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Set virtual fields.
     *
     * @param array $fields The fields.
     * @param bool $merge Whether to merge with existing fields.
     * @return Entity The Entity.
     */
    public function setVirtual(array $fields, bool $merge = false): static
    {
        if ($merge) {
            $fields = array_merge($this->virtual, $fields);
        }

        $this->virtual = array_unique($fields);

        return $this;
    }

    /**
     * Convert the entity to an array.
     *
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
     *
     * @return string The JSON encoded string.
     */
    public function toJson(): string
    {
        return json_encode($this, JSON_PRETTY_PRINT) ?: '';
    }

    /**
     * Unset an entity value.
     *
     * @param string $field The field name.
     * @return Entity The Entity.
     */
    public function unset(string $field): static
    {
        unset($this->fields[$field]);
        unset($this->original[$field]);
        unset($this->dirty[$field]);

        return $this;
    }

    /**
     * Check a value for errors.
     *
     * @param mixed $value The value.
     * @return bool TRUE if the value has errors, otherwise FALSE.
     */
    protected static function checkError(mixed $value): bool
    {
        if ($value instanceof Entity) {
            return $value->hasErrors();
        }

        if (is_array($value)) {
            foreach ($value as $val) {
                if (static::checkError($val)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the mutation method for a field.
     *
     * @param string $field The field name.
     * @param string $prefix The method prefix.
     * @return string|null The mutation method.
     */
    protected static function mutateMethod(string $field, string $prefix): string|null
    {
        if (static::class === Entity::class) {
            return null;
        }

        $method = ucwords($prefix.'_'.$field, '_');
        $method = str_replace('_', '', $method);
        $method = '_'.lcfirst($method);

        if (!method_exists(static::class, $method)) {
            return null;
        }

        return $method;
    }

    /**
     * Read errors from a value.
     *
     * @param mixed $value The value.
     * @param string|null $field The field name.
     * @return array The errors.
     */
    protected static function readError(mixed $value, string|null $field = null): array
    {
        if ($value instanceof Entity) {
            return $field ?
                $value->getError($field) :
                $value->getErrors();
        }

        if (is_array($value)) {
            $fields = array_map(
                function(mixed $val) use ($field): array {
                    if ($val instanceof Entity) {
                        return $field ?
                            $val->getError($field) :
                            $val->getErrors();
                    }

                    return [];
                },
                $value
            );

            return array_filter($fields, fn(array $errors): bool => $errors !== []);
        }

        return [];
    }

    /**
     * Read deeply nested errors using dot notation.
     *
     * @param mixed $value The value.
     * @param string $field The field name.
     * @return array The errors.
     */
    protected static function readNestedErrors(mixed $value, string $field): array
    {
        $path = explode('.', $field);

        while (count($path) > 1) {
            $segment = array_shift($path);

            if ($value instanceof Entity) {
                $value = $value->get($segment);
            } else {
                $value = $value[$segment] ?? null;
            }

            if (!$value) {
                return [];
            }
        }

        $field = array_shift($path);

        return static::readError($value, $field);
    }
}
