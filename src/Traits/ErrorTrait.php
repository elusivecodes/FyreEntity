<?php

namespace Fyre\Entity\Traits;

use Fyre\Entity\Entity;

use function array_diff_key;
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_shift;
use function count;
use function explode;
use function is_array;
use function strpos;

/**
 * ErrorTrait
 */
trait ErrorTrait
{

    protected array $errors = [];

    protected array $invalid = [];

    /**
     * Fill the entity with invalid values.
     * @param array $data The data to fill.
     * @param bool $overwrite Whether to overwrite existing values.
     * @return Entity The Entity.
     */
    public function fillInvalid(array $data, bool $overwrite = false): static
    {
        foreach ($data AS $field => $value) {
            $this->setInvalid($field, $value, $overwrite);
        }

        return $this;
    }

    /**
     * Get the errors for an entity field.
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
     * Get invalid value(s) from the entity.
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
     * Determine if the entity has errors.
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

        foreach ($this->fields AS $value) {
            if (static::checkError($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set errors for an entity field.
     * @param string $field The field name.
     * @param string|array $error The error(s).
     * @param bool $overwrite Whether to overwrite existing errors.
     * @return Entity The Entity.
     */
    public function setError(string $field, string|array $error, bool $overwrite = false): static
    {
        return $this->setErrors([$field => $error], $overwrite);
    }

    /**
     * Set all errors for the entity.
     * @param array $errors The errors.
     * @param bool $overwrite Whether to overwrite existing errors.
     */
    public function setErrors(array $errors, bool $overwrite = false): static
    {
        foreach ($errors AS $field => $error) {
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
     * Set an invalid value.
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
     * Check a value for errors.
     * @param mixed $value The value.
     * @return bool TRUE if the value has errors, otherwise FALSE.
     */
    protected static function checkError(mixed $value): bool
    {
        if ($value instanceof Entity) {
            return $value->hasErrors();
        }

        if (is_array($value)) {
            foreach ($value AS $val) {
                if (static::checkError($val)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Read errors from a value.
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
