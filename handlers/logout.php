<?php

$response = $this->cookies->flash($response, 'success', 'You have successfully logged out');
$response = $this->cookies->delete($response, 'token');
return $this->view->redirect($response, '/');

?>