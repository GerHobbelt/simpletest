<?php
function my_path() {
    return preg_replace('|/[^/]*.php$|', '/', $_SERVER['SCRIPT_NAME']);  // or did you mean 'REQUEST_URI'? (Though that one has a few drawbacks compared to SCRIPT_NAME!)
}
