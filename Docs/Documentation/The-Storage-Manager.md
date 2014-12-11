The Storage Manager
===================

The [Storage Manager](Lib/StorageManager.php) class is a singleton class that manages a collection of storage adapter instances.

To configure adapters use the ```StorageManager::config()``` method. First argument is the name of the config, second an array of options for that adapter. The options array keys can be different for each adapter, depending on the storage system it connects to.

```php
StorageManager::config('Local', array(
	'adapterOptions' => array(TMP, true),
	'adapterClass' => '\Gaufrette\Adapter\Local',
	'class' => '\Gaufrette\Filesystem')
);
````
The files (using the default local adapter config) are stored in /tmp because in a proper application setup this should be the only writeable folder. To make the plugin work out of the box it’s using tmp. But it is recommended to change it to something else. The first item in the `adapterOptions` array sets the location where local files are saved. It defaults to `app/tmp`, but something like this is probably what you want to use in production:
```php
	'adapterOptions' => array(APP.'FileStorage', true),
````
Another reason files don’t go into the webroot and that you have to explicity change that is security. Good practice is to expose absolutely nothing except what we really want to. So either smylink the whole folder or just the folders you really want to be public accessible by URL. From the project root, that would look something like this: 
```$ ln -s ../FileStorage/images app/webroot/images```` 
This will allow public access to images outside of webroot.

To invoke a new instance using a before set configuration call.

```php
$Adapter = StorageManager::adapter('Local');
```

You can also call the adapter instances methods like this

```php
StorageManager::adapter('Local')->write($key, $data);
```

Alternatively you can pass a config array as first argument to get an instance using these settings that is not in the configuration.

To delete configs and by this the instance from the StorageManager call

```php
StorageManager::flush('Local');
```

If you want to flush *all* adapter configs and instances simply call it without the argument.

```php
StorageManager::flush();
```

There will be no adapter instance left after this, you must add a new config to use any adapter.
