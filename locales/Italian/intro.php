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
 
    Traduzione italiana a cura di webdiplomacy.it
 */

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * @package Base
 * @subpackage Static
 */

print libHTML::pageTitle('Introduzione alle regole del gioco da tavolo Diplomacy','Impara a giocare a Diplomacy con questo facile e veloce regolamento.');

?>
<p>
Diplomacy è un gioco da tavolo facile da imparare, ma difficilissimo da padroneggiare maniera eccelsa. 
Le regole somo semplici ed intuitive, e molti giocatori le imparano giocando (partite online oppure dal vivo).
<br />Questa breve introduzione grafica vi aiuterà ad ambientarvi più velocemente per poter subito giocare online.
</p>

<div class="hr"></div>


<p style="text-align:center"><a href="#Obiettivo">Obiettivo</a> - <a href="#Unità">Unità</a> - <a href="#Turni">Turni</a> - 
	<a href="#Movimenti">Movimenti</a> - <a href="#Regole">Regole</a> - <a href="#Gioco">Gioco</a></p>

<div class="hr"></div>

<a name="Obiettivo"></a>
<h3>Obiettivo del Gioco</h3>
<p>L'obiettivo del Diplomacy è di arrivare a conquistare 18 centri di approvigionamento. 
<br />Il numero di unità (Armate o Flotte) che possiede ciascun giocatore corrisponde al numero di centri posseduti.
<br />Per ogni nuovo centro guadagnato, il giocatore può mettere in campo una nuova unità, perdendone una se il centro viene conquistato da un altro giocatore.
<br /><br />
I centri di approvigionamento si distinguono dalle semplici regioni mediante dei segni circolari.</p>
<p style="text-align:center;">
	<img src="images/intro/supply.png" alt=" " title="I centri sono segnati così (Mappa grande)" />
	<img src="images/intro/supply2.png" alt=" " title="I centri sono segnati così (Mappa piccola)" />
</p>

<div class="hr"></div>

<a name="Unità"></a>
<h3>Unità in campo</h3>
<ul class="formlist">
	<li class="formlisttitle">Armata <img src="<?php print STATICSRV; ?>contrib/army.png"
		alt="Diplomacy - Armata "  title="L'icona di un'Armata" /></li>
	<li class="formlistdesc">
	Le Armate possono muoversi solo sulla terraferma.
	</li>

	<li class="formlisttitle">Flotta <img src="<?php print STATICSRV; ?>contrib/fleet.png"
		alt="Diplomacy - Flotta " title="L'icona di una Flotta" /></li>
	<li class="formlistdesc">
	 Le Flotte possono muoversi solo sui mari e sulle regioni con delle coste.
	 <br />Possono anche trasportare le armate attraverso il mare aperto.
	</li>
</ul>

<div class="hr"></div>

<a name="Turni"></a>
<h3>Turni di gioco</h3>
<ul class="formlist">
	<li class="formlisttitle">Primavera</li>
	<li class="formlistdesc">
	Durante il turno di Primavera i giocatori hanno a loro disposizione il tempo per conoscere i loro compagni di gioco, fare diplomazia, raggiungere accordi e stipulare alleanze che presumibilmente porteranno alla gloria il proprio paese. 
	Prima della scadenza del tempo bisogna inserire anche gli ordini di movimento delle proprie truppe. 
	</li>
	<li class="formlisttitle">Ritirate dopo il turno di primavera</li>
	<li class="formlistdesc">
	Se, a seguito delle mosse di primavera alcune unità devono ritirarsi, si ha il turno delle ritirate, durante il quale i giocatori possono decidere se distruggere o ritirare (in quale territorio) le proprie unità.
	Se dopo il turno di Primavera non ci sono ritirate per nessun giocatore, si passa direttamente al turno successivo: Autunno
	</li>
	<li class="formlisttitle">Autunno</li>
	<li class="formlistdesc">
	L'Autunno si svolge esattamente come il turno di primavera. L'unica differenza è che dopo l'autunno avviene il conteggio dei centri di approvigionamento. Tutte le armate o le flotte che finiscono questo turno in una provincia che contiene un centro, lo conquistano. Se un giocatore entra in un centro di approvigionamento non suo durante il turno di primavera e nel turno di autunno ne esce, quel centro non viene conquistato.
	</li>
	<li class="formlisttitle">Ritirate dopo il turno dell'Autunno</li>
	<li class="formlistdesc">
	Se, a seguito delle mosse di Autunno alcune unità devono ritirarsi, si ha il turno delle ritirate, durante il quale i giocatori possono decidere se distruggere o ritirare (in quale territorio) le proprie unità.
	Se dopo il turno di Autunno non ci sono ritirate per nessun giocatore, si passa direttamente al turno successivo:Inverno. 
	</li>
	<li class="formlisttitle">Inverno</li>
	<li class="formlistdesc">
	Durante il turno invernale, se hanno più centri di produzione rispetto alle unità in campo, i giocatori possono costruire nuove armate o flotte. Se il numero dei centri di produzione è inferiore al numero delle unità presenti sulla mappa, allora il giocatore deve rimuovere.<br/>
	<em>Esempio:</em> La Francia durante la Primavera muove l'armata dalla Marsiglia in Spagna. Durante l'Autunno muove l'armata dalla Spagna in Portogallo, finendo il turno in Portogallo. La Spagna non è conquistata, perchè alla fine dell'autunno non c'è nessuna armata in quel centro. Mentre è conquistato il Portogallo. La Francia durante il turno di inverno può costruire un'altra unità. 
	<br /><em>Nota Bene</em> Ai fini di possedere un determinato centro non è necessario finire ogni turno di Autunno su quel centro. Basta una volta sola. Una volta conquistato, il centro rimane di proprietà di un giocatore, finchè l'unità di un altro giocatore non termina il turno di Autunno in quel centro.
	</li>


