<?

	$_tpl['styles']['reformal'] = '.widsnjx {margin:0 auto; position:relative;}.widsnjx fieldset {padding:0; border:none; border:0px solid #000; margin:0;}#poxupih { width:753px; height:auto; position:relative;z-index:1001; min-height:490px;}.poxupih_top {background:url(http://widget.idea.informer.com/i/wdt/box_shadow_n.png) top left repeat-x ; padding-top:20px; margin:0 20px;_background-image: none; _filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'http://widget.idea.informer.com/i/wdt/box_shadow_n.png\');}.poxupih_btm {background:url(http://widget.idea.informer.com/i/wdt/box_shadow_s.png) bottom repeat-x; padding-bottom:20px;_background-image: none; _filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'http://widget.idea.informer.com/i/wdt/box_shadow_s.png\');}.poxupih_1t {background:url(http://widget.idea.informer.com/i/wdt/box_shadow_w.png) left repeat-y; padding-left:20px; margin:0 -20px;_background-image: none; _filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'http://widget.idea.informer.com/i/wdt/box_shadow_w.png\');}.poxupih_rt {background:url(http://widget.idea.informer.com/i/wdt/box_shadow_e.png) right repeat-y; padding-right:20px;_background-image: none; _filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'http://widget.idea.informer.com/i/wdt/box_shadow_e.png\');}.poxupih_center {width:713px;min-width:713px; min-height:450px; height:450px; background:#516683;color:#FFFFFF;}#poxupih_tl {position:absolute;top:0;left:0;height:20px;width:20px;background:url(http://widget.idea.informer.com/i/wdt/box_shadow_nw.png) 0 0 no-repeat;*behavior: url(http://reformal.ru/iepngfix.htc)}#poxupih_bl {position:absolute;bottom:0;left:0;height:20px;width:20px;background:url(http://widget.idea.informer.com/i/wdt/box_shadow_sw.png) 0 0 no-repeat;*behavior: url(http://reformal.ru/iepngfix.htc)}#poxupih_tr {position:absolute;top:0;right:0;height:20px;width:20px;background:url(http://widget.idea.informer.com/i/wdt/box_shadow_ne.png) 0 0 no-repeat;*behavior: url(http://reformal.ru/iepngfix.htc)}#poxupih_br {position:absolute;bottom:0;right:0;height:20px;width:20px;background:url(http://widget.idea.informer.com/i/wdt/box_shadow_se.png) 0 0 no-repeat;*behavior: url(http://reformal.ru/iepngfix.htc)}.gertuik { padding:10px 20px; font-size:18px; font-weight:bold; font-family:Arial, Helvetica, sans-serif; overflow:hidden; max-height:42px;}a.pokusijy {cursor:pointer;display:block; width:16px; height:16px; background: url(http://reformal.ru/i/wdg_data/expand.png) 100% 0px no-repeat; float:right; margin-top:3px;*behavior: url(http://reformal.ru/iepngfix.htc)}.bvnmrte {padding:0; width:100%;overflow:hidden;}.drsdtf { font-family: Tahoma, Geneva, sans-serif; padding:3px 20px; text-align:right; font-size:10px; max-height:22px;}.drsdtf a { font-weight:bold; color:#fff; text-decoration:none;}#poxupih a {position:relative; z-index:10;}/*меняем размеры окна*/#poxupih { width:"+(dref_w)+"px;}.poxupih_center {width:"+(dref_w)+"px; height:"+(dref_h)+"px; background:"+dref_bcolor+";color:#fff;} /*меняем фон и цвет шрифта заголовка и футера*/.tdsh{background-image: none; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'http://reformal.ru/i/feedback_tab.png\');} .frby {position:fixed; left:0; top:263px; z-index:5; width:22px; height:151px;}* html .frby {position:absolute;}.frby a {display:block; width:22px; height:151px; background:#0033ff;}.frby a:hover {background:#0033ff;}.frby img {border:0;} .frgtd {position:fixed; right:1px; top:263px; z-index:5; width:22px; height:151px;}* html .frgtd {position:absolute;}.frgtd a {display:block; width:22px; height:151px; background:#0033ff;}.frgtd a:hover {background:#0033ff;}.frgtd img {border:0;}';
	$_tpl['script']['reformal'] = '
/*REFORMAL*/
reformal_wdg_w    = "713";
reformal_wdg_h    = "460";
reformal_wdg_domain    = "unidoski";
reformal_wdg_mode    = 1;
reformal_wdg_title   = "УниДоски.ру";
reformal_wdg_ltitle  = "Идеи, пожелания, благодарности.. Пишите нам.";
reformal_wdg_lfont   = "Verdana, Geneva, sans-serif";
reformal_wdg_lsize   = "11px";
reformal_wdg_color   = "#6b3b3b";
reformal_wdg_bcolor  = "#516683";
reformal_wdg_tcolor  = "#FFFFFF";
reformal_wdg_align   = "right";
reformal_wdg_charset = "utf-8";
reformal_wdg_waction = 0;
reformal_wdg_vcolor  = "#9FCE54";
reformal_wdg_cmline  = "#E0E0E0";
reformal_wdg_glcolor  = "#105895";
reformal_wdg_tbcolor  = "#FFFFFF";
reformal_wdg_tcolor_aw4  = "#3F4543";
 
