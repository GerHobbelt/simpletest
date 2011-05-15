<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../errors.php');
require_once(dirname(__FILE__) . '/../reflection_php5.php');

class AnyOldLeafClass {
	function aMethod() { }
}

abstract class AnyOldClass {
	function aMethod() { }
}

class AnyOldLeafClassWithAFinal {
	final function aMethod() { }
}

interface AnyOldInterface {
	function aMethod();
}

interface AnyOldArgumentInterface {
	function aMethod(AnyOldInterface $argument);
}

interface AnyDescendentInterface extends AnyOldInterface {
}

class AnyOldImplementation implements AnyOldInterface {
	function aMethod() { }
	function extraMethod() { }
}

abstract class AnyAbstractImplementation implements AnyOldInterface {
}

abstract class AnotherOldAbstractClass {
    protected abstract function aMethod(AnyOldInterface $argument);
}

class AnyOldSubclass extends AnyOldImplementation { }

class AnyOldArgumentClass {
	function aMethod($argument) { }
}

class AnyOldArgumentImplementation implements AnyOldArgumentInterface {
	function aMethod(AnyOldInterface $argument) { }
}

class AnyOldTypeHintedClass implements AnyOldArgumentInterface {
	function aMethod(AnyOldInterface $argument) { }
}

class AnyDescendentImplementation implements AnyDescendentInterface {
	function aMethod() { }
}

class AnyOldOverloadedClass {
	function __isset($key) { }
	function __unset($key) { }
}

class AnyOldClassWithStaticMethods {
	static function aStatic() { }
	static function aStaticWithParameters($arg1, $arg2) { }
}

abstract class AnyOldAbstractClassWithAbstractMethods {
    abstract function anAbstract();
    abstract function anAbstractWithParameter($foo);
    abstract function anAbstractWithMultipleParameters($foo, $bar);
}

class TestOfReflection extends UnitTestCase {

	function testClassExistence() {
		$reflection = new SimpleReflection('AnyOldLeafClass');
		$this->assertTrue($reflection->classOrInterfaceExists());
		$this->assertTrue($reflection->classOrInterfaceExistsSansAutoload());
		$this->assertFalse($reflection->isAbstract());
		$this->assertFalse($reflection->isInterface());
	}

	function testClassNonExistence() {
		$reflection = new SimpleReflection('UnknownThing');
		$this->assertFalse($reflection->classOrInterfaceExists());
		$this->assertFalse($reflection->classOrInterfaceExistsSansAutoload());
	}

	function testDetectionOfAbstractClass() {
		$reflection = new SimpleReflection('AnyOldClass');
		$this->assertTrue($reflection->isAbstract());
	}

	function testDetectionOfFinalMethods() {
		$reflection = new SimpleReflection('AnyOldClass');
		$this->assertFalse($reflection->hasFinal());
		$reflection = new SimpleReflection('AnyOldLeafClassWithAFinal');
		$this->assertTrue($reflection->hasFinal());
	}

	function testFindingParentClass() {
		$reflection = new SimpleReflection('AnyOldSubclass');
		$this->assertEqual($reflection->getParent(), 'AnyOldImplementation');
	}

	function testInterfaceExistence() {
		$reflection = new SimpleReflection('AnyOldInterface');
		$this->assertTrue($reflection->classOrInterfaceExists());
		$this->assertTrue($reflection->classOrInterfaceExistsSansAutoload());
		$this->assertTrue($reflection->isInterface());
	}

	function testMethodsListFromClass() {
		$reflection = new SimpleReflection('AnyOldClass');
		$this->assertIdentical($reflection->getMethods(), array('aMethod'));
	}

	function testMethodsListFromInterface() {
		$reflection = new SimpleReflection('AnyOldInterface');
		$this->assertIdentical($reflection->getMethods(), array('aMethod'));
		$this->assertIdentical($reflection->getInterfaceMethods(), array('aMethod'));
	}

	function testMethodsComeFromDescendentInterfacesASWell() {
		$reflection = new SimpleReflection('AnyDescendentInterface');
		$this->assertIdentical($reflection->getMethods(), array('aMethod'));
	}
	
