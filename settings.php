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
			'domain' => 'alphareign.se',
			'url' => 'https://alphareign.se',
			'version' => '0.0.8',
			'contact' => 'alphareign@protonmail.com'
		]
	]
];

return $settings;

?>