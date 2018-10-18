/* JQuery 模式对话框插件
 * writer: FanJianHan (henryfan@msn.com)
 * License: GPL (GPL-LICENSE.txt) licenses.
 */
 //是否已初始化过对话框
var MessageOninit = false;
//记录body滚动条的x,y偏移量;显示内容的元素对象，显示内容元素对象的父对象
var MessageBox_scrolltop,MessageBox_scrollleft,Messagebox_AC,MessageBox_PC;
//对话框对象，对话框宽度，对话框高度
var MessageBox_win,MessageBox_width,MessageBox_height;
//对话框是否处于移动状态
var MessageBox_Moving = false;

//显示模式提示框
function ShowMessageBox(option)
{
    var container,iframe,enabled,enabledframe;
    var height=400;
    var width =400;
    MessageBox_scrolltop =0;
    MessageBox_scrollleft =0;
    if(!MessageOninit)
    {
       CreateContainer(option);
       MessageOninit = true;
       $('#messagebox_close').click(function(){
             CloseMessageBox();
       });
       $(window).resize(function(){
            SetStyle(option);
            SetEnabledStyle();
       
       });
       $(window).scroll(function(e){
            MessageBox_scrolltop =document.documentElement.scrollTop;
            MessageBox_scrollleft = document.documentElement.scrollLeft;
            SetEnabledStyle();
       });
       MessageBox_win = $("#messagebox_win");
       $('#messagebox_title').mousedown(handleMouseDown);
       $('#messagebox_title').mouseup(handleMouseUp);
       $('#messagebox_title').mousemove(handleMouseMove);
       document.onmouseup = handleMouseUp;
       
       
    }
    
    
    if(option.height)
        height = parseInt(option.height);
    if(option.width)
        width = parseInt(option.width);
    MessageBox_height = height;
    MessageBox_width = width;
    Messagebox_AC = $('#'+option.control);
    MessageBox_PC = Messagebox_AC.parent();
    Messagebox_AC.css('display','');
    enabled='<div id="messagebox_enabled" style="background-color: lightgrey;width:100%;height:100%;position:absolute;z-index:99998;"></div>'
    enabledframe='<iframe id="messagebox_enabledframe"  frameborder=0 scrolling=no style="position:absolute; visibility:inherit; top:0px; left:0px; width:100%;height:100%;z-index:99997; "></iframe>';
    $('#messagebox_enabledframe').remove();
    $('#messagebox_enabled').remove();
    $('#messagebox_title').html(option.title);
	
	$('#messagebox_optionName').empty();
	$('#messagebox_optionName').append(option.optionName);
	$('#messagebox_optionGet').empty();
	$('#messagebox_optionGet').append('<input id="messagebox_optionId" type="text" size="1" style="display:none;" value="'+option.optionId+'" />');
	$('#messagebox_optionGet').append('<input id="messagebox_optionValueId" type="text" size="1" style="display:none;" value="'+option.optionValueId+'" />');
  
    $('#messagebox_from').append(Messagebox_AC);
    SetStyle(option);
    $(document.body).append(enabledframe);
    $(document.body).append(enabled);
    SetEnabledStyle();
    $('#messagebox_win').fadeIn("slow");
  
    
    //创建对话框容器
    function CreateContainer(option)
    {
        var html;
        html='<div id="messagebox_win" style="position:absolute;z-index:99999;"><table  cellpadding="0" cellspacing="0" id="messagebox_table"><tr><td id="messagebox_title_td"><table id="messagebox_title_table" ><tr><td style="width:99%;" ><div id="messagebox_title" style="width:100%;cursor: default;"></div></td><td><button id="messagebox_close">X</button></td></tr></table></td></tr><tr id="messagebox_body_td"><td valign="top" ><div id="messagebox_from" style="text-align: center;"></div></td></tr></table></div>';
        if(option.parent)
        {
            $('#' + option.parent).append(html);
        }
        else
        {
            $(document.body).append(html);
        }
    }
    
    //设置显示时背景式样
    function SetEnabledStyle()
    {
        var de,w,h,css,region;
        region = GetDocumentRegion();
        css ={width:region.width+"px",height:region.height+"px",
        left: MessageBox_scrollleft+'px',top: MessageBox_scrolltop +'px'}
        GetOpacity(css);
        $("#messagebox_enabled").css(css);
        $("#messagebox_enabledframe").css(css);
    }
    
    //设置透明式样
    function GetOpacity(css)
    {
        if(window.navigator.userAgent.indexOf('MSIE')>=1)
        {
            css.filter= 'progid:DXImageTransform.Microsoft.Alpha(opacity=30)';
        }
        else
        {
            css.opacity= '0.3';
        }   
    }
    
    //设置对话框试样
    function SetStyle(option)
    {
        var region,css;
        region = GetDocumentRegion();
        css ={width:MessageBox_width+'px',height:MessageBox_height+'px',
        left: ((region.width - MessageBox_width)/2)+'px',top: ((region.height - MessageBox_height)/2)+'px'}
        if(region.height < MessageBox_height )//如果body显示的高度小于对话框高度
        {
            css.top=10+'px';
        }
        else
        {
            css.top=((region.height - MessageBox_height)/2)+'px'
        }
        $('#messagebox_win').css(css);
        css.top='0px';
        css.left='0px';
        $('#messagebox_table').css(css);
        css.width='100%';
        css.height='16px';
        $('#messagebox_title_td').css(css);
        css.height= height-46 +'px';
        $('#messagebox_body_td').css(css);
      
       
    }
    
    var down_x,down_y,cx,cy;
    function handleMouseDown(e)
    {
            var evt = e || event;
          
         down_x=evt.clientX;
         down_y = evt.clientY;
         cx =(parseInt(MessageBox_win.css('left'))|0);
         cy = (parseInt(MessageBox_win.css('top'))|0)
         MessageBox_Moving = true;
         document.documentElement.onselectstart = function(){return false};
         document.documentElement.ondrag = function(){return false};
         document.onmousemove = handleMouseMove;
         $(document.body).append('<div id="messagebox_move" style="position: absolute; z-index:100000;border-right: midnightblue 1px dashed; border-top: midnightblue 1px dashed; border-left: midnightblue 1px dashed; border-bottom: midnightblue 1px dashed;"></div>');
         $('#messagebox_move').css('width',MessageBox_win.css('width'));
         $('#messagebox_move').css('height',MessageBox_win.css('height'));
         $('#messagebox_move').css('left',MessageBox_win.css('left'));
         $('#messagebox_move').css('top',MessageBox_win.css('top'));
       
       
    }
   
    function GetDocumentRegion()
    {
        var w,h,de;
        de = document.documentElement;
        w = self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
        h = self.innerHeight || (de&&de.clientHeight)|| document.body.clientHeight;
        return {height:h,width:w};
    }
    
    function handleMouseMove(e)
    {
        var left,top,region;
        if (MessageBox_Moving)
        {
            var evt = e || event;
            
            left =evt.clientX+cx-down_x;
            top = evt.clientY+cy-down_y;
            region = GetDocumentRegion();
            if(left+ MessageBox_width > region.width)
            {
                left = region.width - 10- MessageBox_width;
            }
            if(top + MessageBox_height > region.height)
            {
                top = region.height-10 - MessageBox_height;
            }
            if(left <10)
                left =10;
            if(top <10)
                top =10;
            var css ={left:left+'px',top:top+'px'}
            $('#messagebox_move').css(css);
        }
    }
    
    function handleMouseUp()
    {
        if(MessageBox_Moving)
        {
            MessageBox_win.css('width',$('#messagebox_move').css("width"));
            MessageBox_win.css('height',$('#messagebox_move').css("height"));
            MessageBox_win.css('left',$('#messagebox_move').css("left"));
            MessageBox_win.css('top',$('#messagebox_move').css("top"));
        }
        MessageBox_Moving  = false;
        document.onmousemove = null;
       $('#messagebox_move').remove();
    }

     
}

//关闭模式对话框
function CloseMessageBox()
{
    if(MessageOninit)
    {
       $('#messagebox_win').hide();
       $('#messagebox_enabled').remove();
       $('#messagebox_enabledframe').remove();
      Messagebox_AC.css('display','none');
       MessageBox_PC.append(Messagebox_AC);
        
    }
     document.documentElement.onselectstart = null;
         document.documentElement.ondrag = null;
}
$(document).ready(function(){
    $(document).find('[@showoption]').each(function(){
        var namevalue;
        //虽然显示的元素id,显示宽度,显示高度,标题,对话框寄居的元素对象id
        var option={control:'',width:'400',height:'400',title:'',optionId:'',optionValueId:'',optionName:'',parent:null};
        var properties = $(this).attr('showoption').split(';'); 
        for(i=0;i<properties.length;i++)
        {
            namevalue = properties[i].split(':');
            if(namevalue.length >1)
            {
                execute ="option." + namevalue[0] +'=\''+ namevalue[1]+'\';';
                eval(execute);
            }
        }
        
        $(this).click(function(){
            ShowMessageBox(option);
            document.body.focus();
        });
    });    
});
