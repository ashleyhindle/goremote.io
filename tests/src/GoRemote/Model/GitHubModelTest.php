<?php
use Silex\WebTestCase;
use GoRemote\Model\GitHubModel;

class MockGitHubModel extends GitHubModel
{
  	const SOURCE_URL = "tests/fixtures/github-job.json";
}

class GitHubModelTest extends WebTestCase
{
	public function createApplication()
	{
	    // app.php must return an Application instance
	    $app = require __DIR__ . '/../../../../src/app.php';
	    $app['debug'] = true;
	    unset($app['exception_handler']);
	    return $app;
	}

	public function testGetJobResponseValid() {
		$model = new MockGitHubModel();
		$jobs = $model->getJobs();
	 	$this->assertCount(1, $jobs);
	 	$this->assertEquals('Senior Ruby on Rails Engineer', $jobs[0]->position);
	 }
}