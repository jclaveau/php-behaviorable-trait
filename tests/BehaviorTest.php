<?php
namespace JClaveau\PhpBehaviorableTrait;

/**
 * Checks that the BehaviorableTrait works properly.
 */
class BehaviorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @brief Checks that a method from the behavior is called
	 */
	function testCallOfBehaviorMethod()
	{
		$object = new TestedBehaviorOwner();
		$object->attachBehavior('name', new TestedBehavior);

		$out = $object->methodOfBehavior();

		$this->assertEquals('methodOfBehavior called :)', $out);
	}

	/**
	 * @brief Checks that a call of an undefined method generates an exception
	 */
	function testCallOfUndefinedMethod()
	{
		$object = new TestedBehaviorOwner();
		$object->attachBehavior('name', new TestedBehavior);

		try {
			$out = $object->undefinedMethod();
		} catch (\Exception $e) {
			$out = $e->getMessage();
		}

		$this->assertEquals('Undefined method '.__NAMESPACE__.'\TestedBehaviorOwner::undefinedMethod() in class and its behaviors.', $out);
	}

	/**
	 * @brief Checks that a property from a behavior is retrieved
	 */
	public function testGetPropertyFromBehavior()
	{
		$object = new TestedBehaviorOwner();

		// get provoking exception
		try {
			$object->propertyOfTheBehavior;
		} catch (\Exception $e) {
			$msg = $e->getMessage();
		}

		$this->assertEquals('Undefined property: '.__NAMESPACE__.'\TestedBehaviorOwner::$propertyOfTheBehavior', $msg);

		// working get
		$object->attachBehavior('name', new TestedBehavior);
		$out = $object->propertyOfTheBehavior;

		$this->assertEquals(':)', $out);
	}

	/**
	 * @brief Checks that setting property won't change PHP's normal behavior
	 */
	public function testSetPropertyAsNormalPHP()
	{
		// set of undefined property (same )
		$object = new TestedBehaviorOwner();
		$object->propertyOfTheBehavior = 'lala';
		$this->assertEquals('lala', $object->propertyOfTheBehavior);

		// the behavior is now useless
		$behavior = new TestedBehavior;
		$object->attachBehavior('name', $behavior);
		$object->propertyOfTheBehavior = 'lolo';

		$this->assertEquals('lolo', $object->propertyOfTheBehavior);
		$this->assertEquals(':)', $behavior->propertyOfTheBehavior);
	}

	/**
	 * @brief Checks that setting an existing property of a behavior impacts
	 *        this behavior only.
	 */
	public function testSetPropertyFromBehavior()
	{
		// the behavior is now useless
		$object   = new TestedBehaviorOwner();
		$behavior = new TestedBehavior;
		$object->attachBehavior('name', $behavior);
		$object->propertyOfTheBehavior = 'lolo';

		$this->assertEquals('lolo', $object->propertyOfTheBehavior);
		$this->assertEquals('lolo', $behavior->propertyOfTheBehavior);
	}

	/**
	 * @brief Checks that the owner is accessible from the behavior
	 */
	public function testOwner()
	{
		$object   = new TestedBehaviorOwner();
		$behavior = new TestedBehavior;
		$object->attachBehavior('name', $behavior);

		$this->assertEquals($object, $behavior->owner);
	}

	/**
	 * @brief Checks that isset works with behaviors
	 */
	public function testIsset()
	{
		$object   = new TestedBehaviorOwner();
		$behavior = new TestedBehavior;
		$object->attachBehavior('name', $behavior);

		$this->assertTrue(isset($object->propertyOfTheBehavior));
	}

	/**
	 * @brief Checks that unset works with behaviors.
	 *        Unset will throw an exception only if the targetted property is protected
	 */
	public function testUnset()
	{
		$object   = new TestedBehaviorOwner();
		$behavior = new TestedBehavior;
		$object->attachBehavior('name', $behavior);

		// unset of a protected roperty throws an error
		try {
			$exceptionLine = __LINE__ + 1;
			unset($object->protectedProperty);
		} catch (\Exception $e) {
			$msg = $e->getMessage();
		}
		$this->assertEquals('Cannot access protected property '.__NAMESPACE__
			.'\TestedBehaviorOwner::$protectedProperty in '. __FILE__
			.' on line '.$exceptionLine, $msg);
		$this->assertTrue(isset($object->propertyOfTheBehavior));

		// unset of a public property works
		unset($object->publicProperty);
		$this->assertFalse(isset($object->publicProperty));

		// unset of inexistant property doesn't throw anything
		unset($object->lalalala);
	}

	/**/
}

/**
 *
 */
class TestedBehaviorOwner
{
	use BehaviorableTrait;

	protected $protectedProperty = ':)';
	public    $publicProperty    = 'public :)';
}

/**
 *
 */
class TestedBehavior extends Behavior
{
	public $propertyOfTheBehavior = ':)';

	public function methodOfBehavior()
	{
		return 'methodOfBehavior called :)';
	}
}


