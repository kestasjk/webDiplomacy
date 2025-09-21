<?php
/*
    Copyright (C) 2004-2009 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

?><h2>Verifica contro i bot</h2>

<form method="post" action="register.php">

	<ul class="formlist">

		<li class="formlisttitle">Codice di Verifica</li>
		<li class="formlistfield">
		        <img alt="EasyCaptcha image" src="contrib/easycaptcha.php" /><br />
		        <input type="text" name="imageText" />
		</li>
		<li class="formlistdesc">
			Inserendo questo codice ci aiuti a proteggere il sito dallo spam, e dagli script automatici
		</li>

		<li class="formlisttitle">indirizzo E-mail </li>
		<li class="formlistfield"><input type="text" name="emailValidate" value="<?php
		        if ( isset($_REQUEST['emailValidate'] ) )
					print htmlspecialchars($_REQUEST['emailValidate'], ENT_QUOTES, 'UTF-8');
		        ?>"></li>
		<li class="formlistdesc">
		 L'indirizzo serve per evitare doppie iscrizioni. <strong>Non</strong> verrà dato a terzi o usato per inviare spam 
		</li>
</ul>

<div class="hr"></div>

<p class="notice">
	<input type="submit" class="form-submit" value="Continua">
</p>
</form>