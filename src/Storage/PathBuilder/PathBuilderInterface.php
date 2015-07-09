<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2015 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use \Cake\ORM\Entity;

interface PathBuilderInterface {

/**
 * Builds the filename of under which the data gets saved in the storage adapter.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array $options
 * @return string
 */
	public function filename(Entity $entity, array $options = []);

/**
 * Builds the path under which the data gets stored in the storage adapter.
 *
 * @param Entity $entity
 * @param array $options
 * @return string
 */
	public function path(Entity $entity, array $options = []);

/**
 * Returns the path + filename.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array $options
 * @return string
 */
	public function fullPath(Entity $entity, array $options = []);

/**
 * Builds the URL under which the file is accessible.
 *
 * This is for example important for S3 and Dropbox but also the Local adapter
 * if you symlink a folder to your webroot and allow direct access to a file.
 *
 * @param \Cake\ORM\Entity $entity
 * @param array $options
 * @return string
 */
	public function url(Entity $entity, array $options = []);
}
