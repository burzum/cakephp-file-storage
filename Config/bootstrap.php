<?php
App::uses('GaufretteLoader', 'FileStorage.Lib');
App::uses('StorageManager', 'FileStorage.Lib');
spl_autoload_register(__NAMESPACE__ .'\GaufretteLoader::load');

$adapterConfig = Configure::read('FileStorage.adapters');
if (!empty($adapterConfig) && is_array($adapterConfig)) {
	foreach ($adapterConfig as $name => $config) {
		StorageManager::config($name, $config);
	}
}