<?php

namespace App\Traits;
require 'App/Lib/simple_html_dom.php'; // Include the Simple HTML DOM Parser library

trait Citation {
    function generateAPACitation($articleData) {
        $authors = array();
        foreach ($articleData['AuthorList']['Author'] as $author) {
            $authorName = $author['LastName'] . ', ' . substr($author['FirstName'], 0, 1) . '.';
            if (!empty($author['MiddleName'])) {
                $authorName .= ' ' . substr($author['MiddleName'], 0, 1) . '.';
            }
            $authors[] = $authorName;
        }
        
        $year = $articleData['Journal']['PubDate']['Year'];
        $articleTitle = $articleData['ArticleTitle'];
        $journalTitle = $articleData['Journal']['JournalTitle'];
        
        $citation = implode(', ', $authors) . ' (' . $year . '). ' . $articleTitle . '. ' . $journalTitle . ', ' . $articleData['Journal']['Volume'] . '(' . $articleData['Journal']['Issue'] . '), ' . $articleData['FirstPage'] . '-' . $articleData['LastPage'] . '. <b> doi:' . ' <a href="https://doi.org/' . $articleData['ELocationID'] . '" target="_blank">' . $articleData['ELocationID'] . '</a></b>';
    
        return $citation;
    }
    function generateHarvardCitation($articleData) {
        $authors = array();
        foreach ($articleData['AuthorList']['Author'] as $author) {
            $mid = $author['MiddleName'][0] ?? null;
            $authors[] = $author['LastName'] . ', ' . $author['FirstName'] . ' ' . $mid;
        }
        
        $year = $articleData['Journal']['PubDate']['Year'];
        $articleTitle = $articleData['ArticleTitle'];
        $journalTitle = $articleData['Journal']['JournalTitle'];
        $volume = $articleData['Journal']['Volume'];
        $firstPage = $articleData['FirstPage'];
        $lastPage = $articleData['LastPage'];
        $doi = $articleData['ELocationID'];
        $accessedDate = date('F d, Y');

        $authorList = implode('; ', $authors);
        $citation = "$authorList $year, '$articleTitle', $journalTitle, vol. $volume, pp. $firstPage-$lastPage.";
        if (!empty($doi)) {
            $citation .= " [Online]. Available at: ".' <b><a href="https://doi.org/' . $doi . '" target="_blank">' . $doi . '</a></b>'." [Accessed $accessedDate].";
        }
    
        return $citation;
    }
    function generateTurabianCitation($articleData) {
        $authors = array();
        foreach ($articleData['AuthorList']['Author'] as $author) {
            $mid = $author['MiddleName'][0] ?? null;
            $authorName = $author['FirstName'] . ' ' . $mid . ' ' . $author['LastName'];
            $authors[] = $authorName;
        }
    
        $articleTitle = $articleData['ArticleTitle'];
        $journalTitle = $articleData['Journal']['JournalTitle'];
        $pubDate = $articleData['Journal']['PubDate']['Year'];
        $volume = $articleData['Journal']['Volume'];
        $firstPage = $articleData['FirstPage'];
        $lastPage = $articleData['LastPage'];
        $doi = $articleData['ELocationID'];
    
        $authorList = implode(', ', $authors);
        $citation = "$authorList. \"$articleTitle.\" $journalTitle $volume, no. {$articleData['Journal']['Issue']} ($pubDate): $firstPage-$lastPage.";
        
        if (!empty($doi)) {
            $citation .= " DOI: " . ' <b><a href="https://doi.org/' . $doi . '" target="_blank">' . $doi . '</a></b>' . ".";
        }
    
        return $citation;
    }

