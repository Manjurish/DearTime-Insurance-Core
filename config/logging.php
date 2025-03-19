<?php     

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

	  
																			   
						 
																			   
	 
																			 
																		  
																		
	 
	  

    'default' => env('LOG_CHANNEL', 'vapor'),  // Set the default to vapor for Vapor deployments

	  
																			   
				  
																			   
	 
																		  
																	   
																	 
	 
															  
											   
										  
	 
	  

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'slack'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'error',
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
													   
            'with' => [
                'stream' => 'php://stderr',
            ],
            'level' => 'info',  // Ensure logs with info level are captured
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],

        // Vapor channel configuration
        'vapor' => [
            'driver' => 'stack',
            'channels' => ['stderr', 'papertrail'],
            'ignore_exceptions' => false,
        ],
					   
		  
				
							
											   
									 
	  
		  
  
    ],

];

