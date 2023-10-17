<?php

namespace App\Controllers;
use App\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $journal_data = $this->db()->table('settings')->select()->first();
        $featured_article = $this->db()->table('featuredarticle')->select()->first();
        $editorial_board = $this->db()->table('editors')->select()->get();

        $this->view('home/index', [
            "meta" => [
                "title" => "Home",
                "description" => "Home page",
                "keywords" => "home, page"
            ],
            "journal_data" => $journal_data,
            "featured_article" => $featured_article,
            "editorial_board" => $editorial_board,
            "most_read_articles" => [],
            "most_cited_articles" => [],
            "most_downloaded_articles" => [],
        ]);
    }
}
