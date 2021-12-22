<?php

/*
 * This file is part of the webmozart/path-util package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Tests\Unit\Core\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils\Path;
use PHPUnit\Framework\TestCase;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Thomas Schulz <mail@king2500.net>
 */
class PathTest extends TestCase
{
    /** @var array<string,string> */
    protected $storedEnv = [];

    protected function setUp(): void
    {
        $this->storedEnv['HOME'] = $this->getEnv('HOME');
        $this->storedEnv['HOMEDRIVE'] = $this->getEnv('HOMEDRIVE');
        $this->storedEnv['HOMEPATH'] = $this->getEnv('HOMEPATH');

        putenv('HOME=/home/webmozart');
        putenv('HOMEDRIVE=');
        putenv('HOMEPATH=');
    }

    private function getEnv(string $key): string
    {
        $value = getenv($key);
        if (false === $value) {
            return '';
        }

        return $value;
    }

    protected function tearDown(): void
    {
        putenv('HOME='.$this->storedEnv['HOME']);
        putenv('HOMEDRIVE='.$this->storedEnv['HOMEDRIVE']);
        putenv('HOMEPATH='.$this->storedEnv['HOMEPATH']);
    }

    /** @return array<int,array{string,string}> */
    public function provideCanonicalizationTests(): array
    {
        return [
            // relative paths (forward slash)
            ['css/./style.css', 'css/style.css'],
            ['css/../style.css', 'style.css'],
            ['css/./../style.css', 'style.css'],
            ['css/.././style.css', 'style.css'],
            ['css/../../style.css', '../style.css'],
            ['./css/style.css', 'css/style.css'],
            ['../css/style.css', '../css/style.css'],
            ['./../css/style.css', '../css/style.css'],
            ['.././css/style.css', '../css/style.css'],
            ['../../css/style.css', '../../css/style.css'],
            ['', ''],
            ['.', ''],
            ['..', '..'],
            ['./..', '..'],
            ['../.', '..'],
            ['../..', '../..'],

            // relative paths (backslash)
            ['css\\.\\style.css', 'css/style.css'],
            ['css\\..\\style.css', 'style.css'],
            ['css\\.\\..\\style.css', 'style.css'],
            ['css\\..\\.\\style.css', 'style.css'],
            ['css\\..\\..\\style.css', '../style.css'],
            ['.\\css\\style.css', 'css/style.css'],
            ['..\\css\\style.css', '../css/style.css'],
            ['.\\..\\css\\style.css', '../css/style.css'],
            ['..\\.\\css\\style.css', '../css/style.css'],
            ['..\\..\\css\\style.css', '../../css/style.css'],

            // absolute paths (forward slash, UNIX)
            ['/css/style.css', '/css/style.css'],
            ['/css/./style.css', '/css/style.css'],
            ['/css/../style.css', '/style.css'],
            ['/css/./../style.css', '/style.css'],
            ['/css/.././style.css', '/style.css'],
            ['/./css/style.css', '/css/style.css'],
            ['/../css/style.css', '/css/style.css'],
            ['/./../css/style.css', '/css/style.css'],
            ['/.././css/style.css', '/css/style.css'],
            ['/../../css/style.css', '/css/style.css'],

            // absolute paths (backslash, UNIX)
            ['\\css\\style.css', '/css/style.css'],
            ['\\css\\.\\style.css', '/css/style.css'],
            ['\\css\\..\\style.css', '/style.css'],
            ['\\css\\.\\..\\style.css', '/style.css'],
            ['\\css\\..\\.\\style.css', '/style.css'],
            ['\\.\\css\\style.css', '/css/style.css'],
            ['\\..\\css\\style.css', '/css/style.css'],
            ['\\.\\..\\css\\style.css', '/css/style.css'],
            ['\\..\\.\\css\\style.css', '/css/style.css'],
            ['\\..\\..\\css\\style.css', '/css/style.css'],

            // absolute paths (forward slash, Windows)
            ['C:/css/style.css', 'C:/css/style.css'],
            ['C:/css/./style.css', 'C:/css/style.css'],
            ['C:/css/../style.css', 'C:/style.css'],
            ['C:/css/./../style.css', 'C:/style.css'],
            ['C:/css/.././style.css', 'C:/style.css'],
            ['C:/./css/style.css', 'C:/css/style.css'],
            ['C:/../css/style.css', 'C:/css/style.css'],
            ['C:/./../css/style.css', 'C:/css/style.css'],
            ['C:/.././css/style.css', 'C:/css/style.css'],
            ['C:/../../css/style.css', 'C:/css/style.css'],

            // absolute paths (backslash, Windows)
            ['C:\\css\\style.css', 'C:/css/style.css'],
            ['C:\\css\\.\\style.css', 'C:/css/style.css'],
            ['C:\\css\\..\\style.css', 'C:/style.css'],
            ['C:\\css\\.\\..\\style.css', 'C:/style.css'],
            ['C:\\css\\..\\.\\style.css', 'C:/style.css'],
            ['C:\\.\\css\\style.css', 'C:/css/style.css'],
            ['C:\\..\\css\\style.css', 'C:/css/style.css'],
            ['C:\\.\\..\\css\\style.css', 'C:/css/style.css'],
            ['C:\\..\\.\\css\\style.css', 'C:/css/style.css'],
            ['C:\\..\\..\\css\\style.css', 'C:/css/style.css'],

            // Windows special case
            ['C:', 'C:/'],

            // Don't change malformed path
            ['C:css/style.css', 'C:css/style.css'],

            // absolute paths (stream, UNIX)
            ['phar:///css/style.css', 'phar:///css/style.css'],
            ['phar:///css/./style.css', 'phar:///css/style.css'],
            ['phar:///css/../style.css', 'phar:///style.css'],
            ['phar:///css/./../style.css', 'phar:///style.css'],
            ['phar:///css/.././style.css', 'phar:///style.css'],
            ['phar:///./css/style.css', 'phar:///css/style.css'],
            ['phar:///../css/style.css', 'phar:///css/style.css'],
            ['phar:///./../css/style.css', 'phar:///css/style.css'],
            ['phar:///.././css/style.css', 'phar:///css/style.css'],
            ['phar:///../../css/style.css', 'phar:///css/style.css'],

            // absolute paths (stream, Windows)
            ['phar://C:/css/style.css', 'phar://C:/css/style.css'],
            ['phar://C:/css/./style.css', 'phar://C:/css/style.css'],
            ['phar://C:/css/../style.css', 'phar://C:/style.css'],
            ['phar://C:/css/./../style.css', 'phar://C:/style.css'],
            ['phar://C:/css/.././style.css', 'phar://C:/style.css'],
            ['phar://C:/./css/style.css', 'phar://C:/css/style.css'],
            ['phar://C:/../css/style.css', 'phar://C:/css/style.css'],
            ['phar://C:/./../css/style.css', 'phar://C:/css/style.css'],
            ['phar://C:/.././css/style.css', 'phar://C:/css/style.css'],
            ['phar://C:/../../css/style.css', 'phar://C:/css/style.css'],

            // paths with "~" UNIX
            ['~/css/style.css', '/home/webmozart/css/style.css'],
            ['~/css/./style.css', '/home/webmozart/css/style.css'],
            ['~/css/../style.css', '/home/webmozart/style.css'],
            ['~/css/./../style.css', '/home/webmozart/style.css'],
            ['~/css/.././style.css', '/home/webmozart/style.css'],
            ['~/./css/style.css', '/home/webmozart/css/style.css'],
            ['~/../css/style.css', '/home/css/style.css'],
            ['~/./../css/style.css', '/home/css/style.css'],
            ['~/.././css/style.css', '/home/css/style.css'],
            ['~/../../css/style.css', '/css/style.css'],
        ];
    }

