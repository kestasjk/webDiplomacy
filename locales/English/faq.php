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

if( isset(Config::$faq) && is_array(Config::$faq) && count(Config::$faq) )
{
	$faq["Server-specific"]="Sub-section";
	foreach(Config::$faq as $Q=>$A)
		$faq[$Q]=$A;
}

$globalFaq = array(
"I'm new here" => "Sub-section",
"What is this site about?" => "The easiest way to understand is to look at
	<a href='http://webdiplomacy.net/doc/0.90-screenshot.png' class='light'>an in-game screenshot</a>. If the concept is still strange
	read <a href='intro.php' class='light'>the intro to webDiplomacy</a>.",
"How do I play?" => "The gameplay is quite self explanatory, but if you're unsure about something read
	<a href='intro.php' class='light'>the intro to webDiplomacy</a>, and feel free
	to ask for help or clarification in <a href='forum.php' class='light'>the public forum</a>.",
"What's the software license?" => "The <a href='AGPL.txt' class='light'>GNU Affero General License</a>
	(<a href='http://www.opensource.org/licenses/agpl-v3.html' class='light'>Open Source Initiative</a> approved),
	which basically says you can download and change the code as you like, and put it up on your
	own website, but you can't claim you wrote it, and you have to give any changes
	you make back to the community.<br /><br />
	See <a href='credits.php' class='light'>the credits</a> for information about the
	small elements which are under different licenses.",

"Is this software related to phpDiplomacy?" => "This software used to be called phpDiplomacy until version 0.9.
	Sorry for the confusion, we hate name changes too, but for our user-base the old 'php' prefix wasn't
	the immidiately recognizable label that was intended.",


"The interface" => "Sub-section",
"What are those green circles next to peoples names?" => "The green icon appears when a player is logged into the server. This
	means that if the player has accessed the server in the last ~10-15 minutes they will have the green icon next to their name.",
"What the hell is that thing? (<img src='images/icons/online.png' />, <img src='images/icons/mail.png' />, etc)" => "If you see an icon/button/image and don't understand what it means try hovering your mouse over it,
	it may give you a tool-tip helping explain it. If it doesn't feel free to ask on the <a href='forum.php' class='light'>forum</a>.",
"Why do my order choices turn from green to red?" => "Red order choices are unsaved; if you see a lot of red in your orders you should
	save, or you might forget and lose them by closing the browser window or chatting to someone.",
"What do 'Save' and 'Ready' mean?" => "'Save' saves your order; your red, unsaved order choices will turn green once they're successfully
	saved. 'Ready' means you have finished entering your orders, and are ready to continue to the next turn. If everyone is 'Ready' the
	game continues right away, speeding up the game.",
"What are the codes which can add HTML into forum messages? (icons, game links, etc)" => "Often in the forum people discuss points, or
	want to link others to games/user accounts/other forum threads. To make this easier certain forum-codes are recognized and replaced
	with proper links/icons:
	<ul><li><strong>'<em>[number]</em> points'</strong>/<strong>'<em>[number]</em> D'</strong> will result in
	<strong>'points'</strong> / <strong>'D'</strong> being replaced with the points icon (".libHTML::points().").</li>
	<li><strong>'gameID=<em>[number]</em>'</strong> / <strong>'threadID=<em>[number]</em>'</strong> / <strong>'userID=<em>[number]</em>'</strong> will have
	a link to the appropriate game/thread/profile substituted into the message.</li></ul>",
"Why do some things appear to change just after the page has loaded?" => "After a page is loaded JavaScript runs, making a few changes
	(e.g. putting GMT/UTC times into your computer's timezone, making your posts bold, etc) which enhance the page.",

"Game rules" => "Sub-section",
"I want to learn the basic rules" => "See the <a href='intro.php' class='light'>intro page</a>.",
"I want to learn the complete rules" => "See Avalon Hill's <a href='http://www.wizards.com/avalonhill/rules/diplomacy.pdf' class='light'>rulebook</a>.",
"I want to learn the exact specifics of the rules" => "We use the DATC to lay out exactly how all sorts of tricky
	situations are processed, in the cases where there is ambiguity in the rules. (This sort of stuff doesn't come up
	in usual play though.)<br />
	See our DATC page <a href='datc.php' class='light'>here</a>.",
"If someone has to destroy a unit, but doesn't enter destroy orders, what unit is destroyed?" => "It's done as the DATC recommends:
	It's the furthest from your home supply centers. Distance is defined as the smallest number of moves to get from the unit's position
	to a home supply center. When calculating the smallest number of moves armies can move across seas, but fleets can only move across
	seas and coasts. If there are two units both the same distance from a home supply center then fleets disband before armies. If there's still a tie, 
	the territory which is first alphabetically is removed first.",
"If a convoy is attacked does the convoy fail?" => "No; for a convoy to fail it has to be dislodged, and there must be no other convoys
	the the army being convoyed can go through instead.",
"What happens if I order to build/destroy two units in the same territory?" => "The first build order will be accepted, the second won't",
"What happens if two units retreat to the same place?" => "They will both be disbanded",
"Can I dislodge my own units?" => "No; you cannot dislodge your own units, or support the dislodgement of your own units",
"Any other rules I should bear in mind?" => "There is a full list of rules on the <a href='rules.php' class='light'>rules</a> page,
	listing a few extra non-game rules you have to follow to help keep the site fun for everyone.",

"Points" => "Sub-section",
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
	If you're still positive there's a problem let us know in the <a class='light' href='forum.php'>forum</a>.",
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
"Can I donate?" => "See the server-specific section for a donate link, thanks!",
"Do I get anything for being a donator?" => "Markers are given for donators based on total donated to date; this lets everyone
see who is helping keep the site online and who has put value in their webDiplomacy account, but no extra
functionality is available to donators.",
/*"How much do you need to donate to get a silver/gold mark?" => "
We'd rather people donate whatever/whenever they feel is appropriate, rather than aiming for a certain mark.
",*/
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

foreach($globalFaq as $Q=>$A)
	$faq[$Q]=$A;

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
