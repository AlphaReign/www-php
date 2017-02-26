<?php

$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';

if(!isset($this->user) || !is_object($this->user)){
	return $this->view->redirect($response, $redirect);
	exit();
}

if($this->params->comment != ''){
	$text = strip_tags($this->params->comment);
	$text = strlen($text) > 1000 ? substr($text, 0, 1000) : $text;
	$text = str_replace("\n\r", "\n", $text);
	$text = str_replace("\r\n", "\n", $text);
	$text = str_replace("\r", "\n", $text);
	$pos = strpos($text, "\n\n");
	while($pos > 0){
		$text = str_replace("\n\n", "\n", $text);
		$pos = strpos($text, "\n\n");
	};
	$text = trim($text);
	$comment = R::dispense('comment');
	$comment->infohash = $args['infohash'];
	$comment->created = time();
	$comment->comment = $text;
	$this->user->ownComments[] = $comment;
	R::store($this->user);
	R::store($comment);
	$response = $this->cookies->flash($response, 'success', 'Thanks for leaving a comment!');
}

$comments = R::count('comment', 'infohash = ?', [$args['infohash']]);

$params = [
		'index' => 'torrents',
		'type' => 'hash',
		'id' => $args['infohash'],
		'body' => [
		'doc' => [
			'comments' => $comments
		]
	]
];

try{
	$result = $this->client->update($params);
}catch(Exception $error){

}

return $this->view->redirect($response, $redirect);

?>