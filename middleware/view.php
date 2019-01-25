<?php

//This middleware preps some view variables to ensure that we always have access to these values in the view
$app->add(function ($request, $response, $next) {

    timer('starting view');
    $this->view->site = $this->config['site'];
    $this->view->url = $request->getUri()->getPath();

    $this->view->csrfNameKey = $this->csrf->getTokenNameKey();
    $this->view->csrfValueKey = $this->csrf->getTokenValueKey();

    $this->view->csrfName = $request->getAttribute($this->view->csrfNameKey);
    $this->view->csrfValue = $request->getAttribute($this->view->csrfValueKey);


    timer('getting trackers');
    $trackerJSON = file_get_contents(ROOT . '/trackers.json');
    $trackers = json_decode($trackerJSON, true);
    $temp = '';
    foreach($trackers as $i=>$tracker){
        $tracker = urlencode($tracker);
        $temp .= "&tr={$tracker}";
        if(strlen($temp) > 1800){
            break;
        }
    }
    $this->view->trackerLink = $temp;
    timer('finished trackers');


    $getParams = $this->params->get();
    $this->view->getURL = count($getParams) > 0 == '' ? '' : http_build_query($getParams) . '&';

    $sortless = $getParams;
    unset($sortless['sort']);
    $this->view->getURLSortless = count($sortless) > 0 == '' ? '' : http_build_query($sortless) . '&';

    $pageless = $getParams;
    unset($pageless['page']);
    $this->view->getURLPageless = count($pageless) > 0 == '' ? '' : http_build_query($pageless) . '&';

    $typeless = $getParams;
    unset($typeless['type']);
    $this->view->getURLTypeless = count($typeless) > 0 == '' ? '' : http_build_query($typeless) . '&';

    $catless = $getParams;
    unset($catless['cat']);
    $this->view->getURLCatless = count($catless) > 0 == '' ? '' : http_build_query($catless) . '&';

    $tagless = $getParams;
    unset($tagless['tag']);
    $this->view->getURLTagless = count($tagless) > 0 == '' ? '' : http_build_query($tagless) . '&';

    $this->view->defaultTypes = ['video', 'audio', 'doc'];
    $this->view->defaultCategories = ['show', 'movie', 'album', 'ebook'];
    $this->view->defaultTags = ['1080', '720', 'hd', 'sd', 'bdrip', 'xxx', 'dvdrip', 'epub', 'mobi', 'kindle'];

    return $next($request, $response);
});
