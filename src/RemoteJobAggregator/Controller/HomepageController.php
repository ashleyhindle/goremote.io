<?php
namespace RemoteJobAggregator\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomepageController
{
    public function indexAction(Request $request, Application $app)
    {
        $render = $app['twig']->render('index.html.twig', [ ]);

        return $render;
    }

}
