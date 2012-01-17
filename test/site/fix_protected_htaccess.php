<?php
    /*
	 * given
	 *   http://httpd.apache.org/docs/2.2/mod/mod_authn_file.html
	 * we'll need to fix your .htaccess regarding making:
	 *   AuthUserFile ./.htpasswd
	 * a line where the path to .htpasswd is either 'relative to root'
	 * or an absolute path, as specified in the Apache documentation.
	 *
	 * Since you may be running SimpleTest from about anywhere, we need
	 * to determine where we are, path-wise, in your situation and
	 * patch the .htaccess accordingly, before you can successfully
	 * run the ../acceptance_test.php tests (i.e. getting anything 
	 * other than a plethora of 500 response codes in return).
	 *
	 * For this to succeed, we'll need write access rights to your
	 * protected/.htaccess file, but the acceptance_test.php will make
	 * that abundantly clear.
	 */
?><html>
    <head><title>Protected Path config fixer</title></head>
    <body>
		<h1>Protected Path config fixer</h1>
		<p>This page should be requested before you can successfully run the acceptance_test test script, at least where it attempts to access the <a href="./protected/">./protected/</a> directory or its contents.</p>
		
		<h2>When do you need me?</h2>
		<p>This page must be requested (including a 'success' report below) after you've installed or otherwise fetched the SimpleTest source code.</p>
		
		<h2>Why do you need to see me?</h2>
		<p>If you don't and go straight for the goodies in the 'protected' section, then chances are pretty high that you will receive '500' error response codes from the server.</p>
		<p>Further investigation into that '500' response will then show that the server cannot find a proper .htpasswd file as it states 
		   per <a href="http://httpd.apache.org/docs/2.2/mod/mod_authn_file.html">http://httpd.apache.org/docs/2.2/mod/mod_authn_file.html</a>
		   that the <code>AuthUserFile</code> entry in there MUST either list an absolute or 'relative to DocumentRoot' path to said .htpasswd file.</p>
		<p>As your site (where you run SimpleTest, probably as part of a larger system) layout may very well not be identical to ours, you'll have to edit
		   the .htaccess file in the protected section to make the above come true for your situation. This web page performs the necessary site-specific
		   edit 'under the hood' (i.e. as a 'side effect') and reports success or failure of this action in the section below.</p>
		<blockquote>
			<p>Note that this page is requested and 'tested' at the very start of the SimpleTest acceptance_test.php script, so you wouldn't be bothered 
			   with all this, unless the script discovers that I do not have write access to the .htaccess in the protected section and you need to
			   help out manually.</p>
		</blockquote>
		
		<h1><code>.htaccess</code> patching of the protected section</h1>
<?php
	// DocumentRoot is a finicky thing in some (odd) situations, e.g. when residing in a bulk-virtual hosted environment where the mapping happens through
	// too-clever mixes of Alias-ing and mod_rewrite-ing. Hence 'relative to DocumentRoot' is not to be considered a 'sure-fire thing' and we go for
	// the 'absolute path' approach instead.
	//
	// Also note that we only write to .htaccess when our path differs from the one listed in there (and, of course, we have been granted write access).
	
	$protpath = strtr(dirname(__FILE__), '\\', '/');
	if (substr($protpath, -1) != '/')
	{
		$protpath .= '/';
	}
	$protpath .= 'protected/';
	
	function map_htaccess_line($line)
	{
		$line = strtr(trim($line), "\t", " ");
		if ($line{0} != '#' && ($pos = strpos($line, ' ')) !== false)
		{
			$a = array(substr($line, 0, $pos), trim(substr($line, $pos + 1)));
		}
		else
		{
			$a = array($line);
		}
		return $a;
	}
	function filter_htaccess_line($line)
	{
		$line = trim($line);
		return !empty($line);
	}
	
	try
	{
		$protfile = $protpath . '.htaccess';
		$protpwdfile = $protpath . '.htpasswd';
		$protcontent = @file_get_contents($protfile);
		if ($protcontent === false) throw new Exception('Cannot read file: ' . $protfile, 412);
		$protcontent = explode("\n", strtr($protcontent, "\r", "\n"));
		$protcontent = array_map('map_htaccess_line', array_filter($protcontent, 'filter_htaccess_line'));
		
		$edited = false;
		$found = false;
		foreach($protcontent as $key => $val)
		{
			if (count($val) == 2)
			{
				if ($val[0] === 'AuthUserFile')
				{
					$found = true;
					if ($val[1] !== $protpwdfile)
					{
						echo "<pre>$protpwdfile</pre>";
						$val[1] = $protpwdfile;
						$edited = true;
					}
				}
			}
			$protcontent[$key] = trim(implode(' ', $val));
		}
		
		if (!$found) throw new Exception('Protected area file: ' . $protfile . ' is missing a AuthUserFile line; is your .htaccess corrupted?', 417);
		
		if ($edited)
		{
			$protcontent = implode("\n", $protcontent);
			
			$rv = @file_put_contents($protfile, $protcontent);
			if ($rv < strlen($protcontent)) throw new Exception('Cannot/failed to write to file: ' . $protfile, 401);
			$msg = "Your " . $protfile . " has been <strong>successfully edited</strong>.";
			$code = 201;
		}
		else
		{
			$msg = "Your " . $protfile . " already contains the correct values.";
			$code = 200;
		}
	}
	catch (Exception $e)
	{
		$msg = $e->getMessage();
		$code = $e->getCode();
	}
	
	if ($code >= 200 && $code < 300)
	{
		$verdict = "Success";
	}
	else
	{
		$verdict = "Fail";
	}
	
	echo <<<EOT
		<form id="report">
			<label>Our result: </label><textarea name="edit_msg" style="width: 100%; height: 3em;" readonly="readonly">$msg</textarea>
			<label>Our result code: </label><input name="edit_code" value="$code" readonly="readonly"></input>
			<p>Hence our result is one of ...</p>
			<p style="font-size: 200%;" id="verdict">$verdict!</p>
		</form>
EOT;
?>
	</body>
</html>