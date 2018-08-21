<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class WeWorkRemotelyModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://weworkremotely.com/remote-jobs.rss';
	const SOURCE_NAME = 'weworkremotely';
	const SOURCE_ID = 1;

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

		$xml = $this->getRss();
		if (is_null($xml)) {
		    die('Failed to retrieve XML from: ' . self::SOURCE_URL . PHP_EOL);
        }

        $namespaces = $this->xml->getNamespaces(true);
		foreach($xml[0]->channel->item as $job) {
			$jobClass = new JobModel();

			list($jobClass->company->name, $jobClass->position) = explode(':', (string) $job->title);

			$jobClass->applyurl = (string) $job->link;
			$jobClass->dateadded = (string) (new \DateTime($job->pubDate))->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->description = (string) $job->description;
			$jobClass->sourceid = self::SOURCE_ID;
			$jobClass->company->logo = trim((string) $job->children($namespaces['media'])->content->attributes()->url) ?: '';
			$jobClass->company->twitter = '';

			$jobs[] = $jobClass;
		}

		return $jobs;
	}
}
