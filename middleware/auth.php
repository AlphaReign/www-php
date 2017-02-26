<?php

//This middleware preps some view variables to ensure that we always have access to these values in the view
$app->add(function ($request, $response, $next) {
	timer('starting auth');
	$token = $this->cookies->get($request, 'token');

	$this->user = false;

	if($token != ''){
		$token = (new Lcobucci\JWT\Parser())->parse((string) $token);

		$data = new Lcobucci\JWT\ValidationData(); // It will use the current time to validate (iat, nbf and exp)
		$data->setIssuer($this->config['site']['url']);
		$data->setAudience($this->config['site']['url']);
		$data->setId('Jyx9zK5XhR');
		timer('token made');

		$valid = $token->validate($data);
		if($valid){
			try{
				$id = $token->getClaim('id');

				// Let's check the expiration and re-up if invalid
				$exp = $data->get('exp');
				if($exp < time() - 60 * 30){
					$token = (new Lcobucci\JWT\Builder())
						->setIssuer($this->config['site']['url'])
						->setAudience($this->config['site']['url'])
						->setId('Jyx9zK5XhR', true)
						->setIssuedAt(time())
						->setExpiration(time() + 60 * 60 * 48)
						->set('id', $id)
						->getToken();
					$response = $this->cookies->set($response, 'token', $token, '1 day');
					timer('new token set');
				}

				$id = $token->getClaim('id');
				timer('got token claim');
				$this->user = R::load('user', $id);
				timer('user lookup by id');
				$this->view->user = $this->user->export();
				$this->view->affiliateID = str_pad($this->user->id, 5, '0', STR_PAD_LEFT);
				timer('loading user');

			}catch(Exception $error){
				//Issue with token decoding
			}
		}
	}

	if(!isset($this->user) || !is_object($this->user)){
		if($this->params->apikey != ''){
			$user = R::findOne('user', 'apikey = ?', [$this->params->apikey]);
			timer('loading user by api key');
			if(is_object($user)){
				$this->user = $user;
			}
		}
	}

	timer('ending auth');

	return $next($request, $response);
});