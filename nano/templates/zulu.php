<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= $tmpl_title ?></title>
    <link rel="stylesheet" href="<?= $tmpl_gallery_root ?>css/mediaboxAdvWhite.css" type="text/css" media="screen" />
    <link rel="alternate" type="application/rss+xml" title="<?= $tmpl_title ?>" href="rss/" /><link>
    <script src="<?= $tmpl_gallery_root ?>js/mootools.js" type="text/javascript"></script>
    <script src="<?= $tmpl_gallery_root ?>js/mediaboxAdv-1.3.4b.js" type="text/javascript"></script>
<style type="text/css">
body {
    margin: 0 auto;
    padding: 0;
    width: <?= $tmpl_gallery_width ?>;
    /* font: 12px Lucida Sans Unicode, Georgia, sans-serif, Georgia, "Times New Roman", Times, serif; */
    font: 12px Lucida Sans Unicode, sans-serif; 
    background: #001;
    color: #eee;
}
h1 {
    font: normal 220%/100%;
    /* font: normal 220%/100% Georgia, Verdana, Arial, sans-serif Unicode, Georgia, "Times New Roman", Times, serif; */
    margin: 20px 0 5px 0;
    letter-spacing: 5px;
}
.credits {
    padding-bottom: 5px;
    margin: 0 0 30px 0;
    /* font: 120% Georgia, Lucida Sans Unicode, Garamond, Georgia, serif; */
    font: 120%;
}
.credits em {
    color: #999;
}
.backlink,
.backlink a {
    font-size: 10px;
    text-decoration: none;
    color: #AAA;
}
.backlink a:hover,
.backlink a:visited:hover {
    color: #555;
}
img {
    border: none;
}
#page_nav {
    color: #999;
    clear:both;
    text-align: center;
}
#page_nav a:link, #page_nav a:visited, #page_nav a:hover, #page_nav a:visited:hover {
    text-decoration: none;
    color: #eee;
}
#breadcrumb_nav {
    color: #999;
}
#breadcrumb_nav a:link, #breadcrumb_nav a:visited, #breadcrumb_nav a:hover, #breadcrumb_nav a:visited:hover {
    text-decoration: none;
    color: #eee;
}
a {
    color: #eee;
}
#container {
    overflow: auto;
    width: 100%
}
.hidden {
    visibility: hidden;
    position:absolute;
    top:0;
    left:0;
    display:inline;
}
#topbar {
    border-bottom-color: #afafaf;
    border-style: none;
    color: #001;
    position: absolute;
    left: 0;
    top: 0;
    margin: 0;
    padding-top: 5px;
    float: none;
    width: 100%;
    height: 25px;
    text-align: center;
    background-color: #FFFF99;
    border-bottom: 1px solid;
}
#topbar a:link, #topbar a:visited, #topbar a:hover, #topbar a:visited:hover {
    text-decoration: underline;
    color: #001;
}
#topbar img{
    position: absolute;
    right: 6;
    top: 6;
    vertical-align: middle;
}

#folder_comment
{
    margin-bottom:10px;
}
/* ---------- gallery styles start here ----------------------- */
.gallery {
    list-style: none;
    margin: 0;
    padding: 0;
}
.gallery li {
    padding: 1px;
    float: left;
    position: relative;

    /*
    width: 110px;
    height: 110px;
    overflow: visible;
    overflow-y: visible;
    margin: 5px;
    */

    width: <?= $tmpl_thumb_size ?>px;
    height: <?= $tmpl_thumb_size ?>px;
    overflow:hidden;

}
.gallery li:hover img {
    background: #ddd;
    filter: alpha(opacity=70);
    filter: progid:DXImageTransform.Microsoft.Alpha(opacity=70);
    -moz-opacity: 0.70;
    opacity:0.7;
}
.gallery img {
    background: #001;
    color: #666;
}
.gallery em {
    background: #001;
    color: #fff;
    font-style: normal;
    font-weight: normal;
    font-size: 12px;
    padding: 2px 2px;
    display: block;
    position: absolute;
    top: <?= $tmpl_thumb_size-30 ?>px;
    left: 1px;
    width: <?= $tmpl_thumb_size-4 ?>px;
    height: 20px;
    filter: alpha(opacity=60);
    filter: progid:DXImageTransform.Microsoft.Alpha(opacity=60);
    -moz-opacity: 0.60;
    opacity:0.6;
}
.gallery .kex {
    background: #FF9500;
    color: #FF2000;
    font-style: normal;
    font-weight: bold;
    font-size: 16px;
    text-align: right;
    padding: 5px 20px;
    display: block;
    position: absolute;
    top: 15px;
    left: 1px;
    width: 110px;
    height: 19px;
    filter: alpha(opacity=80);
    filter: progid:DXImageTransform.Microsoft.Alpha(opacity=80);
    -moz-opacity: 0.80;
    opacity:0.8;
}
.gallery em-pdf {
    color: #666;
    font-style: normal;
    font-size: 10px;
    padding: 3px 7px;
    display: block;
    position: absolute;
    top: 100px;
    left: 0px;
}
.gallery a {
    text-decoration: none;
}
.gallery a:hover em {
    background: grey;
    color: #001;
}
</style>
</head>
<body>
<h1><?= $tmpl_title ?></h1>
<?= $tmpl_messages ?>
<p class="credits"><em>by: </em><?= $tmpl_author ?></p>

<span id="breadcrumb_nav">
<?= $tmpl_breadcrumb_navigation ?>
</span>

<span id="lightbox_nav_pre">
<?= $tmpl_lightbox_nav_pre ?>
</span>

<br /><br />

<div id="container">
<div id="folder_comment"><?= $tmpl_folder_comment ?></div>
<ul class="gallery">
<?= $tmpl_thumbnails ?>
</ul>
</div>

<br />

<div id="page_nav">
<?= $tmpl_page_navigation ?>
</div>

<span id="lightbox_nav_post">
<?= $tmpl_lightbox_nav_post ?>
</span>

<br />
<!-- CREDITS - DO NOT REMOVE OR YOU WILL VOID MiniGal Nano TERMS OF USE -->
<div class="backlink" align="center">
    Powered by <a href="http://www.minigal.dk" title="MiniGal Nano" target="_blank"
            >MiniGal Nano <?= preg_replace("/\.SSE.*$/", "", $tmpl_version) ?></a><!--
-->.<a title="<?= preg_replace("/^.*\.(SSE.*?)\..*$/", "$1", $tmpl_version) ?>" href="http://sebsauvage.net/wiki/doku.php?id=minigal_nano" target="_blank"
             ><?= preg_replace("/^.*\.(SSE.*?)\..*$/", "$1", $tmpl_version) ?></a><!--
-->.<a title="nano.zulutime.net" href="http://nano.zulutime.net/" target="_blank"
             ><?= preg_replace("/^.*SSE.*?\./", "", $tmpl_version) ?></a>

</div>
<br /><br />
</body>
</html>
