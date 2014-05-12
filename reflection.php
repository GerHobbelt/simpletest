<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 * The modes used for @see SimpleReflection::getSignature.
 *
 * Specifying one of these determines what should be generated exactly: invocation, declaration, argument set or assignment.
 */
define('SIG_GEN_DECLARE',               1);
define('SIG_GEN_INVOKE_AS_PARENT',      2);
define('SIG_GEN_DECLARE_ONLY_THE_ARGS', 3);
define('SIG_GEN_INVOKE_ONLY_THE_ARGS',  4);
define('SIG_GEN_ASSIGN_ONLY_THE_ARGS',  5);
/**#@-*/


/**
 *    Version specific reflection API.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleReflection {
    protected $interface;

    /**
     *    Stashes the class/interface.
     *    @param string $interface    Class or interface
     *                                to inspect.
     */
    function __construct($interface) {
        $this->interface = $interface;
    }

    /**
     *    Checks that a class has been declared. Versions
     *    before PHP5.0.2 need a check that it's not really
     *    an interface.
     *    @return boolean            True if defined.
     *    @access public
     */
    function classExists() {
        if (! class_exists($this->interface)) {
            return false;
        }
        $reflection = new ReflectionClass($this->interface);
        return ! $reflection->isInterface();
    }

    /**
     *    Needed to kill the autoload feature in PHP5
     *    for classes created dynamically.
     *    @return boolean        True if defined.
     *    @access public
     */
    function classExistsSansAutoload() {
        return class_exists($this->interface, false);
    }

    /**
     *    Checks that a class or interface has been
     *    declared.
     *    @return boolean            True if defined.
     *    @access public
     */
    function classOrInterfaceExists() {
        return $this->classOrInterfaceExistsWithAutoload($this->interface, true);
    }

    /**
     *    Needed to kill the autoload feature in PHP5
     *    for classes created dynamically.
     *    @return boolean        True if defined.
     *    @access public
     */
    function classOrInterfaceExistsSansAutoload() {
        return $this->classOrInterfaceExistsWithAutoload($this->interface, false);
    }

    /**
     *    Needed to select the autoload feature in PHP5
     *    for classes created dynamically.
     *    @param string $interface       Class or interface name.
     *    @param boolean $autoload       True totriggerautoload.
     *    @return boolean                True if interface defined.
     *    @access protected
     */
    protected function classOrInterfaceExistsWithAutoload($interface, $autoload) {
        if (function_exists('interface_exists')) {
            if (interface_exists($this->interface, $autoload)) {
                return true;
            }
        }
        return class_exists($this->interface, $autoload);
    }

    /**
     *    Gets the list of methods on a class or
     *    interface.
     *    @returns array              List of method names.
     *    @access public
     */
    function getMethods() {
        return array_unique(get_class_methods($this->interface));
    }

    /**
     *    Gets the list of interfaces from a class. If the
     *    class name is actually an interface then just that
     *    interface is returned.
     *    @returns array          List of interfaces.
     *    @access public
     */
    function getInterfaces() {
        $reflection = new ReflectionClass($this->interface);
        if ($reflection->isInterface()) {
            return array($this->interface);
        }
        return $this->onlyParents($reflection->getInterfaces());
    }

    /**
     *    Gets the list of methods for the implemented
     *    interfaces only.
     *    @returns array      List of enforced method signatures.
     *    @access public
     */
    function getInterfaceMethods() {
        $methods = array();
        foreach ($this->getInterfaces() as $interface) {
            $methods = array_merge($methods, get_class_methods($interface));
        }
        return array_unique($methods);
    }

    /**
     *    Checks to see if the method signature has to be tightly
     *    specified.
     *    @param string $method        Method name.
     *    @returns boolean             True if enforced.
     *    @access protected
     */
    protected function isInterfaceMethod($method) {
        return in_array($method, $this->getInterfaceMethods());
    }

    /**
     *    Finds the parent class name.
     *    @returns string      Parent class name.
     *    @access public
     */
    function getParent() {
        $reflection = new ReflectionClass($this->interface);
        $parent = $reflection->getParentClass();
        if ($parent) {
            return $parent->getName();
        }
        return false;
    }

    /**
     *    Trivially determines if the class is abstract.
     *    @returns boolean      True if abstract.
     *    @access public
     */
    function isAbstract() {
        $reflection = new ReflectionClass($this->interface);
        return $reflection->isAbstract();
    }

    /**
     *    Trivially determines if the class is an interface.
     *    @returns boolean      True if interface.
     *    @access public
     */
    function isInterface() {
        $reflection = new ReflectionClass($this->interface);
        return $reflection->isInterface();
    }

    /**
     *    Scans for final methods, as they screw up inherited
     *    mocks by not allowing you to override them.
     *    @returns boolean   True if the class has a final method.
     *    @access public
     */
    function hasFinal() {
        $reflection = new ReflectionClass($this->interface);
        foreach ($reflection->getMethods() as $method) {
            if ($method->isFinal()) {
                return true;
            }
        }
        return false;
    }

    /**
     *    Whittles a list of interfaces down to only the
     *    necessary top level parents.
     *    @param array $interfaces     Reflection API interfaces
     *                                 to reduce.
     *    @returns array               List of parent interface names.
     *    @access protected
     */
    protected function onlyParents($interfaces) {
        $parents = array();
        $blacklist = array();
        foreach ($interfaces as $interface) {
            foreach($interfaces as $possible_parent) {
                if ($interface->getName() == $possible_parent->getName()) {
                    continue;
                }
                if ($interface->isSubClassOf($possible_parent)) {
                    $blacklist[$possible_parent->getName()] = true;
                }
            }
            if (!isset($blacklist[$interface->getName()])) {
                $parents[] = $interface->getName();
            }
        }
        return $parents;
    }

    /**
     * Checks whether a method is abstract or not.
     * @param   string   $name  Method name.
     * @return  bool            true if method is abstract, else false
     * @access  protected
     */
    protected function isAbstractMethod($name) {
        $interface = new ReflectionClass($this->interface);
        if (! $interface->hasMethod($name)) {
            return false;
        }
        return $interface->getMethod($name)->isAbstract();
    }

    /**
     * Checks whether a method is the constructor.
     * @param   string   $name  Method name.
     * @return  bool            true if method is the constructor
     * @access  protected
     */
    protected function isConstructor($name) {
        return ($name == '__construct') || ($name == $this->interface);
    }

    /**
     * Checks whether a method is abstract in all parents or not.
     * @param   string   $name  Method name.
     * @return  bool            true if method is abstract in parent, else false
     * @access  protected
     */
    protected function isAbstractMethodInParents($name) {
        $interface = new ReflectionClass($this->interface);
        $parent = $interface->getParentClass();
        while($parent) {
            if (! $parent->hasMethod($name)) {
                return false;
            }
            if ($parent->getMethod($name)->isAbstract()) {
                return true;
            }
            $parent = $parent->getParentClass();
        }
        return false;
    }

    /**
     * Checks whether a method is static or not.
     * @param   string  $name   Method name
     * @return  bool            true if method is static, else false
     * @access  protected
     */
    protected function isStaticMethod($name) {
        $interface = new ReflectionClass($this->interface);
        if (! $interface->hasMethod($name)) {
            return false;
        }
        return $interface->getMethod($name)->isStatic();
    }

    /**
     *    Writes the source code matching the declaration / invocation
     *    of a method.
     *    @param string $name       Method name.
     *    @param integer $mode      Determines what should be generated exactly: invocation, declaration, argument set or assignment.
     *    @param mixed $propagator  Used by some of the modes to complete the response. Depends on the specified mode.
     *    @return string            Method signature up to last
     *                              bracket.
     *    @access public
     */
    function getSignature($name, $mode = SIG_GEN_DECLARE, $propagator = null) {
        $decl_prefix = ($mode == SIG_GEN_DECLARE ? 'function ' : '');
        $class = ($mode == SIG_GEN_INVOKE_AS_PARENT ? 'parent::' : '');
        $only_the_args = ($mode == SIG_GEN_INVOKE_ONLY_THE_ARGS || $mode == SIG_GEN_ASSIGN_ONLY_THE_ARGS || $mode == SIG_GEN_DECLARE_ONLY_THE_ARGS);

        $candidate_reply = false;
        if ($name == '__set') {
            $candidate_reply = $decl_prefix . $class . (!$only_the_args ? '__set($key, $value)' :  '$key, $value');
        }
        if ($name == '__call') {
            $candidate_reply = $decl_prefix . $class . (!$only_the_args ? '__call($method, $arguments)' : '$method, $arguments');
        }
        if (version_compare(phpversion(), '5.1.0', '>=')) {
            if (in_array($name, array('__get', '__isset', '__unset'))) {
                $candidate_reply = $decl_prefix . $class . (!$only_the_args ? $name . '($key)' : '$key');
            }
        }
        if ($name == '__toString') {
            $candidate_reply = $decl_prefix . $class . (!$only_the_args ? $name . '()' : '');
        }

        // This wonky try-catch is a work around for a faulty method_exists()
        // in early versions of PHP 5 which would return false for static
        // methods. The Reflection classes work fine, but hasMethod()
        // doesn't exist prior to PHP 5.1.0, so we need to use a more crude
        // detection method.
        if ($candidate_reply === false && !$only_the_args) {
            try {
                $interface = new ReflectionClass($this->interface);
                $interface->getMethod($name);
            } catch (ReflectionException $e) {
                $candidate_reply = $decl_prefix . $class . $name . '()';
            }
        }

        switch ($mode) {
        case SIG_GEN_ASSIGN_ONLY_THE_ARGS:
        default:
            if ($candidate_reply !== false) {
                trigger_error('Mock/Reflection does not (yet) support mode ' . $mode . ' for ' . $name);
                return '';
            }
            // fall through!
        case SIG_GEN_DECLARE_ONLY_THE_ARGS:
        case SIG_GEN_INVOKE_ONLY_THE_ARGS:
            if ($candidate_reply !== false) {
                if (is_array($propagator) && isset($propagator['prefix']) && !empty($candidate_reply)) {
                    $candidate_reply = $propagator['prefix'] . $candidate_reply;
                }
                if (is_array($propagator) && isset($propagator['postfix']) && !empty($candidate_reply)) {
                    $candidate_reply .= $propagator['postfix'];
                }
                return $candidate_reply;
            }
            else {
                return $this->getArgumentsSignature($name, $mode, $propagator);
            }

        case SIG_GEN_DECLARE:
        case SIG_GEN_INVOKE_AS_PARENT:
            if ($candidate_reply !== false) {
                return $candidate_reply;
            }
            else {
                return $this->getFullSignature($name, $mode, $propagator);
            }
        }
    }

    /**
     *    For a signature specified in an interface, full
     *    details must be replicated to be a valid implementation.
     *    @param string $name       Method name.
     *    @param integer $mode      Determines what should be generated exactly: invocation or declaration.
     *    @param mixed $propagator  Used to complete the response. Depends on the specified mode; may, for instance, contain a set of 'default values' for each of the arguments.
     *    @return string            Method signature up to last
     *                              bracket.
     *    @access protected
     */
    protected function getFullSignature($name, $mode, $propagator) {
        $decl_prefix = ($mode == SIG_GEN_DECLARE ? 'function ' : '');
        $interface = new ReflectionClass($this->interface);
        $method = $interface->getMethod($name);
        $reference = $method->returnsReference() ? '&' : '';
        $static = (($method->isStatic() && $mode == SIG_GEN_DECLARE) ? 'static ' : '');
        $class = ($mode == SIG_GEN_INVOKE_AS_PARENT ? ($method->isStatic() ? $this->interface . '::' : 'parent::') : '');
        return $static . $decl_prefix . $reference . $class . $name . '(' .
                implode(', ', $this->getParameterSignatures($method, $mode, $propagator)) .
                ')';
    }

    /**
     *    Generates the interface argument set in various forms (invocation, declaration, assignment).
     *    @param string $name       Method name.
     *    @param integer $mode      Determines what should be generated exactly: argument set for invocation, declaration or assignment.
     *    @param mixed $propagator  Used to complete the response. Depends on the specified mode; may, for instance, contain a set of 'default values' for each of the arguments.
     *    @return string            Method signature up to last
     *                              bracket.
     *    @access protected
     */
    protected function getArgumentsSignature($name, $mode, $propagator) {
        $interface = new ReflectionClass($this->interface);
        $method = $interface->getMethod($name);
        $reference = $method->returnsReference() ? '&' : '';
        $argset = $this->getParameterSignatures($method, $mode, $propagator);

        switch ($mode) {
        case SIG_GEN_DECLARE_ONLY_THE_ARGS:
        case SIG_GEN_INVOKE_ONLY_THE_ARGS:
            $ret = implode(', ', $argset);
            if (is_array($propagator) && isset($propagator['prefix']) && !empty($ret)) {
                $ret = $propagator['prefix'] . $ret;
            }
            if (is_array($propagator) && isset($propagator['postfix']) && !empty($ret)) {
                $ret .= $propagator['postfix'];
            }
            return $ret;

        case SIG_GEN_ASSIGN_ONLY_THE_ARGS:
            $ret = implode(";\n        ", $argset);
            if (!empty($ret)) {
                $ret .= ";\n";
            }
            return $ret;

        default:
            trigger_error('Mock/Reflection getArgumentsSignature() does not (yet) support mode ' . $mode . ' for ' . $name);
            return '';
        }
    }

    /**
     *    Gets the source code for each parameter.
     *    @param ReflectionMethod $method   Method object from reflection API
     *    @param mixed $propagator          Used to complete the response. Depends on the specified mode;
     *                                      may, for instance, contain a set of 'default values' for each of the arguments.
     *    @return array                     List of strings, each a snippet of code.
     *    @access protected
     */
    protected function getParameterSignatures($method, $mode, $propagator) {
        $signatures = array();
        foreach ($method->getParameters() as $parameter) {
            $signature = '';
            $type = $parameter->getClass();
            if ($mode == SIG_GEN_DECLARE || $mode == SIG_GEN_DECLARE_ONLY_THE_ARGS) {
                if (is_null($type) && version_compare(phpversion(), '5.1.0', '>=') && $parameter->isArray()) {
                    $signature .= 'array ';
                } elseif (!is_null($type)) {
                    $signature .= $type->getName() . ' ';
                }
                if ($parameter->isPassedByReference()) {
                    $signature .= '&';
                }
            }
            $varname = $this->suppressSpurious($parameter->getName());
            if ($mode != SIG_GEN_ASSIGN_ONLY_THE_ARGS || $this->isOptional($parameter)) {
                $signature .= '$' . $varname;
                if ($this->isOptional($parameter) && ($mode == SIG_GEN_DECLARE || $mode == SIG_GEN_ASSIGN_ONLY_THE_ARGS || $mode == SIG_GEN_DECLARE_ONLY_THE_ARGS)) {
                    if (is_array($propagator) && isset($propagator['assign']) && is_array($propagator['assign']) && isset($propagator['assign'][$varname])) {
                        $value = var_export($propagator['assign'][$varname], true);
                    }
                    else {
                        $value = 'null';
                    }
                    $signature .= ' = ' . $value;
                }
                $signatures[] = $signature;
            }
            else {
                // write an assignment statement only for non-optional arguments when the caller actually specified a value to set them to:
                if (is_array($propagator) && isset($propagator['assign']) && is_array($propagator['assign']) && isset($propagator['assign'][$varname])) {
                    $value = var_export($propagator['assign'][$varname], true);
                    $signature .= '$' . $varname . ' = ' . $value;
                    $signatures[] = $signature;
                }
            }
        }
        return $signatures;
    }

    /**
     *    The SPL library has problems with the
     *    Reflection library. In particular, you can
     *    get extra characters in parameter names :(.
     *    @param string $name    Parameter name.
     *    @return string         Cleaner name.
     *    @access protected
     */
    protected function suppressSpurious($name) {
        return str_replace(array('[', ']', ' '), '', $name);
    }

    /**
     *    Test of a reflection parameter being optional
     *    that works with early versions of PHP5.
     *    @param reflectionParameter $parameter    Is this optional.
     *    @return boolean                          True if optional.
     *    @access protected
     */
    protected function isOptional($parameter) {
        if (method_exists($parameter, 'isOptional')) {
            return $parameter->isOptional();
        }
        return false;
    }
}
