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
<div class="datc">
<p>
Questi sono i risultati del test <a href="http://web.inter.nl.net/users/L.B.Kruijswijk/">DATC</a> di webDiplomacy, che ipotizza un certo numero di scenari più o meno complicati
e li fa eseguire dal motore del sito, per verificarne il corretto funzionamento in fase di aggiornamento delle mappe.
<br />
Al momento sono stati eseguiti test solo sugli ordini di movimento (Primavera-Autunno). Le fasi delle ritirate e delle costruzioni, anche se pienamente funzionanti, non sono state sottoposte a nessun test. 
</p>
<p>
'Corretto' significa che il test svolto ha avuto successo. 'Non Corretto' significa che il test non è stato eseguito o che ha generato un risultao inaspettato 
(non ci dovrebbe essere nessun risultato del genere sui siti funzionanti) e 'Invalido', che significa che il test non è applicabile a webdiplomacy.<br />
La presenza di test 'Invalidi' influenza il buon funzionamento del sito, perchè certi test si riferiscono alla possibilità di inserire volontariamente ordini errati: opzione totalmente assente su webDiplomacy.
</p>

<a name="sections"></a><h4>Sezioni</h4>
<p>
I test sono suddivisi nelle seguenti sezioni:
<?php
$sections=array(
	1=>array('6.A.','TEST, CONTROLLI BASE'),
	2=>array('6.B.','TEST, CONTROLLI SULLE COSTE'),
	3=>array('6.C.','TEST, MOVIMENTI CIRCOLARI'),
	4=>array('6.D.','TEST, SUPPORTI E RITIRATE'),
	5=>array('6.E.','TEST, BATTAGLIE TESTA A TESTA E GUARNIGIONE ASSEDIATA'),
	6=>array('6.F.','TEST, TRASPORTO'),
	7=>array('6.G.','TEST, TRASPORTO NEI TERRITORI ADIACENTI'),
	8=>array('webDip intro',l_t('webDiplomacy: immagini introduzione al gioco')),
	9=>array('webDip tests',l_t('webDiplomacy: casi di test specifici'))
);
print '<ul>';
foreach( $sections as $sectionID=>$section )
	print '<li><a href="#section'.$sectionID.'">'.$section[0].'</a> - '.$section[1].'</li>';
print '</ul>';
?>
</p>

<a name="choices"></a><h4>Opzioni</h4>
<div id="showchoices">
<p>Alcuni test DATC possono avere più di un risultato corretto in base alle scelte effettuate. <a class="light" href="#" onclick="$('choices').show(); $('showchoices').hide(); return false;">Mostra i dettagli delle opzioni DATC</a></p>
</div>
<div id="choices" style="<?php print libHTML::$hideStyle; ?>">
<p>
Ciascun test è stato effettuato scegliendo l'opzione raccomandata. Risultati:<br />
<ul>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.1">4.A.1</a> - B - Il trasporto fallisce quando tutte le rotte sono interrotte</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.2">4.A.2</a> - D - Il paradosso di Szykman della regola dei trasporti</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.3">4.A.3</a> - D - "Via mare" può essere specificato (ed è sempre esplicito)</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.4">4.A.4</a> - A - Attaccare l'unità che supporta un movimento contro di te non taglia il supporto, anche se attacchi il supporto tramite un trasporto</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.5">4.A.5</a> - B - Se un'unità attacca con successo il territorio adiacente mediante trasporto, l'unità sloggiata può ritirarsi nel territorio dal quale proviene l'attacco</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.6">4.A.6</a> - A - La rotta del trasporto non può essere esplicita</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.7">4.A.7</a> - B - L'unità sloggiata può influenzare il territorio dal quale è partito l'attacco se detto attacco è effettuato mediante trasporto.</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.B">4.B</a> - Non valido</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.C">4.C</a> - Non valido</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.1">4.D.1</a> - Non valido</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.2">4.D.2</a> - Non valido</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.3">4.D.3</a> - Non valido</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.4">4.D.4</a> - Non valido</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.5">4.D.5</a> - B - Se sono presenti più ordini di costruzione per una sola provincia, il primo ordine verrà usato, mentre i seguenti saranno ignorati.</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.6">4.D.6</a> - B - Se sono presenti più ordini di distruzione per un solo territorio, il primo ordine è quello valido, mentre gli altri saranno scartati.</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.7">4.D.7</a> - A - Si può rimandare la costruzione ad un altro anno</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.8">4.D.8</a> - D - Durante la sommossa, le unità sono rimosse calcolando la loro distanza dai centri di produzione patri. Per le armate i territori terrestri o marini hanno la stessa valenza, mentre per le flotte si consideranno solo i territori di mare e quelli costieri. In caso di uguale distanza, verrà preso in considerazione l'ordine alfabetico del nome della provincia.</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.9">4.D.9</a> - B - I giocatori possono appoggiare in difesa le unità di nazioni in sommossa</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.1">4.E.1</a> - D - Solo gli ordini validi nella situazione attuale sono validi</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.2">4.E.2</a> - Non valido</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.3">4.E.3</a> - B - Ordini impliciti non permessi</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.4">4.E.4</a> - B - Ordini perpetui non permessi</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.5">4.E.5</a> - C - Ordini proxy non permessi</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.6">4.E.6</a> - Non valido</li>
</ul>
</p>
</div>

</div>

<?php

libHTML::pagebreak();

$tabl = $DB->sql_tabl("SELECT testID, testName, status, testDesc FROM wD_DATC ORDER BY testID");

print '
<div class="datc">
<a name="tests"></a><h4>Tests</h4>
<table>
	';
$alternate=2;
$lastSectionID=-1;
while ( list($id, $name, $status, $description) = $DB->tabl_row($tabl) )
{
	$alternate = 3-$alternate;

	$sectionID = floor($id/100);
	if( $sectionID != $lastSectionID )
	{
		print '<tr class="replyalternate'.$alternate.'">
<th>'.$sections[$sectionID][0].'</th>
<th><a name="section'.$sectionID.'"></a>'.$sections[$sectionID][1].'</th>
</tr>';
		$alternate = 3-$alternate;
	}
	$lastSectionID = $sectionID;

	if( $status=='Invalid' )
		$image = '(Invalid test)';
	elseif( $status=='NotPassed' )
		$image = 'Test not passed!';
	else
		$image = '
<a href="#" onclick="$(\'testimage'.$id.'\').src=\'datc/maps/'.$id.'-large.map\'; return false;">'.
			'<img id="testimage'.$id.'" src="'.STATICSRV.'datc/maps/'.$id.'-large.map-thumb" alt="Test ID #'.$id.' map thumbnail" />'.
			'</a>
			';

	$details = '<a name="test'.$id.'" href="http://web.inter.nl.net/users/L.B.Kruijswijk/#'.$name.'">'.
			$name.'</a> - '.$status.'<br />'.$description;

	print '
<tr class="threadalternate'.$alternate.'">
<td><p class="notice">'.$image.'</p></td>
<td><p>'.$details.'</p></td>
</tr>
		';
}
print '</table>';
print '</div>';
print '</div>';
libHTML::footer();
