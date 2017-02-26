<?php

//This class allows us to parse the HTML body and access variables from the GET or POST request
//We load this as part of the middleware to ensure the variables will be accessable thoughout the application

namespace AR;

class Params {
    private $data = [];
    private $post;
    private $get;

    function __construct(){
    }

    //These next three magic methods (http://php.net/manual/en/language.oop5.magic.php) are setup so we can set and access variables directly through the class and pass to the variables to the view

    //We can easily set variables into the class to be stored for later
    /*
     * $params = new \AR\Params();
     * $params->variable = 'value';
     *
     */
    public function __set($name, $value){
        $this->data[$name] = $value;
    }

    //We can easily get variables that are stored in this class (which we load from HTTP Request)
    /*
     * $params = new \AR\Params();
     * $params->variable = 'value';
     * echo $parmas->variable; // 'value'
     *
     */
    public function __get($name){
        if(isset($this->data[$name]))
            return $this->data[$name];
        return '';
    }

    //This method is used for Mustache to allow us to access HTTP Request variables in the view
    public function __isset($name){
        if(isset($this->data[$name]))
            return true;
        return false;
    }


    //This is the function that we will use to parse the reqeust.
    //We will be loading this as part of the middleware to ensure that we always have access to the GET and POST variables
    function load($request){
        $this->post = $request->getParsedBody();
        $this->get = $request->getQueryParams();
        if(is_array($this->post)){
            $this->data = array_merge($this->data, $this->post);
        }
        if(is_array($this->get)){
            $this->data = array_merge($this->data, $this->get);
        }
    }

    //If we only want to get the POST variables
    function post(){
        return $this->post;
    }

    //If we only want to get the GET variables
    function get(){
        return $this->get;
    }

    //If we want to get both the GET and POST variables
    function all(){
        return $this->data;
    }
}

?>