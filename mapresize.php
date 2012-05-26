<?php

require_once('header.php');

if ($_SERVER['REQUEST_METHOD'] != "POST") {
	libHTML::starthtml();
	print '<div class="content">';

	$edit=true;
    if (!($User->type['Admin']))
        if (!(array_key_exists($User->username, Config::$devs)))
            $edit = 'false';
			
	// Admins only
	if ($edit=true)  {
		print '<li class="formlisttitle"><form enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
		print '<input type="hidden" name="MAX_FILE_SIZE" value="100000" />';
		print 'Choose a picture in PNG-format to upload: <input name="imgfile" type="file" />';
		print '<li class="formlisttitle">Target resolution: Width (max 2000):  <input type="text" name="new_x" size="4">';	
		print ' - Height (max 2000): <input type="text" name="new_y" size="4"><br>';	
		print '<li class="formlisttitle"><input type="submit" value="Upload File" />';
		print '</form>';
	} else {
		print "Admins and devs only";
	}
	print '</div>';
	libHTML::footer();
	
} else {
	
	include_once ("lib/cache.php");
	
	set_time_limit(190);
	ini_set('memory_limit','100M');

	$new_x = (isset($_POST['new_x'])) ? (int)$_POST['new_x'] : '250';  // 
	$new_y = (isset($_POST['new_y'])) ? (int)$_POST['new_y'] : '250';  // 

	if ($new_x < 100) $new_x=100;
	if ($new_x > 2000) $new_x=2000;
	if ($new_y < 100) $new_y=100;
	if ($new_y > 2000) $new_y=2000;
	
	$img=imagecreatefromstring(file_get_contents($_FILES['imgfile']['tmp_name']));

	if (imageistruecolor($img))
		imagetruecolortopalette($img, false, 200);
		
	$width=imagesx($img);
	$height=imagesy($img);
	
	// Output original Image:
	imagepng($img, libCache::dirID('users',$User->id).'/resize_orig.png');

	// Create BW:
	$white=imagecolorallocate($img,255,255,255);
	$black=imagecolorallocate($img,0,0,0);
	for ( $x=0; ($x < $width); $x++) {
		for ($y=0; ($y<$height); $y++) {
			$rgb = imagecolorat($img, $x, $y);
			$cols = imagecolorsforindex($img, $rgb);
			if (($cols['red'] < 10) &&($cols['green'] < 10) &&($cols['blue'] < 10) )
				imagesetpixel($img,$x,$y,$black);
			else
				imagesetpixel($img,$x,$y,$white);		
		}
	}
	imagepng($img,libCache::dirID('users',$User->id).'/resize_bw.png');

	// Color the territories:
	for ( $x=0; ($x < $width); $x++) {
		for ($y=0; ($y<$height); $y++) {
			$rgb = imagecolorat($img, $x, $y);
			if ($rgb == $white)	
			{
				$r=rand(0,255); $g=rand(0,255); $b=rand(0,255);
				$col=imagecolorallocate($img,$r,$g,$b);
				if (!$col)
					$col = imageColorClosest($img, $r, $g, $b);
				imagefilltoborder($img,$x,$y,$black,$col);
			}
		}
	}
	imagepng($img,libCache::dirID('users',$User->id).'/resize_col.png');

	// Remove the borders:
	for ($i=0; $i<7; $i++) {
		for ($y=0; ($y < $height-1); $y++) {
			for ( $x=0; ($x < $width); $x++) {
				if ((imagecolorat($img, $x, $y)) == $black) {
					imagesetpixel($img,$x,$y,imagecolorat($img, $x, $y+1));
				}
			}
		}
		for ( $x=0; ($x < $width-1); $x++) {
			for ($y=0; ($y<$height); $y++) {
				if ((imagecolorat($img, $x, $y)) == $black) {
					imagesetpixel($img,$x,$y,imagecolorat($img, $x+1, $y));				
				}
			}
		}

	}
	imagepng($img,libCache::dirID('users',$User->id).'/resize_wo_br.png');

	// create new picture
	$img_new=imagecreate($new_x,$new_y);
	imagecopyresized($img_new,$img,0,0,0,0,$new_x,$new_y,$width,$height);
	imagedestroy($img);	
	imagepng($img_new,libCache::dirID('users',$User->id).'/resize_new.png');

	// Add the borders:
	$black=imagecolorallocate($img_new,0,0,0);
	for ( $x=0; ($x < $new_x); $x++) {
		for ($y=0; ($y<$new_y -1 ); $y++) {
			$col1=imagecolorat($img_new, $x, $y);
			$col2=imagecolorat($img_new, $x, $y+1);
			if (($col1 != $col2) && ($col1 != $black)) {
				imagesetpixel($img_new,$x,$y,$black);
			}
		}
	}
	for ($y=0; ($y < $new_y -1); $y++) {
		for ( $x=0; ($x < $new_x-1); $x++) {
			$col1=imagecolorat($img_new, $x, $y);
			$col2=imagecolorat($img_new, $x+1, $y);
			if (($col1 != $col2) && ($col1 != $black) && ($col2 != $black)) {
				imagesetpixel($img_new,$x,$y,$black);
			}

		}
	}
	imagepng($img_new,libCache::dirID('users',$User->id).'/resize_new_br.png');

	// Enhance the borders:
	// Soften the edges:
	for ( $x=1; ($x < $new_x -1 ); $x++) {
		for ($y=1; ($y<$new_y -1 ); $y++) {
			$col1=imagecolorat($img_new, $x,   $y-1);
			$col2=imagecolorat($img_new, $x-1, $y);
			$col3=imagecolorat($img_new, $x,   $y);
			$col4=imagecolorat($img_new, $x+1, $y);
			$col5=imagecolorat($img_new, $x,   $y+1);
			if ($col3 == $black) {
				if (($col1 == $black) && ($col2 == $black))
					imagesetpixel($img_new,$x,$y,$col4);
				if (($col5 == $black) && ($col2 == $black))
					imagesetpixel($img_new,$x,$y,$col4);
				if (($col1 == $black) && ($col4 == $black))
					imagesetpixel($img_new,$x,$y,$col2);
				if (($col5 == $black) && ($col4 == $black))
					imagesetpixel($img_new,$x,$y,$col2);
			}
		}
	}

	// Enhance the stairs (a bit)
	for ( $x=1; ($x < $new_x - 3 ); $x++) {
		for ($y=1; ($y<$new_y - 3 ); $y++) {
			$col1=imagecolorat($img_new, $x,   $y  );
			$col2=imagecolorat($img_new, $x+1, $y  );
			$col3=imagecolorat($img_new, $x+2, $y  );
			$col4=imagecolorat($img_new, $x,   $y+1);
			$col5=imagecolorat($img_new, $x+1, $y+1);
			$col6=imagecolorat($img_new, $x+2, $y+1);
			$col7=imagecolorat($img_new, $x,   $y+2);
			$col8=imagecolorat($img_new, $x+1, $y+2);
			$col9=imagecolorat($img_new, $x+2, $y+2);
			
			$chk1=imagecolorat($img_new, $x-1, $y  );
			$chk2=imagecolorat($img_new, $x  , $y-1);
			$chk3=imagecolorat($img_new, $x+2, $y-1);
			$chk4=imagecolorat($img_new, $x+3, $y  );
			$chk5=imagecolorat($img_new, $x+3, $y+2);
			$chk6=imagecolorat($img_new, $x+2, $y+3);
			$chk7=imagecolorat($img_new, $x  , $y+3);
			$chk8=imagecolorat($img_new, $x-1, $y+2);
			
				if (($col2 == $black) && ($col3 == $black) && ($col4 == $black) && ($col7 == $black) && ($col1 != $black)) {
					if (($chk1 != $black) && ($chk2 != $black)) {
						imagesetpixel($img_new,$x+1,$y  ,$col1);
						imagesetpixel($img_new,$x  ,$y+1,$col1);
						imagesetpixel($img_new,$x+1,$y+1,$black);
				}}
				if (($col7 == $black) && ($col8 == $black) && ($col6 == $black) && ($col3 == $black) && ($col9 != $black)) {
					if (($chk5 != $black) && ($chk6 != $black)) {
						imagesetpixel($img_new,$x+2,$y+1,$col9);
						imagesetpixel($img_new,$x+1,$y+2,$col9);
						imagesetpixel($img_new,$x+1,$y+1,$black);
				}}
				if (($col1 == $black) && ($col2 == $black) && ($col6 == $black) && ($col9 == $black) && ($col3 != $black)) {
					if (($chk3 != $black) && ($chk4 != $black)) {
						imagesetpixel($img_new,$x+1,$y  ,$col3);
						imagesetpixel($img_new,$x+2,$y+1,$col3);
						imagesetpixel($img_new,$x+1,$y+1,$black);
				}}
				if (($col1 == $black) && ($col4 == $black) && ($col8 == $black) && ($col9 == $black) && ($col7 != $black)) {
					if (($chk8 != $black) && ($chk7 != $black)) {
						imagesetpixel($img_new,$x  ,$y+1,$col7);
						imagesetpixel($img_new,$x+1,$y+2,$col7);
						imagesetpixel($img_new,$x+1,$y+1,$black);
				}}
		}
	}
	
		
	imagepng($img_new,libCache::dirID('users',$User->id).'/resize_new_br_enh.png');

	imagedestroy($img_new);

	require_once('header.php');
	libHTML::starthtml();
	print '<div class="content">';
	print "Original:<hr>";
	print '<img src="'.libCache::dirID('users',$User->id).'/resize_orig.png">';
	print "<hr>BW:<hr>";
	print '<img src="'.libCache::dirID('users',$User->id).'/resize_bw.png" >';
	print "<hr>Col:<hr>";
	print '<img src="'.libCache::dirID('users',$User->id).'/resize_col.png">';
	print "<hr>Borders removed:<hr>";
	print '<img src="'.libCache::dirID('users',$User->id).'/resize_wo_br.png">';
	print "<hr>New size:<hr>";
	print '<img src="'.libCache::dirID('users',$User->id).'/resize_new.png">';
	print "<hr>Borders:<hr>";
	print '<img src="'.libCache::dirID('users',$User->id).'/resize_new_br.png">';
	print "<hr>Enhanched borders:<hr>";
	print '<img src="'.libCache::dirID('users',$User->id).'/resize_new_br_enh.png">';
	
	print '</div>';
	libHTML::footer();
	
}

?>