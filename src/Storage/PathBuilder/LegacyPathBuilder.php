<?php
/**
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
namespace Burzum\FileStorage\Storage\PathBuilder;

/**
 * Includes bugs and workarounds that could not be removed without backward
 * compatibility breaking changes. Use this path builder for projects that you
 * migrated from the Cake2 version to Cake3.
 */
class LegacyPathBuilder extends BasePathBuilder {

	/**
	 * Overriding the defaults to get the matching legacy config.
	 *
	 * @inheritDoc
	 */
	protected $_defaultConfig = [
		'pathPrefix' => 'files',
		'modelFolder' => 'files',
		'preserveFilename' => false,
		'idFolder' => true,
		'randomPath' => 'crc32'
	];

	/**
	 * @inheritDoc
	 */
	public function randomPath($string, $level = 3, $method = 'sha1') {
		$string = str_replace('-', '', $string);
		return parent::randomPath($string, $level, $method);
	}
}
