# Supporting other static analysis tools or output formats

If the static analysis tool or output format is not currently supported there are 3 options:

- The simplest thing is to write a script to convert the tool's output to the SARB format (see below).
- Add support to SARB by creating a [ResultsParser](NewResultsParser.md).
- See if the tool will add support for the [SARB format](SarbFormat.md).

## Write a script to convert tool's output to the SARB format

The output from tool is piped into the converter (e.g. `tool2sarb`) which is then piped into SARB:

```shell
tool | tool2sarb | sarb create tool.baseline
```

This example will show how to write a [simple PHP script](../example/eslint2sarb.php) to convert ESLint's [JSON format](https://eslint.org/docs/user-guide/formatters/#json) to the S[SARB format](SarbFormat.md).

Here is a snippet from ESLint's JSON output...

```json
[
  {
    "filePath": "/var/lib/jenkins/workspace/Releases/eslint Release/eslint/fullOfProblems.js",
    "messages": [
      {
        "ruleId": "no-unused-vars",
        "severity": 2,
        "message": "'addOne' is defined but never used.",
        "line": 1,
        "column": 10,
        "nodeType": "Identifier",
        "messageId": "unusedVar",
        "endLine": 1,
        "endColumn": 16
      } ,
      ... next issue in file ...
    ]
  }, 
  ... next file ...
]
```

The [SARB format](SarbFormat.md) is JSON. For each issue the following information is needed:

SARB field name | Description | Field mapped to in ESLint's output
----|-------------|------------------------------------
`file` | Full path to file | `filePath`
`line` | Line number of issue | `line`
`type` | Type of issue (e.g. rule that was violated | `ruleId`
`message` | Human readable description of problem | `message`

The script needs to do the following:

1. Read JSON input from STDIN
1. Convert JSON to an array
1. Loop through the outer array (which has an entry for each file)
1. Loop through the `messages` for each file to get the file's issues. 
1. Add these to an array of issues.
1. Output array of issues as JSON.


```php
<?php
// See `example/eslint2sarb.php`

// 1. Read JSON input from STDIN 
$inputData = stream_get_contents(STDIN);
if ($inputData === false) die("Could not read input");

// 2.Convert JSON to an array 
$asJson = json_decode($inputData, true);
if (!is_array($asJson)) die ("Could not parse JSON");

$issues = [];

// 3. Loop through the outer array (which has an entry for each file) 
foreach($asJson as $fileWithIssues) {

    $fileName = $fileWithIssues['filePath'];

    // 4. Loop through the `messages` for each file to get the file's issues. 
    foreach($fileWithIssues['messages'] as $issue) {
    
        // 5. Add these to an array of issues.
        $issues[] = [
            'file' => $fileName,
            'line' => $issue['line'],
            'type' => $issue['ruleId'],
            'message' => $issue['message'],
        ];
    }
}

// 6. Output array of issues as JSON. 
echo json_encode($issues, JSON_PRETTY_PRINT);
```

Combining simple scripts like the above to convert to the SARB format gives you the ability to use almost any tool for any language.
