<?php
use Cake\Routing\Router;

Router::plugin('Burzum/FileStorage', function($routes) {
	$routes->fallbacks();
});