<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Traits\Citation;
use App\Traits\Logs;

class ArticlesController extends Controller
{
    use Citation;

    public function index($year, $volume, $issue, $id)
    {
        $dir = $year . "/" . $volume . "/" . $issue . "/";
        $file = scandir($_SERVER['DOCUMENT_ROOT'] . "/files/xml/" . $dir);
        $xml = $this->readXML($dir. $file[2]);

        // find article by id
        $article_info = [];
        $previousArticle = $nextArticle = null;

        foreach ($xml['Article'] as $article) {
            if ($article['ELocationID'] == $_ENV['JOURNAL_DOI'] . $id) {
                $article_info = $article;
                $article_info['Journal']['Issue_name'] = $article_info['Journal']['Issue'];
                $article_info['Journal']['Issue'] = $issue;
                $article_index = array_search($article, $xml['Article']);
                $previousArticle = $xml['Article'][$article_index - 1] ?? null;
                $nextArticle = $xml['Article'][$article_index + 1] ?? null;
            }
        }
        if (empty($article_info)) $this->redirect('404');

        $crossRef = $_ENV['APP_DEBUG'] ? [] : $this->getCrossRefCitations($article_info['ELocationID']);
        $google =  $_ENV['APP_DEBUG'] ? [
            'cited_by_url' => null,
            'cited_by_count' => null
        ] :  $this->getGoogleScholorCitaions($article_info['ELocationID']);

        $references = [
            'PubMed Style' => $this->generatePubMedCitation($article_info),
            'AMA (American Medical Association) Style' => $this->generateAMACitation($article_info),
            'MLA (The Modern Language Association) Style' => $this->generateMLACitation($article_info),
            'APA (American Psychological Association) Style' => $this->generateAPACitation($article_info),
            'Chicago Style' => $this->generateChicagoCitation($article_info),
            'Harvard Style' => $this->generateHarvardCitation($article_info),
            'Vancouver/ICMJE Style' => $this->generateVancouverCitation($article_info),
            'Turabian Style' => $this->generateTurabianCitation($article_info)
        ];

        $curi = explode('/', $_SERVER['REQUEST_URI']);
        if (end($curi) == 'pdf') {
            $download = true;
        } else {
            $download = false;
        }   
        // update matrics
        if (!$_ENV['APP_DEBUG']) $this->updateArticleAccessed($article_info, $issue, count($crossRef), $google['cited_by_count'], $download);

        if ($download) {
            // redirect the pdf file
            header('Location: /files/pdf/' . $id .'.pdf');
            exit;
        }

        $this->view('common/articles/index', [
            'meta' => [
                'title' => $article_info['ArticleTitle'],
            ],
            'data' => $article_info,
            'citations' => $crossRef,
            'google' => $google,
            'references' => $references,
            'previousArticle' => $previousArticle,
            'nextArticle' => $nextArticle
        ]);
    }


   
    protected function updateArticleAccessed($article, $issue, $crossref_citation_count = 0, $google_citation_count = 0, $download = false) {
        $a = explode('/', $article['ELocationID']);
        $article_id = end($a);
        
        // extract keywords

        
        try {
            $existingRecord = $this->db()
                ->table('article_analytics')
                ->select()
                ->where('article_id', $article_id)
                ->one();
            if ($existingRecord) {
                // The record exists, update it
                $this->db()
                    ->table('article_analytics')
                    ->update([
                        'crossref_citation_count' => $crossref_citation_count,
                        'google_citation_count' => $google_citation_count,
                        'accessed_count' => $existingRecord['accessed_count'] + 1,
                        'year' => $article['Journal']['PubDate']['Year'],
                        'volume' => $article['Journal']['Volume'],
                        'issue_name' => $article['Journal']['Issue'],
                        'issue' => $issue,
                        'download_count' => $download ? $existingRecord['download_count'] + 1 : $existingRecord['download_count']
                    ])
                    ->where('article_id', $article_id)
                    ->execute();
            } else {
                foreach ($article['AuthorList']['Author'] as $author) {
                    $mid = !empty($author['MiddleName']) ? $author['MiddleName'] : null;                    
                    $authors[] = $author['FirstName'] . ' ' . $mid . ' ' . $author['LastName'];
                }

                // keywords
                $keywords = [];
                if (isset($article['ObjectList']['Object'])) {
                    foreach ($article['ObjectList']['Object'] as $object) {
                        if ($object['@attributes']['Type'] == 'keyword') {
                            $keywords[] = $object['Param'];
                        }
                    }
                }

                
                // The record doesn't exist, insert a new record
                $this->db()
                    ->table('article_analytics')
                    ->insert([
                        'article_id' => $article_id,
                        'crossref_citation_count' => $crossref_citation_count,
                        'google_citation_count' => $google_citation_count,
                        'accessed_count' => 1,
                        'article_title' => $article['ArticleTitle'],
                        'authors' => implode(', ', $authors),
                        'keywords' => implode(', ', $keywords),
                        'year' => $article['Journal']['PubDate']['Year'],
                        'volume' => $article['Journal']['Volume'],
                        'issue_name' => $article['Journal']['Issue'],
                        'issue' => $issue,
                        'download_count' => $download ? 1 : 0
                    ])
                    ->execute();
            }
        } catch (\Exception $e) {
            // Handle exceptions, such as network errors
            echo "HTTP Request Failed: " . $e->getMessage();
        }
    }


}
