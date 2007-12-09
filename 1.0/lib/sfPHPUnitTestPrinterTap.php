<?php

require_once 'PHPUnit/PHPUnit/Util/Printer.php';
require_once 'PHPUnit/PHPUnit/Framework/TestListener.php';

class sfPHPUnitTestPrinterTap extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{
  /**
   * @var    integer
   * @access protected
   */
  protected $testNumber = 0;

  /**
   * @var    boolean
   * @access protected
   */
  protected $testSuccessful = TRUE;
  
  protected $failCount = 0;

  /**
   * An error occurred.
   *
   * @param  PHPUnit_Framework_Test $test
   * @param  Exception              $e
   * @param  float                  $time
   * @access public
   */
  public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
  {
      $this->writeNotOk($test, 'Error');
      
      $this->failCount++;
  }

  /**
   * A failure occurred.
   *
   * @param  PHPUnit_Framework_Test                 $test
   * @param  PHPUnit_Framework_AssertionFailedError $e
   * @param  float                                  $time
   * @access public
   */
  public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
  {
    $this->writeNotOk($test, 'Failure');
    
    $location = $e->getLocation();
    
    $this->writeComment(
      sprintf(
        '#     Failed test (%s at line %d)',
        
        $location['file'],
        $location['line']
      )
    );
    
    if ($e->toString())
    {
      $this->writeComment(
        sprintf(
          '#       %s',
          
          $e->toString()
        )
      );
    }
    
    $this->failCount++;
  }

  /**
   * Incomplete test.
   *
   * @param  PHPUnit_Framework_Test $test
   * @param  Exception              $e
   * @param  float                  $time
   * @access public
   */
  public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
  {
    $this->writeOkWithPrefix($test, 'TODO');
    $this->testSuccessful = false;
  }

  /**
   * Skipped test.
   *
   * @param  PHPUnit_Framework_Test $test
   * @param  Exception              $e
   * @param  float                  $time
   * @access public
   * @since  Method available since Release 3.0.0
   */
  public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
  {
    $this->writeOkWithPrefix($test, 'SKIP');
    $this->testSuccessful = false;
  }

  /**
   * A testsuite started.
   *
   * @param  PHPUnit_Framework_TestSuite $suite
   * @access public
   */
  public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
  {
    if ($this->testNumber == 0)
    {
        $this->writeln(
          sprintf(
            "1..%d",

            count($suite)
          )
        );
    }

    $this->writeComment(
      sprintf(
        "# TestSuite%s started.",

        $suite->getName() ? sprintf(" \"%s\"", $suite->getName()) : ''
      )
    );
  }

  /**
   * A testsuite ended.
   *
   * @param  PHPUnit_Framework_TestSuite $suite
   * @access public
   */
  public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
  {
/*        $this->writeComment(
          sprintf(
            "# TestSuite \"%s\" ended.",

            $suite->getName()
          )
        );*/
      
    if (0 === $this->failCount)
    {
      $this->writeSuccessBar(" Looks like everything went fine.");
    }
    else
    {
      $this->writeErrorBar(
        sprintf(
          ' Looks like you failed %d test%s of %d.',
          
          $this->failCount,
          1 !== $this->failCount ? 's' : '',
          $suite->testCount()
        )
      );
    }
  }

  /**
   * A test started.
   *
   * @param  PHPUnit_Framework_Test $test
   * @access public
   */
  public function startTest(PHPUnit_Framework_Test $test)
  {
    $this->testNumber++;
    $this->testSuccessful = TRUE;
  }

  /**
   * A test ended.
   *
   * @param  PHPUnit_Framework_Test $test
   * @param  float                  $time
   * @access public
   */
  public function endTest(PHPUnit_Framework_Test $test, $time)
  {
    if ($this->testSuccessful)
    {
      $this->writeOk($test);
    }
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
        
    sprintf('%s %d', 'not ok', $this->testNumber),
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

      sprintf('%s %d', 'ok', $this->testNumber),
              PHPUnit_Util_Test::describe($test)
        )
      );
    }
  }
  
  protected function writeComment($line)
  {
    $this->writeln(
    $line
    );
  }
  
  protected function writeErrorBar($line)
  {
    $this->writeln(
      $line
    );
  }
  
  protected function writeSuccessBar($line)
  {
    $this->writeln(
      $line
    );
  }
    
  protected function writeOkWithPrefix(PHPUnit_Framework_Test $test, $prefix)
  {
    $this->writeln(
      sprintf(
        "%s # %s %s",

        sprintf('%s %d', 'ok', $this->testNumber),
        $prefix,
                PHPUnit_Util_Test::describe($test)
              )
            );
  }

  public function writeln($buffer)
  {
    $this->write($buffer . "\n");
  }

  public function echoln($buffer)
  {
    $this->writeln($buffer);
  }

  /**
   * @param  string $buffer
   * @access public
   */
  public function write($buffer)
  {
    parent::write($buffer);
  }
}