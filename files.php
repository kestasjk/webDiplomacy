<?php
// For security reasons fileupload and all variables are ussually discarded in header.php. Save this in constants.
$uploadname=(isset($_FILES['upload']['name']))    ? $_FILES['upload']['name']     : '' ; // The uploaded filename
$uploadtmp =(isset($_FILES['upload']['tmp_name']))? $_FILES['upload']['tmp_name'] : '' ; // the tmp-filename from PHP
define('UP',$uploadname);
define('UPTMP',$uploadtmp);

require_once('header.php');

// Put the values back...
$uploadtmp = UPTMP;

// The Variant-ID for the map
$variantID = isset($_REQUEST['variantID']) ? $_REQUEST['variantID'] : '0'; 
if (!(isset(Config::$variants[$variantID]))) $variantID = 0;

// What to do
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '' ; 

// Users can only access these 3 directories.
$basedir = isset($_REQUEST['basedir']) ? $_REQUEST['basedir'] : '/'; 
switch($basedir) {
	case '/classes/'  : $basedir = '/classes/';   break;
	case '/resources/': $basedir = '/resources/'; break;
	default           : $basedir = '/';
}

// The filename (only letters, numbers and "." or "-" allowed...
$file = isset($_REQUEST['file']) ? $_REQUEST['file'] : UP; 
$file = preg_replace('/[^A-z0-9 \(\)\-\.]/i', '', basename($file));

// Filled with the new content after editing
$updatedfile = isset($_REQUEST['updatedfile'])? $_REQUEST['updatedfile']: '' ; 

// a message to diaplay.
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : '' ; 
switch($msg) {
	case '1'  : $msg = 'Failed to save '.$basedir.$file.' !';                  break;
	case '2'  : $msg = 'File '.$basedir.$file.' saved.';                       break;
	case '3'  : $msg = 'File '.$basedir.$file.' uploaded.';                    break;
	case '4'  : $msg = 'File '.$basedir.$file.' deleted.';                     break;
	case '5'  : $msg = 'File '.$basedir.$file.' verified.';                    break;
	case '6'  : $msg = 'Editing canceled. File '.$basedir.$file.' not saved!'; break;
	default   : $msg = '';
}

/*
 * Now for the different possible actions:
 */

/*
 * View a file
 */
if ($action == 'view' && $variantID != 0) {
	$filename = "variants/" . Config::$variants[$variantID] . $basedir.$file;
	if (file_exists($filename))
	{
		header("Content-type: text/plain; charset=utf-8");
		header("Content-disposition: inline; filename=".$file);
		readfile($filename);
	}
	exit;
}

/*
 * Download the sourcecode in a big zip-file
 */
if ($action == 'download' && $variantID != '0') {
	$variant = libVariant::loadFromVariantID($variantID);
	$version= (isset($variant->version)?'_V'.$variant->version:'');
	$code   = (isset($variant->codeVersion)?'_C'.$variant->codeVersion:'');
	$filename=$variant->name.str_replace('.','_',$version).str_replace('.','_',$code) . '.zip';
	chdir('variants');
	if (!file_exists($filename)) {
		$zip = new ZipArchive();

		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE)
			exit("cannot open <$filename>\n");
		
		$zip->addEmptyDir($variant->name);
		$zip->addEmptyDir($variant->name.'/cache');
		$zip->addEmptyDir($variant->name.'/classes');
		$zip->addEmptyDir($variant->name.'/resources');
		foreach (glob($variant->name. '/classes/*') as $file) $zip->addFile($file);
		foreach (glob($variant->name. '/resources/*') as $file) $zip->addFile($file);
		$zip->addFile($variant->name. '/variant.php');
		$zip->addFile($variant->name. '/install.php');
		if (file_exists($variant->name. '/rules.html'))
			$zip->addFile($variant->name. '/rules.html');
		
		$zip->close();		
	}
	
	header("Content-type: application/force-download");
	header("Content-Transfer-Encoding: Binary");
	header("Content-length: ".filesize($filename));
	header("Content-disposition: attachment; filename=".basename($filename));
	readfile($filename); 	
	exit;
}


/*
 * Show the variant-files with some edit/upload/delete  possibilities for variant developers
 */
 
libHTML::starthtml();
print '<div class="content">';

print '<li class="formlisttitle">Variant: ';
print '<form style="display: inline" method="get" name="set_map">';
print '<select name="variantID" onchange="this.form.submit();">';
if ($variantID == 0)
	print '<option value="0" selected>Choose a variant...</option>';

asort(Config::$variants);

foreach ( Config::$variants as $id=>$name )
{
	if (isset(Config::$devs))
	{
		if (array_key_exists($User->username, Config::$devs))
		{
			foreach (Config::$devs[$User->username] as $variantName)
			{
				if ($name == $variantName)
					print '<option value="'.$id.'"'.($id == $variantID ? ' selected':'').'>'.$name.'</option>';
			}
		
		}
		elseif ($User->id == 5)
		{
			foreach ( Config::$devs as $dev=>$variants )
			{
				foreach ($variants as $variantName)
				{
					if ($name == $variantName)
						print '<option value="'.$id.'"'.($id == $variantID ? ' selected':'').'>'.$dev.': '.$name.'</option>';
				}
			}
		}
	}
	else
	{
		print '<option value="'.$id.'"'.($id == $variantID ? ' selected':'').'>'.$name.'</option>';
	}
}
print '</select></form>'.($variantID == 0 ? '</li>' : '');

