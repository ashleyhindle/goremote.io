<?php
use Silex\WebTestCase;

class HomepageControllerTest extends WebTestCase
{
	public function createApplication()
	{
	    // app.php must return an Application instance
	    $app = require __DIR__ . '/../../../../src/app.php';
	    $app['debug'] = true;
	    unset($app['exception_handler']);
	    return $app;
	}

	public function testHomepageLoads() {
		$client = $this->createClient();
		$crawler = $client->request('GET', '/');

	    $this->assertTrue($client->getResponse()->isOk());
	    $this->assertCount(1, $crawler->filter('h1:contains("Latest Remote Jobs ")'));
	    $this->assertCount(2, $crawler->filter('form')); // MailChimp, Search
	 }
}