Database Setup
==============

You need to setup the plugin database using [the official migrations plugin for CakePHP](https://github.com/cakephp/migrations).

```
cake migrations migrate -p Burzum/FileStorage
```

If you're coming from the CakePHP 2.0 version of the plugin, the support for the CakeDC Migrations plugin has been dropped in favor of the official migrations plugin.

Integer type IDs vs UUIDs
-------------------------

If you want to use integers instead of [UUIDs](http://en.wikipedia.org/wiki/Universally_unique_identifier) put this into your ```bootstrap.php``` *before* you're running the migrations:

```php
Configure::write('FileStorage.schema.useIntegers', true);
```

This config option is **not** available for the regular CakePHP schema that comes with the plugin. It seems to be impossible to override the type on the fly. If you can figure out how to do it a pull request is welcome.
