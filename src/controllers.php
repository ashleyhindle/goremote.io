<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


$app->get('/', 'GoRemote\Controller\HomepageController::indexAction')
    ->bind('homepage');

$app->get('/job/{id}/', 'GoRemote\Controller\JobController::idAction')
    ->bind('job-by-id');

$app->get('/job/{id}/{title}', 'GoRemote\Controller\JobController::idAction')
    ->bind('job-by-id-title');