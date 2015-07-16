<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomepageController
{
    public function indexAction(Request $request, Application $app)
    {
    	$latestJobs = $app['db']->fetchAll('select jobs.*, unix_timestamp(jobs.dateadded) as dateadded_unixtime, companies.name as companyname, companies.url as companyurl, sources.name as sourcename, sources.url as sourceurl from jobs inner join companies using(companyid) inner join sources using(sourceid) where jobs.dateadded > UTC_TIMESTAMP() - INTERVAL 1 MONTH and jobs.datedeleted=0 order by jobs.dateadded desc limit 70');
        $render = $app['twig']->render('index.html.twig', [ 'latestJobs' => $latestJobs ]);

        return $render;
    }
}
