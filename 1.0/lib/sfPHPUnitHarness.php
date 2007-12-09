<?php
require_once 'PHPUnit/Runner/Version.php';

class sfPHPUnitHarness extends sfPHPUnitRegistration
{
  public $stats = array();
  public $output = null;
  
  public function __construct()
  {
    $this->output = new sfPHPUnitOutputColour();
  }

  private function process_test_output($lines)
  {
    foreach (explode("\n", $lines) as $text)
    {
      if (false !== strpos($text, 'not ok '))
      {
        ++$this->current_test;
        $test_number = (int) substr($text, 7);
        $this->stats[$this->current_file]['failed'][] = $test_number;

        ++$this->stats[$this->current_file]['nb_tests'];
        ++$this->stats['_nb_tests'];
      }
      else if (false !== strpos($text, 'ok '))
      {
        ++$this->stats[$this->current_file]['nb_tests'];
        ++$this->stats['_nb_tests'];
      }
      else if (preg_match('/^1\.\.(\d+)/', $text, $match))
      {
        $this->stats[$this->current_file]['plan'] = $match[1];
      }
    }

    return;
  }
  
  public function run()
  {
    if (!count($this->files))
    {
      throw new Exception('You must register some test files before running them!');
    }
    
    // sort the files to be able to predict the order
    sort($this->files);
    
    $this->stats = array(
      '_failed_files' => array(),
      '_failed_tests' => 0,
      '_nb_tests'     => 0,
    );
    
    foreach ($this->files as $file)
    {
      $initialiser = new sfPHPUnitInitialiser();
      
      try
      {
        $initialiser->init($file);
      }
      catch (Exception $e)
      {
        continue;
      }
      
      $this->stats[$file] = array(
        'plan'    => null,
        'nb_tests'  => 0,
        'failed'  => array(),
        'passed'  => array(),
      );
      
      $this->current_file = $file;
      $this->current_test = 0;
      $relative_file = basename($file);
      
      ob_start(array($this, 'process_test_output'));
      $initialiser->run(new sfPHPUnitTestPrinterTap());    /* @var $result PHPUnit_Framework_TestResult */
          ob_end_clean();

      $this->stats[$file]['status_code'] = 0;
      $this->stats[$file]['status'] = $this->stats[$file]['failed'] ? 'not ok' : 'ok';

      $this->output->echoln(
        sprintf(
          '%s%s%s',
          substr(
            $relative_file,
            -min(67, strlen($relative_file))
          ),
          str_repeat(
            '.',
            70 - min(67, strlen($relative_file))
          ),
          $this->stats[$file]['status']
        )
      );

      if (($nb = count($this->stats[$file]['failed'])))
      {
        if ($nb)
        {
          $this->output->echoln(sprintf("    Failed tests: %s", implode(', ', $this->stats[$file]['failed'])));
        }
        
        $this->stats['_failed_files'][] = $file;
        $this->stats['_failed_tests']  += $nb;
      }
    }

    if (count($this->stats['_failed_files']))
    {
      $format = "%-30s  %4s  %5s  %5s  %s";
      $this->output->echoln(sprintf($format, 'Failed Test', 'Stat', 'Total', 'Fail', 'List of Failed'));
      $this->output->echoln("------------------------------------------------------------------");
      foreach ($this->stats as $file => $file_stat)
      {
        if (!in_array($file, $this->stats['_failed_files']))
        {
          continue;
        }

        $relative_file = $this->get_relative_file($file);
        $this->output->echoln(sprintf($format, substr($relative_file, -min(30, strlen($relative_file))), $file_stat['status_code'], count($file_stat['failed']) + count($file_stat['passed']), count($file_stat['failed']), implode(' ', $file_stat['failed'])));
      }

      $this->output->red_bar(
        sprintf(
          'Failed %d/%d test scripts, %.2f%% okay. %d/%d subtests failed, %.2f%% okay.',
          $nb_failed_files = count($this->stats['_failed_files']),
          $nb_files = count($this->files),
          ($nb_files - $nb_failed_files) * 100 / $nb_files,
          $nb_failed_tests = $this->stats['_failed_tests'],
          $nb_tests = $this->stats['_nb_tests'],
          $nb_tests > 0 ? ($nb_tests - $nb_failed_tests) * 100 / $nb_tests : 0
        )
      );
    }
    else
    {
      $this->output->green_bar(' All tests successful.');
      $this->output->green_bar(sprintf(' Files=%d, Tests=%d', count($this->files), $this->stats['_nb_tests']));
    }

    return $this->stats['_failed_tests'] ? false : true;
  }
}