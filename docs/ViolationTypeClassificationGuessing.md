# Guessing the classification of a violation

SARB works by tracking classifications of a violation to a line of code.
See full details in [How SARB works](HowSarbWorks.md).

Ideally a static analysis tool would provide a classification and a message
for each violation it finds.

E.g.
 - Type: `MissingConstructor`
 - Message: `Demo\Employee has an uninitialized variable $this->age, but no constructor`

Sometimes static analysis tools only provide a message and no classification.
The problem with using the message is that between builds the mesage might change
but the fundamental violation remains the same.

E.g. Assume `Demo\Employee` was renamed to `Demo\Person` between builds.
The above message from that static analysis tool would change from:

`Demo\Employee has an uninitialized variable $this->age, but no constructor`

to

`Demo\Person has an uninitialized variable $this->age, but no constructor`

However, this is still fundamentally the same issue (`MissingConstructor`).


In cases where static analysis tools only provide a message,
SARB will try to remove anything from the message that might change
but is not fundamental to the issue being reported. In the case of
PHP static analysers it will remove anything that looks like a
fully qualified class name (FQCN). So the above example after stripping out the FQCN would go from:

`Demo\Employee has an uninitialized variable $this->age, but no constructor`

to:

`has an uninitialized variable $this->age, but no constructor`


So even after renaming a class from `Demo\Employee` to `Demo\Person` the type
recorded in both cases is `has an uninitialized variable $this->age, but no constructor`.
This allows SARB to correctly identify the issue and track it between builds.

## A better solution

The best solution would be for the static analysis tool to provide a classification
of each violation it finds. If the tool you use doesn't then please
encourage the authors (maybe by supplying a PR) to add this to the output from
their tools.