    function generateVancouverCitation($articleData) {
        $authors = array();
        foreach ($articleData['AuthorList']['Author'] as $author) {
            $authorName = $author['LastName'] . ' ' . $author['FirstName'][0];
            if (!empty($author['MiddleName'])) {
                $authorName .= ' ' . $author['MiddleName'][0];
            }
            $authors[] = $authorName;
        }
    
        $articleTitle = $articleData['ArticleTitle'];
        $journalTitle = $articleData['Journal']['JournalTitle'];
        $pubDate = $articleData['Journal']['PubDate']['Year'];
        $volume = $articleData['Journal']['Volume'];
        $issue = $articleData['Journal']['Issue'];
        $firstPage = $articleData['FirstPage'];
        $lastPage = $articleData['LastPage'];
        $doi = $articleData['ELocationID'];
        $accessedDate = date('F d, Y');
        $authorList = implode(', ', $authors);
        $citation = "$authorList. $articleTitle. $journalTitle. ($pubDate); [cited $accessedDate] $volume($issue): $firstPage-$lastPage.";
    
        if (!empty($doi)) {
            $citation .= " doi: " . ' <b><a href="https://doi.org/' . $doi . '" target="_blank">' . $doi . '</a></b>';
        }
    
        return $citation;
    }
    function generateChicagoCitation($articleData) {
        $authors = array();
        foreach ($articleData['AuthorList']['Author'] as $author) {
            $mid = $author['MiddleName'][0] ?? null;
            $authors[] = $author['LastName'] . ', ' . $author['FirstName'] . ' ' . $mid;
        }
    
        $year = $articleData['Journal']['PubDate']['Year'];
        $articleTitle = $articleData['ArticleTitle'];
        $journalTitle = $articleData['Journal']['JournalTitle'];
        $volume = $articleData['Journal']['Volume'];
        $firstPage = $articleData['FirstPage'];
        $lastPage = $articleData['LastPage'];
        $doi = $articleData['ELocationID'];
    
        $citation = implode(', ', $authors) . ' "' . $articleTitle . '" ' . $journalTitle . ' ' . $volume . ', (' . $year . '): ' . $firstPage . '-' . $lastPage;
        if (!empty($doi)) {
            $citation .= ', <b> doi:' . ' <a href="https://doi.org/' . $doi . '" target="_blank">' . $doi . '</a></b>';
        }
        $citation .= '.';
    
        return $citation;
    }
    
    function generateMLACitation($articleData) {
        $authors = array();
    
        foreach ($articleData['AuthorList']['Author'] as $author) {
            $mid = $author['MiddleName'][0] ?? null;

            $authorName = $author['LastName'] . ', ' . $author['FirstName'] . ' ' . $mid;
            $authors[] = $authorName;
        }
    
        $authorList = implode(', ', $authors);
        $articleTitle = '"' . $articleData['ArticleTitle'] . '"';
        $journalTitle = $articleData['Journal']['JournalTitle'];
        $pubDate = $articleData['Journal']['PubDate']['Year'];
        $volume = $articleData['Journal']['Volume'];
        $issue = $articleData['Journal']['Issue'];
        $pageRange = $articleData['FirstPage'] . '-' . $articleData['LastPage'];
        return $authorList . '. ' . $articleTitle . '. ' . $journalTitle . ', ' . $volume . '.' . $issue . ' (' . $pubDate . '), ' . $pageRange . '.'  . ' Print. <b> doi:' . ' <a href="https://doi.org/' . $articleData['ELocationID'] . '" target="_blank">' . $articleData['ELocationID'] . '</a></b>';
    }

    function generateAMACitation($articleData) {
        $authors = array();
        foreach ($articleData['AuthorList']['Author'] as $author) {
            $mid = $author['MiddleName'][0] ?? null;
            $first = $author['FirstName'][0] ?? null;
            $authors[] = $author['LastName'] . ' ' . $first . '' . $mid;
        }
        $authorsString = implode(', ', $authors);

        $journalTitle = $articleData['Journal']['JournalTitle'];
        $articleTitle = $articleData['ArticleTitle'];
        $pubDate = $articleData['Journal']['PubDate']['Year'];
        $volume = $articleData['Journal']['Volume'];
        $pageRange = $articleData['FirstPage'] . '-' . $articleData['LastPage'];
        $doi = $articleData['ELocationID'];

        // Create the AMA-style citation
        $citation = $authorsString . '. ' . $articleTitle . '. ' . $journalTitle . '. ' . $pubDate . '; ';
        if ($volume) {
            $citation .=  $articleData['Journal']['Volume'] . '(' . $articleData['Journal']['Issue'] . ')';
        }
        if ($pageRange) {
            $citation .= ': ' . $pageRange;
        }
        if ($doi) {
            $citation .= '.<b> doi:' . ' <a href="https://doi.org/' . $doi . '" target="_blank">' . $doi . '</a></b>';
        }

        return $citation;
    }
    
