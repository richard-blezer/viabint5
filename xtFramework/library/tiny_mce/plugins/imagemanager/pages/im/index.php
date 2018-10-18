<?php
include '../../../../../../admin/main.php';

if (!$xtc_acl->isLoggedIn()) {
    die('login required');
}
if ($_GET['seckey']!=_SYSTEM_SECURITY_KEY){
    die('wrong key');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=7" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{#common.title}</title>
    <!--[if IE]><script type="text/javascript" src="js/utils/fixpng.js"></script><![endif]-->
    <link rel="stylesheet" type="text/css" href="../../css/index.php?type=im&theme=im&package=core_css" />

</head>
<body>
<div id="center">
    <div id="topnav">
        <div id="topwrap">
            <div id="toolbar" class="toolbar caption">
                <div class="navigation filter">
                    <div class="desc">{#common.filter}</div>
                    <div><input type="text" id="filter" name="filter" value="" /></div>
                </div>

                <div class="navigation">
                    <div class="desc">{#common.directory}</div>
                    <div id="curpath" class="path">&nbsp;</div>
                </div>

                <ul id="tools" class="icons">
                    <li id="createdir" class="icon"><a href="#action" title="{#common.createdir}"><img src="img/icons/newfolder.png" alt="" height="16" width="16" /><span class="caption">{#common.createdir}</span></a></li>
                    <li id="upload" class="icon"><a href="#action" title="{#common.upload}"><img src="img/icons/add.png" alt="" height="16" width="16" /><span class="caption">{#common.upload}</span></a></li>
                    <li id="refresh" class="icon"><a href="#action" title="{#common.refresh}"><img src="img/icons/reload.png" alt="" height="16" width="16" /><span class="caption">{#common.refresh}</span></a></li>
                    <li id="filemanager" class="icon"><a href="#action" title="{#common.filemanager}"><img src="img/icons/filemanager.png" alt="" height="16" width="16" /><span class="caption">{#common.filemanager}</span></a></li>
                </ul>
            </div>

            <br style="clear:both;" /><br />
        </div>
    </div>

    <div id="listcontainer">
        <div id="folders">
            <div>
                <h2>{#panel.categories}</h2>

                <ul class="categories" id="category_list"></ul>
                <ul id="special_list" class="special"></ul>

                <h3>{#panel.folders}</h3>
                <ul id="folder_list" class="folders">
                    <!-- This will be filled with folders -->
                    <li class="progress">{#common.loading}</li>
                </ul>
            </div>
        </div>

        <div id="viewcontainer">
            <div id="pages" class="pagenav">
                <a href="#prev" class="prev"></a>
                <div>{#common.page}</div>
                <input type="text" id="curpage" name="curpage" value="1" />
                <div>{#common.of}&nbsp;</div>
                <div id="numpages">15</div>
                <a href="#next" class="next"></a>
            </div>

            <div id="progress" class="pagenav">{#common.loading}</div>

            <div class="viewmode">
                <form action="#action" name="listOptions">
                    {#view.mode}
                    <select name="selectView" id="selectView">
                        <option value="text">{#view.textlist}</option>
                        <option value="thumbs" selected="selected">{#view.thumbnail}</option>
                    </select>, <select name="setPages" id="setPages">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select> {#view.images_per_page}
                </form>
            </div>

            <br style="clear:both" />

            <div id="filelist">
                <!-- This will be filled by the thumbs_template -->
            </div>
        </div>
    </div>

    <script id="custom_dir_template" type="text/moxiecode-template">
        <![CDATA[
        <li><a href="#action" title="{path}" class="{type}">{title}</a></li>
        ]]>
    </script>

    <script id="folders_template" type="text/moxiecode-template">
        <![CDATA[
        <li class="{type}"><a href="#action" title="{path}">{name}</a></li>
        ]]>
    </script>

    <script id="text_template" type="text/moxiecode-template">
        <![CDATA[
        <div id="file_{index}" class="listview file {type}">
            <div class="wrap"></div>
            <div class="details">
                <div class="wrap2">
                    <div class="name"><a href="#action" title="{name}" rel="file">{name}</a></div>
                    <div class="act"><a href="#action" rel="menu"><img src="img/act.gif" width="16" height="16" alt="" /></a></div>
                </div>
            </div>
        </div>
        ]]>
    </script>

    <script id="thumb_template" type="text/moxiecode-template">
        <![CDATA[
        <div id="file_{index}" class="{type} file thumbnail" style="width:{thumb_width};">
            <div class="wrap">
                <a href="#action" title="{name}" rel="file" class="pic" style="height:{thumb_height};">
                    <div class="mid"><div class="mid2"><img src="img/loading_bg.gif" alt="{thumburl}" style="width:{width};height:{height}" class="thumbnailimage" /></div></div></a>
            </div>
            <div class="details">
                <div class="wrap2">
                    <div class="name" style="width:{text_width};" title="{name}">{name}</div>
                    <div class="act"><a href="#action" rel="menu"><img src="img/act.gif" width="16" height="16" alt="" /></a></div>
                </div>
            </div>
        </div>
        ]]>
    </script>
</div>

<script type="text/javascript" src="../../language/index.php?type=im"></script>
<script type="text/javascript" src="../../js/index.php?type=im&theme=im&package=core"></script>
<script type="text/javascript" src="../../js/index.php?type=im&theme=im&package=imagemanager"></script>


</body>
</html>
