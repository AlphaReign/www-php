<?php

$app->add(function ($request, $response, $next) {

	timer('starting logger');

	$log = R::dispense('log');
	$log->method = $request->getMethod();
	$log->path = $request->getUri()->getPath();

	$get = $this->params->get();
	if(isset($get['password'])){
		$get['password'] = '*****';
	}
	$post = $this->params->post();
	if(isset($post['password'])){
		$post['password'] = '*****';
	}

	$log->get = json_encode($get);
	$log->post = json_encode($post);

	$log->ua = $_SERVER['HTTP_USER_AGENT'];
	if(isset($_SERVER['HTTP_X_REAL_IP'])){
		$log->uid = hash('sha512', $_SERVER['HTTP_X_REAL_IP']);
		// $log->ip = $_SERVER['HTTP_X_REAL_IP'];
	}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
		$log->uid = hash('sha512', $_SERVER['HTTP_X_FORWARDED_FOR']);
		// $log->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	$log->created = time();

	if(isset($this->user) && is_object($this->user)){
		$log->user_id = $this->user->id;
	}

	R::store($log);

	timer('ending logger');

	return $next($request, $response);
});
