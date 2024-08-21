<?php

namespace Rector\Tests\TypeDeclaration\Rector\AddFinalRector\Fixture;

class SomeClass
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

final class SomeClass
{
    public function run()
    {
        return 'run';
    }
}
?>
