<?php

if(!isset($this->user) || !is_object($this->user)){
	$this->view->redirect($response, '/');
}

if($this->params->message != ''){
	$feedback = R::dispense('feedback');
	$feedback->message = $this->params->message;
	$feedback->user_id = $this->user->id;
	$feedback->created = time();
	$feedback->archived = 0;
	R::store($feedback);

	$this->view->flash('success', 'Thank you for your feedback');
}

return $this->view->render($response, 'feedback');

?>