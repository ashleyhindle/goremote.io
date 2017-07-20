<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RssController
{
    public function mainAction(Request $request, Application $app)
    {
        $searchQ = $request->get('search', null);
        $searchQ = ($searchQ == 'All') ? null : $searchQ;
    	$latestJobs = (new \GoRemote\Model\SearchModel())->search($app, $searchQ);
        $descriptionExtra = !is_null($searchQ) ? ' matching "' . $request->get('search') .'"' : '';
    	$rss = <<<RSS
<?xml version="1.0" encoding="UTF-8"?>
<rss xmlns:dc="http://purl.org/dc/elements/1.1/" version="2.0">
  <channel>
    <title>All Remote Jobs In One Place{$descriptionExtra}</title>
    <link>https://goremote.io/rss</link>
    <description>Recent jobs aggregated on GoRemote.io {$descriptionExtra}</description>
    <language>en-US</language>
    <ttl>180</ttl>
RSS;
		// TODO Create a class to get latestjobs so we don't duplicate the query above
		// TODO Use twig for RSS as view is in the controller :(
		foreach ($latestJobs as $job) {
			$job['dateadded'] = date('r', strtotime($job['dateadded']));
			$job['position'] = htmlspecialchars($job['position'], ENT_QUOTES, 'UTF-8');
            $job['companyname'] = htmlspecialchars($job['companyname'], ENT_QUOTES, 'UTF-8');
			$image = (!empty($job['companylogo'])) ? "<img src='{$job['companylogo']}'>" : '';
			$rss .= <<<RSS

			<item>
				<title>{$job['position']} @ {$job['companyname']}</title>
				<description>
					<![CDATA[
						{$image}
						{$job['description']}
					]]>
				</description>
				<pubDate>{$job['dateadded']}</pubDate>
				<guid>https://goremote.io/job/{$job['jobid']}</guid>
				<link>https://goremote.io/job/{$job['jobid']}</link>
			</item>
RSS;
		}

		$rss .= <<<RSS
  </channel>
</rss>
RSS;

		return new Response($rss, 200, array('Content-Type' => 'application/xml; charset=utf-8'));
    }
}
