<?php

/*
 * This file is part of the webmozart/path-util package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Utils;

use DaveLiddament\StaticAnalysisResultsBaseliner\Domain\Common\InvalidPathException;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * Contains utility methods for handling path strings.
 *
 * The methods in this class are able to deal with both UNIX and Windows paths
 * with both forward and backward slashes. All methods return normalized parts
 * containing only forward slashes and no excess "." and ".." segments.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Thomas Schulz <mail@king2500.net>
 */
final class Path
{
    /**
     * The number of buffer entries that triggers a cleanup operation.
     */
    private const CLEANUP_THRESHOLD = 1250;

    /**
     * The buffer size after the cleanup operation.
     */
    private const CLEANUP_SIZE = 1000;

    /**
     * Buffers input/output of {@link canonicalize()}.
     *
     * @var array<string,string>
     */
    private static $buffer = [];

    /**
     * The size of the buffer.
     *
     * @var int
     */
    private static $bufferSize = 0;

    /**
     * Canonicalizes the given path.
     *
     * During normalization, all slashes are replaced by forward slashes ("/").
     * Furthermore, all "." and ".." segments are removed as far as possible.
     * ".." segments at the beginning of relative paths are not removed.
     *
     * ```php
     * echo Path::canonicalize("\webmozart\puli\..\css\style.css");
     * // => /webmozart/css/style.css
     *
     * echo Path::canonicalize("../css/./style.css");
     * // => ../css/style.css
     * ```
     *
     * This method is able to deal with both UNIX and Windows paths.
     *
     * @throws InvalidPathException
     */
    public static function canonicalize(string $path): string
    {
        if ('' === $path) {
            return '';
        }

        // This method is called by many other methods in this class. Buffer
        // the canonicalized paths to make up for the severe performance
        // decrease.
        if (isset(self::$buffer[$path])) {
            return self::$buffer[$path];
        }

        // Replace "~" with user's home directory.
        if ('~' === $path[0]) {
            $path = static::getHomeDirectory().substr($path, 1);
        }

        $path = str_replace('\\', '/', $path);

        list($root, $pathWithoutRoot) = self::split($path);

        $parts = explode('/', $pathWithoutRoot);
        $canonicalParts = [];

        // Collapse "." and "..", if possible
        foreach ($parts as $part) {
            if ('.' === $part || '' === $part) {
                continue;
            }

            // Collapse ".." with the previous part, if one exists
            // Don't collapse ".." if the previous part is also ".."
            if ('..' === $part && count($canonicalParts) > 0
                    && '..' !== $canonicalParts[count($canonicalParts) - 1]) {
                array_pop($canonicalParts);

                continue;
            }

            // Only add ".." prefixes for relative paths
            if ('..' !== $part || '' === $root) {
                $canonicalParts[] = $part;
            }
        }

        // Add the root directory again
        self::$buffer[$path] = $canonicalPath = $root.implode('/', $canonicalParts);
        ++self::$bufferSize;

        // Clean up regularly to prevent memory leaks
        if (self::$bufferSize > self::CLEANUP_THRESHOLD) {
            self::$buffer = array_slice(self::$buffer, -self::CLEANUP_SIZE, null, true);
            self::$bufferSize = self::CLEANUP_SIZE;
        }

        return $canonicalPath;
    }

    /**
     * Returns canonical path of the user's home directory.
     *
     * Supported operating systems:
     *
     *  - UNIX
     *  - Windows8 and upper
     *
     * If your operation system or environment isn't supported, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @throws InvalidPathException If your operation system or environment isn't supported
     */
    public static function getHomeDirectory(): string
    {
        // For UNIX support
        $home = getenv('HOME');
        if (false !== $home && '' !== $home) {
            return static::canonicalize($home);
        }

        // For >= Windows8 support
        $homeDrive = getenv('HOMEDRIVE');
        $homePath = getenv('HOMEPATH');

        if (false !== $homeDrive && '' !== $homeDrive && false !== $homePath && '' !== $homePath) {
            return static::canonicalize($homeDrive.$homePath);
        }

        throw InvalidPathException::operatingSystemNotSupported();
    }

