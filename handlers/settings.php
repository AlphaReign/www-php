<?php

if(!isset($this->user) || !is_object($this->user)){
	return $this->view->redirect($response, '/');
}

$this->view->invite_only = INVITE_ONLY;
$this->view->request_login = REQUEST_LOGIN;

$path = $request->getUri()->getPath();
$path = str_replace('/', '', $path);
$this->view->$path = true;

if($path == 'generate'){
	$this->view->settings = true;
	$this->user->invite = uniqid();
	R::store($this->user);

	return $this->view->redirect($response, '/settings');
}

if($path == 'apikey'){
	$this->view->settings = true;
	$this->user->apikey = uniqid();
	R::store($this->user);

	return $this->view->redirect($response, '/settings');
}

if($path == 'password' && $this->params->password != ''){
	if(strlen($this->params->password) < 6){
		$this->view->flash('danger', 'Password is too short');
	}else{
		$this->user->hash = password_hash($this->params->password, PASSWORD_BCRYPT);
		$this->user->updated = time();
		R::store($this->user);
		$this->view->flash('success', 'Password has been updated');
	}
}

if($path == 'theme' && $this->params->invert != ''){
	if($this->user->inverted && $this->user->inverted == 1){
		$this->user->inverted = 0;
	}else{
		$this->user->inverted = 1;
	}
	R::store($this->user);
}

if($path == 'trackers' && $this->params->tracker != ''){
	if($this->user->trackers && $this->user->trackers == 1){
		$this->user->trackers = 0;
	}else{
		$this->user->trackers = 1;
	}
	R::store($this->user);
}

if($path == 'delete' && $this->params->password != ''){
	$valid = password_verify($this->params->password, $user->hash);
	if(!$valid){
		R::trash($this->user);
		$response = $this->cookies->delete($response, 'token');
		return $this->view->redirect($response, '/');
	}
}


if($path == 'counts'){
	include(ROOT . '/handlers/counts.php');
}

$counts = R::getAll('SELECT COUNT(*) AS count FROM `user` WHERE user_id = ' . $this->user->id);
$this->view->invites = number_format($counts[0]['count']);

$this->view->user = $this->user->export();

return $this->view->render($response, 'settings');

?>