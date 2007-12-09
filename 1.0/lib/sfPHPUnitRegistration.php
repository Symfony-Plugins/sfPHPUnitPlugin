<?php

class sfPHPUnitRegistration
{
  public $files = array();
  public $extension = '.php';
  public $base_dir = '';
  
  public function register($files_or_directories)
  {
    foreach ((array) $files_or_directories as $f_or_d)
    {
      if (is_file($f_or_d))
      {
        $this->files[] = realpath($f_or_d);
      }
      elseif (is_dir($f_or_d))
      {
        $this->register_dir($f_or_d);
      }
      else
      {
        throw new Exception(sprintf('The file or directory "%s" does not exist.', $f_or_d));
      }
    }
  }
  
  public function register_dir($directory)
  {
    if (!is_dir($directory))
    {
      throw new Exception(sprintf('The directory "%s" does not exist.', $directory));
    }

    $files = array();

    $current_dir = opendir($directory);
    while ($entry = readdir($current_dir))
    {
      if ($entry == '.' || $entry == '..') continue;

      if (is_dir($entry))
      {
        $this->register_dir($entry);
      }
      elseif (preg_match('#'.$this->extension.'$#', $entry))
      {
        $files[] = realpath($directory.DIRECTORY_SEPARATOR.$entry);
      }
    }

    $this->files = array_merge($this->files, $files);
  }

  protected function get_relative_file($file)
  {
    return str_replace(DIRECTORY_SEPARATOR, '/', str_replace(array(realpath($this->base_dir).DIRECTORY_SEPARATOR, $this->extension), '', $file));
  }
}