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
   - Added thumbnail cache (reduces server CPU load, server bandwith and speeds up client page display).
   - Thumbnails are now always in JPEG even if the source image is PNG or GIF.

USAGE EXAMPLE:
File: createthumb.php
Example: <img src="createthumb.php?filename=photo.jpg&amp;width=100&amp;height=100">
*/

// error_reporting(E_ALL);
error_reporting(0);

# # /*
# # if (preg_match("/.jpg$|.jpeg$/i", $_GET['filename'])) header('Content-type: image/jpeg');
# # if (preg_match("/.gif$/i", $_GET['filename'])) header('Content-type: image/gif');
# # if (preg_match("/.png$/i", $_GET['filename'])) header('Content-type: image/png');
# # */
# # 
# # if ( basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]) )
# # {
# #   echo "called directly";
# # }
# # else 
# # {
# #   echo "included/required"
# # }


#require '../test/json/tidyjson.php';
# We need to include configuration
include("config.php");

// If this file was included by anohter php file
//if ( $_GET['filename'] == "" )
if ( basename(__FILE__) != basename($_SERVER["SCRIPT_FILENAME"]) )
{
    // This function returns the link to a thumbnail of $filename.
    // If the thumbnail has already been created and cached, the function
    // returns a direct link to the cached jpg file. 
    function get_thumb_link($filename, $thumbsize=120, $square=TRUE)
    {
        // We need access the $cache_setting variable
        global $cache_setting;

        // Process size and proportion
        if ($thumbsize == "") $thumbsize=120;
        $proportion = ($square) ? 'square' : 'rect'  ;
        $squaretext = ($square) ? 'true'   : 'false' ;
            
        // This must of course match the name that gets generated below
        $thumbname = 'thumbs/'.$thumbsize.'px/'.$filename.'-'.$proportion.'.jpg';

        // If thumbnail exists (and we are using cache), serve it.
        if ( file_exists($thumbname) & ($cache_setting==CACHE_ENABLE) )
        {
            return $thumbname;
        }
        else
        {
            return "createthumb.php?filename=" . $filename . "&size=" . $thumbsize  . "&square=" . $squaretext;
        }
    }

    // This function returns an associative array containing the exif data for $filename. 
    // It also caches the data, using json, and useds the cache if it exists
    function get_exif_cached($filename)
    {
        // We need access the $cache_setting variable
        global $cache_setting;

        // Name of file to store EXIF cache
        $exif_cachefilename = 'thumbs/exif/'.$filename.'.json';

        // If thumbnail exists (and we are using cache), serve it.
        if ( file_exists($exif_cachefilename) & ($cache_setting==CACHE_ENABLE) )
        {
            $exif_json = file_get_contents($exif_cachefilename);
        }
        else
        {
            # Read exif and convert some fields to UTF-8 (Could be made better)
            $exif_data = exif_read_data ($filename, NULL ,1 );
            $exif_data['WINXP']['Title'] = mb_convert_encoding ( $exif_data['WINXP']['Title'] , 'UTF-8', 'Windows-1252' );
            $exif_data['IFD0']['Title'] = mb_convert_encoding ( $exif_data['IFD0']['Title'] , 'UTF-8', 'auto' );
            $exif_data['COMPUTED']['UserComment'] = mb_convert_encoding ( $exif_data['COMPUTED']['UserComment'] , 'UTF-8' , 'auto');
            
            $exif_json = json_encode($exif_data);
            #$exif_json = json_encode($exif_data, JSON_PRETTY_PRINT);
            #$exif_json = TidyJSON::tidy($exif_json);

            // Write the cache to a file (if cache is not disabled)
            if ( ($cache_setting==CACHE_ENABLE) | ($cache_setting==CACHE_REGENERATE) )
            {
                // Create directory structure, setting permissions so that thumbs is apache-readable
                // but thumbs/exif is not (so exif cache can not be read directly). Then create
                // the directory for this particular file. 
                if (!is_dir('thumbs'))      { mkdir('thumbs',      $thumb_permissions); }
                if (!is_dir('thumbs/exif')) { mkdir('thumbs/exif', $exif_permissions); }
                if (!is_dir(dirname($exif_cachefilename))) { mkdir(dirname($exif_cachefilename),$exif_permissions,TRUE); }
                file_put_contents($exif_cachefilename, $exif_json);
            }
        }
         return json_decode($exif_json,1);
    }

    # # Try to create thumbnail directory and report if it fails
    # if ( ($cache_setting==CACHE_ENABLE) | ($cache_setting==CACHE_REGENERATE) )
    # {
    #     if (!is_dir('thumbs'))      { mkdir('thumbs',      $thumb_permissions); }
    #     if (!is_writable('thumbs')) { $messages = "Caching enabled, but cache directory ('thumbs') can't be written to."; }     
    # }

    // If this script was included, we do nothing except define functions
    // and then return
    return;

}

// Note: Thumnails get generated relative to phpthumb.class.php
//       Therefore, it needs to be in the same directory as createthumb.php
// Note: We include this below the get_thumb_link() for performance reasons,
//       even if we are unsure that there is any performance impact
include("phpthumb.class.php"); 


# NOTE: THIS FUNCTION DOES NOT SEEM TO BE USED
function get_error_image($text, $width=120, $height=0)
{
    if ($height==0) $height = $width;

    /* Create a black image */
    $im  = imagecreatetruecolor($width, $height);
    $bgc = imagecolorallocate($im, 0, 0, 0);
    $tc  = imagecolorallocate($im, 255, 255, 255);

    imagefilledrectangle($im, 0, 0, $width, $height, $bgc);

    /* Output an error message */
    imagestring($im, 1, 5, 5, $text, $tc);
    return $im;
}