reformal_wdg_bimage = "81ed4e11a7ace40882be38e39092b4fd.png";

function ref_ud(a,ob){if(!ob) ob="body"; $(ob).append(a);}

var dref_w = ((typeof reformal_wdg_w != "undefined") ? reformal_wdg_w : 713) ;
var dref_h = ((typeof reformal_wdg_h != "undefined") ? reformal_wdg_h : 450) ;
var dref_mode = ((typeof reformal_wdg_mode != "undefined") ? reformal_wdg_mode : 0) ;
var dref_title = ((typeof reformal_wdg_title != "undefined") ? reformal_wdg_title : "Реформал") ;
var dref_ltitle = ((typeof reformal_wdg_ltitle != "undefined") ? reformal_wdg_ltitle : "Оставить отзыв") ;
var dref_lfont = ((typeof reformal_wdg_lfont != "undefined") ? reformal_wdg_lfont : "") ;
var dref_lsize = ((typeof reformal_wdg_lsize != "undefined") ? reformal_wdg_lsize : "12px") ;
var dref_color = ((typeof reformal_wdg_color != "undefined" && \'#\'!=reformal_wdg_color) ? reformal_wdg_color : "orange") ;
var dref_bcolor = ((typeof reformal_wdg_bcolor != "undefined") ? reformal_wdg_bcolor : "#FFA000") ;
var dref_tcolor = ((typeof reformal_wdg_tcolor != "undefined") ? reformal_wdg_tcolor : "#FFFFFF") ;
var dref_align = ((typeof reformal_wdg_align != "undefined" && \'\'!=reformal_wdg_align) ? reformal_wdg_align : "left") ;
var dref_charset = ((typeof reformal_wdg_charset != "undefined") ? reformal_wdg_charset : "") ;
var dref_waction = ((typeof reformal_wdg_waction != "undefined") ? reformal_wdg_waction : "0") ;
var dref_vcolor = ((typeof reformal_wdg_vcolor != "undefined") ? reformal_wdg_vcolor : "#9fce54") ;
    dref_vcolor = dref_vcolor.substring(1, dref_vcolor.length);
var dref_cmline = ((typeof reformal_wdg_cmline != "undefined") ? reformal_wdg_cmline : "#E0E0E0") ;
    dref_cmline = dref_cmline.substring(1, dref_cmline.length);
var dref_glcolor = ((typeof reformal_wdg_glcolor != "undefined") ? reformal_wdg_glcolor : "#105895") ;
    dref_glcolor = dref_glcolor.substring(1, dref_glcolor.length);
var dref_tbcolor = ((typeof reformal_wdg_tbcolor != "undefined") ? reformal_wdg_tbcolor : "#FFFFFF") ;
    dref_tbcolor = dref_tbcolor.substring(1, dref_tbcolor.length);

var dref_tcolor_aw4 = (typeof reformal_wdg_tcolor_aw4 != "undefined") ? reformal_wdg_tcolor_aw4 : "#3F4543" ;
    dref_tcolor_aw4 = dref_tcolor_aw4.substring(1, dref_tcolor_aw4.length);


var dref_ext_img = (typeof reformal_wdg_bimage != "undefined" && reformal_wdg_bimage!=\'\') ? 1 : 0 ;
var dref_ext_img_m = (dref_ext_img && reformal_wdg_bimage.substring(3, reformal_wdg_bimage).toLowerCase()==\'htt\') ? 1 : 0;
if (dref_ext_img_m && reformal_wdg_bimage.indexOf( \'reformal.ru/files/\', 0 ) > 0) { dref_ext_img_m = 0; var v = reformal_wdg_bimage.toString().split ( \'/\' ); reformal_wdg_bimage = v[v.length-1]; }

var dref_ext_cms = ((typeof reformal_wdg_cms != "undefined") ? reformal_wdg_cms : \'reformal\') ;

if (typeof reformal_wdg_vlink == "undefined")
        out_link = \'http://\'+reformal_wdg_domain+\'.reformal.ru/proj/?mod=one\';
else
        out_link = (reformal_wdg_https?\'https://\':\'http://\')+reformal_wdg_vlink;


if (dref_waction){
    		if(typeof reformal_wdg_vlink != "undefined")
		vlink = reformal_wdg_vlink;
        else
	    	vlink = \'http://\'+reformal_wdg_domain+\'.reformal.ru/proj/?mod=one\';
}
else
    	vlink = \'javascript:MyOtziv.mo_show_box();\'; 	

MyOtzivCl = function() {
    var siteAdr = \'http://reformal.ru/\';
    
    this.mo_get_win_width = function() {
        var myWidth = 0;
        if( typeof( window.innerWidth ) == \'number\' )             myWidth = window.innerWidth;
        else if( document.documentElement && document.documentElement.clientWidth )             myWidth = document.documentElement.clientWidth;
        else if( document.body && document.body.clientWidth)             myWidth = document.body.clientWidth;
        return myWidth;
    }
	
    this.mo_get_win_height = function() {
        var myHeight = 0;
        if( typeof( window.innerHeight ) == \'number\' )             myHeight = window.innerHeight;
        else if( document.documentElement && document.documentElement.clientHeight )             myHeight = document.documentElement.clientHeight;
        else if( document.body && document.body.clientHeight)             myHeight = document.body.clientHeight;
        return myHeight;
    }

    this.mo_get_scrol = function() {
        var yPos = 0;
        if (self.pageYOffset) {
            yPos = self.pageYOffset;
        } else if (document.documentElement && document.documentElement.scrollTop){
            yPos = document.documentElement.scrollTop;
        } else if (document.body) {
            yPos = document.body.scrollTop;
        }
        return yPos;
    }

    this.mo_show_box = function() {
	    if (document.getElementById("fthere").innerHTML == "") {
		    document.getElementById("fthere").innerHTML = "<iframe id=\"thrwdgfr\" src=\""+siteAdr+"wdg4.php?w="+dref_w+"&h="+dref_h+"&domain="+reformal_wdg_domain+"&bcolor="+dref_tbcolor+"&glcolor="+dref_glcolor+"&cmline="+dref_cmline+"&vcolor="+dref_vcolor+"&tcolor_aw4="+dref_tcolor_aw4+"\" width=\""+dref_w+"\" height=\""+(dref_h-75)+"\" frameborder=\"0\" scrolling=\"no\">Frame error</iframe>";
		}
        var l = this.mo_get_win_width()/2 - dref_w/2;
        var t = this.mo_get_win_height()/2 - dref_h/2 + this.mo_get_scrol();
        document.getElementById(\'myotziv_box\').style.top  = (dref_ext_cms==\'joomla\') ? \'35px\' : t+\'px\';
        document.getElementById(\'myotziv_box\').style.left = l+\'px\'; 
        document.getElementById(\'myotziv_box\').style.display=\'block\';
    }

    this.mo_hide_box = function() {
        document.getElementById(\'myotziv_box\').style.display=\'none\';
    }
    
    this.mo_showframe = function() {
        if (!dref_mode) {
            if (\'left\' == dref_align) { 
	        if (!dref_ext_img)
		{
                ref_ud("<div class=\"frby\"><a href=\""+vlink+"\""+((dref_waction)?\' target=\"_blank\"\':"")+"><img src=\""+siteAdr+"i/transp.gif\" width=\"22\" height=\"151\" alt=\"\" style=\"border: 0;\" class=\"tdsh\" /></a></div>");
		}else
		{
		ref_ud("<div class=\"frby\"><a href=\""+vlink+"\""+((dref_waction)?\' target=\"_blank\"\':"")+"><img src=\""+(dref_ext_img_m ?reformal_wdg_bimage : siteAdr+\'files/images/buttons/\'+reformal_wdg_bimage)+"\" alt=\"\" style=\"border: 0;\" class=\"tdsh\" /></a></div>");
		}
            } 
            else {
		if (!dref_ext_img)
		{
                ref_ud("<div class=\"frgtd\"><a href=\""+vlink+"\""+((dref_waction)?\' target=\"_blank\"\':"")+"><img src=\""+siteAdr+"i/transp.gif\" width=\"22\" height=\"151\" alt=\"\" style=\"border: 0;\" class=\"tdsh\" /></a></div>");}else
				{
		ref_ud("<div class=\"frgtd\"><a href=\""+vlink+"\""+((dref_waction)?\' target=\"_blank\"\':"")+"><img src=\""+(dref_ext_img_m ?reformal_wdg_bimage : siteAdr+\'files/images/buttons/\'+reformal_wdg_bimage)+"\" alt=\"\" style=\"border: 0;\" class=\"tdsh\" /></a></div>");
		
				}
            }
        }
        
        ref_ud("<div style=\""+((dref_ext_cms==\'joomla\') ? \'position:fixed;\' : \'position:absolute;\')+" display: none; top: 0px; left: 0px;\" id=\"myotziv_box\"> <div class=\"widsnjx\"> <div id=\"poxupih\"> <div class=\"poxupih_top\"> <div class=\"poxupih_btm\"> <div class=\"poxupih_1t\"> <div class=\"poxupih_rt\"> <div class=\"poxupih_center\">");
        
        ref_ud(\'<div class="gertuik"> <a class="pokusijy" title="" onclick="MyOtziv.mo_hide_box();"></a> \'+dref_title+\'</div> <div class="bvnmrte" id="fthere"></div>\',\'.poxupih_center\');
        
        ref_ud(\'<div class="drsdtf">на платформе <a href="" title="Reformal.ru">Reformal.ru</a></div> </div> </div> </div> </div> </div> <div id="poxupih_tl"></div> <div id="poxupih_bl"></div> <div id="poxupih_tr"></div> <div id="poxupih_br"></div> </div> </div></div>\',\'.poxupih_center\');
    }
}
var MyOtziv = new MyOtzivCl();
';
$_tpl['onload'] .= 'MyOtziv.mo_showframe();';
