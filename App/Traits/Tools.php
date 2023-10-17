<?php

namespace App\Traits;

trait Tools {

    public function getIssueArchive() {
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/files/xml/";
        $years = scandir($dir);
        $years = array_diff($years, ['.', '..']);
        $years = array_reverse($years);

        $data = [];
        foreach ($years as $year) {
            if (in_array($year, ['current-issue.xml', 'latest-issue.xml'])) continue;
            $data[$year] = [];
            $volumes = scandir($dir . $year);
            $volumes = array_diff($volumes, ['.', '..']);
            $volumes = array_reverse($volumes);
            foreach ($volumes as $volume) {
                $data[$year][$volume] = [];
                $issues = scandir($dir . $year . "/" . $volume);
                $issues = array_diff($issues, ['.', '..']);
                $issues = array_reverse($issues);
                
                foreach ($issues as $issue) {
                    // get issue name fom xml
                    $xml = $this->readXML("${year}/${volume}/${issue}/" . scandir($dir . $year . "/" . $volume . "/" . $issue)[2]);
                    if (isset($xml['Article'][0])) {
                        $issue_name = $xml['Article'][0]['Journal']['Issue'];
                    } else {
                        $issue_name = $xml['Article']['Journal']['Issue'];
                        $data[$year][$volume][$issue] = $issue;
                    }
                    $data[$year][$volume][$issue_name] = $issue;
                }
            }
        }
        return $data;
    }
    public function generateEndnoteXML($articleData, $id) {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
    
        // Create the root element
        $root = $xml->createElement('xml');
        $xml->appendChild($root);
    
        // Create the record element
        $record = $xml->createElement('record');
        $root->appendChild($record);
    
        // Create data elements
        $authors = $articleData['AuthorList']['Author'];
        foreach ($authors as $author) {
            $data = $xml->createElement('data');
            $record->appendChild($data);
    
            $element = $xml->createElement('element', $author['LastName']);
            $element->setAttribute('name', 'Author');
            $data->appendChild($element);
    
            if (!empty($author['FirstName'])) {
                $element = $xml->createElement('element', $author['FirstName']);
                $element->setAttribute('name', 'Author');
                $data->appendChild($element);
            }
            
            if (!empty($author['MiddleName'])) {
                $element = $xml->createElement('element', $author['MiddleName']);
                $element->setAttribute('name', 'Author');
                $data->appendChild($element);
            }
        }
    
        $articleTitle = $articleData['ArticleTitle'];
        $data = $xml->createElement('data');
        $record->appendChild($data);
    
        $element = $xml->createElement('element', $articleTitle);
        $element->setAttribute('name', 'Title');
        $data->appendChild($element);
    
        $journalTitle = $articleData['Journal']['JournalTitle'];
        $data = $xml->createElement('data');
        $record->appendChild($data);
    
        $element = $xml->createElement('element', $journalTitle);
        $element->setAttribute('name', 'Secondary Title');
        $data->appendChild($element);
    
        $pubDate = $articleData['Journal']['PubDate']['Year'];
        $data = $xml->createElement('data');
        $record->appendChild($data);
    
        $element = $xml->createElement('element', $pubDate);
        $element->setAttribute('name', 'Year');
        $data->appendChild($element);

        $volume = $articleData['Journal']['Volume'];
        $data = $xml->createElement('data');
        $record->appendChild($data);

        $element = $xml->createElement('element', $volume);
        $element->setAttribute('name', 'Volume');
        $data->appendChild($element);

        $issue = $articleData['Journal']['Issue'];
        $data = $xml->createElement('data');
        $record->appendChild($data);

        $element = $xml->createElement('element', $issue);
        $element->setAttribute('name', 'Number');
        $data->appendChild($element);

        $firstPage = $articleData['FirstPage'];
        $lastPage = $articleData['LastPage'];
        $data = $xml->createElement('data');
        $record->appendChild($data);

        $element = $xml->createElement('element', $firstPage);

        $element->setAttribute('name', 'Pages');
        $data->appendChild($element);

        $element = $xml->createElement('element', $lastPage);
        $element->setAttribute('name', 'Pages');
        $data->appendChild($element);

        
        $doi = $articleData['ELocationID'];
        $data = $xml->createElement('data');
        $record->appendChild($data);

        $element = $xml->createElement('element', $doi);
        $element->setAttribute('name', 'DOI');
        $data->appendChild($element);

    
        // Save the XML data to a file
        $xml->save($_SERVER['DOCUMENT_ROOT'] . "/files/citations/endnoteXML/" . $id . '.xml');

    }
    public function generateEndnoteTagged($reference) {
        $authorList = implode('; ', $reference['authors']);
        $articleTitle = $reference['title'];
        $journalTitle = $reference['journal'];
        $pubDate = $reference['year'];
        $volume = $reference['volume'];
        $firstPage = $reference['firstPage'];
        $lastPage = $reference['lastPage'];
        $doi = $reference['doi'];
        $issue = $reference['issue'];

        return "%A $authorList\n%T $articleTitle\n%J $journalTitle\n%D $pubDate\n%I $issue\n%V $volume\n%P $firstPage-$lastPage\n%U $doi\n";

    }
    public function generateRefworksTagged($reference) {
        $authorList = implode('; ', $reference['authors']);
        $articleTitle = $reference['title'];
        $journalTitle = $reference['journal'];
        $pubDate = $reference['year'];
        $volume = $reference['volume'];
        $firstPage = $reference['firstPage'];
        $lastPage = $reference['lastPage'];
        $doi = $reference['doi'];
        $issue = $reference['issue'];

        return "TY - JOUR\nAU - $authorList\nTI - $articleTitle\nT2 - $journalTitle\nPY - $pubDate\nVL - $volume\nIS $issue\nSP - $firstPage\nEP - $lastPage\nDO - $doi\n";

    }
 