	function testCanSeparateInterfaceMethodsFromOthers() {
		$reflection = new SimpleReflection('AnyOldImplementation');
		$this->assertIdentical($reflection->getMethods(), array('aMethod', 'extraMethod'));
		$this->assertIdentical($reflection->getInterfaceMethods(), array('aMethod'));
	}

	function testMethodsComeFromDescendentInterfacesInAbstractClass() {
		$reflection = new SimpleReflection('AnyAbstractImplementation');
		$this->assertIdentical($reflection->getMethods(), array('aMethod'));
	}

	function testInterfaceHasOnlyItselfToImplement() {
		$reflection = new SimpleReflection('AnyOldInterface');
		$this->assertEqual(
				$reflection->getInterfaces(),
				array('AnyOldInterface'));
	}

	function testInterfacesListedForClass() {
		$reflection = new SimpleReflection('AnyOldImplementation');
		$this->assertEqual(
				$reflection->getInterfaces(),
				array('AnyOldInterface'));
	}

	function testInterfacesListedForSubclass() {
		$reflection = new SimpleReflection('AnyOldSubclass');
		$this->assertEqual(
				$reflection->getInterfaces(),
				array('AnyOldInterface'));
	}

	function testNoParameterCreationWhenNoInterface() {
		$reflection = new SimpleReflection('AnyOldArgumentClass');
		$function = $reflection->getSignature('aMethod');
		if (version_compare(phpversion(), '5.0.2', '<=')) {
			$this->assertEqual('function amethod($argument)', strtolower($function));
		} else {
			$this->assertEqual('function aMethod($argument)', $function);
		}
	}

	function testParameterCreationWithoutTypeHinting() {
		$reflection = new SimpleReflection('AnyOldArgumentImplementation');
		$function = $reflection->getSignature('aMethod');
		if (version_compare(phpversion(), '5.0.2', '<=')) {
			$this->assertEqual('function amethod(AnyOldInterface $argument)', $function);
		} else {
			$this->assertEqual('function aMethod(AnyOldInterface $argument)', $function);
		}
	}

	function testParameterCreationForTypeHinting() {
		$reflection = new SimpleReflection('AnyOldTypeHintedClass');
		$function = $reflection->getSignature('aMethod');
		if (version_compare(phpversion(), '5.0.2', '<=')) {
			$this->assertEqual('function amethod(AnyOldInterface $argument)', $function);
		} else {
			$this->assertEqual('function aMethod(AnyOldInterface $argument)', $function);
		}
	}

	function testIssetFunctionSignature() {
		$reflection = new SimpleReflection('AnyOldOverloadedClass');
		$function = $reflection->getSignature('__isset');
		$this->assertEqual('function __isset($key)', $function);
	}
	
	function testUnsetFunctionSignature() {
		$reflection = new SimpleReflection('AnyOldOverloadedClass');
		$function = $reflection->getSignature('__unset');
		$this->assertEqual('function __unset($key)', $function);
	}

	function testProperlyReflectsTheFinalInterfaceWhenObjectImplementsAnExtendedInterface() {
		$reflection = new SimpleReflection('AnyDescendentImplementation');
		$interfaces = $reflection->getInterfaces();
		$this->assertEqual(1, count($interfaces));
		$this->assertEqual('AnyDescendentInterface', array_shift($interfaces));
	}
	
	function testCreatingSignatureForAbstractMethod() {
	    $reflection = new SimpleReflection('AnotherOldAbstractClass');
	    $this->assertEqual($reflection->getSignature('aMethod'), 'function aMethod(AnyOldInterface $argument)');
	}
	
	function testCanProperlyGenerateStaticMethodSignatures() {
		$reflection = new SimpleReflection('AnyOldClassWithStaticMethods');
		$this->assertEqual('static function aStatic()', $reflection->getSignature('aStatic'));
		$this->assertEqual(
			'static function aStaticWithParameters($arg1, $arg2)',
			$reflection->getSignature('aStaticWithParameters')
		);
	}
}

class TestOfReflectionWithTypeHints extends UnitTestCase {
	function skip() {
		$this->skipIf(version_compare(phpversion(), '5.1.0', '<'), 'Reflection with type hints only tested for PHP 5.1.0 and above');
	}

