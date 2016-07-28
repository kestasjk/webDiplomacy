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
<div class="content-bare content-board-header content-title-header">
<div class="pageTitle barAlt1">
	Crea una nuova partita
</div>
<div class="pageDescription barAlt2">
Inizia una nuova partita. Sei tu a deciderne il nome, la variante, quando durano i turni e quanto vale in termini di punti.
</div>
</div>
<div class="content content-follow-on">
<form method="post">
<ul class="formlist">

	<li class="formlisttitle">
	 Nome:
	</li>
	<li class="formlistfield">
		<input type="text" name="newGame[name]" value="" size="30">
	</li>
	<li class="formlistdesc">
		Il nome della tua partita
	</li>

	<li class="formlisttitle">
		Durata turno: (5 minuti - 10 giorni)
	</li>
	<li class="formlistfield">
		<select name="newGame[phaseMinutes]" onChange="document.getElementById('wait').selectedIndex = this.selectedIndex">
		<?php
			$phaseList = array(5, 10, 15, 20, 30,
				60, 120, 240, 360, 480, 600, 720, 840, 960, 1080, 1200, 1320,
				1440, 2160, 2880, 4320, 5760, 7200, 8640, 10080, 14400, 1440+60, 2880+60*2);

			foreach ($phaseList as $i) {
				$opt = libTime::timeLengthText($i*60);

				print '<option value="'.$i.'"'.($i==1440 ? ' selected' : '').'>'.$opt.'</option>';
			}
		?>
		</select>
	</li>
	<li class="formlistdesc">
		Il numero massimo di ore, dato ai giocatori per la diplomazia e l'inserimento degli ordini di cisacun turno.<br />
		Tempi più lunghi significano decisioni più accurate e diplomazia più attenta, ma fanno durare di più le partite. 
    Tempi più corti rendono le partite più veloci, ma i giocatori devono controllare la partita frequentemente se non si volgiono ritrovare in sommossa.<br /><br />

		<strong>Standard:</strong> 24 ore/1 giorno
	</li>

	<li class="formlisttitle">
		Puntata: (5<?php print libHTML::points(); ?>-
			<?php print $User->points.libHTML::points(); ?>)
	</li>
	<li class="formlistfield">
		<input type="text" name="newGame[bet]" size="7" value="<?php print $formPoints ?>" />
	</li>
	<li class="formlistdesc">
		La puntata necessaria per iscriversi a questa partita. Questa è la quantità di punti che tutti i giocatori, te incluso, metterete sul "piatto"
		 (<a href="points.php" class="light">Cosa sono i punti?</a>).<br /><br />

		<strong>Standard:</strong> <?php print $defaultPoints.libHTML::points(); ?>
	</li>

	<li class="formlisttitle">
		<img src="images/icons/lock.png" alt="Private" /> Password (optionale):
	</li>
	<li class="formlistfield">
		<ul>
			<li>Password: <input type="password" name="newGame[password]" value="" size="30" /></li>
			<li>Confirm: <input type="password" name="newGame[passwordcheck]" value="" size="30" /></li>
		</ul>
	</li>
	<li class="formlistdesc">
		<strong>Questa opzione non è obbligatoria.</strong> Solo le persone che conoscono la password saranno in grado di iscriversi alla partita.<br /><br />

		<strong>Standard:</strong> Nessuna password
	</li>
</ul>

<div class="hr"></div>

<div id="AdvancedSettingsButton">
<ul class="formlist">
	<li class="formlisttitle">
		<a href="#" onclick="$('AdvancedSettings').show(); $('AdvancedSettingsButton').hide(); return false;">
		Apri le opzioni avanzate
		</a>
	</li>
	<li class="formlistdesc">
	 Le opzioni avanzate permettono maggiori impostazioni della partita.
		<br /><br />

		Le impostazioni Standard sono consigliate ai <strong>giocatori nuovi</strong>.
	</li>
</ul>
</div>

<div id="AdvancedSettings" style="<?php print libHTML::$hideStyle; ?>">

<h3>Impostazioni Avanzate</h3>

