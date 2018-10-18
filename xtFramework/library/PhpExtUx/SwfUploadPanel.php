<?php

/**
 * Makes a Panel to provide the ability to upload multiple files using the SwfUpload flash script.
 *
 * @author Michael Giddens (Ext.ux.SwfUploadPanel2.js), Matthias Benkwitz (PhpExtUx_SwfUploadPanel)
 * @website http://www.silverbiology.com, http://www.bui-hinsche.de
 * @created 2008-03-06, 2008-05-06
 * @version 0.4, 0.1
 *
 * @known_issues
 *		- Progress bar used hardcoded width. Not sure how to make 100% in bbar
 *		- Panel requires width / height to be set.  Not sure why it will not fit
 *		- when panel is nested sometimes the column model is not always shown to fit until a file is added. Render order issue.
 *
 * ExtJS Extension
 *
 *
 *
LastModified: March 6th 2008

The SwfUploadPanel widget is a control, where the user can pick mulitple files and upload them in a queue style fasion.


Current Version: 0.5
Uses SwfUpload v2.0.2


Released for ExtJS 2.x.

Demo: See Below

Usage
    * Download ExtJS 2.x library
    * Download Ext.ux.SwfUploadPanel library
    * Unpack ExtJS library to a folder
    * Unpack Ext.ux.SwfUploadPanel.js.zip to s plugin folder for example and follow example!
    * I will try and document more when I have time.


View Forum Posts

    * http://extjs.com/forum/showthread.php?t=19082


Changelog
  * Ver.: 0.4 (beta) Migrated to SwfUpload 2.0.2
  * Ver.: 0.3 (beta) Fixed the progress bar to be dynamic 100
        - Fixed the postParam to work correctly now just past the (name, value)
        - added a few events so you can bind some listeners ie.  swfUploadLoaded, fileUploadComplete, queueUploadComplete
  * Ver.: 0.2 (beta) Added Stop Upload, Remove Files from Queue
  * Ver.: 0.1 (beta) Basic MultiFile Upload

FAQ:
	Q: I set everything up and the file select dialog does not open when I click Add Files?
	A: Double check to make sure the Flash swf path is correct. (In FireBug you will see: "Could not find Flash element" when the path is not right.)

	Q: The Dialog opens and the files show up but when I click upload it hangs up and nothing happens.
	A: The upload_url needs to be correct.  From my testing it only works with absolute paths. So make sure you have the complete url.

	Q: SWFUpload is a Flash object and uses a different session from by browser window. How do I force the same.
	A: Look at the code for the first example and see how the session is sent from the cookie session. Thanks MD!
		(Note: This may not be the safest approach from session hijacking but the only solution I have at the moment.)

PHP code to swap out with the browser session:

if ( (!empty($_REQUEST["PHPSESSID"]) && !empty($_REQUEST["PHPSESSIDX"]))
&& $_REQUEST["PHPSESSID"] != $_REQUEST["PHPSESSIDX"] ) {

$_REQUEST["PHPSESSID"] = $_REQUEST["PHPSESSIDX"];
unset($_REQUEST["PHPSESSIDX"]);
$_COOKIE["PHPSESSID"] = $_REQUEST["PHPSESSID"];
}

session_start();

 *************************************************************
 * example js:

var dlg = new Ext.ux.SwfUploadPanel({
		  title: 'Dialog Sample'
		, width: 500
		, height: 300
		, border: false

		// Uploader Params
		, upload_url: 'http://www.silverbiology.com/ext/plugins/SwfUploadPanel/upload_example.php'
		, post_params: { id: 123}
		, file_types: '*.gif;*.html'
		, file_types_description: 'Image Files'
        , debug: true
		, flash_url: "../../ext_plugin/Ext.ux.SwfUploadPanel.js/swfupload_f9.swf"
		, single_select: true // Select only one file from the FileDialog

		// Custom Params
		, single_file_select: false // Set to true if you only want to select one file from the FileDialog.
		, confirm_delete: false // This will prompt for removing files from queue.
		, remove_completed: true // Remove file from grid after uploaded.
	}); // End

 *************************************************************
 * example PhpExt:

$upload_panel= new PhpExtUx_SwfUploadPanel();
                                       $upload_panel->setFlashUrl("http://localhost/XXX/swfupload_f9.swf")
                                                ->setUploadUrl('http://localhost/YYY/upload.php')
                                                ->setDebug(true)
                                                ->setTitle("uploadpanel")
                                                ->setAutoHeight(true)
                                                ->setAutoWidth(true)
                                                ->setConfirmDelete(true)
                                                ->setRemoveCompleted(true)
                                                ->setFileTypes("*.gif;*.html");

 *
 *
 *
*/

