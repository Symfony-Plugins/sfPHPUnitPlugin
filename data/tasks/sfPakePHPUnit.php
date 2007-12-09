<?php
/**
 * @package symfony.plugins
 * @subpackage sfPHPUnitPlugin
 * @author Joshua May (notjosh@gmail.com)
 * @version    SVN: $Id$
 */

pake_desc('runs all PHPunit tests');
pake_task('phpunit-all', 'project_exists');

pake_desc('runs PHPunit unit tests');
pake_task('phpunit-unit', 'project_exists');

pake_desc('runs PHPunit functional tests');
pake_task('phpunit-functional', 'project_exists');

function run_phpunit_unit($task, $args)
{
  _init_phpunit();

  if (isset($args[0]))
  {
    foreach ($args as $path)
    {
      $files = pakeFinder::type('file')->ignore_version_control()->follow_link()->name(basename($path).'Test.php')->in(sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.'unit'.DIRECTORY_SEPARATOR.dirname($path));
      foreach ($files as $file)
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

        $initialiser->run(new sfPHPUnitTestPrinterTapColour());
      }
    }
  }
  else
  {
    $h = new sfPHPUnitHarness();
    $h->base_dir = sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'unit';

    $finder = pakeFinder::type('file')->ignore_version_control()->follow_link()->name('*Test.php');
    $h->register($finder->in($h->base_dir));

    $h->run();
  }
}

function run_phpunit_functional($task, $args)
{
  _init_phpunit();

  $selenium_process = _init_selenium();

  if (isset($args[0]))
  {
    foreach ($args as $path)
    {
      $files = pakeFinder::type('file')->ignore_version_control()->follow_link()->name(basename($path).'Test.php')->in(sfConfig::get('sf_test_dir').DIRECTORY_SEPARATOR.'phpunit'.DIRECTORY_SEPARATOR.'functional'.DIRECTORY_SEPARATOR.dirname($path));
      foreach ($files as $file)
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

        $initialiser->run(new sfPHPUnitTestPrinterTapColour());
      }
    }
  }
  else
  {
    $h = new sfPHPUnitHarness();
    $h->base_dir = sfConfig::get('sf_test_dir') . DIRECTORY_SEPARATOR . 'phpunit' . DIRECTORY_SEPARATOR . 'functional';

    $finder = pakeFinder::type('file')->ignore_version_control()->follow_link()->name('*Test.php');
    $h->register($finder->in($h->base_dir));

    $h->run();
  }

  if (false !== $selenium_process)
  {
  	_terminate_selenium($selenium_process);
  }
}

function _init_phpunit()
{
  $phpUnitPath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . '/lib/PHPUnit');
  set_include_path($phpUnitPath . PATH_SEPARATOR . get_include_path());
  
  _init_symfony();
}

function _init_selenium()
{
  $host = sfConfig::get('sf_selenium_rc_host');
  $port = sfConfig::get('sf_selenium_rc_port');

  // probe for selenium running externally
  pake_echo_comment(
    sprintf(
      'checking for selenium on %s:%s',
      $host,
      $port
    )
  );

  $success = _probe_selenium($host, $port, 1);

  if ($success)
  {
    pake_echo_comment('selenium found! proceeding with tests..');
    return false;
  }

  pake_echo_comment('could not find selenium. will look for selenium .jars in /lib/selenium');

  // try and find selenium .jar, use it!
  // look in /lib/selenium
  $seleniumPath = sfConfig::get('sf_lib_dir') . DIRECTORY_SEPARATOR . 'selenium';
  $jar = pakeFinder::type('file')->ignore_version_control()->follow_link()->name('*selenium*server*.jar')->in($seleniumPath);

  if (0 === count($jar))
  {
    throw new Exception('could not find selenium-server.jar');
  }

  $jar = $jar[0];

  pake_echo_comment('found ' . basename($jar) . '! starting now...');

  $resource = _start_selenium($jar);

  if (false === $resource)
  {
    throw new Exception('could not launch selenium');
  }

  $success = _probe_selenium($host, $port, 5, 1);

  if (false === $success)
  {
    _terminate_selenium($resource);
    throw new Exception('selenium doesn\'t seem to be responding');
  }

  pake_echo_comment('selenium started');

  return $resource;
}

function _terminate_selenium($selenium_process)
{
  if (false === $selenium_process)
  {
  	return;
  }

  pake_echo_comment('selenium terminating');
  proc_terminate($selenium_process);
}

function _start_selenium($jar_path)
{
  $args = ' -port ' . sfConfig::get('sf_selenium_rc_port');
  $cmd = "java -jar $jar_path" . $args;
  $cmd = escapeshellcmd($cmd);

  $descriptorspec = array(
    0 => array('pipe', 'r'),
    1 => array('pipe', 'w'),
    2 => array('pipe', 'r')
  );

  $pipes = array();
  $resource = proc_open($cmd, $descriptorspec, $pipes);

  return $resource;
}

function _probe_selenium($host, $port, $attempts = 5, $timeout = 1)
{
  while (true)
  {
    $r = @fsockopen($host, $port);

    if (false !== $r)
    {
      return true;
    }
    else
    {
      if (--$attempts <= 0)
      {
        return false;
      }

      pake_echo_comment('.');
      sleep($timeout);
    }
  }

  return false;
}

function _init_symfony()
{
  define('SF_ROOT_DIR',    realpath(dirname(__file__).'/../../../..'));
  define('SF_APP',         'frontend');
  define('SF_ENVIRONMENT', 'dev');
  define('SF_DEBUG',       true);

  require_once(SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php'); 
}

function run_phpunit_all($task, $args)
{
  _init_phpunit();

  $h = new sfPHPUnitHarness();
  $h->base_dir = sfConfig::get('sf_test_dir');

  $finder = pakeFinder::type('file')->ignore_version_control()->follow_link()->name('*Test.php');
  $h->register($finder->in($h->base_dir));

  $h->run();
}