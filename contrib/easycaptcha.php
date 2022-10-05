<?php

/**
 * @package Base
 * @subpackage EasyCaptcha
 */

require_once('php-captcha.inc.php');

$fonts = array('VeraBd.ttf');

$alphabet = 'a_b_c_d_e_f_g_h_i_j_k_l_m_n_o_p_q_r_s_t_u_v_w_x_y_z';
$alphabet = explode('_', $alphabet);
shuffle($alphabet);

$captchaText = '';
for($i = 0; $i < 4; $i++ )
{
	$captchaText .= $alphabet[$i];
}

$time = time();

define('IN_CODE', 1);
require_once('../config.php');

setcookie('imageToken', md5(Config::$secret.$captchaText.$_SERVER['REMOTE_ADDR'].$time).'|'.$time, ['expires'=>null,'samesite'=>'Lax']);

$oVisualCaptcha = new PhpCaptcha($fonts, strlen($captchaText) * 30, 60);
$oVisualCaptcha->UseColour(true);
$oVisualCaptcha->Create($captchaText);

?>
