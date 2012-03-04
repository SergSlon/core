<?php
/**
 * Generate Package Loader object and related configuration
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

// Add some Core classes to the global DiC
$env->dic->set_classes(array(
	'Debug'             => 'Fuel\\Core\\Debug',
	'Error'             => 'Fuel\\Core\\Error',
	'Loader:Closure'    => 'Fuel\\Core\\Loader\\Closure',
	'Loader:Lowercase'  => 'Fuel\\Core\\Loader\\Lowercase',
	'Profiler'          => 'Fuel\\Core\\Profiler',
	'Request:Curl'      => 'Fuel\\Core\\Request\\Curl',
	'Security:Xss'      => 'Fuel\\Core\\Security\\String\\Xss',
	'View:Markdown'     => 'Fuel\\Core\\View\\Markdown',
	'View:Twig'         => 'Fuel\\Core\\View\\Twig',
));

// Forge and return the Core Package object
return $env->forge('Loader:Package')
	->set_path(__DIR__)
	->set_namespace('Fuel\\Core')
	->add_class_aliases(array(
		'Classes\\Controller\\Template'  => 'Fuel\\Core\\Controller\\Template',
		'Classes\\Presenter\\Base'       => 'Fuel\\Core\\Presenter\\Base',
		'Classes\\Request\\Curl'         => 'Fuel\\Core\\Request\\Curl',
	));
