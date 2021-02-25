<?php

$inputData = stream_get_contents(STDIN);
if ($inputData === false) die("Could not read input");

$asJson = json_decode($inputData, true);
if (!is_array($asJson)) die ("Could not parse JSON");

$issues = [];

foreach($asJson as $fileWithIssues) {

    $fileName = $fileWithIssues['filePath'];

    foreach($fileWithIssues['messages'] as $issue) {
        $issues[] = [
            'file' => $fileName,
            'line' => $issue['line'],
            'type' => $issue['ruleId'],
            'message' => $issue['message'],
        ];
    }
}

echo json_encode($issues, JSON_PRETTY_PRINT);
