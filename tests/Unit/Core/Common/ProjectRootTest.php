<?php

declare(strict_types=1);

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Common;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\AbsoluteFileName;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\ProjectRoot;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\RelativeFileName;
use PHPUnit\Framework\TestCase;

final class ProjectRootTest extends TestCase
{
    private const CURRENT_WORKING_DIRECTORY = '/home/sarb';
    private const RELATIVE_PATH = 'foo/bar';
    private const ABSOLUTE_PATH = '/vagrant/code';

    public function testInstantiateRelativeToCurrentWorkingDirectory(): void
    {
        $projectRoot = ProjectRoot::fromProjectRoot(self::RELATIVE_PATH, self::CURRENT_WORKING_DIRECTORY);
        $this->assertEquals(self::CURRENT_WORKING_DIRECTORY.\DIRECTORY_SEPARATOR.self::RELATIVE_PATH, (string) $projectRoot);
    }

    public function testInstantiateWithAbsolutePath(): void
    {
        $projectRoot = ProjectRoot::fromProjectRoot(self::ABSOLUTE_PATH, self::CURRENT_WORKING_DIRECTORY);
        $this->assertEquals(self::ABSOLUTE_PATH, (string) $projectRoot);
    }

    public function testNonCanonicalPath(): void
    {
        $projectRoot = ProjectRoot::fromProjectRoot('/foo/baz/../bar', self::CURRENT_WORKING_DIRECTORY);
        $this->assertEquals('/foo/bar', (string) $projectRoot);
    }

    public function testRemoveProjectRootNoTrailingSlash(): void
    {
        $projectRoot = ProjectRoot::fromProjectRoot('/foo/bar', self::CURRENT_WORKING_DIRECTORY);
        $actual = $projectRoot->getPathRelativeToRootDirectory(new AbsoluteFileName('/foo/bar/baz/hello.php'));
        $this->assertSame('baz/hello.php', $actual->getFileName());
    }

    public function testRemoveProjectRootWithTrailingSlash(): void
    {
        $projectRoot = ProjectRoot::fromProjectRoot('/foo/bar/', self::CURRENT_WORKING_DIRECTORY);
        $actual = $projectRoot->getPathRelativeToRootDirectory(new AbsoluteFileName('/foo/bar/baz/hello.php'));
        $this->assertSame('baz/hello.php', $actual->getFileName());
    }

    public function testPathNotInProjectRoot(): void
    {
        $this->expectException(InvalidPathException::class);
        $expectedMessage = 'Path [/bar/baz.php] not in the project root [/foo/bar]. Is project root configured correctly?';
        $this->expectExceptionMessage($expectedMessage);
        $projectRoot = ProjectRoot::fromProjectRoot('/foo/bar', self::CURRENT_WORKING_DIRECTORY);
        $projectRoot->getPathRelativeToRootDirectory(new AbsoluteFileName('/bar/baz.php'));
    }

    public function testGetFullPath(): void
    {
        $projectRoot = ProjectRoot::fromProjectRoot('/foo/bar', self::CURRENT_WORKING_DIRECTORY);
        $fullPath = $projectRoot->getAbsoluteFileName(new RelativeFileName('fruit/apple.php'));
        $this->assertSame('/foo/bar/fruit/apple.php', $fullPath->getFileName());
    }

    public function testFromCurrentWorkingDirectory(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory(self::CURRENT_WORKING_DIRECTORY);
        $fullPath = $projectRoot->getAbsoluteFileName(new RelativeFileName('fruit/orange.php'));
        $this->assertSame('/home/sarb/fruit/orange.php', $fullPath->getFileName());
    }

    public function testProjectRootWithRelativePath(): void
    {
        $projectRoot = ProjectRoot::fromCurrentWorkingDirectory(self::CURRENT_WORKING_DIRECTORY);
        $projectRoot = $projectRoot->withRelativePath('foo/bar');
        $fullPath = $projectRoot->getAbsoluteFileName(new RelativeFileName('fruit/orange.php'));
        $this->assertSame('/home/sarb/foo/bar/fruit/orange.php', $fullPath->getFileName());
    }
}
