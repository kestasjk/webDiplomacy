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

/**
 * @package Base
 * @subpackage Forms
 */

?>
	<li class="formlisttitle">Indirizzo E-mail </li>
	<li class="formlistfield"><input type="text" name="userForm[email]" size="50" value="<?php
		if ( isset($_REQUEST['userForm']['email'] ) )
		{
			print $_REQUEST['userForm']['email'];
		}
		elseif( isset($User->email) )
		{
			print $User->email;
		}
		?>" <?php if ( isset($_REQUEST['emailToken']) ) print 'readonly '; ?> /></li>
	<li class="formlistdesc">Il tuo indirizzo di posta elettronica; <strong>non</strong> verrà usato per inviare spam e <strong>non</strong> verrà dato a terzi.</li>

	<li class="formlisttitle">Nascondi l'indirizzo e-mail:</li>
	<li class="formlistfield">
		<input type="radio" name="userForm[hideEmail]" value="Yes" <?php if($User->hideEmail=='Yes') print "checked"; ?>>Sì
		<input type="radio" name="userForm[hideEmail]" value="No" <?php if($User->hideEmail=='No') print "checked"; ?>>No
	</li>
	<li class="formlistdesc">
	  Scegli se rendere visibile il tuo indirizzo di posta agli altri giocatori.
	  Se decidi di renderlo visibile, sarà visualizzato come immagine per evitare
	  che sia preda dei bot automatici.
	</li>
	
	<li class="formlisttitle">Ricevi notifiche delle partite via mail:</li>
	<li class="formlistfield">
		<input type="radio" name="userForm[sendEmail]" value="Yes" <?php //if($User->sendEmail=='Yes') print "checked"; ?>>Sì
		<input type="radio" name="userForm[sendEmail]" value="No" <?php //if($User->sendEmail=='No') print "checked"; ?>>No
	</li>
	<li class="formlistdesc">
	  Vuoi ricevere aggiornamenti sulle tue partite in corso? Ad ogni cambio turno, pausa o ripresa di una partita. 
	</li>

       <li class="formlisttitle">Visualizza la lente sulla pagina della partita:</li>
	<li class="formlistfield">
		<input type="radio" name="userForm[showZoom]" value="Yes" <?php //if($User->showZoom=='Yes') print "checked"; ?>>Sì
		<input type="radio" name="userForm[showZoom]" value="No" <?php //if($User->showZoom=='No') print "checked"; ?>>No
	</li>
	<li class="formlistdesc">
	  Trasforma il cursore in una lente per ingrandire una certa area della mappa piccola e vedere le mosse dettagliate senza aprire altri link  
	</li>

	<li class="formlisttitle">Password:</li>
	<li class="formlistfield">
		<input type="password" name="userForm[password]" maxlength=30>
	</li>
	<li class="formlistdesc">
		La tua password su webDiplomacy. Inserisci qui una password nuova se vuoi cambiarla.
	</li>

	<li class="formlisttitle">Ripeti la password:</li>
	<li class="formlistfield">
		<input type="password" name="userForm[passwordcheck]" maxlength=30>
	</li>
	<li class="formlistdesc">
		Ripeti la password per evitare errori di battitura.
	</li>

	<input type="hidden" name="locale" value="English" />

	<li class="formlisttitle">Il tuo sito:</li>
	<li class="formlistfield">
		<input type="text" size=50 name="userForm[homepage]" value="<?php print $User->homepage; ?>" maxlength=150>
	</li>
	<li class="formlistdesc">
		<?php if ( !$User->type['User'] ) print '<strong>(Optional)</strong>: '; ?>
		il tuo blog, sito personale o sito preferito.
	</li>

	<li class="formlisttitle">Commento:</li>
	<li class="formlistfield">
		<TEXTAREA NAME="userForm[comment]" ROWS="3" COLS="50"><?php
			print str_replace('<br />', "\n", $User->comment);
		?></textarea>
	</li>
	<li class="formlistdesc">
		<?php if ( !$User->type['User'] ) print '<strong>(Optional)</strong>: '; ?>
		Il commento viene visualizzato nel profilo. Può anche essere il nome utente su Messenger, Skype o il numero di ICQ.
	</li>
<?php

/*
 * This is done in PHP because Eclipse complains about HTML syntax errors otherwise
 * because the starting <form><ul> is elsewhere
 */
print '</ul>

<div class="hr"></div>

<input type="submit" class="form-submit notice" value="Update">
</form>';

?>