	function testParameterCreationForTypeHintingWithArray() {
		eval('interface AnyOldArrayTypeHintedInterface {
				  function amethod(array $argument);
			  } 
			  class AnyOldArrayTypeHintedClass implements AnyOldArrayTypeHintedInterface {
				  function amethod(array $argument) {}
			  }');
		$reflection = new SimpleReflection('AnyOldArrayTypeHintedClass');
		$function = $reflection->getSignature('amethod');
		$this->assertEqual('function amethod(array $argument)', $function);
	}
}

class TestOfAbstractsWithAbstractMethods extends UnitTestCase {
    function testCanProperlyGenerateAbstractMethods() {
        $reflection = new SimpleReflection('AnyOldAbstractClassWithAbstractMethods');
        $this->assertEqual(
            'function anAbstract()',
            $reflection->getSignature('anAbstract')
        );
        $this->assertEqual(
            'function anAbstractWithParameter($foo)',
            $reflection->getSignature('anAbstractWithParameter')
        );
        $this->assertEqual(
            'function anAbstractWithMultipleParameters($foo, $bar)',
            $reflection->getSignature('anAbstractWithMultipleParameters')
        );
    }
}

class AnyOldClassInvokingMethodsWithArgumentsInConstructor {
	function __construct($arg1 = 1, $arg2 = 2) { }
	function callMe($arg1, $arg2, $arg3 = 3, $arg4 = 4, $arg5 = 5, $arg6 = 6, $arg7 = 7) { }
	function callMeNil() { }
	function __isset($key) { }
	function __unset($key) { }
	static function aStatic() { }
	static function aStaticWithParameters($arg1, $arg2) { }
}
		
class TestOfMethodsReflectionGenerationModes extends UnitTestCase {

    private $old;

    function setUp() {
        $this->old = error_reporting(E_ALL);
        set_error_handler('SimpleTestErrorHandler');
    }

    function tearDown() {
        restore_error_handler();
        error_reporting($this->old);
    }

