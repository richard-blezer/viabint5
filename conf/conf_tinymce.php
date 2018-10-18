<?php
 /*
 #########################################################################
 #                       xt:Commerce  4.1 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce  4.1 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id$
 # @copyright xt:Commerce International Ltd., www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # xt:Commerce International Ltd., Kafkasou 9, Aglantzia, CY-2112 Nicosia
 #
 # office@xt-commerce.com
 #
 #########################################################################
 */

if (_VALID_CALL!='true') die(' Access not Allowed ');

($plugin_code = $xtPlugin->PluginCode('tiny_mce_top')) ? eval($plugin_code) : false;
?>

<script type="text/javascript">

var MCE_CLEAN_REX = /<([^>]*?)(mce_tsrc|mce_src|mce_style|mce_href)\="(.*?)"(.*?)>/ig;
var MCE_CLEAN_REX2 = /<br mce_bogus\="1">/ig;
function cleanMCEOutput(x) {
    x = x.replace (MCE_CLEAN_REX, '<$1$4>');
    return x.replace (MCE_CLEAN_REX2, '');
}

var tinyMCESettings = {
mode : "specific_textareas",
language : "<?php echo $language->code; ?>",
seckey : "<?php echo _SYSTEM_SECURITY_KEY; ?>",
theme : "advanced",
editor_selector : "TinyMce",
document_base_url : "<?php echo _SYSTEM_BASE_HTTP._SRV_WEB; ?>",
plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager",
theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,insertimage,cleanup,help,code,|,preview,|,forecolor,backcolor",
theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",
theme_advanced_toolbar_location : "top",
theme_advanced_toolbar_align : "left",
theme_advanced_path_location : "bottom",
theme_advanced_resizing : true,
extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
template_external_list_url : "example_template_list.js",

apply_source_formatting : true,
fix_list_elements : true,
fix_table_elements : true,
fix_nesting : true,
verify_css_classes : true,
verify_html : true,

setup : function(ed) {
     ed.onChange.add(function(ed, e) {
         var elemetID = ed.id;
         var d_el = Ext.getDom(elemetID);
				 // console.log (e);
         d_el.value = cleanMCEOutput (e.content);
     });
  }
};

<?php
		($plugin_code = $xtPlugin->PluginCode('tiny_mce_js')) ? eval($plugin_code) : false;
?>

tinyMCE.init(tinyMCESettings);

</script>
