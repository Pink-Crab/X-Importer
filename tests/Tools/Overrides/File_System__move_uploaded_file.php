<?php 
/**
 * Over rides the core PHP move_uploaded_file function when called in the File_System
 * 
 * Used for testing only.
 */
namespace PinkCrab\X_Importer\File_System;

function move_uploaded_file($src, $dest){
    return copy($src, $dest);
}