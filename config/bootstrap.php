<?php
use Burzum\FileStorage\Storage\Listener\LocalListener;
use Cake\Event\EventManager;

$listener = new LocalListener([
    'processImages' => true
]);
EventManager::instance()->on($listener);
