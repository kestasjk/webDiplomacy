<?php

$uploadname=(isset($_FILES['upload']['name']))    ? $_FILES['upload']['name']     : '' ; // 
$uploadtmp =(isset($_FILES['upload']['tmp_name']))? $_FILES['upload']['tmp_name'] : '' ; // 
define('UP',$uploadname);
define('UPTMP',$uploadtmp);

require_once('header.php');

// All possible variables:
$variantID =(isset($_REQUEST['variantID'])) ? $_REQUEST['variantID'] : '0'; // The Variant-ID for the map
$action    =(isset($_REQUEST['action']))    ? $_REQUEST['action']    : '' ; //
$basedir   =(isset($_REQUEST['basedir']))   ? $_REQUEST['basedir']   : '/'; // 
$file      =(isset($_REQUEST['file']))      ? $_REQUEST['file']      : '' ; // 
$uploadtmp = UPTMP;
$uploadname = UP;

// Users can only access these 3 directories.
$basedir = ( (strpos($basedir,'classes') > 0) ? '/classes/' : 
	( (strpos($basedir,'resources') > 0) ? '/resources/' : '/' ) );

$file = ($file != '' ? basename($file) : basename($uploadname));
if (!(isset(Config::$variants[$variantID]))) $variantID=0;

if ($action == 'view' && $variantID != 0) {
	$filename = "variants/" . Config::$variants[$variantID] . $basedir . '/' . $file;
	header("Content-type: text/plain; charset=utf-8");
	header("Content-disposition: inline; filename=".$file);
	readfile($filename);
	exit;
}

if ($action == 'download' && $variantID != '0') {
	$variant = libVariant::loadFromVariantID($variantID);
	$version= (isset($variant->version)?'_V'.$variant->version:'');
	$code   = (isset($variant->codeVersion)?'_C'.$variant->codeVersion:'');
	$filename=$variant->name.str_replace('.','_',$version).str_replace('.','_',$code) . '.zip';
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
	readfile($filename); 	
	exit;
}

libHTML::starthtml();
print '<div class="content">';

// Generate an array with all variants available:
$all_variants = array();
foreach (Config::$variants as $id => $name)
	$all_variants[$id] = $name;
if ($variantID == 0)
	$all_variants[0] = ' Choose a variant...';
asort($all_variants);

print '<li class="formlisttitle">Variant: ';
print '<form style="display: inline" method="get" name="set_map">';
print '<select name="variantID" onchange="this.form.submit();">';
foreach ( $all_variants as $id=>$name ) {
	print '<option value="'.$id.'"';
	if ($id == $variantID)
		print ' selected';
	print '>'.$name.'</option>';
}
print '</select></form>';

if ($variantID != 0) {

	$variantbase = "variants/" . Config::$variants[$variantID];

	$edit = false;
	if ($User->id == 5) $edit=true;
	if (isset(Config::$devs))
		if (array_key_exists($User->username, Config::$devs)) 
		 if (in_array(Config::$variants[$variantID], Config::$devs[$User->username]))
			$edit = true;
	
	print '<form style="display: inline" action="'.$_SERVER['SCRIPT_NAME'].'" method="POST">
			<input type="hidden" name="variantID" value="'.$variantID.'" />
			<input type="hidden" name="action" value="download" />
			<input type="submit" value="Download as zip" /></form>';


	if ($edit)
	{
		if (!is_dir($variantbase."/backup/"))
			mkdir ($variantbase."/backup");
			
		if (($action == 'delete') && (file_exists ($variantbase.$basedir.$file)))
			rename($variantbase.$basedir.$file, $variantbase."/backup/".date("ymd-His")."-del-".$file);

		if ($action == 'upload') {
			if (!($file == 'install.php' && file_exists($variantbase.'/install-backup.php')))
			{
				if (file_exists ($variantbase.$basedir.$file))
					rename($variantbase.$basedir.$file, $variantbase."/backup/".date("ymd-His")."-upl-".$file);
				if (file_exists ($variantbase.$basedir.$file.' (wait for verify)'))
					rename($variantbase.$basedir.$file.' (wait for verify)', $variantbase."/backup/".date("ymd-His")."-upl-".$file);
				if (!stripos($file, 'php') === false)
					$file .= ' (wait for verify)';
				rename ($uploadtmp, $variantbase.$basedir.$file);
				chmod($variantbase.$basedir.$file, 0644);
			}
			echo '<script type="text/javascript">top.location.replace("'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'");</script>';
		}

		if ($action == 'verify' && ($User->id == 5)) {
			if (file_exists ($variantbase.$basedir.$file))
				rename($variantbase.$basedir.$file, substr($variantbase.$basedir.$file, 0, -18));
		}
	
		print '
			<li class="formlisttitle">
			<form enctype="multipart/form-data" 
				action="'. $_SERVER['SCRIPT_NAME'] .'"
				method="POST">
			<input type="hidden" name="variantID" value="' . $variantID . '" />
			<input type="hidden" name="action" value="upload" />
			Upload file: <input type="file" name="upload" /> - directory:
			<select name="basedir">
			<option value="/" selected>  /           </option>
			<option value="/classes/">   /classes/   </option>
			<option value="/resources/"> /resources/ </option>
			</select>
			<input type="submit" value="Upload File" />
			</form>';
	}
	
	// print the variant-files in a nice grid
	print '<li class="formlisttitle">Variant-Files:';
	print("<TABLE border=1 cellpadding=5 cellspacing=0 class=whitelinks>\n");
	foreach (array("/","/classes/", "/resources/") as $dirname) {
		print("<TH>" . $dirname . "</TH><TR>\n");
		
		$files = array();
		$dir = opendir($variantbase . $dirname);
		while (false !== ($file = readdir($dir)))
			if (!is_dir($variantbase . $dirname . $file))
				$files[] = $file;
		closedir($dir);
		sort($files);
		
		foreach ($files as $file) {
			// Rename the install-backup.php to avoid deletion by accident...
			if ($file=='install-backup.php') $file .= ' (locked by edit tool)';
			
			// Call the php and html files with a wrapper to display the content...
			if (substr($file, -3) == 'php' || substr($file, -4) == 'html' || substr($file, -4) == 'htm')
				print('<TD><a href="'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&action=view&file='.$file.'&basedir='.$dirname.'">'.$dirname.$file.'</a></td>');
			else
				print("<TD><a href=\"$variantbase$dirname$file\">$dirname$file</a></td>");

			// Add a delete button if we have a developer:
			if ($edit == 'on')
				print('<td><a href="' . $_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&action=delete&file='.$file.'&basedir='.$dirname.'">Delete File</a></td>');
				
			// Superuser can verify files:
			if (($User->id == 5) && substr($file, -7) == "verify)")
				print('<td><a href="' . $_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&action=verify&file='.$file.'&basedir='.$dirname.'">Verify File</a></td>');
				
			print("</TR>\n");
		}
	}
	print("</TABLE>\n");
}
print '</div>';
libHTML::footer();

?>
