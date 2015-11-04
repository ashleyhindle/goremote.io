<?php
use Silex\WebTestCase;

class RssControllerTest extends WebTestCase
{
	public function createApplication()
	{
	    // app.php must return an Application instance
	    $app = require __DIR__ . '/../../../../src/app.php';
	    $app['debug'] = true;
	    unset($app['exception_handler']);
	    return $app;
	}

	public function testRssLoads() {
		$client = $this->createClient();
		$crawler = $client->request('GET', '/rss');
		$response = $client->getResponse();
	    $this->assertTrue($response->isOk());
	    $this->assertContains('<rss xmlns:dc="http://purl.org/dc/elements/1.1/" version="2.0">', $response->getContent());
	 }
}