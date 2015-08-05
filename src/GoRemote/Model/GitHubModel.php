<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class GitHubModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://jobs.github.com/positions.json?location=remote';
	const SOURCE_NAME = 'github';
	const SOURCE_ID = 3;

	public function getJobs()
	{
		$jobs = [];
		$json = $this->getJobsJson();
		$tz = new \DateTimeZone('Europe/London');

		foreach($json as $job) {
			$jobClass = new JobModel();

			$jobClass->applyurl = trim((string) $job['url']);
			$jobClass->position = trim((string) $job['title']);
			$jobClass->dateadded = (string) (new \DateTime($job['created_at']))->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job['description'];
			$jobClass->sourceid = self::SOURCE_ID;
			
			$jobClass->company->name = $job['company'];
			$jobClass->company->twitter = '';
			$jobClass->company->logo = str_replace('http://', '//', $job['company_logo']);

			$jobs[] = $jobClass;
		}

		return $jobs;
	}

	protected function getJobsJson()
	{
	   	return json_decode(file_get_contents(static::SOURCE_URL), true);
	}
}