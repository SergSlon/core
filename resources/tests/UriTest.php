<?php

namespace Fuel\Core;

/**
 * @backupGlobals  disabled
 */
class UriTest extends \PHPUnit_Framework_TestCase
{
	public function testConstruct()
	{
		$uriString = 'https://user:pass@answer.to:5/life/the/universe.and?everything=42';
		$uri = new Uri($uriString);

		$this->assertEquals('https', $uri->getScheme());
		$this->assertEquals('user:pass', $uri->getUser());
		$this->assertEquals('user', $uri->getUsername());
		$this->assertEquals('pass', $uri->getPassword());
		$this->assertEquals('answer.to', $uri->getHostname());
		$this->assertEquals(5, $uri->getPort());
		$this->assertEquals(array('life', 'the', 'universe'), $uri->getSegment());
		$this->assertEquals('and', $uri->getExtension());
		$this->assertEquals(array('everything' => '42'), $uri->getQuery());
		$this->assertEquals($uriString, $uri->get());
		$this->assertEquals($uriString, strval($uri));
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

	public function testSetGetUser()
	{
		$uri = new Uri();
		$user = 'Jelmer';
		$pass = 'noneOfYourBusiness';
		$uri->setUser($user, $pass);

		$this->assertEquals($user.':'.$pass, $uri->getUser());
		$this->assertEquals($user.':'.$pass.'@', $uri->getUser(true));
		$this->assertEquals($user, $uri->getUserName());
		$this->assertEquals($pass, $uri->getPassword());
	}

	public function testSetGetHostname()
	{
		$uri = new Uri();
		$uri->setHostname('not.butter');
		$this->assertEquals('not.butter', $uri->getHostname());
	}

	public function testSetGetPort()
	{
		$uri = new Uri();
		$uri->setPort(8443);
		$this->assertEquals(8443, $uri->getPort());
		$this->assertEquals(':8443', $uri->getPort(true));
	}

	public function testSetGetSegments()
	{
		$uri = new Uri();
		$uri->setSegments(array('one', 'two', 'three'));

		$this->assertEquals('one', $uri->getSegment(1));
		$this->assertEquals('two', $uri->getSegment(2));
		$this->assertEquals('three', $uri->getSegment(3));
	}

	public function testSetGetPath()
	{
		$uri = new Uri();
		$path = 'come/as/you/are';
		$ext = 'nirvana';
		$query = array('clubOf' => 27);
		$uri->setPath($path.'.'.$ext.'?'.http_build_query($query));

		$this->assertEquals('/'.$path, $uri->getPath());

		return $uri;
	}

	/**
	 * @depends  testSetGetPath
	 */
	public function testSetGetExtension(Uri $uri)
	{
		$this->assertEquals('nirvana', $uri->getExtension());

		$uri->setExtension('KurtCobain');
		$this->assertEquals('KurtCobain', $uri->getExtension());
	}

	/**
	 * @depends  testSetGetPath
	 */
	public function testSetGetAddQuery(Uri $uri)
	{
		$this->assertEquals(array('clubOf' => 27), $uri->getQuery());

		$uri->addQuery(array('foundingMember' => 'true'));
		$this->assertEquals(array('clubOf' => 27, 'foundingMember' => 'true'), $uri->getQuery());

		$uri->setQuery('alsoSang=SmellsLikeTeenSpirit');
		$this->assertEquals('alsoSang=SmellsLikeTeenSpirit', $uri->getQueryString());
		$this->assertEquals('?alsoSang=SmellsLikeTeenSpirit', $uri->getQueryString(true));
	}
}
