<?php
/*
MINIGAL NANO
- A PHP/HTML/CSS based image gallery script

This script and included files are subject to licensing from Creative Commons (http://creativecommons.org/licenses/by-sa/2.5/)
You may use, edit and redistribute this script, as long as you pay tribute to the original author by NOT removing the linkback to www.minigal.dk ("Powered by MiniGal Nano x.x.x")

MiniGal Nano is created by Thomas Rybak

Copyright 2010 by Thomas Rybak
Support: www.minigal.dk
Community: www.minigal.dk/forum

Please enjoy this free script!


Version 0.3.5 modified by Sebastien SAUVAGE (sebsauvage.net):
  - Disabled new version check (problems on some servers)
  - Disabled error reporting
  - Added gallery comment (create comment.html in each directory)
  - security update against XSS
  
Version 0.3.5 with further modifications by Magnus Thor Torfason (zulutime.net):
  - (Changes made 2012-10)
  - Added dynamic, cached, resizing to lightbox version of pictures (not just thumbnails)
  - Changed thumbnail generation to use phpThumb, which allows for better handling of
    large photos, as well as files with minor errors/problems. Using phpThumb for the 
    actual thumbnail generation also simplifies the code in createthumb.php considerably.
  - Added setting to configure the lightbox pic size ($lightbox_pic_size)
  - Extended createthumb.php to allow proportional scaling (not just square), based on url parameter,
    this is used for the dynamic cached resizing of the lightbox version.
  - Modified createthumb.php to cache copies in separate folder based on location and size,
    to limit the number of cache files that end up in a single folder.
  - Added function getThumbLink() which returns direct link to cached images rather than 
    spooling them through a php file, which lowers resource usage and greatly improves lightbox
    preloading on Chrome (and potentially other browsers).
  - Added slideshow option to the lightbox. Great to view photos at your leasure or to trigger
    caching of all photos in your album.
  - Improved EXIF usage to display EXIF captions and date taken in the lightbox.  
  - When sorting images by date, this version uses the EXIF date taken, rather than
    the date when the file was modified.
  - Added option to exclude certain files or folders from album according to regular expression.
  - Changing thumb_size in config.php now correctly changes the layout of the template
    (for the 'zulu' template at least)
  - Converted templating to php instead of find/replace
  - Added the option to display a banner on top of folder thumbnails for recently
    changed folders (text and age at which banner disappears is configurable).
  - Cached EXIF data to speed load times
  - Updated mediaboxAdvanced to preload two images ahead rather than one, to speed 
    load times when user want to go quickly past an image.
  - Improved caching options to allow three settings: ENABLE, DISABLE, and REGENERATE

*/

// ==================================================================
// Do not edit below this section unless you know what you are doing!
// ==================================================================

//----------------------------
// DEFINE VERSION (AND QUIT IF CLI)
//----------------------------
$version = "0.3.5.SSE2.Zulu.4";
if ( php_sapi_name() == "cli" ) return;

//----------------------------
// DEBUG CODE
//----------------------------
// error_reporting(E_ERROR);
// error_reporting(E_ALL);
error_reporting(0);
$mtime     = microtime();
$mtime     = explode(" ",$mtime);
$mtime     = $mtime[1] + $mtime[0];
$starttime = $mtime;

//----------------------------
// SET MEMORY
//----------------------------
ini_set("memory_limit","256M");

//----------------------------
// INCLUDE CONFIGURATION FILES
//----------------------------
require("config_default.php");
include("config.php");

//----------------------------
// INCLUDE:   createhumb.php 
// TO DEFINE: 
//  - get_thumb_link()
//  - get_exif_cached()
//----------------------------
include("createthumb.php");

//----------------------------
// DEFINE VARIABLES
//----------------------------
$breadcrumb_navigation = "";
$page_navigation       = "";
$lighbox_nav_pre       = "";
$lighbox_nav_post      = "";
$thumbnails            = "";
$new                   = "";
$images                = "";
$exif_data             = "";
$messages              = "";
$comment               = "";

//----------------------------
// PHP ENVIRONMENT CHECK
//----------------------------
if (!function_exists('exif_read_data') && $display_exif == 1) 
{
    $display_exif = 0;
    $messages = "Error: PHP EXIF is not available. Set &#36;display_exif = 0; in config.php to remove this message";
}


