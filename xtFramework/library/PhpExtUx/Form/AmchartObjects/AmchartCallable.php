<?php

/**
 * Class AmChartCallable
 * @author radoslav
 *
 */
class AmChartCallable extends PhpExt_Object {
	
	/**
	 * Params that should be passed to the method
	 * @var PhpExt_ObjectCollection
	 */
	protected $_methodParams = null;
	
	public function setMethodName($value) {
		$this->setExtConfigProperty('methodName', $value);
		return $this;
	}
	
	public function getMethodName() {
		return $this->getExtConfigProperty('methodName');
	}
	
	public function addMethodParam($value) {
		if (AmChartConfigObject::isConfigObject($value)) {
			$executable = new AmchartExecutableObject();
			$executable->setClassName($value->getClassConfigName())->setConfig($value);
			$this->_methodParams->add($executable);
			return $this;
		}
		$this->_methodParams->add($value);
		return $this;
	}
	
	public function getMethodParams() {
		return $this->getExtConfigProperty('methodParams');
	}
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartConfigObject","AmchartConfigObject");
	
		$validProps = array(
			'methodName',
			'methodParams'
		);
		$this->addValidConfigProperties($validProps);
	
		$this->_methodParams = new PhpExt_ObjectCollection();
		$this->_methodParams->setForceArray(true);
		$this->setExtConfigProperty('methodParams', $this->_methodParams);
	}
	
	public static function createCallable() {
		$args = func_get_args();
		$callable = new AmChartCallable();
		
		foreach ($args as $arg) {
			$callable->addMethodParam($arg);
		}
		
		return $callable;
	}
}
