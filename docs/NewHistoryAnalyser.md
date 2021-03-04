# Adding support for SCMs other than git

SARB uses an SCM to answer the question: "Where was line number X for file Y at the time the baseline was created".
There are 2 possible answers:
- It wasn't in the baseline
- It was line A in file B


To support another SCM the following interfaces need implementing:

- HistoryMarker
- HistoryMarkerFactory  
- HistoryAnalyser  
- HistoryFactory


## HistoryMarker

[src/Domain/HistoryAnalyser/HistoryMarker.php](../src/Domain/HistoryAnalyser/HistoryMarker.php)

This holds a string representation of the point where the baseline was taken. E.g. for git this is the git SHA of the baseline.

## HistoryMarkerFactory

[src/Domain/HistoryAnalyser/HistoryMarkerFactory.php](../src/Domain/HistoryAnalyser/HistoryMarkerFactory.php)

Responsible for creating a _HistoryMarker_.


## HistoryAnalyser

[src/Domain/HistoryAnalyser/HistoryAnalyser.php](../src/Domain/HistoryAnalyser/HistoryAnalyser.php)

This is used to answer the question: "Where was line number X for file Y at the time the baseline was created".


## HistoryFactory

[src/Domain/HistoryAnalyser/HistoryFactory.php](../src/Domain/HistoryAnalyser/HistoryFactory.php)

This is responsible for building the _HistoryAnalyser_ and _HistoryMarkerFactory_.
