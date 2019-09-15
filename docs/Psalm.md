# Using SARB with Psalm

Instructions for using SARB with [Psalm](https://github.com/psalm/psalm).

Firstly check out Psalm's own [baseline functionality](https://psalm.dev/docs/running_psalm/dealing_with_code_issues/#using-a-baseline-file).

## Install required software

```
composer require --dev psalm/psalm
composer require --dev dave-liddament/sarb
```

## Before create the baseline

First fix all the issues you want to fix before creating the baseline. 

Make sure before creating the baseline that:

- all code is committed to git. Running `git status` should return with ` nothing to commit, working tree clean` in the response.
- the current commit is the one you want to use as the baseline.

**This is very important as SARB uses the current git SHA when working out what code has changed from the baseline.** 


## Creating the baseline

Generate the output from Psalm:
```
vendor/bin/psalm --report=/tmp/psalm.json
```


Generate the SARB baseline:
```
vendor/bin/sarb create-baseline /tmp/psalm.json psalm.baseline psalm-json
```

You should see a message along these lines:
```
Baseline created
Errors in baseline 2060
```

You'll probably want to check the baseline file into your repo, so CI or other developers can make use of it.


## Removing baseline from results

After you've created the baseline, edit the code. Once done run Psalm and SARB again, 
this time removing the baseline to make you've not added any extra issues.

```
vendor/bin/psalm --report=/tmp/psalm.json
vendor/bin/sarb remove-baseline /tmp/psalm.json psalm.baseline /tmp/psalm-baseline-removed.json
```

This will return with a list of all the issues introduced since the baseline. 
You can also view the errors introduced since the baseline in the text format by looking at, 
in this example, the file `/tmp/psalm-baseline-removed.json`



## Using Psalm's text format

Psalm's text format is also supported by SARB. 

When running Psalm use:
```
vendor/bin/psalm --report=/tmp/psalm.txt
```

And for creating SARB baselines:
```
vendor/bin/sarb create-baseline /tmp/psalm.txt psalm.baseline psalm-text-tmp
```

And removing the SARB baseline:
```
vendor/bin/sarb remove-baseline /tmp/psalm.txt psalm.baseline /tmp/psalm-baseline-removed.txt
```




