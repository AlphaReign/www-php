<?php

$app->add(function ($request, $response, $next) {

    timer('start counts');

    try{
        $counts = file_get_contents('counts.txt');
        $this->view->count = explode('|', $counts)[0];
        $time = explode('|', $counts)[1];
        if($this->params->refresh == 'counts' || $time + 60 * 60 < time()){
            $count = $this->client->count(['index' => 'torrents', 'type' => 'hash']);
            $count = number_format($count['count']);
            $this->view->count = $count;
            file_put_contents('counts.txt', $count . '|' . time());
        }
    }catch(Exception $error){
        exit('Doing some maintenance');
    }

    timer('end counts');

    return $next($request, $response);
});
