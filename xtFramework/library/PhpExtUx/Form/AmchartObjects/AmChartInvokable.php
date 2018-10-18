<?php

/**
 * Class AmChartInvokable
 * @author radoslav
 *
 */
class AmChartInvokable extends PhpExt_Observable {
	
	/**
	 * Methods to be invoked
	 * @var PhpExt_ObjectCollection
	 */
	protected $_invokableMethods = null;
	
	protected function addMethodCall($method_name, AmChartCallable $object) {
		$object->setMethodName($method_name);
		$this->_invokableMethods->add($object);
		return $this;
	}
	
	public function getMethodsCalls() {
		return $this->getExtConfigProperty('invokableMethods');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","AmchartConfigObject");
	
		$validProps = array(
			'invokableMethods',
		);
		$this->addValidConfigProperties($validProps);
		
		$this->_invokableMethods = new PhpExt_ObjectCollection();
		$this->_invokableMethods->setForceArray(true);
		$this->setExtConfigProperty('invokableMethods', $this->_invokableMethods);
	}
	
}