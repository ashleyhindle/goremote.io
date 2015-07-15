<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class StackOverflowModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'http://careers.stackoverflow.com/jobs/feed?allowsremote=True';
	const SOURCE_NAME = 'stackoverflow';
	const SOURCE_ID = 4;

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
		foreach($this->getRss()->channel->item as $job) {
			$jobClass = new JobModel();

			$jobClass->applyurl = (string) $job->link;
			$jobClass->position = (string) current(explode(' at ', $job->title));
			$jobClass->dateadded = (string) (new \DateTime($job->pubDate))->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job->description;
			$jobClass->sourceid = self::SOURCE_ID;
			
			preg_match('/ at (.+)\(/U', (string) $job->title, $matches);
			$jobClass->companyname = trim($matches[1]);
			$jobClass->companylogo = '';

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}