<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JobController
{
    public function idAction(Request $request, Application $app)
    {
    	$jobs = $app['db']->fetchAll('select jobs.*, unix_timestamp(jobs.dateadded) as dateadded_unixtime, companies.name as companyname, companies.twitter as companytwitter, companies.url as companyurl, companies.logo as companylogo, sources.name as sourcename, sources.twitter as sourcetwitter, sources.url as sourceurl from jobs inner join companies using(companyid) inner join sources using(sourceid) where jobid=?', 
    		[
    			$request->get('id')
    		]);
        $render = $app['twig']->render('job.html.twig', [ 'job' => $jobs[0] ]);
        return $render;
    }

    public function searchAction(Request $request, Application $app)
    {
    	$render = $app['twig']->render('search.html.twig', 
            [
                'jobs' => (new \GoRemote\Model\SearchModel())->search($app, $request->get('query'))
            ]);
        return $render;
    }

    public function addAction(Application $app)
    {
    	$sources = $app['db']->fetchAll('select * from sources');
    	return $app['twig']->rendeR('add.html.twig', ['sources' => $sources]);
    }
}