    /**
     * @dataProvider provideCanonicalizationTests
     */
    public function testCanonicalize(string $path, string $canonicalized): void
    {
        $this->assertSame($canonicalized, Path::canonicalize($path));
    }

    /** @return array<int,array{string,bool}> */
    public function provideIsAbsolutePathTests(): array
    {
        return [
            ['/css/style.css', true],
            ['/', true],
            ['css/style.css', false],
            ['', false],

            ['\\css\\style.css', true],
            ['\\', true],
            ['css\\style.css', false],

            ['C:/css/style.css', true],
            ['D:/', true],

            ['E:\\css\\style.css', true],
            ['F:\\', true],

            ['phar:///css/style.css', true],
            ['phar:///', true],

            // Windows special case
            ['C:', true],

            // Not considered absolute
            ['C:css/style.css', false],
        ];
    }

    /**
     * @dataProvider provideIsAbsolutePathTests
     */
    public function testIsAbsolute(string $path, bool $isAbsolute): void
    {
        $this->assertSame($isAbsolute, Path::isAbsolute($path));
    }

    /** @return array<int,array{string,string,string}> */
    public function providePathTests(): array
    {
        return [
            // relative to absolute path
            ['css/style.css', '/webmozart/puli', '/webmozart/puli/css/style.css'],
            ['../css/style.css', '/webmozart/puli', '/webmozart/css/style.css'],
            ['../../css/style.css', '/webmozart/puli', '/css/style.css'],

            // relative to root
            ['css/style.css', '/', '/css/style.css'],
            ['css/style.css', 'C:', 'C:/css/style.css'],
            ['css/style.css', 'C:/', 'C:/css/style.css'],

            // same sub directories in different base directories
            ['../../puli/css/style.css', '/webmozart/css', '/puli/css/style.css'],

            ['', '/webmozart/puli', '/webmozart/puli'],
            ['..', '/webmozart/puli', '/webmozart'],
        ];
    }

