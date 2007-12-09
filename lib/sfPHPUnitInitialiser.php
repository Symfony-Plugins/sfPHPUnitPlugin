<?php
class sfPHPUnitInitialiser
{
  private $class = '';
  private $file = '';
  private $initialised = false;
  
  public function init($file)
  {
    $relative_file = str_replace('.php', '', basename($file));
    $this->class = basename($relative_file);
    
    // require, so phpunit doesn't have to (fail at) find it
    ob_start();
    require_once($file);
    ob_end_clean();
    
    if (!class_exists($this->class))
    {
      throw new Exception('Undefined class: ' . $this->class);
    }
    
    $this->file = $file;
    $this->initialised = true;
  }
  
  public function run($printer = null)
  {
    if (!$this->initialised)
    {
      throw new Exception('Test not initialised before running');
    }
    
    $arguments = array(
      'test' => $this->class,
      'testFile' => $this->file,
      'printer' => $printer
    );
    
    $runner = new sfPHPUnitTestRunner();
    
    $suite = $runner->getTest(
      $arguments['test'],
      $arguments['testFile']
    );
    
        if ($suite->testAt(0) instanceof PHPUnit_Framework_Warning &&
            strpos($suite->testAt(0)->getMessage(), 'No tests found in class') !== FALSE)
        {
            $skeleton = new PHPUnit_Util_Skeleton(
                $arguments['test'],
                $arguments['testFile']
            );

            $result = $skeleton->generate(TRUE);

            if (!$result['incomplete']) {
                eval(str_replace(array('<?php', '?>'), '', $result['code']));
                $suite = new PHPUnit_Framework_TestSuite($arguments['test'] . 'Test');
            }
        }

        try
        {
            $result = $runner->doRun(
              $suite,
              $arguments
            );
        }
        catch (Exception $e)
        {
            throw new RuntimeException(
              'Could not create and run test suite: ' . $e->getMessage()
            );
        }
        
        return $result;
  }
  
  public function getClass()
  {
    return $this->class;
  }
}