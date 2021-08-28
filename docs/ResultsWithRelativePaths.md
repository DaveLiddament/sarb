# Dealing with static analysis tools that report issues using relative file paths

SARB needs to match up the results from the static analyser and the SCM (e.g. git). 

If the static analyser give the absolute paths of the files containing issues then everything is fine. 
However, some static analysers, e.g. Phan, only provide relative paths to the files with issues. 
Depending on the directory layout you might need to supply `--relative-path-to-code` option when running SARB.

The _project root_ is the root directory for project. E.g. if using git then this is the directory that contains the `.git` folder.

If that static analysis results are reported relative to the _project root_ directory then you don't need to supply the `--relative-path-to-code` option.

If the static analysis results are relative to a child directory, then you need to supply the relative path between the _project root_ and the root directory of the code being analysed.
E.g. assume that you PHP code lived in a directory `backend` and you are using Phan, you'd need to supply `--relative-path-to-code=backend` when running SARB.