    /** @return array<int,array{string,string,string}> */
    public function provideMakeAbsoluteTests(): array
    {
        return array_merge($this->providePathTests(), [
            // collapse dots
            ['css/./style.css', '/webmozart/puli', '/webmozart/puli/css/style.css'],
            ['css/../style.css', '/webmozart/puli', '/webmozart/puli/style.css'],
            ['css/./../style.css', '/webmozart/puli', '/webmozart/puli/style.css'],
            ['css/.././style.css', '/webmozart/puli', '/webmozart/puli/style.css'],
            ['./css/style.css', '/webmozart/puli', '/webmozart/puli/css/style.css'],

            ['css\\.\\style.css', '\\webmozart\\puli', '/webmozart/puli/css/style.css'],
            ['css\\..\\style.css', '\\webmozart\\puli', '/webmozart/puli/style.css'],
            ['css\\.\\..\\style.css', '\\webmozart\\puli', '/webmozart/puli/style.css'],
            ['css\\..\\.\\style.css', '\\webmozart\\puli', '/webmozart/puli/style.css'],
            ['.\\css\\style.css', '\\webmozart\\puli', '/webmozart/puli/css/style.css'],

            // collapse dots on root
            ['./css/style.css', '/', '/css/style.css'],
            ['../css/style.css', '/', '/css/style.css'],
            ['../css/./style.css', '/', '/css/style.css'],
            ['../css/../style.css', '/', '/style.css'],
            ['../css/./../style.css', '/', '/style.css'],
            ['../css/.././style.css', '/', '/style.css'],

            ['.\\css\\style.css', '\\', '/css/style.css'],
            ['..\\css\\style.css', '\\', '/css/style.css'],
            ['..\\css\\.\\style.css', '\\', '/css/style.css'],
            ['..\\css\\..\\style.css', '\\', '/style.css'],
            ['..\\css\\.\\..\\style.css', '\\', '/style.css'],
            ['..\\css\\..\\.\\style.css', '\\', '/style.css'],

            ['./css/style.css', 'C:/', 'C:/css/style.css'],
            ['../css/style.css', 'C:/', 'C:/css/style.css'],
            ['../css/./style.css', 'C:/', 'C:/css/style.css'],
            ['../css/../style.css', 'C:/', 'C:/style.css'],
            ['../css/./../style.css', 'C:/', 'C:/style.css'],
            ['../css/.././style.css', 'C:/', 'C:/style.css'],

            ['.\\css\\style.css', 'C:\\', 'C:/css/style.css'],
            ['..\\css\\style.css', 'C:\\', 'C:/css/style.css'],
            ['..\\css\\.\\style.css', 'C:\\', 'C:/css/style.css'],
            ['..\\css\\..\\style.css', 'C:\\', 'C:/style.css'],
            ['..\\css\\.\\..\\style.css', 'C:\\', 'C:/style.css'],
            ['..\\css\\..\\.\\style.css', 'C:\\', 'C:/style.css'],

            ['./css/style.css', 'phar:///', 'phar:///css/style.css'],
            ['../css/style.css', 'phar:///', 'phar:///css/style.css'],
            ['../css/./style.css', 'phar:///', 'phar:///css/style.css'],
            ['../css/../style.css', 'phar:///', 'phar:///style.css'],
            ['../css/./../style.css', 'phar:///', 'phar:///style.css'],
            ['../css/.././style.css', 'phar:///', 'phar:///style.css'],

            ['./css/style.css', 'phar://C:/', 'phar://C:/css/style.css'],
            ['../css/style.css', 'phar://C:/', 'phar://C:/css/style.css'],
            ['../css/./style.css', 'phar://C:/', 'phar://C:/css/style.css'],
            ['../css/../style.css', 'phar://C:/', 'phar://C:/style.css'],
            ['../css/./../style.css', 'phar://C:/', 'phar://C:/style.css'],
            ['../css/.././style.css', 'phar://C:/', 'phar://C:/style.css'],

            // absolute paths
            ['/css/style.css', '/webmozart/puli', '/css/style.css'],
            ['\\css\\style.css', '/webmozart/puli', '/css/style.css'],
            ['C:/css/style.css', 'C:/webmozart/puli', 'C:/css/style.css'],
            ['D:\\css\\style.css', 'D:/webmozart/puli', 'D:/css/style.css'],
        ]);
    }

