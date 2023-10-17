<?php

namespace App\Controllers;
use App\Core\Controller;

class PageController extends Controller
{
    public function index($slug)
    {

        $data = $this->db()->table('pages')->select()->where('page_url', $slug)->first();
        if(!$data) $this->redirect('/404');
        
        $this->view('common/single/index', [
            "meta" => [
            "title" => $data['page_title'],
            "description" => $data['page_title'] . " information for " .  $_ENV['JOURNAL_TITLE'],
            "keywords" =>  $data['page_title'] .  $_ENV['JOURNAL_TITLE'],
            ],
            "content" => $data['page_content']
        ]);
    }

    public function notFound()
    {
        $this->view('common/empty/index');
    }
}