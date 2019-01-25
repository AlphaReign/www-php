<?php

$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/';

if(INVITE_ONLY || REQUEST_LOGIN){
	if(!isset($this->user) || !is_object($this->user)){
		return $this->view->redirect($response, $redirect);
		exit();
	}
}

$infohash = $args['infohash'];
$this->view->infohash = $infohash;

try{
	$torrent = $this->client->get(['index' => 'torrents', 'type' => 'hash', 'id' => $infohash]);
}catch(Exception $error){
	return $this->view->redirect($response, $redirect);
	exit();
}

$temp = $torrent['_source'];
if(isset($temp['length'])){
	$temp['size'] = formatBytes($temp['length']);
}
if(isset($temp['files'])){
	$temp['hasFiles'] = true;
	$temp['length'] = 0;
	foreach($temp['files'] as $key=>$file){
		$temp['length'] += $file['length'];
		$temp['files'][$key]['path'] = str_replace(',', '/', $file['path']);
		$temp['files'][$key]['size'] = formatBytes($file['length']);
	}
	$temp['size'] = formatBytes($temp['length']);
}else{
	$temp['files'] = [];
	$temp['files'][]['path'] = $temp['name'];
	if(isset($temp['size'])){
		$temp['files'][]['size'] = $temp['size'];
	}
	$temp['hasFiles'] = true;
}
$temp['created'] = strtotime($temp['created']);
$this->view->torrent = $temp;


$comments = R::findAll('comment', 'infohash = ? order by created asc', [$infohash]);
$temp = [];
foreach($comments as $comment){
	$user = $comment->user;
	$comment->comment = str_replace("\n", '<br>', $comment->comment);
	$export = $comment->export();
	$export['user'] = $user->export();
	$temp[] = $export;
}

$this->view->comments = $temp;
return $this->view->render($response, 'torrent');

?>
