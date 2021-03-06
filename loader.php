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
	'Debugger'                => 'Fuel\Core\Debug',
	'Error'                   => 'Fuel\Core\Error',
	'Loader.Closure'          => 'Fuel\Core\Loader\Closure',
	'Loader.Lowercase'        => 'Fuel\Core\Loader\Lowercase',
	'Form'                    => 'Fuel\Core\Form\Base',
	'Parser.Markdown'         => 'Fuel\Core\Parser\Markdown',
	'Parser.Twig'             => 'Fuel\Core\Parser\Twig',
	'Profiler'                => 'Fuel\Core\Profiler',
	'Request.Curl'            => 'Fuel\Core\Request\Curl',
	'Security\Csrf'           => 'Fuel\Core\Security\Csrf\Session',
	'Security\Csrf.Session'   => 'Fuel\Core\Security\Csrf\Session',
	'Security\String.Xss'     => 'Fuel\Core\Security\String\Xss',
	'Session'                 => 'Fuel\Core\Session\PhpNative',
	'Uri'                     => 'Fuel\Core\Uri',
	'Validation'              => 'Fuel\Core\Validation\Fuel',
	'Validation\Value'        => 'Fuel\Validation\Value\Base',
	'Validation\Error'        => 'Fuel\Validation\Error\Base',
	'View.Markdown'           => 'Fuel\Core\View\Markdown',
	'View.Twig'               => 'Fuel\Core\View\Twig',
));

// Add third party suggested stuff
$env->dic->setClasses(array(
	// Swiftmailer
	'Email' => 'Swift_Message',

	// Imagine
	'Image'          => 'Imagine\Gd\Imagine',
	'Image.Gd'       => 'Imagine\Gd\Imagine',
	'Image.Imagick'  => 'Imagine\Imagick\Imagine',
	'Image.Gmagick'  => 'Imagine\Gmagick\Imagine',
));

// Forge and return the Core Package object
return $env->forge('Loader.Package')
	->setPath(__DIR__)
	->setNamespace(false)
	->addClassAliases(array(
		'Fuel\Aliases\Controller\Template'  => 'Fuel\Core\Controller\Template',
		'Fuel\Aliases\Request\Curl'         => 'Fuel\Core\Request\Curl',
		'Fuel\Aliases\FieldSet\Field\Base'  => 'Fuel\Core\FieldSet\Field\Base',
	));
