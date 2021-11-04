<?php

/**
 * @file
 * Contains \DrupalProject\composer\LibrariesHandler.
 */

namespace DrupalProject\composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class LibrariesHandler
{

  protected static function getDrupalRoot($project_root)
  {
    return $project_root .  '/web';
  }

  public static function renameLibraries(Event $event)
    {
        $fs = new Filesystem();
        $root = static::getDrupalRoot(getcwd());

        $oldNames = [0 => "cycle", 1 => "jquery-hoverintent"];
        $newNames = [0 => "jquery.cycle", 1 => "jquery.hoverIntent"];

        // rename the libraries but check case it already exists
        foreach($oldNames as $key => $fromFilename) {
            $toFilename = $newNames[$key];
            if ($fs->exists($root . '/libraries/' . $fromFilename) & $fs->exists($root . '/libraries/' . $toFilename)) {
                $fs->remove($root . '/libraries/' . $toFilename);
                $event->getIO()->write("  - Successfully removed $toFilename");
                $fs->rename($root . '/libraries/'. $fromFilename, $root . '/libraries/' . $toFilename);
                $event->getIO()->write("  - Renamed $fromFilename to $toFilename");
            }
            else {
                if ($fs->exists($root . '/libraries/' . $fromFilename) & !$fs->exists($root . '/libraries/' . $toFilename)) {
                    $fs->rename($root . '/libraries/'. $fromFilename, $root . '/libraries/' . $toFilename);
                    $event->getIO()->write("  - Renamed $fromFilename to $toFilename");
                }
            }
        }
    }
}
