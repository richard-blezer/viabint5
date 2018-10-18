/**
 * Version: 1.1
 * Date: 2011-03-16 17:10
 */


_shopgateHTTP_GET_VARS=new Array();
_shopgateStrGET=document.location.search.substr(1,document.location.search.length);
if(_shopgateStrGET!='')
{
	_shopgateGArr=_shopgateStrGET.split('&');
	for(i=0;i<_shopgateGArr.length;++i)
        {
        v='';
        vArr=_shopgateGArr[i].split('=');
        
        if(vArr.length>1){v=vArr[1];}
        _shopgateHTTP_GET_VARS[unescape(vArr[0])]=unescape(v);
       }
}
 
function _shopgateGET(v)
{
	if(!_shopgateHTTP_GET_VARS[v]){return 'undefined';}
	return _shopgateHTTP_GET_VARS[v];
}

function _shopgateIsDefined(variable) {
	return (typeof(variable) != 'undefined');// && variable != null);
}

function _shopgateBuildUrl() {
	var url = "http://start.shopgate.com/" + _shopgateShopNumber;
	return url;
}

function _shopgateCheckUserAgent() {
	return navigator.userAgent.match(/iPhone|iPad|iPod/);
}

function _shopgateRedirect() {
	if(!_shopgateIsDefined(_shopgateShopNumber)) return;
	
	var _cookieExpireTime = 30; // Minutes
	
	if(_shopgateCheckUserAgent()) {
		var isBackFromShopgate = _shopgateGET("shopgate_use_shop_website");
		
		if(document.cookie.match(/__shopgate_use_app=0/)) {
			return;
		}
		
		if(isBackFromShopgate !== 'undefined'
		&& typeof(isBackFromShopgate) !== 'undefined') {
			document.cookie = "__shopgate_use_app=0;path=/";
		} else {
			window.location.href = _shopgateBuildUrl();
		}
	}
}

function _shopgateDrawTopBar() {
	var div = document.createElement("div");
	
	div.id = "_shopgateAppBanner";
	
	div.style.width = "100%";
	div.style.height = "200px !important";
	div.style.background = "gray";
	div.style.overflow = "hidden";

//	var imgAppStore = document.createElement("img");
//	imgAppStore.src = "http://shopgate-static.s3.amazonaws.com/api/ios_icons/app_store.png";
//	
//	var btnAppStore = document.createElement("a");
//	btnAppStore = 
	
	var iFrame = document.createElement("iframe");
	//iFrame.src = "https://www.shopgatepg.com/shopstart/app_banner/10001";
	iFrame.src = "http://www.localhost/index.php/shopstart/app_banner/10008";
	iFrame.style.width = "100%";
	iFrame.style.height = "200px !important";
	iFrame.style.border = "none";
	
	div.appendChild(iFrame);
	
	document.body.insertBefore(div, document.body.firstChild);
}

