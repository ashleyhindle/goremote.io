<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class AuthenticJobsModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://authenticjobs.com/rss/custom.php?terms=&type=1,2,3,4,5,6,7&cats=&onlyremote=1&location=';
	const SOURCE_NAME = 'authenticjobs';
	const SOURCE_ID = 5;

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

			$explodedTitle = explode(':', (string) $job->title);

			$jobClass->applyurl = (string) $job->guid;
			$jobClass->position = (string) (count($explodedTitle) > 1) ? trim($explodedTitle[1]) : trim($job->title);
			$jobClass->dateadded = (string) (new \DateTime($job->pubDate))->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job->description;
			$jobClass->sourceid = self::SOURCE_ID;
			
			$jobClass->company->name = trim($explodedTitle[0]);

			$doc = new \DOMDocument();
			
			libxml_use_internal_errors(true);
			$doc->loadHTML(file_get_contents($jobClass->applyurl));
			libxml_clear_errors();

			$xpath = new \DOMXpath($doc);
			$elements = $xpath->query("//li[@class='twitter']");
			$jobClass->company->twitter = ($elements->length > 0) ? str_replace('@', '', trim($elements->item(0)->textContent)) : '';
			$jobClass->company->logo = '';

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}