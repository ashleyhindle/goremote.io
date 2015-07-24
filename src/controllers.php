<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


$app->get('/', 'GoRemote\Controller\HomepageController::indexAction')
    ->bind('homepage');

$app->get('/twitter/auth', 'GoRemote\Controller\TwitterController::authAction')
	->bind('twitter-auth');

$app->get('/add', 'GoRemote\Controller\JobController::addAction')
    ->bind('add');

$app->get('/rss', 'GoRemote\Controller\RssController::mainAction')
	->bind('rss');

$app->get('/api/companies', 'GoRemote\Controller\ApiController::companiesAction')
	->bind('api-companies');

$app->get('/api/company/{companyid}', 'GoRemote\Controller\ApiController::companyAction')
	->bind('api-company-by-id');

$app->get('/api/jobs', 'GoRemote\Controller\ApiController::jobsAction')
	->bind('api-jobs');

$app->get('/api/job/{id}', 'GoRemote\Controller\ApiController::jobAction')
	->bind('api-job-by-id');

$app->get('/api/sources', 'GoRemote\Controller\ApiController::sourcesAction')
	->bind('api-sources');

$app->get('/api/source/{id}', 'GoRemote\Controller\ApiController::sourceAction')
	->bind('api-source-by-id');

$app->get('/api/search/{query}', 'GoRemote\Controller\ApiController::searchAction')
	->bind('api-search')
	->assert('query', '.+');

$app->get('/alljobs.rss', 'GoRemote\Controller\RssController::mainAction')
	->bind('alljobs.rss');

$app->get('/search/{query}', 'GoRemote\Controller\JobController::searchAction')
    ->bind('search')
    ->assert('query', '.+');

$app->get('/job/{id}/', 'GoRemote\Controller\JobController::idAction')
    ->bind('job-by-id');

$app->get('/job/{id}/{title}', 'GoRemote\Controller\JobController::idAction')
    ->bind('job-by-id-title')
    ->assert('title', '.+');