<?php

function createXML($xmlLocation){
	if (file_exists($xmlLocation)) {
		$xml = simplexml_load_file("$xmlLocation");
		$xmlObject = new SimpleXMLElement($xml->asXML());
		echo "\n" . "XML loaded"; 
	} else {
		exit('Failed to open XML file');
	}
	return $xmlObject;
}

function folderize($dir, $newDir, $downloads, $xmlStoragePath, $movieExtensions) {    
	$cdir = scandir($dir);

	foreach ($cdir as $key => $value) { 
		$x = 0;
		if (!in_array($value,array(".",".."))) { 
			//if it doesnt have a directory, create one
			if (!is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
				$filePath = $dir . DIRECTORY_SEPARATOR . $value; 
				$folderName = preg_replace('/\\.[^.\\s]{3,4}$/', '', $value);
				$newFilePath = $newDir . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR . $value;
				mkdir($newDir . DIRECTORY_SEPARATOR . $folderName);
				rename($filePath, $newFilePath);
				$x++;
			}
			if(!$downloads->xpath("/downloads/folder[@name='{$value}']")){
				$downloads->addChild("folder")->addAttribute('name', $value);
				
				$folderItems = scandir($dir . DIRECTORY_SEPARATOR . $value);
				$thisNode = $downloads->xpath("/downloads/folder[@name='{$value}']");
				$thisNode[0]->addChild('video', '');
				foreach ($folderItems as $k => $v){
					$ext = pathinfo($v, PATHINFO_EXTENSION);
					if ($ext !== '.' && $ext !== '..' && $ext !== ''){
						if(strpos($movieExtensions, $ext) !== false){
							$thisNode[0]->video = 'true';
						} elseif($thisNode[0]->video != 'true') {
							$thisNode[0]->video = 'false';
						} 
					} 
				}
				$downloads->asXML("{$xmlStoragePath}"); 
			}
  		} 
	}
	return $downloads;
	echo "Moved " . $x . " files into into their own folder" ;   
} 

function formatXML($xml, $xmlStoragePath){
	$dom = new DOMDocument("1.0");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($xml->asXML());
	$dom->save($xmlStoragePath);
}

$sourcePath = 'E:\4. movietimestestfolder';
$destinationPath = 'E:\5. Processed Video'; 
$xmlStoragePath = 'storage.xml';
$movieExtensions = 'mkv, avi, mp4, wmv, mov';

$xml = createXML($xmlStoragePath);
$result = folderize($sourcePath, $sourcePath, $xml, $xmlStoragePath, $movieExtensions); 
formatXML($result, $xmlStoragePath);


