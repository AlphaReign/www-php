<?php

$start = microtime(true);
$last = microtime(true);
$timerLogs = "\n";

function timer($name){
	global $start;
	global $last;
	global $timerLogs;
	$total = round(microtime(true) - $start, 5);
	$current = round(microtime(true) - $last, 4);
	$last = microtime(true);
	$timerLogs .= "{$total} | {$current} : {$name}\n";
}

timer('start');

session_start();

define('ROOT', __DIR__);
define('INVITE_ONLY', false);
define('REQUEST_LOGIN', true);

define('DBNAME', 'databasename');
define('DBUSER', 'databaseuser');
define('DBPASS', 'databasepass');

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/lib/rb.php';

timer('autoload done');

R::setup('mysql:host=localhost;dbname='.DBNAME, DBUSER, DBPASS);

timer('db connection init done');

foreach(glob(__DIR__ . '/dependencies/*.php') as $dependencies){ require_once($dependencies); }

$settings = require __DIR__ . '/settings.php';
$app = new \Slim\App($settings);
$app->getContainer()['config'] = $settings['settings'];

$container = $app->getContainer();

$container['view'] = function ($c) {
	$settings = $c->get('settings')['renderer'];
	$data['site'] = $c->get('settings')['site'];
	$data['ems'] = $c->get('settings')['ems'];
	return new \AR\Renderer($settings['template_path'], $data);
};

$container['cookies'] = function ($c) {
	$settings = $c->get('settings')['ems'];
	return new \AR\Cookies($settings);
};

$container['params'] = function ($c) {
	return new \AR\Params();
};

$container['csrf'] = function ($c) {
	return new \Slim\Csrf\Guard;
};

if (!$settings['settings']['displayErrorDetails']) {
	$container['errorHandler'] = function ($c) {
		return function ($request, $response, $exception) use ($c) {
			$error = file_get_contents(ROOT . '/error.html');
			return $c['response']
			->withStatus(500)
			->withHeader('Content-Type', 'text/html')
			->write($error);
		};
	};

	$container['phpErrorHandler'] = function ($c) {
		return function ($request, $response, $exception) use ($c) {
			$error = file_get_contents(ROOT . '/errorPHP.html');
			return $c['response']
			->withStatus(500)
			->withHeader('Content-Type', 'text/html')
			->write($error);
		};
	};

	$c['notFoundHandler'] = function ($c) {
		return function ($request, $response) use ($c) {
			$error = file_get_contents(ROOT . '/404.html');
			return $c['response']
			->withStatus(404)
			->withHeader('Content-Type', 'text/html')
			->write($error);
		};
	};
}

timer('app loaded');

require_once(__DIR__ . '/middleware/logger.php');
require_once(__DIR__ . '/middleware/counts.php');
require_once(__DIR__ . '/middleware/es.php');
require_once(__DIR__ . '/middleware/auth.php');
require_once(__DIR__ . '/middleware/view.php');
require_once(__DIR__ . '/middleware/params.php');
require_once(__DIR__ . '/middleware/cookies.php');
$app->add(new \Slim\Csrf\Guard);

require_once(__DIR__ . '/func.php');

timer('middleware loaded');

$app->get('/api', function($request, $response, $args){ return include(__DIR__ . '/handlers/api.php'); });


$app->get('/torrent/{infohash}', function($request, $response, $args){ return include(__DIR__ . '/handlers/torrent.php'); });
$app->get('/update/{infohash}', function($request, $response, $args){ return include(__DIR__ . '/handlers/update.php'); });
$app->post('/comment/{infohash}', function($request, $response, $args){ return include(__DIR__ . '/handlers/comment.php'); });
$app->get('/upvote/{infohash}', function($request, $response, $args){ return include(__DIR__ . '/handlers/upvote.php'); });
$app->get('/downvote/{infohash}', function($request, $response, $args){ return include(__DIR__ . '/handlers/downvote.php'); });
$app->get('/flag/{infohash}', function($request, $response, $args){ return include(__DIR__ . '/handlers/flag.php'); });

$app->get('/admin', function($request, $response, $args){ return include(__DIR__ . '/handlers/admin.php'); });

$app->get('/message/{id}/dismiss', function($request, $response, $args){ return include(__DIR__ . '/handlers/dismiss.php'); });
$app->get('/messages', function($request, $response, $args){ return include(__DIR__ . '/handlers/messages.php'); });
$app->get('/feedback', function($request, $response, $args){ return include(__DIR__ . '/handlers/feedback.php'); });
$app->post('/feedback', function($request, $response, $args){ return include(__DIR__ . '/handlers/feedback.php'); });

$app->get('/counts', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->get('/apikey', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->get('/generate', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->get('/delete', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->post('/delete', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->get('/theme', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->post('/theme', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->get('/trackers', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->post('/trackers', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->get('/password', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->post('/password', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });
$app->get('/settings', function($request, $response, $args){ return include(__DIR__ . '/handlers/settings.php'); });

$app->get('/vip', function($request, $response, $args){ return include(__DIR__ . '/handlers/vip.php'); });

$app->get('/invite/{invitation}', function($request, $response, $args){ return include(__DIR__ . '/handlers/auth.php'); });
$app->get('/logout', function($request, $response, $args){ return include(__DIR__ . '/handlers/logout.php'); });
$app->get('/login', function($request, $response, $args){ return include(__DIR__ . '/handlers/auth.php'); });
$app->post('/login', function($request, $response, $args){ return include(__DIR__ . '/handlers/login.php'); });
$app->get('/register', function($request, $response, $args){ return include(__DIR__ . '/handlers/auth.php'); });
$app->post('/register', function($request, $response, $args){ return include(__DIR__ . '/handlers/register.php'); });

$app->get('/privacy', function($request, $response, $args){ return include(__DIR__ . '/handlers/privacy.php'); });
$app->get('/about', function($request, $response, $args){ return include(__DIR__ . '/handlers/about.php'); });
$app->get('/contact', function($request, $response, $args){ return include(__DIR__ . '/handlers/contact.php'); });
$app->get('/', function($request, $response, $args){ return include(__DIR__ . '/handlers/index.php'); });

timer('routes added');

$app->run();

R::close();

timer('done');

if(isset($container->user) && is_object($container->user) && $container->user->isAdmin && $container->view->url != '/api'){
	// print_r($_SERVER);
}
if(isset($container->user) && is_object($container->user) && $container->user->isMod && $container->view->url != '/api'){
	echo "<div id='timer'><pre>$timerLogs</pre></div>";
}

?>
