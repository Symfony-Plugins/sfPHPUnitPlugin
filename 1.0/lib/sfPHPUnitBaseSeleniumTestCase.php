<?php

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

abstract class sfPHPUnitBaseSeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase
{
  // set default host/port/etc
  public function __construct($name = null, array $data = array())
  {
  	parent::__construct(
  	  $name,
  	  $data,
  	  array(
  	    'host' => sfConfig::get('sf_selenium_rc_host'),
  	    'port' => sfConfig::get('sf_selenium_rc_port')
  	  )
  	);
  	
  	$this->setBrowser(sfConfig::get('sf_selenium_rc_browser'));
  }
}
