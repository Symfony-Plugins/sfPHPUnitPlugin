<?php

class sfPHPUnitOutputColour
{
  public $coloriser = null;

  public function __construct()
  {
    $this->coloriser = new lime_colorizer();
  }
  
  

  function diag()
  {
    $messages = func_get_args();
    foreach ($messages as $message)
    {
      echo $this->coloriser->colorize('# '.join("\n# ", (array) $message), 'COMMENT')."\n";
    }
  }

  function comment($message)
  {
    echo $this->coloriser->colorize(sprintf('# %s', $message), 'COMMENT')."\n";
  }

  function echoln($message, $coloriser_parameter = null)
  {
    $message = preg_replace('/(?:^|\.)((?:not ok|dubious) *\d*)\b/e', '$this->coloriser->colorize(\'$1\', \'ERROR\')', $message);
    $message = preg_replace('/(?:^|\.)(ok *\d*)\b/e', '$this->coloriser->colorize(\'$1\', \'INFO\')', $message);
    $message = preg_replace('/"(.+?)"/e', '$this->coloriser->colorize(\'$1\', \'PARAMETER\')', $message);
    $message = preg_replace('/(\->|\:\:)?([a-zA-Z0-9_]+?)\(\)/e', '$this->coloriser->colorize(\'$1$2()\', \'PARAMETER\')', $message);

    echo ($coloriser_parameter ? $this->coloriser->colorize($message, $coloriser_parameter) : $message)."\n";
  }

  function green_bar($message)
  {
    echo $this->coloriser->colorize($message.str_repeat(' ', 71 - min(71, strlen($message))), 'GREEN_BAR')."\n";
  }

  function red_bar($message)
  {
    echo $this->coloriser->colorize($message.str_repeat(' ', 71 - min(71, strlen($message))), 'RED_BAR')."\n";
  }
}