<?php

$redirect = $_SERVER['HTTP_REFERER'];

if(!isset($this->user) || !is_object($this->user)){
	return $this->view->redirect($response, $redirect);
	exit();
}

$flag = R::findOne('flag', 'infohash = ? and user_id = ?', [$args['infohash'], $this->user->id]);
if(!is_object($flag)){
	$flag = R::dispense('flag');
	$flag->infohash = $args['infohash'];
	$this->user->ownFlags[] = $flag;
	R::store($this->user);
	R::store($flag);
}

$flags = R::count('flag', 'infohash = ?', [$args['infohash']]);

$params = [
	'index' => 'torrents',
	'type' => 'hash',
	'id' => $args['infohash'],
	'body' => [
		'doc' => [
			'flags' => $flags
		]
	]
];

if($this->user->isAdmin){
	$params['body']['doc']['inactive'] = true;
}

try{
	$result = $this->client->update($params);
}catch(Exception $error){

}

$response = $this->cookies->flash($response, 'success', 'Thank you for reporting!');
return $this->view->redirect($response, $redirect);

?>