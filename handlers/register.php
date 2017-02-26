<?php

$user = [];
$user['username'] = strtolower($this->params->username);
$user['password'] = $this->params->password;
$user['hash'] = password_hash($this->params->password, PASSWORD_BCRYPT);
$user['invitation'] = $this->params->invitation;

if(strlen($user['username']) < 6){
	$this->view->flash('danger', 'Username is not long enough');
	$this->view->register = true;
	$this->view->registerform = $user;
	return $this->view->render($response, 'auth');
}

$foundUser = R::findOne('user', 'username = ?', [$user['username']]);
if(is_object($foundUser)){
	$this->view->flash('danger', 'Username is already taken');
	$this->view->register = true;
	$this->view->registerform = $user;
	return $this->view->render($response, 'auth');
}

if(INVITE_ONLY){
	$inviter = R::findOne('user', 'invite = ?', [$user['invitation']]);
	if(!is_object($inviter)){
		$this->view->flash('danger', 'Invitation not found');
		$this->view->register = true;
		$this->view->registerform = $user;
		return $this->view->render($response, 'auth');
	}

	if($user['invitation'] != '5807f7b36f5cf'){
		$usedInvitation = R::findOne('user', 'invitation = ?', [$user['invitation']]);
		if(is_object($usedInvitation)){
			$this->view->flash('danger', 'Invitation code already used');
			$this->view->register = true;
			$this->view->registerform = $user;
			return $this->view->render($response, 'auth');
		}
	}
}

if(strlen($user['password']) < 6){
	$this->view->flash('danger', 'Please use a stronger password');
	$this->view->register = true;
	$this->view->registerform = $user;
	return $this->view->render($response, 'auth');
}

unset($user['password']);

$newUser = R::dispense('user');
$newUser->import($user);
$newUser->created = time();
$newUser->updated = time();
$newUser->active = 1;
if(INVITE_ONLY){
	$newUser->user_id = $inviter->id; //Invited, also builds hiearchy for RedBean
	if($inviter->username != 'prefinem'){
		$inviter->invite = '';
		R::store($inviter);
	}
}
R::store($newUser);


$this->view->flash('success', 'You have successfully registered, please login');
return $this->view->render($response, 'auth');

?>