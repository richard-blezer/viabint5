<?php
  /*
 #########################################################################
 #                       xt:Commerce VEYTON 4.0 Shopsoftware
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # Copyright 2007-2011 xt:Commerce International Ltd. All Rights Reserved.
 # This file may not be redistributed in whole or significant part.
 # Content of this file is Protected By International Copyright Laws.
 #
 # ~~~~~~ xt:Commerce VEYTON 4.0 Shopsoftware IS NOT FREE SOFTWARE ~~~~~~~
 #
 # http://www.xt-commerce.com
 #
 # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 #
 # @version $Id: cleverreach.php 4611 2011-03-30 16:39:15Z mzanier $
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

defined('_VALID_CALL') or die('Direct Access is not allowed.');

$display_output = false;



if(isset($_GET["limit"]) && $_GET["api"] == XT_CLEVERREACH_API_KEY){    
    $cleverreach = new cleverreach();
    
    $cleverreach->prepare_list(XT_CLEVERREACH_API_KEY, XT_CLEVERREACH_LIST_ID);
    
    $cleverreach->exportOrders($_GET["limit"]);
}elseif($_GET["api"] == XT_CLEVERREACH_API_KEY){

    $rs    = $db->Execute("SELECT count(customers_id) as count FROM ".TABLE_CUSTOMERS);
?>

<html>
<head>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<title>CR-Veyton Import</title>

<style>

.progress_border{
    background:#FFF;
    width:600px;
    height:15px;
    border:1px solid #333;
    float:left;
 }
</style>

<script>
var $cr=jQuery.noConflict();

var api = "<?php echo $_GET["api"] ?>";

var total = <?php echo $rs->fields["count"] ?>;
var limit = 0;
var elapsed = 0;
var start = 0;
var end = 0;
$cr(document).ready(function(){
    call_import(limit);
});

function call_import(limit){
    start = new Date().getTime();
    $cr.get("index.php?page=cleverreach&limit="+limit+"&api="+api, function(data){
        eval(data);
    });
}

function update_import(){
    limit = limit + 50;

    //change Stuff
    if(total == 0){
        divide = 50;
    }else{
        divide = total;
    }

    if(limit > total){
        limit = total;
    }
    
    css_percent = Math.round(100 / divide * limit);

    $cr(".progress_bar").css("width", css_percent+"%");
    $cr(".progress").html(css_percent+"%");

    end = new Date().getTime();

    elapsed = end - start;

    $cr("#timer").html(Math.floor(((elapsed / limit * total)/1000)/3600)+":"+Math.floor(((elapsed / limit * total)/1000)/60)+":"+Math.floor(((elapsed / limit * total)/1000)%60));
    
    if(limit < total){
        call_import(limit);
    }
}
</script>
<style>
body { font-size: 14px; font-family:Arial;}
h1{ color:#666; font-size:16px;}
.containergrey{ width:800px;  background-color:#F4F4F4;-moz-border-radius:6px; border-radius:6px; -webkit-border-radius:6px;margin:15px 0px 15px 0px;padding: 10px }
</style>
</head>
<body>
<div align="center">
<img src="plugins/xt_cleverreach/images/logo_admin.gif">
<h1>Import wurde gestartet</h1><div class="containergrey">
    <div style="float:left">Progress:</div>
    <div class="progress_border" style="float:left; margin-left:5px; margin-right:5px; margin-bottom:5px;">
        <div class="progress_bar" style="background-color:#fc7000; float:left; width:0%; height:15px; "></div>
    </div>
    <div class="progress" style="float:left">0%</div>
    <br clear="all">

    Bitte schließen Sie die Seite nicht, bevor der Import 100% erreicht hat.
</div></div>
</body>
</html>

<?php }else{ ?>

<html>
<head>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<title>CleverReach/Veyton Import</title>

<style>
body { font-size: 14px; font-family:Arial;}
h1{ color:#666; font-size:16px;}
.containergrey{ width:700px;  background-color:#F4F4F4;-moz-border-radius:6px; border-radius:6px; -webkit-border-radius:6px;margin:15px 0px 15px 0px;padding: 10px }
</style>
</head>
<body>


<div align="center">
<img src="plugins/xt_cleverreach/images/logo_admin.gif">
<h1>Import von vorhandenen Daten</h1><div class="containergrey">

<form action="index.php" method="get">
<input type="hidden" name="page" value="cleverreach">
Durch Starten des dieses Vorgangs werden alle innerhalb von VEYTON vorhanden Kundendaten in Ihren CleverReach-Account übertragen.<br><br>

Bitte geben Sie Ihren API-Key aus Sicherheitsgründen ein: 
<input type="text" name="api">
<input type="submit" value="Import starten">
</form>
</div></div>
</body>
</html>

<?php } ?>