    /**
     * @dataProvider provideMakeAbsoluteTests
     */
    public function testMakeAbsolute(string $relativePath, string $basePath, string $absolutePath): void
    {
        $this->assertSame($absolutePath, Path::makeAbsolute($relativePath, $basePath));
    }

    /** @return array<int,array{string,string}> */
    public function provideAbsolutePathsWithDifferentRoots(): array
    {
        return [
            ['C:/css/style.css', '/webmozart/puli'],
            ['C:/css/style.css', '\\webmozart\\puli'],
            ['C:\\css\\style.css', '/webmozart/puli'],
            ['C:\\css\\style.css', '\\webmozart\\puli'],

            ['/css/style.css', 'C:/webmozart/puli'],
            ['/css/style.css', 'C:\\webmozart\\puli'],
            ['\\css\\style.css', 'C:/webmozart/puli'],
            ['\\css\\style.css', 'C:\\webmozart\\puli'],

            ['D:/css/style.css', 'C:/webmozart/puli'],
            ['D:/css/style.css', 'C:\\webmozart\\puli'],
            ['D:\\css\\style.css', 'C:/webmozart/puli'],
            ['D:\\css\\style.css', 'C:\\webmozart\\puli'],

            ['phar:///css/style.css', '/webmozart/puli'],
            ['/css/style.css', 'phar:///webmozart/puli'],

            ['phar://C:/css/style.css', 'C:/webmozart/puli'],
            ['phar://C:/css/style.css', 'C:\\webmozart\\puli'],
            ['phar://C:\\css\\style.css', 'C:/webmozart/puli'],
            ['phar://C:\\css\\style.css', 'C:\\webmozart\\puli'],
        ];
    }

    /**
     * @dataProvider provideAbsolutePathsWithDifferentRoots
     */
    public function testMakeAbsoluteDoesNotFailIfDifferentRoot(string $basePath, string $absolutePath): void
    {
        // If a path in partition D: is passed, but $basePath is in partition
        // C:, the path should be returned unchanged
        $this->assertSame(Path::canonicalize($absolutePath), Path::makeAbsolute($absolutePath, $basePath));
    }

