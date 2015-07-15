<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class WfhModel
{
	const SOURCE_URL = 'https://www.wfh.io/jobs.atom';
	const SOURCE_NAME = 'wfh';

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
		foreach($this->getRss()->entry as $job) {
			$jobClass = new JobModel();

			$jobClass->applyurl = (string) $job->link->attributes()->href;
			$jobClass->position = (string) $job->title;
			$jobClass->dateadded = (string) (new \DateTime($job->published))->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job->content;
			$jobClass->sourceid = 2;
			$jobClass->companyid = 99;
			
			//TODO: Don't be so expensive
			preg_match('/<small> @ (.+)<\/small>/', file_get_contents($jobClass->applyurl), $matches);
			$jobClass->companyname = trim($matches[1]);
			$jobClass->companylogo = '';

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}