<?php
namespace GoRemote\Model;

use Doctrine\DBAL\Connection;

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

    protected $buzzwords = [
        'javascript', 'node', 'php', 'ruby', 'rails', 'clojure', 'java', 'html', 'css', 'frontend', 'front-end',
        'devops', 'backend', 'back-end', 'angular', 'chef', 'vagrant', 'docker', 'salt', 'haddop', 'cassandra', 'mysql',
        'postgresql', 'postgres', 'mobile', 'machine learning', 'nlp', 'react', 'operations', 'software engineer', 'manager', 'symfony',
        'laravel', 'qa', 'full-stack', 'full stack', 'rest ', 'api', 'senior', 'intern', 'ansible', 'consul', 'nginx',
        'coffeescript', 'backbone', 'knockout', 'haml', 'tdd', 'aws', 'python', 'flask', 'spring', 'sql', 'tomcat', 'designer',
        'cloud', 'scala', 'haskell', 'android', 'ios', 'swift', 'objective c', 'flux', 'redis', 'elasticache', 'elasticsearch',
        'browserify', 'git', 'nlp', 'machine learning', 'product manager', 'project manager', 'objective-c', 'ux ', 'ui ',
        'opencv', 'django', 'celery', 'erlang', 'amazon web services', 'linux', 'PCI', 'redshift', 'customer success', 'customer support',
        'jenkins', 'perl', 'golang', 'go ', 'paas', 'elastic search', 'game', ' unity', 'bgp', 'dns', 'scala', 'neo4j', 'c#', 'asp.net', '.net', 'marionette', 'mssql',
        'vpn', 'nosql', 'opengl', 'opencl', 'cuda', 'gpu', 'crypto', 'heroku', 'erlang', 'electron', 'mongo', 'dev-ops', 'dev ops',
        'phonegap', 'jenkins', 'saas', 'paas', 'security', 'analytics', 'physics', 'dba', 'distributed', 'containers', 'junior',
        'big data', 'data science', 'sales', 'cordova', 'multiple positions', 'haproxy', 'cdn', 'sass', 'zookeeper',
        'xml', 'json', 'system admin', 'zeromq', 'kafka', 'ec2', 'route53', 'aurora', 'es6', 'cloudfront', 'babel',
        'npm', 'mocha', 'marketing', ' qt', 'solr', 'tdd', 'agile', 'rabbitmq', 'grunt', 'gulp', 'd3', 'iaas', 'computer vision',
        'sinatra', 'kernel', 'virtual machine', 'engineer', 'sysadmin', 'vlan', 'firewall', 'backup', 'high availability', 'virtualisation', 'virtualization',
        'saltstack', 'c++', 'front end', 'beanstalk', 'beanstalkd', 'happiness engineer', 'backend engineer', 'frontend engineer',
        'infrastructure', 'senior software engineer', 'network', 'storage', 'success engineer', 'accountant', 'technical editor',
        ' QA ', ' UX ', 'CRM', 'project management', 
    ];


    public function __construct()
	{
		$this->company = new \GoRemote\Model\CompanyModel();
	}

	public function insert(Connection $db)
	{

	    $this->position = strip_tags($this->position);

		$jobDuplicate = $db->fetchColumn(
			'select jobid from jobs where (dateadded=? and sourceid=? and applyurl=?) or (position=? and companyid=? and dateadded > NOW() - INTERVAL 1 MONTH)',
			[
				(string) $this->dateadded,
				(int) $this->sourceid,
				(string) trim($this->applyurl),
				(string) trim($this->position),
				(int) $this->company->id
			]);

		if ($jobDuplicate) {
			Throw new \Exception('Duplicate job: ' . $jobDuplicate);
		}

		$this->description = $string = preg_replace('/(<br\/>){2,}/','<br/>', html_entity_decode(trim(strip_tags(str_replace(
			['<div>', '</div>', '<br />', "\n\n"],
			['', "<br/>", "<br/>", "<br/>"], $this->description), '<p><b><strong><ul><li><br><br/><br />'))));

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
		$tweetMessage = "{companyname} are looking for {indefinitearticle} {position} {link} - work from anywhere! #remote #jobs";
		$companyname = (!empty($this->company->twitter)) ? $this->company->twitter : trim($this->company->name);
		if (empty(trim($companyname))) {
			return true;
		}

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

    public function extractBuzzwords($text)
    {
        $text = strtolower($text);
        $matches = [];

        // Not pretty, but faster than preg_match_all
        foreach ($this->buzzwords as $buzzword) {
            if (strpos($text, $buzzword) !== false) {
                $matches[] = trim($buzzword);
            }
        }

        return array_unique($matches);
    }

	public function getLatestJobs(\GoRemote\Application $app, $interval=self::DEFAULT_SEARCH_INTERVAL)
	{
	    $jobs = [];
		$jobsFromDb = $app['db']->fetchAll('select jobs.*, unix_timestamp(jobs.dateadded) as dateadded_unixtime, companies.name as companyname, companies.twitter as companytwitter, companies.url as companyurl, sources.name as sourcename, sources.url as sourceurl from jobs inner join companies using(companyid) inner join sources using(sourceid) where jobs.dateadded > UTC_TIMESTAMP() - INTERVAL ? SECOND and jobs.position <> "" and jobs.datedeleted=0 order by jobs.dateadded desc limit 170',
			[
				$interval
			]);

        foreach ($jobsFromDb as $job) {
            $job['tags'] = $this->extractBuzzwords($job['position'] . ' ' . $job['description']);
            $jobs[] = $job;
        }

        return $jobs;
	}
}
