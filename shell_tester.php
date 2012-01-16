<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once(dirname(__FILE__) . '/test_case.php');
/**#@-*/

/**
 *    Wrapper for exec() functionality.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleShell {
    protected $output;

    /**
     *    Executes the shell comand and stashes the output.
     *    @access public
     */
    function __construct() {
        $this->output = false;
    }

    /**
     *    Actually runs the command. Does not trap the
     *    error stream output as this need PHP 4.3+.
     *    @param string $command    The actual command line
     *                              to run.
     *    @return integer           Exit code.
     *    @access public
     */
    function execute($command) {
        $this->output = false;
        exec($this->fixPHPpathInCommand($command), $this->output, $ret);
        return $ret;
    }

    /**
     *    Accessor for the last output.
     *    @return string        Output as text.
     *    @access public
     */
    function getOutput() {
        return implode("\n", $this->output);
    }

    /**
     *    Accessor for the last output.
     *    @return array         Output as array of lines.
     *    @access public
     */
    function getOutputAsList() {
        return $this->output;
    }

	/**
	 *    Test whether the given 'php' binary path actually points to a valid and accessible php binary.
	 *
	 *    @return $exe path when the binary is valid, FALSE otherwise.
	 *    @access private
	 */
	private function checkPHPexePath($exe) 
	{
		if (is_executable($exe))
		{
            exec($exe . ' -v', $output, $exit_status);
			if ($exit_status === 0) 
			{
				if (0)
				{
					// but a lot of binaries on UNIX (and Windows) do work for the above command, so we make double sure:
					exec($exe . ' -i', $output, $exit_status);
					// ... and ascertain that the phpinfo() actually ran by looking for something which should always show up in there:
					if ($exit_status === 0 && strpos(implode('', $output), 'extension_dir') !== false) 
					{
						return $exe;
					}
				}
				else
				{
					// it's enough when '-v' produces a 'PHP n.n.n' response to ensure that the binary addressed is indeed a valid PHP engine:
					if (preg_match('/PHP\s*[4-9]\.[0-9]+\.[0-9]+/', implode('', $output)) == 1) 
					{
						return $exe;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 *    Test whether the given directory path actually contains a valid and accessible php binary.
	 *
	 *    @return the complete path to the PHP binary when a valid binary has been located, FALSE otherwise.
	 *    @access private
	 */
	private function expandPHPexePath($dir) 
	{
		$dir = (empty($dir) ? $dir : str_replace('//', '/', strtr($dir . '/', '\\', '/')));
		
		$version = explode('.', phpversion());
		$phpcli = array('php', 'php' . $version[0], 'php.exe', 'php' . $version[0] . '.exe');
		
		$fileset = array();
		foreach($phpcli as $binname)
		{
			$fileset[] = $dir . $binname;
		}
		
		// also accomodate environments where php binary names are augmented, e.g. 'php-5.2.8.exe', but
		// don't use glob() when we expect the platform shell to resolve our path issue for us at the same time:
		if (!empty($dir))
		{
			$scanarr = array('php*.exe');
			if (!isset($_SERVER['WINDIR']))
			{
				$scanarr[] = 'php*';	// only add this one on UNIX as it can produce a lot of clutter on Windows when scanning the 'extensions_dir' path train...
			}
			foreach($scanarr as $wildcard)
			{
				$rv = glob($dir . $wildcard);
				if (is_array($rv))
				{
					// strip out any .dll, .so, etc. as they're useless and should not be 'executed' anyhow:
					foreach($rv as $idx => $file)
					{
						if (preg_match('/\.(dll|so|a|o|com|lib|sh)$/', $file) == 1)
						{
							unset($rv[$idx]);
						}
					}
					$fileset = array_merge($fileset, $rv);
				}
			}
		}
			
		foreach($fileset as $file)
		{
			$rv = $this->checkPHPexePath($file);
			if ($rv !== false) return $rv;
		}
		return false;
	}
	
	/**
	 *    Test whether the given directory path OR any of its parent directories actually contains a valid and accessible php binary.
	 *
	 *    Note: This scan also looks in places where you might expect PHP to reside when you're running a LAMP/WAMP/XAMPP rig.
	 *
	 *    @return the complete path to the PHP binary when a valid binary has been located, FALSE otherwise.
	 *    @access private
	 */
	private function ascendedScan4PHPexePath($dir) 
	{
		if (!empty($dir))
		{
			$olddir = $dir . '/xxx'; // fake, done to suit the precondition for the next loop
			for (;;)
			{
				$dir = str_replace('//', '/', strtr($dir . '/', '\\', '/'));
				if (strlen($olddir) - strlen($dir) < 2) break;
			
				// LAMP/WAMP/XAMPP: also scan the ./php/ and ./php/bin/ branches:
				foreach(array('', '/php/', '/php/bin/') as $branch)
				{
					$exe = $this->expandPHPexePath($dir . $branch);
					if ($exe !== false) return $exe;
				}

				// fix for Windows path ascend: the shortest path on Windows is either 'C:/' i.e. 3 or '//?/c/' (UNC) or '/c/' (Cygwin/MingW)
				$olddir = $dir;
				$dir = dirname(substr($dir, 0, strlen($dir) - 1));
			}
		}
		return false;
	}
	
    /**
     *    Delivers the path to a usable PHP executable.
     *    This is useful for many installations which don't have 'php' (or 'php.exe')
     *    not readily available in the PATH.
     *
     *    @return the path to the PHP executable
     *    @access public
     */
    function getPHPexePath() {
        global $_SERVER;
        static $exe_path;

        if (empty($exe_path))
        {
            for ($state = 0; ; $state++)
            {
                switch($state)
                {
                case 0:
                    $exe = $this->expandPHPexePath('');
                    break;
                    
                case 1:
                    if (defined('PHP_BINDIR') && PHP_BINDIR !== '.')
                    {
                        $exe = $this->expandPHPexePath(PHP_BINDIR);
                        break;
                    }
                    continue 2;
                    
                case 2:
                    if (!empty($_SERVER['PHPRC']))
                    {
                        $exe = $this->expandPHPexePath($_SERVER['PHPRC']);
                        break;
                    }
                    continue 2;
                
				case 3:
					// http://stackoverflow.com/questions/2372624/get-current-php-executable-from-within-script
                    if (!empty($_SERVER['_']))
                    {
                        $exe = $this->checkPHPexePath($_SERVER['_']);
                        break;
                    }
                    continue 2;
				
				case 4:
					// once more inpired by http://stackoverflow.com/questions/2372624/get-current-php-executable-from-within-script
					//
					// This time around we expect either the path to PHP itself or to the PHP_CGI version or in case of apache running
					// with PHP as a module, the path to Apache.
					// Hence we do NOT assume that the binary path delivered via the pid lookup(s) below are necessarily PHP binaries
					// themselves; we perform a path-ascend-scan to check for other viable PHP locations as well:
					if (function_exists('posix_getpid'))
					{
						// Gets the PID of the current executable
						$pid = posix_getpid();

						// Returns the exact path to the PHP executable.
						$path = exec("readlink -f /proc/$pid/exe");
						if (!empty($path))
						{
							// don't test httpd server binaries themselves: pretty useless effort anyway:
							if (strpos($path, 'php') !== false)
							{
								$exe = $this->checkPHPexePath($path);
								if ($exe !== false) break;
							}
							$exe = $this->ascendedScan4PHPexePath(dirname($path));
							if ($exe !== false) break;
						}
					}
					else if (function_exists('getmypid'))
					{
						$pid = getmypid();
						$path = exec("readlink -f /proc/$pid/exe");
						if (!empty($path))
						{
							// don't test httpd server binaries themselves: pretty useless effort anyway:
							if (strpos($path, 'php') !== false)
							{
								$exe = $this->checkPHPexePath($path);
								if ($exe !== false) break;
							}
							$exe = $this->ascendedScan4PHPexePath(dirname($path));
							if ($exe !== false) break;
						}
					}
					// The above is kept as not everyone will have /proc/self/exe:
					$path = exec("readlink -f /proc/self/exe");
					if (!empty($path))
					{
						// don't test httpd server binaries themselves: pretty useless effort anyway:
						if (strpos($path, 'php') !== false)
						{
							$exe = $this->checkPHPexePath($path);
							if ($exe !== false) break;
						}
						$exe = $this->ascendedScan4PHPexePath(dirname($path));
						if ($exe !== false) break;
					}
                    continue 2;
					
				case 5:
					// on Windows you might need to look in a few other places, e.g. when you have a XAMPP or WAMP install:
					// using code from  http://www.apachehaus.com/forum/index.php?topic=38.0
					if (class_exists('COM'))
					{
						$wmi = new COM('winmgmts://');
						foreach(array('httpd', 'apache', 'php', 'php%') as $exename)
						{
							$processes = $wmi->ExecQuery("SELECT * FROM Win32_Process WHERE Name LIKE '$exename.exe'");
							foreach($processes as $process)
							{
								if (property_exists($process, 'CommandLine'))
								{
									if ($process->CommandLine{0} == '"')
									{
										$cmd = substr($process->CommandLine, 1);
										$cmd = substr($cmd, 0, strcspn($cmd, '"'));
									}
									else
									{
										$cmd = substr($process->CommandLine, 0, strcspn($cmd, " \t"));
									}
									// don't test the apache binaries themselves: pretty useless effort anyway:
									if (strpos($exename, 'php') !== false)
									{
										$exe = $this->checkPHPexePath($cmd);
										if ($exe !== false) break 3;
									}
									$exe = $this->ascendedScan4PHPexePath(dirname($cmd));
									if ($exe !== false) break 3;
								}
							}
						}
					}
					continue 2;
					
				case 6:
					// on Windows (and probably on some other platforms as well), we MAY expect to find the extensions quite near the php binary itself:
					$dir = ini_get('extension_dir');
                    if (!empty($dir))
                    {
						$exe = $this->ascendedScan4PHPexePath($dir);
						if ($exe !== false) break;
                    }
                    continue 2;
					
				case 7:
					// inspired by http://stackoverflow.com/questions/3889486/how-to-get-the-path-of-the-php-bin-from-php/3889630#3889630
					$paths = explode(PATH_SEPARATOR, getenv('PATH'));
					foreach ($paths as $path) 
					{
						$exe = $this->expandPHPexePath($path);
						if ($exe !== false) break 2;
					}
					continue 2;
					
                default:
                    break 2;
                }

				if (!empty($exe))
				{
                    $exe_path = $exe;
                    return $exe;
                }
            }
            
            // this will fail, but we pass it on anyhow...
            $exe_path = 'php';
            return 'php';
        }
        
        return $exe_path;
    }


    /**
     *    Process the command line so users can always write 'php yada yada ...' and not have to
     *    worry about where their PHP executable resides exactly.
     *
     *    @return processed command line
     *    @access public
     */
     function fixPHPpathInCommand($command)
     {
        $command = explode(' ', $command, 2);
        if ($command[0] === 'php')
        {
            $command[0] = $this->getPHPexePath();
        }
        return implode(' ', $command);
     }
}

/**
 *    Test case for testing of command line scripts and
 *    utilities. Usually scripts that are external to the
 *    PHP code, but support it in some way.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class ShellTestCase extends SimpleTestCase {
    protected $current_shell;
    protected $last_status;
    protected $last_command;

    /**
     *    Creates an empty test case. Should be subclassed
     *    with test methods for a functional test case.
     *    @param string $label     Name of test case. Will use
     *                             the class name if none specified.
     *    @access public
     */
    function __construct($label = false) {
        parent::__construct($label);
        $this->current_shell = $this->createShell();
        $this->last_status = false;
        $this->last_command = '';
    }

    /**
     *    Executes a command and buffers the results.
     *    @param string $command     Command to run.
     *    @return boolean            True if zero exit code.
     *    @access public
     */
    function execute($command) {
        $shell = $this->getShell();
        $this->last_status = $shell->execute($command);
        $this->last_command = $command;
        return ($this->last_status === 0);
    }

    /**
     *    Dumps the output of the last command.
     *    @access public
     */
    function dumpOutput() {
        $this->dump($this->getOutput());
    }

    /**
     *    Accessor for the last output.
     *    @return string        Output as text.
     *    @access public
     */
    function getOutput() {
        $shell = $this->getShell();
        return $shell->getOutput();
    }

    /**
     *    Accessor for the last output.
     *    @return array         Output as array of lines.
     *    @access public
     */
    function getOutputAsList() {
        $shell = $this->getShell();
        return $shell->getOutputAsList();
    }

    /**
     *    Called from within the test methods to register
     *    passes and failures.
     *    @param boolean $result    Pass on true.
     *    @param string $message    Message to display describing
     *                              the test state.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertTrue($result, $message = false) {
        return $this->assert(new TrueExpectation(), $result, $message);
    }

    /**
     *    Will be true on false and vice versa. False
     *    is the PHP definition of false, so that null,
     *    empty strings, zero and an empty array all count
     *    as false.
     *    @param boolean $result    Pass on false.
     *    @param string $message    Message to display.
     *    @return boolean           True on pass
     *    @access public
     */
    function assertFalse($result, $message = '%s') {
        return $this->assert(new FalseExpectation(), $result, $message);
    }

    /**
     *    Will trigger a pass if the two parameters have
     *    the same value only. Otherwise a fail. This
     *    is for testing hand extracted text, etc.
     *    @param mixed $first          Value to compare.
     *    @param mixed $second         Value to compare.
     *    @param string $message       Message to display.
     *    @return boolean              True on pass
     *    @access public
     */
    function assertEqual($first, $second, $message = "%s") {
        return $this->assert(
                new EqualExpectation($first),
                $second,
                $message);
    }

    /**
     *    Will trigger a pass if the two parameters have
     *    a different value. Otherwise a fail. This
     *    is for testing hand extracted text, etc.
     *    @param mixed $first           Value to compare.
     *    @param mixed $second          Value to compare.
     *    @param string $message        Message to display.
     *    @return boolean               True on pass
     *    @access public
     */
    function assertNotEqual($first, $second, $message = "%s") {
        return $this->assert(
                new NotEqualExpectation($first),
                $second,
                $message);
    }

    /**
     *    Tests the last status code from the shell.
     *    @param integer $status   Expected status of last
     *                             command.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertExitCode($status, $message = "%s") {
        $message = sprintf($message, "Expected status code of [$status] from [" .
                $this->last_command . "], but got [" .
                $this->last_status . "]");
        return $this->assertTrue($status === $this->last_status, $message);
    }

    /**
     *    Attempt to exactly match the combined STDERR and
     *    STDOUT output.
     *    @param string $expected  Expected output.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertOutput($expected, $message = "%s") {
        $shell = $this->getShell();
        return $this->assert(
                new EqualExpectation($expected),
                $shell->getOutput(),
                $message);
    }

    /**
     *    Scans the output for a Perl regex. If found
     *    anywhere it passes, else it fails.
     *    @param string $pattern    Regex to search for.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertOutputPattern($pattern, $message = "%s") {
        $shell = $this->getShell();
        return $this->assert(
                new PatternExpectation($pattern),
                $shell->getOutput(),
                $message);
    }

    /**
     *    If a Perl regex is found anywhere in the current
     *    output then a failure is generated, else a pass.
     *    @param string $pattern    Regex to search for.
     *    @param $message           Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertNoOutputPattern($pattern, $message = "%s") {
        $shell = $this->getShell();
        return $this->assert(
                new NoPatternExpectation($pattern),
                $shell->getOutput(),
                $message);
    }

    /**
     *    File existence check.
     *    @param string $path      Full filename and path.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertFileExists($path, $message = "%s") {
        $message = sprintf($message, "File [$path] should exist");
        return $this->assertTrue(file_exists($path), $message);
    }

    /**
     *    File non-existence check.
     *    @param string $path      Full filename and path.
     *    @param string $message   Message to display.
     *    @return boolean          True if pass.
     *    @access public
     */
    function assertFileNotExists($path, $message = "%s") {
        $message = sprintf($message, "File [$path] should not exist");
        return $this->assertFalse(file_exists($path), $message);
    }

    /**
     *    Scans a file for a Perl regex. If found
     *    anywhere it passes, else it fails.
     *    @param string $pattern    Regex to search for.
     *    @param string $path       Full filename and path.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertFilePattern($pattern, $path, $message = "%s") {
        return $this->assert(
                new PatternExpectation($pattern),
                implode('', file($path)),
                $message);
    }

    /**
     *    If a Perl regex is found anywhere in the named
     *    file then a failure is generated, else a pass.
     *    @param string $pattern    Regex to search for.
     *    @param string $path       Full filename and path.
     *    @param string $message    Message to display.
     *    @return boolean           True if pass.
     *    @access public
     */
    function assertNoFilePattern($pattern, $path, $message = "%s") {
        return $this->assert(
                new NoPatternExpectation($pattern),
                implode('', file($path)),
                $message);
    }

    /**
     *    Accessor for current shell. Used for testing the
     *    the tester itself.
     *    @return Shell        Current shell.
     *    @access protected
     */
    protected function getShell() {
        return $this->current_shell;
    }

    /**
     *    Factory for the shell to run the command on.
     *    @return Shell        New shell object.
     *    @access protected
     */
    protected function createShell() {
        return new SimpleShell();
    }
}
