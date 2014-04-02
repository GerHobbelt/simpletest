<?php
/**
 *	Fancy HTML outputter for Cognifty
 *  
 *	@package	SimpleTest
 *	@subpackage	UnitTester
 */

/**#@+
 *	include other SimpleTest class files
 */
/**#@-*/

/**
 *    Sample minimal test displayer. Generates only
 *    failure messages and a pass count.
 *	  @package SimpleTest
 *	  @subpackage UnitTester
 */
class FancyHtmlReporter extends SimpleReporter {
	var $_character_set;
	var $passfailtrail = array();

	/**
	 *    Does nothing yet. The first output will
	 *    be sent on the first test start. For use
	 *    by a web browser.
	 *    @access public
	 */
	function HtmlReporter($character_set = 'ISO-8859-1') {
		$this->SimpleReporter();
		$this->_character_set = $character_set;
	}

	/**
	 *    Paints the top of the web page setting the
	 *    title to the name of the starting test.
	 *    @param string $test_name      Name class of test.
	 *    @access public
	 */
	function paintHeader($test_name) {
		$this->sendNoCacheHeaders();
		print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
		print "<html>\n<head>\n<title>$test_name</title>\n";
		print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" .
			$this->_character_set . "\">\n";
		print "<style type=\"text/css\">\n";
		print $this->_getCss() . "\n";
		print "</style>\n";
		print "</head>\n<body>\n";
		print "<h1>$test_name</h1>\n";
		flush();
	}

	/**
	 *    Send the headers necessary to ensure the page is
	 *    reloaded on every request. Otherwise you could be
	 *    scratching your head over out of date test data.
	 *    @access public
	 *    @static
	 */
	function sendNoCacheHeaders() {
		if (! headers_sent()) {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
		}
	}

	/**
	 *    Paints the CSS. Add additional styles here.
	 *    @return string            CSS code as text.
	 *    @access protected
	 */
	function _getCss() {
		return ".fail { background-color: inherit; color: red; }" .
			".pass { background-color: inherit; color: green; }" .
			" pre { background-color: lightgray; color: inherit; }";
	}

	/**
	 *    Paints the end of the test with a summary of
	 *    the passes and failures.
	 *    @param string $test_name        Name class of test.
	 *    @access public
	 */
	function paintFooter($test_name) {
		$colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
		print "<div style=\"";
		print "padding: 8px; margin-top: 1em; background-color: $colour; color: white;";
		print "\">";
		print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
		print " test cases complete:\n";
		print "<strong>" . $this->getPassCount() . "</strong> passes, ";
		print "<strong>" . $this->getFailCount() . "</strong> fails and ";
		print "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
		print "</div>\n";

		$id =0;
		echo "<table border=\"2\" width=\"100%\">\n";
		foreach ($this->passfailtrail as $section => $fileStruct) {
			$id++;
			echo "\t<tr><th colspan=\"3\" align=\"left\">".$section."</th></tr>\n";
			foreach ($fileStruct as $file => $classStruct) {
				$id++;
				foreach ($classStruct as $class => $funcStruct) {
					$id++;
					echo "\t\t<tr><td width=\"50%\" valign=\"top\" rowspan=\"".(count($funcStruct)+1)."\">".$file." -&gt; ".$class."</td><td>passed / failed</td><td>Test</td></tr>\n";
					foreach ($funcStruct as $func => $passFail) {
						$id++;
						$pass = @count($passFail['pass']);
						$fail = @count($passFail['fail']);
						if ($pass > 0 ) {
							$passColor = "#CEC";
						} else {
							$passColor = "none";
						}
						if ($fail > 0 ) {
							$failColor = "#ECC";
						} else {
							$failColor = "none";
						}

						$passHtml = '';
						$failHtml = '';
						if ($pass) { 
							$passHtml = '<div id="p_'.$id.'" style="border:1px solid silver;background-color:#FFE;display:none;position:absolute;" onmouseout = "this.style.display=\'none\';">';
							foreach ($passFail['pass'] as $msg) {
								$passHtml .= htmlspecialchars($msg) ."<br/><br/>\n\n";
							}
							$passHtml .= '</div>';
						}
						if ($fail) { 
							$failHtml = '<div id="f_'.$id.'" style="border:1px solid silver;background-color:#FEE;display:none;position:absolute;" onmouseout = "this.style.display=\'none\';">';
							foreach ($passFail['fail'] as $msg) {
								$failHtml .= htmlspecialchars($msg) ."<br/><br/>\n\n";
							}
							$failHtml .= '</div>';
						}

						echo "\t\t\t<tr><td valign=\"top\">".$passHtml."\n".$failHtml."\n";
						echo "(<span onmouseover=\"document.getElementById('p_".$id."').style.display='block';\" style=\"background-color:".$passColor.";\">".$pass."</span>";
						echo "/<span onmouseover=\"document.getElementById('f_".$id."').style.display='block';\"style=\"background-color:".$failColor.";\">".$fail."</span>)";
						echo "</td><td>".$func."</td></tr>\n";
					}
				}
			}
		}
		echo "</table>\n";
		//    print "<pre>\n"; print_r($this->passfailtrail);echo "\n</pre>\n";
		print "</body>\n</html>\n";
	}

