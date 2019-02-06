<?php

timer('start search');

if(!isset($this->user) || !is_object($this->user)){
	$this->view->redirect($response, '/');
}

$page = $this->params->page == '' || !is_numeric($this->params->page) ? 1 : $this->params->page;

$limit = 25;
$from = ($page - 1) * $limit;
$this->view->next = $page + 1;
$this->view->prev = $page - 1;
$this->view->page = $page;

$query = trim($this->params->query);

$this->view->query = $query;

if($query != '' && ctype_xdigit($query) && strlen($query) == 40){
	include(ROOT . '/handlers/search/hash.php');
}else{
	include(ROOT . '/handlers/search/query.php');
}

timer('made search');

if(isset($torrents) && is_array($torrents) && count($torrents) > 0){
	$results = [];
	foreach($torrents as $torrent){
		$temp = $torrent['_source'];
		$temp['score'] = isset($torrent['_score']) ? $torrent['_score'] : '';
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
		$temp['tags'] = explode(",", $temp["tags"]);
		$results[] = $temp;
	}
	$this->view->results = $results;
}

timer('search end');

return $this->view->render($response, 'search');

?>
