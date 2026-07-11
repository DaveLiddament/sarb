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
- 8.2
- 8.3
- 8.4
- 8.5


### Including new libraries

SARB's main dependencies are Symfony Components. 
SARB must work with all currently [supported](https://symfony.com/releases) Symfony versions (maintained and security fixes).

Unless there is a very good reason do NOT add any other libraries. 


## Code checks

After writing your code run code style fixer, this will automatically format the code to the project style:

```
composer cs-fix
```


Check all the CI tasks would run:
```
composer ci-<php version>

# e.g.
composer ci-8.2
```

In addition to the above code coverage needs to 100%. 
Documented usage of `@codeCoverageIgnore` is allowed for the cases for lines of code where it is impossible to get test coverage.

## Docker 

A Dockerfile is provided to help with development.
There is a service for each supported PHP version: `php82`, `php83`, `php84`, `php85`.

Use `docker compose run --rm <service> <command>` to run a one-off command on a given PHP version.

E.g. to run `composer cs-fix` on PHP 8.2:

```shell
docker compose run --rm php82 composer cs-fix
```

See the composer scripts section for all scripts available.
E.g. to run the full CI checks on PHP 8.3:

```shell
docker compose run --rm php83 composer ci-8.3
```

You can also get shell access. E.g. to get a shell on PHP 8.3:

```shell
docker compose run --rm php83 bash
```

If you change the `Dockerfile`, force a rebuild with `docker compose build` (or add `--build` to the `run` command).


To check for all environments run the following:

```shell
docker compose run --rm php82 composer ci-8.2
docker compose run --rm php83 composer ci-8.3
docker compose run --rm php84 composer ci-8.4
docker compose run --rm php85 composer ci-8.5
```
