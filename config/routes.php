<?php
use Cake\Routing\Router;

Router::plugin('FileStorage', function($routes) {
	$routes->fallbacks();
});