function _shopgateDrawMobileButton() {
	//if(!_shopgateCheckUserAgent()) return;
	
	//_shopgateDrawTopBar();
	
	var text = document.createTextNode("Mobile Theme");
	
	function _createOffImage() {
		var imgOff = document.createElement("img");
		
		imgOff.src = 'http://shopgatepg-static.s3.amazonaws.com/api/ios_icons/off.jpg';
		
		imgOff.style.fontFamily = 'Georgia,"Bitstream Charter",serif';
		
		imgOff.style.fontSize = '12px';
	    imgOff.style.lineHeight = '18px';
		
		imgOff.style.font = 'bold x-large/1.2 "Helvetica Neue",Helvetica,Arial,sans-serif';
	    imgOff.style.textAlign = 'left ';
		
		imgOff.style.color = '#0066CC';
		
		imgOff.style.background = 'none repeat scroll 0 0 transparent';
	    imgOff.style.margin = '0';
	    imgOff.style.padding = '0';
	    imgOff.style.verticalAlign = 'baseline';
		
		imgOff.style.border = 'medium none';
		    
		imgOff.style.height = '34px ';
	    imgOff.style.position = 'absolute ';
	    imgOff.style.right = '15px ';
	    imgOff.style.top = '12px ';
	 	
	 	return imgOff;
	}
	
	function _createOnImage() {
		var imgOn = document.createElement("img");
		
		imgOn.src = 'http://shopgatepg-static.s3.amazonaws.com/api/ios_icons/on.jpg';
		
		imgOn.style.fontFamily = 'Georgia,"Bitstream Charter",serif';
		
		imgOn.style.visibility = 'collapse';
		
		imgOn.style.fontSize = '12px';
	    imgOn.style.lineHeight = '18px';
		
		imgOn.style.font = 'bold x-large/1.2 "Helvetica Neue",Helvetica,Arial,sans-serif';
	    imgOn.style.textAlign = 'left ';
		
		imgOn.style.color = '#0066CC';
		
		imgOn.style.background = 'none repeat scroll 0 0 transparent';
	    imgOn.style.margin = '0';
	    imgOn.style.padding = '0';
	    imgOn.style.verticalAlign = 'baseline';
		
		imgOn.style.border = 'medium none';
		    
		imgOn.style.height = '34px ';
	    imgOn.style.position = 'absolute ';
	    imgOn.style.right = '15px ';
	    imgOn.style.top = '12px ';
	 	
	 	return imgOn;
	}
	
	function _createDiv() {
		var div = document.createElement("div");
		
		div.style.fontFamily = 'Georgia,"Bitstream Charter",serif';
		div.style.lineHeight = '18px';
		div.style.fontSize = '12px';
		div.style.verticalAlign = 'baseline';
		div.style.backgroundColor = "#FFFFFF";
	    div.style.border = "1px solid #ADADAD";
	    div.style.clear = "both";
	    div.style.color = "#222222";
	    div.style.font = 'bold x-large/1.2 "Helvetica Neue",Helvetica,Arial,sans-serif';
	    div.style.margin = '40px auto';
	    div.style.padding = '15px ';
	    div.style.position = 'relative ';
	    div.style.textAlign = 'left ';
	    div.style.width = '375px ';
		div.style.WebkitBorderRadius  = '8px';
		
		return div;
	}
	
	function _createAnchor() {
		var anchor = document.createElement("a");
		
		anchor.id = "_shopgateMobileToggle";
		
	    anchor.style.fontFamily = 'Georgia,"Bitstream Charter",serif';
		
		anchor.style.fontSize = '12px';
	    anchor.style.lineHeight = '18px';
	    
		anchor.style.font = 'bold x-large/1.2 "Helvetica Neue",Helvetica,Arial,sans-serif';
	    anchor.style.textAlign = 'left ';
		
		anchor.style.background = 'none repeat scroll 0 0 transparent';
	    anchor.style.border = '0 none';
	    anchor.style.margin = '0';
	    anchor.style.padding = '0';
	    anchor.style.verticalAlign = 'baseline';
		
		anchor.style.color = '#0066CC';
		
//		anchor.href = _shopgateBuildUrl();
		
		anchor.onclick = function() {
			var appBanner = document.getElementById('_shopgateAppBanner');
			
			if(anchor.firstChild.style.visibility == 'collapse') {
				document.body.removeChild(appBanner);
				
				anchor.firstChild.style.visibility = 'visible';
				anchor.firstChild.nextSibling.style.visibility = 'collapse';
			} else {
				_shopgateDrawTopBar();
				
				anchor.firstChild.style.visibility = 'collapse';
				anchor.firstChild.nextSibling.style.visibility = 'visible';
			}
		};
		
		anchor.appendChild(_createOffImage());
		anchor.appendChild(_createOnImage());
		
		return anchor;
	}

	var div = _createDiv();
	div.appendChild(text);
	div.appendChild(_createAnchor());
	
	document.body.appendChild(div);
}

_shopgateRedirect();

window.addEventListener('load', _shopgateDrawMobileButton, false);

window.addEventListener('unload', function() { document.getElementById('_shopgateMobileToggle').firstChild.style.visibility = 'visible'; document.getElementById('_shopgateMobileToggle').firstChild.nextSibling.style.visibility = 'collapse'; }, false);
