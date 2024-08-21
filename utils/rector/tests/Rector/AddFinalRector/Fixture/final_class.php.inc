<?php

namespace Rector\Tests\TypeDeclaration\Rector\AddFinalRector\Fixture;

final class AnotherClass
{
    public function run()
    {
        return 'run';
    }
}
?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\AddFinalRector\Fixture;

final class AnotherClass
{
    public function run()
    {
        return 'run';
    }
}
?>
