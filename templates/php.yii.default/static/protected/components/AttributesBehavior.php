<?php

class AttributesBehavior extends CBehavior
{
	public $attributes = array();
	protected $behaviors = null;
	
	protected function createBehaviors()
	{
		$this->behaviors = new CList();
		foreach ($this->attributes as $options) {
			$names = array_shift($options);
			$attribs = preg_split('/\s*,\s*/', $names, -1, PREG_SPLIT_NO_EMPTY);
			if (empty($attribs)) {
				throw new CException('Attributes have not set');
			}
			$behaviorName = array_shift($options);
			if (empty($behaviorName)) {
				throw new CException('Behavior name is missing');
			}
			$class = ucfirst($behaviorName) . 'AttributeBehavior';
			$behavior = new $class($attribs, $options);
			if (!($behavior instanceof AttributeBehavior)) {
				throw new CException('Invalid behavior: ' . $class);
			}
			$this->behaviors->add($behavior);
		}
	}
	
	public function getNiceValue()
	{
		if (null === $this->behaviors) {
			$this->createBehaviors();
		}
		
		$args = func_get_args();
		$attribute = array_shift($args);
		
		$value = $this->owner->$attribute;
		
		foreach ($this->behaviors as $behavior) {
			if ($behavior->hasAttribute($attribute)) {
				$value = $behavior->getNiceValue($value, $attribute, $args);
			}
		}
		
		return $value;
	}
}
