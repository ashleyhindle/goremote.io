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
		// Check that the model doesn't break anything, like removing spaces from the position, or breaking the URL
		$model = new MockGitHubModel();
		$jobs = $model->getJobs();
		$job = $jobs[0];
	 	$this->assertCount(1, $jobs);
	 	$this->assertEquals('Senior Ruby on Rails Engineer', $job->position);
	 	$this->assertEquals('ArcheMedX', $job->company->name);
	 	$this->assertEquals('http://jobs.github.com/positions/bc201b4e-2ee0-11e5-9dae-12379d0e3001', $job->applyurl);
	 }
}