    /** @return array<int,array{string,string, string}> */
    public function provideMakeRelativeTests(): array
    {
        $paths = array_map(function (array $arguments) {
            return [$arguments[2], $arguments[1], $arguments[0]];
        }, $this->providePathTests());

        return array_merge($paths, [
            ['/webmozart/puli/./css/style.css', '/webmozart/puli', 'css/style.css'],
            ['/webmozart/puli/../css/style.css', '/webmozart/puli', '../css/style.css'],
            ['/webmozart/puli/.././css/style.css', '/webmozart/puli', '../css/style.css'],
            ['/webmozart/puli/./../css/style.css', '/webmozart/puli', '../css/style.css'],
            ['/webmozart/puli/../../css/style.css', '/webmozart/puli', '../../css/style.css'],
            ['/webmozart/puli/css/style.css', '/webmozart/./puli', 'css/style.css'],
            ['/webmozart/puli/css/style.css', '/webmozart/../puli', '../webmozart/puli/css/style.css'],
            ['/webmozart/puli/css/style.css', '/webmozart/./../puli', '../webmozart/puli/css/style.css'],
            ['/webmozart/puli/css/style.css', '/webmozart/.././puli', '../webmozart/puli/css/style.css'],
            ['/webmozart/puli/css/style.css', '/webmozart/../../puli', '../webmozart/puli/css/style.css'],

            // first argument shorter than second
            ['/css', '/webmozart/puli', '../../css'],

            // second argument shorter than first
            ['/webmozart/puli', '/css', '../webmozart/puli'],

            ['\\webmozart\\puli\\css\\style.css', '\\webmozart\\puli', 'css/style.css'],
            ['\\webmozart\\css\\style.css', '\\webmozart\\puli', '../css/style.css'],
            ['\\css\\style.css', '\\webmozart\\puli', '../../css/style.css'],

            ['C:/webmozart/puli/css/style.css', 'C:/webmozart/puli', 'css/style.css'],
            ['C:/webmozart/css/style.css', 'C:/webmozart/puli', '../css/style.css'],
            ['C:/css/style.css', 'C:/webmozart/puli', '../../css/style.css'],

            ['C:\\webmozart\\puli\\css\\style.css', 'C:\\webmozart\\puli', 'css/style.css'],
            ['C:\\webmozart\\css\\style.css', 'C:\\webmozart\\puli', '../css/style.css'],
            ['C:\\css\\style.css', 'C:\\webmozart\\puli', '../../css/style.css'],

            ['phar:///webmozart/puli/css/style.css', 'phar:///webmozart/puli', 'css/style.css'],
            ['phar:///webmozart/css/style.css', 'phar:///webmozart/puli', '../css/style.css'],
            ['phar:///css/style.css', 'phar:///webmozart/puli', '../../css/style.css'],

            ['phar://C:/webmozart/puli/css/style.css', 'phar://C:/webmozart/puli', 'css/style.css'],
            ['phar://C:/webmozart/css/style.css', 'phar://C:/webmozart/puli', '../css/style.css'],
            ['phar://C:/css/style.css', 'phar://C:/webmozart/puli', '../../css/style.css'],

            // already relative + already in root basepath
            ['../style.css', '/', 'style.css'],
            ['./style.css', '/', 'style.css'],
            ['../../style.css', '/', 'style.css'],
            ['..\\style.css', 'C:\\', 'style.css'],
            ['.\\style.css', 'C:\\', 'style.css'],
            ['..\\..\\style.css', 'C:\\', 'style.css'],
            ['../style.css', 'C:/', 'style.css'],
            ['./style.css', 'C:/', 'style.css'],
            ['../../style.css', 'C:/', 'style.css'],
            ['..\\style.css', '\\', 'style.css'],
            ['.\\style.css', '\\', 'style.css'],
            ['..\\..\\style.css', '\\', 'style.css'],
            ['../style.css', 'phar:///', 'style.css'],
            ['./style.css', 'phar:///', 'style.css'],
            ['../../style.css', 'phar:///', 'style.css'],
            ['..\\style.css', 'phar://C:\\', 'style.css'],
            ['.\\style.css', 'phar://C:\\', 'style.css'],
            ['..\\..\\style.css', 'phar://C:\\', 'style.css'],

            ['css/../style.css', '/', 'style.css'],
            ['css/./style.css', '/', 'css/style.css'],
            ['css\\..\\style.css', 'C:\\', 'style.css'],
            ['css\\.\\style.css', 'C:\\', 'css/style.css'],
            ['css/../style.css', 'C:/', 'style.css'],
            ['css/./style.css', 'C:/', 'css/style.css'],
            ['css\\..\\style.css', '\\', 'style.css'],
            ['css\\.\\style.css', '\\', 'css/style.css'],
            ['css/../style.css', 'phar:///', 'style.css'],
            ['css/./style.css', 'phar:///', 'css/style.css'],
            ['css\\..\\style.css', 'phar://C:\\', 'style.css'],
            ['css\\.\\style.css', 'phar://C:\\', 'css/style.css'],

            // already relative
            ['css/style.css', '/webmozart/puli', 'css/style.css'],
            ['css\\style.css', '\\webmozart\\puli', 'css/style.css'],

            // both relative
            ['css/style.css', 'webmozart/puli', '../../css/style.css'],
            ['css\\style.css', 'webmozart\\puli', '../../css/style.css'],

            // relative to empty
            ['css/style.css', '', 'css/style.css'],
            ['css\\style.css', '', 'css/style.css'],

            // different slashes in path and base path
            ['/webmozart/puli/css/style.css', '\\webmozart\\puli', 'css/style.css'],
            ['\\webmozart\\puli\\css\\style.css', '/webmozart/puli', 'css/style.css'],
        ]);
    }