</ul>
<div class="hr"></div>
<a name="Movimenti"></a>
<h3>Movimenti</h3>
<ul class="formlist">
	<li class="formlisttitle">Difesa - Hold</li>
	<li class="formlistdesc">
		L'Unità difenderà il terrirorio sul quale si trova, se questo viene attaccato; altrimenti starà ferma.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/801-large.map-thumb" alt="Diplomacy - Armata ferma " title="Armata ferma a Napoli" />
		</p>
	</li>


	<li class="formlisttitle">Movimento - Move</li>
	<li class="formlistdesc">
		L'Unità tenta di attaccare un territorio adiacente.  
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/802-large.map-thumb" alt="Diplomacy - Armata in movimento " title="L'Armata da Napoli muove verso Roma" />
		</p>
	</li>

        <li class="formlisttitle">Supporto - Support</li>
        <li class="formlistdesc">Il supporto è la peculilarità del Dilpomacy. Visto che nessuna unità è più forte di un'altra,
	  hai bisogno di combinare la forza di più unità per attaccare altri territori o per difendersi dagli attacchi degli avversari.<br />
	  Attenzione! Esistono due tipi di supporto diversi: Il Supporto alla Difesa e il Supporto all'Attacco. Per ricevere n supporto difensivo l'Unità deve per forza stare ferma. Un'unità che attacca un territorio non può ricevere supporti in difesa, ma solo supporti per attaccare.</li>
	  
	<li class="formlisttitle">Supporta la Difesa - Support Hold</li>
	<li class="formlistdesc">
	  Il supporto è la peculilarità del Dilpomacy. Visto che nessuna unità è più forte di un'altra,
	  hai bisogno di combinare la forza di più unità per attaccare altri territori.
		<br />
		<em>(Tieni fermo il cursore del mouse sulle immagini per avere spiegazioni più dettagliate.)</em>
		<p style="text-align:center;">
				<img src="<?php print STATICSRV; ?>datc/maps/804-large.map-thumb" alt="Diplomacy - Esempio regola supporto "
				title="Il Supporto alla Difesa (Verde) dato dalla flotta nel mare Tirreno all'Armata di Roma, permette di difendere la città da un attacco di uguale supporto" />
		</p>
	</li>
	
	<li class="formlisttitle">Supporta l'Attacco - Support Move</li>
	<li class="formlistdesc">
	<p style="text-align:center;">
	<img src="<?php print STATICSRV; ?>datc/maps/803-large.map-thumb" alt="Diplomacy - Esempio regola supporto "
				title="Il Supporto (in giallo) all'Attacco, dato dall'Armata in Toscana, permette all'Armata di Venezia di sconfiggere l'Armata che stava a difesa di Roma" />
		</p></li>


	<li class="formlisttitle">Trasporto - Convoy</li>
	<li class="formlistdesc">
		Puoi usare le Flotte per trasportare le Armate attraverso territori di mare aperto. Avendo le necessarie Flotte a disposizione, 
    puoi trasportare l'Armata attraverso più territori (una Flotta per ogni territorio) in un solo turno.
    
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/805-large.map-thumb" alt="Diplomacy - regola trasporto armata "
				title="L'Armata da Venezia muove verso Tunisia, e le Flotte in Adriatico e nello Ionio la trasportano" />
		</p>
	</li>
</ul>

