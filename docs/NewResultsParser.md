# Adding support for new static analysis tools / formats

**Results Parsers** convert the output from static analysis tools into a format that SARB can understand.
To support a new static analysis tool a new Results Parser is required.
This document explains how to create a ResultsParser.

**NOTE:** It might be easier to get the static analysis tool to output results in the `sarb.json` [format](SarbFormat.md).  

## Overview

Results Parses are specific to both:

 - the static analysis tool (e.g. Psalm)
 - the output format (e.g. JSON)

So to parse the JSON output from Psalm and to parse the XML output from Psalm would require 2 different ResultsParsers.


The job of a ResultsParser is to parse the output from a static analysis tool.
For each violation that is found the following information needs to be extracted:

 - Filename violation occurred.
 - Line number violation occurred.
 - Type of violation (e.g. `PossibleNullReturn`),
 - A human readable description of the issue (e.g. `Cannot assign $asArray to a mixed type`)
 - Serialised version of violation. This is not used by SARB itself but is needed when reconstructing the analysis results with the baseline removed.



## Writing a new ResultsParser

By convention a ResultsParser is named by the static analysis tool and then the format.

Assume we have a results parser to handle Psalm's JSON output..:

 - the identifier is `psalm-json`
 - the name of the ResultsParser class is `PsalmJsonResultsParser`
 - the name of the Identifier class is `PsalmJsonIdentifier`
 - the above classes live under the namespace `dave-liddament\StaticAnalysisResultsBaseliner\plugins\ResultsParsers\PsalmJsonResultsParser`


### Identifier

The first thing to do is to create the class `PsalmJsonIdentifier`. This implements the `Identifier` interface.
It provides:
- a method of returning the identifier string (`psalm-json`)
- a longer human readable description (`Psalm results (JSON format)`)
- how to run the tool to give the output in the desired format (`psalm --output-format=json`) 
  This is shown when running  `sarb list-static-analysis-tools`

E.g. 

```php
declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PsalmJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;

class PsalmJsonIdentifier implements Identifier
{

    public function getCode(): string
    {
        return 'psalm-json';
    }

    public function getDescription(): string
    {
        return 'Psalm results (JSON format)';
    }
    
    public function getToolCommand() : string
    {
        return 'psalm --output-format=json';
    }
}
```


### ResultsParser

The next thing to do is to create an implementation of a ResultsParser. The example of Psalm JSON ResultsParser will be used.

#### Method: getIdentifier

The first method to implement is `getIdentifier` this just returns an instance of the relevant `Identifier`class.

```php
declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\ResultsParsers\PsalmJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;

class PsalmJsonResultsParser implements ResultsParser
{

    public function getIdentifier(): Identifier
    {
        return new PsalmJsonIdentifier();
    }

}
```


#### Method: convertFromString

The second method to implement should parse the results from the static analysis tool. In this case Psalm's JSON output.

The method that needs implementing looks like this:

```php


    /**
     * Takes a string representation of the static analysis results and converts to AnalysisResults.
     */
    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults;

}
```


This is what Psalm's JSON output looks like:

```php
[
    {
        "severity":"error",
        "line_from":29,
        "line_to":29,
        "type":"MixedAssignment",
        "message":"Cannot assign $asArray to a mixed type",
        "file_name":"src\/Domain\/Utils\/JsonUtils.php",
        "file_path":"\/vagrant\/static-analysis-baseliner\/src\/Domain\/Utils\/JsonUtils.php",
        "snippet":"        $asArray = json_decode($jsonAsString, true);",
        "selected_text":"$asArray",
        "from":635,
        "to":643,
        "snippet_from":627,
        "snippet_to":679,
        "column_from":9,
        "column_to":17
    },

    ... next violation ...

]
```


A valid implementation to do this would be this...

```php


    public function convertFromString(string $resultsAsString, ProjectRoot $projectRoot): AnalysisResults;
    {
        try {
            $analysisResultsAsArray = JsonUtils::asArray($resultsAsString);
        } catch (JsonParseException $e) {
            throw new InvalidContentTypeException('Not a valid JSON format');
        }

        $analysisResults = new AnalysisResults();

        $resultsCount = 0;

        foreach ($analysisResultsAsArray as $analysisResultAsArray) {

            $resultsCount++;

            try {
                ArrayUtils::assertArray($analysisResultAsArray);

                $fileNameAsString = ArrayUtils::getStringValue($analysisResultAsArray, 'file_name');
                $lineAsInt = ArrayUtils::getIntValue($analysisResultAsArray, 'line_from');
                $typeAsString = ArrayUtils::getStringValue($analysisResultAsArray, 'type');
                $message = ArrayUtils::getStringValue($analysisResultAsArray, 'message');
                $severityAsString = ArrayUtils::getStringValue($analysisResultAsArray, 'severity');

                $location = Location::fromAbsoluteFileName(
                    new AbsoluteFileName($fileNameAsString),
                    $projectRoot,
                    new LineNumber($lineAsInt)
                );

                $severity = ($severityAsString === 'error') ? Severity::error() : Severity::warning();
                
                $analysisResult =  new AnalysisResult(
                    $location,
                    new Type($typeAsString),
                    $message,
                    $analysisResultAsArray,
                    $severity
                );

                $analysisResults->addAnalysisResult($analysisResult);

            } catch (ArrayParseException | JsonParseException $e) {
                throw new ParseAtLocationException("Result [$resultsCount]", $e);
            }
        }

        return $analysisResults;
    }


```


