<?php 
namespace App\Controllers;

use App\Core\Controller;
use App\Traits\Tools;

class CitationToolsController extends Controller
{
    use Tools;
    public function generate($year, $volume, $issue, $id, $tool) {
        $article_info = $this->getArticleData($year, $volume, $issue, $id); 
        foreach ($article_info['AuthorList']['Author'] as $author) {
            $first = $author['FirstName'] ?? null;
            $last = $author['LastName'] ?? null;
            if (!empty($author['MiddleName'])) {
                $mid = $author['MiddleName'];
            }
            $authors[] = $first . ' ' . $mid . ' ' . $last;
        }
    
        $reference = [
            'id' => $id,
            'authors' => $authors,
            'title' => $article_info['ArticleTitle'],
            'year' => $article_info['Journal']['PubDate']['Year'],
            'volume' => $article_info['Journal']['Volume'],
            'issue' => $article_info['Journal']['Issue'],
            'journal' => $article_info['Journal']['JournalTitle'],
            'publisher' => $article_info['Journal']['PublisherName'],
            'firstPage' => $article_info['FirstPage'],
            'lastPage' => $article_info['LastPage'],
            'doi' => $article_info['ELocationID']
        ];

        // check for folder existance
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/files/citations/$tool")) mkdir($_SERVER['DOCUMENT_ROOT'] . "/files/citations/$tool", 0777, true);
        
        if ($tool === 'bibtex') $this->bibtex($reference);
        elseif ($tool === 'zotero') $this->bibtex($reference);
        elseif ($tool === 'endnoteXML') $this->generateEndNoteXMLCitation($article_info, $id);
        elseif ($tool === 'bookends') $this->bookends($reference);
        elseif ($tool === 'endnoteTagged') $this->endnoteTagged($reference);
        elseif ($tool === 'medlars') $this->medlars($reference);
        elseif ($tool === 'mendlay') $this->medlars($reference);
        elseif ($tool === 'refworksTagged') $this->generateRefWorksTaggedCitation($reference);
        elseif ($tool === 'ris') $this->generateRisCitation($reference);
        else $this->redirect('404');
    }

    private function generateEndNoteXMLCitation($articleData, $id) {
        $this->generateEndnoteXML($articleData, $id);
        // Download the file

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $id . '.xml"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($_SERVER['DOCUMENT_ROOT'] . "/files/citations/endnoteXML/" . $id . ".xml"));
        readfile($_SERVER['DOCUMENT_ROOT'] . "/files/citations/endnoteXML/" . $id . ".xml");
        exit;
    }
    
    private function generateRefWorksTaggedCitation($reference) {
        // Save the BibTeX entries to a file
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . "/files/citations/refworksTagged/" . $reference['id'] . ".rtf", "w");
        fwrite($file, $this->generateRefworksTagged($reference));
        fclose($file);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' .  $reference['id'] . '.rtf"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($_SERVER['DOCUMENT_ROOT'] . "/files/citations/refworksTagged/" .  $reference['id'] . ".rtf"));
        readfile($_SERVER['DOCUMENT_ROOT'] . "/files/citations/refworksTagged/" .  $reference['id'] . ".rtf");
        exit;
    }
    private function generateRisCitation($reference) {
        // Save the BibTeX entries to a file
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . "/files/citations/ris/" . $reference['id'] . ".ris", "w");
        fwrite($file, $this->generateRis($reference));
        fclose($file);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' .  $reference['id'] . '.ris"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($_SERVER['DOCUMENT_ROOT'] . "/files/citations/ris/" .  $reference['id'] . ".ris"));
        readfile($_SERVER['DOCUMENT_ROOT'] . "/files/citations/ris/" .  $reference['id'] . ".ris");
        exit;
    }
    
    private function medlars($reference) {
        // Save the BibTeX entries to a file
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . "/files/citations/medlars/" . $reference['id'] . ".txt", "w");
        fwrite($file, $this->generateMedlars($reference));
        fclose($file);

        // Download the file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $reference['id'] . '.txt"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($_SERVER['DOCUMENT_ROOT'] . "/files/citations/medlars/" . $reference['id'] . ".txt"));
        readfile($_SERVER['DOCUMENT_ROOT'] . "/files/citations/medlars/" . $reference['id'] . ".txt");
        exit;
    }
    private function endnoteTagged($reference) {
        // Save the BibTeX entries to a file
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . "/files/citations/endnoteTagged/" . $reference['id'] . ".enw", "w");
        fwrite($file, $this->generateEndnoteTagged($reference));
        fclose($file);

        // Download the file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $reference['id'] . '.enw"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($_SERVER['DOCUMENT_ROOT'] . "/files/citations/endnoteTagged/" . $reference['id'] . ".enw"));
        readfile($_SERVER['DOCUMENT_ROOT'] . "/files/citations/endnoteTagged/" . $reference['id'] . ".enw");
        exit;
    }
    private function bookends($reference) {
        // Save the BibTeX entries to a file
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . "/files/citations/bookends/" . $reference['id'] . ".tag", "w");
        fwrite($file, $this->generateBookends($reference));
        fclose($file);

        // Download the file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $reference['id'] . '.tag"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($_SERVER['DOCUMENT_ROOT'] . "/files/citations/bookends/" . $reference['id'] . ".tag"));
        readfile($_SERVER['DOCUMENT_ROOT'] . "/files/citations/bookends/" . $reference['id'] . ".tag");
        exit;
    }

    private function bibtex($reference)
    {
        // Save the BibTeX entries to a file
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . "/files/citations/bibtex/" . $reference['id'] . ".bib", "w");
        fwrite($file, $this->generateBibTeX($reference));
        fclose($file);

        // Download the file
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $reference['id'] . '.bib"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($_SERVER['DOCUMENT_ROOT'] . "/files/citations/bibtex/" . $reference['id'] . ".bib"));
        readfile($_SERVER['DOCUMENT_ROOT'] . "/files/citations/bibtex/" . $reference['id'] . ".bib");
        exit;
    }

    private function getArticleData($year, $volume, $issue, $id) {
        $dir = $year . "/" . $volume . "/" . $issue . "/";
        $file = scandir($_SERVER['DOCUMENT_ROOT'] . "/files/xml/" . $dir);
        $xml = $this->readXML($dir. $file[2]);
        
        // find article by id
        $article_info = [];
        foreach ($xml['Article'] as $article) if ($article['ELocationID'] == $_ENV['JOURNAL_DOI'] . $id) $article_info = $article;
        if (empty($article_info)) $this->redirect('404');
        
        return $article_info;
    }
}