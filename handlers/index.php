<?php

if(INVITE_ONLY || REQUEST_LOGIN){
	if(!is_object($this->user)){
		if($request->getUri()->getPath() == '/register'){
			$this->view->register = true;
		}
		if(isset($args['invitation'])){
			$this->view->registerform = $args;
			$this->view->register = true;
		}
		return $this->view->render($response, 'auth');
	}
}

include(ROOT . '/handlers/search.php');

?>