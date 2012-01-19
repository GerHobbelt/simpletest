<?php
    require_once('self.php');
	
	$url = my_path();
    header('Location: ' . $url . 'network_confirm.php?r=rrr');
?><html>
    <head><title>Redirection test</title></head>
    <body>This is a test page for the SimpleTest PHP unit tester</body>
</html>