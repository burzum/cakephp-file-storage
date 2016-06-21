# Path Builders

Path builders are classes that are used to build the storage paths for a file based on the information coming from the `file_storage` table.

A path builder *should but doesn't have to* build a unique path per entity based on all the data available in the entity.

They implement at least these methods:

 * **filename**: filename
 * **path**: relative path
 * **fullPath**: absolute path
 * **url**: URL to the file

Each of them will take a an object that implements `\Cake\Datasource\EntityInterface` as first argument. Based on that entity object it will generate a path depending on the logic implemented in the path builder.

The reason for this is to separate or share, just as needed, the path building logic between different storage systems. For example S3 differs in it's first part of the path, it's using a bucket while locally you usually have something like a base path instead of the bucket.

If you want to change the way your files are saved extend the `BasePathBuilder` class or write your very own path builder by implementing the `PathBuilderInterface`.

**Warning:** Be aware that a path builder doesn't know and doesn't need to know about what adapter you're using. So if you have a verify specific path builder that works with one storage backend, it doesn't necessary have to work with another due to file system limitations or other kinds of restrictions. But usually most storage backends work the same way.

## Using path builders

The path builders constructors take a single argument, an array. Every path builder *should* extend `BasePathBuilder` and provide at least some of the config options of it as well.

```php
$pathBuilder = new BasePathBuilder([
	'prefix' => 'some-prefix-for-the-path'
]);
$pathToEntity = $pathBuilder->path($entity);
```

## The PathBuilderTrait

The trait will add a few methods to a class:

 * `createPathBuilder()` will return a new instance of a path builder.
 * `pathBuilder()` will get/set a path builder from the `PathBuilder::$_pathBuilder` property.
 * `getPathBuilder` gets the path builder object.
 * `setPathBuilder` sets a path builder.

If you want to configure a default path builder just add it's name to your config if your object is using the `InstanceConfigTrait` for example:


```php
protected $_defaultConfig = [
	'pathBuilder' => 'Base',
	'pathBuilderOptions' => [
		'modelFolder' => true
	]
];

public function __construct(View $view, array $config = []) {
	parent::__construct($view, $config);

	$this->pathBuilder(
		$this->config('pathBuilder'),
		$this->config('pathBuilderOptions')
	);
}
```

Or set your own configuration options up:

```php
public function __construct(array $properties = [], array $options = []) {
	$options += [
		'pathBuilder' => null,
		'pathBuilderOptions' => []
	];
	parent::__construct($properties, $options);
	if (is_string($options['pathBuilder'])) {
		$this->pathBuilder(
			$options['pathBuilder'],
			$options['pathBuilderOptions']
		);
	}
}
```

## Building your own path builder

Put the file in whatever your applications namespace is, usually it is `App`.

Place a file called `MyFancyPathBuilder.php`in `src/Storage/`.

```php
namespace App\Storage\PathBuilder;

use Burzum\FileStorage\Storage\PathBuilder\PathBuilderInterface;

class MyFancyPathBuilder implements PathBuilderInterface {
	// Implement the interface
}
```

Inside the method bodies of the interface you'll implement the logic of whatever path you want to build.

## Path builders included in the plugin

### BasePathBuilder

This is the path builder all other BP's *should* inherit from. But if you like to write your very own path builder you're free to implement it from the ground up but you'll have to use the `PathBuilderInterface`.

The BasePathBuilder comes with a set of configuration options:

```php
[
	'stripUuid' => true,
	'pathPrefix' => '',
	'pathSuffix' => '',
	'filePrefix' => '',
	'fileSuffix' => '',
	'preserveFilename' => false,
	'preserveExtension' => true,
	'uuidFolder' => true,
	'randomPath' => 'sha1'
]
```

### LegacyPathBuilder

It is basically the BasePathBuilder with a different default config and a minor change in the randomPath() method that was required to make it behave the same as the old code of the CakePHP2 version.
