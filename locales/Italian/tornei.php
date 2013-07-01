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

print libHTML::pageTitle('Giocare a Diplomacy dal vivo: calendario tornei CISD e partite amichevoli ','Collegamenti alle informazioni, regole e iscrizioni ai tornei dal vivo di Diplomacy.');
?>

<div id="rulebook">
<p class="notice notext">
  <a href="#CISD">CISD</a>
- <a href="#Master italiano di Diplomacy">Master italiano di Diplomacy</a>
- <a href="#Partite">Altre partite</a>
- <a href="#Estero">Tornei all'estero</a>
</p>
<?php require('mod/social.php');?>
<div class="hr"></div>

<a name="CISD"></a><h3> CISD 2013 ( Campionato Italo-Sammarinese di Diplomacy ) :</h3>
<ul><li> Torneo di Pavia <a href="#">Torneo concluso</a></li>
    <li> Torneo di Udine <a href="#">Torneo concluso</a></li>
    <li> Torneo di San Marino <a href="#">Torneo concluso</a>. </li>
    <li> Torneo di Lucca <a href="#">Info sul forum</a></li>
    <li> Torneo di Roma <a href="#">Info sul forum</a></li>
    
    <br /> 
    <li><a href="http://forum.webdiplomacy.it/viewtopic.php?f=56&t=523">Classifica provvisoria CISD 2013</a></li>
    <br />
    <li><a href="http://forum.webdiplomacy.it/viewtopic.php?f=43&t=457">Classifica finale del CISD 2012</a></li>
</ul>

<div class="hr"></div>

<a name="Master italiano di Diplomacy"></a><h3> Master italiano di Diplomacy 2013 :</h3>
<br />
<ul><li> Il Master italiano 2013 vedrà affrontarsi i primi sette classificati del CISD 2013, in una singola partita avvincente e piena di suspance.</li>
    <li> <a href="#">Info a seguire</a>.</li>

</ul>

<div class="hr"></div>

<a name="Partite"></a><h3> Partite amichevoli organizzate al di fuori del CISD e non valevoli per la sua classifica :</h3>

<ul>
<li><a href="http://forum.webdiplomacy.it/viewforum.php?f=27">Partite amichevoli a Milano</a></li>
<li><a href="http://forum.webdiplomacy.it/viewforum.php?f=28">Partite amichevoli a Roma</a></li>
<br>
<li><a href="http://forum.webdiplomacy.it/viewforum.php?f=14">Partite amichevoli in altre parti d'Italia</a></li>
</ul>

<div class="hr"></div>

<a name="Estero"></a><h3> Tornei all'Estero : </h3>
<ul>
<li> Questa sezione è in costruzione. Vai sul Forum per avere più notizie.</li>

</ul>

<div class="hr"></div>

</div>
<p>Non tutti gli eventi sono elencati in questa pagina ? <br />
   Hai in programma una partita a Diplomacy con gli amici e ti manca un giocatore? <br />
   Hai il gioco in scatola che prende polvere nell'armadio e vorresti provarlo? <br />
   Conosci tanti giocatori nella tua zona e vorresti organizzare un torneo di Diplomacy? <br/>
    Segnalalo sul <a href="http://forum.webdiplomacy.it/" class="light"> forum </a> nella sezione <b>Diplomacy dal vivo</b> - <i>Partite amichevoli</i></p>
    
    
<style type="text/css">
#rulebook p {
color:#444;
font-weight:normal;
margin-bottom:30px;
margin-left:20px;
margin-right:20px;
padding-left:10px;
}
#rulebook p.notext {
border:0;
color:black;
}
#rulebook em {
text-decoration:underline;
font-style:normal;
}
</style> 