//----------------------------
// FUNCTIONS
//----------------------------
function padstring($name, $length) 
{
    global $label_max_length;
    if (!isset($length)) $length = $label_max_length;
    if (strlen($name) > $length) 
    {
        return substr($name,0,$length) . "...";
    } 
    else return $name;
}

function getfirstImage($dirname) 
{
    $imageName = false;
    $ext = array("jpg", "png", "jpeg", "gif", "JPG", "PNG", "GIF", "JPEG");
    if($handle = opendir($dirname))
    {
        while(false !== ($file = readdir($handle)))
        {
            $lastdot = strrpos($file, '.');
            $extension = substr($file, $lastdot + 1);
            if ($file[0] != '.' && in_array($extension, $ext)) break;
        }
        $imageName = $file;
        closedir($handle);
    }
    return($imageName);
}

function readEXIF($file) 
{

    // // Many examples available here:
    // // http://www.php.net/manual/en/function.exif-read-data.php
    //
    // // Another example date handling code
    // $capture_date = $exif_result['SubIFD']['DateTimeOriginal']; // Date and Time
    // $xdate = explode(':', str_replace(" ",":",$capture_date));
    // $capture_date = date($cfgrow['dateformat'], mktime($xdate[3],$xdate[4],$xdate[5],$xdate[1],$xdate[2],$xdate[0]));

    $exif_data = get_exif_cached($file);
    $exif_ifd0  = $exif_data['IFD0'];
    $exif_exif  = $exif_data['EXIF'];
    $exif_winxp = $exif_data['WINXP'];

    $emodel     = $exif_ifd0['Model'];
    $efocal     = $exif_ifd0['FocalLength'];
    list($x,$y) = split('/', $efocal);
    $efocal     = round($x/$y,0);

    $edate_org  = preg_replace("/:/", "-", $exif_exif['DateTimeOriginal'] , 2); 
    $edate_mod  = preg_replace("/:/", "-", $exif_exif['DateTime']         , 2);
    $edate_file = date ("Y-m-d H:i:s", filemtime($file));

    $eiso          = $exif_exif['ISOSpeedRatings'];
    $eexposuretime = $exif_exif['ExposureTime'];
    $efnumber      = $exif_exif['FNumber'];
    list($x,$y)    = split('/', $efnumber);
    $efnumber      = round($x/$y,0);

    $etitle        = $exif_winxp['Title'];

    $result = '';
    if (strlen($etitle) > 0) $result .= $etitle . "&#10;";
    if (strlen($edate_org) > 0 OR strlen($emodel) > 0 OR strlen($efocal) > 0 OR strlen($eexposuretime) > 0 OR strlen($efnumber) > 0 OR strlen($eiso) > 0) $result .= ":: [";
    if (strlen($edate_org)     > 0) $result .= " $edate_org";
    if (strlen($emodel)        > 0) $result .= " | $emodel";
    if ($efocal                > 0) $result .= " | $efocal" . "mm";
    if (strlen($eexposuretime) > 0) $result .= " | $eexposuretime" . "s";
    if ($efnumber              > 0) $result .= " | f$efnumber";
    if (strlen($eiso)          > 0) $result .= " | ISO $eiso";
    if (strlen($edate_org) > 0 OR strlen($emodel) > 0 OR strlen($efocal) > 0 OR strlen($eexposuretime) > 0 OR strlen($efnumber) > 0 OR strlen($eiso) > 0) $result .= " ]";
    //if (strlen($edate_mod)     > 0) $result .= "\n   [ $edate_mod | $edate_file ]";

    // This is a bit of debugging, to see if Title is always duplicated in ImageDescription
    if ( strlen( $exif_ifd0['ImageDescription'] ) > 0 ) $result .= " [" . $exif_ifd0['ImageDescription'] .  "]";

    return($result);
}

// This function returs a usable date for a file. If an EXIF (IFD0) date is
// available, it uses that, otherwise it returns the file modified time.
function readDateEXIF($file)
{

    $exif_data = get_exif_cached($file);
    $exif_exif  = $exif_data['EXIF'];

    $edate_org = preg_replace("/:/", "-", $exif_exif['DateTimeOriginal'] , 2); 
    $edate_mod = preg_replace("/:/", "-", $exif_exif['DateTime']         , 2);
    if (strlen($edate_org)  > 0) 
        return($edate_org);
    else if (strlen($edate_mod)  > 0) 
        return($edate_mod);
    else
        return(filemtime($file));
}



