<?php
    /**
     *	base include file for SimpleTest
     *	@package	SimpleTest
     *	@subpackage	UnitTester
     *	@version	$Id$
     */

    /**
     *    Version specific reflection API.
	 *	  @package SimpleTest
	 *	  @subpackage UnitTester
     */
    class SimpleReflection {
        
        function classOrInterfaceExists($class) {
            return class_exists($class);
        }
        
        /**
         *    Needed to kill the autoload feature in PHP5
         *    for classes created dynamically.
         */
        function classOrInterfaceExistsSansAutoload($class) {
            if (version_compare(phpversion(), '5') >= 0) {
                return class_exists($class, false);
            }
            return class_exists($class);
        }
        
        function getMethods($class) {
            return get_class_methods($class);
        }
    }
?>
