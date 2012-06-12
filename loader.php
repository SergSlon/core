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
	'Asset'                   => 'Fuel\\Core\\Asset\\Base',
	'Cache'                   => 'Fuel\\Core\\Cache\\Base',
	'Cache_Format'            => 'Fuel\\Core\\Cache\\Format\\Serialize',
	'Cache_Format.Json'       => 'Fuel\\Core\\Cache\\Format\\Json',
	'Cache_Format.Serialize'  => 'Fuel\\Core\\Cache\\Format\\Serialize',
	'Cache_Format.String'     => 'Fuel\\Core\\Cache\\Storage\\String',
	'Cache_Storage'           => 'Fuel\\Core\\Cache\\Storage\\File',
	'Debug'                   => 'Fuel\\Core\\Debug',
	'Error'                   => 'Fuel\\Core\\Error',
	'Loader.Closure'          => 'Fuel\\Core\\Loader\\Closure',
	'Loader.Lowercase'        => 'Fuel\\Core\\Loader\\Lowercase',
	'Fieldset'                => 'Fuel\\Core\\Fieldset\\Base',
	'Fieldset_Field.Button'   => 'Fuel\\Core\\Fieldset\\Field\\Button',
	'Fieldset_Field.Options'  => 'Fuel\\Core\\Fieldset\\Field\\Options',
	'Fieldset_Field.Text'     => 'Fuel\\Core\\Fieldset\\Field\\Text',
	'Form'                    => 'Fuel\\Core\\Form\\Base',
	'Migration'               => 'Fuel\\Core\\Migration\\Base',
	'Migration_Container'     => 'Fuel\\Core\\Migration\\Container\\Base',
	'Migration_Container_Storage'  => 'Fuel\\Core\\Migration\\Container\\Storage\\Base',
	'Parser.Markdown'         => 'Fuel\\Core\\Parser\\Markdown',
	'Parser.Twig'             => 'Fuel\\Core\\Parser\\Twig',
	'Profiler'                => 'Fuel\\Core\\Profiler',
	'Request.Curl'            => 'Fuel\\Core\\Request\\Curl',
	'Security_String.Xss'     => 'Fuel\\Core\\Security\\String\\Xss',
	'Theme'                   => 'Fuel\\Core\\Theme\\Base',
	'Validation'              => 'Fuel\\Core\\Validation\\Fuel',
	'View.Markdown'           => 'Fuel\\Core\\View\\Markdown',
	'View.Twig'               => 'Fuel\\Core\\View\\Twig',
));

// Forge and return the Core Package object
return $env->forge('Loader.Package')
	->setPath(__DIR__)
	->setNamespace(false)
	->addClassAliases(array(
		'Classes\\Controller\\Template'  => 'Fuel\\Core\\Controller\\Template',
		'Classes\\Request\\Curl'         => 'Fuel\\Core\\Request\\Curl',
	));
