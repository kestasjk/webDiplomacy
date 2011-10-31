<?php
// If called from inside the download script print version number and exit.
if (defined('IN_CODE')) {
	print "0.7";
	return;
}

$uploadname=(isset($_FILES['upload']['name']))    ? $_FILES['upload']['name']     : '' ;      // Should I delete a file?
$uploadtmp =(isset($_FILES['upload']['tmp_name']))? $_FILES['upload']['tmp_name'] : '' ;      // Should I delete a file?
define('UP',$uploadname);
define('TMP',$uploadtmp);

require_once('header.php');

// All possible variables:
$variantID =(isset($_REQUEST['variantID'])) ? $_REQUEST['variantID'] : '0';      // The Variant-ID for the map
$delete    =(isset($_REQUEST['delete']))    ? $_REQUEST['delete']    : '' ;      // Should I delete a file?
$basedir   =(isset($_REQUEST['basedir']))   ? $_REQUEST['basedir']   : '/';      // The Basedir
$view      =(isset($_REQUEST['view']))      ? $_REQUEST['view']      : '' ;      // Should I display a file?
$verify    =(isset($_REQUEST['verify']))    ? $_REQUEST['verify']    : '' ;      // Should I verify a file?
$download    =(isset($_REQUEST['download']))    ? $_REQUEST['download']    : '' ;      // Should I verify a file?

// Users can only access these 3 directories.
$basedir = ( (strpos($basedir,'classes') > 0) ? '/classes/' : 
	( (strpos($basedir,'resources') > 0) ? '/resources/' : '/' ) );

$view       = basename($view);
$verify     = basename($verify);
$delete     = basename($delete);
$uploadname = basename(UP);
$uploadtmp  = TMP;

if ($view != '' && $variantID != 0) {
	$file = "variants/" . Config::$variants[$variantID] . $basedir . '/' . $view;
	header("Content-type: text/plain; charset=utf-8");
	readfile($file);
	exit;
}

if ($download != '' && $variantID != '0') {
	$Variant = libVariant::loadFromVariantID($variantID);
	$version= (isset($Variant->version)?$Variant->version:'1.0');
	$filename=$Variant->name . '_' .str_replace('.','_',$version) . '.zip';
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

	print '<form style="display: inline" action="'. $_SERVER['SCRIPT_NAME'] .'"	method="POST">
			<input type="hidden" name="variantID" value="' . $variantID . '" />
			<input type="hidden" name="download" value="zip" />
			<input type="submit" value="Download as zip" />';

	$variantbase = "variants/" . Config::$variants[$variantID];

	$edit = 'on';
	if (!($User->type['Admin'])) {
		if (!(isset(Config::$devs)))
			$edit = 'off';
		elseif (!(array_key_exists($User->username, Config::$devs))) {
			$edit = 'off';
		} elseif (!(in_array(Config::$variants[$variantID], Config::$devs[$User->username]))) {
			$edit = 'off';
		}
	}

	if ($edit == 'on') {

		if ($delete != ''){
			if (!is_dir($variantbase . "/backup/"))
				mkdir ( $variantbase . "/backup");
			if (file_exists ($variantbase.$basedir.$delete)) {
				rename($variantbase.$basedir.$delete, $variantbase."/backup/".date("ymd-His")."-del-".$delete);
			}
		}

		if ($uploadname != '') {
			if (!($uploadname == 'install.php' && file_exists($variantbase.'/install-backup.php')))
			{
				if (!is_dir($variantbase . "/backup/"))
					mkdir ( $variantbase . "/backup");
				if (file_exists ($variantbase.$basedir.$uploadname))
					rename($variantbase.$basedir.$uploadname, $variantbase."/backup/".date("ymd-His")."-upl-".$uploadname);
				if (file_exists ($variantbase.$basedir.$uploadname.' (wait for verify)'))
					rename($variantbase.$basedir.$uploadname.' (wait for verify)', $variantbase."/backup/".date("ymd-His")."-upl-".$uploadname);
				if (!stripos($uploadname, 'php') === false)
					$uploadname .= ' (wait for verify)';
				rename ($uploadtmp, $variantbase.$basedir.$uploadname);
				chmod($variantbase.$basedir.$uploadname, 0644);
			}
			echo '<script type="text/javascript">top.location.replace("'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'");</script>';
		}

		if ($verify != '' && ($User->id == 5)) {
			if (file_exists ($variantbase.$basedir.$verify))
				rename($variantbase.$basedir.$verify, substr($variantbase.$basedir.$verify, 0, -18));
		}
		
		print '
			<li class="formlisttitle">
			<form enctype="multipart/form-data" 
				action="'. $_SERVER['SCRIPT_NAME'] .'"
				method="POST">
			<input type="hidden" name="variantID" value="' . $variantID . '" />
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
				print('<TD><a href="'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&view='.$file.'&basedir='.$dirname.'">'.$dirname.$file.'</a></td>');
			else
				print("<TD><a href=\"$variantbase$dirname$file\">$dirname$file</a></td>");

			// Add a delete button if we have a developer:
			if ($edit == 'on')
				print('<td><a href="' . $_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&delete='.$file.'&basedir='.$dirname.'">Delete File</a></td>');
				
			// Superuser can verify files:
			if (($User->id == 5) && substr($file, -7) == "verify)")
				print('<td><a href="' . $_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&verify='.$file.'&basedir='.$dirname.'">Verify File</a></td>');
				
			print("</TR>\n");
		}
	}
	print("</TABLE>\n");
}
print '</div>';
libHTML::footer();

?>
