<?php
/**
 * This should really extend PHPUnit_Util_Log_TAP, but the methods and variables are all private
 * instead of protected, which means virtually replicating everything as it is.
 */

require_once 'sfPHPUnitTestPrinterTap.php';

class sfPHPUnitTestPrinterTapColour extends sfPHPUnitTestPrinterTap
{
  protected $coloriser = null;
    
    public function __construct()
    {
      $this->coloriser = new pakeColor();
    }

    /**
     * @param  PHPUnit_Framework_Test $test
     * @param  string                  $prefix
     * @param  string                  $directive
     * @access protected
     */
    protected function writeNotOk(PHPUnit_Framework_Test $test, $prefix = '', $directive = '')
    {
      $line = sprintf(
          "%s - %s%s%s",
          
          $this->coloriser->colorize(sprintf('%s %d', 'not ok', $this->testNumber), 'ERROR'),
          $prefix != '' ? $prefix . ': ' : '',
          PHPUnit_Util_Test::describe($test),
          $directive != '' ? ' # ' . $directive : ''
      );
      
      $this->writeln($line);
      
      $this->testSuccessful = FALSE;
    }
    
    protected function writeOk(PHPUnit_Framework_Test $test, $prefix = '', $directive = '')
    {
      if ($this->testSuccessful === TRUE)
      {
        $this->writeln(
          sprintf(
            "%s - %s",

            $this->coloriser->colorize(sprintf('%s %d', 'ok', $this->testNumber), 'INFO'),
            PHPUnit_Util_Test::describe($test)
          )
        );
      }
    }
    
    protected function writeComment($line)
    {
      $this->writeln(
      $this->coloriser->colorize($line, 'COMMENT')
      );
    }
    
    protected function writeErrorBar($line)
    {
      $this->writeln(
        $this->coloriser->colorize($line.str_repeat(' ', 71 - min(71, strlen($line))), 'RED_BAR')
      );
    }
    
    protected function writeSuccessBar($line)
    {
      $this->writeln(
        $this->coloriser->colorize($line.str_repeat(' ', 71 - min(71, strlen($line))), 'GREEN_BAR')
      );
    }
    
  protected function writeOkWithPrefix(PHPUnit_Framework_Test $test, $prefix)
  {
    $this->writeln(
      sprintf(
        "%s # %s %s",

        $this->coloriser->colorize(sprintf('%s %d', 'ok', $this->testNumber), 'INFO'),
        $prefix,
                PHPUnit_Util_Test::describe($test)
              )
            );
  }
}

pakeColor::style('ERROR', array('bg' => 'red', 'fg' => 'white', 'bold' => true));
pakeColor::style('INFO',  array('fg' => 'green', 'bold' => true));
pakeColor::style('PARAMETER', array('fg' => 'cyan'));
pakeColor::style('COMMENT',  array('fg' => 'yellow'));

pakeColor::style('GREEN_BAR',  array('fg' => 'white', 'bg' => 'green', 'bold' => true));
pakeColor::style('RED_BAR',  array('fg' => 'white', 'bg' => 'red', 'bold' => true));