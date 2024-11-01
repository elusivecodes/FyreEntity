<?php
declare(strict_types=1);

namespace Fyre\Entity;

use Fyre\Utility\Inflector;
use ReflectionClass;

use function array_search;
use function array_splice;
use function class_exists;
use function in_array;
use function is_subclass_of;
use function trim;

/**
 * EntityLocator
 */
class EntityLocator
{
    protected string $defaultEntityClass = Entity::class;

    protected array $entities = [];

    protected Inflector $inflector;

    protected array $namespaces = [];

    /**
     * New EntityLocator constructor.
     *
     * @param Inflector $inflector The Inflector.
     * @param array $namespaces The namespaces.
     */
    public function __construct(Inflector $inflector, array $namespaces = [])
    {
        $this->inflector = $inflector;

        foreach ($namespaces AS $namespace) {
            $this->addNamespace($namespace);
        }
    }

    /**
     * Add a namespace for locating entities.
     *
     * @param string $namespace The namespace.
     * @return static The EntityLocator.
     */
    public function addNamespace(string $namespace): static
    {
        $namespace = static::normalizeNamespace($namespace);

        if (!in_array($namespace, $this->namespaces)) {
            $this->namespaces[] = $namespace;
        }

        return $this;
    }

    /**
     * Clear all namespaces and entities.
     */
    public function clear(): void
    {
        $this->namespaces = [];
        $this->entities = [];
    }

    /**
     * Find the entity class name for an alias.
     *
     * @param string $alias The alias.
     * @return string The entity class name.
     */
    public function find(string $alias): string
    {
        return $this->entities[$alias] ??= static::locate($alias);
    }

    /**
     * Find the alias for an entity class.
     *
     * @param string $entityClass The entity class name.
     * @return string The alias.
     */
    public function findAlias(string $entityClass): string
    {
        $alias = array_search($entityClass, $this->entities);

        if ($alias) {
            return $alias;
        }

        $name = (new ReflectionClass($entityClass))->getShortName();

        return $this->inflector->pluralize($name);
    }

    /**
     * Get the default entity class name.
     *
     * @return string The default entity class name.
     */
    public function getDefaultEntityClass(): string
    {
        return $this->defaultEntityClass;
    }

    /**
     * Get the namespaces.
     *
     * @return array The namespaces.
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Determine if a namespace exists.
     *
     * @param string $namespace The namespace.
     * @return bool TRUE if the namespace exists, otherwise FALSE.
     */
    public function hasNamespace(string $namespace): bool
    {
        $namespace = static::normalizeNamespace($namespace);

        return in_array($namespace, $this->namespaces);
    }

    /**
     * Map an alias to an entity class.
     *
     * @param string $alias The alias.
     * @param string $entityClass The entity class.
     * @return static The EntityLocator.
     */
    public function map(string $alias, string $entityClass): static
    {
        $this->entities[$alias] = $entityClass;

        return $this;
    }

    /**
     * Remove a namespace.
     *
     * @param string $namespace The namespace.
     * @return static The EntityLocator.
     */
    public function removeNamespace(string $namespace): static
    {
        $namespace = static::normalizeNamespace($namespace);

        foreach ($this->namespaces as $i => $otherNamespace) {
            if ($otherNamespace !== $namespace) {
                continue;
            }

            array_splice($this->namespaces, $i, 1);
            break;
        }

        return $this;
    }

    /**
     * Set the default entity class name.
     *
     * @param string $defaultEntityClass The default entity class name.
     * @return static The EntityLocator.
     */
    public function setDefaultEntityClass(string $defaultEntityClass): static
    {
        $this->defaultEntityClass = $defaultEntityClass;

        return $this;
    }

    /**
     * Locate the entity class name for an alias.
     *
     * @param string $alias The alias.
     * @return string The entity class name.
     */
    protected function locate(string $alias): string
    {
        $alias = $this->inflector->classify($alias);

        foreach ($this->namespaces as $namespace) {
            $fullClass = $namespace.$alias;

            if (class_exists($fullClass) && is_subclass_of($fullClass, Entity::class)) {
                return $fullClass;
            }
        }

        return $this->defaultEntityClass;
    }

    /**
     * Normalize a namespace
     *
     * @param string $namespace The namespace.
     * @return string The normalized namespace.
     */
    protected static function normalizeNamespace(string $namespace): string
    {
        return trim($namespace, '\\').'\\';
    }
}
