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


$faq = array();

/*if( isset(Config::$faq) && is_array(Config::$faq) && count(Config::$faq) )
{
	$faq[""]="Sub-section";
	foreach(Config::$faq as $Q=>$A)
		$faq[$Q]=$A;
}*/

$globalFaq = array(
"Sono nuovo qui" => "Sub-section",
"Cos'è questo sito?" => "Su questo sito è possibile giocare online al gioco da tavolo Diplomacy. Diplomacy è un gioco strategico senza dadi che premia la capacità di comunicazione e diplomazia. Ecco una breve <a href='intro.php' class='light'>introduzione</a> alle regole del Diplomacy.",
"Come faccio a giocare?" => "Le regole sono abbastanza semplici da capire, ma se dovessi essere in dubbio riguardo a qualcosa leggi <a href='intro.php' class='light'>l'introduzione a webDiplomacy</a>, e sentiti libero di chiedere per aiuto o spiegazioni nel <a href='http://forum.webdiplomacy.it' class='light'>forum pubblico</a>.",
"Qual'è la licenza del software di questo sito?" => "La <a href='AGPL.txt' class='light'>GNU Affero General License</a>
	(<a href='http://www.opensource.org/licenses/agpl-v3.html' class='light'>Open Source Initiative</a> approved),
	che in sostanza spiega che puoi scaricare e modificare il codice come piace a te e metterlo nel tuo sito
	ma che non puoi rivendicare di averlo scritto tu e che devi mettere a disposizione della community ogni modifica da te effettuata.<br /><br />
	Vedi i <a href='credits.php' class='light'>riconoscimenti</a> per informazioni in merito ai dettagli che ricadono sotto licenze differenti.",

"Questo software ha qualcosa a che fare con phpDiplomacy?" => "Fino alla versione 0.9 il nome di questo programma era in effetti phpDiplomacy. Ci spiace per la confusione; anche noi odiamo i cambiamenti di nome, ma il vecchio prefisso 'php' non si è rivelato essere l'etichetta immediatamente riconoscibile che doveva essere nelle nostre intenzioni iniziali.",


"L'interfaccia" => "Sub-section",
"Cosa sono quei cerchi verdi accanto al nome della persona?" => "L'icona verde appare quando un giocatore è on-line sul server. Questo significa che se il giocatore ha avuto accesso al server negli ultimi ~10-15 minuti questi avranno l'icona verde accanto al loro nome.",
"E questo cosa sarebbe? (<img src='images/icons/online.png' />, <img src='images/icons/mail.png' />, ecc)" => "Se vedi un/una icona/bottone/immagine che non sai cosa significhi prova a passarci sopra il mouse, potrebbe uscire una nota con una breve spiegazione. Se non dovesse succedere, sentiti libero di chiedere sul <a href='http://forum.webdiplomacy.it' class='light'>forum</a>.",
"Perchè i miei ordini cambiano colore da verde a rosso?" => "Gli ordini in rosso sono ancora da salvare; se vedi molti ordini in rosso dovresti salvare, altrimenti potresti dimenticartene e perderli chiudendo la finstra del browser o chattando con qualcuno.",
"Cosa significano 'Salva' e 'Pronto'?" => "Il pulsante 'Salva' permette di salvare i propri ordini; Gli ordini in rosso, cioè quelli non ancora salvati, diventeranno verdi non appena schiacci 'Salva'. <br />
Il pulsante 'Pronto' è disponibile una volta salvati i propri ordini e, se schiacciato, significa che hai finito di inserire i tuoi ordini e che sei pronto per continuare con il turno successivo senza aspettare la scadenza del turno. Se tutti i giocatori di una partita sono 'Pronti' la partita avanza di turno immediatamente.",
"Come faccio a scrivere nel forum?" => "Il sito del gioco e il forum della comunità per una precisa scelta sono separati. Quindi, occorre una registrazione per poter giocare online sul sito webdiplomacy.it ed una seconda registrazione per poter scrivere commenti sul forum.",
"Perchè alcune cose sembrano cambiare non appena la pagina si è caricata?" => "Dopo che la pagina si è caricata parte JavaScript, andando a fare alcune modifiche (per esempio mettendo l'orario GMT/UTC nel tuo computer, rendendo in grassetto i tuoi interventi, ecc) che migliorano la visualizzazione della pagina.",

"Regole del gioco" => "Sub-section",
"Voglio imparare le regole basi del gioco" => "Consulta la versione semplificata delle <a href='intro.php' class='light'>regole del gioco Diplomacy</a>.",
"Voglio imparare le regole avanzate del gioco" => "<a href='http://www.wizards.com/avalonhill/rules/diplomacy.pdf' class='light'>Qui</a> è disponibile l'ultimo manuale delle regole in inglese. Più avanti verrà messo a disposizione di tutti la versione tradotta in italiano.",
"Voglio imparare i dettagli sulle regole del gioco" => "Per risolvere esattamente ogni sorta di situazioni intricate, nei casi in cui c'è ambiguità nel regolamento, usiamo il DATC. (Questo situazioni generalmente non capitano spesso in una partita, comunque.)
<br />Vedi la nostra pagina <a href='datc.php' class='light'>DATC</a>.",
"Se qualcuno deve distruggere una unità ma non ha inserito gli ordini su quale unità distruggere, quale unità è distrutta?" => "Si risolve come da raccomandazione DATC: viene distrutta l'unità più lontana dai tuoi Centri di approvigionamento iniziali. La distanza è definita come il minor numero di mosse necessario per arrivare alla posizione dell'unità partendo dai Centri di partenza. Quando si calcola il minor numero di mosse, le armate si considerano come se si potessero muovere attraversando i mari, le flotte invece si considerano soltanto secondo il loro naturale movimento attraverso i mari e le coste. Se ci dovessero essere due unità con la medesima distanza da un Centro di partenza, si prende in considerazione l'elenco alfabetico dei territori nei quali si trovano le unità.",
"Se un convoglio è attaccato, il convoglio fallisce?" => "No; perchè un ordine di convoglio non abbia successo è necessario che la flotta che convoglia venga costretta a ritirarsi, e non ci devono essere altri convogli grazie al quale l'armata riesca comunque a essere trasportata.",
"Cosa succede se ordino di costruire/distruggere due unità nello stesso territorio?" => "Il primo ordine di costruzione sarà accettato, il secondo no",
"Cosa succede se due unità ricevono l'ordine di ritirsi nello stesso territorio?" => "Entrambe le unità vengono distrutte",
"Posso attaccare e costringere alla ritirata le mie unità?" => "No; non puoi costringere al ritiro le tue stesse unità nè supportare un attacco verso le tue stesse unità.",
"Ci sono altre regole che dovrei tenere a mente?" => "Consulta la pagina delle <a href='rules.php' class='light'>regole di questo sito</a>, che è necessario rispettare per mantenere il sito divertente per chiunque.",

"Punti" => "Sub-section",
"Cosa succede se finisco tutti i miei punti?" => "Non è possibile. Ogni giocatore ha sempre almeno 100 punti a propria disposizione indipendentemente dai suoi risultati.
Ovviamente, nei 100 punti sono comprese anche i punti scommessi nelle partite attualmente in corso di svolgimento. Se un giocatore non partecipa a nessuna partita, avrà sempre almeno 100 punti a disposizione.<br /><br />
Se un giocatore inizia due partite da 50 punti, avrà 100 punti 'impegnati' nelle due partite in corso e 0 punti a disposizione per iniziare partite nuove. Insomma, non potrà iniziare nuove partite, finchè non finisce una delle due partite da 50 punti.
	",
"Come sono suddivisi i punti in caso di patta?" => "
In caso di patta, i punti vengono equamente divisi tra tutti i giocatori sopravissuti, indipendentemente dal numero di centri posseduti.<br/>
	Leggi la <a href='points.php' class='light'>guida</a> ai punti di webDiplomacy  per capire meglio.",
"Ho in mente un sistema migliore!" => "
Riceviamo costantemente proposte di differenti sistemi di punteggio. Alcuni migliorano certi aspetti, a scapito di altri, ma in generale il sistema attuale svolge 
relativamente bene il proprio lavoro, perciò è difficile che venga cambiato.
(<a href='http://forum.webdiplomacy.net/viewtopic.php?p=288#p288' class='light'>Qui</a> è possibile vedere un breve riassunto dei criteri che un eventuale nuovo sistema di punteggio deve soddisfare.)<br /><br />",
"Potete pattare una partita, ma dare i 2/3 della posta a quel giocatore, mentre agli altri..." => "Le patte sono votate all'unanimità da tutti i sopravissuti e la posta viene sempre suddivisa equamente tra tutti. Se 3 giocatori raggiungono la patta in una partita da 90 punti, riceveranno 30 punti a testa.",

"Problemi" => "Sub-section",
"La mia partita è in tilt!" => "A volte, di solito dopo gli aggiornamenti, possono capitare errori di programmazione o verificarsi problemi al server.
Se questi problemi accadono mentre è in corso l'aggiornamento di una o più partite, queste vengono subito bloccate, in attesa di una valida soluzione.<br /><br />
Appena il problema è stato risolto i moderatori possono far ripartire la partita.<br /><br />
Se la vostra partita è ferma da parecchio tempo, chiedete di farla ripartire contattando i moderatori.",
"Scadenza \"Adesso\"? \"L'aggiornamento è stato interrotto\"?" => "
Se un moderatore o un amministratore nota un potenziale problema o pericolo, ha a sua disposizione una funzione per fermare completamente tutti i processi del sito prima che la loro esecuzione possa causare danni irreparabili agli ordini o alle partite.
Una volta risolto il problema, alle partite interrotte viene aggiunto del tempo per riprendere la diplomazia e controllare gli ordini inseriti.
<br /> Se la scadenza è 'Adesso', vuol dire che la partita sta per essere aggiornata a momenti (a meno che non sia in pausa).<br />
Questa scritta appare anche quando il conto alla rovescia termina, ma la pagina della partita non viene aggiornata dall'utente. Una volta ricaricata la pagina, se la partita è stata aggiornata, ricompare il tempo della prossima scadenza.",
"Non ho inserito quegli ordini!" => "
A volte riceviamo questo tipo di lamentela, ma in genere le verifiche confermano la corrispondenza tra gli ordini inseriti e quelli eseguiti dal sito.
Quello che può accadere è che il browser usato dall'utente non abbia salvato l'ultima versione degli ordini per un motivo indipendente dal sito.
Se avete sperimentato questo problema una soluzione potrebbe essere quella di cambiare il browser utilizzato, oppure, ogni volta dopo aver cambiato i propri ordini,
 chiudere e riaprire il browser e poi controllare che il sito abbia correttamente salvato l'ultima versione degli ordini.
<br />",
"Gli ordini degli altri sono sbagliati, ma sono io a rimetterci!" => "
Sfortunatamente sembra che alcuni giocatori giustificano le proprie intenzioni mascherandole da errori del server. Può succedere, infatti, che
un giocatore affermi di aver cambiato gli ordini all'ultimo, ma che nonostante questo una vecchia versione degli ordini sia stata eseguita.
<br /><br />
Questo è <a href='rules.php' class='light'>contro le regole</a>, perchè fa lavorare inutilmente i moderatori alla ricerca di bug ed errori.
Dovete avere una notevole dose di scetticismo verso qualsiasi affermazione di errore da parte del server nell'elaborare gli ordini.
Infatti, sul server principale di webdiplomacy vengono elaborati oltre 20000 ordini al giorno (febbraio 2010) senza errori di sorta. 
Quindi sembra altamente improbabile che un sistema stabile e sicuro sbagli all'improvviso un singolo ordine di un singolo utente, senza aver alterato il suo buon funzionamento per tutti gli altri giocatori.",
"I miei ordini hanno avuto un esito sbagliato!" => "Prima di segnalare cose del genere controllate bene (varie volte)
che abbiate inserito correttamente gli ordini e che non avete sbagliato nell'interpretare le regole. Nel 99.999% dei casi 
gli 'errori del server' si rivelano semplici disattenzioni o errata interpretazione delle regole.<br />",

"Richieste di miglioramenti" => "Sub-section",
"Mappa interattiva punta e clicca" => "
Questa funzione sarebbe ottima da implementare, ma i lavori proseguono molto lentamente. Se conoscete JavaScript e SVG/Canvas potete darci una mano sul
<a href='http://forum.webdiplomacy.net/' class='light'>forum di sviluppo</a>.",
"Traduzioni" => "Attualmente è possibile tradurre completamente webdiplomacy nella propria lingua, ma la visualizzazione è limitata ad una sola lingua per volta.",
"Nuove varianti" => "Se una variante è abbastanza bilanciata, e giocabile; e c'è un notevole interesse ad implementarla, potete richiederne l'introsuzione su questo sito. Considerate che ogni variante richiede un pò di lavoro e se il richiedente fosse disponibile potrebbe velocizzare molto i tempi di introduzione.<br />
	Nuove varianti e la loro creazione possono essere discussi nella sezione <em>Varians</em> del <a href='http://forum.webdiplomacy.net/' class='light'>forum degli sviluppatori</a>.<br /><br />",
"Posso suggerire un miglioramento?" => "Potete chiedere o suggerire nuove idee sul forum della <a class='light' href='http://forum.webdiplomacy.it/'>comunità italiana</a>.
Ricordatevi, che per quanto un'idea sia buona, una vostra capacità di scrivere codice per implementarla, potrebbe velocizzare di molto i tempi.",

"Dare una mano" => "Sub-section",
"Posso dare una mano a svilluppare il sito?" => "Certo che sì! Se cose come HTML/CSS/JavaScript/PHP 5/MySQL/SVG/Canvas non ti sono sconosciute,
	o se sei un grafico o disegnatore di icone, o se vuoi imparare consulta la pagina con le  <a class='light' href='http://webdiplomacy.it/developers.php'>informazioni per sviluppatori</a>.",
"Posso supportare questo progetto?" => "Certo! Questo sito è totalmente gratuito, ma a breve sarà disponibile un link per effettuare donazioni per suportare i costi di gestione e lo sviluppo di nuove funzioni.",
"Cosa ottengo diventando donatore?" => "Tutti i donatori ricevono un simbolo accanto al proprio nome utente a seconda della cifra totale donata, in modo da poter vedere chi contribuisce attivamente a tenere vivo il sito e chi da valore al proprio account su webDiplomacy.it .
Per il momento nessuna altra funzionalità aggiunta è a disposizione dei donatori: il sito è uguale per tutti.",
/*"How much do you need to donate to get a silver/gold mark?" => "
We'd rather people donate whatever/whenever they feel is appropriate, rather than aiming for a certain mark.
",*/
"Ci sono altri modi per dare una mano?" => "Certo! Raccontate ai vostri amici di webDiplomacy.it, mettete una breve recensione nel vostro sito oppure il link nella vostra firma nei vari forum ai quali partecipate, partecipate alla vita del <a href='http://forum.webdiplomacy.it' class='light'>forum</a> e aiutate i nuovi giocatori; Grazie!",

"Mappa" => "Sub-section",
"Perchè alcuni ordini non si vedono sulla mappa?" => "Non tutti gli ordini sono visualizzati sulla mappa piccola. Basta aprire la mappa grande per visualizzare tutti gli ordini del turno.
	<br/> Inoltre, in fondo alla pagina c'è il link <em>Ordini</em> che apre la pagina con la lista testuale di tutti gli ordini dell'intera partita.",
"Ho difficoltà a distinguere i colori di Germania e Austria" => "Sì, alcune persone potrebbero avere problemi nel distinguere questi colori. Risolveremo questo problema al più presto."
);

foreach($globalFaq as $Q=>$A)
	$faq[$Q]=$A;

$i=1;

print libHTML::pageTitle('webDiplomacy.it - Domande frequenti','Risposte alle domande più frequenti che si fanno spesso nei forum; Clicca sulla domanda per visualizzare la risposta.');


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
