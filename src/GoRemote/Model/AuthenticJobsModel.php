<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class AuthenticJobsModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://authenticjobs.com/rss/custom.php?terms=&type=1,2,3,4,5,6,7&cats=&onlyremote=1&location=';
	const SOURCE_NAME = 'authenticjobs';

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

			$explodedTitle = explode(':', (string) $job->title);
			$jobClass->applyurl = (string) $job->link;
			$jobClass->position = (string) trim($explodedTitle[1]);
			$jobClass->dateadded = (string) (new \DateTime($job->pubDate))->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job->description;
			$jobClass->sourceid = 5;
			$jobClass->companyid = 99;
			
			$jobClass->companyname = trim($explodedTitle[0]);
			$jobClass->companylogo = '';

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}