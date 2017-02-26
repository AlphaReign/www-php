<?php

$this->view->invite_only = INVITE_ONLY;
$this->view->request_login = REQUEST_LOGIN;

if($request->getUri()->getPath() == '/register'){
	$this->view->register = true;
}
if(isset($args['invitation'])){
	$this->view->registerform = $args;
	$this->view->register = true;
}
return $this->view->render($response, 'auth');

?>