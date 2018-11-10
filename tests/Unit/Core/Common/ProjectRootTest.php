<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use LogicException;
use PHPUnit\Framework\TestCase;

class ProjectRootTest extends TestCase
{
    public function invalidProjectRootDataProvider(): array
    {
        return [
            'relativePath' => ['foo/bar'],
            'doubleDots' => ['/foo/bar/../baz'],
        ];
    }

    /**
     * @dataProvider invalidProjectRootDataProvider
     */
    public function testInvalidProjectRoot(string $invalidProjectRoot): void
    {
        $this->expectException(LogicException::class);
        new ProjectRoot($invalidProjectRoot);
    }

    public function testRemoveProjectRootNoTrailngSlash(): void
    {
        $projectRoot = new ProjectRoot('/foo/bar');
        $actual = $projectRoot->getPathRelativeToRootDirectory('/foo/bar/baz/hello.php');
        $this->assertSame('baz/hello.php', $actual);
    }

    public function testRemoveProjectRootWithTrailngSlash(): void
    {
        $projectRoot = new ProjectRoot('/foo/bar/');
        $actual = $projectRoot->getPathRelativeToRootDirectory('/foo/bar/baz/hello.php');
        $this->assertSame('baz/hello.php', $actual);
    }

    public function testPathNotInProjectRoot(): void
    {
        $this->expectException(LogicException::class);
        $projectRoot = new ProjectRoot('/foo/bar');
        $projectRoot->getPathRelativeToRootDirectory('bar/baz.php');
    }
}
