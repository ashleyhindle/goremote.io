<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomepageController
{
    public function indexAction(Request $request, Application $app)
    {
    	$latestJobs = $app['db']->fetchAll('select jobs.*, unix_timestamp(jobs.dateadded) as dateadded_unixtime, companies.name as companyname, companies.url as companyurl, sources.name as sourcename from jobs inner join companies using(companyid) inner join sources using(sourceid) order by dateadded desc limit 30');
        $render = $app['twig']->render('index.html.twig', [ 'latestJobs' => $latestJobs ]);

        return $render;
    }

}
