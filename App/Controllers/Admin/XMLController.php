<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Traits\Tools;

class XMLController extends Controller
{

    use Tools;

    public function makeCurrent()
    {
        $year = $_POST['year'];
        $volume = $_POST['volume'];
        $issue = $_POST['issue'];

        $data = $this->db()->table('current_issue')->select()->first();
        
        if ($data) {
            $this->db()->table('current_issue')->where('id', $data['id'])->update([
                'year' => $year,
                'volume' => $volume,
                'issue' => $issue,
            ])->execute();
            $this->back('success', 'Current issue updated successfully');
        } else {
            $this->db()->table('current_issue')->insert([
                'year' => $year,
                'volume' => $volume,
                'issue' => $issue,
            ])->execute();

            $this->back('success', 'Current issue updated successfully');
        }
    }

    public function index()
    {
        $data = $this->getIssueArchive();
        $this->view('admin/issues/index', [
            'data' => $data,
            'meta' => [
                'title' => "Issues",
                'description' => "Journal issues",
            ],
        ]);
    }

    public function create() {
        $this->view('admin/issues/create', [
            'meta' => [
                'title' => "Create Issue",
                'description' => "Create a new issue",
            ],
        ]);
    }

    public function insert() {
        if(!isset($_FILES['xml_file']) || $_FILES['xml_file']['error'] == UPLOAD_ERR_NO_FILE) {
            $this->back('error', 'Error uploading xml. No file choosen!');
        }

        $year = $_POST['year'];
        $volume = $_POST['volume'];
        $issue = $_POST['issue'];
        $issue_label = $_POST['issue_label'];

        $file = $_FILES['xml_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_error = $file['error'];
        $file_ext = explode('.', $file_name);
        $file_ext = strtolower(end($file_ext));

        $allowed = ['xml'];
        if (!in_array($file_ext, $allowed)) $this->back('error', 'File type not allowed');
        if ($file_error) $this->back('error', 'Error uploading xml');
       
        $file_name_new = uniqid('', true) . '.' . $file_ext;
        $file_destination = "files/xml/$year/$volume/$issue/$file_name_new";
        
        // create directory if not exists
        if (!file_exists("files/xml/$year/$volume/$issue")) mkdir("files/xml/$year/$volume/$issue", 0777, true);

        if (!move_uploaded_file($file_tmp, $file_destination))  $this->back('error', 'Error uploading xml'. $file_tmp . ' ' . $file_destination); 
        if ($issue_label != '') {
            $xml = simplexml_load_file($file_destination);
            foreach ($xml->Article as $article) $article->Journal->Issue = $issue_label;
            $xml->asXML($file_destination);
        }
        $this->back('success', 'XML uploaded successfully');
    }
}