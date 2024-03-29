<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

abstract class sfPHPUnitBaseSeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase
{
  // set default host/port/etc
  public function x__construct($name = null, array $data = array())
  {
  	parent::__construct(
  	  $name,
  	  $data,
  	  array(
  	    'host' => sfConfig::get('sf_selenium_rc_host') ? sfConfig::get('sf_selenium_rc_host') : 'localhost',
  	    'port' => sfConfig::get('sf_selenium_rc_port') ? sfConfig::get('sf_selenium_rc_port') : 4444,
  	  )
  	);
  	
  	$this->setBrowser(sfConfig::get('sf_selenium_rc_browser') ? sfConfig::get('sf_selenium_rc_browser') : '*firefox');
  }
}
