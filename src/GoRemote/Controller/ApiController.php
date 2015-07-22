<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController
{
	public function jobsAction(Application $app)
	{
		$latestJobs = (new \GoRemote\Model\JobModel())->getLatestJobs($app);
        return $app->json($latestJobs);
	}

    public function jobAction(Request $request, Application $app)
    {
    	$jobs = $app['db']->fetchAll('select jobs.*, unix_timestamp(jobs.dateadded) as dateadded_unixtime, companies.name as companyname, companies.url as companyurl, companies.logo as companylogo, sources.name as sourcename, sources.twitter as sourcetwitter, sources.url as sourceurl from jobs inner join companies using(companyid) inner join sources using(sourceid) where jobid=?', 
    		[
    			$request->get('id')
    		]);
        
        return $app->json($jobs[0]);
    }

    public function companiesAction(Request $request, Application $app)
    {
    	$companies = $app['db']->fetchAll('select * from companies');
        return $app->json($companies);
    }

    public function companyAction(Request $request, Application $app)
    {
    	$companies = $app['db']->fetchAll('select * from companies where companyid=?', 
    		[
    			$request->get('companyid')
    		]);
        
        return $app->json($companies);
    }

    public function searchAction(Request $request, Application $app)
    {
        return $app->json(
        		(new \GoRemote\Model\SearchModel())->search($app, $request->get('query'))
        );
    }

    public function sourcesAction(Application $app)
    {
    	$sources = $app['db']->fetchAll('select * from sources');
    	return $app->json($sources);
    }

    public function sourceAction(Request $request, Application $app)
    {
    	$sources = $app['db']->fetchAll('select * from sources where sourceid=?',
    		[
    			$request->get('id')
    		]);
    	return $app->json($sources);
    }
}
