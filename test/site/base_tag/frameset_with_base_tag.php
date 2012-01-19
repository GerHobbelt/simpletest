<?php
    require_once('../self.php');
?><html>
    <head>
        <title>Frameset for testing of SimpleTest</title>
        <base href='<?php echo my_path() . '../'; ?>'>
    </head>
    <frameset rows="100%, *">
        <frame name="base" src="base_tag/page_1.html" />
        <noframes>
            This content is for no frames only.
        </noframes>
    </frameset>
</html>