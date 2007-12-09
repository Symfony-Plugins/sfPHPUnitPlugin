<?php

class sfPHPUnitContainer
{

}









require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Log/PMD.php';
require_once 'PHPUnit/Util/Log/TAP.php';
require_once 'PHPUnit/Util/Fileloader.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Getopt.php';
require_once 'PHPUnit/Util/Skeleton.php';
require_once 'PHPUnit/Util/TestDox/ResultPrinter/Text.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * A TestRunner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2007 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.0.0
 */
class PHPUnit_TextUI_Command
{
    /**
     * @access public
     * @static
     */
    public static function main()
    {
        $runner    = new sfPHPUnitTestRunner();

        $suite = $runner->getTest(
          $arguments['test'],
          $arguments['testFile'],
          $arguments['syntaxCheck']
        );

        if ($suite->testAt(0) instanceof PHPUnit_Framework_Warning &&
            strpos($suite->testAt(0)->getMessage(), 'No tests found in class') !== FALSE) {
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

        try {
            $result = $runner->doRun(
              $suite,
              $arguments
            );
        }

        catch (Exception $e) {
            throw new RuntimeException(
              'Could not create and run test suite: ' . $e->getMessage()
            );
        }

        if ($result->wasSuccessful()) {
            exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
        }

        else if($result->errorCount() > 0) {
            exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
        }

        else {
            exit(PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
        }
    }
}

define('PHPUnit_MAIN_METHOD', 'PHPUnit_TextUI_Command::main');
PHPUnit_TextUI_Command::main();
?>
