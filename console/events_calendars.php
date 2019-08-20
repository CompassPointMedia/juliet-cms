<?php 
/*
*/
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');$hideCtrlSection=false;


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Event Calendar Manager</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
/* local CSS styles */
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
</script>


<!-- following coding modified from ajaxloader.info - a long way to go to be modular -->
<style type="text/css">
#content {
	position:relative;
	float:left;
	}
#color-picker {
	position:absolute;
	top:20px;
	left:0px;
	background: url("/images/i/grad/color-picker.png") no-repeat left top;
	height:258px;
	width:266px;
	display:none;
	border:0;
	z-index:1;
	}
#close {
	position:absolute;
	right:-8px;
	top:-10px;
	height:26px;
	width:26px;
	cursor:pointer;
	}
</style>
<script language="javascript" type="text/javascript">
//Added by Samuel
var colorField='ColorCode';


/* ------------------ domel.js ------------- */
/*
	domEl() function - painless DOM manipulation
	written by Pawel Knapik  //  pawel.saikko.com
*/
var domEl = function(e,c,a,p,x) {
if(e||c) {
	c=(typeof c=='string'||(typeof c=='object'&&!c.length))?[c]:c;	
	e=(!e&&c.length==1)?document.createTextNode(c[0]):e;	
	var n = (typeof e=='string')?document.createElement(e) : !(e&&e===c[0])?e.cloneNode(false):e.cloneNode(true);	
	if(e.nodeType!=3) {
		c[0]===e?c[0]='':'';
		for(var i=0,j=c.length;i<j;i++) {
			if(typeof c[i]=='string' && c[i].length!=0) n.appendChild(document.createTextNode(c[i]));
			else if(c[i].length!=0) n.appendChild(c[i].cloneNode(true));
		}
		if(a) {for(var i=(a.length-1);i>=0;i--) a[i][0]=='class'?n.className=a[i][1]:n.setAttribute(a[i][0],a[i][1]);}
	}
}
	if(!p)return n;
	p=(typeof p=='object'&&!p.length)?[p]:p;
	for(var i=(p.length-1);i>=0;i--) {
		if(x){while(p[i].firstChild)p[i].removeChild(p[i].firstChild);
			if(!e&&!c&&p[i].parentNode)p[i].parentNode.removeChild(p[i]);}
		if(n) p[i].appendChild(n.cloneNode(true));
	}	
}

/*
	getElementsByClass - algorithm by Dustin Diaz, shortened by Pawel Knapik
*/
function getElementsByClass(s,n,t) {
	var c=[], e=(n?n:document).getElementsByTagName(t?t:'*'),r=new RegExp("(^|\\s)"+s+"(\\s|$)");
	for (var i=0,j=e.length;i<j;i++) r.test(e[i].className)?c.push(e[i]):''; return c }
	
/*
	$() based on prototype.js dollar function idea, optimized by Pawel Knapik.
*/
function $(){var r=[],a=arguments;for(var i=0,j=a.length;i<j;i++){(typeof a[i]=='string')?(r.push(document.getElementById(a[i]))):(r.push(a[i]))}
return(r.length==1)?r[0]:r}

/* ------------------ script.js ------------- */
function ALstart() {	
	// G n ration de l'image
	
	if($('top10')) {
		var lis=$('top10').getElementsByTagName('li');
		for(var i=0;i<lis.length;i++) {
			lis[i].onclick=function() {
				var img=this.style.backgroundImage.replace(')','');
				img=img.split('/');
				window.location='download.php?img='+img[img.length-1];
			}
		}
	}
	
	
	// Supprime les caract res invalides de la saisie
	$(colorField).onkeyup=function(e) {
		var rech=new RegExp(/[^0-9A-Fa-f]/g);
		this.value=this.value.replace(rech,'');
	}
	
	// G n ration du menu
	ALmakeMenu();
	// G n ration du color-picker
	ALmakeColorPicker();
}


