<?php

namespace App\Controllers;
use App\Core\Controller;
use App\Traits\Tools;

class IssueController extends Controller
{

    use Tools;

    public function current_issue()
    {
        $data = $this->db()->table('current_issue')->select()->first();
        $this->index($data['year'], $data['volume'], $data['issue']);
    }

    public function latest_issue()
    {
        $this->view('common/issues/latest', [
            'meta' => [
            'title' => 'Lates issue',
            'description' => 'Online first articles of the journal ' . $_ENV["JOURNAL_TITLE"],
            ],
            'data' => []
        ]);
    }

    public function index($year, $volume, $issue)
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] .  "/files/xml/${year}/${volume}/${issue}";
        $data = $this->getIssueData($this->readXML("${year}/${volume}/${issue}/" . scandir($dir)[2]), $issue);
        $this->view('common/issues/index', $data);
    }

    public function archives() {
        $data = $this->getIssueArchive();


        $this->view('common/issues/archive', [
            'meta' => [
                'title' => 'Archives',
                'description' => 'Archives of the journal ' . $_ENV['JOURNAL_TITLE'],
            ],
            'data' => $data
        ]);
        
    }

    private function getIssueData($xml, $issue = null) {
        $articles = $issue_details = [];
        foreach ($xml['Article'] as $article) {
            $articles[$article['PublicationType']][] = $article;
            if (empty($issue_details)) {
                $issue_details = [
                    "volume" => $article['Journal']['Volume'],
                    "issue" => $issue,
                    "issue_name" => $article['Journal']['Issue'],
                    "year" => $article['Journal']['PubDate']['Year'],
                    "urls" => $this->getIssueUrl($article['Journal']['PubDate']['Year'], $article['Journal']['Volume'], $issue),
                ];
            }
        }

        return [
            "meta" => [
                "title" => "Issue " . $issue_details['issue'] . " of Volume " . $issue_details['volume'] . " (" . $issue_details['year'] . ")",
                "description" =>   "Issue " . $issue_details['issue'] . " of Volume " . $issue_details['volume'] . " (" . $issue_details['year'] . ")" . $_ENV["JOURNAL_TITLE"],
            ],
            "data" => [
                "issue_details" => $issue_details,
                "articles" => $articles,
            ]
        ];
    }


    private function getIssueUrl($currentYear, $currentVolume, $currentIssue) {
        // Directory structure root
        $rootDirectory = $_SERVER['DOCUMENT_ROOT'] . "/files/xml/";  // Adjust this to your actual directory structure

        // Function to list subdirectories (issues) in a given directory
        function listIssues($directory) {
            $issues = array();
            $entries = scandir($directory);
            foreach ($entries as $entry) {
                if (is_dir($directory . $entry) && $entry != '.' && $entry != '..') {
                    $issues[] = $entry;
                }
            }
            return $issues;
        }

        // Find the list of issues in the current volume
        $currentVolumeDirectory = $rootDirectory . $currentYear . '/' . $currentVolume . '/';
        $allIssues = listIssues($currentVolumeDirectory);

        // Sort the issues numerically
        sort($allIssues, SORT_NUMERIC);

        // Find the index of the current issue
        $currentIssueIndex = array_search($currentIssue, $allIssues);

        // Calculate the next and previous issue numbers
        $nextIssue = ($currentIssueIndex < count($allIssues) - 1) ? $allIssues[$currentIssueIndex + 1] : null;
        $previousIssue = ($currentIssueIndex > 0) ? $allIssues[$currentIssueIndex - 1] : null;

        // Generate full links for the next and previous issues
        $nextIssueLink = ($nextIssue !== null) ? $currentYear . '/' . $currentVolume . '/' . $nextIssue : null;
        $previousIssueLink = ($previousIssue !== null) ? $currentYear . '/' . $currentVolume . '/' . $previousIssue : null;

        // if next issue is null, then we need to check if there is a next volume
        if ($nextIssue === null) {
            // check if $currentVolume + 1 exists
            $nextVolumeDirectory = $rootDirectory . $currentYear . '/' . ($currentVolume + 1) . '/';
            if (is_dir($nextVolumeDirectory)) {
                // if it does, then we need to get the first issue of that volume
                $nextVolumeIssues = listIssues($nextVolumeDirectory);
                $nextIssueLink = $currentYear . '/' . ($currentVolume + 1) . '/' . $nextVolumeIssues[0];
            } else {
                // check if $currentYear + 1 exists
                $nextYearDirectory = $rootDirectory . ($currentYear + 1) . '/';
                if (is_dir($nextYearDirectory)) {
                    // if it does, then we need to get the first issue of that year
                    $nextYearVolumes = scandir($nextYearDirectory);
                    $nextYearVolumes = array_diff($nextYearVolumes, ['.', '..']);
                    $nextYearVolumes = array_reverse(array_reverse($nextYearVolumes));
                    $nextVolumeDirectory = $rootDirectory . ($currentYear + 1) . '/' . $nextYearVolumes[0] . '/';
                    $nextVolumeIssues = listIssues($nextVolumeDirectory);
                    $nextIssueLink = ($currentYear + 1) . '/' . $nextYearVolumes[0] . '/' . $nextVolumeIssues[0];
                }
            }
        }

        // if previous issue is null, then we need to check if there is a previous volume
        if ($previousIssue === null) {
            // check if $currentVolume - 1 exists
            $previousVolumeDirectory = $rootDirectory . $currentYear . '/' . ($currentVolume - 1) . '/';
            if (is_dir($previousVolumeDirectory)) {
                // if it does, then we need to get the last issue of that volume
                $previousVolumeIssues = listIssues($previousVolumeDirectory);
                $previousIssueLink = $currentYear . '/' . ($currentVolume - 1) . '/' . $previousVolumeIssues[count($previousVolumeIssues) - 1];
            } else {
                // check if $currentYear - 1 exists
                $previousYearDirectory = $rootDirectory . ($currentYear - 1) . '/';
                if (is_dir($previousYearDirectory)) {
                    // if it does, then we need to get the last issue of that year
                    $previousYearVolumes = scandir($previousYearDirectory);
                    $previousYearVolumes = array_diff($previousYearVolumes, ['.', '..']);
                    $previousYearVolumes = array_reverse($previousYearVolumes);
                    $previousVolumeDirectory = $rootDirectory . ($currentYear - 1) . '/' . $previousYearVolumes[0] . '/';
                    $previousVolumeIssues = listIssues($previousVolumeDirectory);
                    $previousIssueLink = ($currentYear - 1) . '/' . $previousYearVolumes[0] . '/' . $previousVolumeIssues[count($previousVolumeIssues) - 1];
                }
            }
        }
        
        return [
            'next' => $nextIssueLink,
            'previous' => $previousIssueLink
        ];

    }


}
