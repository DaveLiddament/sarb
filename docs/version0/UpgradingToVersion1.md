# Upgrading to Version 1

There are 2 steps required:

1. Run the upgrade tool on the existing baseline file

```shell
vendor/bin/sarb upgrade-from-version-0 <baseline file>
```

You should see a message that says "Baseline updated" and further instructions for how to update scripts.

*NOTE:* The updates will expect results to output in the JSON format from whichever tool you were using. 

2. Update any scripts that use SARB to use the updated CLI 

E.g. Version 0.x:
```shell
vendor/bin/psalm --report=reports/latest_psalm_issues.json
vendor/bin/sarb remove-baseline-results \
                reports/latest_psalm_issues.json \
                reports/sarb_baseline.json \
                reports/issues_since_baseline.json
```

Version 1:
```shell
vendor/bin/psalm --output-format=json | vendor/bin/sarb remove reports/sarb_baseline.json
```
