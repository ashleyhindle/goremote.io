<?php
namespace GoRemote;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
	public function testApplicationCanBeLoaded()
	{
		$app = new \GoRemote\Application();
		$this->assertInstanceOf('\GoRemote\Application', $app);
	}
}