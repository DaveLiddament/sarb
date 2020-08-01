# Static Analysis Results Baseliner (SARB)

[![Code quality](https://github.com/DaveLiddament/sarb/workflows/Full%20checks/badge.svg)](https://github.com/DaveLiddament/sarb) 
[![Tests](https://github.com/DaveLiddament/sarb/workflows/Tests/badge.svg)](https://github.com/DaveLiddament/sarb) 
[![Security](https://github.com/DaveLiddament/sarb/workflows/Security/badge.svg)](https://github.com/DaveLiddament/sarb) 
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/DaveLiddament/sarb/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/DaveLiddament/sarb/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/dave-liddament/sarb/v/stable)](https://packagist.org/packages/dave-liddament/sarb)
[![Total Downloads](https://poser.pugx.org/dave-liddament/sarb/downloads)](https://packagist.org/packages/dave-liddament/sarb)
[![Type coverage](https://shepherd.dev/github/DaveLiddament/sarb/coverage.svg)](https://shepherd.dev/github/DaveLiddament/sarb/coverage.svg)
[![Code Coverage](https://codecov.io/gh/DaveLiddament/sarb/branch/master/graph/badge.svg)](https://codecov.io/gh/DaveLiddament/sarb)
[![License](https://poser.pugx.org/dave-liddament/sarb/license)](https://packagist.org/packages/dave-liddament/sarb)

**This is still in beta.**

 * [Why SARB](#why-sarb)
 * [Requirements](#requirements)
 * [Installing](#installing)
 * [Using SARB](#using-sarb)
 * [Examples](#examples)
 * [Further reading](#further-reading)

## Why SARB?

If you've tried to introduce advanced static analysis tools (e.g.
[Psalm](https://getpsalm.org), [PHPStan](https://github.com/phpstan/phpstan))
to legacy projects the tools have probably reported thousands of problems.
It's unrealistic to fix all but the most critical ones before continuing development.

SARB is used to create a baseline of these results. As work on the project
progresses SARB can takes the latest static analysis results, removes
those issues in the baseline and report the issues raised since the baseline.
SARB does this, in conjunction with git, by tracking lines of code between commits.
Currently SARB only supports git but it is possible to [add support for other SCMs](docs/NewHistoryAnalyser.md).

SARB is written in PHP, however it can be used to baseline results for any language and any static analysis tool.


#### Why not SARB?

SARB should not be used on greenfield projects. If you're lucky enough to work on a greenfield project make sure you fix all problems raised by static analysis as you go along.

## Requirements

Currently SARB only supports projects that use [git](https://git-scm.com/).

SARB requires PHP >= 7.1 to run. The project being analysed does not need to run PHP 7.1 or even be a PHP project at all.

## Installing

You can either add directly to the project you wish to run analysis on:

```
composer require --dev dave-liddament/sarb
```

Or you can install SARB globally (e.g. if you want to use it on a non PHP project):

```
composer global require dave-liddament/sarb
```

If you install globally make sure the composer bin directory is in your path.


## Using SARB

#### 1. Make sure the current git commit is the one to be used in the baseline

When creating the baseline SARB needs to know the git commit SHA of the baseline.
So make sure your code is in the state you want it to be in for the baseline and that the current commit represents that state.


#### 2. Run the static analyser

Run the static analyser and output results to a file.

E.g. with using the JSON format for Psalm:
```
vendor/bin/psalm --report=reports/baseline_psalm_issues.json
```

It is this output that will be used to create the baseline.


**NOTE:** Make sure that both the tool and format are supported. To get a list use:

```
vendor/bin/sarb list-static-analysis-tools
```

If your tool or format is in this list then create your own ResultsParser.


#### 3. Create the baseline

If you are running SARB within your project then run this command:
```
vendor/bin/sarb create-baseline \
                reports/baseline_psalm_issues.json \
                reports/sarb_baseline.json \
                psalm-json
```

Breaking this down:

 * Firstly we are specifying that we wish to create a baseline with `create-baseline`.
 * Now specify the initial baseline results from the static analysis tool. In this example `reports/baseline_psalm_issues.json`
 * Then specify SARB's baseline output. Here `reports/sarb_baseline.json`
 * Finally specify the static analysis tool. This is a combination of both tool and format. Here it is `psalm-json`.

**NOTE: SARB will create the baseline and record the git SHA that the project is currently at. Make sure this is correct.**

If you are running SARB in standalone mode then you need 1 extra option:

```
./sarb create-baseline \
       --project-root=path/to/project/root \
       reports/baseline_psalm_issues.json \
       reports/sarb_baseline.json \
       psalm-json
```

You must specify the option `--project-root`. This must point to the root of the project (where the `.git` directory lives).

#### 4. Continue coding and run the static analysis

Continue coding. When done:

 * Commit the code
 * Rerun the static analysis on the latest code. e.g.:

```
vendor/bin/psalm --report=reports/latest_psalm_issues.json
```


#### 5. Use SARB to remove baseline results

If you are running SARB within your project then run this command:
```
vendor/bin/sarb remove-baseline-results \
                reports/latest_psalm_issues.json \
                reports/sarb_baseline.json \
                reports/issues_since_baseline.json
```

Breaking this down:

 * Firstly we are specifying that we wish to remove issues from the baseline with `remove-baseline-results`.
 * Then specify the latest results from the static analysis tool. In this example `reports/latest_psalm_issues.json`
 * Now specify SARB's baseline output. Here `reports/sarb_baseline.json`
 * Finally we specify the output file. This will be in the same format as the output from the static analysis, but will only contain the issues introduced since the baseline.


As before if you are running SARB in standalone mode then you need 1 extra option:
```
    --project-root=path/to/project/root
```

If you are running this in CI then you can add the flag `-f`. This means a none zero return code is returned if any issues have been introduced sinde the baseline.

## Examples

 * [Exakat](https://www.exakat.io/exakat-1-8-3-review/)
 * [Phan](docs/Phan.md)
 * [PHP CodeSniffer](docs/PhpCodeSniffer.md)
 * [PHPStan](docs/PhpStan.md)
 * [Psalm](docs/Psalm.md)


## Further Reading
 
 * [How SARB works](docs/HowSarbWorks.md)
 * [Adding support for new static analysis tools / format](docs/NewResultsParser.md)
 * [Adding support for SCMs other than git](docs/NewHistoryAnalyser.md)
 * [How to contribute](docs/Contributing.md)
 * [Unified Diff Terminology](docs/UnifiedDiffTerminology.md)
 * [SARB format](docs/SarbFormat.md)


## Authors

 * [Dave Liddament](https://www.daveliddament.co.uk) [@daveliddament](https://twitter.com/daveliddament)
 * [Community contributors](https://github.com/daveliddament/sarb/graphs/contributors)
