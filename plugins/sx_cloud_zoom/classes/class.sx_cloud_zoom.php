<?php
defined('_VALID_CALL') or die('Direct Access is not allowed.');
	
class cloud_zoom {
	
	function _getCloudZoom() {
        global $xtMinify;
        $xtMinify->add_resource(_SRV_WEB_PLUGINS . 'sx_cloud_zoom/javascript/cloud-zoom.1.0.3.min.js',119);
	}
}
?>