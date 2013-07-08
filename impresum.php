<?php
/*
    Copyright (C) 2012 Oliver Auth

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

require_once('header.php');

libHTML::starthtml();

print libHTML::pageTitle('Impresum','Angaben gemäß § 5 TMG');

print '
<h2>Kontakt:</h2>
<p>'.Config::$impresum['name'].'<br />'.Config::$impresum['street'].'<br />'.Config::$impresum['city'].'<br /></p>
<p>E-Mail: '.Config::$impresum['email'].'</p>

<h2>Haftungsausschluss:</h2>
<p><strong>Haftung für Inhalte</strong></p>
    <p>Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. 
      Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte 
      können wir jedoch keine Gewähr übernehmen. Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für 
      eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. 
      Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht 
      verpflichtet, übermittelte oder gespeicherte fremde Informationen zu 
      überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige 
      Tätigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der 
      Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon 
      unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem 
      Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei 
      Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte 
      umgehend entfernen.</p>
    <p><strong>Haftung für Links</strong></p>
    <p>Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren 
      Inhalte wir keinen Einfluss haben. Deshalb können wir für diese 
      fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte 
      der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der 
      Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung 
      auf mögliche Rechtsverstöße überprüft. Rechtswidrige 
      Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente 
      inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte 
      einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen 
      werden wir derartige Links umgehend entfernen.</p>
    <p><strong>Urheberrecht</strong></p>
    <p>Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten 
      unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und 
      jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen 
      der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads 
      und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen 
      Gebrauch gestattet. Soweit die Inhalte auf dieser Seite nicht vom Betreiber erstellt wurden, 
      werden die Urheberrechte Dritter beachtet. Insbesondere werden Inhalte Dritter als solche 
      gekennzeichnet. Sollten Sie trotzdem auf eine Urheberrechtsverletzung aufmerksam werden, bitten wir um einen entsprechenden Hinweis. 
      Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Inhalte umgehend entfernen.</p>
    <p><strong>Datenschutz</strong></p>
    <p>Die Nutzung unserer Webseite ist in der Regel ohne Angabe personenbezogener Daten möglich. Soweit auf unseren Seiten personenbezogene Daten (beispielsweise Name, 
      Anschrift oder eMail-Adressen) erhoben werden, erfolgt dies, soweit möglich, stets auf freiwilliger Basis. Diese Daten werden ohne Ihre ausdrückliche Zustimmung nicht an Dritte weitergegeben.   
    </p>
    <p>Wir weisen darauf hin, dass die Datenübertragung im Internet (z.B. 
      bei der Kommunikation per E-Mail) Sicherheitslücken aufweisen kann. 
      Ein lückenloser Schutz der Daten vor dem Zugriff durch Dritte ist nicht 
      möglich. </p>
    <p>Der Nutzung von im Rahmen der Impressumspflicht veröffentlichten Kontaktdaten 
      durch Dritte zur Übersendung von nicht ausdrücklich angeforderter 
      Werbung und Informationsmaterialien wird hiermit ausdrücklich widersprochen. 
      Die Betreiber der Seiten behalten sich ausdrücklich rechtliche Schritte 
      im Falle der unverlangten Zusendung von Werbeinformationen, etwa durch Spam-Mails, 
      vor.</p><p> </p>
<p>Quelle: <i><a href="http://www.e-recht24.de/muster-disclaimer.htm" target="_blank">Disclaimer</a> von eRecht24, dem Portal zum Internetrecht von <a href="http://www.e-recht24.de/" target="_blank">Rechtsanwalt</a> Sören Siebert.</i></p>
';
print '</div>';
libHTML::footer();

?>