//----------------------------
// DETERMINE FOLDER STRUCTURE 
//----------------------------
if (!defined("GALLERY_ROOT")) define("GALLERY_ROOT", "");
$photodir   = rtrim('photos' . "/" .$_REQUEST["dir"],"/");
$photodir   = str_replace("/..", "", $photodir); // Prevent looking at any up-level folders
$currentdir = GALLERY_ROOT . $photodir;

//----------------------------
// READ FILES AND FOLDERS
//----------------------------
$files = array();
$dirs = array();
if ($handle = opendir($currentdir))
{
    // LOAD FOLDER COMMENT FROM TEXT FILE (IF AVAILABLE)
    $comment_filepath = $currentdir . "/comment.html";
    if (file_exists($comment_filepath))
    {
        $fd = fopen($comment_filepath, "r");
        $comment = utf8_encode(fread($fd,filesize ($comment_filepath))); // utf8_encode to convert from iso-8859 to UTF-8
        fclose($fd);
    }

    // LOAD PHOTO CAPTIONS FROM TEXT FILE (IF AVAILABLE)
    if ( file_exists($currentdir ."/captions.txt") )
    {
        $file_handle = fopen($currentdir ."/captions.txt", "rb");
        while (!feof($file_handle) ) 
        {
            $line_of_text = fgets($file_handle);
            $parts = explode('/n', $line_of_text);
            foreach($parts as $img_capts)
            {
                list($img_filename, $img_caption) = explode('|', $img_capts);
                $img_captions[$img_filename] = $img_caption;
            }
        }
        fclose($file_handle);
    }

    while (false !== ($file = readdir($handle)))
    {

        if ( is_dir($currentdir . "/" . $file) )
        { 
            //----------------------------
            // 1. LOAD FOLDERS
            //----------------------------
            if ( !preg_match("/^\./", $file) && !preg_match("$exclude_pattern", $file))
            {
                //checkpermissions($currentdir . "/" . $file); // Check for correct file permission
                $folder_mtime    = filemtime($currentdir . "/" . $file);
                $new_folder_html = "";
                if ( ($new_folder_maxage != 0) & ($folder_mtime > time()-$new_folder_maxage) )
                    $new_folder_html = "<span class='kex'>" . $new_folder_text . "</span>";

                if (file_exists("$currentdir/" . $file . "/folder.jpg"))
                {
                    // Set thumbnail to folder.jpg if found:
                    $folder_thumb_link = get_thumb_link( $currentdir . "/" . $file . "/" . "folder.jpg", $thumb_size, TRUE);
                    
                }  
                else
                {
                    // Set thumbnail to first image found (if any):
                    unset ($firstimage);
                    $firstimage = getfirstImage("$currentdir/" . $file);
                    if ($firstimage != "") 
                    {
                        // If we find an image, then we use that
                        $folder_link_thumbnail = get_thumb_link( $currentdir . "/" . $file . "/" . $firstimage, $thumb_size, TRUE);

                    } 
                    else 
                    {
                        // If no folder.jpg or image is found, then display default icon:
                        $folder_link_thumbnail = GALLERY_ROOT . "images/folder_" . strtolower($folder_color) . ".png";
                    }
                }

                $dirs[] = array(
                    "name" => $file,
                    "date" => $folder_mtime,
                    "link_thumbnail" => $folder_link_thumbnail, 
                    "html" => "   <li><a href='?dir=" . ltrim($_GET['dir'] . "/" . $file, "/") . "' title='". $file . "'>" 
                    . "<em>" . padstring($file, $label_max_length) . "</em>"
                    . $new_folder_html
                    . "<img src='" . $folder_link_thumbnail . "' width='$thumb_size' height='$thumb_size' " 
                    . " alt='$label_loading' /></a></li>\n");
            }
        }
        else if ( !preg_match("/^\./", $file) && !preg_match("$exclude_pattern", $file) && $file != "comment.html" && $file != "captions.txt" && $file != "folder.jpg" )
        {
            //----------------------------
            // 2. LOAD FILES (PHOTOS)
            //----------------------------
            if (preg_match("/.jpg$|.gif$|.png$/i", $file))
            {
                // JPG, GIF and PNG

                // Read EXIF and append to any caption from captions.txt
                if ($display_exif == 1) $img_captions[$file] .= readEXIF($currentdir . "/" . $file);

                $link_thumbnail = get_thumb_link( $currentdir . "/" . $file, $thumb_size, TRUE);
                $link_lightbox  = get_thumb_link( $currentdir . "/" . $file, $lightbox_pic_size, FALSE);

                $files[] = array (
                    "name"           => $file,
                    "date"           => readDateEXIF($currentdir . "/" . $file), 
                    "size"           => filesize($currentdir . "/" . $file),
                    "link_thumbnail" => $link_thumbnail, 
                    "link_lightbox"  => $link_lightbox, 
                    "html" => "    <li><a href='" . $link_lightbox . "' "
                            . "rel='lightbox[billeder]' title='" . $img_captions[$file] . "'><span></span>" 
                            . "<img src='" . $link_thumbnail . "' "
                            . "alt='$label_loading' /></a></li>\n");
            }
            else
            {
                // Other filetypes
                $extension = "";
                if (preg_match("/.html/i", $file)) $extension = "HTML"; // PDF
                if (preg_match("/.pdf$/i", $file)) $extension = "PDF"; // PDF
                if (preg_match("/.zip$/i", $file)) $extension = "ZIP"; // ZIP archive
                if (preg_match("/.rar$|.r[0-9]{2,}/i", $file)) $extension = "RAR"; // RAR Archive
                if (preg_match("/.tar$/i", $file)) $extension = "TAR"; // TARball archive
                if (preg_match("/.gz$/i", $file)) $extension = "GZ"; // GZip archive
                if (preg_match("/.doc$|.docx$/i", $file)) $extension = "DOCX"; // Word
                if (preg_match("/.ppt$|.pptx$/i", $file)) $extension = "PPTX"; //Powerpoint
                if (preg_match("/.xls$|.xlsx$/i", $file)) $extension = "XLXS"; // Excel
                                    
                if ($extension != "")
                {
                    $files[] = array (
                        "name" => $file,
                        "date" => filemtime($currentdir . "/" . $file),
                        "size" => filesize($currentdir . "/" . $file),
                        "html" => "<li><a href='" . $currentdir . "/" . $file . "' title='$file'><em-pdf>" . padstring($file, 20) . "</em-pdf><span></span><img src='" . GALLERY_ROOT . "images/filetype_" . $extension . ".png' width='$thumb_size' height='$thumb_size' alt='$file' /></a></li>");
                }
            }
        }
    }
    closedir($handle);
} 
else die("ERROR: Could not open ".htmlspecialchars(stripslashes($currentdir))." for reading!");



