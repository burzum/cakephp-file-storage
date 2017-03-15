<?php
namespace Burzum\FileStorage\Event;

use Burzum\Imagine\Lib\ImageProcessor;
use Burzum\Imagine\Lib\ImagineUtility;
use Cake\Event\Event;
use Cake\Core\Configure;
use Cake\ORM\Table;
use Burzum\FileStorage\Storage\StorageManager;
use Burzum\FileStorage\Storage\StorageUtils;
use RuntimeException;

/**
 * @author Florian Krämer
 * @copy 2013 - 2015 Florian Krämer
 * @license MIT
 */
class ImageProcessingListener extends AbstractStorageEventListener {

	/**
	 * The adapter class
	 *
	 * @param null|string
	 */
	public $adapterClass = null;

	/**
	 * ImageProcessor instance
	 *
	 * @var ImageProcessor
	 */
	public $_imageProcessor = null;

	/**
	 * Name of the storage table class name the event listener requires the table
	 * instances to extend.
	 * This information is important to know when to use the event callbacks or not.
	 * Must be \FileStorage\Model\Table\FileStorageTable or \FileStorage\Model\Table\ImageStorageTable
	 *
	 * @var string
	 */
	public $storageTableClass = '\Burzum\FileStorage\Model\Table\ImageStorageTable';

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		$this->config('autoRotate', []);
		$this->config($config);
		$this->_imageProcessor = new ImageProcessor();
	}

	/**
	 * Implemented Events
	 *
	 * @return array
	 */
	public function implementedEvents() {
		return array(
			'ImageVersion.createVersion' => 'createVersions',
			'ImageVersion.removeVersion' => 'removeVersions',
			'ImageVersion.getVersions' => 'imagePath',
			'ImageStorage.beforeSave' => 'beforeSave',
			'ImageStorage.afterSave' => 'afterSave',
			'ImageStorage.afterDelete' => 'afterDelete',
			'FileStorage.ImageHelper.imagePath' => 'imagePath' // Deprecated
		);
	}

	/**
	 * Auto rotates the image if an orientation in the exif data is found that is not 0.
	 * This applies only to JPEG and TIFF since the intention of this function is to 
	 * take into account the orientation data saved in the meta data (EXIF) by digital cameras.
	 *
	 * @param string $imageFile Path to the image file.
	 * @param string $format Format of the image to save. Workaround for imagines save(). :(
	 * @return boolean
	 */
	protected function _autoRotate($imageFile, $format) {
		$format = strtolower($format);
		if (!in_array($format, ['jpg', 'jpeg', 'tif', 'tiff'])) {
			return false;
		}
		
		$orientation = ImagineUtility::getImageOrientation($imageFile);
		$degree = 0;
		if ($orientation === false) {
			return false;
		}
		if ($orientation === 0) {
			return true;
		}
		switch ($orientation) {
			case 180:
				$degree = -180;
				break;
			case -90:
				$degree = 90;
				break;
			case 90:
				$degree = -90;
				break;
		}
		$processor = new ImageProcessor();
		$image = $processor->open($imageFile);
		$processor->rotate(['degree' => $degree]);
		$image->save($imageFile, ['format' => $format]);
		return true;
	}

	/**
	 * Creates the different versions of images that are configured
	 *
	 * @param \Cake\ORM\Table $table
	 * @param array $entity
	 * @param array $operations
	 * @throws \Burzum\FileStorage\Event\Exception
	 * @throws \Exception
	 * @return false|null
	 */
	protected function _createVersions(Table $table, $entity, array $operations) {
		$Storage = StorageManager::getAdapter($entity['adapter']);
		$path = $this->_buildPath($entity, true);
		$tmpFile = $this->_tmpFile($Storage, $path, TMP . 'image-processing');

		foreach ($operations as $version => $imageOperations) {
			$hash = StorageUtils::hashOperations($imageOperations);
			$string = $this->_buildPath($entity, true, $hash);

			if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
				$string = str_replace('\\', '/', $string);
			}

			if ($Storage->has($string)) {
				return false;
			}

			try {
				$image = $table->processImage($tmpFile, null, array('format' => $entity['extension']), $imageOperations);
				$Storage->write($string, $image->get($entity['extension']), true);
			} catch (\Exception $e) {
				$this->log($e->getMessage());
				unlink($tmpFile);
				throw $e;
			}
		}

		unlink($tmpFile);
	}

	/**
	 * Creates versions for a given image record
	 *
	 * @param Event $Event
	 * @return void
	 */
	public function createVersions(Event $Event) {
		if ($this->_checkEvent($Event)) {
			$table = $Event->subject();
			$record = $Event->getData('record');
			$this->_createVersions($table, $record, $Event->getData('operations'));
			$Event->stopPropagation();
		}
	}

	/**
	 * Removes versions for a given image record
	 *
	 * @param Event $Event
	 */
	public function removeVersions(Event $Event) {
		$this->_removeVersions($Event);
	}

	/**
	 * Removes versions for a given image record
	 *
	 * @param Event $Event
	 * @return void
	 */
	protected function _removeVersions(Event $Event) {
		if ($this->_checkEvent($Event)) {
			$Storage = $Event->getData('storage');
			$record = $Event->getData('record');
			foreach ((array)$Event->getData('operations') as $version => $operations) {
				$hash = StorageUtils::hashOperations($operations);
				$string = $this->_buildPath($record, true, $hash);
				if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
					$string = str_replace('\\', '/', $string);
				}
				try {
					if ($Storage->has($string)) {
						$Storage->delete($string);
					}
				} catch (\Exception $e) {
					$this->log($e->getMessage());
				}
			}
			$Event->stopPropagation();
		}
	}

	/**
	 * afterDelete
	 *
	 * @param Event $Event
	 * @return boolean|null
	 */
	public function afterDelete(Event $Event) {
		if ($this->_checkEvent($Event)) {
			$record = $Event->getData('record');
			$string = $this->_buildPath($record, true, null);
			if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
				$string = str_replace('\\', '/', $string);
			}

			try {
				$Storage = StorageManager::adapter($record['adapter']);
				if (!$Storage->has($string)) {
					$Event->stopPropagation();
					$Event->result = false;
					return false;
				}
				$Storage->delete($string);
			} catch (\Exception $e) {
				$this->log($e->getMessage());
				$Event->stopPropagation();
				$Event->result = false;
				return false;
			}

			$operations = Configure::read('FileStorage.imageSizes.' . $record['model']);
			if (!empty($operations)) {
				$Event->setData('operations', $operations);
				$this->_removeVersions($Event);
			}

			$Event->stopPropagation();
			$Event->result = true;

			return true;
		}
	}

	/**
	 * beforeSave
	 *
	 * @param Event $Event
	 * @return void
	 */
	public function beforeSave(Event $Event) {
		if ($this->_checkEvent($Event)) {
			$record = $Event->getData('record');
			if (empty($record['model'])) {
				return;
			}
			if (in_array($record['model'], (array)$this->getConfig('autoRotate'))) {
				$imageFile = $record['file']['tmp_name'];
				$format = StorageUtils::fileExtension($record['file']['name']);
				$this->_autoRotate($imageFile, $format);
			}
		}
	}

	/**
	 * afterSave
	 *
	 * @param Event $Event
	 * @return void
	 */
	public function afterSave(Event $Event) {
		if ($this->_checkEvent($Event)) {
			$table = $Event->subject();
			$record = $Event->getData('record');
			$Storage = StorageManager::getAdapter($record->get('adapter'));
			try {
				$id = $record->{$table->primaryKey()};
				$filename = $this->stripDashes($id);
				$file = $record['file'];
				$record['path'] = $this->fsPath('images' . DS . $record['model'], $id);
				if ($this->_config['preserveFilename'] === true) {
					$path = $record['path'] . $record['filename'];
				} else {
					$path = $record['path'] . $filename . '.' . $record['extension'];
				}

				if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
					$path = str_replace('\\', '/', $path);
					$record['path'] = str_replace('\\', '/', $record['path']);
				}

				$Storage->write($path, file_get_contents($file['tmp_name']), true);

				$data = $table->save($record, array(
					'validate' => false,
					'callbacks' => false
				));

				$operations = Configure::read('FileStorage.imageSizes.' . $record['model']);
				if (!empty($operations)) {
					$this->_createVersions($table, $record, $operations);
				}
				$table->data = $data;
			} catch (\Exception $e) {
				$this->log($e->getMessage());
			}
		}
	}

	/**
	 * Generates the path the image url / path for viewing it in a browser depending on the storage adapter
	 *
	 * @param Event $Event
	 * @throws RuntimeException
	 * @return void
	 */
	public function imagePath(Event $Event) {
		$data = $Event->getData();
		extract($data);

		if (!isset($Event->data['image']['adapter'])) {
			throw new RuntimeException(__d('file_storage', 'No adapter config key passed!'));
		}

		$adapterClass = $this->getAdapterClassName($Event->data['image']['adapter']);
		$buildMethod = '_build' . $adapterClass . 'Path';

		if (method_exists($this, $buildMethod)) {
			return $this->$buildMethod($Event);
		}

		throw new RuntimeException(__d('file_storage', 'No callback image url callback implemented for adapter %s', $adapterClass));
	}

	/**
	 * Builds an url to the given image
	 *
	 * @param Event $Event
	 * @return void
	 */
	protected function _buildLocalPath(Event $Event) {
		$data = $Event->getData();
		extract($data);
		$path = $this->_buildPath($image, true, $hash);
		$data['path'] = $Event->result = '/' . $path;
		$Event->setData($data);
		$Event->stopPropagation();
	}

	/**
	 * Wrapper around the other AmazonS3 Adapter
	 *
	 * @param Event $Event
	 * @see ImageProcessingListener::_buildAmazonS3Path()
	 */
	protected function _buildAwsS3Path($Event) {
		$this->_buildAmazonS3Path($Event);
	}

	/**
	 * Builds an url to the given image for the amazon s3 adapter
	 * http(s)://<bucket>.s3.amazonaws.com/<object>
	 * http(s)://s3.amazonaws.com/<bucket>/<object>
	 *
	 * @param Event $Event
	 * @return void
	 */
	protected function _buildAmazonS3Path(Event $Event) {
		$data = $Event->getData();
		extract($data);

		$path = '/' . $this->_buildPath($image, true, $hash);

		$config = StorageManager::config($Event->data['image']['adapter']);
		$bucket = $config['adapterOptions'][1];
		if (!empty($config['cloudFrontUrl'])) {
			$cfDist = $config['cloudFrontUrl'];
		} else {
			$cfDist = null;
		}

		$http = 'http';
		if (!empty($data['options']['ssl']) && $data['options']['ssl'] === true) {
			$http = 'https';
		}

		$path = str_replace('\\', '/', $path);
		$bucketPrefix = !empty($data['options']['bucketPrefix']) && $data['options']['bucketPrefix'] === true;

		$data['path'] = $Event->result = $this->_buildCloudFrontDistributionUrl($http, $path, $bucket, $bucketPrefix, $cfDist);

		$Event->setData($data);
		$Event->stopPropagation();
	}

	/**
	 * Builds an url to serve content from cloudfront
	 *
	 * @param string $protocol
	 * @param string $image
	 * @param string $bucket
	 * @param string null $bucketPrefix
	 * @param string $cfDist
	 * @param boolean $bucketPrefix
	 * @return string
	 */
	protected function _buildCloudFrontDistributionUrl($protocol, $image, $bucket, $bucketPrefix = null, $cfDist = null) {
		$path = $protocol . '://';
		if (is_string($cfDist)) {
			$path .= $cfDist;
		} else {
			if ($bucketPrefix) {
				$path .= $bucket . '.s3.amazonaws.com';
			} else {
				$path .= 's3.amazonaws.com/' . $bucket;
			}
		}
		$path .= $image;

		return $path;
	}

	/**
	 * Builds a path to a file
	 *
	 * @param array $record
	 * @param boolean $extension
	 * @param string $hash
	 * @return string
	 */
	protected function _buildPath($record, $extension = true, $hash = null) {
		if ($this->_config['preserveFilename'] === true) {
			if (!empty($hash)) {
				$path = $record['path'] . preg_replace('/\.[^.]*$/', '', $record['filename']) . '.' . $hash . '.' . $record['extension'];
			} else {
				$path = $record['path'] . $record['filename'];
			}
		} else {
			$path = $record['path'] . str_replace('-', '', $record['id']);
			if (!empty($hash)) {
				$path .= '.' . $hash;
			}
			if ($extension === true) {
				$path .= '.' . $record['extension'];
			}
		}

		if ($this->adapterClass === 'AmazonS3' || $this->adapterClass === 'AwsS3') {
			return str_replace('\\', '/', $path);
		}

		return $path;
	}

	/**
	 * Gets the adapter class name from the adapter configuration key
	 *
	 * @param string
	 * @return string|false
	 */
	public function getAdapterClassName($adapterConfigName) {
		$config = StorageManager::config($adapterConfigName);

		switch ($config['adapterClass']) {
			case '\Gaufrette\Adapter\Local':
				$this->adapterClass = 'Local';
				return $this->adapterClass;
			case '\Gaufrette\Adapter\AwsS3':
				$this->adapterClass = 'AwsS3';
				return $this->adapterClass;
			case '\Gaufrette\Adapter\AmazonS3':
				$this->adapterClass = 'AwsS3';
				return $this->adapterClass;
			case '\Gaufrette\Adapter\AwsS3':
				$this->adapterClass = 'AwsS3';
				return $this->adapterClass;
			default:
				return false;
		}
	}

}
