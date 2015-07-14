<?php
/* NameSpaces */
// ToDo: Change this to your Application Wrapper
use GoRemote\Application;

use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use SilexMemcache\MemcacheExtension;
use Silex\Provider\SessionServiceProvider;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Knp\Provider\ConsoleServiceProvider;
use Silex\Provider\TwigServiceProvider;


/**
 * @var Symfony\Component\HttpFoundation\Session\Session $session
 * @var Silex\Application $app
 */

$app = new Application();
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/always.php'));

$app->register(new TwigServiceProvider(), [
    'twig.path'    => [__DIR__ . '/../views'],
    'twig.options' => [
        'cache' => false
    ],
]);

$app->register(new MonologServiceProvider(), [
    'monolog.logfile' => $app['config.monolog.logfile'],
]);

$app['monolog.name'] = 'goremote';
$app['monolog'] = $app->share($app->extend('monolog', function($monolog) {
    $handlers = $monolog->getHandlers();
    foreach($handlers as $handler) {
        $handler->setFormatter(new \Monolog\Formatter\LineFormatter(null, 'Y-m-d H:i:s', true));
    }
    return $monolog;
}));

$app->before(function (Request $request) use ($app) {
    $app['monolog']->addRecord(\Monolog\Logger::DEBUG, 'Opened page: ' . $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath());
}, Application::EARLY_EVENT);

$app->register(new SessionServiceProvider());

$app->register(
    new ConsoleServiceProvider(),
    array(
        'console.name' => 'GoRemoteCli',
        'console.version' => '1.0.0',
        'console.project_directory' => __DIR__ . "/GoRemote"
    )
);

$app->register(new Silex\Provider\SwiftmailerServiceProvider());
/**
 * This registers $app['memcache'] which is a Memcached instance
 */
$app->register(
    new MemcacheExtension(),
    [
        'memcache.library'    => 'memcached',
        'memcache.server' =>
            [
                [
                    '127.0.0.1',
                    11211
                ]
            ]
    ]
);

$app['session.storage.handler'] = $app->share(function ($app) {
    return new MemcachedSessionHandler($app['memcache']);
});

$app->register(new UrlGeneratorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new DoctrineServiceProvider());

$app["db.options"] = [
    "driver" => "pdo_mysql",
    "host"      => getenv('APP_MYSQL_HOSTNAME') ?: 'localhost',
    "dbname"    => getenv('APP_MYSQL_DATABASE') ?: 'goremote',
    "user"      => getenv('APP_MYSQL_USERNAME') ?: 'goremote',
    "password"  => getenv('APP_MYSQL_PASSWORD')
];

$app->register(new LewisB\PheanstalkServiceProvider\PheanstalkServiceProvider(), array(
    'pheanstalk.server' => '127.0.0.1'
));

if ($app['config.debug']) {
    // enable the debug mode
    $app['debug'] = true;
    if ($app['config.enableProfiler'] == true) {
        $app->register($p = new WebProfilerServiceProvider(), [
            'profiler.cache_dir' => '/tmp/cache/profiler/',
        ]);
        $app->mount('/_profiler', $p);
    }

    // Doing the config stuff seems to remove the config, so let's save and reset it.
}

return $app;
