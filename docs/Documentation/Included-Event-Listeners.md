Included Event Listeners
========================

**[For the deprecated event listeners please click here](Legacy-Event-Listeners.md)**

---

Introduction
------------

The included event listeners will throw a StorageException when something went wrong. It's your duty to handle them. Also you can configure a logger to the `storage` log scope to filter logs by this scope.

Each listener has a configured *Path Builder*, check the [path builder documentation] to see what they do and what their purpose is.

To change the path builder config for a listener check what path builder the listener is using and pass the path builder config to the constructor of the listener:

```php
$listener = new LocalListener([
	'pathBuilderOptions' => [
		// options go here
	]
]);
```

If you want to implement your own listeners you'll have to extend them from the [AbstractListener](../../src/Storage/Listener/AbstractListener.php) and implement the event callbacks.

Local Listener
--------------

The local listener will store files by default in this kind of path:

```
<basePath>/<model>/<randomPath>/<uuid>/<uuid>.<extension>
```

Example:

```
/var/www/my_app/files/Documents/05/51/68/38f684612c6f11e5a2cb0800200c9a66/38f684612c6f11e5a2cb0800200c9a66.jpg
```

The listener is using by default the `LocalPathBuilder` to generate the path.

The reason for the UUID folder name is simply to ensure it is unique per file and it makes it easy to store versions of the same file in the same folder.

AWS S3 Listener
---------------

There is no new AWS S3 listener yet, you can either use the old legacy listener or write your own based on the new listeners. A contribution of a new listener is highly welcome!

Legacy Local File Storage Listener
----------------------------------

This listener mimics the behavior of the deprecated `LocalFileStorageEventListener`.
 