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
$env->dic->set_classes(array(
	'Asset'                   => 'Fuel\\Core\\Asset\\Base',
	'Cache'                   => 'Fuel\\Core\\Cache\\Base',
	'Cache_Format'            => 'Fuel\\Core\\Cache\\Storage\\Serialize',
	'Cache_Format.Json'       => 'Fuel\\Core\\Cache\\Storage\\Json',
	'Cache_Format.Serialize'  => 'Fuel\\Core\\Cache\\Storage\\Serialize',
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
	'Profiler'                => 'Fuel\\Core\\Profiler',
	'Request.Curl'            => 'Fuel\\Core\\Request\\Curl',
	'Security_String.Xss'     => 'Fuel\\Core\\Security\\String\\Xss',
	'Theme'                   => 'Fuel\\Core\\Theme\\Base',
	'Validation'              => 'Fuel\\Core\\Validation\\Base',
	'View.Markdown'           => 'Fuel\\Core\\View\\Markdown',
	'View.Twig'               => 'Fuel\\Core\\View\\Twig',
));

// Forge and return the Core Package object
return $env->forge('Loader.Package')
	->set_path(__DIR__)
	->set_namespace(false)
	->add_classes(array(
		'Fuel\\Core\\Asset\\Base' => __DIR__.'/classes/Asset/Base.php',
		'Fuel\\Core\\Cache\\Exception\\Expired' => __DIR__.'/classes/Cache/Exception/Expired.php',
		'Fuel\\Core\\Cache\\Exception\\NotFound' => __DIR__.'/classes/Cache/Exception/NotFound.php',
		'Fuel\\Core\\Cache\\Format\\Formatable' => __DIR__.'/classes/Cache/Format/Formatable.php',
		'Fuel\\Core\\Cache\\Format\\Json' => __DIR__.'/classes/Cache/Format/Json.php',
		'Fuel\\Core\\Cache\\Format\\Serialize' => __DIR__.'/classes/Cache/Format/Serialize.php',
		'Fuel\\Core\\Cache\\Format\\String' => __DIR__.'/classes/Cache/Format/String.php',
		'Fuel\\Core\\Cache\\Storage\\Base' => __DIR__.'/classes/Cache/Storage/Base.php',
		'Fuel\\Core\\Cache\\Storage\\File' => __DIR__.'/classes/Cache/Storage/File.php',
		'Fuel\\Core\\Cache\\Base' => __DIR__.'/classes/Cache/Base.php',
		'Fuel\\Core\\Controller\\Template' => __DIR__.'/classes/Controller/Template.php',
		'Fuel\\Core\\Form\\Inputable' => __DIR__.'/classes/Form/Inputable.php',
		'Fuel\\Core\\Loader\\Closure' => __DIR__.'/classes/Loader/Closure.php',
		'Fuel\\Core\\Loader\\Lowercase' => __DIR__.'/classes/Loader/Lowercase.php',
		'Fuel\\Core\\Migration\\Container\\Base' => __DIR__.'/classes/Migration/Container/Base.php',
		'Fuel\\Core\\Migration\\Base' => __DIR__.'/classes/Migration/Base.php',
		'Fuel\\Core\\Migration\\Exception' => __DIR__.'/classes/Migration/Exception.php',
		'Fuel\\Core\\Migration\\Migratable' => __DIR__.'/classes/Migration/Migratable.php',
		'Fuel\\Core\\Parser\\Markdown' => __DIR__.'/classes/Parser/Markdown.php',
		'Fuel\\Core\\Parser\\Twig' => __DIR__.'/classes/Parser/Twig.php',
		'Fuel\\Core\\Presenter\\Base' => __DIR__.'/classes/Presenter/Base.php',
		'Fuel\\Core\\Request\\Curl' => __DIR__.'/classes/Request/Curl.php',
		'Fuel\\Core\\Security\\String\\Xss' => __DIR__.'/classes/Security/String/Xss.php',
		'Fuel\\Core\\Theme\\Base' => __DIR__.'/classes/Theme/Base.php',
		'Fuel\\Core\\Debug' => __DIR__.'/classes/Debug.php',
		'Fuel\\Core\\Error' => __DIR__.'/classes/Error.php',
		'Fuel\\Core\\Profiler' => __DIR__.'/classes/Profiler.php',
		'Fuel\\Core\\Validation\\Validatable' => __DIR__.'/classes/Validation/Validatable.php',
	))
	->add_class_aliases(array(
		'Classes\\Controller\\Template'  => 'Fuel\\Core\\Controller\\Template',
		'Classes\\Presenter\\Base'       => 'Fuel\\Core\\Presenter\\Base',
		'Classes\\Request\\Curl'         => 'Fuel\\Core\\Request\\Curl',
	));
