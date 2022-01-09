# FyreEntity

**FyreEntity** is a free, entity library for *PHP*.


## Table Of Contents
- [Installation](#installation)
- [Entity Creation](#entity-creation)
- [Methods](#methods)
- [Mutations](#mutations)



## Installation

**Using Composer**

```
composer require fyre/entity
```

In PHP:

```php
use Fyre\Entity\Entity;
```


## Entity Creation

- `$data` is an array containing the data for populating the entity.
- `$options` is an array containing the options for creating the entity.
    - `source` is a string representing the entity source, and will default to *null*.
    - `new` is a boolean indicating whether the entity is new, and will default to *true*.
    - `clean` is a boolean indicating whether to clean the entity after init, and will default to *true*.

```php
$entity = new Entity($data, $options);
```


## Methods

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

Alternatively, you can get a value using the magic `__get` method.

```php
$value = $entity->$field;
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

Alternatively, you can determine if a value is set using the magic `__isset` method.

```php
$isset = isset($entity->$field);
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

**Is New**

Determine if the entity is new.

```php
$isNew = $entity->isNew();
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

Alternatively, you can set a value using the magic `__set` method.

```php
$entity->$field = $value;
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
$entity->setError($errors, $overwrite);
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

Alternatively, you can unset a value using the magic `__unset` method.

```php
unset($entity->$field);
```