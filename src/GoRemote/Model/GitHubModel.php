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
		$json = json_decode(file_get_contents(self::SOURCE_URL), true);
		$tz = new \DateTimeZone('Europe/London');

		foreach($json as $job) {
			$jobClass = new JobModel();

			$jobClass->applyurl = (string) $job['url'];
			$jobClass->position = (string) $job['title'];
			$jobClass->dateadded = (string) (new \DateTime($job['created_at']))->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job['description'];
			$jobClass->sourceid = self::SOURCE_ID;
			
			$jobClass->companyname = $job['company'];
			$jobClass->companylogo = str_replace('http://', '//', $job['company_logo']);

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}