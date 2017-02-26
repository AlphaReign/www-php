<?php

if(!isset($this->user) || !is_object($this->user) || !$this->user->isAdmin){
	return $this->view->redirect($response, '/');
}

$feedback = R::load('feedback', $args['id']);
$feedback->archived = 1;
R::store($feedback);

return $this->view->redirect($response, '/messages');

?>