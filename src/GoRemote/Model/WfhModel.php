<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class WfhModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://www.wfh.io/jobs.atom';
	const SOURCE_NAME = 'wfh';
	const SOURCE_ID = 2;

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

		foreach($this->getRss()->entry as $job) {
			$jobClass = new JobModel();

			$jobClass->applyurl = (string) $job->link->attributes()->href;
			$jobClass->position = (string) $job->title;
			$jobClass->dateadded = (string) (new \DateTime($job->published))->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job->content;
			$jobClass->sourceid = self::SOURCE_ID;
			
			//TODO: Don't be so expensive
			$fc = file_get_contents($jobClass->applyurl);
			preg_match('/<small> @ (.+)<\/small>/', $fc, $matches);
			$jobClass->companyname = trim($matches[1]);
			$r = preg_match('/href="https?:\/\/(www)?.twitter.com\/(.+)"/', $fc, $matches);
			$jobClass->companytwitter = (!empty($r)) ? trim($matches[2]) : '';
			$jobClass->companylogo = '';

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}