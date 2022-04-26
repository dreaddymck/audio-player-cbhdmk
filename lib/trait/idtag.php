<?php

namespace DMCK_WP_MEDIA_PLUGIN;

require_once __DIR__ . '/../../vendor/james-heinrich/getid3/getid3/getid3.php';

trait _idtag {
 
	function idtag_get_image($url){
		$url = str_replace(" ", "%20", $url);
        $res = "";
		if ($fp_remote = fopen($url, 'rb')) {
			$localtempfilename = tempnam('/tmp', 'getID3');
			if ($fp_local = fopen($localtempfilename, 'wb')) {
				while ($buffer = fread($fp_remote, 8192)) {
					fwrite($fp_local, $buffer);
				}
				fclose($fp_local);
				// Initialize getID3 engine
				$getID3 = new \getID3;
				$mediainfo = $getID3->analyze($localtempfilename);
				// Delete temporary file
				unlink($localtempfilename);
				if(isset($mediainfo['comments']['picture'][0])){
					$res='data:'.$mediainfo['comments']['picture'][0]['image_mime'].';charset=utf-8;base64,'.base64_encode($mediainfo['comments']['picture'][0]['data']);
				}
			}
			fclose($fp_remote);
		}
		return $res;
    }
}