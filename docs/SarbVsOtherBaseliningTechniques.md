# How SARB compares to other baselining tools (e.g. Psalm and PHPStan baseline functions)

SARB, in conjunction with git, tracks issues to the line of code they appear on. 
Git is very clever; it can track lines of code even if the file they are in is renamed or moved. 
This means that SARB tracks baseline issues even if the file they are in is moved elsewhere.

The baseliners used in [Psalm](https://psalm.dev/docs/running_psalm/dealing_with_code_issues/#using-a-baseline-file) 
and [PHPStan](https://phpstan.org/user-guide/baseline) track issues based on the file they appear in. 
If the file changes or is moved then issues there were in the baseline will be flagged as new issues. 


## When should I use SARB?

SARB is an excellent tool if any of the following are true:

- The tool you are using doesn't have a baseline feature.
- A lot of the refactoring you are doing is moving or renaming files.


## When should I use a tool's internal baseline feature?

You should use your existing tool's baseline feature if any of the following are true:

- You are already using it and don't have any major issues with it.
- You are not using git.
- Most of the code in the baseline is unlikely to be moved or renamed. 


# Show me an example of the differences between the 2 methods

This examples PHPStan, Psalm works in a similar way.

### Initial code

Assume initially the code being analysed in `src/Person.php` is:
```php
<?php

namespace App;

class Person
{
    private ?string $name;
    
    public function setName(?string $name)
    {
        $this->name = $name;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}
```

Running PHPStan on level 8 produces the following issues: 

```
 ------ ---------------------------------------------------------------------------- 
  Line   Person.php                                                                  
 ------ ---------------------------------------------------------------------------- 
  11     Method App\Person::setName() has no return typehint specified.              
  19     Method App\Person::getName() should return string but returns string|null.  
 ------ ---------------------------------------------------------------------------- 
```

### Creating the baseline

Creating a baseline and rerunning PHPStan (with the baseline) shows no issues.
Creating a SARB baseline and running SARB also shows no new issues:


### Updating the code (SARB and PHPStan give same results)
Assuming the code is updated the code to add a Person's job. `src/Person.php` becomes:

```php
<?php

namespace App;

class Person
{
    private ?string $job;
    
    public function setJob(?string $job)
    {
        $this->job = $job;
    }
    
    public function getJob(): string
    {
        return $this->job;
    }
    
    private ?string $name;
    
    public function setName(?string $name)
    {
        $this->name = $name;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}
```
Rerunning PHPStan with its baseline gives only the newly introduced issues:

**PHPStan output**
```
 ------ --------------------------------------------------------------------------- 
  Line   Person.php                                                                 
 ------ --------------------------------------------------------------------------- 
  11     Method App\Person::setJob() has no return typehint specified.              
  19     Method App\Person::getJob() should return string but returns string|null.  
 ------ --------------------------------------------------------------------------- 
```

SARB highlights the same issues:

**SARB output**
```
Latest analysis issue count: 4
Baseline issue count: 2
Issue count with baseline removed: 2

FILE: /home/vagrant/phpstan-demo/src/Person.php
+------+---------------------------------------------------------------------------+
| Line | Description                                                               |
+------+---------------------------------------------------------------------------+
| 11   | Method App\Person::setJob() has no return typehint specified.             |
| 19   | Method App\Person::getJob() should return string but returns string|null. |
+------+---------------------------------------------------------------------------+
```

### Renaming a file (SARB and PHPStan differ)

Now the `Person` class is updated to `Employee` so `src/Person.php` is moved to `src/Employee.php`:

```php
<?php

namespace App;

class Employee
{
    private ?string $job;
    
    public function setJob(?string $job)
    {
        $this->job = $job;
    }
    
    public function getJob(): string
    {
        return $this->job;
    }
    
    private ?string $name;
    
    public function setName(?string $name)
    {
        $this->name = $name;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}
```

PHPStan (with its baseline) shows all 4 issues:
```
 ------ ------------------------------------------------------------------------------ 
  Line   Employee.php                                                                  
 ------ ------------------------------------------------------------------------------ 
  11     Method App\Employee::setJob() has no return typehint specified.               
  19     Method App\Employee::getJob() should return string but returns string|null.   
  25     Method App\Employee::setName() has no return typehint specified.              
  33     Method App\Employee::getName() should return string but returns string|null.  
 ------ ------------------------------------------------------------------------------ 
 ```

Where as SARB only shows the 2 new issues:

```
Latest analysis issue count: 4
Baseline issue count: 2
Issue count with baseline removed: 2

FILE: /home/vagrant/phpstan-demo/src/Employee.php
+------+-----------------------------------------------------------------------------+
| Line | Description                                                                 |
+------+-----------------------------------------------------------------------------+
| 11   | Method App\Employee::setJob() has no return typehint specified.             |
| 19   | Method App\Employee::getJob() should return string but returns string|null. |
+------+-----------------------------------------------------------------------------+
```