/**
 * @see PhpExt_Ext
 */
include_once 'PhpExt/Ext.php';
/**
 * @see PhpExt_Panel
 */
include_once 'PhpExt/Panel.php';

/**
 * @package PhpExtUx_SwfUploadPanel
 *  */
class PhpExtUx_SwfUploadPanel extends PhpExt_Panel
{
    // post_params
    /**
     */
    public function setPostParams($value) {
    	$this->setExtConfigProperty("post_params", $value);
    	return $this;
    }
    /**
     */
    public function getPostParams() {
    	return $this->getExtConfigProperty("post_params");
    }

    // flash_url
    /**
     */
    public function setFlashUrl($value) {
    	$this->setExtConfigProperty("flash_url", $value);
    	return $this;
    }
    /**
     */
    public function getFlashUrl() {
    	return $this->getExtConfigProperty("flash_url");
    }
    // single_select
    /**
     */
    public function setSingleSelect($value) {
    	$this->setExtConfigProperty("single_select", $value);
    	return $this;
    }
    /**
     */
    public function getSingleSelect() {
    	return $this->getExtConfigProperty("single_select");
    }
    // single_file_select
    /**
     */
    public function setSingleFileSelect($value) {
    	$this->setExtConfigProperty("single_file_select", $value);
    	return $this;
    }
    /**
     */
    public function getSingleFileSelect() {
    	return $this->getExtConfigProperty("single_file_select");
    }
    // confirm_delete
    /**
     */
    public function setConfirmDelete($value) {
    	$this->setExtConfigProperty("confirm_delete", $value);
    	return $this;
    }
    /**
     */
    public function getConfirmDelete() {
    	return $this->getExtConfigProperty("confirm_delete");
    }
    // remove_completed
    /**
     */
    public function setRemoveCompleted($value) {
    	$this->setExtConfigProperty("remove_completed", $value);
    	return $this;
    }
    /**
     */
    public function getRemoveCompleted() {
    	return $this->getExtConfigProperty("remove_completed");
    }
    // file_types
    /**
     */
    public function setFileTypes($value) {
    	$this->setExtConfigProperty("file_types", $value);
    	return $this;
    }
    /**
     */
    public function getFileFypes() {
    	return $this->getExtConfigProperty("file_types");
    }
    // upload_url
    /**
     */
    public function setUploadUrl($value) {
    	$this->setExtConfigProperty("upload_url", $value);
    	return $this;
    }
    /**
     */
    public function getUploadUrl() {
    	return $this->getExtConfigProperty("upload_url");
    }

    // file_types_description
    /**
     */
    public function setFileTypesDescription($value) {
    	$this->setExtConfigProperty("file_types_description", $value);
    	return $this;
    }
    /**
     */
    public function getFileTypesDescription() {
    	return $this->getExtConfigProperty("file_types_description");
    }

    // debug
    /**
     */
    public function setDebug($value) {
    	$this->setExtConfigProperty("debug", $value);
    	return $this;
    }
    /**
     */
    public function getDebug() {
    	return $this->getExtConfigProperty("debug");
    }

    // title
	/**
	 *
	 * @param string $value
	 * @return
	 */
	public function setTitle($value) {
		return parent::setTitle($value);
	}
	/**
	 *
	 * @return string
	 */
	public function getTitle() {
		return parent::getTitle();
	}

    // width
	/**
	 *
	 * @param string $value
	 * @return
	 */
	public function setWidth($value) {
		return parent::setWidth($value);
	}
	/**
	 *
	 * @return string
	 */
	public function getWidth() {
		return parent::getWidth();
	}

    // height
	/**
	 *
	 * @param string $value
	 * @return
	 */
	public function setHeight($value) {
		return parent::setHeight($value);
	}
	/**
	 *
	 * @return string
	 */
	public function getHeight() {
		return parent::getHeight();
	}

    // border
	/**
	 *
	 * @param string $value
	 * @return
	 */
	public function setBorder($value) {
		return parent::setBorder($value);
	}
	/**
	 *
	 * @return string
	 */
	public function getBorder() {
		return parent::getBorder();
	}

	public function __construct() {
		parent::__construct();
		$this->setExtClassInfo("Ext.ux.SwfUploadPanel","uploader");

		$validProps = array(
			"height",
			"width",
		    "post_params",
		    "flash_url",
		    "single_select",
		    "single_file_select",
		    "confirm_delete",
		    "remove_completed",
		    "file_types",
		    "upload_url",
		    "file_types_description",
		    "debug"
		);
		$this->addValidConfigProperties($validProps);
	}

	public function getJavascript($lazy = false, $varName = null) {
		return parent::getJavascript(false, $varName);
	}



}






?>