	/**
	 *    Paints the test failure with a breadcrumbs
	 *    trail of the nesting test suites below the
	 *    top level test.
	 *    @param string $message    Failure message displayed in
	 *                              the context of the other tests.
	 *    @access public
	 */
	function paintFail($message) {
		parent::paintFail($message);
		/*
			print "<span class=\"fail\">Fail</span>: ";
			array_shift($breadcrumb);
			print implode(" -&gt; ", $breadcrumb);
			print " -&gt; " . $this->_htmlEntities($message) . "<br />\n";
		 */

		$breadcrumb = $this->getTestList();
		$set = array_shift($breadcrumb);
		$file = basename(array_shift($breadcrumb));
		$class = array_shift($breadcrumb);
		$test = array_shift($breadcrumb);
		if (!isset($this->passfailtrail[$set]) ) {
			$this->passfailtrail[$set] = array();
		}
		if (!isset($this->passfailtrail[$set][$file]) ) {
			$this->passfailtrail[$set][$file] = array();
		}
		if (!isset($this->passfailtrail[$set][$file][$class]) ) {
			$this->passfailtrail[$set][$file][$class] = array();
		}
		if (!isset($this->passfailtrail[$set][$file][$class][$test]) ) {
			$this->passfailtrail[$set][$file][$class][$test] = array();
		}
		$this->passfailtrail[$set][$file][$class][$test]['fail'][] = $message;
	}

	/**
	 *    Paints the test failure with a breadcrumbs
	 *    trail of the nesting test suites below the
	 *    top level test.
	 *    @param string $message    Failure message displayed in
	 *                              the context of the other tests.
	 *    @access public
	 */
	function paintPass($message) {
		parent::paintPass($message);
		//           print "<span class=\"fail\">Pass</span>: ";
		$breadcrumb = $this->getTestList();
		$set = array_shift($breadcrumb);
		$file = basename(array_shift($breadcrumb));
		$class = array_shift($breadcrumb);
		$test = array_shift($breadcrumb);
		if (!isset($this->passfailtrail[$set]) ) {
			$this->passfailtrail[$set] = array();
		}
		if (!isset($this->passfailtrail[$set][$file]) ) {
			$this->passfailtrail[$set][$file] = array();
		}
		if (!isset($this->passfailtrail[$set][$file][$class]) ) {
			$this->passfailtrail[$set][$file][$class] = array();
		}
		if (!isset($this->passfailtrail[$set][$file][$class][$test]) ) {
			$this->passfailtrail[$set][$file][$class][$test] = array();
		}
		$this->passfailtrail[$set][$file][$class][$test]['pass'][] = $message;

		//print implode(" -&gt; ", $breadcrumb);
		//print " -&gt; " . $this->_htmlEntities($message) . "<br />\n";
	}

	/**
	 *    Paints a PHP error.
	 *    @param string $message        Message is ignored.
	 *    @access public
	 */
	function paintError($message) {
		parent::paintError($message);
		print "<span class=\"fail\">Exception</span>: ";
		$breadcrumb = $this->getTestList();
		array_shift($breadcrumb);
		print implode(" -&gt; ", $breadcrumb);
		print " -&gt; <strong>" . $this->_htmlEntities($message) . "</strong><br />\n";
	}

	/**
	 *    Paints a PHP exception.
	 *    @param Exception $exception        Exception to display.
	 *    @access public
	 */
	function paintException($exception) {
		parent::paintException($exception);
		print "<span class=\"fail\">Exception</span>: ";
		$breadcrumb = $this->getTestList();
		array_shift($breadcrumb);
		print implode(" -&gt; ", $breadcrumb);
		$message = 'Unexpected exception of type [' . get_class($exception) .
			'] with message ['. $exception->getMessage() .
			'] in ['. $exception->getFile() .
			' line ' . $exception->getLine() . ']';
		print " -&gt; <strong>" . $this->_htmlEntities($message) . "</strong><br />\n";
	}

	/**
	 *    Prints the message for skipping tests.
	 *    @param string $message    Text of skip condition.
	 *    @access public
	 */
	function paintSkip($message) {
		parent::paintSkip($message);
		print "<span class=\"pass\">Skipped</span>: ";
		$breadcrumb = $this->getTestList();
		array_shift($breadcrumb);
		print implode(" -&gt; ", $breadcrumb);
		print " -&gt; " . $this->_htmlEntities($message) . "<br />\n";
	}

	/**
	 *    Paints formatted text such as dumped variables.
	 *    @param string $message        Text to show.
	 *    @access public
	 */
	function paintFormattedMessage($message) {
		print '<pre>' . $this->_htmlEntities($message) . '</pre>';
	}

	/**
	 *    Character set adjusted entity conversion.
	 *    @param string $message    Plain text or Unicode message.
	 *    @return string            Browser readable message.
	 *    @access protected
	 */
	function _htmlEntities($message) {
		return htmlentities($message, ENT_COMPAT, $this->_character_set);
	}
}
?>
