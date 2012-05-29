<?php
App::uses('GaufretteLoader', 'FileStorage.Lib');
App::uses('StorageManager', 'FileStorage.Lib');
spl_autoload_register(__NAMESPACE__ .'\GaufretteLoader::load');