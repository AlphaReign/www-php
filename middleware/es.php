<?php

$app->add(function ($request, $response, $next) {
	timer('connecting to ES');
	try{
		$this->client = Elasticsearch\ClientBuilder::create()->build();
	}catch(Exception $error){
		exit('Upgrading database');
	}

	return $next($request, $response);
});
