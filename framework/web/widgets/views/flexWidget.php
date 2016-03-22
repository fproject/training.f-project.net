<?php
/**
 * The view file for CFlexWidget.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @package system.web.widgets.views
 * @since 1.0
 *
 */
/* @var $this CFlexWidget */
/*
 * Bui Sy Nguyen <nguyenbs@gmail.com>
 * 20130610: Replaced the widget view for displaying Flex 4 and Flash Player 11+
 * */

Yii::app()->clientScript->registerScript('widgets.flexWidget',"
    function isMSIE()
    {
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf('MSIE ');

        if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\\:11\\./))
            return true;

        return false;
    };

    window.onbeforeunload = function() {
        var warning='';
        var fxControl = document.$this->name || window.$this->name;
        if (typeof fxControl.onAppClosing=='function') {
            warning = fxControl.onAppClosing();
        }
        if (warning!='')
            return warning;
        else
            return;
    };

    if(isMSIE())
        window.onload = function() {
            var fxControl = document.$this->name || window.$this->name;
            fxControl.focus();
        };

", CClientScript::POS_HEAD);
?>
<head>
    <title></title>
    <meta name="google" value="notranslate" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- Include CSS to eliminate any default margins/padding and set the height of the html element and
         the body element to 100%, because Firefox, or any Gecko based browser, interprets percentage as
         the percentage of the height of its parent container, which has to be set explicitly.  Fix for
         Firefox 3.6 focus border issues.  Initially, don't display flashContent div so it won't show
         if JavaScript disabled.
    -->
    <style type="text/css" media="screen">
        html, body  { height:100%; }
        body { margin:0; padding:0; overflow:auto; text-align:center;
            background-color: #ffffff; }
        object:focus { outline:none; }
        #flashContent { display:none; }
    </style>

    <script type="text/javascript">
        // For version detection, set to min. required Flash Player version, or 0 (or 0.0.0), for no version detection.
        var swfVersionStr = "<?php echo $this->flashVersion; ?>";
        // To use express install, set to playerProductInstall.swf, otherwise the empty string.
        var xiSwfUrlStr = "<?php echo $this->baseUrl ?>/playerProductInstall.swf";
        var flashvars = {<?php echo $this->flashVarsAsString; ?>};
        var params = {};
        params.quality = "<?php echo $this->quality; ?>";
        params.bgcolor = "<?php echo $this->bgColor; ?>";
        params.allowscriptaccess = "<?php echo $this->allowScriptAccess ?>";
        params.allowfullscreen = "<?php echo $this->allowFullScreen ?>";
        params.allowFullScreenInteractive = "<?php echo $this->allowFullScreenInteractive ?>";
        var attributes = {};
        attributes.id = "<?php echo $this->name; ?>";
        attributes.name = "<?php echo $this->name; ?>";
        attributes.align = "<?php echo $this->align; ?>";
        attributes.margin = "0px";
        swfobject.embedSWF(
            "<?php echo $this->baseUrl ?>/<?php echo $this->name ?>.swf", "flashContent",
            "<?php echo $this->width; ?>", "<?php echo $this->height; ?>",
            swfVersionStr, xiSwfUrlStr,
            flashvars, params, attributes);
        // JavaScript enabled so display the flashContent div in case it is not replaced with a swf object.
        swfobject.createCSS("#flashContent", "display:block;text-align:left;");
    </script>
</head>
<body>
<!-- SWFObject's dynamic embed method replaces this alternative HTML content with Flash content when enough
     JavaScript and Flash plug-in support is available. The div is initially hidden so that it doesn't show
     when JavaScript is disabled.
-->
<div id="flashContent">
    <p>
        To view this page ensure that Adobe Flash Player version
        <?php echo $this->flashVersion; ?> or greater is installed.
    </p>
    <script type="text/javascript">
        var pageHost = ((document.location.protocol == "https:") ? "https://" : "http://");
        document.write("<a href='http://www.adobe.com/go/getflashplayer'><img src='"
            + pageHost + "www.adobe.com/images/shared/download_buttons/get_flash_player.gif' alt='Get Adobe Flash player' /></a>" );
    </script>
</div>

<noscript>
    <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%" id="ProjectKit">
        <param name="movie" value="<?php echo $this->baseUrl ?>/<?php echo $this->name ?>.swf" />
        <param name="quality" value="<?php echo $this->quality; ?>" />
        <param name="bgcolor" value="<?php echo $this->bgColor; ?>" />
        <param name="allowScriptAccess" value="<?php echo $this->allowScriptAccess ?>" />
        <param name="allowFullScreen" value="<?php echo $this->allowFullScreen ?>" />
        <param name="allowFullScreenInteractive" value="<?php echo $this->allowFullScreenInteractive ?>" />
        <!--[if !IE]>-->
        <object type="application/x-shockwave-flash" data="<?php echo $this->baseUrl ?>/<?php echo $this->name ?>.swf" width="100%" height="100%">
            <param name="quality" value="<?php echo $this->quality; ?>" />
            <param name="bgcolor" value="<?php echo $this->bgColor; ?>" />
            <param name="allowScriptAccess" value="<?php echo $this->allowScriptAccess ?>" />
            <param name="allowFullScreen" value="<?php echo $this->allowFullScreen ?>" />
            <param name="allowFullScreenInteractive" value="<?php echo $this->allowFullScreenInteractive ?>" />
            <!--<![endif]-->
            <!--[if gte IE 6]>-->
            <p>
                Either scripts and active content are not permitted to run or Adobe Flash Player version
                <?php echo $this->flashVersion; ?> or greater is not installed.
            </p>
            <!--<![endif]-->
            <a href="http://www.adobe.com/go/getflashplayer">
                <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash Player" />
            </a>
            <!--[if !IE]>-->
        </object>
        <!--<![endif]-->
    </object>
</noscript>
</body>