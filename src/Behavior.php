<?php
namespace JClaveau\PhpBehaviorableTrait;

class Behavior
{
	public $owner = null;

	public function attach($owner)
	{
		$this->owner = $owner;
	}

	public function detach()
	{
		$this->owner = null;
	}
}