    /**
     * Returns whether a path is absolute.
     */
    public static function isAbsolute(string $path): bool
    {
        if ('' === $path) {
            return false;
        }

        // Strip scheme
        if (false !== ($pos = strpos($path, '://'))) {
            $path = substr($path, $pos + 3);
        }

        // UNIX root "/" or "\" (Windows style)
        if ('/' === $path[0] || '\\' === $path[0]) {
            return true;
        }

        // Windows root
        if (strlen($path) > 1 && ctype_alpha($path[0]) && ':' === $path[1]) {
            // Special case: "C:"
            if (2 === strlen($path)) {
                return true;
            }

            // Normal case: "C:/ or "C:\"
            if ('/' === $path[2] || '\\' === $path[2]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Turns a relative path into an absolute path.
     *
     * Usually, the relative path is appended to the given base path. Dot
     * segments ("." and "..") are removed/collapsed and all slashes turned
     * into forward slashes.
     *
     * ```php
     * echo Path::makeAbsolute("../style.css", "/webmozart/puli/css");
     * // => /webmozart/puli/style.css
     * ```
     *
     * If an absolute path is passed, that path is returned unless its root
     * directory is different than the one of the base path. In that case, an
     * exception is thrown.
     *
     * ```php
     * Path::makeAbsolute("/style.css", "/webmozart/puli/css");
     * // => /style.css
     *
     * Path::makeAbsolute("C:/style.css", "C:/webmozart/puli/css");
     * // => C:/style.css
     *
     * Path::makeAbsolute("C:/style.css", "/webmozart/puli/css");
     * // InvalidArgumentException
     * ```
     *
     * If the base path is not an absolute path, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @throws \InvalidArgumentException if the base path is not absolute or if
     *                                  the given path is an absolute path with
     *                                  a different root than the base path
     * @throws InvalidPathException
     */
    public static function makeAbsolute(string $path, string $basePath): string
    {
        Assert::stringNotEmpty($basePath, 'The base path must be a non-empty string. Got: %s');

        if (!static::isAbsolute($basePath)) {
            throw new \InvalidArgumentException(sprintf('The base path "%s" is not an absolute path.', $basePath));
        }

        if (static::isAbsolute($path)) {
            return static::canonicalize($path);
        }

        if (false !== ($pos = strpos($basePath, '://'))) {
            $scheme = substr($basePath, 0, $pos + 3);
            $basePath = substr($basePath, $pos + 3);
        } else {
            $scheme = '';
        }

        return $scheme.self::canonicalize(rtrim($basePath, '/\\').'/'.$path);
    }

    /**
     * Turns a path into a relative path.
     *
     * The relative path is created relative to the given base path:
     *
     * ```php
     * echo Path::makeRelative("/webmozart/style.css", "/webmozart/puli");
     * // => ../style.css
     * ```
     *
     * If a relative path is passed and the base path is absolute, the relative
     * path is returned unchanged:
     *
     * ```php
     * Path::makeRelative("style.css", "/webmozart/puli/css");
     * // => style.css
     * ```
     *
     * If both paths are relative, the relative path is created with the
     * assumption that both paths are relative to the same directory:
     *
     * ```php
     * Path::makeRelative("style.css", "webmozart/puli/css");
     * // => ../../../style.css
     * ```
     *
     * If both paths are absolute, their root directory must be the same,
     * otherwise an exception is thrown:
     *
     * ```php
     * Path::makeRelative("C:/webmozart/style.css", "/webmozart/puli");
     * // InvalidArgumentException
     * ```
     *
     * If the passed path is absolute, but the base path is not, an exception
     * is thrown as well:
     *
     * ```php
     * Path::makeRelative("/webmozart/style.css", "webmozart/puli");
     * // InvalidArgumentException
     * ```
     *
     * If the base path is not an absolute path, an exception is thrown.
     *
     * The result is a canonical path.
     *
     * @throws \InvalidArgumentException if the base path is not absolute or if
     *                                  the given path has a different root
     *                                  than the base path
     * @throws InvalidPathException
     */
    public static function makeRelative(string $path, string $basePath): string
    {
        $path = static::canonicalize($path);
        $basePath = static::canonicalize($basePath);

        list($root, $relativePath) = self::split($path);
        list($baseRoot, $relativeBasePath) = self::split($basePath);

        // If the base path is given as absolute path and the path is already
        // relative, consider it to be relative to the given absolute path
        // already
        if ('' === $root && '' !== $baseRoot) {
            // If base path is already in its root
            if ('' === $relativeBasePath) {
                $relativePath = ltrim($relativePath, './\\');
            }

            return $relativePath;
        }

        // If the passed path is absolute, but the base path is not, we
        // cannot generate a relative path
        if ('' !== $root && '' === $baseRoot) {
            throw new \InvalidArgumentException(sprintf('The absolute path "%s" cannot be made relative to the relative path "%s". You should provide an absolute base path instead.', $path, $basePath));
        }

        // Fail if the roots of the two paths are different
        if (('' !== $baseRoot) && ($root !== $baseRoot)) {
            throw new \InvalidArgumentException(sprintf('The path "%s" cannot be made relative to "%s", because they have different roots ("%s" and "%s").', $path, $basePath, $root, $baseRoot));
        }

        if ('' === $relativeBasePath) {
            return $relativePath;
        }

        // Build a "../../" prefix with as many "../" parts as necessary
        $parts = explode('/', $relativePath);
        $baseParts = explode('/', $relativeBasePath);
        $dotDotPrefix = '';

        // Once we found a non-matching part in the prefix, we need to add
        // "../" parts for all remaining parts
        $match = true;

        foreach ($baseParts as $i => $basePart) {
            if ($match && isset($parts[$i]) && $basePart === $parts[$i]) {
                unset($parts[$i]);

                continue;
            }

            $match = false;
            $dotDotPrefix .= '../';
        }

        return rtrim($dotDotPrefix.implode('/', $parts), '/');
    }

    /**
     * Joins two or more path strings.
     *
     * The result is a canonical path.
     *
     * @param array<int,string> $paths path parts as parameters or array
     *
     * @throws InvalidPathException
     */
    public static function join(array $paths): string
    {
        $finalPath = null;
        $wasScheme = false;

        foreach ($paths as $path) {
            if ('' === $path) {
                continue;
            }

            if (null === $finalPath) {
                // For first part we keep slashes, like '/top', 'C:\' or 'phar://'
                $finalPath = $path;
                $wasScheme = str_contains($path, '://');
                continue;
            }

            // Only add slash if previous part didn't end with '/' or '\'
            if (!in_array(substr($finalPath, -1), ['/', '\\'], true)) {
                $finalPath .= '/';
            }

            // If first part included a scheme like 'phar://' we allow current part to start with '/', otherwise trim
            $finalPath .= $wasScheme ? $path : ltrim($path, '/');
            $wasScheme = false;
        }

        if (null === $finalPath) {
            return '';
        }

        return self::canonicalize($finalPath);
    }

    /**
     * Returns whether a path is a base path of another path.
     *
     * Dot segments ("." and "..") are removed/collapsed and all slashes turned
     * into forward slashes.
     *
     * ```php
     * Path::isBasePath('/webmozart', '/webmozart/css');
     * // => true
     *
     * Path::isBasePath('/webmozart', '/webmozart');
     * // => true
     *
     * Path::isBasePath('/webmozart', '/webmozart/..');
     * // => false
     *
     * Path::isBasePath('/webmozart', '/puli');
     * // => false
     * ```
     *
     * @throws InvalidPathException
     */
    public static function isBasePath(string $basePath, string $ofPath): bool
    {
        $basePath = self::canonicalize($basePath);
        $ofPath = self::canonicalize($ofPath);

        // Append slashes to prevent false positives when two paths have
        // a common prefix, for example /base/foo and /base/foobar.
        // Don't append a slash for the root "/", because then that root
        // won't be discovered as common prefix ("//" is not a prefix of
        // "/foobar/").
        return str_starts_with($ofPath.'/', rtrim($basePath, '/').'/');
    }

    /**
     * Splits a part into its root directory and the remainder.
     *
     * If the path has no root directory, an empty root directory will be
     * returned.
     *
     * If the root directory is a Windows style partition, the resulting root
     * will always contain a trailing slash.
     *
     * list ($root, $path) = Path::split("C:/webmozart")
     * // => array("C:/", "webmozart")
     *
     * list ($root, $path) = Path::split("C:")
     * // => array("C:/", "")
     *
     * @return array{string,string}
     */
    private static function split(string $path): array
    {
        if ('' === $path) {
            return ['', ''];
        }

        // Remember scheme as part of the root, if any
        if (false !== ($pos = strpos($path, '://'))) {
            $root = substr($path, 0, $pos + 3);
            $path = substr($path, $pos + 3);
        } else {
            $root = '';
        }

        $length = strlen($path);

        // Remove and remember root directory
        if ('/' === $path[0]) {
            $root .= '/';
            $path = $length > 1 ? substr($path, 1) : '';
        } elseif ($length > 1 && ctype_alpha($path[0]) && ':' === $path[1]) {
            if (2 === $length) {
                // Windows special case: "C:"
                $root .= $path.'/';
                $path = '';
            } elseif ('/' === $path[2]) {
                // Windows normal case: "C:/"..
                $root .= substr($path, 0, 3);
                $path = $length > 3 ? substr($path, 3) : '';
            }
        }

        return [$root, $path];
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
