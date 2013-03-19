<?php

# This is a command line php script
if ( php_sapi_name() != "cli" ) 
{
    die("This script may only be run from the command line");
}

# Include index.php to determine the version
include("index.php");
echo "Building MiniGal nano (Zulu flavor) $version\n";

# Define the other release variables
$release_dir    = "../nano-releases";
$release_file   = "MiniGal-nano-Zulu-$version.tar.gz";
$exclusions     = "--exclude='./photos/*' "
                . "--exclude='./photos-unpublished' "
                . "--exclude='./thumbs' "
                . "--exclude='./thumbs-unpublished' "
                . "--exclude='*/.svn' "
                . "--exclude='*.tgz' "
                . "--exclude='*.tar.gz' "
                . "--exclude='*.swp' "
                ;

# Create release directory if it doesn't exist
if ( !is_dir($release_dir) && !mkdir($release_dir, 0700, TRUE) )
{
    die("Release dir not found, and could not create it ($release_dir)\n");
}

# Refuese to overwrite/append to existing release file
if ( file_exists("$release_dir/$release_file" ) )
{
    die("Output file already exists ($release_dir/$release_file)\n");
}


$out = array();

# Package main components
$command = "tar cvfz $release_dir/intermediate.tar.gz --transform='s/^./nano/' $exclusions . | sort" ;
exec($command, $out);

# Postprocessing
exec("(cd $release_dir && tar xvfzp intermediate.tar.gz && rm intermediate.tar.gz)", $out);
exec("(cd $release_dir && find nano -type d -print0 | xargs -r -0 chmod 711)", $out);
exec("(cd $release_dir && find nano -type f -print0 | xargs -r -0 chmod 744)", $out);
exec("(cd $release_dir && tar cvfzp $release_file --remove-files nano )", $out);


foreach ( $out as $line )
{
    echo $line . "\n";
}
#var_dump($out);



