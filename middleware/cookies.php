<?php

//This middleware loads any persistant flash messages (to abstract the process out into a single location)
$app->add(function ($request, $response, $next) {
	timer('starting cookies');
    $this->view->message = $this->cookies->getFlash($request);
    $response = $this->cookies->delete($response, 'flash');
    $response = $next($request, $response);
    return $response;
});