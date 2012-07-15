<?php

namespace Fuel\Core;

/**
 * @backupGlobals  disabled
 */
class UriTest extends \PHPUnit_Framework_TestCase
{
	public function testConstruct()
	{
		$uri = new Uri('https://user:pass@answer.to/life/the/universe.and?everything=42');

		$this->assertEquals('https', $uri->getScheme());
		$this->assertEquals('user:pass', $uri->getUser());
		$this->assertEquals('user', $uri->getUsername());
		$this->assertEquals('pass', $uri->getPassword());
		$this->assertEquals('answer.to', $uri->getHostname());
		$this->assertEquals(array('life', 'the', 'universe'), $uri->getSegment());
		$this->assertEquals('and', $uri->getExtension());
		$this->assertEquals(array('everything' => '42'), $uri->getQuery());
	}

	public function testConstructArray()
	{
		$queryString = 'really=BobShane&notReally=FrankSinatra&certainlyNot=RobbieWilliams';
		$uriArray = array(
			'scheme' => 'ftp',
			'hostname' => 'it.was.a',
			'path' => 'not/so/good/year.by?'.$queryString.'&this=overwritten',
			'segments' => array('very', 'good', 'year.by'),
			'extension' => 'originallyBy',
			'query' => $queryString,
		);
		$uri = new Uri($uriArray);

		$this->assertEquals('ftp', $uri->getScheme());
		$this->assertEquals('it.was.a', $uri->getHostname());
		$this->assertEquals(array('not', 'so', 'good', 'year'), $uri->getSegment());
		$this->assertEquals('originallyBy', $uri->getExtension());
		$this->assertEquals($queryString, $uri->getQueryString());

		parse_str($queryString, $queryArray);
		$uriArray['username'] = 'username';
		unset($queryArray['notReally']);
		unset($uriArray['path']);
		unset($uriArray['extension']);
		$uriArray['query'] = $queryArray;
		$uri = new Uri($uriArray);


		$this->assertEquals('username', $uri->getUser());
		$this->assertEquals('username', $uri->getUsername());
		$this->assertEquals('/very/good/year', $uri->getPath());
		$this->assertEquals('by', $uri->getExtension());
		$this->assertEquals($queryArray, $uri->getQuery());
	}

	/**
	 * @expectedException  InvalidArgumentException
	 */
	public function testConstructInvalid()
	{
		new Uri(0);
	}

	public function testSetGetScheme()
	{
		$uri = new Uri();
		$uri->setScheme('test');
		$this->assertEquals('test', $uri->getScheme());
		$this->assertEquals('test://', $uri->getScheme(true));
	}

	public function testSetGetHostname()
	{
		$uri = new Uri();
		$uri->setHostname('not.butter');
		$this->assertEquals('not.butter', $uri->getHostname());
	}
}