<ul class="formlist">
<?php
if( count(Config::$variants)==1 )
{
	foreach(Config::$variants as $variantID=>$variantName) ;

	$defaultVariantName=$variantName;

	print '<input type="hidden" name="newGame[variantID]" value="'.$variantID.'" />';
}
else
{
?>
	<li class="formlisttitle">Variante:</li>
	<li class="formlistfield">
	<?php
	$checkboxes=array();
	$first=true;
	foreach(Config::$variants as $variantID=>$variantName)
	{
		if( $first )
			$defaultVariantName=$variantName;
		$Variant = libVariant::loadFromVariantName($variantName);
		$checkboxes[] = '<input type="radio" '.($first?'checked="on" ':'').'name="newGame[variantID]" value="'.$variantID.'"> '.$Variant->link();
		$first=false;
	}
	print '<p>'.implode('</p><p>', $checkboxes).'</p>';
	?>
	</li>
	<li class="formlistdesc">
		Scegli la variante, tra quelle disponibili su questo server.<br /><br />

		Oppure clicca il nome della variante, per maggiori informazioni sulla stessa.<br /><br />

		<strong>Standard:</strong> <?php print $defaultVariantName; ?>
	</li>
<?php
}
?>

	<li class="formlisttitle">Divisione dei punti:</li>
	<li class="formlistfield">
		<input type="radio" name="newGame[potType]" value="Points-per-supply-center" checked > Per Numero di Centri<br />
		<input type="radio" name="newGame[potType]" value="Winner-takes-all"> Vincitore Intasca la Posta (VIP)
	</li>
	<li class="formlistdesc">
		Decidi se la posta in gioco verrà divisa tra tutti i sopravissuti in base al numero di centri posseduti, o se il vincitore della partita si intasca l'intero ammontare.
     (<a href="points.php#ppscwta" class="light">per saperne di più</a>).<br /><br />

		<strong>Standard:</strong> Per Numero di Centri
	</li>

	<li class="formlisttitle">
		Giocatori Anonimi:
	</li>
	<li class="formlistfield">
		<input type="radio" name="newGame[anon]" value="No" checked>No
		<input type="radio" name="newGame[anon]" value="Yes">Sì
	</li>
	<li class="formlistdesc">
		Se scegli sì, i nomi dei giocatori della partita non saranno visibili fino a che la partita non finisce.<br /><br />

		<strong>Standard:</strong> No, i giocatori non sono anonimi
	</li>

	<li class="formlisttitle">
		Diplomazia della partita:
	</li>
	<li class="formlistfield">
		<input type="radio" name="newGame[pressType]" value="Regular" checked>Permetti tutto
		<input type="radio" name="newGame[pressType]" value="PublicPressOnly">Solo messaggi Globali. Niente comunicazioni personali
		<input type="radio" name="newGame[pressType]" value="NoPress">Nessuna diplomazia
	</li>
	<li class="formlistdesc">
		Proibisci solo i messaggi personali tra giocatori, oppure ogni forma di diplomazia (Militare).

		<br /><br /><strong>Default:</strong> Permetti tutte le comunicazioni
	</li>

	<li class="formlisttitle">
		Tempo di attesa dei nuovi giocatori: (5 minuti - 10 giorni)
	</li>
	<li class="formlistfield">
		<select id="wait" name="newGame[joinPeriod]">
		<?php
			foreach ($phaseList as $i) {
				$opt = libTime::timeLengthText($i*60);

				print '<option value="'.$i.'"'.($i==1440 ? ' selected' : '').'>'.$opt.'</option>';
			}
		?>
		</select>
	</li>
	<li class="formlistdesc">
		Tempo utile agli altri giocatori per iscriversi a questa partita. Se entro questa scadenza la partita non avrà raccolto un numero sufficiente di giocatori, sarà cancellata e bisognerà crearne una nuova. Per turni brevi (5 minuti), è consigliabile dare una scadenza maggiore a questo valore.

		<br /><br /><strong>Standard:</strong> Stesso tempo di un turno
	</li>
</ul>

</div>

<div class="hr"></div>

<p class="notice">
	<input type="submit" class="form-submit" value="Create">
</p>
</form>
