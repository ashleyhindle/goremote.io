<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class GolangProjectsModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://golangprojects.com/remoterss.xml';
	const SOURCE_NAME = 'golangprojects';
	const SOURCE_ID = 9;

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

			//TODO Why doesn't this trim?
			//Inserted job (13) for Golang Engineer  from  Walmart eCommerce from golangprojects
			
			list($jobClass->position, $jobClass->company->name) = array_map('trim', explode('@', $job->title));

			$jobClass->applyurl = (string) $job->link;
			$jobClass->dateadded = (string) (new \DateTime($job->pubDate))->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->sourceid = self::SOURCE_ID;

			$fc = file_get_contents($jobClass->applyurl);
			//TODO this will break if they change their HTML :( I'd like to use DomXPath but there are no id's or classes
			preg_match('/<b>Job description<\/b>(.+)(?:<b>Instructions how to apply<\/b>)/ism', $fc, $descriptionMatches);
			preg_match('/Website: <a target="_blank" href="(.+)">(?:.+)<\/a>/u', $fc, $urlMatches);
			preg_match('/href="?\'?(?:https?:)?\/\/(?:www\.)?twitter\.com\/(?!search)(?!share)(?!golangprojects)(\w+)"?\'?/u', $fc, $twitterMatches);
			
			$jobClass->description = (!empty($descriptionMatches[1])) ? $descriptionMatches[1] : (string) $job->description;
			$jobClass->company->url = (!empty($urlMatches)) ? $urlMatches[1] : '';
			$jobClass->company->twitter = (!empty($twitterMatches[1])) ? $twitterMatches[1] : '';
			$jobClass->company->logo = '';

			$jobs[] = $jobClass;
		}
		
		return $jobs;
	}
}
