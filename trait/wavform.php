<?php

require_once __DIR__ . '/../vendor/autoload.php';

use maximal\audio\Waveform;

trait _wavform {
    
    function __construct(){} 
    function wavform(){
        $name	= isset($_SERVER["argv"][3]) && $_SERVER["argv"][3] ? htmlspecialchars( $_SERVER["argv"][3] ) : "";
        $folder   = isset($_SERVER["argv"][2]) && $_SERVER["argv"][2] ? $_SERVER["argv"][2] : "";
        if(!$folder){
            exit("Missing parameter: '/path/to/folder/' ");
        }

        function do_wavform($fileInfo){
            $pathname = $fileInfo->getPathname(); 
            $basename = $fileInfo->getBasename(".mp3");
            $path = $fileInfo->getPath();
            $png = 	$path.'/'.$basename.'.wavform.png';  
            $waveform = new Waveform($pathname);
            Waveform::$color = [95, 95, 95, 0.5];
            Waveform::$backgroundColor = [0, 0, 0, 0];					
            $success = $waveform->getWaveform( $png, 1200, 600);
            if($success){
                var_dump("Writing: ".$path.'/'.$basename.'.wavform.png');
            }				
        }
        if($folder){
            foreach (new DirectoryIterator($folder) as $fileInfo) {				
                if($fileInfo->isDir() && !$fileInfo->isDot()) {
                    // Do whatever
                    continue;
                }
                // var_dump($fileInfo->getBasename(".mp3"));
                if($fileInfo->getExtension() == "mp3"){
                    if($name){
                        // var_dump($name . "  ".$fileInfo->getBasename());
                        if($name == $fileInfo->getBasename()){
                            do_wavform($fileInfo);
                        }
                        continue;
                    }else{
                        do_wavform($fileInfo);
                    }
                }
            }				
            return;
        }
    }
}