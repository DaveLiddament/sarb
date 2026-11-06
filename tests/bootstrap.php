<?php

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

require __DIR__.'/../vendor/autoload.php';

// Prune old scratchpad directories. They are kept when an integration test fails (to help
// investigate the failure), but nothing else ever deletes them.
$scratchpadDirectory = __DIR__.'/scratchpad';
$entries = glob($scratchpadDirectory.'/*', \GLOB_ONLYDIR);
if (false !== $entries) {
    $cutoff = time() - (7 * 24 * 60 * 60);
    $fileSystem = new Filesystem();
    foreach ($entries as $entry) {
        if (filemtime($entry) < $cutoff) {
            try {
                $fileSystem->remove($entry);
            } catch (IOException) {
                // E.g. directory owned by another user. Ignore.
            }
        }
    }
}
