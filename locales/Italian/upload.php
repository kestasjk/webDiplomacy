<html><head><title>Upload translations</title></head>
<body>
<h1>Upload</h1>
<p>Hack page for uploading translations. Copy the translations below into a text file, <b>take a backup</b>, make any necessary modifications, then reupload the file.<br /><br />

If you get errors check that the translations are well formed. You should be able to paste them into a PHP script that looks like this:
<textarea ROWS="6" style="width:50%"><?php print "<?php \$translations=array(
' from %s' => 'da %s',
' to %s' => 'a %s',
' via convoy' => 'con trasporto'
);
?>";
?>
</textarea><br />
And it should run without any syntax errors.</p>
<?php 

error_reporting(E_STRICT | E_ALL | E_NOTICE);

if( isset($_FILES["file"]) ) {
	
	print '<h2>Processing upload</h2>';
	
	print '<p>Loading data..<br />';
	
	if ($_FILES["file"]["error"] > 0)
		die("Error: " . $_FILES["file"]["error"]);
	
	$translations = file_get_contents($_FILES["file"]["tmp_name"]);
	
	//if( get_magic_quotes_gpc() )
	//	$translations = stripslashes($translations);
	
	$length = strlen($translations);
	
	$mode = 'whitespace_before';
	$quote_mode = 'single';
	$string = array();
	$string_from = "";
	$parsed = array();
	
	function error_context($translations, $i) {
		$start = $i - 20;
		$length = 40;
		if( $start < 0 ) $start = 0;
		return substr($translations, $start, $length);
	}
	
	function process_string($str) {
		return mb_convert_encoding(stripslashes($str),"UTF-8","US-ASCII");
	}
	
	// whitespace_before -> quote
	// string_from -> quote			<== translate from
	// whitespace_between_first -> =>
	// whitespace_between_last -> quote
	// string_to -> quote 			<== translate to
	// whitespace_after -> ,
	
	print 'Parsing..<br />';
	
	for( $i = 0; $i < $length; $i++)
	{
		
		//print "Character ".$i.". Mode: ".$mode.". Character: ".$translations{$i}.".<br />";
		
		switch($mode) {
			case 'whitespace_before':
				if( $translations{$i} == ' ' || $translations{$i} == "\r" || $translations{$i} == "\n"  || $translations{$i} == "\t" )
					continue;
				else if( $translations{$i} == "'" )
				{
					$quote_mode = 'single';
					$mode = "string_from";
				}
				else if( $translations{$i} == '"' )
				{
					$quote_mode = 'double';
					$mode = "string_from";
				}
				else
				{
					die("Parse error at character ".$i.". Mode: ".$mode.". Character: ".$translations{$i}."."." Context: ".error_context($translations, $i));
				}
				break;
				
			case 'string_from':
				if( ( $quote_mode == 'single' && ($translations{$i} == "'" && ( $i == 0 || $translations{$i-1} != '\\' ) ) )  ||
					( $quote_mode == 'double' && ($translations{$i} == '"' && ( $i == 0 || $translations{$i-1} != '\\' ) ) ) ) 
				{
					$mode = 'whitespace_between_first';
					$string_from = process_string(implode('',$string));
					$parsed[$string_from] = "";
					$string = array();
				}
				else
				{
					$string[] = $translations{$i};
				}
				break;
				
			case 'whitespace_between_first':
				if( $translations{$i} == ' ' || $translations{$i} == "\n"  || $translations{$i} == "\t" )
					continue;
				else if( $translations{$i} == '=' && $translations{$i+1} == '>' )
				{
					$i++;
					$mode = 'whitespace_between_last';
				}
				else
				{
					die("Parse error at character ".$i.". Mode: ".$mode.". Character: ".$translations{$i}.". String from: ".$string_from." Context: ".error_context($translations, $i));
				}
				break;
				
			case 'whitespace_between_last':
				if( $translations{$i} == ' ' || $translations{$i} == "\n"  || $translations{$i} == "\t" )
					continue;
				else if( $translations{$i} == "'" )
				{
					$quote_mode = 'single';
					$mode = "string_to";
				}
				else if( $translations{$i} == '"' )
				{
					$quote_mode = 'double';
					$mode = "string_to";
				}
				else
				{
					die("Parse error at character ".$i.". Mode: ".$mode.". Character: ".$translations{$i}.". String from: ".$string_from." Context: ".error_context($translations, $i));
				}
				break;
				
			case 'string_to':
				if( ( $quote_mode == 'single' && ($translations{$i} == "'" && $translations{$i-1} != '\\' ) ) ||
					( $quote_mode == 'double' && ($translations{$i} == '"' && $translations{$i-1} != '\\' ) ) ) 
				{
					$mode = 'whitespace_after';
					$parsed[$string_from] = process_string(implode('',$string));
					$string = array();
				}
				else
				{
					$string[] = $translations{$i};
				}
				break;
				
			case 'whitespace_after':
				if( $translations{$i} == ' ' || $translations{$i} == "\n"  || $translations{$i} == "\t" )
					continue;
				else if( $translations{$i} == ',' )
				{
					$mode = "whitespace_before";
				}
				else
				{
					die("Parse error at character ".$i.". Mode: ".$mode.". Character: ".$translations{$i}.". String from: ".$string_from." Context: ".error_context($translations, $i));
				}
				break;
				
			default:
				die("Unexpected parse mode: ".$mode);
		}
		
	}
	
	//print htmlentities(serialize($parsed));
	/*
	print "Parsed length = ".strlen(serialize($parsed))." Current length = ".strlen(file_get_contents('lookup.php.txt'))."\n\n";
	print "Parsed\n\n";
	print serialize($parsed);
	print "Current\n\n";
	print file_get_contents('lookup.php.txt');
	
	$current = unserialize(file_get_contents('lookup.php.txt'));
	
	foreach($parsed as $k=>$v) {
		if( $v != $current[$k] ) {
			print "Difference with ".$k.":\n".$v."\nvs\n".$current[$k]."\n\n";
		}
	}
	foreach($current as $k=>$v) {
		if( $v != $parsed[$k] ) {
			print "Difference with ".$k.":\n".$v."\nvs\n".$parsed[$k]."\n\n";
		}
	}
	*/
	
	print 'Saving to PHP..<br />';
	
	if( false === file_put_contents("lookup.php.txt",serialize($parsed)) ) {
		die("Couldn't write results to lookup.php.txt");
	}
	
	print 'Saving to JavaScript..<br />';
	
	$js = "Locale.textLookup = \$H({\n";
	
	foreach($parsed as $k=>$v) {
		$js .= "\t'".
			str_replace("'", "\\'", str_replace("\n", "\\\n", str_replace("\r\n", "\n", $k)))
			."': '".
			str_replace("'", "\\'", str_replace("\n", "\\\n", str_replace("\r\n", "\n", $v)))
			."',\n";
		
	}
	$js .= "});\n";
	
	if( false === file_put_contents("lookup.js",$js) ) {
		die("Couldn't write results to lookup.js");
	}
	
	print 'Done.<br /><br />Check below that the translations have been applied successfully.</p>';
}

?>
<h2>Translations upload:</h2>
<form method="post" enctype="multipart/form-data">
<input type="file" name="file" id="file"><br>
<input type="Submit" value="Submit" />
</form>
<h2>Current translations:</h2>
<textarea ROWS="20" style="width:100%">
<?php 
$current = unserialize(file_get_contents('lookup.php.txt'));
foreach( $current as $k=>$v)
	print "'".addslashes($k)."' => '".addslashes($v)."',\n";

?></textarea>
</body>
</html>