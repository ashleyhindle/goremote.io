<?php
namespace GoRemote\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JobController
{
    public function idAction(Request $request, Application $app)
    {
        $render = $app['twig']->render('job.html.twig', [ 'jobid' => $request->get('id') ]);
        return $render;
    }

}
