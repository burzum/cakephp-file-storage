<?php
App::uses('GaufretteLoader', 'FileStorage.Lib');
spl_autoload_register(__NAMESPACE__ .'\GaufretteLoader::load');