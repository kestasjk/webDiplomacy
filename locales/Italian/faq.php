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
 * @subpackage Static
 */

$faq = array(
"Sono nuovo qui!" => "Sub-section",
"Di che cosa parla questo sito?" => "La maniera più semplice per capirlo è di dare un occhio a
	<a href='http://webdiplomacy.net/doc/0.90-screenshot.png' class='light'>una immagine del gioco</a>. Se il concetto non è ancora ben chiaro guarda <a href='intro.php' class='light'>l'introduzione a webDiplomacy</a>.",
"Come faccio a giocare?" => "Le regole sono abbastanza semplici da capire, ma se dovessi essere in dubbio riguardo a qualcosa leggi
	<a href='intro.php' class='light'>l'introduzione a webDiplomacy</a>, e sentiti libero di chiedere per aiuto o spiegazioni nel <a href='http://forum.webdiplomacy.it/' class='light'>forum pubblico</a>.",
"Qual è la licenza del programma?" => "La <a href='AGPL.txt' class='light'>GNU Affero General License</a>
	(<a href='http://www.opensource.org/licenses/agpl-v3.html' class='light'>Open Source Initiative</a> approved),
	che in sostanza spiega che puoi scaricare e modificare il codice come piace a te e metterlo nel tuo sito ma che non puoi rivendicare di averlo scritto tu e che devi riferire di ogni modifica alla community.<br /><br />
	Vedi <a href='credits.php' class='light'>riferimenti</a> per informazioni in merito ai dettagli che sono ricadono sotto licenze differenti.",

"Questo programma ha qualcosa a che fare con phpDiplomacy?" => "Questo programma era solito essere chiamato phpDiplomacy sino alla versione 0.9.
	Ci spiace per la confusione, anche noi odiamo i cambiamenti di nome, ma per il nostro user-base il vecchio prefisso 'php' non si è rivelato essere l'etichetta immediatamente riconoscibile che doveva essere nelle nostre intenzioni iniziali.",


"L'interfaccia" => "Sub-section",
"Cosa sono quei cerchi verdi accanto al nome della persona?" => "L'icona verde appare quando un giocatore è on-line sul server. Questo significa che se il giocatore ha avuto accesso al server negli ultimi ~10-15 minuti questi avranni l'icona verde accanto al loro nome.",
"E questo cosa sarebbe? (<img src='images/icons/online.png' />, <img src='images/icons/mail.png' />, etc)" => "Se vedi un/una icona/bottone/immagine che non sai cosa significhi prova a passarci sopra il mouse, potrebbe uscire una nota con una breve spiegazione.
Se non dovesse succedere, sentiti libero di chiedere sul <a href='http://forum.webdiplomacy.it/' class='light'>forum</a>.",
"Perchè i miei ordini cambiano colore da verde a rosso?" => "Gli ordini in rosso sono ancora da salvare; se vedi molti ordini in rosso dovresti salvare, altrimenti potresti dimenticartene e perderli chiudendo la finstra del browser o chattando con qualcuno.",
"Cosa significano 'Salva' e 'Pronto' (Save e Ready)?" => "'Salva' salva i tuoi ordini; i tuoi ordini non ancora salvati, in rosso, diventeranno verdi non appena saranno salvati definitivamente. 'Pronto' significa che hai finito di inserire i tuoi ordini e che sei pronto per continuare con il turno successivo. Se ognuno è 'Pronto' il gioco prosegue proprio in quell'istante, velocizzando la partita.",
"Cosa sono i codici che possono aggiungere HTML nei messaggi del forum? (icone, collegamenti a partite, ecc)" => "Spesso nei forum le persone discutono o vogliono aggiungere collegamenti a partite/account di utenti/altre discussioni nel forum. Per rendere questo più semplice alcuni codici sono riconosciuti automaticamente e rimpiazzati con il corretto link/simbolo:
	<ul><li><strong>'<em>[number]</em> punti'</strong>/<strong>'<em>[number]</em> D'</strong> risulterà in
	<strong>'punti'</strong> / <strong>'D'</strong> venendo rimpiazzati con il simbolo dei punti (".libHTML::points().").</li>
	<li><strong>'gameID=<em>[number]</em>'</strong> / <strong>'threadID=<em>[number]</em>'</strong> / <strong>'userID=<em>[number]</em>'</strong> avranno un link appropriato con il/la partita/discussione/profilo sostituiti nel messaggio.</li></ul>",
"Perchè alcune cose sembrano cambiare non appena la pagina si è caricata?" => "Dopo che la pagina si è caricata parte JavaScript, andando a fare alcune modifiche
	(per esempio mettendo l'orario GMT/UTC nel tuo computer, rendendo in grassetto i tuoi interventi, ecc) che implementano la pagina.",

"Regole del gioco" => "Sub-section",
"Voglio imparare le basi del gioco" => "Vedi la <a href='intro.php' class='light'>pagina introduttiva</a>.",
"Voglio imparare le regole avanzate del gioco" => "Vedi <a href='http://www.wizards.com/avalonhill/rules/diplomacy.pdf' class='light'>il regolamento di Avalon Hill</a>.",
"Voglio imparare i dettagli sulle regole del gioco" => "Noi usiamo il DATC per risolvere esattamente ogni sorta di situazioni intricate, nei casi in cui c'è ambiguità nel regolamento. (Questo tipo di cose generalmente non capitano spesso in una partita, comunque.)<br />
	Vedi la nostra pagina DATC <a href='datc.php' class='light'>here</a>.",
"Se qualcuno deve distruggere una unità ma non ha inserito gli ordini su quale unità distruggere, quale unità è distrutta?" => "Si risolve come raccomanda DATC:
	L'unità più lontana dai tuoi Centri di rifornimento di partenza.La distanza è definita come il minor numero di mosse necessario per arrivare alla posizione dell'unità partendo dai Centri di partenza. Quando si calcola il minor numero di mosse, le armate si considerano come se si potessero muovere attraversando i mari, le flotte invece si considerano soltanto secondo il loro naturale movimento attraverso i mari e le coste. Se ci dovessero essere due unità con la medesima distanza da un Centro di partenza, si rimuove l'unità che giace sul territorio che viene prima secondo l'ordine alfabetico.",
"Se un convoglio è attaccato, il convoglio fallisce?" => "No; perchè un ordine di convoglio non abbia successo è necessario che la flotta che convoglia venga costretta a ritirarsi, e non ci devono essere altri convogli grazie al quale l'armata riesca comunque a essere convogliata.",
"Cosa succede se ordino di costruire/distruggere due unità nello stesso territorio?" => "Il primo ordine di costruzione sarà accettato, il secondo no",
"Cosa succede se due unità ritirano nello stesso territorio?" => "Entrambe le unità saranno distrutte",
"Posso attaccare e costringere a ritirarsi le mie unità?" => "No; non puoi costringere al ritiro le tue stesse unità nè supportare un attacco verso le tue stesse unità.",
"Ci sono altre regole che dovrei tenere a mente?" => "C'è una lista completa delle regole sulla pagina delle <a href='rules.php' class='light'>regole</a>,
	che elenca alcune altre regole che dovrai seguire per aiutarci a mantenere il sito divertente per chiunque.",

"Punti" => "Sub-section",
"What happens when I run out?" => "You can't run out: Your total number of points include the number of points which you have 'bet' into games you're currently playing in,
	as well as the points you have in your account. Your total number of points never falls below 100; whenever it does
	you're given your points back.<br /><br />
	To put it another way; any player who isn't currently playing in any games will always have at least 100 points, so
	you won't run out!",
"How are the points split in a draw?" => "In a draw the points are split evenly among all the survivors still in the game,
	regardless of the number of supply centers each player has.<br/>
	Read <a href='points.php' class='light'>the points guide</a> for more info about the points system.",
"I have an idea for a better system" => "We constantly get new ideas for the points system, but usually they're either missing
	out in some aspect (the points system serves multiple functions), or they improve in one area but are worse in another.<br /><br />
	The points system does the job fine, so it's unlikely to be replaced.
	(See <a href='http://forum.webdiplomacy.net/viewtopic.php?p=288#p288' class='light'>this page</a> for an
	explanation regarding the role of the existing system, and what a replacement would have to do.)<br /><br />
	There's no real way to express how good a player really is in a single number, the points system as it is is
	probably good enough for now, and there's definitely no agreement on what would replace it.",
"Can you draw the game, but give 2/3rds of the points to this player and ..." => "Draws can only be given one way; an even split to
	all survivors.",

"Bugs" => "Sub-section",
"My game has crashed!" => "Sometimes (usually only shortly after code updates) a software bug or server error may occur while a
	game is being processed.
	When this happens the problem is detected, all changes are undone, and the game is marked as crashed.<br /><br />
	Admins will see a message whenever a game crashes, and information about the crash is saved so that the problem that caused it can be fixed quickly.
	Once a mod or admin has marked the game as OK the game will continue where it left off again.<br /><br />

	If your game has been crashed for a long time try asking about it in the forum.",
"The phase ends \"Now\"? \"Processing has been paused\"?" => "When the server detects that no games have processed for a while
	(over 30 minutes or so), or a moderator/admin sees a problem and his the panic button, all game processing is disabled until
	the problem is resolved.<br />
	After the all-clear is given games will usually be given time to make up for any during which orders couldn't be entered, and
	processing will resume. Until that point if a game says it will be processed 'Now' that means it would process now, except
	processing is disabled.<br /><br />

	You may also see it if you a games timer counted down to 0 while you were viewing the page, in which
	case you should refresh the page to view the newly processed game.",
"I didn't enter those orders!" => "Occasionally we get this complaint, but every time we have checked the
	input logs to see what order was actually entered it turns out to be the mistaken order.
	Also the mistaken orders are often the 'Bulgaria'/'Budapest' sort of mistake which are easier to
	imagine human error than a bug.<br /><br />
	Try finalizing your orders and then checking them over, so you can be sure of what you entered.",
"Someone says their orders messed up, and I'm paying the price!" => "
	Unfortunately it does seem that sometimes people will claim that their orders came out wrong to cover up the intention of
	their actions. (e.g. \"I was going to stab you, then read your message and changed my orders so I wasn't going to stab you,
	but my old orders came out instead of the new ones! Oh so sorry about that!\")<br /><br />

	This is against <a href='rules.php' class='light'>the rules</a>, as it makes work for admins over made up bugs. When someone
	tells you a bug caused a mistake in their orders you should reserve some skepticism, and remember that the official server alone
	receives and processes over 20,000 orders per day (as of Feb 2010) without mistake every minute of every day for years on
	end, so sudden bugs which change whole order-sets around simply don't seem to genuinely happen ever, despite checking every
	single report.
",
"My orders gave the wrong results!" => "Before reporting this as a bug double check that you entered your orders correctly and you're
	not misunderstanding the rules. 99.999% of the time \"adjudicator bugs\" turn out to be a misunderstanding.<br />
	If you're still positive there's a problem let us know in the <a class='light' href='http://forum.webdiplomacy.it/'>forum</a>.",
"A part of the site looks wrong in an alternative browser" => "webDiplomacy isn't currently completely web standards compliant,
	so there may be glitches. We would like to get webDiplomacy working on everything (within reason) but we need users
	of alternative browsers to let us know what's wrong and tell us how to make it look right in that browser.",
"This site seems to slow my computer down" => "See Help > What is Plura? for a likely cause and fix.",

"Feature Requests" => "Sub-section",
"Better forum" => "A better forum would be good, but getting it to fit in and appear as part of webDiplomacy, rather than just
	a separate site, is difficult, and would likely use more server resources than our efficient but lightweight built-in forum.<br />
	At the moment we are trying to improve our existing forum in small incrememnts.",
"A point and click interactive map" => "This is being worked on, but progress is slow. If you know JavaScript and SVG/Canvas why not
	carry on the work on the <a href='http://forum.webdiplomacy.net/' class='light'>development forum</a>?",
"Translations" => "Eventually translations will be supported, but it is a long process and not a top priority.",
"New variants" => "If a variant has lasting appeal, is well balanced, isn't gimmicky, has been tried and tested on another server, and was
	created by a reputable developer, then it's up for consideration to be included in the standard release.<br />
	You can discuss this in the variants section of the webDiplomacy
	<a href='http://forum.webdiplomacy.net/' class='light'>developers forum</a>.<br /><br />

	Also creating your own variants or porting
	existing variants to the webDiplomacy variants system is easier than ever, from simple map-change variants all the
	way to strange rule-changing variants, the system is flexible enough to accomadate your varaint ideas.
	",
"Can I suggest a feature?" => "Feature suggestions are best made in the <a class='light' href='http://forum.webdiplomacy.net/'>developer forums</a>,
	elsewhere they're likely to be missed. Remember that unless you can back-up your suggestion with code even good ideas may not get far.",

"Helping out" => "Sub-section",
"Can I help develop the software?" => "You sure can: if you're an HTML/CSS/JavaScript/PHP 5/MySQL/SVG/Canvas developer,
	graphics/icon artist, or want to learn, check out the <a class='light' href='http://webdiplomacy.net/developers.php'>dev info</a>,
	and if you get lost you can get help/discuss ideas in the <a class='light' href='http://forum.webdiplomacy.net/'>developer forums</a>.",
"Can I donate?" => "If you enjoy the site and want to help out, but can't code, you can donate to the project via
PayPal, and this is student-ware so all donations are appreciated. :-)
<div style='text-align:center'>
<form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
<input type='hidden' name='cmd' value='_s-xclick'>
<input type='image' src='https://www.paypal.com/en_US/i/btn/x-click-but21.gif' border='0' name='submit' alt='Make payments with PayPal - it's fast, free and secure!'>
<img alt='' border='0' src='https://www.paypal.com/en_AU/i/scr/pixel.gif' width='1' height='1'>
<input type='hidden' name='encrypted' value='-----BEGIN PKCS7-----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-----END PKCS7-----
'>
</form></div>A big thanks to all the past donors who helped make all '07-'08 server fees community paid!",
"How else can I help?" => "Tell your friends about webDiplomacy, put links on your website, help new players out in the forums,
	and give helpful feedback to developers. Thanks!",

"Map" => "Sub-section",
"Why are some orders missing from the map?" => "Not all orders are drawn on the small map. Below the small map there is a set of icons;
	the one in the middle (<img src='images/historyicons/external.png' alt='example' />) opens up the large map, which contains all orders.<br/>
	Also at the bottom of the board page is a link to open up a textual list of all the orders entered in the game, if you can't see
	something in the large map.",
"I can't tell the difference between Germany and Austria" => "Color-blind people may have trouble distinguishing Germany and Austria's
	colors. We hope to fix this problem in the future."
);

if( isset(Config::$faq) && is_array(Config::$faq) && count(Config::$faq) )
{
	$faq["Server-specific"]="Sub-section";
	$faq["What is this section?"]="webDiplomacy is free, open-source software, and there are several servers running webDiplomacy code.
			This section is for questions which may be specific to this particular installation, whereas the rest of it applies to all
			installations. (e.g. This section may contain Q&amp;A regarding who runs and pays for this server, or relate to the features
			which set this server apart from the official installation if any.)";

	foreach(Config::$faq as $Q=>$A)
		$faq[$Q]=$A;
}


$i=1;

print libHTML::pageTitle('Frequently Asked Questions','Answers to the questions people often ask in the forums; click on a question to expand the answer.');


$sections = array();
$section=0;
foreach( $faq as $q => $a )
	if ( $a == "Sub-section" )
		$sections[] = '<a href="#faq_'.$section++.'" class="light">'.$q.'</a>';
print '<div style="text-align:center; font-weight:bold"><strong>Sections:</strong> '.implode(' - ', $sections).'</div>
	<div class="hr"></div>';

$section=0;
foreach( $faq as $q => $a )
{
	if ( $a == "Sub-section" )
	{
		if( $section ) print '</ul></div>';

		print '<div><p><a name="faq_'.$section.'"></a><strong>'.$q.'</strong></p><ul>';

		$question=1;
		$section++;
	}
	else
	{
		print '<li><div id="faq_answer_'.$section.'_'.$question.'">
			<a class="faq_question" name="faq_'.$section.'_'.$question.'"
			onclick="FAQShow('.$section.', '.$question.'); return false;" href="#">'.$q.'</a>
			<div class="faq_answer" style="margin-top:5px; margin-bottom:15px;"><ul><li>'.$a.'</li></ul></div>
			</div></li>';
		$question++;
	}
}
print '</ul></div>
</div>';

?>
<script type="text/javascript">
function FAQHide() {
	$$('.faq_question').map( function (e) {e.setStyle({fontWeight:'normal'});} );
	$$('.faq_answer').map( function (e) {e.hide();} );
}
function FAQShow(section, question) {
	FAQHide();
	$$('#faq_answer_'+section+'_'+question+' .faq_answer').map(function (e) {e.show();});
	$$('#faq_answer_'+section+'_'+question+' .faq_question').map(function (e) {e.setStyle({fontWeight:'bold'});});
}
</script>
<?php libHTML::$footerScript[] = 'FAQHide();'; ?>
