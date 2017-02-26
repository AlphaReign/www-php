<?php

if(!isset($this->user) || !$this->user->isAdmin){
	exit();
}

$doc = [];
$doc['index'] = 'torrents';
$doc['type'] = 'hash';
$doc['size'] = 500;
$doc['from'] = 0;
$doc['body'] = [];
$doc['body']['query'] = [
	'range' => [
		'created' => [
			'gte' => 147861827316
		]
	]
];

$results = $this->client->search($doc);
$torrents = $results['hits']['hits'];

echo count($torrents) . "\n<br>";

foreach($torrents as $torrent){
	$this->client->delete([
		'index' => $torrent['_index'],
		'type' => $torrent['_type'],
		'id' => $torrent['_id']
	]);
}

echo "Done\n<br>";

// if($this->params->key != '2346346723451'){
// 	exit();
// }

// $user = R::findOne('user', 'username = ?', ['admin']);
// if(!is_object($user)){
// 	$user = R::dispense('user');
// 	$user->username = 'admin';
// 	$user->hash = password_hash(time(), PASSWORD_BCRYPT);
// 	$user->created = time();
// 	$user->updated = time();
// }

// $user->invite = uniqid();
// R::store($user);

// echo $user->invite;
// exit();

?>