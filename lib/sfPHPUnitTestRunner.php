<?php
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Runner/BaseTestRunner.php';

require_once 'PHPUnit/Runner/StandardTestSuiteLoader.php';

require_once 'PHPUnit/TextUI/ResultPrinter.php';

class sfPHPUnitTestRunner extends PHPUnit_Runner_BaseTestRunner
{
  const SUCCESS_EXIT   = 0;
  const FAILURE_EXIT   = 1;
  const EXCEPTION_EXIT = 2;
    
  private $printer = null;

  public static function run($test, array $arguments = array())
  {
    if ($test instanceof ReflectionClass)
    {
      $test = new PHPUnit_Framework_TestSuite($test);
    }

    if ($test instanceof PHPUnit_Framework_Test)
    {
      $aTestRunner = new sfPHPUnitTestRunner();

      return $aTestRunner->doRun(
        $test,
        $arguments
      );
    }
    else
    {
      throw new InvalidArgumentException(
        'No test case or test suite found.'
      );
    }
  }
    
  public function doRun(PHPUnit_Framework_Test $suite, array $arguments = array())
  {
    $arguments['filter']             = isset($arguments['filter'])             ? $arguments['filter']             : FALSE;
    $arguments['group']              = isset($arguments['group'])              ? $arguments['group']              : array();
    $arguments['stopOnFailure']      = isset($arguments['stopOnFailure'])      ? $arguments['stopOnFailure']      : FALSE;
    $arguments['repeat']             = isset($arguments['repeat'])             ? $arguments['repeat']             : FALSE;
    $arguments['reportCharset']      = isset($arguments['reportCharset'])      ? $arguments['reportCharset']      : 'ISO-8859-1';
    $arguments['testDatabasePrefix'] = isset($arguments['testDatabasePrefix']) ? $arguments['testDatabasePrefix'] : '';
    $arguments['verbose']            = isset($arguments['verbose'])            ? $arguments['verbose']            : FALSE;
    $arguments['wait']               = isset($arguments['wait'])               ? $arguments['wait']               : FALSE;

    if (is_integer($arguments['repeat']))
    {
      $suite = new PHPUnit_Extensions_RepeatedTest(
        $suite, $arguments['repeat'], $arguments['filter'], $arguments['group']
      );
    }

    $result = $this->createTestResult();

    if ($arguments['stopOnFailure'])
    {
        $result->stopOnFailure(TRUE);
    }

    if ($this->printer === NULL)
    {
      if (isset($arguments['printer']) && $arguments['printer'] instanceof PHPUnit_Util_Printer)
      {
        $this->printer = $arguments['printer'];
      }
    }

    if ($this->printer instanceof PHPUnit_Framework_TestListener)
    {
      $result->addListener($this->printer);
    }

    $suite->run($result, $arguments['filter'], $arguments['group']);

    $result->flushListeners();

    if ($this->printer instanceof sfPHPUnitTestPrinter)
    {
      $this->printer->printResult($result);
    }

    $this->pause($arguments['wait']);

    return $result;
  }

  protected function pause($wait)
  {
   return;
  }
  
  protected function createTestResult()
  {
    return new PHPUnit_Framework_TestResult();
  }
  
  public function testStarted($testName)
  {
  }

  public function testEnded($testName)
  {
  }
  
  public function testFailed($status, PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e)
  {
  }

  protected function runFailed($message)
  {
    //self::printVersionString();
    //self::write($message);
    //exit(self::FAILURE_EXIT);
  }
}