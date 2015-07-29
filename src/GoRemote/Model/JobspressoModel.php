<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class JobspressoModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://jobspresso.co/?feed=job_feed&job_types=designer%2Cdeveloper%2Cmarketing%2Cproject-mgmt%2Csales%2Csupport%2Csys-admin%2Ctester&search_location&job_categories&search_keywords';
	const SOURCE_NAME = 'jobspresso';
	const SOURCE_ID = 7;

	private $xml;

	public function __construct()
	{

	}

	public function getXml()
	{
		return $this->xml;
	}

	public function setXml($xml)
	{
		$this->xml = new \SimpleXmlElement($xml);
		return $this->xml;
	}

	public function getRss()
	{
		return $this->setXml(file_get_contents(self::SOURCE_URL));
	}

	public function getJobs()
	{
		$jobs = [];
		$tz = new \DateTimeZone('Europe/London');

		foreach($this->getRss()->channel->item as $job) {
			$jobClass = new JobModel();

			$jobClass->position = (string) $job->title;
			$jobClass->applyurl = (string) $job->link;
			$jobClass->dateadded = (string) (new \DateTime($job->pubDate))->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->description = (string)$job->children('content', true)->encoded;
			$jobClass->sourceid = self::SOURCE_ID;

			$fc = file_get_contents($jobClass->applyurl);
			preg_match('/<img class="company_logo" src="(.+)" alt="(.+)" \/>/',
				$fc, $matches);

			$jobClass->companylogo = (!empty($matches[1])) ? $matches[1] : '';			
			$jobClass->companyname = (!empty($matches[2])) ? $matches[2] : $job->children('job_listing', true)->company;

			preg_match('/href="?\'?(?:https?:)?\/\/(?:www\.)?twitter\.com\/(?!search)(?!jobspresso)(\w+)"?\'?/iu', $fc, $matches);
			$jobClass->companytwitter = (!empty($matches[1])) ? $matches[1] : '';

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}