# NOTE: THESE FUNCTIONS ARE PROBABLY NO LONGER NEEDED
function str_split_php4( $text, $split = 1 ) 
{
    // place each character of the string into and array
    $array = array();
    for ( $i=0; $i < strlen( $text ); ){
        $key = NULL;
        for ( $j = 0; $j < $split; $j++, $i++ ) {
            $key .= $text[$i];
        }
        array_push( $array, $key );
    }
    return $array;
}
function sanitize($name)
{
    // Sanitize image filename (taken from http://iamcam.wordpress.com/2007/03/20/clean-file-names-using-php-preg_replace/ )
    $fname=$name;
    $replace="_";
    $pattern="/([[:alnum:]_\.-]*)/";
    $fname=str_replace(str_split_php4(preg_replace($pattern,$replace,$fname)),$replace,$fname);
    return $fname;
}

// Make sure we have the size to serve
if ($_GET['size'] == "") $_GET['size'] = 120;
$thumbsize = $_GET['size'];

// Do we want to make a square or not (square is default)
$square = TRUE;
if ( strcasecmp ( $_GET['square'] , "FALSE" )==0 ) $square=FALSE;
$proportion = 'rect';
if ($square) $proportion = 'square';

// Thumbnail file name and path. (Always jpg for simplification).
$thumbname = 'thumbs/'.$thumbsize.'px/'.$_GET['filename'].'-'.$proportion.'.jpg';

if ( file_exists($thumbname) & ($cache_setting==CACHE_ENABLE) )  
{
    // If thumbnail exists, and cache is enabled, serve it
    $fd = fopen($thumbname, "r");
    $cacheContent = fread($fd,filesize ($thumbname));
    fclose($fd);
    header('Content-type: image/jpeg');
    echo($cacheContent);
}
else 
{
    // Else, generate thumbnail, send it and save it to file (unless cache is disabled).

    // Get requested size of image
    $height     = $_GET['size'];
    $width      = $_GET['size'];

    // Display error image if file isn't found
    if ( !is_file($_GET['filename']) ) {
        header('Content-type: image/jpeg');
        $errorimage = get_error_image("Image not found", $width, $height);
        ImageJPEG($errorimage,null,90);
        return;
    }
    
    // Display error image if file isn't found
    if ( !is_readable($_GET['filename']) ) {
        header('Content-type: image/jpeg');
        $errorimage = get_error_image("Wrong permissions, not readable", $width, $height);
        ImageJPEG($errorimage,null,90);
        return;
    }

    // Load data into phpThumb object and set parameters
    $phpThumb = new phpThumb();
    $phpThumb->setSourceData(file_get_contents($_GET['filename']));
    $phpThumb->setParameter('w', $width);    // Set width
    $phpThumb->setParameter('h', $height);   // Set height
    $phpThumb->setParameter('q', 100);       // Specify quality

    if ( $square ) {
        $phpThumb->setParameter('zc', 1);        // Specify zoom-crop (for square thumb)
    }

    // Generate thumbnail and output it to browser
    $phpThumb->GenerateThumbnail();
    $phpThumb->OutputThumbnail();

    // Output thumbnail to cache file if cache is not disabled
    if ( ($cache_setting==CACHE_ENABLE) | ($cache_setting==CACHE_REGENERATE) )
    {
        // Create directory structure, setting permissions so that thumbs is apache-readable
        // This allows direct access of thumbnails, speeding up image serving, reducing resource
        // use, and allowing lightbox to to correctly preload pictures on Chrome
        // Then create the directory for this particular file. 
        if (!is_dir('thumbs')) { mkdir('thumbs',$thumb_permissions); }
        if (!is_dir(dirname($thumbname))) { mkdir(dirname($thumbname),$thumb_permissions,TRUE); }
        $phpThumb->RenderToFile("$thumbname");
    }

    // Clean up
    $phpThumb->purgeTempFiles();
}




######################################################
# 
# REMOVED CODE BELOW, KEPT ONLY FOR REFERENCE 
# 
######################################################

# The needed file permissions depend on which user php is running as, 
# so a hard-coded check for file permissions is not appropriate. 
# 
# // Display error image if file exists, but can't be opened
# if (
#     substr(decoct(fileperms($_GET['filename'])), -1, strlen(fileperms($_GET['filename']))) < 4 
#     OR substr(decoct(fileperms($_GET['filename'])), -3,1) < 4) 
# {
#     header('Content-type: image/jpeg');
#     $errorimage = ImageCreateFromJPEG('images/cannotopen.jpg');
#     ImageJPEG($errorimage,null,90);
# }

# // NOTE: THIS IS PROBABLY NOT NEEDED ANY MORE
# // Define variables
# $target = "";
# $xoord = 0;
# $yoord = 0;
# $imgsize = GetImageSize($_GET['filename']);
# $width_file  = $imgsize[0];
# $height_file = $imgsize[1];
# $width_orig  = $width_file;
# $height_orig = $height_file;
# 
# // NOTE: AND THIS IS PROBABLY NOT NEEDED EITHER
# if ( $square ) {
#     // Create square thumbnail
#     if ($width_orig > $height_orig) { // If the width is greater than the height it is a horizontal picture
#         $xoord = ceil(($width_orig-$height_orig)/2);
#         $width_orig = $height_orig;      // Then we read a square frame that  equals the width
#     } else {
#         $yoord = ceil(($height_orig-$width_orig)/2);
#         $height_orig = $width_orig;
#     }
# } else {
#     // Create rectangular (original proportions) thumbnail 
#     $ratio_orig = $width_orig/$height_orig;
# 
#     if ($width/$height > $ratio_orig) {
#        $width = $height*$ratio_orig;
#     } else {
#        $height = $width/$ratio_orig;
#     }
# }

    
?>
