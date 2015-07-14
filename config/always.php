<?php
return array (
    'config.debug' => true,
    'config.enableProfiler' => false,
    'config.monologConsts' =>
        array (
            'DEBUG' => 100,
            'INFO' => 200,
            'NOTICE' => 250,
            'WARNING' => 300,
            'ERROR' => 400,
            'CRITICAL' => 500,
            'ALERT' => 550,
            'EMERGENCY' => 600,
        ),
    'config.monolog' =>
        array (
            'minLogLevel' => 400,
            'minAdminNotifyLevel' => 500,
        ),
    'config.monolog.logfile' => '/tmp/myproject_monolog.log',
    'swiftmailer.options' => 
	array (
	    'host'       => 'smtp.mandrillapp.com',
	    'port'       => 587,
	    'username'   => 'smelly@skeleton.com',
	    'password'   => 'smelly-skeleton',
	),
);
