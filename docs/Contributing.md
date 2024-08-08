# Contributing to SARB

Contributions are welcome. 

If you want to support another static analysis tool then please read [Adding support for new static analysis tools / formats](NewResultsParser.md).



## Requirements for code created

Given SARB's use case it will probably be used with older code bases. 
For that reason the aim is to keep supported PHP versions and dependencies as wide as possible. 

### Supported PHP versions

SARB must support all PHP versions that are either in [active or security](https://www.php.net/supported-versions.php) support. 

Support for out of date versions of PHP should only be dropped if it is too difficult to keep them.

Please make sure that the code runs on the following PHP versions:
- 8.0
- 8.1
- 8.2
- 8.3


### Including new libraries

SARB's main dependencies are Symfony Components. 
SARB must work with all currently [supported](https://symfony.com/releases) Symfony versions (maintained and security fixes).

Unless there is a very good reason do NOT add any other libraries. 


## Code checks

After writing your code run code style fixer, this will automatically format the code to the project style:

```
composer cs-fix
```


Check all the CI tasks would run. NOTE you'll need to download the deptrac PHAR, see [instructions](https://github.com/qossmic/deptrac#installation):
```
composer ci-<php version>

# e.g.
composer ci-8.1
```

In addition to the above code coverage needs to 100%. 
Documented usage of `@codeCoverageIgnore` is allowed for the cases for lines of code where it is impossible to get test coverage.



