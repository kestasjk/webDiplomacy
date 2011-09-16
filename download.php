<?php

require_once('header.php');

$download = (isset($_REQUEST['download'])) ? $_REQUEST['download'] : '';    // Display the list or a downloadlink.?

if ($download == '') {
	libHTML::starthtml();

	print libHTML::pageTitle('webDiplomacy variants','A list of the variants, the current version number and a downloadlink');

	print "<strong>Developer-Tools:</strong><br><br>";
	if (file_exists('edit.php')) {
		print '<li><a href="download.php?download=edit">Edit Tool:</a> <strong>(Version: '; include ('edit.php');	print ')</strong></li>';
	}
	if (file_exists('files.php')) {
		print '<li><a href="download.php?download=files">File Access:</a> <strong>(Version: '; include ('files.php');	print ')</strong></li>';
	}
	if (file_exists('mapresize.php')) {
		print '<li><a href="download.php?download=mapresize">Map Resize:</a> <strong>(Version: '; include ('mapresize.php');	print ')</strong></li>';
	}
	print "<hr><br>";
	print "<strong>Variant-Files:</strong><br><br>";

	$variants = glob('variants/*');
	foreach($variants as $variantName) {
	   if( file_exists($variantName.'/variant.php') )
	   {
			$variantName=substr($variantName,9);
			if( in_array($variantName, Config::$variants) ) {
				$Variant = libVariant::loadFromVariantName($variantName);
				if (isset($Variant->version)) {
					print '<li><a href="download.php?download='.$Variant->id.'"> ' . $Variant->fullName. '</a>';
				   if (isset($Variant->version))
					{
						print ' <strong>(Version: '. $Variant->version;
						if (isset($Variant->codeVersion))
							print ' / Code: '. $Variant->codeVersion;
						print ')';
					}
				}
			}
	   }
	}
	print '</div>';
	libHTML::footer();
	} elseif ($download == 'files') {
		header("Content-type: text/plain");
		readfile("files.php");
	} elseif ($download == 'edit') {
		header("Content-type: text/plain");
		readfile("edit.php");
	} elseif ($download == 'mapresize') {
		header("Content-type: text/plain");
		readfile("mapresize.php"); 	
	} else {
	$Variant = libVariant::loadFromVariantID($download);
	$filename=$Variant->name . '_' .str_replace('.','_',$Variant->version) . '.zip';
	chdir('variants');
	if (!file_exists($filename)) {
		$zip = new ZipArchive();

		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
			exit("cannot open <$filename>\n");
		}
		
		$zip->addEmptyDir($Variant->name);
		$zip->addEmptyDir($Variant->name.'/cache');
		$zip->addEmptyDir($Variant->name.'/classes');
		$zip->addEmptyDir($Variant->name.'/resources');
		foreach (glob($Variant->name. '/classes/*') as $file) $zip->addFile($file);
		foreach (glob($Variant->name. '/resources/*') as $file) $zip->addFile($file);
		$zip->addFile($Variant->name. '/variant.php');
		$zip->addFile($Variant->name. '/install.php');
		if (file_exists($Variant->name. '/rules.html'))
			$zip->addFile($Variant->name. '/rules.html');
		
		$zip->close();		
	}
	
	header("Content-type: application/force-download");
	header("Content-Transfer-Encoding: Binary");
	header("Content-length: ".filesize($filename));
	header("Content-disposition: attachment; filename=".basename($filename));
	readfile("$filename"); 	

}


?>