    public function generateRis($reference) {
        $authorList = implode(' and  ', $reference['authors']);
        $articleTitle = $reference['title'];
        $journalTitle = $reference['journal'];
        $pubDate = $reference['year'];
        $volume = $reference['volume'];
        $firstPage = $reference['firstPage'];
        $lastPage = $reference['lastPage'];
        $doi = $reference['doi'];
        $issue = $reference['issue'];

        return "TY - JOUR\nAU - $authorList\nTI - $articleTitle\nT2 - $journalTitle\nPY - $pubDate\nVL - $volume\nIS $issue\nSP - $firstPage\nEP - $lastPage\nDO - $doi\n";

    }
 
    public function generateMedlars($reference) {
        $authorList = implode(', ', $reference['authors']);
        $articleTitle = $reference['title'];
        $journalTitle = $reference['journal'];
        $pubDate = $reference['year'];
        $volume = $reference['volume'];

        $firstPage = $reference['firstPage'];
        $lastPage = $reference['lastPage'];
        $doi = $reference['doi'];
        $issue = $reference['issue'];

        return "$authorList. $articleTitle. $journalTitle. $pubDate; $volume($issue):$firstPage-$lastPage. doi:$doi\n";
    }

    public function generateBookends($reference) {
        $authorsString = implode(' and ', $reference['authors']);
        $articleTitle = $reference['title'];
        $journalTitle = $reference['journal'];
        $pubDate = $reference['year'];
        $volume = $reference['volume'];
        $issue = $reference['issue'];
        $firstPage = $reference['firstPage'];
        $lastPage = $reference['lastPage'];
        $doi = $reference['doi'];

        $citation = <<<BOOKENDS
            %A $authorsString
            %T $articleTitle
            %J $journalTitle
            %D $pubDate
            %V $volume
            %I $issue
            %P $firstPage-$lastPage
            %U $doi
        BOOKENDS;

        return $citation;
    }
    public function generateBibTeX($reference) {
        $entry = "@";
        if (isset($reference['journal'])) {
            // It's an article
            $entry .= "article{";
        } elseif (isset($reference['booktitle'])) {
            // It's an inproceedings (conference paper)
            $entry .= "inproceedings{";
        } else {
            // It's a generic entry (e.g., thesis)
            $entry .= "misc{";
        }
    
        // Generate a unique key for the entry (e.g., AuthorYear)
    
        $entry .= $reference['id']. ",\n";
        $entry .= "    author = {" . implode(' and ', $reference['authors'])  . "},\n";
        $entry .= "    title = {" . $reference['title'] . "},\n";
        $entry .= "    year = {" . $reference['year'] . "},\n";
        $entry .= "    volume = {" . $reference['volume'] . "},\n";
        $entry .= "    number = {" . $reference['issue'] . "},\n";
        $entry .= "    pages = {" . $reference['firstPage'] . "--" . $reference['lastPage'] . "},\n";
        $entry .= "    doi = {" . $reference['doi'] . "},\n";


    
        if (isset($reference['journal'])) {
            $entry .= "    journal = {" . $reference['journal'] . "},\n";
        }
    
        if (isset($reference['publisher'])) {
            $entry .= "    publisher = {" . $reference['publisher'] . "},\n";
        }
    
        $entry .= "}\n";
    
        return $entry;
    }

    
}