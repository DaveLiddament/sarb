# How SARB works

SARB does 2 main tasks.

 - Create baseline.
 - Remove baseline results from static analysis results run after the baseline.

SARB with the help of an SCM (by default git) tracks the lines of code between the baseline and the current commit.


## Creating a baseline

When creating a baseline SARB records:
  - the SHA of the current git commit
  - the static analysis tool used (e.g. psalm-json)

It then iterates through each of the violations that the static analysis tool found.
For each violation it records:
 - the location (filename and line number)
 - type (e.g. PossibleNullValue)
 - serialised version of the violation (to include all other information about violation)


Assume we have the following code that we run through [Psalm](https://getpsalm.org/r/23e7f1edb2) at it's most strict level.

**Filename: `src/Person.php`**

```php
<?php

class Person
{
  /**
   * @var string|null
   */
  private $name;

  public function setName(?string $name): void
  {
    $this->name = $name;
  }

  public function getName(): string
  {
    return $this->name;
  }
}
```

Psalm will find 2 issues. Using the Psalm JSON **Results Parser** the issues get recorded in the baseline like this:

Filename | Line number | Type | Full Details
---------|-------------|------|-------------
src/Person.php|15|InvalidNullableReturnType| full error serialised as a string
src/Person.php|17|NullableReturnStatement| full error serialised as a string


SARB will also record the following information in the baseline:

Static analysis tool|History analyser|History marker
--------------------|----------------|--------------
psalm-json|git|9c1f15548dec012393acce4672940e13

This first 2 entries define the **Results Parser** and **History Analyser** used to create the baseline.
The History Marker is, in this case, the git SHA of the codebase at the point the baseline was made.

## Removing baseline results

After the baseline is created further work is done. Assume the file above is renamed from `Person` to `Employee`.
Also we add an extra property along with getters and setters.  The code now looks like this:

**Filename: `src/Employee.php`**

```php
<?php

class Employee
{
  /**
   * @var int
   */
  private $age;

  /**
   * @var string|null
   */
  private $name;

  public function setAge(int $age): void
  {
    $this->age = $age;
  }

  public function getAge(): int
  {
    return $this->age;
  }

  public function setName(?string $name): void
  {
    $this->name = $name;
  }

  public function getName(): string
  {
    return $this->name;
  }
}
```

Running through [Psalm](https://getpsalm.org/r/87a57f213c) in its most strict mode would yield 3 issues.

The first thing that SARB does when removing the baseline results is to read in the baseline file.
It then reads in the results form the latest static analysis results, again using the relevant **Results Parser**.

The results for the latest set of static analysis results look like this:

Filename | Line number | Type | Full Details
---------|-------------|------|-------------
src/Employee.php|8|MissingConstructor| full error serialised as a string
src/Employee.php|30|InvalidNullableReturnType| full error serialised as a string
src/Employee.php|32|NullableReturnStatement| full error serialised as a string

SARB then iterates through each of the issues raised.
For each issue SARB uses a **History Analyser** to see if the issue was in the baseline.

The first issue is of type `MissingConstructor` at location `src/Employee.php:8`.
SARB asks the History Analyser what the location of `src/Employee.php:8` was in the baseline.
As this is new code, the History Analyser will report that this location is not in the baseline.
SARB therefore knows that this is an issue created since the baseline and must be reported as a new issue.

The second issue is of type `InvalidNullableReturnType` at location `src/Employee.php:30`.
Again SARB asks the History Analyser where this location was in the baseline.
Using a bit of git magic the History Analyser responds with `src/Person.php:15`.
SARB then looks in the baseline to see if there was an issue of type `InvalidNullableReturnType` at location `src/Person.php:15`.

As a reminder here is what in the baseline:

Filename | Line number | Type | Full Details
---------|-------------|------|-------------
src/Person.php|15|InvalidNullableReturnType| full error serialised as a string
src/Person.php|17|NullableReturnStatement| full error serialised as a string

And it's possible to see that there is an issue of type `InvalidNullableReturnType` at location `src/Person.php:15`.
SARB assumes this is an existing issue and doesn't report it.

Once SARB has found all the issues introduced since the baseline it then uses
the **Results Parser** to convert back to the output format of which ever static analysis tool was used.


