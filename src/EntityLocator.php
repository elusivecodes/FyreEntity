<?php
declare(strict_types=1);

namespace Fyre\Entity;

use Fyre\Utility\Inflector;

use function array_splice;
use function class_exists;
use function in_array;
use function is_subclass_of;
use function trim;

/**
 * EntityLocator
 */
abstract class EntityLocator
{
    protected static string $defaultEntityClass = Entity::class;

    protected static array $entities = [];

    protected static array $namespaces = [];

    /**
     * Add a namespace for locating entities.
     *
     * @param string $namespace The namespace.
     */
    public static function addNamespace(string $namespace): void
    {
        $namespace = static::normalizeNamespace($namespace);

        if (!in_array($namespace, static::$namespaces)) {
            static::$namespaces[] = $namespace;
        }
    }

    /**
     * Clear all namespaces and entities.
     */
    public static function clear(): void
    {
        static::$namespaces = [];
        static::$entities = [];
    }

    /**
     * Find the entity class name for an alias.
     *
     * @param string $alias The alias.
     * @return string The entity class name.
     */
    public static function find(string $alias): string
    {
        return static::$entities[$alias] ??= static::locate($alias);
    }

    /**
     * Get the default entity class name.
     *
     * @return string The default entity class name.
     */
    public static function getDefaultEntityClass(): string
    {
        return static::$defaultEntityClass;
    }

    /**
     * Get the namespaces.
     *
     * @return array The namespaces.
     */
    public static function getNamespaces(): array
    {
        return static::$namespaces;
    }

    /**
     * Determine if a namespace exists.
     *
     * @param string $namespace The namespace.
     * @return bool TRUE if the namespace exists, otherwise FALSE.
     */
    public static function hasNamespace(string $namespace): bool
    {
        $namespace = static::normalizeNamespace($namespace);

        return in_array($namespace, static::$namespaces);
    }

    /**
     * Remove a namespace.
     *
     * @param string $namespace The namespace.
     * @return bool TRUE If the namespace was removed, otherwise FALSE.
     */
    public static function removeNamespace(string $namespace): bool
    {
        $namespace = static::normalizeNamespace($namespace);

        foreach (static::$namespaces as $i => $otherNamespace) {
            if ($otherNamespace !== $namespace) {
                continue;
            }

            array_splice(static::$namespaces, $i, 1);

            return true;
        }

        return false;
    }

    /**
     * Set the default entity class name.
     *
     * @param string $defaultEntityClass The default entity class name.
     */
    public static function setDefaultEntityClass(string $defaultEntityClass): void
    {
        static::$defaultEntityClass = $defaultEntityClass;
    }

    /**
     * Locate the entity class name for an alias.
     *
     * @param string $alias The alias.
     * @return string The entity class name.
     */
    protected static function locate(string $alias): string
    {
        $alias = Inflector::singularize($alias);

        foreach (static::$namespaces as $namespace) {
            $fullClass = $namespace.$alias;

            if (class_exists($fullClass) && is_subclass_of($fullClass, Entity::class)) {
                return $fullClass;
            }
        }

        return static::$defaultEntityClass;
    }

    /**
     * Normalize a namespace
     *
     * @param string $namespace The namespace.
     * @return string The normalized namespace.
     */
    protected static function normalizeNamespace(string $namespace): string
    {
        $namespace = trim($namespace, '\\');

        return $namespace ?
            '\\'.$namespace.'\\' :
            '\\';
    }
}