function ALsendData(data) {
    
    if($('trans').checked) var trans=1;
    else var trans=0;
    
    var c1 = $(colorField).value.substring(0,2);
    var c2 = $(colorField).value.substring(2,4);
    var c3 = $(colorField).value.substring(4);
    
    
    var url = 'cache/'+c1+'/'+c2+'/'+c3+'/';
    
    domEl('img','',[['src',url],['alt','activity indicator']],$('loader'),true);
    
    // Centre l'image
    $('loader').style.lineHeight=115+'px';
    $('loader').style.textAlign='center';
    
    
    if(!$('downloadit')) domEl('a','Download it',[['id','downloadit'],['href','download.php?img='+url]],$('previewinner'));
    else $('downloadit').setAttribute('href','download.php?img='+url);
    
    ALindicator('hidden');

    /*
    // Internet Explorer c'est mal
    if(window.ActiveXObject) var ALajax = new ActiveXObject("Microsoft.XMLHTTP") ;
    // les autres c'est bien
    else var ALajax = new XMLHttpRequest();

    ALajax.onreadystatechange = function() {
        if (ALajax.readyState == 4 && ALajax.status == 200) {
			
			if(!ALajax.responseXML) return false;
			
			var image=ALajax.responseXML.getElementsByTagName('image')[0];
			
			domEl('img','',[['src',image.getAttribute('url')],['alt','activity indicator']],$('loader'),true);

			// Centre l'image
			$('loader').style.lineHeight=115+'px';
			$('loader').style.textAlign='center';
			

			if(!$('downloadit')) domEl('a','Download it',[['id','downloadit'],['href','download.php?img='+image.getAttribute('url')]],$('previewinner'));
			else $('downloadit').setAttribute('href','download.php?img='+image.getAttribute('url'));
			
			ALindicator('hidden');
		}
    }    

	ALajax.open("POST", 'generate.php', true);
    ALajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded; charset=utf-8');
    ALajax.send(data);
    */
}

function ALindicator(view) {
	$('indicator').style.visibility=view;
}

function ALstartLite() {
	ALstart(true);
}

