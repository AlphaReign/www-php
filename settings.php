<?php

$settings = [
	'settings' => [
		'displayErrorDetails' => false,
		'renderer' => [
			'template_path' => ROOT . '/views/',
		],
		'site' => [
			'name' => 'AlphaReign',
			'short' => 'AR',
			'lead' => 'A Private Torrent Search Engine',
			'domain' => 'example.com',
			'url' => 'https://example.com',
			'version' => '0.0.8',
			'contact' => 'contact@example.com'
		],
		'elasticsearch' => [
			'hosts' => ['127.0.0.1:9200']
		]
	]
];

return $settings;

?>
