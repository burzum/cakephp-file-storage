<?php
namespace Burzum\FileStorage\Model\Table;

/**
 * ImageStorageTable
 *
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
class ImageStorageTable extends FileStorageTable {

/**
 * Name
 *
 * @var string
 */
	public $name = 'ImageStorage';

/**
 * Initialize
 *
 * @param array $config
 * @return void
 */
	public function initialize(array $config) {
		parent::initialize($config);
		$this->addBehavior('Burzum/FileStorage.ImageStorage');
		$this->addBehavior('Burzum/Imagine.Imagine');
		$this->addBehavior('Burzum/FileStorage.UploadValidator', array(
			'localFile' => false,
			'validate' => true,
			'allowedExtensions' => array(
				'jpg', 'jpeg', 'png', 'gif'
			)
		));
	}

}
