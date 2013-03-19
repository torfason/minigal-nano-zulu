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

*/

if ( !defined("CONFIG_CONSTANTS") )
{
    define("CACHE_ENABLE",     1);
    define("CACHE_DISABLE",    2);
    define("CACHE_REGENERATE", 3);

    define("CONFIG_CONSTANTS", TRUE);
}

// EDIT SETTINGS BELOW TO CUSTOMIZE YOUR GALLERY
$thumbs_pr_page    = "18";           // Number of thumbnails on a single page
$gallery_width     = "920px";        // Gallery width. Eg: "500px" or "70%"
$backgroundcolor   = "white";        // This provides a quick way to change your gallerys background to suit your website. Use either main colors like "black", "white", "yellow" etc. Or HEX colors, eg. " #AAAAAA"
$templatefile      = "zulu";         // Template filename (must be placed in 'templates' folder) (exhhibition/zulutime)
$title             = "My Gallery";   // Text to be displayed in browser titlebar
$author            = "Me :)";        // Pretty obvious :)
$folder_color      = "black";        // Color of folder icons: blue / black / vista / purple / green / grey
$sorting_folders   = "name";         // Sort folders by: [name][date]
$sorting_files     = "date";         // Sort files by: [name][date][size]
$sortdir_folders   = "ASC";          // Sort direction of folders: [ASC][DESC]
$sortdir_files     = "ASC";          // Sort direction of files: [ASC][DESC]

// LANGUAGE STRINGS
$label_home        = "Home";         // Name of home link in breadcrumb navigation
$label_new         = "New";          // Text to display for new images. Use with $display_new variable
$label_page        = "Page";         // Text used for page navigation
$label_all         = "All";          // Text used for link to display all images in one page
$label_noimages    = "No images";    // Empty folder text
$label_loading     = "Loading...";   // Thumbnail loading text

// CACHE SETTINGS
$cache_setting     = CACHE_ENABLE;   // [CACHE_ENABLE][CACHE_DISABLE][CACHE_REGENERATE]
$thumb_permissions = 0711;           // File permissions for thumbnail cache (this gets written by php; read by apache)
$exif_permissions  = 0700;           // File permissions for exif cache (written and read by php)

//HIGHLIGHT NEW FOLDERS
$new_folder_maxage = 24*60*60 * 14;  // Show overlay for all folders younger than $new_folder_age seconds old (zero disables this)
$new_folder_text   = "N E W !";      // What text do we want in the new folder overlay

// ADVANCED SETTINGS
$lightbox_pic_size = 800;                   // Change the size of the picture served by the lightbox (mediaboxAdvanced). This only affects maximum size, since lighbox will scale down further as needed.
$thumb_size        = 150;                   // Thumbnail height/width (square thumbs). Changing this will most likely require manual altering of the template file to make it look properly! 
$label_max_length  = 18;                    // Maximum chars of a folder name that will be displayed on the folder thumbnail  
$exclude_pattern   = "/(_gsdata_|\.svn)/";  // Regular expression pattern matching any files/folders to exclude from the album
$display_exif      = 1;                     // Do we want to display exif data in lightbox?

?>
