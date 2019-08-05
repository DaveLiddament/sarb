# Using SARB with PHP CodeSniffer

Instructions for using SARB with [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer).


## Install required software

```
composer require --dev squizlabs/php_codesniffer
composer require --dev daveliddament/sarb
```

## Before create the baseline

First fix all the issues you want to fix before creating the baseline. 

Make sure before creating the baseline that:

- all code is committed to git. Running `git status` should return with ` nothing to commit, working tree clean` in the response.
- the current commit is the one you want to use as the baseline.

**This is very important as SARB uses the current git SHA when working out what code has changed from the baseline.** 


## Creating the baseline

Generate the output from PHP CS:
```
vendor/bin/phpcs src/ > /tmp/phpcs.txt
```


Generate the SARB baseline:
```
vendor/bin/sarb create-baseline /tmp/phpcs.txt phpcs.baseline phpcodesniffer-full
```

You should see a message along these lines:
```
Baseline created
Errors in baseline 2060
```

You'll probably want to check the baseline file into your repo, so CI or other developers can make use of it.


## Removing baseline from results

After you've created the baseline, edit the code. Once done run PHP CodeSniffer and SARB again, 
this time removing the baseline to make you've not added any extra issues.

```
vendor/bin/phpcs src/ > /tmp/phpcs.txt
vendor/bin/sarb remove-baseline /tmp/phpcs.txt phpcs.baseline /tmp/phpcs-baseline-removed.txt
```

This will return with a list of all the issues introduced since the baseline. 
You can also view the errors introduced since the baseline in the text format by looking at, 
in this example, the file `/tmp/phpcs-baseline-removed.txt`



