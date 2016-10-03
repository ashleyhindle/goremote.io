<?php
namespace GoRemote\Model;

use GoRemote\Model\JobModel;

class HackerNewsModel implements \GoRemote\Model\SourceInterface
{
	const SOURCE_URL = 'https://hacker-news.firebaseio.com/v0/item/{ITEM}.json?print=pretty';
	const SOURCE_NAME = 'hackernews';
	const SOURCE_ID = 10;
	private $processedItems = [];
	private $db;

	public function __construct(\Doctrine\DBAL\Connection $db)
	{
		$this->db = $db;
		$processedItems = $this->db->fetchAll('select itemid from hackernews');
		foreach ($processedItems as $item) {
			$this->processedItems[] = $item['itemid'];
		}
	}

	private function getFirebaseItem($item)
	{
		return json_decode(
			file_get_contents(
				str_replace(
					'{ITEM}', $item, static::SOURCE_URL
				)
			), 
			true
		);
	}

	private function getValidSeparators(array $chars, array $separators)
	{
		$valid = false;
		foreach($separators as $sep) {
			if(array_key_exists(ord($sep), $chars) && $chars[ord($sep)] >= 2) {
				$valid = $sep;
				break;
			}
		}

		return $valid;
	}

	private $buzzwords = [
		'javascript', 'node', 'php', 'ruby', 'rails', 'clojure', 'java', 'html', 'css', 'frontend', 'front-end',
		'devops', 'backend', 'back-end', 'angular', 'chef', 'vagrant', 'docker', 'salt', 'haddop', 'cassandra', 'mysql', 
		'postgresql', 'postgres', 'mobile', 'machine learning', 'nlp', 'react', 'operations', 'software engineer', 'support', 'manager', 'symfony',
		'laravel', 'qa', 'full-stack', 'full stack', 'rest ', 'api', 'senior', 'intern', 'ansible', 'consul', 'nginx',
		'coffeescript', 'backbone', 'knockout', 'haml', 'tdd', 'aws', 'python', 'flask', 'spring', 'sql', 'tomcat', 'designer',
		'cloud', 'scala', 'haskell', 'android', 'ios', 'swift', 'objective c', 'flux', 'redis', 'elasticache', 'elasticsearch',
		'browserify', 'git', 'nlp', 'machine learning', 'product manager', 'project manager', 'objective-c', 'ux ', 'ui ',
		'opencv', 'django', 'celery', 'erlang', 'amazon web services', 'linux', 'PCI', 'redshift', 'customer success', 'customer support',
		'jenkins', 'perl', 'golang', 'go ', 'paas', 'elastic search', 'game', 'unity', 'bgp', 'dns', 'scala', 'neo4j', 'c#', 'asp.net', '.net', 'marionette', 'mssql',
		'vpn', 'nosql', 'opengl', 'opencl', 'cuda', 'gpu', 'crypto', 'heroku', 'erlang', 'electron', 'mongo', 'dev-ops', 'dev ops',
		'phonegap', 'jenkins', 'saas', 'paas', 'security', 'analytics', 'physics', 'dba', 'distributed', 'containers', 'junior',
		'big data', 'data science', 'sales', 'cordova', 'multiple positions', 'haproxy', 'cdn', 'sass', 'zookeeper',
		'xml', 'json', 'system admin', 'zeromq', 'kafka', 'ec2', 'route53', 'aurora', 'es6', 'cloudfront', 'babel',
		'npm', 'mocha', 'marketing', ' qt', 'solr', 'tdd', 'agile', 'rabbitmq', 'grunt', 'gulp', 'd3', 'iaas', 'computer vision',
		'sinatra', 'kernel', 'virtual machine', 'engineer', 'sysadmin', 'vlan', 'firewall', 'backup', 'high availability', 'virtualisation', 'virtualization',
		'saltstack', 'c++'
	];

	private function extractBuzzwords($text)
	{
		$text = strtolower($text);
		$regexp = '/' . implode('|', array_map('preg_quote', $this->buzzwords)) . '/i';
		$count = preg_match_all($regexp, $text, $matches);

		return ($count > 0) ? array_map('ucwords', 
								array_map('trim', 
									array_unique(
										$matches[0]
									)
								)
							)
							: [];
	}

	public function getJobs()
	{
		$jobs = [];
		$json = $this->getJobsJson();
		$tz = new \DateTimeZone('Europe/London');
		$kids = $json['kids'];
		$regex = "/(?<!no)(?<!not) ?remote/i";

		foreach($kids as $item) {
			if (in_array($item, $this->processedItems)) {
				echo '*';
				continue; // Already processed it
			}

			$kid = $this->getFirebaseItem($item);
			$jobClass = new JobModel();

			if (array_key_exists('text', $kid) === false) {
				$this->markProcessed($item);
				continue;
			}

			$kid['text'] = str_replace('<p>', "\n", $kid['text']);
			$kid['firstline'] = implode("\n", array_slice(explode("\n", $kid['text']), 0, 1));
			$chars = count_chars($kid['firstline'], 1);
			$separators = ['|', '-', '•'];
			$separator = $this->getValidSeparators($chars, $separators);
			
			if(preg_match($regex, $kid['firstline']) === 0) {
				echo '.';
				$this->markProcessed($item);
				continue;
			} elseif(!$separator) {
				// For now we only care about the ones with nice formatting with separators (| or -)
				// Let's Encrypt | Full Time | Remote
				// Trail | London | Full Time, Remote
				// Joyent, San Francisco / Vancouver | ONSITE or REMOTE | Software engineer
				// this _obviously_ limits us, but every comment is different so this is pretty hard
				// Maybe I need NLP?
				echo '-';
				$this->markProcessed($item);
				continue;
			}

			echo '#_#';

			$buzzwords = $this->extractBuzzwords($kid['text']);
			$jobClass->position = (!empty($buzzwords)) 
				? implode(', ', $buzzwords)
				: $kid['firstline'];

			$jobClass->applyurl = 'https://news.ycombinator.com/item?id=' . $item;
			$jobClass->dateadded = (string) (new \DateTime())->setTimestamp($kid['time'])->setTimezone($tz)->format('Y-m-d H:i:s');
			$jobClass->description = $kid['text'];
			$jobClass->sourceid = self::SOURCE_ID;
			
			$jobClass->company->name = $this->getCompanyName($kid['firstline'], $separator);
			$jobClass->company->twitter = '';
			$jobClass->company->logo = '';

			$jobs[] = $jobClass;

			$this->markProcessed($item);

			if (count($jobs) > 2) {
				break;
			}
		}

		return $jobs;
	}

	private function markProcessed($itemid)
	{
		return $this->db->insert('hackernews',
				[
					'itemid' => $itemid
				]
			);
	}

	private function getCompanyName($text, $separator)
	{
		$name = '';
		$chars = count_chars($text, 1);
		if (array_key_exists(ord($separator), $chars) && $chars[ord($separator)] >= 2) {
			$name = html_entity_decode(trim(current(explode($separator, $text))));
		}

		return strip_tags($name);
	}

	protected function getJobsJson()
	{
		return $this->getFirebaseItem(12627852); // 12627852 is October 2016
	}
}
