<?php

$to_filter = array("product", "order", "customer", "xt_reviews", "acl_user", "redirect_deleted", "redirect_404");

($plugin_code = $xtPlugin->PluginCode('show_panel:to_filter')) ? eval($plugin_code) : false; 

$table = get_class($this->class);
if (in_array($table, $to_filter)) {
    
    $panelId = 'ffp'.$table;
    if(isset($_REQUEST['catID'])) $panelId  .= $_REQUEST['catID'];
    if(isset($_REQUEST['c_oID'])) $panelId  .= $_REQUEST['c_oID'];
	if(isset($_GET['parentNode'])) {
		$catst = explode("catst_",$_GET['parentNode']);
		$store_cat_id = '';
		if ($catst[1]) 
			$store_cat_id = 'catst_'.$catst[1];
		$panelId  .=$store_cat_id;
	}
	
    $east = new PhpExt_Panel();
    $east->setTitle("Filter")
            ->setCollapsible(true)
            ->setID($panelId)
            ->setWidth('290px')
            ->setLayout(new PhpExt_Layout_FormLayout());

    $formId = 'ff' . $table;
    if(isset($_REQUEST['catID'])) $formId .= $_REQUEST['catID'];
    //if(isset($_REQUEST['parentNode'])) $formId .= $_REQUEST['parentNode'];
    
    $simple = new PhpExt_Form_FormPanel();
    $simple->setUrl(_SRV_WEB."adminHandler.php?filter_form=yes&pg=overview&parentNode=".$_REQUEST['parentNode'] . "&get_data=true&load_section=" . $table . "&PHPSESSID=" . session_id())
            ->setMethod('post')
            ->setID($formId)
            ->setBodyStyle("padding:5px 5px 5px 5px; background-color:#fff; color:#000")
            ->setDefaultType("textfield");


    $filterClass = ucfirst(get_class($this->class)) . "Filter";
    require_once(_SRV_WEBROOT . "xtFramework/admin/filter/class.formFilter.php");

    if (file_exists(_SRV_WEBROOT . "xtFramework/admin/filter/class." . $table . "Filter.php")) {
        require_once(_SRV_WEBROOT . "xtFramework/admin/filter/class." . $table . "Filter.php");
        if(class_exists($filterClass)){
            $a = new $filterClass();
            $formFields = $a->formFields();
        }
    }
    
    ($plugin_code = $xtPlugin->PluginCode('show_panel:filter_class')) ? eval($plugin_code) : false;
    
    if (is_array($formFields))
        foreach ($formFields as $field) {
            $simple->addItem($field);
        }

    $f1 = PhpExt_Form_Hidden::createHidden("filter", get_class($this->class));
    $simple->addItem($f1);


    $submit = PhpExt_Button::createTextButton(TEXT_FIND);
    $submit->setType(BUTTON_TYPE_SUBMIT);
	
	$reset = PhpExt_Button::createTextButton(TEXT_RESET);
	$reset->setType(BUTTON_TYPE_BUTTON);
    
      if(isset($_REQUEST['catID']))   $grid_id = $this->code.(int)$_REQUEST['catID'];
     else $grid_id = $this->code;

    $submit->attachListener("click", new PhpExt_Listener(PhpExt_Javascript::functionDef(
                            null, 'var ff = Ext.getCmp(\''. $formId  . '\'); ff.getForm().submit(
                          {
	                      waitMsg: \' Uploading data...\',
                              failure: function(ff, action) {
						    Ext.MessageBox.alert(\'Error Message\', action.result.data);  
						},
                            success: function(ff, action){
								if($(\'#filter_items_per_page_'.$filterClass.'_'.$grid_id.$store_cat_id.'\').length > 0) //dynamic pagination exists
								{
									Ext.getCmp("' . $grid_id. "gridForm" . '").getBottomToolbar().pageSize = parseInt($(\'#filter_items_per_page_'.$filterClass.'_'.$grid_id.$store_cat_id.'\').val(), 10);
									Ext.getCmp("' . $grid_id. "gridForm" . '").getStore().baseParams.limit = parseInt($(\'#filter_items_per_page_'.$filterClass.'_'.$grid_id.$store_cat_id.'\').val(), 10);
								}

								var f = Ext.getCmp("' . $grid_id. "gridForm" . '").store; f.reload({params:{start:0}});

	                    }}); '
            )));
	
	 $reset->attachListener("click", new PhpExt_Listener(PhpExt_Javascript::functionDef(
                            null, 'var ff = Ext.getCmp(\''. $formId  . '\'); ff.getForm().reset();ff.getForm().submit(
                          {
	                      waitMsg: \' Uploading data...\',
                              failure: function(ff, action) {
						    Ext.MessageBox.alert(\'Error Message\', action.result.data);  
						},
                              success: function(ff, action){
									if($(\'#filter_items_per_page_'.$filterClass.'_'.$grid_id.$store_cat_id.'\').length > 0) //dynamic pagination exists
									{
										Ext.getCmp("' . $grid_id. "gridForm" . '").getBottomToolbar().pageSize = parseInt($(\'#filter_items_per_page_'.$filterClass.'_'.$grid_id.$store_cat_id.'\').val(), 10);
										Ext.getCmp("' . $grid_id. "gridForm" . '").getStore().baseParams.limit = parseInt($(\'#filter_items_per_page_'.$filterClass.'_'.$grid_id.$store_cat_id.'\').val(), 10);
									}								
									var f = Ext.getCmp("' . $grid_id. "gridForm" . '").store; f.reload({params:{start:0}});
	                    }}); '
            )));


    $simple->addButton($submit);
	$simple->addButton($reset);
    //$simple->addButton(PhpExt_Button::createTextButton("Cancel"));
    $east->addItem($simple);

    $wrapperPanel->addItem($east, PhpExt_Layout_BorderLayoutData::createEastRegion()
                
                    ->setSplit(true)
                    ->setFloatable(true) //???
                    ->setMinSize(280)
                    ->setMaxSize(400)
                    ->setMargins("0 5 5 0"));
}
                            