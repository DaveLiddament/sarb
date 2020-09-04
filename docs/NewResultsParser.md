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
 - the above classes live under the namespace `dave-liddament\StaticAnalysisResultsBaseliner\plugins\PsalmJsonResultsParser`


### Identifier

The first thing to do is to create the class `PsalmJsonIdentifier`. This implements the `Identifier` interface.
It provide a method of returning the identifier string (`psalm-json`) and a longer description. E.g.

```php
declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PsalmJsonResultsParser;

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
}
```


### ResultsParser

The next thing to do is to create an implementation of a ResultsParser. The example of Psalm JSON ResultsParser will be used.

#### Method: getIdentifier

The first method to implement is `getIdentifer` this just returns an instance of the relevant `Identifer`class.

```php
declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Plugins\PsalmJsonResultsParser;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\Identifier;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\ResultsParser\ResultsParser;

class PsalmJsonResultsParser implements ResultsParser
{

    public function getIdentifer(): Identifer
    {
        return new PsalmJsonIdetifier;
    }

}
```


#### Method: convertFromString

The second method to implement should parse the results from the static analysis tool. In this case Psalm's JSON output.

The method that needs implementing looks like this:

```php


    /**
     * Takes a string representation of the static analysis results and converts to AnalysisResults.
     *
     * @param string $resultsAsString
     *
     * @throws ParseAtLocationException
     * @throws InvalidContentTypeException
     *
     * @return AnalysisResults
     */
    public function convertFromString(string $resultsAsString): AnalysisResults;

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


    public function convertFromString(string $resultsAsString): AnalysisResults;
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

                $location = new Location(
                    new FileName($fileNameAsString),
                    new LineNumber($lineAsInt)
                );

                $analysisResult =  new AnalysisResult(
                    $location,
                    new Type($typeAsString),
                    $message,
                    JsonUtils::toString($analysisResultAsArray)
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

The that does this is:

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

**NOTE:** The file path should be the absolute path. SARB stores the relative path in the baseline file, but the HistoryAnalyser needs the absolute path.

It does this like so:

```php
                $fileNameAsString = ArrayUtils::getStringValue($analysisResultAsArray, 'file_path');
                $lineAsInt = ArrayUtils::getIntValue($analysisResultAsArray, 'line_from');
                $typeAsString = ArrayUtils::getStringValue($analysisResultAsArray, 'type');
                $message = ArrayUtils::getStringValue($analysisResultAsArray, 'message');
```


SARB needs to capture all ths information and create an `AnalysisResult`.
As well as the information above SARB also needs a serialised version (as a string) of all the information.
This is needed to recreate a file that looks like Psalm's JSON output with the baseline results removed.

The easiest way to do this is to take the array that represents the entire violation and serialise it as a string:

```php
                $location = new Location(
                    new FileName($fileNameAsString),
                    new LineNumber($lineAsInt)
                );

                $analysisResult =  new AnalysisResult(
                    $location,
                    new Type($typeAsString),
                    $message,
                    JsonUtils::toString($analysisResultAsArray)
                );
```


Finally each individual `AnalysisResult` should be added to the `AnalysisResults`

```php
                $analysisResults->addAnalysisResult($analysisResult);
```


And that's it!

#### Method: convertToString

The next method to implement needs to convert `AnalysisResults` into a string that is in the same format of the static analysis tool's output.
`AnalysisResults` will hold only the violations that were not in the baseline.

In the case of Psalm JSON format this is simple.

```php
    /**
     * Create a string representation of the Analysis results (for persisting to a file).
     *
     * @param AnalysisResults $analysisResults
     *
     * @throws JsonParseException
     *
     * @return string
     */
    public function convertToString(AnalysisResults $analysisResults): string
    {
        $asArray = [];
        foreach ($analysisResults->getAnalysisResults() as $analysisResult) {
            $asArray[] = JsonUtils::toArray($analysisResult->getFullDetails());
        }

        return JsonUtils::toString($asArray);
    }
```

#### Method: showTypeGuessingWarning

The final method to implement just returns true or false.

```php
    /**
     * Returns true if the ResultsParser has to guess the violation type.
     *
     * See docs/ViolationTypeClassificationGuessing.md
     *
     * @return bool
     */
    public function showTypeGuessingWarning(): bool
    {
        return false;
    }
```

Read more about [guessing violation type classification](ViolationTypeClassificationGuessing.md).
In this example the static analysis tool provides a type so we are not guessing the
classification. So this will return false.
