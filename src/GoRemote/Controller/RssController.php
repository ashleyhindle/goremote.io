<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RssController
{
    public function mainAction(Application $app)
    {
    	$latestJobs = $app['db']->fetchAll('select jobs.*, unix_timestamp(jobs.dateadded) as dateadded_unixtime, companies.name as companyname, companies.url as companyurl, sources.name as sourcename, sources.url as sourceurl from jobs inner join companies using(companyid) inner join sources using(sourceid) where jobs.dateadded > UTC_TIMESTAMP() - INTERVAL 1 MONTH and jobs.datedeleted=0 order by jobs.dateadded desc limit 70');
    	$rss = <<<RSS
<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" version="2.0">
  <channel>
    <title>All Recent Jobs</title>
    <link>https://goremote.io/rss</link>
    <description>Recent jobs aggregated on GoRemote.io</description>
    <language>en-US</language>
    <ttl>180</ttl>
RSS;
		// TODO Create a class to get latestjobs so we don't duplicate the query above
		// TODO Use twig for RSS as view is in the controller :(
		foreach ($latestJobs as $job) {
			$rss .= <<<RSS
			<item>
				<title>{$job['position']}</title>
				<description><![CDATA[<html><body>{$job['description']}</body></html>]]></description>
				<pubDate>{$job['dateadded']}</description>
				<guid>https://goremote.io/job/{$job['jobid']}</guid>
				<link>https://goremote.io/job/{$job['jobid']}</link>
				<companyName>{$job['companyname']}</companyName>
				<sourceName>{$job['sourcename']}</sourceName>
			</item>
RSS;
		}

		$rss .= <<<RSS
  </channel>
</rss>
RSS;

		return new Response($rss, 200, array('Content-Type' => 'application/xml'));
    }
}
