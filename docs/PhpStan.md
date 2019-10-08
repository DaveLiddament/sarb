# Using SARB with PHPStan

Instructions for using SARB with [PHPStan](https://github.com/phpstan/phpstan).


## Install required software

```
composer require --dev phpstan/phpstan
composer require --dev dave-liddament/sarb
```

## Before creating the baseline

First fix all the issues you want to fix before creating the baseline. 

Make sure before creating the baseline that:

- all code is committed to git. Running `git status` should return with `nothing to commit, working tree clean` in the response.
- the current commit is the one you want to use as the baseline.

**This is very important as SARB uses the current git SHA when working out what code has changed from the baseline.** 


## Creating the baseline

Generate the output from PHPStan:
```
vendor/bin/phpstan analyse --error-format=json > /tmp/phpstan.json
```


Generate the SARB baseline:
```
vendor/bin/sarb create-baseline /tmp/phpstan.json phpstan.baseline phpstan-json-tmp
```

You should see a message along these lines:
```
Baseline created
Errors in baseline 2060
```

You'll probably want to check the baseline file into your repo, so CI or other developers can make use of it.


## Removing baseline from results

After you've created the baseline, edit the code. Once done run PHPStan and SARB again, 
this time removing the baseline to make sure you've not added any extra issues.

```
vendor/bin/phpstan analyse --error-format=json > /tmp/phpstan.json
vendor/bin/sarb remove-baseline /tmp/phpstan.json phpstan.baseline /tmp/phpstan-baseline-removed.json
```

This will return with a list of all the issues introduced since the baseline. 
You can also view the errors introduced since the baseline in the text format by looking at, 
in this example, the file `/tmp/phpstan-baseline-removed.json`



## Using PHPStan's text format

PHPStan's raw format is also supported by SARB. 

When running PHPStan use:
```
vendor/bin/phpstan analyse --error-format=raw > /tmp/phpstan.txt
```

And for creating SARB baselines:
```
vendor/bin/sarb create-baseline /tmp/phpstan.txt phpstan.baseline phpstan-text-tmp
```

And removing the SARB baseline:
```
vendor/bin/sarb remove-baseline /tmp/phpstan.txt phpstan.baseline /tmp/phpstan-baseline-removed.txt
```




