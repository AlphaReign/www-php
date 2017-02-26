<?php

//This middleware loads all the GET and POST variables on each request so that we can access them from with the Slim App anywhere
$app->add(function ($request, $response, $next) {

	timer('starting params');
    $this->params->load($request);
    $this->view->params = $this->params->all();
    return $next($request, $response);
});