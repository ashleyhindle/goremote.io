<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JobController
{
    public function idAction(Request $request, Application $app)
    {
    	$jobs = $app['db']->fetchAll('select jobs.*, unix_timestamp(jobs.dateadded) as dateadded_unixtime, companies.name as companyname, companies.twitter as companytwitter, companies.url as companyurl, companies.logo as companylogo, sources.name as sourcename, sources.twitter as sourcetwitter, sources.url as sourceurl from jobs inner join companies using(companyid) inner join sources using(sourceid) where jobid=? and jobs.datedeleted="0000-00-00 00:00:00"', 
    		[
    			$request->get('id')
    		]);

	if (empty($jobs)) {
		return $app->redirect('/?invalidJob');
	}

        return $app['twig']->render('job.html.twig', [ 'job' => $jobs[0] ]);
    }

    public function searchAction(Request $request, Application $app)
    {
    	return $app['twig']->render('search.html.twig', 
            [
                'jobs' => (new \GoRemote\Model\SearchModel())->search($app, $request->get('query'))
            ]);
    }

    public function addAction(Application $app)
    {
    	$sources = $app['db']->fetchAll('select * from sources');
    	return $app['twig']->render('add.html.twig', ['sources' => $sources]);
    }
}
