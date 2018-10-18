<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS . 'xt_orders_invoices/classes/constants.php';
require_once _SRV_WEBROOT . 'plugins/xt_orders_invoices/classes/class.xt_orders_invoices.php';
require_once _SRV_WEBROOT . 'plugins/xt_orders_invoices/classes/class.xt_print_buttons.php';

$invoice = new xt_orders_invoices();

if (!$invoice->isExistByOrderId($this->oID)) {
    $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? '&sec='.$_SESSION['admin_user']['admin_key']: '';
    $js = "
        startNo = (typeof startNo != 'undefined') ? startNo : false;
        comment = (typeof comment != 'undefined') ? comment : '';
        var conn = new Ext.data.Connection();
        if (typeof(Ext.WindowMgr.get('editStartIdRemoteWindow')) != 'undefined') {
            Ext.WindowMgr.get('editStartIdRemoteWindow').destroy();
        }
        conn.request({
            url: 'adminHandler.php?plugin=xt_orders_invoices&load_section=xt_orders_invoices".$add_to_url."&pg=issue',
            method:'GET',
            params: {'id': " . $this->oID . ", 'start_no': startNo, 'comment': comment},
            success: function(responseObject) {
                if (typeof new_window != 'undefined' && new_window) { new_window.destroy() }
                contentTabs.getActiveTab().getUpdater().refresh();
                window.open('adminHandler.php?plugin=xt_orders_invoices&load_section=xt_orders_invoices&pg=getInvoicePdf&type=invoice&".$add_to_url."&id='+responseObject.responseText, '_blank');
            }
        });
    ";
    $extF = new ExtFunctions();
    $window = $extF->_RemoteWindow("TEXT_XT_ORDERS_INVOICES", "TEXT_XT_ORDERS_INVOICES_COMMENT", "adminHandler.php?plugin=xt_orders_invoices&load_section=xt_orders_invoices&pg=getCommentForm", '', array(), 450, 250, '');
    $saveBtn = PhpExt_Button::createTextButton(
        __define('TEXT_SAVE'), new PhpExt_Handler(PhpExt_Javascript::stm("
                            var comment = Ext.ComponentMgr.get('invoiceCommentForm').getForm().findField('invoiceComment').getValue();
                            $js
                            "
        ))
    );
    $saveBtn->setIcon('images/icons/table_save.png')
        ->setIconCssClass("x-btn-text");
    $window->addButton($saveBtn);
    $newWinJs =  $window->getJavascript(false, 'new_window');
    $jsWithComment = "
        if (typeof(Ext.WindowMgr.get('editStartIdRemoteWindow')) != 'undefined') {
            Ext.WindowMgr.get('editStartIdRemoteWindow').destroy();
        }
        $newWinJs;
        new_window.show();
    ";



    global $db;
    // shop ermitteln
    $shopId = $this->order_data['order_data']['shop_id'];
    // hat der shop separaten kreis ?
    $separateAssignment = $invoice->isSeparateAssignmentForOrder($this->oID);
     // wurde schon nach initialem wert bei separation gefragt ? wenn 0 dann nicht
    $separationProcessed = $invoice->isSeparateAssignmentStartedForShop($shopId) ;
      // wurde schon nach initialem wert für globalen counter gefragt ? wenn 0 dann nicht
    $globalStartNumber = $db->GetOne("SELECT `config_value` FROM " . TABLE_CONFIGURATION. " WHERE `config_key`= '_INVOICE_NUMBER_GLOBAL_LAST_USED'" );
    if
    (
        ($separateAssignment && !$separationProcessed)
        ||
        (!$separateAssignment && $globalStartNumber=='0')
    )
    {
        $showStartNumberWindow = true;
    }
    else{
        $showStartNumberWindow = false;
    }

    if ($showStartNumberWindow) {
        $extF = new ExtFunctions();
        $extF->setCode('editStartId');
        $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? '&sec='.$_SESSION['admin_user']['admin_key']: '';
        $window = $extF->_RemoteWindow("TEXT_XT_ORDERS_INVOICES", "XT_ORDERS_INVOICES_TEXT_START_ID", "adminHandler.php?plugin=xt_orders_invoices&load_section=xt_orders_invoices&pg=getStartIdForm&shop_id=$shopId", '', array(), 300, 200, '');
        $saveBtn = PhpExt_Button::createTextButton(
            __define('TEXT_SAVE'), new PhpExt_Handler(PhpExt_Javascript::stm("
                            if (Ext.ComponentMgr.get('invoice_start_id').isValid()) {
                                var conn = new Ext.data.Connection();
                                conn.request({
                                    url: 'adminHandler.php?plugin=xt_orders_invoices&load_section=xt_orders_invoices".$add_to_url."&pg=saveStartIdForm&withComment='+withComment,
                                    method: 'POST',
                                    params: Ext.ComponentMgr.get('invoiceStartIdForm').getForm().getValues(),
                                    error: function(responseObject) {
                                        Ext.Msg.alert('" . __define('TEXT_ALERT') . "', '" . __define('TEXT_NO_SUCCESS') . "');
                                    },
                                    waitMsg: 'SAVING...',
                                    success: function(responseObject) {
                                        var json = Ext.util.JSON.decode(responseObject.responseText)
                                        var startNo = json.startNo;
                                        if (new_window) { new_window.destroy() }
                                        console.log(json);
                                        if (json.withComment==1)
                                        {
                                            " . $jsWithComment . "
                                        }
                                        else
                                        {
                                        " . $js . "
                                    }
                                    }
                                });
                            } else {
                                Ext.ComponentMgr.get('invoice_payment').focus();
                            }"
            ))
        );
        $saveBtn->setIcon('images/icons/table_save.png')
            ->setIconCssClass("x-btn-text");
        $window->addButton($saveBtn);

        $newWinJs =  $window->getJavascript(false, 'new_window');
        $js = $jsWithComment = "
        if (typeof(Ext.WindowMgr.get('editStartIdRemoteWindow')) != 'undefined') {
            Ext.WindowMgr.get('editStartIdRemoteWindow').destroy();
        }
        $newWinJs;
        new_window.show();";

    }

    $invoiceBtn = PhpExt_Button::createTextButton(
        __define("XT_ORDERS_INVOICES_BUTTON_ISSUE"), new PhpExt_Handler(PhpExt_Javascript::stm('var withComment=0;'.$js))
    );

    $invoiceBtn->setType(PhpExt_Button::BUTTON_TYPE_BUTTON);
    $invoiceBtn->setName('issueInvoice');
    $Panel->addButton($invoiceBtn);

    $invoiceWithCommentBtn = PhpExt_Button::createTextButton(
        __define("XT_ORDERS_INVOICES_BUTTON_ISSUE_WITH_COMMENT"), new PhpExt_Handler(PhpExt_Javascript::stm('var withComment=1;'.$jsWithComment))
    );
    $invoiceWithCommentBtn->setType(PhpExt_Button::BUTTON_TYPE_BUTTON);
    $invoiceBtn->setName('issueInvoiceWithComment');
    $Panel->addButton($invoiceWithCommentBtn);
}
else {
    // Store
    $reader = new PhpExt_Data_ArrayReader();
    $reader->setId(COL_INVOICE_ID);

    $reader->addField(new PhpExt_Data_FieldConfigObject('invoice_status', 'invoice_status', PhpExt_Data_FieldConfigObject::TYPE_BOOLEAN));
    $reader->addField(new PhpExt_Data_FieldConfigObject('document', 'document'));
    $reader->addField(new PhpExt_Data_FieldConfigObject('id', COL_INVOICE_ID, 'int'));
    $reader->addField(new PhpExt_Data_FieldConfigObject('issued_date', 'invoice_issued_date'));
    $reader->addField(new PhpExt_Data_FieldConfigObject('invoice_due_date', 'invoice_due_date'));
    $reader->addField(new PhpExt_Data_FieldConfigObject('amount', 'invoice_total_formatted2'));
    $reader->addField(new PhpExt_Data_FieldConfigObject('invoice_paid', 'invoice_paid', PhpExt_Data_FieldConfigObject::TYPE_BOOLEAN));
    $reader->addField(new PhpExt_Data_FieldConfigObject('invoice_sent', 'invoice_sent', PhpExt_Data_FieldConfigObject::TYPE_BOOLEAN));
    $reader->addField(new PhpExt_Data_FieldConfigObject('invoice_sent_date', 'invoice_sent_date'));
    $reader->addField(new PhpExt_Data_FieldConfigObject('payment', 'invoice_payment'));

    $store = new PhpExt_Data_Store();
    $store->setReader($reader)->setData(PhpExt_Javascript::variable("invoicesData"));

    // ColumnModel
    $colModel = new PhpExt_Grid_ColumnModel();
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_INVOICE_STATUS, 'invoice_status', null, 50, null, PhpExt_Javascript::variable('status_icon'), true, false));
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_DOCUMENT));
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_INVOICE_ID));
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_INVOICE_ISSUED_DATE));
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_INVOICE_DUE_DATE, 'invoice_due_date'));
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_INVOICE_TOTAL_FORMATTED));
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_INVOICE_PAID, 'invoice_paid', null, 70, null, PhpExt_Javascript::variable('status_icon'), true, false));
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_INVOICE_SENT, 'invoice_sent', null, 70, null, PhpExt_Javascript::variable('status_icon'), true, false));
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_INVOICE_SENT_DATE));
    $colModel->addColumn(PhpExt_Grid_ColumnConfigObject::createColumn(TEXT_INVOICE_PAYMENT));

    // Actions
    $rowAction = new PhpExtUx_Grid_RowAction(__define("TEXT_SELECTED_ITEMS"));
    $rowAction->__set("header", 'Actions');

    $params = $invoice->_getParams();
    $settings = $params['rowActions'];

    $actions = new PhpExtUx_Grid_RowActionCollection();
    foreach ($settings as $setting) {
        $actions->add(PhpExt_Grid_RowActionObject::createAction($setting['iconCls'], '', $setting['qtipIndex'], $setting['tooltip']));
    }

    $rowAction->__set("actions", $actions);
    $colModel->addColumn($rowAction);

    $actJs = '';
    $settings = $params['rowActionsFunctions'];
    foreach ($settings as $action => $fkt_content) {
        $actJs .= "if (action == '" . $action . "') { " . $fkt_content . " }";
    }

    $rowaction = PhpExt_Javascript::variable("rowAction.on('action', function(grid, record, action, row, col) {" . $actJs . "});");

    // Grid
    $grid = new PhpExt_Grid_GridPanel();
    $grid->setStore($store)
        ->setId('xt_orders_invoicesgridForm')
        ->setColumnModel($colModel)
    //->setAutoExpandColumn("actions")
    //->setAutoHeight(false)
    //->setAutoWidth(true)
        ->setBorder(false)
        ->setLayout(new PhpExt_Layout_FitLayout())
        ->setRenderTo(PhpExt_Javascript::variable("Ext.get('memoContainer" . $this->oID . "')"));
    $grid->getPlugins()->add($rowAction);

    // Render
    $renderer = PhpExt_Javascript::functionDef(
        'status_icon', "if (data == 1) { return '<img src=\"images/icons/bullet_green.png\" /><img src=\"images/icons/bullet_white.png\" />';" .
        "} else {" .
        "return '<img src=\"images/icons/bullet_white.png\" /><img src=\"images/icons/bullet_red.png\" />';	}", array('data')
    );

    $js = PhpExt_Ext::onReady(
        PhpExt_Javascript::stm("var invoicesData = " . PhpExt_Javascript::jsonEncode($invoice->getOrderData($this->oID))), $rowAction->getJavascript(false, "rowAction"), $grid->getJavascript(false, "invoicesGrid"), $rowaction, $renderer
    );

    echo '<script type="text/javascript">' . $js . '</script>';
    echo '<script type="text/javascript" src="../plugins/xt_orders_invoices/js/xt_orders_invoices.js"></script>';
    echo "<br />";
}

// print butoons

$pb = new xt_print_buttons('admin');
$pb->url_data = array('get_data' => true);
$pbData = $pb->_get();

foreach($pbData->data as $button) {
	$type = $button[COL_PRINT_BUTTONS_TEMPALTE_TYPE];
    $cid = $this->order_data['order_customer']['customers_id'];
    $add_to_url = (isset($_SESSION['admin_user']['admin_key']))? '&sec='.$_SESSION['admin_user']['admin_key']: '';
    $js = "
        window.open('adminHandler.php?plugin=xt_orders_invoices&load_section=xt_orders_invoices&pg=printButtonPDF&cid=$cid&oid=$this->oID&type=$type', '_blank');
    ";

    global $language;

    $poBtn = PhpExt_Button::createTextButton(
        $button[COL_PRINT_BUTTONS_CAPTION.'_'.$language->content_language], new PhpExt_Handler(PhpExt_Javascript::stm($js))
    );

    $poBtn->setType(PhpExt_Button::BUTTON_TYPE_BUTTON);
    $poBtn->setName($button[COL_TEMPLATE_TYPE]);
    $Panel->addButton($poBtn);
}
?>