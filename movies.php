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

function formatXML($xml, $xmlStoragePath){
	$dom = new DOMDocument("1.0");
	$dom->preserveWhiteSpace = false;
	$dom->formatOutput = true;
	$dom->loadXML($xml->asXML());
	$dom->save($xmlStoragePath);
}

function addNodeAB($keyA, $keyB, $childName, $array, $xmlNode){
	if ( !empty($array[$keyA]) || !empty($array[$keyB]) ){
		if ( !empty($array[$keyA])){
			$xmlNode[0]->addChild($childName, (string)$array[$keyA]);
		} else {
			$xmlNode[0]->addChild($childName, (string)$array[$keyB]);	
		}
	}
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
							$re = '/^( 
								(?P<showNameA>.*[^ (_.]) # Show name
								    [ (_.]+
								    ( # Year with possible Season and Episode
								      (?P<showYearA>\d{4}(?!p))
								      ([ (_.]+S(?P<seasonA>\d{1,2})E(?P<episodeA>\d{1,2}))?
								    | # Season and Episode only
								      (?<!\d{4}[ (_.])
								      S(?P<seasonB>\d{1,2})E(?P<episodeB>\d{1,2})
								    )
								|
								  # Show name with no other information
								  (?P<showNameB>.+)
								)/mx';
								    // | # Alternate format for episode
								    //   (?P<episodeC>\d{3}[^720p|1080p])
							$value = str_replace('.', ' ', $value);
							preg_match($re, $value, $matches);

							addNodeAB('showNameA', 'showNameB', 'show-name', $matches, $thisNode);
							if ( !empty($matches['showYearA']) ){
								$thisNode[0]->addChild('show-year', (string)$matches['showYearA']);
							}
							addNodeAB('seasonA', 'seasonB', 'season', $matches, $thisNode);
							addNodeAB('episodeA', 'episodeB', 'episode', $matches, $thisNode);
							
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

$sourcePath = 'E:\4. movietimestestfolder';
$destinationPath = 'E:\5. Processed Video'; 
$xmlStoragePath = 'storage.xml';
$movieExtensions = 'mkv, avi, mp4, wmv, mov';

$xml = createXML($xmlStoragePath);
$result = folderize($sourcePath, $sourcePath, $xml, $xmlStoragePath, $movieExtensions); 
formatXML($result, $xmlStoragePath);


