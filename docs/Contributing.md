# Contributing to SARB

Contributions are welcome. 

If you want to support another static analysis tool then please read [Adding support for new static analysis tools / formats](NewResultsParser.md).



## Requirements for code created

### Including new libraries

Unless there is a very good reason, it would be good NOT to include any other libraries. 

### Supported PHP versions

Please make sure that the code runs on the following PHP versions:
- 7.3
- 7.4
- 8.0
- 8.1


### Code checks

After writing your code run code style fixer, this will automatically format the code to the project style:

```
composer cs-fix
```


Check all the CI tasks would run. NOTE you'll need to download the deptrac PHAR, see [instructions](https://github.com/qossmic/deptrac#installation):
```
composer ci
```

In addition to the above code coverage needs to 100%. 
Documented usage of `@codeCoverageIgnore` is allowed for the cases for lines of code where it is impossible to get test coverage.

Also attempt to keep Infection PHP's [Mutation Score Indicator](https://infection.github.io/guide/#Mutation-Score-Indicator-MSI) (MSI) above 90%.