function xiti() {
	Xt_param = 's=278834&amp;p=Ajaxload';
	try {Xt_r = top.document.referrer;}
	catch(e) {Xt_r = document.referrer; }
	Xt_h = new Date();
	Xt_i = '<img ';
	Xt_i += 'src="http://logv32.xiti.com/hit.xiti?'+Xt_param;
	Xt_i += '&amp;hl='+Xt_h.getHours()+'x'+Xt_h.getMinutes()+'x'+Xt_h.getSeconds();
	if(parseFloat(navigator.appVersion)>=4)
	{Xt_s=screen;Xt_i+='&amp;r='+Xt_s.width+'x'+Xt_s.height+'x'+Xt_s.pixelDepth+'x'+Xt_s.colorDepth;}
	$('xiti-logo').innerHTML=Xt_i+'&amp;ref='+Xt_r.replace(/[<>"]/g, '').replace(/&/g, '$')+'" alt="Xiti" />';
}

function ALmakeMenu() {
	return;
	var types = $('type').getElementsByTagName('option');
	var typesL = types.length;
	
	var div = domEl('div','',[['id','typelist']]);
	
	for(var i=0;i<typesL;i++) {
		var img = domEl('img','',[['src','images/exemples/'+types[i].value+'.gif'],['alt',types[i].text]]);
		var a = domEl('a',img,[['href','javascript:ALuseType('+types[i].value+')'],['title',types[i].text]],div);
	}	
	$('type').parentNode.insertBefore(div,$('type'));
	
	domEl('div','',[['id','layer']],$('type').parentNode);
	
	$('layer').onclick = function() {
		this.blur;
		$('typelist').style.display = 'block';
		document.onclick = function() {
			document.onclick = function() {
				$('type').style.display = '';
				$('typelist').style.display = '';
				document.onclick = null;
			}
		}
	}	
}

function ALmakeColorPicker() {
	myAddEventListener($(colorField),'focus',function() {
		$('color-picker').style.display = 'block';
		$('color-picker1').style.display = 'block';
	});
}

function ALuseType(id) {
	$('type').value = id;
	$('type').style.display = '';
	$('typelist').style.display = '';
}

if(window.addEventListener) window.addEventListener('load',ALstart,false);

/* ------------------ color-picker.js ------------- */
// A few configuration settings
var CROSSHAIRS_LOCATION = '/images/i/grad/crosshairs.png';
var HUE_SLIDER_LOCATION = '/images/i/grad/h.png';
var HUE_SLIDER_ARROWS_LOCATION = '/images/i/grad/position.png';
var SAT_VAL_SQUARE_LOCATION = '/images/i/grad/sv.png';

// Here are some boring utility functions. The real code comes later.

function hexToRgb(hex_string, default_)
{
    if (default_ == undefined)
    {
        default_ = null;
    }

    if (hex_string.substr(0, 1) == '#')
    {
        hex_string = hex_string.substr(1);
    }
    
    var r;
    var g;
    var b;
    if (hex_string.length == 3)
    {
        r = hex_string.substr(0, 1);
        r += r;
        g = hex_string.substr(1, 1);
        g += g;
        b = hex_string.substr(2, 1);
        b += b;
    }
    else if (hex_string.length == 6)
    {
        r = hex_string.substr(0, 2);
        g = hex_string.substr(2, 2);
        b = hex_string.substr(4, 2);
    }
    else
    {
        return default_;
    }
    
    r = parseInt(r, 16);
    g = parseInt(g, 16);
    b = parseInt(b, 16);
    if (isNaN(r) || isNaN(g) || isNaN(b))
    {
        return default_;
    }
    else
    {
        return {r: r / 255, g: g / 255, b: b / 255};
    }
}

function rgbToHex(r, g, b, includeHash)
{
    r = Math.round(r * 255);
    g = Math.round(g * 255);
    b = Math.round(b * 255);
    if (includeHash == undefined)
    {
        includeHash = true;
    }
    
    r = r.toString(16);
    if (r.length == 1)
    {
        r = '0' + r;
    }
    g = g.toString(16);
    if (g.length == 1)
    {
        g = '0' + g;
    }
    b = b.toString(16);
    if (b.length == 1)
    {
        b = '0' + b;
    }
    return ((includeHash ? '#' : '') + r + g + b).toUpperCase();
}

var arVersion = navigator.appVersion.split("MSIE");
var version = parseFloat(arVersion[1]);

function fixPNG(myImage)
{
    if ((version >= 5.5) && (version < 7) && (document.body.filters)) 
    {
        var node = document.createElement('span');
        node.id = myImage.id;
        node.className = myImage.className;
        node.title = myImage.title;
        node.style.cssText = myImage.style.cssText;
        node.style.setAttribute('filter', "progid:DXImageTransform.Microsoft.AlphaImageLoader"
                                        + "(src=\'" + myImage.src + "\', sizingMethod='scale')");
        node.style.fontSize = '0';
        node.style.width = myImage.width.toString() + 'px';
        node.style.height = myImage.height.toString() + 'px';
        node.style.display = 'inline-block';
        return node;
    }
    else
    {
        return myImage.cloneNode(false);
    }
}

function trackDrag(node, handler)
{
    function fixCoords(x, y)
    {
        var nodePageCoords = pageCoords(node);
        x = (x - nodePageCoords.x) + document.documentElement.scrollLeft;
        y = (y - nodePageCoords.y) + document.documentElement.scrollTop;
        if (x < 0) x = 0;
        if (y < 0) y = 0;
        if (x > node.offsetWidth - 1) x = node.offsetWidth - 1;
        if (y > node.offsetHeight - 1) y = node.offsetHeight - 1;
        return {x: x, y: y};
    }
    function mouseDown(ev)
    {
        var coords = fixCoords(ev.clientX, ev.clientY);
        var lastX = coords.x;
        var lastY = coords.y;
        handler(coords.x, coords.y);

        function moveHandler(ev)
        {
            var coords = fixCoords(ev.clientX, ev.clientY);
            if (coords.x != lastX || coords.y != lastY)
            {
                lastX = coords.x;
                lastY = coords.y;
                handler(coords.x, coords.y);
            }
        }
        function upHandler(ev)
        {
            myRemoveEventListener(document, 'mouseup', upHandler);
            myRemoveEventListener(document, 'mousemove', moveHandler);
            myAddEventListener(node, 'mousedown', mouseDown);
        }
        myAddEventListener(document, 'mouseup', upHandler);
        myAddEventListener(document, 'mousemove', moveHandler);
        myRemoveEventListener(node, 'mousedown', mouseDown);
        if (ev.preventDefault) ev.preventDefault();
    }
    myAddEventListener(node, 'mousedown', mouseDown);
    node.onmousedown = function(e) { return false; };
    node.onselectstart = function(e) { return false; };
    node.ondragstart = function(e) { return false; };
}

var eventListeners = [];

function findEventListener(node, event, handler)
{
    var i;
    for (i in eventListeners)
    {
        if (eventListeners[i].node == node && eventListeners[i].event == event
         && eventListeners[i].handler == handler)
        {
            return i;
        }
    }
    return null;
}
function myAddEventListener(node, event, handler)
{
    if (findEventListener(node, event, handler) != null)
    {
        return;
    }

    if (!node.addEventListener)
    {
        node.attachEvent('on' + event, handler);
    }
    else
    {
        node.addEventListener(event, handler, false);
    }

    eventListeners.push({node: node, event: event, handler: handler});
}

function removeEventListenerIndex(index)
{
    var eventListener = eventListeners[index];
    delete eventListeners[index];
    
    if (!eventListener.node.removeEventListener)
    {
        eventListener.node.detachEvent('on' + eventListener.event,
                                       eventListener.handler);
    }
    else
    {
        eventListener.node.removeEventListener(eventListener.event,
                                               eventListener.handler, false);
    }
}

function myRemoveEventListener(node, event, handler)
{
    removeEventListenerIndex(findEventListener(node, event, handler));
}

function cleanupEventListeners()
{
    var i;
    for (i = eventListeners.length; i > 0; i--)
    {
        if (eventListeners[i] != undefined)
        {
            removeEventListenerIndex(i);
        }
    }
}
myAddEventListener(window, 'unload', cleanupEventListeners);

// This copyright statement applies to the following two functions,
// which are taken from MochiKit.
//
// Copyright 2005 Bob Ippolito <bob@redivi.com>
//
// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and associated documentation files (the
// "Software"), to deal in the Software without restriction, including
// without limitation the rights to use, copy, modify, merge, publish,
// distribute, sublicense, and/or sell copies of the Software, and to
// permit persons to whom the Software is furnished to do so, subject
// to the following conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Software.
// 
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
// EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
// BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN
// ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
// CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

function hsvToRgb(hue, saturation, value)
{
	
    var red;
    var green;
    var blue;		
    if (value == 0.0)
    {
        red = 0;
        green = 0;
        blue = 0;
    }
    else
    {
        var i = Math.floor(hue * 6);
        var f = (hue * 6) - i;
        var p = value * (1 - saturation);
        var q = value * (1 - (saturation * f));
        var t = value * (1 - (saturation * (1 - f)));
        switch (i)
        {
            case 1: red = q; green = value; blue = p; break;
            case 2: red = p; green = value; blue = t; break;
            case 3: red = p; green = q; blue = value; break;
            case 4: red = t; green = p; blue = value; break;
            case 5: red = value; green = p; blue = q; break;
            case 6: // fall through
            case 0: red = value; green = t; blue = p; break;
        }
    }
		
    return {r: red, g: green, b: blue};
}

function rgbToHsv(red, green, blue)
{
    var max = Math.max(Math.max(red, green), blue);
    var min = Math.min(Math.min(red, green), blue);
    var hue;
    var saturation;
    var value = max;
    if (min == max)
    {
        hue = 0;
        saturation = 0;
    }
    else
    {
        var delta = (max - min);
        saturation = delta / max;
        if (red == max)
        {
            hue = (green - blue) / delta;
        }
        else if (green == max)
        {
            hue = 2 + ((blue - red) / delta);
        }
        else
        {
            hue = 4 + ((red - green) / delta);
        }
        hue /= 6;
        if (hue < 0)
        {
            hue += 1;
        }
        if (hue > 1)
        {
            hue -= 1;
        }
    }
    return {
        h: hue,
        s: saturation,
        v: value
    };
}

function pageCoords(node)
{
    var x = node.offsetLeft;
    var y = node.offsetTop;
    var parent = node.offsetParent;
    while (parent != null)
    {
        x += parent.offsetLeft;
        y += parent.offsetTop;
        parent = parent.offsetParent;
    }
    return {x: x, y: y};
}

// The real code begins here.
var huePositionImg = document.createElement('img');
huePositionImg.galleryImg = false;
huePositionImg.width = 20;
huePositionImg.height = 11;
huePositionImg.src = HUE_SLIDER_ARROWS_LOCATION;
huePositionImg.style.position = 'absolute';

var hueSelectorImg = document.createElement('img');
hueSelectorImg.galleryImg = false;
hueSelectorImg.width = 20;
hueSelectorImg.height = 200;
hueSelectorImg.src = HUE_SLIDER_LOCATION;
hueSelectorImg.style.display = 'block';

var satValImg = document.createElement('img');
satValImg.galleryImg = false;
satValImg.width = 200;
satValImg.height = 200;
satValImg.src = SAT_VAL_SQUARE_LOCATION;
satValImg.style.display = 'block';

var crossHairsImg = document.createElement('img');
crossHairsImg.galleryImg = false;
crossHairsImg.width = 21;
crossHairsImg.height = 21;
crossHairsImg.src = CROSSHAIRS_LOCATION;
crossHairsImg.style.position = 'absolute';

function makeColorSelector(inputBox)
{
    var rgb, hsv
    
    function colorChanged()
    {
        var hex = rgbToHex(rgb.r, rgb.g, rgb.b);
        var hueRgb = hsvToRgb(hsv.h, 1, 1);
        var hueHex = rgbToHex(hueRgb.r, hueRgb.g, hueRgb.b);
        previewDiv.style.background = hex;
        inputBox.value = hex.substr(1);
        satValDiv.style.background = hueHex;
        crossHairs.style.left = ((hsv.v*199)-10).toString() + 'px';
        crossHairs.style.top = (((1-hsv.s)*199)-10).toString() + 'px';
        huePos.style.top = ((hsv.h*199)-5).toString() + 'px';
    }
    function rgbChanged()
    {
        hsv = rgbToHsv(rgb.r, rgb.g, rgb.b);
        colorChanged();
    }
    function hsvChanged()
    {
        rgb = hsvToRgb(hsv.h, hsv.s, hsv.v);
        colorChanged();
    }
    
    var colorSelectorDiv = document.createElement('div');
    /*colorSelectorDiv.style.position = 'relative';
    colorSelectorDiv.style.height = '238px';
    colorSelectorDiv.style.width = '246px';*/
    
    var satValDiv = document.createElement('div');
    satValDiv.style.position = 'relative';
    satValDiv.style.width = '200px';
    satValDiv.style.height = '200px';
    colorSelectorDiv.style.margin = '14px';
    var newSatValImg = fixPNG(satValImg);
    satValDiv.appendChild(newSatValImg);
    var crossHairs = crossHairsImg.cloneNode(false);
    satValDiv.appendChild(crossHairs);
    function satValDragged(x, y)
    {
        hsv.s = 1-(y/199);
        hsv.v = (x/199);
        hsvChanged();
    }
    trackDrag(satValDiv, satValDragged)
    colorSelectorDiv.appendChild(satValDiv);

    var hueDiv = document.createElement('div');
    hueDiv.style.position = 'absolute';
    hueDiv.style.right = '22px';
    hueDiv.style.top = '14px';
    hueDiv.style.width = '20px';
    hueDiv.style.height = '200px';
    var huePos = fixPNG(huePositionImg);
    hueDiv.appendChild(hueSelectorImg.cloneNode(false));
    hueDiv.appendChild(huePos);
    function hueDragged(x, y)
    {
        hsv.h = y/199;
        hsvChanged();
    }
    trackDrag(hueDiv, hueDragged);
    colorSelectorDiv.appendChild(hueDiv);
    
    var previewDiv = document.createElement('div');
    previewDiv.style.height = '20px'
    previewDiv.style.width = '228px';
    previewDiv.style.position = 'absolute';
    previewDiv.style.top = '220px';
    previewDiv.style.left = '14px';
    previewDiv.style.border = '1px solid black';
    colorSelectorDiv.appendChild(previewDiv);
    
    function inputBoxChanged(first)
    {
		if(inputBox.value.length!=6) return;

        rgb = hexToRgb(inputBox.value, {r: 0, g: 0, b: 0});
        rgbChanged();
    }
    myAddEventListener(inputBox, 'keyup', inputBoxChanged);
    //inputBox.size = 8;
    //inputBox.style.position = 'absolute';
    //inputBox.style.right = '15px';
    //inputBox.style.top = (225 + (25 - (inputBox.offsetHeight/2))).toString() + 'px';
    //colorSelectorDiv.appendChild(inputBox);
    
    inputBoxChanged(true);
    
    return colorSelectorDiv;
}

function makeColorSelectors(ev)
{
    var inputNodes = document.getElementsByTagName('input');
    var i;

	var j=0;
		var ok = new Array();
    for (i = 0; i < inputNodes.length; i++)
    {
        var node = inputNodes[i];
        if (node.className != 'color' || ok[node.id])
        {
            continue;
        }
				ok[node.id]='ok';
        var parent = node.parentNode;
        var prevNode = node.previousSibling;
        var selector = makeColorSelector(node);
		selector.id = 'color-picker'+(++j);
		selector.style.display = 'none';
        //parent.insertBefore(selector, (prevNode ? prevNode.nextSibling : null));
				
		document.getElementById('color-picker').appendChild(selector);
    }
	var img=domEl('img','',[['id','close'],['src','/images/i/grad/color-picker-close.png'],['title','Close']]);
	img.onclick = function() {
		this.parentNode.style.display='none';
	}
	document.getElementById('color-picker').appendChild(img);				
}

myAddEventListener(window, 'load', makeColorSelectors);
</script>


<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="../console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->

<?php
if(count($_REQUEST)) /* taken from navbuttons 1.43 */
foreach($_REQUEST as $n=>$v){
	if(substr($n,0,2)=='cb'){
		if(!$setCBPresent){
			$setCBPresent=true;
			?><!-- callback fields automatically generated --><?php
			echo "\n";
			?><input name="cbPresent" id="cbPresent" value="1" type="hidden" /><?php
			echo "\n";
		}
		if(is_array($v)){
			foreach($v as $o=>$w){
				echo "\t\t";
				?><input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo stripslashes($w)?>" /><?php
				echo "\n";
			}
		}else{
			echo "\t\t";
			?><input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo stripslashes($v)?>" /><?php
			echo "\n";
		}
	}
}
?>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<?php require($CONSOLE_ROOT.'/components/comp_607_eventcalendars.php');?>

<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
<!-- InstanceEndEditable --></div>
<?php if(!$suppressForm){ ?>
</form>
<?php }?>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onclick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onclick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onclick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php
page_end();
?>