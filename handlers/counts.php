<?php

if(!isset($this->user) || !is_object($this->user) || !$this->user->isMod){
    return $this->view->redirect($response, '/');
}

if(is_object($this->user) && $this->user->isMod){
    $counts = R::getAll('SELECT COUNT(*) as total FROM log WHERE user_id != 2 AND user_id != 3');
    $this->view->total_hits = number_format($counts[0]['total']);

    timer('total counts');

    $counts = R::getAll('SELECT COUNT(DISTINCT uid) AS total FROM log WHERE user_id != 2 AND user_id != 3');
    $this->view->total_people = number_format($counts[0]['total']);

    timer('total users counts');

    $this->view->total_users = number_format(R::count('user') - 1);

    $counts = R::getAll('SELECT COUNT(*) AS total FROM ( SELECT ua, uid FROM log WHERE created > ? AND user_id != 2 AND user_id != 3 GROUP BY ua, uid ) AS pcs', [time() - 60*15]);
    $this->view->last_15 = number_format($counts[0]['total']);

    timer('last 15 min');

    $counts = R::getAll('SELECT COUNT(*) AS total FROM ( SELECT ua, uid FROM log WHERE created > ? AND user_id != 2 AND user_id != 3 GROUP BY ua, uid ) AS pcs', [time() - 60*60]);
    $this->view->last_hour = number_format($counts[0]['total']);

    timer('last hour');

    $counts = R::getAll('SELECT COUNT(*) AS total FROM ( SELECT ua, uid FROM log WHERE created > ? AND user_id != 2 AND user_id != 3 GROUP BY ua, uid ) AS pcs', [time() - 60*60*24]);
    $this->view->last_day = number_format($counts[0]['total']);

    timer('last day');

    // $results = $this->client->count([
    //     "index"=> "torrents",
    //     "type"=> "hash",
    //     "body"=> [
    //         "query"=> [
    //             "bool"=> [
    //                 "must_not"=> [
    //                     "exists"=> [
    //                         "field"=> "categories_updated"
    //                     ]
    //                 ]
    //             ]
    //         ]
    //     ]
    // ]);

    // $this->view->catless = number_format($results['count']);

    // timer('categoryless');

}

?>