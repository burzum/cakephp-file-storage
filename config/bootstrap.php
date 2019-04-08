<?php
use Burzum\FileStorage\Storage\Listener\ImageProcessingListener;
use Burzum\FileStorage\Storage\Listener\LocalListener;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\Log\Log;

$listener = new LocalListener();
EventManager::instance()->on($listener);

if (\version_compare(Configure::version(), '3.7.0', 'ge')) {
	$imaginePluginIsLoaded = Plugin::isLoaded('Burzum/Imagine');
} else {
	$imaginePluginIsLoaded = Plugin::loaded('Burzum/Imagine');
}

if ($imaginePluginIsLoaded) {
	$listener = new ImageProcessingListener();
	EventManager::instance()->on($listener);
}

if (Log::getConfig('FileStorage') === null) {
    Log::setConfig('FileStorage', [
        'className' => 'File',
        'path' => LOGS,
        'levels' => [],
        'scopes' => ['fileStorage'],
        'file' => 'fileStorage.log',
    ]);
}
