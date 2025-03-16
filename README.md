# FyreEntity

**FyreEntity** is a free, open-source entity library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Methods](#methods)
- [Entities](#entities)



## Installation

**Using Composer**

```
composer require fyre/entity
```

In PHP:

```php
use Fyre\Entity\EntityLocator;
```


## Basic Usage

- `$inflector` is an [*Inflector*](https://github.com/elusivecodes/FyreInflector).

```php
$entityLocator = new EntityLocator($inflector);
```

**Autoloading**

It is recommended to bind the *EntityLocator* to the [*Container*](https://github.com/elusivecodes/FyreContainer) as a singleton.

```php
$container->singleton(EntityLocator::class);
```


## Methods

**Add Namespace**

Add a namespace for locating entities.

- `$namespace` is a string representing the namespace.

```php
$entityLocator->addNamespace($namespace);
```

**Clear**

Clear all namespaces and entities.

```php
$entityLocator->clear();
```

**Find**

Find the entity class name for an alias.

- `$alias` is a string representing the alias.

```php
$className = $entityLocator->find($alias);
```

**Find Alias**

Find the alias for an entity class name.

- `$className` is a string representing the entity class name.

```php
$alias = $entityLocator->findAlias($className);
```

**Get Default Entity Class**

Get the default entity class name.

```php
$defaultEntityClass = $entityLocator->getDefaultEntityClass();
```

**Get Namespaces**

Get the namespaces.

```php
$namespaces = $entityLocator->getNamespaces();
```

**Has Namespace**

Check if a namespace exists.

- `$namespace` is a string representing the namespace.

```php
$hasNamespace = $entityLocator->hasNamespace($namespace);
```

**Map**

Map an alias to an entity class name.

- `$alias` is a string representing the alias.
- `$className` is a string representing the entity class name.

```php
$entityLocator->map($alias, $className);
```

**Remove Namespace**

Remove a namespace.

- `$namespace` is a string representing the namespace.

```php
$entityLocator->removeNamespace($namespace);
```

**Set Default Entity Class**

Set the default entity class name.

- `$defaultEntityClass` is a string representing the default entity class name.

```php
$entityLocator->setDefaultEntityClass($defaultEntityClass);
```


## Entities

```php
use Fyre\Entity\Entity;
```

- `$data` is an array containing the data for populating the entity.
- `$options` is an array containing the options for creating the entity.
    - `source` is a string representing the entity source, and will default to *null*.
    - `new` is a boolean indicating whether the entity is new, and will default to *true*.
    - `clean` is a boolean indicating whether to clean the entity after init, and will default to *true*.

```php
$entity = new Entity($data, $options);
```

**Clean**

Clean the entity.

```php
$entity->clean();
```

**Clear**

Clear values from the entity.

- `$fields` is an array containing the fields to clear.

```php
$entity->clear($fields);
```

**Extract**

Extract values from the entity.

- `$fields` is an array containing the fields to extract.

```php
$values = $entity->extract($fields);
```

**Extract Dirty**

Extract dirty values from the entity.

- `$fields` is an array containing the fields to extract.

```php
$values = $entity->extractDirty($fields);
```

If the `$field` argument is omitted, this method will return all dirty values.

```php
$values = $entity->extractDirty();
```

**Extract Original**

Extract original values from the entity.

- `$fields` is an array containing the fields to extract.

```php
$values = $entity->extractOriginal($fields);
```

**Fill**

Fill the entity with values.

- `$fields` is an array containing the data to fill.
- `$options` is an array containing options for filling the entity.
    - `guard` is a boolean indicating whether to check whether the field is accessible, and will default to *true*.
    - `mutate` is a boolean indicating whether to mutate the value, and will default to *true*.

```php
$entity->fill($data, $options);
```

If the `mutate` option is set to *true*, and a `_setFieldName` method exists in the entity (where the field name is *field_name*), then that method will be called for each value being set. The argument will be the value being populated, and the return value of that method will be stored in the entity instead.

**Fill Invalid**

Fill the entity with invalid values.

- `$fields` is an array containing the data to fill.
- `$overwrite` is a boolean indicating whether to overwrite existing values, and will default to *false*.

```php
$entity->fillInvalid($data, $overwrite);
```

**Get**

Get a value from the entity.

- `$field` is a string representing the field name.

```php
$value = $entity->get($field);
```

Alternatively, you can get a value using the magic `__get` method or array syntax.

```php
$value = $entity->$field;
$value = $entity[$field];
```

If a `_getFieldName` method exists in the entity (where the field name is *field_name*), then that method will be called for the value being retrieved. The argument of that method will be the value stored in the entity, and the return value of that method will be returned instead.

**Get Accessible**

Get the accessible fields from the entity.

```php
$accessible = $entity->getAccessible();
```

**Get Dirty**

Get the dirty fields from the entity.

```php
$dirty = $entity->getDirty();
```

**Get Error**

Get the errors for an entity field.

- `$field` is a string representing the field name.

```php
$errors = $entity->getError($field);
```

**Get Errors**

Get all errors for the entity.

```php
$errors = $entity->getErrors();
```

**Get Hidden**

Get the hidden fields from the entity.

```php
$hidden = $entity->getHidden();
```

**Get Invalid**

Get invalid value(s) from the entity.

- `$field` is a string representing the field name.

```php
$value = $entity->getInvalid($field);
```

If the `$field` argument is omitted, this method will return all invalid values.

```php
$invalid = $entity->getInvalid();
```

**Get Original**

Get an original value from the entity.

- `$field` is a string representing the field name.

```php
$value = $entity->getOriginal($field);
```

If the `$field` argument is omitted, this method will return all original values.

```php
$original = $entity->getOriginal();
```

**Get Source**

Get the entity source.

```php
$source = $entity->getSource();
```

**Get Virtual**

Get the virtual fields from the entity.

```php
$virtual = $entity->getVirtual();
```

**Get Visible**

Get the visible fields from the entity.

```php
$visible = $entity->getVisible();
```

**Has**

Determine if an entity value is set.

- `$field` is a string representing the field name.

```php
$has = $entity->has($field);
```

Alternatively, you can determine if a value is set using the magic `__isset` method or array syntax.

```php
$isset = isset($entity->$field);
$isset = isset($entity[$field]);
```

**Has Value**

Determine if an entity value is not empty.

- `$field` is a string representing the field name.

```php
$hasValue = $entity->hasValue($field);
```

**Has Errors**

Determine if the entity has errors.

```php
$hasErrors = $entity->hasErrors();
```

**Is Accessible**

Determine if an entity field is accessible.

- `$field` is a string representing the field name.

```php
$isAccessible = $entity->isAccessible($field);
```

**Is Dirty**

Determine if an entity field is dirty.

- `$field` is a string representing the field name.

```php
$isDirty = $entity->isDirty($field);
```

If the `$field` argument is omitted, this method will determine whether the entity has any dirty fields.

```php
$isDirty = $entity->isDirty();
```

**Is Empty**

Determine if an entity value is empty.

- `$field` is a string representing the field name.

```php
$isEmpty = $entity->isEmpty($field);
```

If the `$field` argument is omitted, this method will determine whether all entity fields are empty.

```php
$isEmpty = $entity->isEmpty();
```

**Is New**

Determine if the entity is new.

```php
$isNew = $entity->isNew();
```

**Restore State**

Restore the saved entity state.

- `$restoreErrors` is a boolean indicating whether to restore the errors, and will default to *true*.

```php
$entity->restoreState($restoreErrors);
```

**Save State**

Save the current entity state.

```php
$entity->saveState();
```

**Set**

Set an entity value.

- `$field` is a string representing the field name.
- `$value` is the value to set.
- `$options` is an array containing options for filling the entity.
    - `guard` is a boolean indicating whether to check whether the field is accessible, and will default to *true*.
    - `mutate` is a boolean indicating whether to mutate the value, and will default to *true*.

```php
$entity->set($field, $value, $options);
```

Alternatively, you can set a value using the magic `__set` method or array syntax.

```php
$entity->$field = $value;
$entity[$field] = $value;
```

If the `mutate` option is set to *true*, and a `_setFieldName` method exists in the entity (where the field name is *field_name*), then that method will be called for the value being set. The argument will be the value being populated, and the return value of that method will be stored in the entity instead.

**Set Access**

Set whether a field is accessible.

- `$field` is a string representing the field name.
- `$accessible` is a boolean indicating whether the field is accessible.

```php
$entity->setAccess($field, $accessible);
```

**Set Dirty**

Set whether a field is dirty.

- `$field` is a string representing the field name.
- `$dirty` is a boolean indicating whether the field is dirty, and will default to *true*.

```php
$entity->setDirty($field, $dirty);
```

**Set Error**

Set errors for an entity field.

- `$field` is a string representing the field name.
- `$error` is a string or array containing the errors.
- `$overwrite` is a boolean indicating whether to overwrite existing errors, and will default to *false*.

```php
$entity->setError($field, $error, $ovewrite);
```

**Set Errors**

Set all errors for the entity.

- `$errors` is an array containing the errors.
- `$overwrite` is a boolean indicating whether to overwrite existing errors, and will default to *false*.

```php
$entity->setErrors($errors, $overwrite);
```

**Set Hidden**

Set hidden fields.

- `$field` is an array containing the field names.
- `$merge` is a boolean indicating whether to merge with existing fields.

```php
$entity->setHidden($fields, $merge);
```

**Set Invalid**

Set an invalid value.

- `$field` is a string representing the field name.
- `$value` is the value to set.
- `$overwrite` is a boolean indicating whether to overwrite existing errors, and will default to *true*.

```php
$entity->setInvalid($field, $value, $overwrite);
```

**Set New**

Set whether the entity is new.

- `$new` is a boolean whether the entity is new, and will default to *true*.

```php
$entity->setNew($new);
```

**Set Source**

Set the entity source.

- `$source` is a string representing the source.

```php
$entity->setSource($source);
```

**Set Virtual**

Set virtual fields.

- `$field` is an array containing the field names.
- `$merge` is a boolean indicating whether to merge with existing fields.

```php
$entity->setVirtual($fields, $merge);
```

**To Array**

Convert the entity to an array.

```php
$array = $entity->toArray();
```

**To JSON**

Convert the entity to a JSON string.

```php
$json = $entity->toJson();
```

Alternatively, you can cast the value to a string using the magic `__toString` method.

```php
$json = (string) $entity;
```

**Unset**

Unset an entity value.

- `$field` is a string representing the field name.

```php
$entity->unset($field);
```

Alternatively, you can unset a value using the magic `__unset` method or array syntax.

```php
unset($entity->$field);
unset($entity[$field]);
```