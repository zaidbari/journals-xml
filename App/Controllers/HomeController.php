<?php

namespace App\Controllers;
use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {

        $this->view('home/index', [
            "meta" => [
                "title" => "Home",
                "description" => "Home page",
                "keywords" => "home, page"
            ],
            "journal_data" => [],
            "featured_article" => [],
            "editorial_board" => [],
            "most_read_articles" => [],
            "most_cited_articles" => [],
            "most_downloaded_articles" => [],
        ]);
    }
}