<div class="hr"></div>
<a name="Regole"></a>
<h3>Regole del gioco</h3>
<ul class="formlist">
<li class="formlistdesc">
  Tutte le Armate e le Flotte hanno uguale forza, e, a parità di supporti, <strong>il difensore</strong>
	riesce sempre a difendersi dall'<strong>attaccante</strong>.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/806-large.map-thumb" alt="Diplomacy - attacco non riuscito "
			title="L'Armata di Napoli ha provato a muovere senza supporti verso Roma. Il difensore riesce a resistere all'Attacco" />
		<img src="<?php print STATICSRV; ?>datc/maps/807-large.map-thumb" alt="Diplomacy - stallo "
			title="La Flotta e l'Armata che muovono verso la Puglia hanno uguale forza (1) e quindi nessuno dei due entra nel territorio conteso" />
	</p>
</li>


<li class="formlistdesc">
	L'unico modo per vincere le battaglie è quello di suportare l'<strong>Attacco</strong> con altre unità. Il <strong>supporto all'attacco</strong>
  viene visualizzato sulla mappa con frecce gialle.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/803-large.map-thumb" alt="Diplomacy - attacco riuscito "
				title="Il supporto dato dall'Armata in Toscana permette all'Armata di Venezia di sconfiggere l'Armata di Roma" />
	</p>
</li>


<li class="formlistdesc">
	Ovviamente, il supporto può essere dato anche in <strong>difesa</strong>. Segnato sulla mappa in verde.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/804-large.map-thumb" alt="Diplomacy - difesa riuscita "
				title="Il Supporto alla Difesa (Verde) dato dalla flotta nel mare Tirreno all'Armata di Roma, permette di difendere la città da un attacco di uguale supporto" />
	</p>
</li>


<li class="formlistdesc">
	Se il numero di supporti all'<strong>Attacco</strong> è maggiore del numero dei supporti alla <strong>Difesa</strong>,
	l'attacco riesce. Altrimenti vince il difensore.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/808-large.map-thumb" alt="Diplomacy - attacco riuscito "
				title="La Flotta di Trieste viene aiutata da due unità (totale 3), mentre l'armata di Venezia aveva solo un aiuto (Roma), quindi La Flotta di Trieste entra a Venezia. L'Armata che in precedenza era in Venezia non è più visibile sulla mappa perchè sconfitta e costretta a ritirare in un territorio adiacente" />
		<img src="<?php print STATICSRV; ?>datc/maps/809-large.map-thumb" alt="Diplomacy - attacco non riuscito "
				title="La Flotta di Trieste viene aiutata da due unità (totale 3), ma anche la Flotta di Venezia ha due aiuti questa volta (Roma e Puglia), quindi l'attacco è bloccato" />
	</p>
</li>

<li class="formlistdesc">
	Un'Unità che viene attaccata, deve <strong>Difendersi</strong>, e non può supportare altre Unità per quel turno.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/808-large.map-thumb" alt="Diplomacy - supporti e tagli "
				title="Nessuna unità di supporto viene attaccata, quindi: Trieste 2 - Venezia 1; Trieste muove in Venezia" />
		<img src="<?php print STATICSRV; ?>datc/maps/810-large.map-thumb" alt="Diplomacy - supporti e tagli  "
				title="Un'Armata attacca il Tirolo da Monaco, vanificando il suo supporto all'Attacco: Trieste 1 - Venezia 1; Trieste non muove in Venezia" />
		<img src="<?php print STATICSRV; ?>datc/maps/811-large.map-thumb" alt="Diplomacy - supporti e tagli  "
				title="Una flotta nel mare Tirreno attacca Roma, rendendo nullo il suo supporto in Difesa di Venezia: Trieste 1 - Venice 0; Trieste muove in Venezia" />
	</p>
</li>

</ul>
<div class="hr"></div>
<ul class="formlist">
<li class="formlistdesc">
	<a name="Gioco"></a>
	Con queste regole hai gli strumenti di base per cominciare a giocare a Diplomacy online!
	
	<br /> Dopo esserti
	<a href="register.php" class="light">registrato</a> su questo sito, puoi 
	<a href="gamecreate.php" class="light">creare</a> nuove partite oppure
	<a href="gamelistings.php" class="light">giocare online</a> alle partite che aspettano solo te.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/812-large.map-thumb" alt=" "
				title="Il supporto dato dalla Flotta in Prussia a difesa della Flotta del Baltico permette a quest'ultima di ressitere all'attacco dalla Livonia e di procedere al trasporto di un'armata da Berlino in Svezia"  />
	</p>
	</li>
</ul>
