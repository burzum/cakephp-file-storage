<?php
class GaufretteLoader {
/**
 * Gaufrette Vendor Classloader
 *
 * @param string $class Classname to be loaded
 * @return void
 */
	public static function load($class) {
		$base = Configure::read('FileStorage.GaufretteLib');
		if (empty($base)) {
			$base = CakePlugin::path('FileStorage') . 'Vendor' . DS . 'Gaufrette' . DS . 'src' . DS;
		}

		$class = str_replace('\\', DS, $class);
		if (file_exists($base . $class . '.php')) {
			include($base . $class . '.php');
		}
	}
}