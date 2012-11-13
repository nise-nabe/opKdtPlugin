<?php

abstract class opKdtBaseTask extends sfBaseTask
{
  protected function findPhpBinary()
  {
    if (defined('PHP_BINARY') && PHP_BINARY)
    {
      return PHP_BINARY;
    }

    if (false !== strpos(basename($php = $_SERVER['_']), 'php'))
    {
      return $php;
    }

    // from https://github.com/symfony/Process/blob/379b35a41a2749cf7361dda0f03e04410daaca4c/PhpExecutableFinder.php
    $suffixes = DIRECTORY_SEPARATOR == '\\' ? (getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : array('.exe', '.bat', '.cmd', '.com')) : array('');
    foreach ($suffixes as $suffix)
    {
      if (is_executable($php = PHP_BINDIR.DIRECTORY_SEPARATOR.'php'.$suffix))
      {
        return $php;
      }
    }

    if ($php = getenv('PHP_PEAR_PHP_BIN'))
    {
      if (is_executable($php))
      {
        return $php;
      }
    }

    return sfToolkit::getPhpCli();
  }
}
