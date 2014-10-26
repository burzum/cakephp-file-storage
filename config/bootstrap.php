<?php
//App::uses('FileStorageUtils', 'FileStorage.Lib/Utility');
//App::uses('StorageManager', 'FileStorage.Lib');
//App::uses('LocalImageProcessingListener', 'FileStorage.Event');
//App::uses('LocalFileStorageListener', 'FileStorage.Event');
//App::uses('CakeEventManager', 'Event');

use Cake\Event\EventManager;
use FileStorage\Event\ImageProcessingListener;
use FileStorage\Event\LocalFileStorageListener;

$listener = new ImageProcessingListener();
EventManager::instance()->attach($listener);

$listener = new LocalFileStorageListener();
EventManager::instance()->attach($listener);
