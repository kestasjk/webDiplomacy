<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

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
 * @subpackage Static
 */
?>
<div>
<?php
$credits = array(
	array('
		<a href="http://www.wizards.com/default.asp?x=ah/prod/diplomacy">Avalon Hill</a>
	','
		Il gioco in scatola di Diplomacy.<br />
		Se vi piace webDiplomacy, provate anche il gioco da tavolo
	')

	,array('<a href="http://kestas.kuliukas.com/">Kestas Kuliukas</a>
	','Creatore &amp; sviluppatore')

	,array('Sconosciuto
	','Le icone del carro armato e della corazzata<br />
	Rilasciate sotto la licenza <a href="http://www.opensource.org/licenses/gpl-license.php" class="light">GNU General Public License</a>')

	,array('Sconosciuto
	','Mappa di gioco a tutto schermo')

	,array('Algis Kuliukas
	','guru dell\'SQL')

	,array('<a href="https://sourceforge.net/sendmessage.php?touser=1295433">paranoidjpn</a>
	','Traduzione giapponese, test vari, supporto UTF-8, sviluppo mappa piccola')

	,array('Bitstream
	','Font usati nella mappa grande.<br />
	Rilasciati sotto la <a href="contrib/BVFL.txt" class="light">Bitsream Vera Fonts License</a>')

	,array('<a href="http://www.xcelco.on.ca/~ravgames/dipmaps/">Rob Addison</a>
	','Immagine della mappa piccola')

	,array('mrlachette, Magilla, arning
	','Debig e test delle versioni prima della 0.72')

	,array('Gli utenti, i donatori
	','Proposte, suggerimenti, report dei bug, aiuto sui forum e donazioni')

	,array('Lucas Kruijswijk
	','Adattamento test DATC')

	,array('figlesquidge
	','sviluppatore mappa SVG, patch coste differenti')

	,array('<a href="http://sourceforge.net/users/fallingrock/">Chris Hughes</a>
	','sviluppo di webDiplomacy su Facebook, turni di gioco variabili, impaginazione liste partite')

	,array('<a href="http://www.webdiplomacy.net/profile.php?userID=3013">thewonderllama</a>
	','Fix degli ordini di piazzamento delle unità, tornei GFDT')

	,array('Chrispminis, figlesquidge, dangermouse, thewonderllama, TheGhostMaker
	','moderatori del sito webdiplomacy.net , sviluppo delle regole del sito')

	,array('TheGhostMaker
	','Sviluppo e manutenzione del Ghost-rating, aiuto con il GFDT, abbellimento della home page')

	,array('jayp
	','Tante caratteristiche nuove per la Versione 0.91. Sviluppatore del codice per le varianti')

	,array('Carey Jensen (gilgatex)
	','Sviluppatore varianti, sviluppatore sito goondip.com')

	,array('Oliver Auth (Sleepcap)
	','Creatore di varianti'),

	array('
		La libreria JavasCript <a href="http://www.prototypejs.org/">Prototype</a>
	','
		La libreria JavaScript utilizzata dal sito
	'),
	
	array('
		Traduzione italiana: <a href="http://webdiplomacy.it">WebDiplomacy.it</a>
	','
		La traduzione di tutto il sito in lingua italiana
	'),

	array('
		Alex Lebedev
	','
		Sponsor sviluppo traduzione.
	')
	);

	$leftColumn=array();
	$rightColumn=array();

	$half=ceil(count($credits)/2);
	for($i=0;$i<$half;$i++)
	{
		$leftColumn[]=$credits[$i];
		if ( isset($credits[$i+$half]) )
			$rightColumn[]=$credits[$i+$half];
	}

	print '<div class="rightHalf"><ul class="formlist">';
	foreach($rightColumn as $credit)
		print '<li class="formlisttitle">'.$credit[0].'</li><li class="formlistdesc">'.$credit[1].'</li>';
	print '</ul></div>';

	print '<div class="leftHalf"><ul class="formlist">';
	foreach($leftColumn as $credit)
		print '<li class="formlisttitle">'.$credit[0].'</li><li class="formlistdesc">'.$credit[1].'</li>';
	print '</ul></div>';

?>
<div style="clear:both"></div>
</div>