    function generatePubMedCitation($articleData) {
        // Extract necessary information from the XML data
        $journalTitle = $articleData['Journal']['JournalTitle'];
        $articleTitle = $articleData['ArticleTitle'];
        $authorList = $articleData['AuthorList']['Author'];
        $pubDate = $articleData['Journal']['PubDate'];
        $doi = $articleData['ELocationID'];
    
        // Format the authors' names
        $authors = array();
        foreach ($authorList as $author) {
            $mid = $author['MiddleName'][0] ?? null;
            $first = $author['FirstName'][0] ?? null;
            $authors[] = $author['LastName'] . ' ' . $first . '' . $mid;
        }
        $authorsString = implode(', ', $authors);
    
        // Format the publication date
        $year = $pubDate['Year'];
    
        // Create the PubMed-style citation
        $citation = $authorsString . '. ';
        $citation .= $articleTitle . '. ';
        $citation .= $journalTitle . '. ';
        $citation .= $year . '; ';
        if ($articleData['Journal']['Volume']) {
            $citation .=  $articleData['Journal']['Volume'] . '(' . $articleData['Journal']['Issue'] . ') ';
        }
        if ($articleData['FirstPage'] && $articleData['LastPage']) {
            $citation .= $articleData['FirstPage'] . '-' . $articleData['LastPage'] . '.';
        } elseif ($articleData['FirstPage']) {
            $citation .= $articleData['FirstPage'] . '.';
        } elseif ($articleData['LastPage']) {
            $citation .= $articleData['LastPage'] . '.';
        }
        $citation .= '<b> doi: ' . ' <a href="https://doi.org/' . $doi . '" target="_blank">' . $doi . '</a></b>';
    
        return $citation;
    }
    
    public function getGoogleScholorCitaions($doi) {
        $url = 'https://scholar.google.com/scholar?hl=en&as_sdt=0%2C5&q=' . $doi . '&btnG=';
        $html = file_get_html($url); // Fetch the HTML content
        if ($html) {
           // get the anchor tag that has a text "Cited by"
            $article = $html->find('.gs_ri', 0);
            $anchors = $article->find('a');
            // see if it exists
            $citedByAnchor = null;

            foreach ($anchors as $anchor) {
                if (strpos($anchor->plaintext, 'Cited by') !== false) {
                    $citedByAnchor =(int) explode('Cited by ', $anchor->plaintext)[1];
                    $citedByUrl = $anchor->href;
                    break;
                }
            }

            $html->clear(); // Clean up the Simple HTML DOM object
            return [
                'cited_by_count' => $citedByAnchor ?? null,
                'cited_by_url' => $citedByUrl ?? null
            ];
        
        } else {
            return [
                'cited_by_url' => null,
                'cited_by_count' => null
            ];
        }
    }

    public function getCrossRefCitations($doi) {
        // URL to send the POST request to
        $url = 'https://doi.crossref.org/servlet/getForwardLinks?usr=discpub&pwd=dpub_46&doi=' . $doi;

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ''); // Empty body
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));

        // Execute the cURL session
        $res = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            // Close the cURL session
            curl_close($ch);

            // Check if the response is not empty
            if (!empty($res)) {    
               // Convert the SimpleXML object to an associative array
               $response = json_decode(json_encode(simplexml_load_string($res)), true);
   
               $citations = [];
               if (isset($response['query_result']['body']['forward_link'])) {
                    if (!isset($response['query_result']['body']['forward_link']['journal_cite'])) {
                        foreach ($response['query_result']['body']['forward_link'] as $citation) {
                            $citations[] = [
                                'article_title' => $citation['journal_cite']['article_title'],
                                'doi' => $citation['journal_cite']['doi'],
                                'article_journal' => $citation['journal_cite']['journal_title'],
                                'year' => $citation['journal_cite']['year'],
                                'volume' => $citation['journal_cite']['volume'] ?? ' ',
                            ];
                        }
                    } else {
                        $citation = $response['query_result']['body']['forward_link']['journal_cite'];
                        $citations[] = [
                            'article_title' => $citation['article_title'],
                            'doi' => $citation['doi'],
                            'article_journal' => $citation['journal_title'],
                            'year' => $citation['year'],
                            'volume' => $citation['volume'],
                        ];
                    }
                }
                // Print the JSON response
                return $citations;
            } else {
                return [];
            }
        }
   }
   
}