if ($variantID != 0)
{
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
			<input type="submit" value="Download as zip" /></form>
			</li>';

	if ($edit)
	{
		if ($msg != '')
			print '<li class="formlisttitle">'.$msg.'</li>';
			
		if (($action == 'edit') && (file_exists ($variantbase.$basedir.$file)))
		{	
			print '
				<li class="formlisttitle">Edit: '.$basedir.$file.': 
					<form  style="display: inline" action="'. $_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&msg=6&file='.$file.'&basedir='.$basedir.'" method="post">
					<input type="submit" value="Cancel">
					</form>
					<form  style="display: inline" action="'. $_SERVER['SCRIPT_NAME'] .'" method="post">
					<input type="submit" value="Save Changes"></li>
					<input type="hidden" name="action" value="filesave" />
					<input type="hidden" name="variantID" value="' . $variantID . '" />
					<input type="hidden" name="basedir" value="'.$basedir.'"/>
					<input type="hidden" name="file" value="'.$file.'"/>
					<textarea rows="20" style="border: 1px solid #666666; font-family: courier;" name="updatedfile">';
			//Open the file chosen in the select box in the previous form into the text area
			$file2open = fopen($variantbase.$basedir.$file, "r");
			$current_data = @fread($file2open, filesize($variantbase.$basedir.$file));
			fclose($file2open);
			// Recplace a "</textarea>" tag so it does not break the layout
			$current_data = str_ireplace("</textarea>", "<END-TA-DO-NOT-EDIT>", $current_data);
			echo $current_data;
			print '</textarea></form></div>';
			libHTML::footer();
			exit;
		}
		
		// Delete the global css if a CSS file got edited or upload
		if (($action == 'upload' || $action == 'filesave') && !stripos($file, '.css') === false)
			foreach (glob(libCache::Dirname('css').'/variants-*.css') as $cssfilename)
				unlink($cssfilename);
				
		if (($action == 'filesave') && (file_exists ($variantbase.$basedir.$file)))
		{
			rename($variantbase.$basedir.$file, $variantbase."/cache/".date("ymd-His")."-edit-".$file);
			if (stripos($file, '(wait for verify)') === false)
				if (!stripos($file, 'php') === false)
					$file .= ' (wait for verify)';
			$file2ed = fopen($variantbase.$basedir.$file, "w+");
			// Recplace a "</textarea>" tag so it dows not break the layout
			$updatedfile = str_ireplace("<END-TA-DO-NOT-EDIT>", "</textarea>", $updatedfile);
			//Remove any slashes that may be added do to " ' " s.  Thats a single tick, btw.
			$updatedfile = stripslashes($updatedfile);
			$ok = fwrite($file2ed,$updatedfile);
			fclose($file2ed);
			
			if (!$ok)
				echo '<script type="text/javascript">top.location.replace("'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&msg=1&file='.$file.'&basedir='.$basedir.'");</script>';
			else
				echo '<script type="text/javascript">top.location.replace("'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&msg=2&file='.$file.'&basedir='.$basedir.'");</script>';
			exit;
		}
		
		if (($action == 'delete') && (file_exists ($variantbase.$basedir.$file)))
		{
			rename($variantbase.$basedir.$file, $variantbase."/cache/".date("ymd-His")."-del-".$file);
			echo '<script type="text/javascript">top.location.replace("'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&msg=4&file='.$file.'&basedir='.$basedir.'");</script>';
			exit;
		}

		if ($action == 'upload') {
			if (!($file == 'install.php' && file_exists($variantbase.'/cache/install-backup.php')))
			{
				if ($file != '')
				{
					if (file_exists ($variantbase.$basedir.$file))
						rename($variantbase.$basedir.$file, $variantbase."/cache/".date("ymd-His")."-upl-".$file);
					if (file_exists ($variantbase.$basedir.$file.' (wait for verify)'))
						rename($variantbase.$basedir.$file.' (wait for verify)', $variantbase."/cache/".date("ymd-His")."-upl-".$file);
					if (!stripos($file, 'php') === false)
						$file .= ' (wait for verify)';
					rename ($uploadtmp, $variantbase.$basedir.$file);
					chmod($variantbase.$basedir.$file, 0644);
				}
			}
			
			echo '<script type="text/javascript">top.location.replace("'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&msg=3&file='.$file.'&basedir='.$basedir.'");</script>';
			exit;
		}

		if ($action == 'verify' && ($User->id == 5)) {
			if (file_exists ($variantbase.$basedir.$file))
			{
				$newfile = substr($file, 0, -18);
				rename($variantbase.$basedir.$file, $variantbase.$basedir.$newfile);
			}
			echo '<script type="text/javascript">top.location.replace("'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&msg=5&file='.$newfile.'&basedir='.$basedir.'");</script>';
			exit;
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
			// Call the php and html files with a wrapper to display the content...
			if (substr($file, -3) == 'php' || substr($file, -4) == 'html' || substr($file, -4) == 'htm')
				print('<TD><a href="'.$_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&action=view&file='.$file.'&basedir='.$dirname.'">'.$dirname.$file.'</a></td>');
			else
				print("<TD><a href=\"$variantbase$dirname$file\">$dirname$file</a></td>");

			if ($edit == 'on')
			{
				// Add a delete button if we have a developer:
				print('<td><a href="' . $_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&action=delete&file='.$file.'&basedir='.$dirname.'">Delete</a></td>');
				
				// Superuser can edit files:
				print('<td><a href="' . $_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&action=edit&file='.$file.'&basedir='.$dirname.'">Edit</a></td>');
			}
			
			// Superuser can verify files:
			if (($User->id == 5) && substr($file, -7) == "verify)")
				print('<td><a href="' . $_SERVER['SCRIPT_NAME'].'?variantID='.$variantID.'&action=verify&file='.$file.'&basedir='.$dirname.'">Verify</a></td>');
				
			print("</TR>\n");
		}
	}
	print("</TABLE>\n");
}
print '</div>';
libHTML::footer();

?>
