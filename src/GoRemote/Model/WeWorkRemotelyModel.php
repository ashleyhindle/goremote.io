<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class WeWorkRemotelyModel
{
	const SOURCE_URL = 'https://weworkremotely.com/jobs.rss';
	const SOURCE_NAME = 'weworkremotely';

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
		foreach($this->getRss()[0]->channel->item as $job) {
			$jobClass = new JobModel();

			$jobClass->applyurl = (string) $job->link;
			$jobClass->position = (string) $job->title;
			$jobClass->dateadded = (string) (new \DateTime($job->pubDate))->format('Y-m-d H:i:s');
			$jobClass->description = trim(strip_tags(str_replace(
				['<div>', '</div>', '</ul>', '<br />'],
				['', "\n", "\n", "\n"],
				(string) $job->description)));
			$jobClass->sourceid = self::SOURCE_NAME;
			$jobClass->companyid = self::SOURCE_NAME;

			$jobs[] = $jobClass;
			print_r($job);
			print_r($jobs);
			exit;
		}

		return $jobs;
	}
}