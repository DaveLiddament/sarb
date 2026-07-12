# SARB format

This document defines the `sarb.json` file format. If a static analysis tool provides an output in this format
it will work directly with SARB, using the input format `sarb-json`:

```shell
<static analysis tool> | vendor/bin/sarb create --input-format="sarb-json" tool.baseline
<static analysis tool> | vendor/bin/sarb remove tool.baseline
```


## Motivation

SARB could work with almost any static analysis tool. SARB needs to understand the static analysis tool's output.
There are 2 options:

- Write a [Results Parser](NewResultsParser.md) that translates the tool's output into something SARB understands
- Make the tool output data in the `sarb.json` format

In most cases it is probably easier to add support for outputting the `sarb.json` to the static analysis tool rather than writing a Results Parser.


## Format

The following information is needed for each issue the static analysis tool finds:

- Absolute path of the file. [string]
- Line number of the issue. [integer]
- Type (e.g. `MixedType`). [string]
- Message (e.g. `Can not assign $asArray to a mixed type`). [string]
- Severity either `error` or `warning`. This is optional, if not supplied then `error` is assumed. [string]

It is very important `Type` must not have file name, class name, function or line number in it. 


```json
[
  {
    "file": "/home/johnsmith/project/Controller/HomeController.php",
    "line": 10,
    "type": "MixedType",
    "message" : "Cannot assign $asArray to a mixed type",
    "severity": "error"
  },
  ... repeat for each issue ...   
]
```


## Relative path variant

If the static analysis tool reports paths relative to the project root (instead of absolute paths), use the
input format `sarb-relative-json`. The format is the same except each issue has a `relative_path` key instead
of `file`:

```json
[
  {
    "relative_path": "Controller/HomeController.php",
    "line": 10,
    "type": "MixedType",
    "message" : "Cannot assign $asArray to a mixed type",
    "severity": "error"
  },
  ... repeat for each issue ...   
]
```

See also [notes on results with relative paths](ResultsWithRelativePaths.md).

