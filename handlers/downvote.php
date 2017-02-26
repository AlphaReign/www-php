<?php

$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';

if(!isset($this->user) || !is_object($this->user)){
	return $this->view->redirect($response, $redirect);
	exit();
}

$downvote = R::findOne('downvote', 'infohash = ? and user_id = ?', [$args['infohash'], $this->user->id]);
if(!is_object($downvote)){
	$downvote = R::dispense('downvote');
	$downvote->infohash = $args['infohash'];
	$this->user->ownDownvotes[] = $downvote;
	R::store($this->user);
	R::store($downvote);
}

$upvote = R::findOne('upvote', 'infohash = ? and user_id = ?', [$args['infohash'], $this->user->id]);
if(is_object($upvote)){
	R::trash($upvote);
}

$upvotes = R::count('upvote', 'infohash = ?', [$args['infohash']]);
$downvotes = R::count('downvote', 'infohash = ?', [$args['infohash']]);

$params = [
		'index' => 'torrents',
		'type' => 'hash',
		'id' => $args['infohash'],
		'body' => [
		'doc' => [
			'upvotes' => $upvotes,
			'downvotes' => $downvotes
		]
	]
];
try{
	$result = $this->client->update($params);
}catch(Exception $error){

}

$response = $this->cookies->flash($response, 'success', 'Thank you for voting!');
return $this->view->redirect($response, $redirect);

?>