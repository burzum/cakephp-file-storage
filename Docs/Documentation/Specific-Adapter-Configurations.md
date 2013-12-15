# Specific Addapter Configuration

Gaufrette does not come with a lot detail about what exactly some adapters expect so here is a list to help you with that.

But you should not blindly copy and paste that code, get an understanding of the storage service you want to use before!

## OpenCloud (Rackspace)

Get the SDK from here http://github.com/rackspace/php-opencloud and add it to your class autoloader

```php
define('RAXSDK_SSL_VERIFYHOST', 0);
define('RAXSDK_SSL_VERIFYPEER', 0);

$connection = new \OpenCloud\Rackspace(
	'https://lon.identity.api.rackspacecloud.com/v2.0/', // Rackspace Auth URL
	array(
		'username' => 'YOUR-USERNAME',
		'apiKey' => 'YOUR-API-KEY'
	)
);

// LON (London) or DFW (Dallas)
$objstore = $connection->ObjectStore('cloudFiles', 'LON');

StorageManager::config('OpenCloudTest', array(
	'adapterOptions' => array(
		$objstore,
		'test1',
	),
	'adapterClass' => '\Gaufrette\Adapter\OpenCloud',
	'class' => '\Gaufrette\Filesystem')
);
```

## AmazonS3

Get the SDK from here http:// github.com/amazonwebservices/aws-sdk-for-php and load the sdk.class.php file from where ever you cloned the SDK.

```php
require_once(APP . 'Vendor' . DS . 'AwsSdk' . DS . 'sdk.class.php');
CFCredentials::set(array(
	'production' => array(
		'certificate_authority' => true,
		'key' => 'YOUR-KEY',
		'secret' => 'YOUR-SECRET')
	)
);
$s3 = new AmazonS3();

StorageManager::config('S3', array(
	'adapterOptions' => array(
		$s3,
		'YOUR-BUCKET-HERE'),
	'adapterClass' => '\Gaufrette\Adapter\AmazonS3',
	'class' => '\Gaufrette\Filesystem'));
```