//----------------------------
// SORT FILES AND FOLDERS
//----------------------------
if (sizeof($dirs) > 0) 
{
    foreach ($dirs as $key => $row)
    {
        if($row["name"] == "") unset($dirs[$key]); //Delete empty array entries
        $name[$key] = strtolower($row['name']);
        $date[$key] = strtolower($row['date']);
    }
    if (strtoupper($sortdir_folders) == "DESC") array_multisort($$sorting_folders, SORT_DESC, $name, SORT_DESC, $dirs);
    else array_multisort($$sorting_folders, SORT_ASC, $name, SORT_ASC, $dirs);
}
if (sizeof($files) > 0)
{
    foreach ($files as $key => $row)
    {
        if($row["name"] == "") unset($files[$key]); //Delete empty array entries
        $name[$key] = strtolower($row['name']);
        $date[$key] = strtolower($row['date']);
        $size[$key] = strtolower($row['size']);
    }
    if (strtoupper($sortdir_files) == "DESC") array_multisort($$sorting_files, SORT_DESC, $name, SORT_ASC, $files);
    else array_multisort($$sorting_files, SORT_ASC, $name, SORT_ASC, $files);
}

//----------------------------
// OFFSET DETERMINATION
//----------------------------
$offset_start = ($_GET["page"] * $thumbs_pr_page) - $thumbs_pr_page;
if (!isset($_GET["page"])) $offset_start = 0;
$offset_end = $offset_start + $thumbs_pr_page;
if ($offset_end > sizeof($dirs) + sizeof($files)) $offset_end = sizeof($dirs) + sizeof($files);

if ($_GET["page"] == "all")
{
    $offset_start = 0;
    $offset_end = sizeof($dirs) + sizeof($files);
}

