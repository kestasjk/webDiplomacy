<?php

/**
 * @package Base
 * @subpackage EasyCaptcha
 */

putenv('GDFONTPATH=' . realpath('.'));
$fonts = array('VeraBd.ttf');

$time = time();

$alphabet = 'a_b_c_d_e_f_g_h_i_j_k_l_m_n_o_p_q_r_s_t_u_v_w_x_y_z';
$alphabet = explode('_', $alphabet);
shuffle($alphabet);

$captchaText = '';
for($i = 0; $i < 4; $i++ )
{
        $captchaText .= $alphabet[$i];
}

define('IN_CODE', 1);
require_once('../config.php');
setcookie('imageToken', md5(Config::$secret.$captchaText.$_SERVER['REMOTE_ADDR'].$time).'|'.$time, ['expires'=>null,'samesite'=>'Lax','path'=>'/']);

$width = strlen($captchaText)*30;
$height = 60;

$oImage = imagecreate($width, $height);
if( !$oImage ) die("imagecreate failed");

imagecolorallocate($oImage, 255, 255, 255);

$spacing = (int)($width / strlen($captchaText));

for ($i = 0; $i < 25; $i++) {
        $iLineColour = imagecolorallocate($oImage, rand(100, 250), rand(100, 250), rand(100, 250));
        imageline($oImage, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $iLineColour);
}

for($i=0; $i < strlen($captchaText); $i++)
{
            $sCurrentFont = 'VeraBd.ttf';

               $iTextColour = imagecolorallocate($oImage, rand(0, 100), rand(0, 100), rand(0, 100));


            // select random font size
            $iFontSize = rand(16, 25);

            // select random angle
            $iAngle = rand(-30, 30);

            // get dimensions of character in selected font and text size
            $aCharDetails = imageftbbox($iFontSize, $iAngle, $sCurrentFont, $captchaText[$i], array());

            // calculate character starting coordinates
            $iX = $spacing / 4 + $i * $spacing;
            $iCharHeight = $aCharDetails[2] - $aCharDetails[5];
            $iY = $height / 2 + $iCharHeight / 4;

            // write text to image
            imagefttext($oImage, $iFontSize, $iAngle, $iX, $iY, $iTextColour, $sCurrentFont, $captchaText[$i], array());
}

header("Content-type: image/png");
if (!imagepng($oImage)) die('imagepng failed');
//imagedestroy($oImage);