    function testCanProperlyGenerateSignatureModes() {
        $reflection = new SimpleReflection('AnyOldClassInvokingMethodsWithArgumentsInConstructor');
        $this->assertEqual(
            'function __construct($arg1 = null, $arg2 = null)',
            $reflection->getSignature('__construct')
        );
        $this->assertEqual(
            'function __construct($arg1 = null, $arg2 = null)',
            $reflection->getSignature('__construct', SIG_GEN_DECLARE)
        );
        $this->assertEqual(
            'parent::__construct($arg1, $arg2)',
            $reflection->getSignature('__construct', SIG_GEN_INVOKE_AS_PARENT)
        );
        $this->assertEqual(
            '$arg1 = null, $arg2 = null',
            $reflection->getSignature('__construct', SIG_GEN_DECLARE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            '$arg1, $arg2',
            $reflection->getSignature('__construct', SIG_GEN_INVOKE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            "\$arg1 = null;\n        \$arg2 = null;\n",
            $reflection->getSignature('__construct', SIG_GEN_ASSIGN_ONLY_THE_ARGS)
        );
		
        $this->assertEqual(
            'function callMe($arg1, $arg2, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null, $arg7 = null)',
            $reflection->getSignature('callMe')
        );
        $this->assertEqual(
            'function callMe($arg1, $arg2, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null, $arg7 = null)',
            $reflection->getSignature('callMe', SIG_GEN_DECLARE)
        );
        $this->assertEqual(
            'parent::callMe($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7)',
            $reflection->getSignature('callMe', SIG_GEN_INVOKE_AS_PARENT)
        );
        $this->assertEqual(
            '$arg1, $arg2, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null, $arg7 = null',
            $reflection->getSignature('callMe', SIG_GEN_DECLARE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            '$arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7',
            $reflection->getSignature('callMe', SIG_GEN_INVOKE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            "\$arg3 = null;\n        \$arg4 = null;\n        \$arg5 = null;\n        \$arg6 = null;\n        \$arg7 = null;\n",
            $reflection->getSignature('callMe', SIG_GEN_ASSIGN_ONLY_THE_ARGS)
        );
		
        $this->assertEqual(
            'function callMeNil()',
            $reflection->getSignature('callMeNil')
        );
        $this->assertEqual(
            'function callMeNil()',
            $reflection->getSignature('callMeNil', SIG_GEN_DECLARE)
        );
        $this->assertEqual(
            'parent::callMeNil()',
            $reflection->getSignature('callMeNil', SIG_GEN_INVOKE_AS_PARENT)
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('callMeNil', SIG_GEN_DECLARE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('callMeNil', SIG_GEN_INVOKE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            "",
            $reflection->getSignature('callMeNil', SIG_GEN_ASSIGN_ONLY_THE_ARGS)
        );
    }

    function testCanProperlyGenerateSignatureModesWithExtras() {
        $reflection = new SimpleReflection('AnyOldClassInvokingMethodsWithArgumentsInConstructor');
        $this->assertEqual(
            "function __construct(\$arg1 = 'A10', \$arg2 = 'B20')",
            $reflection->getSignature('__construct', SIG_GEN_DECLARE, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            "parent::__construct(\$arg1, \$arg2)",
            $reflection->getSignature('__construct', SIG_GEN_INVOKE_AS_PARENT, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            "\$arg1 = 'A10';\n        \$arg2 = 'B20';\n",
            $reflection->getSignature('__construct', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            'PREFIX::$arg1 = null, $arg2 = null::POSTFIX',
            $reflection->getSignature('__construct', SIG_GEN_DECLARE_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            'PREFIX::$arg1, $arg2::POSTFIX',
            $reflection->getSignature('__construct', SIG_GEN_INVOKE_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            "\$arg1 = null;\n        \$arg2 = null;\n",
            $reflection->getSignature('__construct', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );

        $this->assertEqual(
            "\$arg1 = 'A10';\n        \$arg2 = 'B20';\n",
            $reflection->getSignature('__construct', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20'), 'prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            'PREFIX::$arg1 = \'A10\', $arg2 = null::POSTFIX',
            $reflection->getSignature('__construct', SIG_GEN_DECLARE_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10'), 'prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
    }
	
    function testCopingWithSpecialMethodCall() {
        $reflection = new SimpleReflection('AnyOldClassInvokingMethodsWithArgumentsInConstructor');
        $this->assertEqual(
            'function __call($method, $arguments)',
            $reflection->getSignature('__call')
        );
        $this->assertEqual(
            'function __call($method, $arguments)',
            $reflection->getSignature('__call', SIG_GEN_DECLARE)
        );
        $this->assertEqual(
            'parent::__call($method, $arguments)',
            $reflection->getSignature('__call', SIG_GEN_INVOKE_AS_PARENT)
        );
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __call/'));
        $this->assertEqual(
            '$method, $arguments',
            $reflection->getSignature('__call', SIG_GEN_DECLARE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            '$method, $arguments',
            $reflection->getSignature('__call', SIG_GEN_INVOKE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__call', SIG_GEN_ASSIGN_ONLY_THE_ARGS)
        );
		
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __call/'));
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __call/'));
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __call/'));
        $this->assertEqual(
            'function __call($method, $arguments)',
            $reflection->getSignature('__call', SIG_GEN_DECLARE, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            'parent::__call($method, $arguments)',
            $reflection->getSignature('__call', SIG_GEN_INVOKE_AS_PARENT, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__call', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            'PREFIX::$method, $arguments::POSTFIX',
            $reflection->getSignature('__call', SIG_GEN_DECLARE_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            'PREFIX::$method, $arguments::POSTFIX',
            $reflection->getSignature('__call', SIG_GEN_INVOKE_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__call', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );

        $this->assertEqual(
            '',
            $reflection->getSignature('__call', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20'), 'prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            'PREFIX::$method, $arguments::POSTFIX',
            $reflection->getSignature('__call', SIG_GEN_DECLARE_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10'), 'prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
    }
	
    function testCopingWithSpecialMethodtoString() {
        $reflection = new SimpleReflection('AnyOldClassInvokingMethodsWithArgumentsInConstructor');
        $this->assertEqual(
            'function __toString()',
            $reflection->getSignature('__toString')
        );
        $this->assertEqual(
            'function __toString()',
            $reflection->getSignature('__toString', SIG_GEN_DECLARE)
        );
        $this->assertEqual(
            'parent::__toString()',
            $reflection->getSignature('__toString', SIG_GEN_INVOKE_AS_PARENT)
        );
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __toString/'));
        $this->assertEqual(
            '',
            $reflection->getSignature('__toString', SIG_GEN_DECLARE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__toString', SIG_GEN_INVOKE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__toString', SIG_GEN_ASSIGN_ONLY_THE_ARGS)
        );
		
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __toString/'));
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __toString/'));
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __toString/'));
        $this->assertEqual(
            'function __toString()',
            $reflection->getSignature('__toString', SIG_GEN_DECLARE, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            'parent::__toString()',
            $reflection->getSignature('__toString', SIG_GEN_INVOKE_AS_PARENT, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__toString', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__toString', SIG_GEN_DECLARE_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__toString', SIG_GEN_INVOKE_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__toString', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );

        $this->assertEqual(
            '',
            $reflection->getSignature('__toString', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20'), 'prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__toString', SIG_GEN_DECLARE_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10'), 'prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
    }
}	

		
class TestOfMethodsReflectionForPHP51andAbove extends UnitTestCase {

    private $old;

    function setUp() {
        $this->old = error_reporting(E_ALL);
        set_error_handler('SimpleTestErrorHandler');
    }

    function tearDown() {
        restore_error_handler();
        error_reporting($this->old);
    }

    function skip() {
        $this->skipIf(
                version_compare(phpversion(), '5.1', '<'),
                '__unset does not exist as a special function for PHP version up to PHP 5.1');
    }

    function testCopingWithSpecialMethodunsetPHP51() {
        $reflection = new SimpleReflection('AnyOldClassInvokingMethodsWithArgumentsInConstructor');
        $this->assertEqual(
            'function __unset($key)',
            $reflection->getSignature('__unset')
        );
        $this->assertEqual(
            'function __unset($key)',
            $reflection->getSignature('__unset', SIG_GEN_DECLARE)
        );
        $this->assertEqual(
            'parent::__unset($key)',
            $reflection->getSignature('__unset', SIG_GEN_INVOKE_AS_PARENT)
        );
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __unset/'));
        $this->assertEqual(
            '$key',
            $reflection->getSignature('__unset', SIG_GEN_DECLARE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            '$key',
            $reflection->getSignature('__unset', SIG_GEN_INVOKE_ONLY_THE_ARGS)
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__unset', SIG_GEN_ASSIGN_ONLY_THE_ARGS)
        );
		
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __unset/'));
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __unset/'));
        $this->expectError(new PatternExpectation('/Reflection does not \(yet\) support mode ' . SIG_GEN_ASSIGN_ONLY_THE_ARGS . ' for __unset/'));
        $this->assertEqual(
            'function __unset($key)',
            $reflection->getSignature('__unset', SIG_GEN_DECLARE, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            'parent::__unset($key)',
            $reflection->getSignature('__unset', SIG_GEN_INVOKE_AS_PARENT, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__unset', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20')))
        );
        $this->assertEqual(
            'PREFIX::$key::POSTFIX',
            $reflection->getSignature('__unset', SIG_GEN_DECLARE_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            'PREFIX::$key::POSTFIX',
            $reflection->getSignature('__unset', SIG_GEN_INVOKE_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            '',
            $reflection->getSignature('__unset', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );

        $this->assertEqual(
            '',
            $reflection->getSignature('__unset', SIG_GEN_ASSIGN_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10', 'arg2' => 'B20'), 'prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
        $this->assertEqual(
            'PREFIX::$key::POSTFIX',
            $reflection->getSignature('__unset', SIG_GEN_DECLARE_ONLY_THE_ARGS, array('assign' => array('arg1' => 'A10'), 'prefix' => 'PREFIX::', 'postfix' => '::POSTFIX'))
        );
    }
}	
		

?>