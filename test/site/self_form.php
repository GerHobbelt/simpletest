<html>
    <head><title>Test of form self submission</title></head>
    <body>
        <form>
            <input type="hidden" name="secret" value="Wrong form">
        </form>
        <p>[<?php print (isset($_GET['visible']) ? $_GET['visible'] : '---'); ?>]</p>
        <p>[<?php print (isset($_GET['secret']) ? $_GET['secret'] : '---'); ?>]</p>
        <p>[<?php print (isset($_GET['again']) ? $_GET['again'] : '---'); ?>]</p>
        <form>
            <input type="text" name="visible">
            <input type="hidden" name="secret" value="Submitted">
            <input type="submit" name="again">
        </form>
        <!-- Bad form closing tag --></form>
    </body>
</html>