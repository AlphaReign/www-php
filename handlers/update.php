<?php

$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';

if(!isset($this->user) || !is_object($this->user) || !$this->user->isAdmin){
	return $this->view->redirect($response, $redirect);
	exit();
}

$flags = R::count('flag', 'infohash = ?', [$args['infohash']]);
$upvotes = R::count('upvote', 'infohash = ?', [$args['infohash']]);
$downvotes = R::count('downvote', 'infohash = ?', [$args['infohash']]);

$params = [
		'index' => 'torrents',
		'type' => 'hash',
		'id' => $args['infohash'],
		'body' => [
		'doc' => [
			'categories_updated' => 0,
			'flags' => $flags,
			'upvotes' => $upvotes,
			'downvotes' => $downvotes
		]
	]
];
try{
	$result = $this->client->update($params);
}catch(Exception $error){}

return $this->view->redirect($response, $redirect);

?>