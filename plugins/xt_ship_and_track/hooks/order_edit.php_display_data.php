<?php

defined('_VALID_CALL') or die('Direct Access is not allowed.');

require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/class.xt_tracking.php';
require_once _SRV_WEBROOT . _SRV_WEB_PLUGINS. 'xt_ship_and_track/classes/class.xt_ship_and_track.php';

/**
 *  Hinzufügen von verschidenen panels zu order_edit.php / order.html
 */


// js panel zum hinzufügen von trackingcodes
$addTrackingPanelJs =  xt_tracking::orderEdit_displayAddTracking($this->oID);

// js/html panel zur darstellung von trackingcodes
$trackingPanelJs =  xt_tracking::orderEdit_displayTrackingsJs($this->oID);

// hermes panel
$addParcelPanelJs =  xt_ship_and_track::orderEdit_displayAddParcel($this->oID);

$js = $addParcelPanelJs . $trackingPanelJs . $addTrackingPanelJs . $js;


// #########################################################################

$shipmentPanel = new PhpExt_TabPanel();
$shipmentPanel->setId('shipmentPanel'.$this->oID)
    ->setActiveTab(0)
    ->setDeferredRender(true);

$trackingPanel = xt_tracking::getTrackingPanel($this->oID);
if ($trackingPanel)
{
    $shipmentPanel->addItem($trackingPanel);
}
$shipmentPanel->addItem(xt_ship_and_track::getAddParcelPanel($this->oID));

$addTrackingPanel = xt_tracking::getAddTrackingPanel($this->oID);
$shipmentPanel->addItem($addTrackingPanel);


$layout = new PhpExt_Panel();
$layout->setLayout(new PhpExt_Layout_BorderLayout())
    ->setId('center'.$this->oID)
    ->setAutoWidth(false)->setHeight(195)
    ->addItem($shipmentPanel, PhpExt_Layout_BorderLayoutData::createCenterRegion())
    ->setRenderTo(PhpExt_Javascript::variable("Ext.get('tabbedShipmentPanel".$this->oID."')"));

$js2 = PhpExt_Ext::OnReady
(
    '$("#memoContainer"+'.$this->oID.').prepend("<div style=\'width:100%\' id=\'tabbedShipmentPanel'.$this->oID.'\'></div>")'
    ,$layout->getJavascript(false, "tabbedShipmentPanel".$this->oID)
);

$js = $js . $js2;

?>


