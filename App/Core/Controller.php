<?php

namespace App\Core;

use App\Traits\Logs;
use App\Traits\Model;
use App\Traits\Request;
use App\Traits\Validation;
use App\Traits\View;

class Controller
{
    use Validation, Request, View, Model, Logs;
    public function slugify($content)
    {
        $content = strtolower($content);
        $content = str_replace(' ', '-', $content);
        $content = preg_replace('/[^A-Za-z0-9\-]/', '', $content);
        return $content;
    }

    public function readXML($file_path)
    {
        $xml = json_decode(json_encode(simplexml_load_file($_SERVER['DOCUMENT_ROOT'] . "/files/xml/" . $file_path)), true) or die("Error: Cannot create object");
        if (!isset($xml['Article'][0])) {
            // if single author in AuthorList
            if (isset($xml['Article']['AuthorList']['Author']['LastName'])) {
                $xml['Article']['AuthorList']['Author'] = array($xml['Article']['AuthorList']['Author']);
            }
            $xml['Article'] = array($xml['Article']);
        }
        return $xml;
    }
}
