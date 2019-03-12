<?php
use Burzum\FileStorage\Storage\Listener\ImageProcessingListener;
use Burzum\FileStorage\Storage\Listener\LocalListener;
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\Log\Log;

$listener = new LocalListener();
EventManager::instance()->on($listener);

if (Plugin::isLoaded('Burzum/Imagine')) {
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