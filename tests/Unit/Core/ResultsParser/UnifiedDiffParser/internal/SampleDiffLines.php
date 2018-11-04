<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\ResultsParser\UnifiedDiffParser\internal;

interface SampleDiffLines
{
    public const DIFF_START = 'diff --git a/src/Person.php b/src/Person.php';

    public const ORIGINAL_FILE_NAME = '--- a/src/Person.php';

    public const NEW_FILE_NAME = '+++ b/src/Student.php';

    public const RENAME_FROM = 'rename from src/Printer.php';

    public const RENAME_TO = 'rename to src/Foo.php';

    public const CHANGE_HUNK_START = '@@ -15,12 +15,18 @@ class Person';
}