Breaking this down...

SARB reads the static analysis results in as a string. The first thing to do is to convert the string to an array.
We can use SARB's `JsonUtils::toArray` method. This takes a string and returns and `array` representation.
NOTE: If the file provided is not a JSON representation then `convertFromString` must throw an `InvalidContentTypeException`.
SARB catches this and asks the user if they submitted the correct file.

The code that does this is:

```php
    try {
        $asArray = JsonUtils::asArray($resultsAsString);
    } catch (JsonParseException $e) {
        throw new InvalidContentTypeException('Not a valid JSON format');
    }
```


Next an instance of `AnalysisResults` is created. This is what is returned from the `convertFromString` method.

```php
        $analysisResults = new AnalysisResults();
```


Now we iterate through the JSON array:


```php
        $resultsCount = 0;

        foreach ($analysisResultsAsArray as $analysisResultAsArray) {

            $resultsCount++;

            try {

                // Code to pull out data for each result

            } catch (ArrayParseException | JsonParseException $e) {
                throw new ParseAtLocationException("Result [$resultsCount]", $e);
            }
        }
```

Any exceptions to do with parsing should be caught and rethrown as `ParseAtLocationException`.
If some kind of parsing error occurs it is probably due to the fact that the incorrect file has been
specified. SARB will ask the user if they supplied the correct file.


`$analysisResultAsArray` is an array that holds information about a single violation.

The first thing to do is check that `$analysisResultAsArray` is actually an array.
If it isn't then probably the wrong file has been specified.

```php
                ArrayUtils::assertArray($analysisResultAsArray);
```

`ArrayUtils` methods throw `ArrayParseException` if the argument is not of the correct type.


SARB needs to pull out:

 - file path (`file_path` in Psalm's JSON output)
 - line number (`line_from` in Psalm's JSON output)
 - type (`type` in Psalm's JSON output)
 - message (`message` in Psalm's JSON output)
 - severity (`severity` in Psalm's JSON output). NOTE: Report a severity of `error` if there is no concept of severity.

**NOTES:** 

1. `Type` must refer to the type of violation (e.g. `MissingConstructor`). See more about this at [How SARB works](HowSarbWorks.md)
1. Ideally the file path should be the absolute path. SARB stores the relative path in the baseline file, but the HistoryAnalyser needs the absolute path. If the static analysis tool does not provide an absolute path then a relative path can be used, see [using a relative path](#using-relative-paths).
2. 
Here is the code to pull the information from the array:

```php
                $fileNameAsString = ArrayUtils::getStringValue($analysisResultAsArray, 'file_path');
                $lineAsInt = ArrayUtils::getIntValue($analysisResultAsArray, 'line_from');
                $typeAsString = ArrayUtils::getStringValue($analysisResultAsArray, 'type');
                $message = ArrayUtils::getStringValue($analysisResultAsArray, 'message');
                $severityAsString = ArrayUtils::getStringValue($analysisResultAsArray, 'severity');
```

The final piece of information that SARB takes is an array containing all the data from the tool about the particular violation. 
This allows tool specific output formatters to be written to output additional information if needed.
E.g. PHP-CS gives additional fields e.g. is_fixable. If this data needs to be shown to end user then a custom output formatter could be written to give all this additional information.


SARB needs to capture all this information and create an `AnalysisResult`.
```php
                $location = new Location(
                    new AbsoluteFileName($fileNameAsString),
                    $projectRoot,
                    new LineNumber($lineAsInt)
                );

                $severity = ($severityAsString === 'error') ? Severity::error() : Severity::warning();

                $analysisResult =  new AnalysisResult(
                    $location,
                    new Type($typeAsString),
                    $message,
                    $analysisResultAsArray,
                    $severity
                );
```


Finally each individual `AnalysisResult` should be added to the `AnalysisResults`

```php
                $analysisResults->addAnalysisResult($analysisResult);
```


And that's it!


#### Method: showTypeGuessingWarning

The final method to implement just returns true or false.

```php
    /**
     * Returns true if the ResultsParser has to guess the violation type.
     *
     * See docs/ViolationTypeClassificationGuessing.md
     */
    public function showTypeGuessingWarning(): bool
    {
        return false;
    }
```

Read more about [guessing violation type classification](ViolationTypeClassificationGuessing.md).
In this example the static analysis tool provides a `type` so we are not guessing the
classification. So this will return false.


## Using relative paths

Using relative paths is less ideal than using absolute paths. See [Results with relative paths](ResultsWithRelativePaths.md). 
If the relative paths are not relative then use the following code for creating the `Location` object.

```php
        $relativeFileNameAsString = ArrayUtils::getStringValue($analysisResult, 'relative_file_path');
        $lineAsInt = ArrayUtils::getIntValue($analysisResult, 'line_number');

        $location = Location::fromAbsoluteFileName(
            new RelativeFileName($relativeFileNameAsString),
            $projectRoot,
            new LineNumber($lineAsInt)
        );
```

## Passing on errors from the static analysis tool

If the static analysis tool reports an error that means it could not run successfully, then throw the `ErrorReportedByStaticAnalysisTool` exception from the `convertFromString` method.

This will cause SARB to report the error to the user and exit with a non-zero exit code. 
See the [`PhpstanJsonResultsParser`](../src/Plugins/ResultsParsers/PhpstanJsonResultsParser/PhpstanJsonResultsParser.php) for an example of this.


