<?php
/**
 * Generate Package Loader object and related configuration
 *
 * @package    Fuel\Core
 * @version    2.0.0
 * @license    MIT License
 * @copyright  2010 - 2012 Fuel Development Team
 */

// Add the core procedural helpers
require 'helpers.php';

// Add some Core classes to the global DiC
$env->dic->setClasses(array(
	'Cache'                   => 'Fuel\\Cache\\Base',
	'Cache_Format'            => 'Fuel\\Cache\\Format\\Serialize',
	'Cache_Format.Json'       => 'Fuel\\Cache\\Format\\Json',
	'Cache_Format.Serialize'  => 'Fuel\\Cache\\Format\\Serialize',
	'Cache_Format.String'     => 'Fuel\\Cache\\Storage\\String',
	'Cache_Storage'           => 'Fuel\\Cache\\Storage\\File',
	'Debugger'                => 'Fuel\\Core\\Debug',
	'Error'                   => 'Fuel\\Core\\Error',
	'Loader.Closure'          => 'Fuel\\Core\\Loader\\Closure',
	'Loader.Lowercase'        => 'Fuel\\Core\\Loader\\Lowercase',
	'FieldSet'                => 'Fuel\\FieldSet\\Base',
	'FieldSet_Field.Button'   => 'Fuel\\FieldSet\\Field\\Button',
	'FieldSet_Field.Options'  => 'Fuel\\FieldSet\\Field\\Options',
	'FieldSet_Field.Text'     => 'Fuel\\FieldSet\\Field\\Text',
	'Form'                    => 'Fuel\\Core\\Form\\Base',
	'Migration'               => 'Fuel\\Migration\\Base',
	'Migration_Container'     => 'Fuel\\Migration\\Container\\Base',
	'Migration_Container_Storage'  => 'Fuel\\Migration\\Container\\Storage\\Base',
	'Parser.Markdown'         => 'Fuel\\Core\\Parser\\Markdown',
	'Parser.Twig'             => 'Fuel\\Core\\Parser\\Twig',
	'Profiler'                => 'Fuel\\Core\\Profiler',
	'Request.Curl'            => 'Fuel\\Core\\Request\\Curl',
	'Security_String.Xss'     => 'Fuel\\Core\\Security\\String\\Xss',
	'Validation'              => 'Fuel\\Core\\Validation\\Fuel',
	'Validation.Value'        => 'Fuel\\Validation\\Value\\Base',
	'Validation.Error'        => 'Fuel\\Validation\\Error\\Base',
	'View.Markdown'           => 'Fuel\\Core\\View\\Markdown',
	'View.Twig'               => 'Fuel\\Core\\View\\Twig',
));

// Add third party suggested stuff
$env->dic->setClasses(array(
	// Swiftmailer
	'Email' => 'Swift_Message',

	// Imagine
	'Image'          => 'Imagine\\Gd\\Imagine',
	'Image.Gd'       => 'Imagine\\Gd\\Imagine',
	'Image.Imagick'  => 'Imagine\\Imagick\\Imagine',
	'Image.Gmagick'  => 'Imagine\\Gmagick\\Imagine',
));

// Forge and return the Core Package object
return $env->forge('Loader.Package')
	->setPath(__DIR__)
	->setNamespace(false)
	->addClassAliases(array(
		'Classes\\Controller\\Template'  => 'Fuel\\Core\\Controller\\Template',
		'Classes\\Request\\Curl'         => 'Fuel\\Core\\Request\\Curl',
	));
