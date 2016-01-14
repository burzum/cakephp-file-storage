<?php
namespace Burzum\FileStorage\View\Helper;

use Burzum\FileStorage\Storage\PathBuilder\PathBuilderTrait;
use Cake\View\View;
use Cake\View\Helper;

/**
 * Storage Helper
 *
 * This helper provides access to the path builders. This will allow you to get
 * the url/path and true filename of a file storage entity in the view.
 *
 * @author Florian Krämer
 * @copyright 2012 - 2016 Florian Krämer
 * @license MIT
 */
class StorageHelper extends Helper {

	use PathBuilderTrait;

	/**
	 * Default configuration
	 */
	protected $_defaultConfig = [
		'pathBuilder' => 'Base',
		'pathBuilderOptions' => [
			'modelFolder' => true
		]
	];

	/**
	 * Constructor
	 *
	 * @param \Cake\View\View
	 * @param array $config
	 */
	public function __construct(View $view, array $config = []) {
		parent::__construct($view, $config);

		$this->pathBuilder(
			$this->config('pathBuilder'),
			$this->config('pathBuilderOptions')
		);
	}

	/**
	 * Proxy to the configured path builder methods.
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args) {
		if (method_exists($this->_pathBuilder, $method)) {
			return call_user_func_array([$this->_pathBuilder, $method], $args);
		}
	}
}
