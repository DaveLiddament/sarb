<?php

namespace Rector\Tests\TypeDeclaration\Rector\AddFinalRector\Fixture;

abstract class AnotherClass
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

abstract class AnotherClass
{
    public function run()
    {
        return 'run';
    }
}
?>
