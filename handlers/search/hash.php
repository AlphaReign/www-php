<?php

$query = trim($this->params->query);

$this->view->query = $query;
$this->view->title = 'Results for "' . $query . '"';

$hash = strtolower($query);
try{
	$results = $this->client->get([
		'index' => 'torrents',
		'type' => 'hash',
		'id' => $hash
	]);
	$torrents = [$results];
}catch(Exception $error){
	$this->view->flash('info', "Could not find torrent for hash: {$hash}");
}

?>