<?php

if(!isset($this->user) || !is_object($this->user)){
	$this->view->redirect($response, '/');
}

$this->params->query = $this->params->q;
$limit = $this->params->limit == '' || !is_numeric($this->params->limit) ? 25 : $this->params->limit;
$from = $this->params->offset == '' || !is_numeric($this->params->offset) ? 0 : $this->params->offset;
// $this->params->categories = $this->params->cat;
$output = strtolower($this->params->o) == 'json' ? 'JSON' : 'XML';

if($this->params->t == 'caps'){
	if($output == 'JSON'){
		return $this->view->json($response);
	}else{
		$response = $response->withHeader('Content-Type', 'text/xml');
		return $this->view->render($response, 'capabilities');
	}
}

if($this->params->t == 'tvsearch'){
	$this->params->category = 'show';
}

if($this->params->categories != ''){
	$categories = explode(',', $this->params->categories);
	$temp = [];
	foreach($categories as $category){
		if($category >= 2000 && $category < 3000){
			$temp[] = 'movie';
		}elseif($category >= 5000 && $category < 6000){
			$temp[] = 'show';
		}elseif($category >= 6000 && $category < 7000){
			$this->params->tag = 'XXX';
		}else{
			$temp[] = $category;
		}
	}
	$temp = array_unique($temp);
	$this->params->categories == join($temp);
}

if($this->params->query == '' && $this->params->rid != ''){
	try{
		$results = $this->client->get([
			'index' => 'show',
			'type' => 'rid',
			'id' => $this->params->rid
		]);
		$show = $results['_source'];
	}catch(Exception $error){
		try{
			$show = json_decode(file_get_contents('http://api.tvmaze.com/lookup/shows?tvrage='.$this->params->rid), true);
			$results = $this->client->index([
				'index' => 'show',
				'type' => 'rid',
				'id' => $this->params->rid,
				'body' => $show
			]);
		}catch(Exception $error){}
	}
	$temp = $show['name'];
	$this->params->query = $temp;
}

if($this->params->season != ''){
	$temp = ' S' . str_pad($this->params->season, 2, '0', STR_PAD_LEFT);
	if($this->params->ep != ''){
		$temp .= 'E' . str_pad($this->params->ep, 2, '0', STR_PAD_LEFT);
	}
	$this->params->query = $this->params->query . $temp;
}

include(ROOT . '/handlers/search/query.php');

$results = [];
if(isset($torrents) && is_array($torrents) && count($torrents) > 0){
	foreach($torrents as $torrentRecord){
		$torrent = $torrentRecord['_source'];
		$torrent['score'] = isset($torrentRecord['_score']) ? $torrentRecord['_score'] : '';
		if(isset($torrent['length'])){
			$torrent['size'] = formatBytes($torrent['length']);
		}
		if(isset($torrent['files'])){
			$torrent['hasFiles'] = true;
			$torrent['length'] = 0;
			foreach($torrent['files'] as $key=>$file){
				$torrent['length'] += $file['length'];
				$torrent['files'][$key]['path'] = str_replace(',', '/', $file['path']);
				$torrent['files'][$key]['size'] = formatBytes($file['length']);
			}
			$torrent['size'] = formatBytes($torrent['length']);
		}else{
			$torrent['files'] = [];
			$torrent['files'][]['path'] = $torrent['name'];
			if(isset($torrent['size'])){
				$torrent['files'][]['size'] = $torrent['size'];
			}
			$torrent['hasFiles'] = true;
		}

		if(!isset($torrent['length'])){
			$torrent['length'] = 0;
		}
		if(isset($torrent['created'])){
			$torrent['date'] = date("D, j M Y G:i:s", $torrent['created']);
		}else{
			$torrent['date'] = date("D, j M Y G:i:s", time());
		}
		if(isset($torrent['seeders']) && isset($torrent['leechers'])){
			$torrent['peers'] = $torrent['seeders'] + $torrent['leechers'];
		}
		if(isset($torrent['files'])){
			$torrent['files'] = count($torrent['files']);
		}

		$torrent['magnet'] = 'magnet:?xt=urn:btih:' . $torrent['infohash'] . '&dn=' . $torrent['name'];
		// if($this->user->trackers == 1){
		// 	$trackers = urldecode($this->view->trackerLink);
		// 	$trackers = str_replace('&amp;', '&', $trackers);
		// 	$torrent['magnet'] .= $trackers;
		// }

		unset($torrent['flags']);
		unset($torrent['score']);
		unset($torrent['hasFiles']);
		unset($torrent['dht']);

		$results[] = $torrent;
	}
}

if($output == 'JSON'){
	return $this->view->json($response, $results);
}else{
	$this->view->results = $results;
	$response = $response->withHeader('Content-Type', 'text/xml');
	return $this->view->render($response, 'rss');
}

?>