    /**
     * @dataProvider provideMakeRelativeTests
     */
    public function testMakeRelative(string $absolutePath, string $basePath, string $relativePath): void
    {
        $this->assertSame($relativePath, Path::makeRelative($absolutePath, $basePath));
    }

    public function testMakeRelativeFailsIfAbsolutePathAndBasePathNotAbsolute(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The absolute path "/webmozart/puli/css/style.css" cannot be made relative to the relative path "webmozart/puli". You should provide an absolute base path instead.');
        Path::makeRelative('/webmozart/puli/css/style.css', 'webmozart/puli');
    }

    public function testMakeRelativeFailsIfAbsolutePathAndBasePathEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The absolute path "/webmozart/puli/css/style.css" cannot be made relative to the relative path "". You should provide an absolute base path instead.');
        Path::makeRelative('/webmozart/puli/css/style.css', '');
    }

    /**
     * @dataProvider provideAbsolutePathsWithDifferentRoots
     */
    public function testMakeRelativeFailsIfDifferentRoot(string $absolutePath, string $basePath): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Path::makeRelative($absolutePath, $basePath);
    }

    /** @return array<int,array{string,bool}> */
    public function provideIsLocalTests(): array
    {
        return [
            ['/bg.png', true],
            ['bg.png', true],
            ['http://example.com/bg.png', false],
            ['http://example.com', false],
            ['', false],
        ];
    }

    /** @return array<int,array{string,string, bool}> */
    public function provideIsBasePathTests(): array
    {
        return [
            // same paths
            ['/base/path', '/base/path', true],
            ['C:/base/path', 'C:/base/path', true],
            ['C:\\base\\path', 'C:\\base\\path', true],
            ['C:/base/path', 'C:\\base\\path', true],
            ['phar:///base/path', 'phar:///base/path', true],
            ['phar://C:/base/path', 'phar://C:/base/path', true],

            // trailing slash
            ['/base/path/', '/base/path', true],
            ['C:/base/path/', 'C:/base/path', true],
            ['C:\\base\\path\\', 'C:\\base\\path', true],
            ['C:/base/path/', 'C:\\base\\path', true],
            ['phar:///base/path/', 'phar:///base/path', true],
            ['phar://C:/base/path/', 'phar://C:/base/path', true],

            ['/base/path', '/base/path/', true],
            ['C:/base/path', 'C:/base/path/', true],
            ['C:\\base\\path', 'C:\\base\\path\\', true],
            ['C:/base/path', 'C:\\base\\path\\', true],
            ['phar:///base/path', 'phar:///base/path/', true],
            ['phar://C:/base/path', 'phar://C:/base/path/', true],

            // first in second
            ['/base/path/sub', '/base/path', false],
            ['C:/base/path/sub', 'C:/base/path', false],
            ['C:\\base\\path\\sub', 'C:\\base\\path', false],
            ['C:/base/path/sub', 'C:\\base\\path', false],
            ['phar:///base/path/sub', 'phar:///base/path', false],
            ['phar://C:/base/path/sub', 'phar://C:/base/path', false],

            // second in first
            ['/base/path', '/base/path/sub', true],
            ['C:/base/path', 'C:/base/path/sub', true],
            ['C:\\base\\path', 'C:\\base\\path\\sub', true],
            ['C:/base/path', 'C:\\base\\path\\sub', true],
            ['phar:///base/path', 'phar:///base/path/sub', true],
            ['phar://C:/base/path', 'phar://C:/base/path/sub', true],

            // first is prefix
            ['/base/path/di', '/base/path/dir', false],
            ['C:/base/path/di', 'C:/base/path/dir', false],
            ['C:\\base\\path\\di', 'C:\\base\\path\\dir', false],
            ['C:/base/path/di', 'C:\\base\\path\\dir', false],
            ['phar:///base/path/di', 'phar:///base/path/dir', false],
            ['phar://C:/base/path/di', 'phar://C:/base/path/dir', false],

            // second is prefix
            ['/base/path/dir', '/base/path/di', false],
            ['C:/base/path/dir', 'C:/base/path/di', false],
            ['C:\\base\\path\\dir', 'C:\\base\\path\\di', false],
            ['C:/base/path/dir', 'C:\\base\\path\\di', false],
            ['phar:///base/path/dir', 'phar:///base/path/di', false],
            ['phar://C:/base/path/dir', 'phar://C:/base/path/di', false],

            // root
            ['/', '/second', true],
            ['C:/', 'C:/second', true],
            ['C:', 'C:/second', true],
            ['C:\\', 'C:\\second', true],
            ['C:/', 'C:\\second', true],
            ['phar:///', 'phar:///second', true],
            ['phar://C:/', 'phar://C:/second', true],

            // windows vs unix
            ['/base/path', 'C:/base/path', false],
            ['C:/base/path', '/base/path', false],
            ['/base/path', 'C:\\base\\path', false],
            ['/base/path', 'phar:///base/path', false],
            ['phar:///base/path', 'phar://C:/base/path', false],

            // different partitions
            ['C:/base/path', 'D:/base/path', false],
            ['C:/base/path', 'D:\\base\\path', false],
            ['C:\\base\\path', 'D:\\base\\path', false],
            ['C:/base/path', 'phar://C:/base/path', false],
            ['phar://C:/base/path', 'phar://D:/base/path', false],
        ];
    }

    /**
     * @dataProvider provideIsBasePathTests
     */
    public function testIsBasePath(string $path, string $ofPath, bool $result): void
    {
        $this->assertSame($result, Path::isBasePath($path, $ofPath));
    }

    /** @return array<int,array{string, string, string}> */
    public function provideJoinTests(): array
    {
        return [
            ['', '', ''],
            ['/path/to/test', '', '/path/to/test'],
            ['/path/to//test', '', '/path/to/test'],
            ['', '/path/to/test', '/path/to/test'],
            ['', '/path/to//test', '/path/to/test'],

            ['/path/to/test', 'subdir', '/path/to/test/subdir'],
            ['/path/to/test/', 'subdir', '/path/to/test/subdir'],
            ['/path/to/test', '/subdir', '/path/to/test/subdir'],
            ['/path/to/test/', '/subdir', '/path/to/test/subdir'],
            ['/path/to/test', './subdir', '/path/to/test/subdir'],
            ['/path/to/test/', './subdir', '/path/to/test/subdir'],
            ['/path/to/test/', '../parentdir', '/path/to/parentdir'],
            ['/path/to/test', '../parentdir', '/path/to/parentdir'],
            ['path/to/test/', '/subdir', 'path/to/test/subdir'],
            ['path/to/test', '/subdir', 'path/to/test/subdir'],
            ['../path/to/test', '/subdir', '../path/to/test/subdir'],
            ['path', '../../subdir', '../subdir'],
            ['/path', '../../subdir', '/subdir'],
            ['../path', '../../subdir', '../../subdir'],

            ['base/path', 'to/test', 'base/path/to/test'],

            ['C:\\path\\to\\test', 'subdir', 'C:/path/to/test/subdir'],
            ['C:\\path\\to\\test\\', 'subdir', 'C:/path/to/test/subdir'],
            ['C:\\path\\to\\test', '/subdir', 'C:/path/to/test/subdir'],
            ['C:\\path\\to\\test\\', '/subdir', 'C:/path/to/test/subdir'],

            ['/', 'subdir', '/subdir'],
            ['/', '/subdir', '/subdir'],
            ['C:/', 'subdir', 'C:/subdir'],
            ['C:/', '/subdir', 'C:/subdir'],
            ['C:\\', 'subdir', 'C:/subdir'],
            ['C:\\', '/subdir', 'C:/subdir'],
            ['C:', 'subdir', 'C:/subdir'],
            ['C:', '/subdir', 'C:/subdir'],

            ['phar://', '/path/to/test', 'phar:///path/to/test'],
            ['phar:///', '/path/to/test', 'phar:///path/to/test'],
            ['phar:///path/to/test', 'subdir', 'phar:///path/to/test/subdir'],
            ['phar:///path/to/test', 'subdir/', 'phar:///path/to/test/subdir'],
            ['phar:///path/to/test', '/subdir', 'phar:///path/to/test/subdir'],
            ['phar:///path/to/test/', 'subdir', 'phar:///path/to/test/subdir'],
            ['phar:///path/to/test/', '/subdir', 'phar:///path/to/test/subdir'],

            ['phar://', 'C:/path/to/test', 'phar://C:/path/to/test'],
            ['phar://', 'C:\\path\\to\\test', 'phar://C:/path/to/test'],
            ['phar://C:/path/to/test', 'subdir', 'phar://C:/path/to/test/subdir'],
            ['phar://C:/path/to/test', 'subdir/', 'phar://C:/path/to/test/subdir'],
            ['phar://C:/path/to/test', '/subdir', 'phar://C:/path/to/test/subdir'],
            ['phar://C:/path/to/test/', 'subdir', 'phar://C:/path/to/test/subdir'],
            ['phar://C:/path/to/test/', '/subdir', 'phar://C:/path/to/test/subdir'],
            ['phar://C:', 'path/to/test', 'phar://C:/path/to/test'],
            ['phar://C:', '/path/to/test', 'phar://C:/path/to/test'],
            ['phar://C:/', 'path/to/test', 'phar://C:/path/to/test'],
            ['phar://C:/', '/path/to/test', 'phar://C:/path/to/test'],
        ];
    }

    /**
     * @dataProvider provideJoinTests
     */
    public function testJoin(string $path1, string $path2, string $result): void
    {
        $this->assertSame($result, Path::join([$path1, $path2]));
    }

    public function testJoinVarArgs(): void
    {
        $this->assertSame('/path', Path::join(['/path']));
        $this->assertSame('/path/to', Path::join(['/path', 'to']));
        $this->assertSame('/path/to/test', Path::join(['/path', 'to', '/test']));
        $this->assertSame('/path/to/test/subdir', Path::join(['/path', 'to', '/test', 'subdir/']));
    }

    public function testGetHomeDirectoryFailsIfNotSupportedOperationSystem(): void
    {
        putenv('HOME=');

        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage("Your environment or operation system isn't supported");

        Path::getHomeDirectory();
    }

    public function testGetHomeDirectoryForUnix(): void
    {
        $this->assertEquals('/home/webmozart', Path::getHomeDirectory());
    }

    public function testGetHomeDirectoryForWindows(): void
    {
        putenv('HOME=');
        putenv('HOMEDRIVE=C:');
        putenv('HOMEPATH=/users/webmozart');

        $this->assertEquals('C:/users/webmozart', Path::getHomeDirectory());
    }
}
