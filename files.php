<?php
// If called from inside the download script print version number and exit.
if (defined('IN_CODE')) {
    print "0.5";
    return;
}

$uploadname=(isset($_FILES['upload']['name']))    ? $_FILES['upload']['name']     : '' ;      // Should I delete a file?
$uploadtmp =(isset($_FILES['upload']['tmp_name']))? $_FILES['upload']['tmp_name'] : '' ;      // Should I delete a file?
define(UP,$uploadname);
define(TMP,$uploadtmp);

require_once('header.php');

// All possible variables:
$variantID =(isset($_REQUEST['variantID']))       ? $_REQUEST['variantID']        : '0';      // The Variant-ID for the map
$delete    =(isset($_REQUEST['delete']))          ? $_REQUEST['delete']           : '' ;      // Should I delete a file?
$basedir   =(isset($_REQUEST['basedir']))         ? $_REQUEST['basedir']          : '/' ;      // Should I delete a file?
$view      =(isset($_REQUEST['view']))            ? $_REQUEST['view']             : '' ;      // Should I delete a file?

// Users can access only these 2 directories.
if (strpos($basedir,'classes') > 0)
    $basedir='/classes/';
elseif  (strpos($basedir,'resources') > 0)
    $basedir = '/resources/';
else
    $basedir = '/';

$view       = basename($view);
$delete     = basename($delete);
$uploadname = basename(UP);
$uploadtmp  = TMP;

if ($view != '' && $variantID != 0) {
    $file = "variants/" . Config::$variants[$variantID] . $basedir . '/' . $view;
    header("Content-type: text/plain; charset=utf-8");
    readfile($file);
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

    $edit = 'on';
    if (!($User->type['Admin'])) {
        if (!(array_key_exists($User->username, Config::$devs))) {
            $edit = 'off';
        } elseif (!(in_array(Config::$variants[$variantID], Config::$devs[$User->username]))) {
            $edit = 'off';
        }
    }

    if ($edit == 'on') {

        if ($delete != ''){
            if (!is_dir($variantbase . "/backup/"))
                mkdir ( $variantbase . "/backup");
            if (file_exists ($variantbase.$basedir.$delete))
            {
                rename($variantbase.$basedir.$delete, $variantbase."/backup/".time()."-del-".$delete);
                print '<li class="formlisttitle"> File ' . $delete . ' deleted!';
            }
        }

        if ($uploadname != '') {
            if ($uploadname == 'install.php' && file_exists($variantbase.'/install-backup.php'))
                print '<li class="formlisttitle">Can\'t upload new install.php, you need to turn off edit mode in the edit-tool!';
            else
            {
                if (!is_dir($variantbase . "/backup/"))
                    mkdir ( $variantbase . "/backup");
                if (file_exists ($variantbase.$basedir.$uploadname))
                    rename($variantbase.$basedir.$uploadname, $variantbase."/backup/".time()."-upl-".$uploadname);
                if (file_exists ($variantbase.$basedir.$uploadname.' (wait for verify)'))
                    rename($variantbase.$basedir.$uploadname.' (wait for verify)', $variantbase."/backup/".time()."-upl-".$uploadname);
                if (!stripos($uploadname, 'php') === false)
                    $uploadname .= ' (wait for verify)';
                rename ($uploadtmp, $variantbase.$basedir.$uploadname);
                chmod($variantbase.$basedir.$uploadname, 0644);
                print '<li class="formlisttitle"> File ' . $basedir . $uploadname . ' uploaded!';
            }
        }

        print
            '<li class="formlisttitle"><form enctype="multipart/form-data" action="'. $_SERVER['SCRIPT_NAME'] .'" method="POST">
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
    // print the variant-files
    print '<li class="formlisttitle">Variant-Files:';
    print("<TABLE border=1 cellpadding=5 cellspacing=0 class=whitelinks>\n");
    foreach (array("/","/classes/", "/resources/") as $dirname) {
        $dirArray = array();
        $dir = opendir($variantbase . $dirname);
        while (false !== ($file = readdir($dir)))
            if (!is_dir($variantbase . $dirname . $file))
                $dirArray[] = $dirname . $file;
        closedir($dir);
        sort($dirArray);
        print("<TH>" . $dirname . "</TH><TR>\n");
        for ($index = 0; $index < count($dirArray); $index++) {
            if (strpos( basename($dirArray[$index]),'install-') === false)
            {
                if (substr($dirArray[$index], -3) == 'php' || substr($dirArray[$index], -4) == 'html')
                    print('<TD><a href="' . $_SERVER['SCRIPT_NAME'] . '?variantID=' . $variantID . '&view=' . basename($dirArray[$index]) . '&basedir=' . pathinfo($dirArray[$index], PATHINFO_DIRNAME) . '">' . $dirArray[$index] . '</a></td>');
                else
                    print("<TD><a href=\"$variantbase$dirArray[$index]\">$dirArray[$index]</a></td>");
                if ($edit == 'on')
                    print('<td><a href="' . $_SERVER['SCRIPT_NAME'] . '?variantID=' . $variantID . '&delete=' . $dirArray[$index] . '&basedir=' . pathinfo($dirArray[$index], PATHINFO_DIRNAME) . '">Delete File</a></td>');
                print("</TR>\n");
            }
        }
    }
    print("</TABLE>\n");
}
print '</div>';
libHTML::footer();

?>
