# Path Builders

Path builders are classes that are used to build the storage paths for a file based on the information coming from the `file_storage` table.

A path builder *should but doesn't have to* build a unique path per entity based on all the data available in the entity.

They implement at least these methods:

 * **filename**: filename
 * **path**: relative path
 * **fullPath**: absolute path
 * **url**: URL to the file

Each of them will take a `FileStorage` entity as first argument. Based on that entity it will generate a path depending on the logic implemented in the path builder.

The reason for this is to separate or share, just as needed, the path building logic between different storage systems. For example S3 differs in it's first part of the path, it's using a bucket while locally you usually have something like a base path instead of the bucket.

If you want to change the way your files are saved extend the `BasePathBuilder` class.

## Using path builders

The path builders constructors take a single argument, an array. Every path builder *should* extend `BasePathBuilder` and provide at least some of the config options of it as well.

```php
$pathBuilder = new BasePathBuilder([
	'prefix' => 'some-prefix-for-the-path'
]);
$pathToEntity = $pathBuilder->path($entity);
```

## The PathBuilderTrait

The trait allows you to add two methods to any class:

 * `PathBuilderTrait::createPathBuilder()` will return a new instance of a path builder.
 * `PathBuilderTrait::pathBuilder()` will get/set a path builder from the `PathBuilder::$_pathBuilder` property.

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

## Path builders included in the plugin

### BasePathBuilder

This is the path builder all other BP's *should* inherit from. But if you like to write your very own BP you're free to implement it from the ground up but you'll have to use the PathBuilderInterface.

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
