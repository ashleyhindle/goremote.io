<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomepageController
{
    public function indexAction(Request $request, Application $app)
    {
    	$latestJobs = (new \GoRemote\Model\JobModel())->getLatestJobs($app);
    	$lastWeekJobCount = $app['db']->fetchColumn('select count(1) from jobs where dateadded > UTC_TIMESTAMP() - INTERVAL 1 WEEK');
    	// TODO: These really need to be memcached
        $render = $app['twig']->render('index.html.twig', 
        	[
        		'latestJobs' => $latestJobs,
        		'lastWeekJobCount' => $lastWeekJobCount
        	]);

        return $render;
    }
}
