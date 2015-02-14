<?php
use \Cake\Event\EventManager;
use \Burzum\FileStorage\Event\ImageProcessingListener;
use \Burzum\FileStorage\Event\LocalFileStorageListener;

$listener = new ImageProcessingListener();
EventManager::instance()->on($listener);

$listener = new LocalFileStorageListener();
EventManager::instance()->on($listener);
