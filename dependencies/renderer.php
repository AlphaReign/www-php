<?php

namespace AR;

class Renderer {
    public static $templateDirectory = null;
    private $data = [];
    private $flash = [];

    public function __construct($templateDirectory, $data = []){
        $this->data = $data;
        $this->templateDirectory = $templateDirectory;
    }

    public function __set($name, $value){
        $this->data[$name] = $value;
    }

    public function __get($name){
        if(isset($this->data[$name]))
            return $this->data[$name];
        return '';
    }

    public function __isset($name){
        if(isset($this->data[$name]))
            return true;
        return false;
    }

    public function flash($type, $text){
        if(!isset($this->data['message'])){
            $this->data['message'] = [];
        }
        $this->data['message'][] = ['type'=>$type, 'text'=>$text];
    }

    public function html($template, $data = []) {
        if(is_array($data)){
            foreach($data as $key=>$value){
                $this->$key = $value;
            }
        }
        $m = new \Mustache_Engine(array(
            'loader' => new \Mustache_Loader_FilesystemLoader($this->templateDirectory),
            'partials_loader' => new \Mustache_Loader_FilesystemLoader($this->templateDirectory.'/partials')
        ));
        $html = $m->render($template, $this->data);
        return $html;
    }

    public function render($response, $template, $data = []) {
        $html = $this->html($template, $data);
        $html = $this->minify($html);
        return $response->write($html);
    }

    public function json($response, $data = []){
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->write(json_encode($data));
    }

    public function redirect($response, $url, $status=302){
        $response = $response->withStatus($status)->withHeader('Location', $url);
        return $response;
    }

    public function minify($html){
        $search = array(
            '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
            '/[^\S ]+\</s',  // strip whitespaces before tags, except space
            '/(\s)+/s'       // shorten multiple whitespace sequences
        );

        $replace = array(
            '>',
            '<',
            '\\1'
        );

        $html = preg_replace($search, $replace, $html);

        return $html;
    }
}