//----------------------------
// PAGE NAVIGATION
//----------------------------
if (!isset($_GET["page"])) $_GET["page"] = 1;
if (sizeof($dirs) + sizeof($files) > $thumbs_pr_page)
{
    $page_navigation .= "$label_page ";
    for ($i=1; $i <= ceil((sizeof($files) + sizeof($dirs)) / $thumbs_pr_page); $i++)
    {
        if ($_GET["page"] == $i)
            $page_navigation .= "$i";
        else
            $page_navigation .= "<a href='?dir=" . $_GET["dir"] . "&page=" . ($i) . "'>" . $i . "</a>";
        if ($i != ceil((sizeof($files) + sizeof($dirs)) / $thumbs_pr_page)) 
            $page_navigation .= " |\n ";
    }
    //Insert link to view all images
    if ($_GET["page"] == "all") 
        $page_navigation .= " |\n $label_all\n";
    else 
        $page_navigation .= " |\n <a href='?dir=" . $_GET["dir"] . "&page=all'>$label_all</a>\n";
}

//----------------------------
// BREADCRUMB NAVIGATION
//----------------------------
if ($_GET['dir'] != "")
{
    $breadcrumb_navigation .= "    <a href='?dir='>" . $label_home . "</a> >\n";
    $navitems = explode("/", $_REQUEST['dir']);
    for($i = 0; $i < sizeof($navitems); $i++)
    {
        if ($i == sizeof($navitems)-1) 
        {
            $breadcrumb_navigation .= "    " . $navitems[$i] . "\n";
        }
        else
        {
            $breadcrumb_navigation .= "    <a href='?dir=";
            for ($x = 0; $x <= $i; $x++)
            {
                $breadcrumb_navigation .= $navitems[$x];
                if ($x < $i) $breadcrumb_navigation .= "/";
            }
            $breadcrumb_navigation .= "'>" . $navitems[$i] . "</a> > \n";
        }
    }
} else $breadcrumb_navigation .= $label_home;

//----------------------------
// DISPLAY FOLDERS
//----------------------------
if (count($dirs) + count($files) == 0) 
{
    $thumbnails .= "<li>$label_noimages</li>"; //Display 'no images' text
    if($currentdir == "photos") $messages = "It looks like you have just installed MiniGal Nano. Please run the <a href='system_check.php'>system check tool</a>";
}
$offset_current = $offset_start;
for ($x = $offset_start; $x < sizeof($dirs) && $x < $offset_end; $x++)
{
    $offset_current++;
    $thumbnails .= $dirs[$x]["html"];
}

//----------------------------
// LIGHTBOX NAVIGATION (PRE)
//----------------------------
//Include hidden links for all images BEFORE current page so lightbox is able to browse images on different pages
for ($y = 0; $y < $offset_start - sizeof($dirs); $y++)
{
    $lightbox_nav_pre  .= "   <a href='" . $files[$y]["link_lightbox"] . "' " 
                        . "rel='lightbox[billeder]' class='hidden' title='" . $img_captions[$files[$y]["name"]] . "'></a>\n";
}

//----------------------------
// DISPLAY FILES
//----------------------------
for ($i = max(0,$offset_start-sizeof($dirs));     $i < $offset_end && $offset_current < $offset_end; $i++)
{
    $offset_current++;
    $thumbnails .= $files[$i]["html"];
}

//----------------------------
// LIGHTBOX NAVIGATION (POST)
//----------------------------
//Include hidden links for all images AFTER current page so lightbox is able to browse images on different pages
for ($y = $i; $y < sizeof($files); $y++)
{
    $lightbox_nav_post .= "   <a href='" . $files[$y]["link_lightbox"] . "' " 
                        . "rel='lightbox[billeder]'  class='hidden' title='" . $img_captions[$files[$y]["name"]] . "'></a>\n";
}

//----------------------------
// OUTPUT MESSAGES
//----------------------------
if ($messages != "") {
    $messages = "<div id=\"topbar\">" . $messages . " <a href=\"#\" onclick=\"document.getElementById('topbar').style.display = 'none';\";><img src=\"images/close.png\" /></a></div>";
}


# echo "<!-- \n\n $XIF  \n\n -->";
# require '../test/json/tidyjson.php';
# $tidy = TidyJSON::tidy($XIF);
# echo "<!-- \n\n $tidy  \n\n -->";


