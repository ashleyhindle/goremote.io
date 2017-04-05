<?php
namespace GoRemote\Model;

class WorkingNomadsModel extends JobModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://www.workingnomads.co/api/exposed_jobs/';
	const SOURCE_NAME = 'workingnomads';
	const SOURCE_ID = 11;

	public function getJobs()
	{
		$jobs = [];
		$json = $this->getJobsJson();
		$tz = new \DateTimeZone('Europe/London');

		foreach($json as $job) {
			$jobClass = new JobModel();

			$jobClass->applyurl = trim((string) $job['url']);
			$jobClass->position = trim((string) $job['title']);
			$jobClass->dateadded = (string) (new \DateTime($job['pub_date']))->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job['description'];
			$jobClass->sourceid = self::SOURCE_ID;
			
			$jobClass->company->name = $job['company_name'];
			$jobClass->company->twitter = '';
			$jobClass->company->logo ='';

			$jobs[] = $jobClass;
		}

		return $jobs;
	}

	protected function getJobsJson()
	{
	   	return json_decode(file_get_contents(static::SOURCE_URL), true);
	}
}