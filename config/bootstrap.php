<?php
use Burzum\FileStorage\Storage\Listener\ImageProcessingListener;
use Burzum\FileStorage\Storage\Listener\LocalListener;
use Cake\Event\EventManager;
use Cake\Log\Log;

$listener = new LocalListener();
EventManager::instance()->on($listener);

$listener = new ImageProcessingListener();
EventManager::instance()->on($listener);

Log::setConfig('FileStorage', [
    'className' => 'File',
    'path' => LOGS,
    'levels' => [],
    'scopes' => ['fileStorage'],
    'file' => 'fileStorage.log',
]);
