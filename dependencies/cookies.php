<?php

//This class allows us to set cookies according to the PSR-7 standard
//We are using the FigCookies PSR-7 library found here: https://github.com/dflydev/dflydev-fig-cookies
//We also provide helper methods to allow us to set flash messages which was we used in Slim 2
//Flash messages are messages that we want to display to the user.  They can be rendered in the same request or saved in a cookie for the next request

namespace AR;

class Cookies {
    private $flash = [];
    private $cookies = [];

    function __construct($settings = []){
        $this->settings = $settings;
    }

    //Basic method for setting cookies. We could possibly set defaults for the time so that is one less thing to try and remember
    function set($response, $name, $value, $time){
        $date = date("D, d-M-Y H:i:s T", strtotime($time));
        $seconds = strtotime($time) - time();

        $this->cookies[] = ['name'=>$name,'value'=>$value,'expire'=>$seconds];

        $response = \Dflydev\FigCookies\FigResponseCookies::set($response, \Dflydev\FigCookies\SetCookie::create($name)
            ->withValue($value)
            ->withExpires($time)
            ->withDomain($this->settings['domain'])
            ->withPath('/')
        );

        return $response;
    }

    //Basic method for getting the cookie value from the request.  We default to '' so that there won't be isset issues in the application
    function get($request, $name){
        $cookie = \Dflydev\FigCookies\FigRequestCookies::get($request, $name, '');
        return $cookie->getValue();
    }

    //Deleting cookies requires you to expire the cookie.  We also set the value to '' to ensure that if the browser doesn't heed our expiration request, that value won't still be there.
    function delete($response, $name){
        $date = date("D, d-M-Y H:i:s T");

        $this->cookies[] = ['name'=>$name,'value'=>'','expire'=>0];

        $response = \Dflydev\FigCookies\FigResponseCookies::set($response, \Dflydev\FigCookies\SetCookie::create($name)
            ->withExpires($date)
            ->withValue('')
            ->withDomain($this->settings['domain'])
            ->withPath('/')
        );

        return $response;

    }

    //To create persistant flash variables that last for one request, we need to set in a cookie
    //A future enhancement would be to place these in the session so that we don't have to set a cookie everytime we create a flash message
    //The session would also be redundant to connection issues since the session is already set at the top level of the app in /index.php
    //We json encode the flash variables since cookies only accept strings
    function flash($response, $type, $text){
        $this->flash[] = ['type'=>$type, 'text'=>$text];
        $flash = json_encode($this->flash);
        return $this->set($response, 'flash', $flash, '1 day');
    }

    //This is a helper method to ensure we don't have to remember how or where we stored the flash cookie
    //We json encode the flash variables since cookies only accept strings.  This means we must decode the cookie to be able to interpret the data
    function getFlash($request){
        $flash = $this->get($request, 'flash');
        $flash = json_decode($flash, true);
        return $flash;
    }

    //We store an array of all the cookies that we set or delete so that we can access them easily in the application if we need too
    //This returns all the cookies we have set or deleted
    function all(){
        return $this->cookies;
    }
}

?>