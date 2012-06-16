<?php

if (file_exists($file = __DIR__.'/../../autoload.php'))
{
	$vendorDir = __DIR__.'/../../';
	include $file;
}
else
{
	$vendorDir = __DIR__.'/vendor/';
	include './vendor/autoload.php';
}

$env = new Fuel\Kernel\Environment();
$env->init(array(
	'name'         => 'test',
	'path'         => $vendorDir,
	'namespaces'   => require $vendorDir.'composer/autoload_namespaces.php',
	'environments' => array(),
));
