<<<<<<< HEAD
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
 
    Traduzione italiana a cura di webdiplomacy.it
 */

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * @package Base
 * @subpackage Static
 */

print libHTML::pageTitle('Sviluppatori/Webmaster - Informazioni utili','Per coloro che vogliono installare/migliorare/modificare webDiplomacy.');

?>

<h4>Collegamenti</h4>

<p><a href="http://forum.webdiplomacy.it" class="light">forum.webdiplomacy.it</a> - Il forum italiano su Diplomacy. Potete discutere qui le vostre idee per migliorare il sito di gioco.</p>

<p><a href="http://forum.webdiplomacy.net" class="light">forum.webdiplomacy.net</a> - Il forum degli sviluppatori.</p>

<p><a href="https://github.com/kestasjk/webDiplomacy" class="light">github.com/kestasjk/webDiplomacy</a> - La pagina github del progetto webDiplomacy</p>

<div class="hr"></div>

<h4>Webmasters</h4>

<p><a href="README.txt" class="light">README.txt</a> - File leggimi per l'installazione</p>

<p><a href="AGPL.txt" class="light">AGPL.txt</a> - La licenza di questo sito. qualsiasi cambiamento al codice sorgente deve essere condiviso.</p>

<div class="hr"></div>

<h4>Implementazioni nuove</h4>
<p>Per implementare un'idea nuova, prima di tutto verificate nella sezione "todo-list" del sito  forum.webdiplomacy.net se è già stata proposta. Se non è presente nella lista, aggiungetela nella sezione Ideas/Requests.</p>

<p>Quando l'implementazione è pronta, lasciate un collegamento e il codice utilizzato al sito di prova. Dopo le opportune verifiche la patch sarà aggiunta all'installazione principale.</p>

<div class="hr"></div>

<h4>Varie ed eventuali</h4>

<p><a href="http://webdiplomacy.it/doc/javascript.txt" class="light">javascript.txt</a> - Informazioni su JavaScript utilizzati</p>

<p><a href="http://webdiplomacy.it/doc/gotchas.txt" class="light">gotchas.txt</a> - Bug strani</p>

<p><a href="http://webdiplomacy.it/doc/archive.txt" class="light">archive.txt</a> - Informazioni sulle tabelle di archivio</p>

<p><a href="http://webdiplomacy.it/doc/coasts.txt" class="light">coasts.txt</a> - Informazioni su come siano gestite le coste</p>
=======
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

print libHTML::pageTitle('Developer/webmaster info','If you want to fix/improve/install webDiplomacy all the info you need to make it happen is here.');

?>

<h4>Links</h4>

<p><a href="http://forum.webdiplomacy.net" class="light">forum.webdiplomacy.net</a> - The forum for developers.</p>

<p><a href="http://sourceforge.net/projects/phpdiplomacy" class="light">forum.webdiplomacy.net</a> - The sourceforge.net project page.</p>

<p><a href="https://github.com/kestasjk/webDiplomacy" class="light">github.com/kestasjk/webDiplomacy</a> - The github .</p>

<div class="hr"></div>

<h4>Webmasters</h4>

<p><a href="http://webdiplomacy.net/README.txt" class="light">README.txt</a> - Installation data for webmasters</p>

<p><a href="http://webdiplomacy.net/AGPL.txt" class="light">AGPL.txt</a> - The license protecting this code, if you make
	changes to the code you've got to share those changes.</p>

<div class="hr"></div>

<p>Some of the data below may apply mainly to 0.8x, and not be up to date for 0.9x, but the differences are mostly minor.</p>

<div class="hr"></div>

<h4>Layout</h4>

<p>If you want to make a change this is where you should start. The two images and text files below will give you a feel
for where everything is and how webDip is structured, so you know where to go to find whatever you need to change.</p>

<p><a href="http://webdiplomacy.net/doc/layout-code.png" class="light">layout-code.png</a>, <a href="http://webdiplomacy.net/doc/layout-code.txt" class="light">layout-code.txt</a>
- File/directory layout image and text file; how the code is structured and what different files do</p>

<p><a href="http://webdiplomacy.net/doc/layout-database.png" class="light">layout-database.png</a>,
<a href="http://webdiplomacy.net/doc/layout-database.txt" class="light">layout-database.txt</a>
 - The database layout image and text file; how the database is structured and what different tables do</p>

<div class="hr"></div>

<h4>Guidelines</h4>
<p>To get a patch submitted first check that the idea is okay. If it's not in the todo-list at forum.webdiplomacy.net post
it to the ideas section.</p>

<p>Once the patch is done post a link to a demo site where it's working, along with the code, and it'll get
added in once it has been checked.</p>

<div class="hr"></div>

<h4>Misc notes</h4>

<p><a href="http://webdiplomacy.net/doc/javascript.txt" class="light">javascript.txt</a> - JavaScript info</p>

<p><a href="http://webdiplomacy.net/doc/gotchas.txt" class="light">gotchas.txt</a> - Annoying quirks</p>

<p><a href="http://webdiplomacy.net/doc/archive.txt" class="light">archive.txt</a> - Info on the archive tables</p>

<p><a href="http://webdiplomacy.net/doc/coasts.txt" class="light">coasts.txt</a> - Info on how coasts are handled</p>
>>>>>>> 6a7b05d6871d85fa057a232e3a92742e88fe9047
