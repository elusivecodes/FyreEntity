<?php

namespace Fyre\Entity\Traits;

use Fyre\Entity\Entity;

use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_merge;
use function array_unique;
use function in_array;
use function lcfirst;
use function method_exists;
use function str_replace;
use function ucwords;

/**
 * FieldTrait
 */
trait FieldTrait
{
    protected array $accessible = [
        '*' => true,
    ];

    protected array $dirty = [];

    protected array $fields = [];

    protected array $hidden = [];

    protected array $original = [];

    protected array $virtual = [];

    /**
     * Get a value from the entity.
     *
     * @param string $field The field name.
     * @return mixed The value.
     */
    public function &get(string $field): mixed
    {
        $value = &$this->fields[$field] ?? null;

        $method = static::mutateMethod($field, 'get');

        if ($method) {
            $value = $this->$method($value);
        }

        return $value;
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
     * Get the hidden fields from the entity.
     *
     * @return array The hidden fields.
     */
    public function getHidden(): array
    {
        return $this->hidden;
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
            return array_merge($this->original, $this->fields);
        }

        return $this->original[$field] ?? $this->fields[$field] ?? null;
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

        if ($hasField && !array_key_exists($field, $this->original)) {
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
}
