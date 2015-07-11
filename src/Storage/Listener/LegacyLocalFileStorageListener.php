<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Filesystem\Folder;

/**
 * Local FileStorage Event Listener for the CakePHP FileStorage plugin
 *
 * @author Florian Krämer
 * @author Tomenko Yegeny
 * @license MIT
 */
class LegacyLocalFileStorageListener extends LocalListener {

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaultConfig = [
		'pathBuilder' => 'BasePath',
		'pathBuilderOptions' => [
			'pathPrefix' => 'files',
			'modelFolder' => false,
			'preserveFilename' => true,
			'randomPath' => 'crc32'
		],
		'imageProcessing' => false,
	];
}
