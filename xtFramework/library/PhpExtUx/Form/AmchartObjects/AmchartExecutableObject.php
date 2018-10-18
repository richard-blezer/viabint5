<?php


class AmchartExecutableObject extends PhpExt_Object {
	
	public function setClassName($value) {
		$this->setExtConfigProperty('className', $value);
		return $this;
	}
	
	public function getClassName() {
		return $this->getExtConfigProperty('className');
	}
	
	public function setConfig(PhpExt_Object $value) {
		$this->setExtConfigProperty('config', $value);
		return $this;
	}
	
	public function getConfig() {
		return $this->getExtConfigProperty('config');
	} 
	
	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.AmchartExecutableObject","AmchartExecutableObject");
	
		$validProps = array(
				'className',
				'config',
		);
		$this->addValidConfigProperties($validProps);
	}
	
}