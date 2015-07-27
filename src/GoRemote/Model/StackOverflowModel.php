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

	// TODO Use DomDocument/DomXPath
	public function parseDescription($link) {
		$fc = file_get_contents($link);
		$count = preg_match_all('/<div class="description" ?>(.+)<\/div>/mUs', $fc, $matches);
		if (empty($count)) {
			return false;
		}

		return implode("\n", $matches[1]);
	}


	// TODO don't duplicate this, and don't be disgusting
	public function parseTwitter($link) {
		$companyUrl = '';
		$doc = new \DOMDocument();
		libxml_use_internal_errors(true);
		$doc->loadHTML(file_get_contents($link));

		foreach($doc->getElementsByTagName('a') as $link) { 
			if ($link->getAttribute('class') == 'employer') {
				$companyUrl = $link->getAttribute('href');
				break;
			}
		}

		if (empty($companyUrl)) {
			return '';
		}

		$fc = file_get_contents($companyUrl);
		$resultCount = preg_match('/href="?\'?(?:https?:)?\/\/(?:www\.)?twitter\.com\/(?!search)(\w+)"?\'?/u', $fc, $matches);

		if (empty($resultCount)) {
			return '';
		}

		return $matches[1];
	}

	public function getJobs()
	{
		$jobs = [];
		$tz = new \DateTimeZone('Europe/London');

		foreach($this->getRss()->channel->item as $job) {
			$jobClass = new JobModel();

			$jobClass->applyurl = (string) $job->link;
			$jobClass->position = (string) current(explode(' at ', $job->title));
			$jobClass->dateadded = (string) (new \DateTime($job->pubDate))->setTimezone($tz)->format('Y-m-d H:i:s');

			//Description from RSS is short, get it from the link if possible
			$jobClass->description = ($this->parseDescription((string)$job->link)) ?: (string) $job->description;
			$jobClass->sourceid = self::SOURCE_ID;
			
			preg_match('/ at (.+)\(/U', (string) $job->title, $matches);
			$jobClass->companyname = trim($matches[1]);
			$jobClass->companylogo = '';
			$jobClass->companytwitter = $this->parseTwitter((string)$job->link);

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}