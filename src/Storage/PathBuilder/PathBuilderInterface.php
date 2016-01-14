<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

use \Cake\Datasource\EntityInterface;

interface PathBuilderInterface {

	/**
	 * Builds the filename of under which the data gets saved in the storage adapter.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return string
	 */
	public function filename(EntityInterface $entity, array $options = []);

	/**
	 * Builds the path under which the data gets stored in the storage adapter.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return string
	 */
	public function path(EntityInterface $entity, array $options = []);

	/**
	 * Returns the path + filename.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return string
	 */
	public function fullPath(EntityInterface $entity, array $options = []);

	/**
	 * Builds the URL under which the file is accessible.
	 *
	 * This is for example important for S3 and Dropbox but also the Local adapter
	 * if you symlink a folder to your webroot and allow direct access to a file.
	 *
	 * @param \Cake\Datasource\EntityInterface $entity
	 * @param array $options
	 * @return string
	 */
	public function url(EntityInterface $entity, array $options = []);
}
