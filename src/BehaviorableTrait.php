<?php
/**
 * Provides an easy way to enable decorator/composition pattern in PHP 5.4+.
 * @see https://en.wikipedia.org/wiki/Decorator_pattern.
 *
 * This class is mainly inspired from the behaviors of Yii2 simplified and couple
 * with the use of traits that makes the inheritence optionnal.
 *
 * @see https://github.com/yiisoft/yii2/blob/master/framework/base/Component.php
 * @see https://github.com/yiisoft/yii2/blob/master/framework/base/Behavior.php
 *
 * Philosophical question : Should we follow the Yii way of unset/isset (which
 * doesn't unset the properties but set them as null) or the PHP which trully unset
 * properties so they don't exist anymore, even if it is useless?
 */
namespace JClaveau\PhpBehaviorableTrait;

trait BehaviorableTrait
{
	/** @var array */
	protected $behaviors = [];

	/**
	 * @brief  Attaches a behavior from the object having this trait.
	 * @param  string   $name
	 * @param  Behavior $behavior
	 */
	public function attachBehavior($name, $behavior)
	{
		$this->behaviors[$name] = $behavior;
		$behavior->owner        = $this;
	}

	/**
	 * @brief  Detaches a behavior from the object having this trait.
	 * @param  string $name The name
	 */
	public function detachBehavior($name)
	{
		unset($this->behaviors[$name]);
	}

	/**
	 * @brief  Look for a property in the behaviors
	 * @param  string $name
	 * @return The value of the property if found
	 */
	public function __get($name)
	{
		foreach ($this->behaviors as $behavior) {
			try {
				return $behavior->$name;
			} catch(\Exception $e) {
				continue;
			}
		}

		// This is only called to trigger the normal PHP Exception
		return $this->$name;
	}

	/**
	 * @brief Sets a property
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __set($name, $value)
	{
		foreach ($this->behaviors as $behavior) {
			if (isset($behavior->$name)) {
				$behavior->$name = $value;
				return;
			}

			if (method_exists($behavior, '__get')) {
				try {
					$behavior->$name = $value;
					return;
				} catch (\Exception $e) {
					continue;
				}
			}
		}

		$this->$name = $value;
	}

	/**
	 * @brief  Calls a method from the current object or its behaviors
	 * @param  string $name    The name of the called method
	 * @param  array  $params  The parameters of the called method
	 * @return mixed           The return of the called method
	 * @throws \ErrorException If the method is undefined
	 */
	public function __call($name, $params)
	{
		foreach ($this->behaviors as $behavior) {
			if (is_callable([$behavior, $name])) {
				return call_user_func_array([$behavior, $name], $params);
			}
		}

		throw new \ErrorException('Undefined method ' . get_class($this) . "::$name() in class and its behaviors.");
	}

	/**
	 * @brief  Checks if a property is set.
	 * @param  bool
	 */
	public function __isset($name)
	{
		try {
			$this->__get($name);
			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @brief  Unsets a property from the current object or one of its behaviour.
	 *         The goal here is to follow as much as possible the default behavior
	 *         of PHP : Throw an exception only if the targetted property is protected.
	 * @param  string $name The name of the targetted property
	 */
	public function __unset($name)
	{
		$done        = false;
		$isProtected = false;

		if (isset($this->$name)) {
			$class = get_class($this);

			$reflected = new \ReflectionProperty($class, $name);
			if ($reflected->isPublic()) {
				unset($this->$name);
				$done = true;

			} else {
				$isProtected = true;
			}

		}

		foreach ($this->behaviors as $behavior) {
			if (isset($behavior->$name)) {
				unset($behavior->$name);
				$done = true;
			}
		}

		if (!$done && $isProtected) {
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
			throw new \ErrorException("Cannot access protected property {$class}::\${$name}"
				." in {$trace['file']} on line {$trace['line']}");
		}
	}

	/**
	 * @brief Flush the behaviors during cloning.
	 */
	public function __clone()
	{
		$this->behaviors = [];
	}

	/**/
}

