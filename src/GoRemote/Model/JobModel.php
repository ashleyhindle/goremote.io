<?php
namespace GoRemote\Model;

class JobModel
{
	public $jobid = 0;
	public $applyurl;
	public $position;
	public $dateadded;
	public $description;
	public $sourceid;
	//TODO: Make ->company a class/model of Company instead of these silly variables
	public $company;
	public $hackernews = 0;

	const DEFAULT_SEARCH_INTERVAL = 2592000; // 86400 * 30 - 1 month (ish)

	public function __construct()
	{
		$this->company = new \GoRemote\Model\CompanyModel();
	}

	public function insert(\Doctrine\DBAL\Connection $db)
	{

		$jobDuplicate = $db->fetchColumn(
			'select jobid from jobs where (dateadded=? and sourceid=? and applyurl=?) or (position=? and companyid=?)',
			[
				(string) $this->dateadded,
				(int) $this->sourceid,
				(string) trim($this->applyurl),
				(string) trim($this->position),
				(int) $this->company->id
			]);

		if ($jobDuplicate) {
			return false;
		}

		$this->description = $string = preg_replace('/(<br\/>){2,}/','<br/>', html_entity_decode(trim(strip_tags(str_replace(
			['<div>', '</div>', '<br />', "\n\n"],
			['', "<br/>", "<br/>", "<br/>"], $this->description), '<b><strong><ul><li><br><br/><br />'))));

		$db->insert('jobs', [
			'applyurl' => trim($this->applyurl),
			'position' => trim($this->position),
			'dateadded' => $this->dateadded,
			'description' => trim($this->description),
			'sourceid' => $this->sourceid,
			'companyid' => $this->company->id,
			]);

		$this->jobid = $db->lastInsertId();
		return $this->jobid;
	}

	// TODO - separate into own model, with methods for getting random message type, method for replacements and such
	public function tweet($app)
	{
		$tweetMessage = "{companyname} are looking for {indefinitearticle} {position} {link} - work from anywhere! #remote";
		$app['twitter']->setToken(
			$app['config.twitter']['token'],
			$app['config.twitter']['token_secret']
			);
		$companyname = (!empty($this->company->twitter)) ? '@' . $this->company->twitter : trim($this->company->name);
		$tweet = [
			'status' => str_replace(
				[
					'{companyname}',
					'{indefinitearticle}',
					'{position}',
					'{link}'
				],
				[
					$companyname,
					'a', //TODO: calculate indefinite article properly
					trim($this->position),
					'https://goremote.io' . str_replace('https://goremote.io/', '', $app['url_generator']->generate('job-by-id', array('id' => $this->jobid)))
				],
				$tweetMessage
			)
		];

		return $app['twitter']->statuses_update($tweet);
	}

	public function getLatestJobs(\GoRemote\Application $app, $interval=self::DEFAULT_SEARCH_INTERVAL)
	{
		return $app['db']->fetchAll('select jobs.*, unix_timestamp(jobs.dateadded) as dateadded_unixtime, companies.name as companyname, companies.twitter as companytwitter, companies.url as companyurl, sources.name as sourcename, sources.url as sourceurl from jobs inner join companies using(companyid) inner join sources using(sourceid) where jobs.dateadded > UTC_TIMESTAMP() - INTERVAL ? SECOND and jobs.datedeleted=0 order by jobs.dateadded desc limit 170',
			[
				$interval
			]);
	}
}