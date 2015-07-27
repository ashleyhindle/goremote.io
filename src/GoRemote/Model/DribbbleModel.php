<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class DribbbleModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://dribbble.com/jobs.rss?location=Anywhere';
	const SOURCE_NAME = 'dribbble';
	const SOURCE_ID = 8;

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

			preg_match('/ is hiring an? (.+)/', (string) $job->title, $matches);
			$jobClass->position = (string) $matches[1];
			$jobClass->applyurl = (string) $job->link;
			$jobClass->dateadded = (string) (new \DateTime($job->pubDate))->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job->title;
			$jobClass->sourceid = self::SOURCE_ID;

			$jobClass->companylogo = '';
			$jobClass->companytwitter = '';
			$jobClass->companyname = (string) $job->children('dc', true)->creator;

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}