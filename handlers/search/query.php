<?php

$query = trim($this->params->query);

$doc = [];
$doc['index'] = 'torrents';
$doc['type'] = 'hash';
$doc['size'] = $limit;
$doc['from'] = $from;
$doc['body'] = [];

if($query != ''){
	$this->view->query = $query;
	$this->view->title = 'Results for "' . $query . '"';
	$queryDoc = [
		'function_score' => [
			'query' => [
				'bool' => [
					'must' => [
						'match' => [
							'search' => [
								'query' => $query,
								'operator' => 'and'
							]
						]
					],
					'must_not' => [[
						'term' => [
							'inactive' => true
						]], [
						'range' => [
							'flags' => [
								'gte' => 5
							]
						]
					]]
				]
			],
			'field_value_factor' => [
				'field' => 'seeders',
				'modifier' => 'sqrt',
				'factor' => .1,
				'missing' => 0
			],
			'boost_mode' => 'sum',
			'max_boost' => 10
		]
	];
}else{
	$this->view->title = 'Top Torrents';
	$queryDoc = [
		'bool' => [
			'must_not' => [[
				'term' => [
					'inactive' => true
				]], [
				'range' => [
					'flags' => [
						'gte' => 5
					]
				]
			]]
		]
	];
	$sort = [
		'seeders' => [
			'order' => 'desc',
			'missing' => '_last'
		]
	];
}


$filters = [];

if($this->params->type != ''){
	$filters = ['term' => ['type' => $this->params->type]];
}
if($this->params->category != ''){
	$filters = ['term' => ['categories' => $this->params->category]];
}
if($this->params->categories != ''){
	$categories = explode(',', $this->params->categories);
	$filters = ['terms' => ['categories' => $categories]];
}
if($this->params->tag != ''){
	$filters = ['term' => ['tags' => $this->params->tag]];
}

if(count($filters) > 0){
	if(isset($queryDoc['function_score'])){
		$queryDoc['function_score']['query']['bool']['filter'] = $filters;;
	}else{
		$queryDoc['bool']['filter'] = $filters;
	}
}

if($this->params->sort != '' && in_array($this->params->sort, ['seeders', 'leechers', 'name', 'created'])){
	$sort = [
		$this->params->sort => [
			'order' => 'desc',
			'missing' => '_last'
		]
	];
}
else {
	$sort = [
		'seeders' => [
			'order' => 'desc',
			'missing' => '_last'
		]
	];	
}

if(isset($sort)){
	$doc['body']['sort'] = $sort;
}
if(isset($queryDoc)){
	$doc['body']['query'] = $queryDoc;
}

$results = $this->client->search($doc);
$torrents = $results['hits']['hits'];
try{
	$this->view->total = number_format($results['hits']['total']);
}
catch(Exception $error){}


?>
