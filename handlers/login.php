<?php

$username = strtolower($this->params->username);
$password = $this->params->password;
$this->view->loginform = ['username' => $username, 'password' => $password];

$user = R::findOne('user', 'username = ?', [$username]);
if(!is_object($user)){
	$this->view->flash('danger', 'Username not found');
	return $this->view->render($response, 'auth');
}

$valid = password_verify($password, $user->hash);
if(!$valid){
	$this->view->flash('danger', 'Incorrect password');
	return $this->view->render($response, 'auth');
}

$user->updated = time();
$user->active = 1;
R::store($user);

use Lcobucci\JWT\Builder;

$token = (new Builder())
	->setIssuer($this->config['site']['url'])
	->setAudience($this->config['site']['url'])
	->setId('Jyx9zK5XhR', true)
	->setIssuedAt(time())
	->setExpiration(time() + 60 * 60 * 48)
	->set('id', $user->id)
	->getToken();

// $response = $this->cookies->flash($response, 'success', 'You have successfully logged in');
$response = $this->cookies->set($response, 'token', $token, '2 days');
return $this->view->redirect($response, '/');

?>