if (GALLERY_ROOT == "") 
{
    // Normal template file
    $templatefile = "templates/" . $templatefile . ".php";

    $tmpl_title                 = $title;
    $tmpl_messages              = $messages;
    $tmpl_author                = $author;
    $tmpl_gallery_root          = $gallery_root;
    $tmpl_images                = $images;
    $tmpl_thumbnails            = $thumbnails;
    $tmpl_thumb_size            = $thumb_size;
    $tmpl_breadcrumb_navigation = $breadcrumb_navigation;
    $tmpl_page_navigation       = $page_navigation;
    $tmpl_lightbox_nav_pre      = $lightbox_nav_pre;
    $tmpl_lightbox_nav_post     = $lightbox_nav_post;
    $tmpl_folder_comment        = $folder_comment;
    $tmpl_bgcolor               = $bgcolor;
    $tmpl_gallery_width         = $gallery_width;
    $tmpl_version               = $version;

    include($templatefile);
}
else
{
    // Special "integrate" template (should also be php-ized)
    $templatefile = GALLERY_ROOT . "templates/integrate.html";

    //PROCESS TEMPLATE FILE
    if(!$fd = fopen($templatefile, "r"))
    {
        echo "Template ".htmlspecialchars(stripslashes($templatefile))." not found!";
        exit();
    }
    else
    {
        $template = fread ($fd, filesize ($templatefile));
        fclose ($fd);
        $template = stripslashes($template);
        $template = preg_replace("/<% title %>/", $title, $template);
        $template = preg_replace("/<% messages %>/", $messages, $template);
        $template = preg_replace("/<% author %>/", $author, $template);
        $template = preg_replace("/<% gallery_root %>/", GALLERY_ROOT, $template);
        $template = preg_replace("/<% images %>/", "$images", $template);
        $template = preg_replace("/<% thumbnails %>/", "$thumbnails", $template);
        $template = preg_replace("/<% thumb_size %>/", "$thumb_size", $template);
        $template = preg_replace("/<% breadcrumb_navigation %>/", "$breadcrumb_navigation $lightbox_nav_pre" , $template);
        $template = preg_replace("/<% page_navigation %>/", "$page_navigation $lightbox_nav_post", $template);
        $template = preg_replace("/<% folder_comment %>/", "$comment", $template);
        $template = preg_replace("/<% bgcolor %>/", "$backgroundcolor", $template);
        $template = preg_replace("/<% gallery_width %>/", "$gallery_width", $template);
        $template = preg_replace("/<% version %>/", "$version", $template);
        echo "$template";
    }
}

//----------------------------
// DEBUG CODE
//----------------------------
$mtime     = microtime();
$mtime     = explode(" ",$mtime);
$mtime     = $mtime[1] + $mtime[0];
$endtime   = $mtime;
$totaltime = ($endtime - $starttime);
echo "<!-- This page was created in ".$totaltime." seconds -->\n";


   
######################################################
# 
# REMOVED FUNCTIONS BELOW, KEPT ONLY FOR REFERENCE 
# 
######################################################

# //----------------------------
# // NOT CLEAR WHY THIS FUNCTION WAS USED,
# // SWITCHED TO NATIVE is_dir() INSTEAD
# //----------------------------
# function is_directory($filepath) 
# {
#     // NOTE: Why not use native is_dir() function?
#     // $filepath must be the entire system path to the file
#     if (!@opendir($filepath)) return FALSE;
#     else 
#     {
#         // This returns before closing directory!
#         return TRUE;
#         closedir($filepath);
#     }
# }

# //----------------------------
# // PERMISSION CHECK DISABLED 
# // (CORRECT PERMISSIONS DEPEND ON SERVER USER STRUCTURE)
# //----------------------------
# function checkpermissions($file) 
# {
#     global $messages;
#     if (substr(decoct(fileperms($file)), -1, strlen(fileperms($file))) < 4 OR substr(decoct(fileperms($file)), -3,1) < 4) 
#     {
#         //$messages =   "At least one file or folder has wrong permissions. Learn how to " 
#         //            . "<a href='http://minigal.dk/faq-reader/items/how-do-i-change-file-permissions-chmod.html' target='_blank'>set file permissions</a>";
#     }
# }

# //----------------------------
# // VERSION CHECK DISABLED
# //----------------------------
# if (ini_get('allow_url_fopen') == "1") 
# {
#     $file = @fopen ("http://www.minigal.dk/minigalnano_version.php", "r");
#     $server_version = fgets ($file, 1024);
#     if (strlen($server_version) == 5 ) 
#     { 
#         //If string retrieved is exactly 5 chars then continue
#         if (version_compare($server_version, $version, '>')) 
#         {
#             $messages = "MiniGal Nano $server_version is available! <a href='http://www.minigal.dk/minigal-nano.html' target='_blank'>Get it now</a>";
#         }
#     }
#     fclose($file);
# }
?>

