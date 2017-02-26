<?php

if(!isset($this->user) || !is_object($this->user) || !$this->user->isMod){
	return $this->view->redirect($response, '/');
}

$messages = R::find('feedback', 'archived != ? order by created desc', [1]);
$temp = [];
foreach($messages as $message){
	$export = $message->export();
	$export['username'] = $message->user->username;
	$export['message'] = str_replace("\n", '<br>', $export['message']);
	$temp[] = $export;
}

$this->view->messages = $temp;
return $this->view->render($response, 'messages');

?>