<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\Listener;

/**
 * Local FileStorage Event Listener for the CakePHP FileStorage plugin
 *
 * @author Florian Krämer
 * @author Tomenko Yegeny
 * @license MIT
 */
class S3Listener extends LocalListener {

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaultConfig = [
		'pathBuilder' => 'S3',
		'pathBuilderOptions' => [
			'modelFolder' => false,
		],
		'fileHash' => false,
		'imageProcessing' => false,
	];

/**
 * List of adapter classes the event listener can work with.
 *
 * It is used in FileStorageEventListenerBase::getAdapterClassName to get the
 * class, to detect if an event passed to this listener should be processed or
 * not. Only events with an adapter class present in this array will be
 * processed.
 *
 * @var array
 */
	public $_adapterClasses = [
		'\Gaufrette\Adapter\AwsS3'
	];
}
