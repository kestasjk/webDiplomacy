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

$globalFaq = array(

"The Basics of webDiplomacy" => "Sub-section",

"What is Diplomacy?" => "Diplomacy is a strategy game currently published by Avalon Hill and created by Allan Calhamer in 1954. 
In Diplomacy, players practice wartime tactics, employ powerful rhetoric in their negotiations, and make friends and enemies while competing to dominate the board. 
Unlike other war games, players submit their orders simultaneously and the entire board is adjudicated at once, 
meaning that everyone sees the moves that their competitors made at the same time each and every phase. 
Diplomacy is known as a favorite game for many powerful historical figures, including President John F. Kennedy, Henry Kissinger, Ray Bradbury, and Walter Cronkite, 
and it is a great game for learning and teaching history, negotiation, patience, and strategy.
<br><br>
For a more in depth explanation of the mechanics and gameplay of Diplomacy, visit our <a href='intro.php' class='light'>intro to webDiplomacy</a> 
page, where you can begin to learn how to play Diplomacy. You can also visit <a href='/contrib/phpBB3/viewforum.php?f=6' class='light'>our forum</a> 
if you have questions or seek clarification.",

"What is webDiplomacy?" => "webDiplomacy is an online platform for playing the popular board game Diplomacy. 
webDiplomacy was created in 2004 as a completely ad-free and nonprofit site, and is one of the first web-based platforms for playing Diplomacy. 
webDiplomacy maintains a modernized gameplay setup, a large community of some of the world's best Diplomacy players, and a variety of extra unique features. 
Our goal is to allow players from around the world to play a fun, challenging game without having to commit tons of time and money, but still supporting 
the high quality, world class gameplay that is necessary to get the best Diplomacy experience.
<br><br>
To get an in depth look at how webDiplomacy works and how you can play Diplomacy on our site, check out our <a href='intro.php' class='light'>intro to webDiplomacy</a> 
as well as <a href='variants.php' class='light'>our variant Diplomacy boards</a> and how we <a href='points.php' class='light'>score our games and rank players</a>.",

"Do I have to pay to use webDiplomacy?" => "No! webDiplomacy is a completely free place to play Diplomacy without any intrusive advertisements or paywalls. 
We will never charge you for extra features or require you to give any payment information in order to play.
<br><br>
However, webDiplomacy still needs money for operating costs and regular server maintenance and as such we accept and appreciate donations, small or large. 
You are not required to donate and will never be pressured to donate to webDiplomacy, but if you would like to donate, you can check out our 
<a href='donations.php' class='light'>donations page</a>, which explains in further detail why we need donations and makes it easy for you to contribute.",

"I like webDiplomacy and want to give back. How can I help?" => "There are a number of ways that you can help webDiplomacy. The simplest way to help is by 
<a href='donations.php' class='light'>donating.</a> Your donations are used to maintain our server and handle generic, everyday operating costs. 
Without donations, webDiplomacy would be unable to continue operating.
<br><br>
However, donating is not the only way you can help. If you have time and a fair amount of experience playing Diplomacy online, 
we are always looking for skilled, patient, and experienced moderators. webDiplomacy's moderators are the best trained and most effective 
of any Diplomacy site online at keeping games free of cheating and ensuring that gameplay is as smooth as possible at all times. 
Moderators are also responsible for welcome and assisting new players, assisting with tournaments and special games, and managing any player emergencies. 
If you are an experienced, patient Diplomacy player with an aptitude for spotting suspicious behaviior and a desire to help out on webDiplomacy, 
send us an email at <a href='mailto:".Config::$adminEMail."' class='light'>".Config::$adminEMail."</a> with your username and a brief bit about why 
you think you would make a good moderator. Even if we don't need new moderators right this second, we will keep your application on file and you will be 
at the top of our list whenever we need new moderators down the road.
<br><br>
If moderating just doesn't sound like your cup of tea but you are a skilled developer, webDiplomacy could always use your help. 
Skilled developers with experience in PHP, JavaScript, or SQL, as well as HTML or CSS, can check out <a href='developers.php' class='light'>our developers' page</a> 
or <a href='mailto:".Config::$adminEMail."' class='light'>contact the moderators</a> to see if you would be able to help us. 
webDiplomacy can also use help from graphic designers and icon artists to keep our site looking fresh and updated.",

"Can I play on my phone?" => "Yes! webDiplomacy is compatible with your mobile device.",

"Where can I find the rules that I need to follow?" => "You can find a complete list of our site rules <a href='rules.php' class='light'>here</a>. 
If you have questions about a rule or need help, you can always <a href='mailto:".Config::$adminEMail."' class='light'>contact the moderators</a>.",


"Playing on webDiplomacy" => "Sub-section",

"I want to learn how to play Diplomacy!" => "While it is impossible to master the art of Diplomacy, the game itself is an easy game to begin to learn. 
The <a href='intro.php' class='light'>intro page</a> is a good starting point for learning the basic mechanics of the game of Diplomacy online. 
Once you have a good command of how the game works, <a href='gamelistings.php' class='light'>start playing</a>! There is no better way to learn 
beyond the basics than by actually playing the game.
<br><br>
If you really want to dive into the more advanced guide of how Diplomacy works, check out 
<a href='http://www.wizards.com/avalonhill/rules/diplomacy.pdf' class='light'>Avalon Hill's rulebook</a> on Diplomacy. 
Be mindful that Avalon Hill's rulebook is written for those who purchased the board game itself, not for those who are playing online, so there are some inconsistencies. 
For example, webDiplomacy does not offer an alternative for six players on the classic board. Instead, we offer different Diplomacy boards called 
<a href='variants.php' class='light'>variants</a> where you can play on a different map with different amounts of players. 
Using our variaint boards, you can play with as many as 34 players or as few as 2.
<br><br>
If you have questions about the game of Diplomacy or want to talk strategy with the rest of our online Diplomacy community, 
check out <a href='/contrib/phpBB3/viewforum.php?f=6' class='light'>our forum</a>. You will find all sorts of insightful discussions there 
and are always welcome to ask your own questions as well.",

"I already know how to play. But where do I start?" => "If you already know what you're doing, <a href='gamelistings.php' class='light'>start playing</a>! 
You can join games that other people have created, take over open positions in ongoing games left vacant, or create your own game and allow others to join it.",

"What are all these settings on the game creation page?" => "When you create a game on webDiplomacy, you have to specify some settings. 
First and foremost, every game has a name, bet size, and phase length. The name is up to you, so long as it abides by our <a href='rules.php' class='light'>site rules</a>. 
You can bet as few as 5 or as many points as you have to your account. Every other player that joins will have to match your bet, 
and then when the game is over the points will be distributed according to the <a href='points.php#DSS' class='light'>scoring system</a> 
that you choose. Finally, the phase length is how long you have to enter your orders in each phase. Since everyone enters their orders simultaneously and in secret, 
pick a deadline that makes sense for how long you want to play. If you want to play an entire game today, try a live game with 5-30 minute phase lengths. 
If you only want to check the site every few days, play a game with a longer phase length so you have more time.
<br><br>
Next, you'll choose the type of game messaging you want to play. By default, your game will be set to 'all,' or full press, messaging, which means everyone 
can speak with everyone else at all times. If you choose 'global only,' or public press, you can only send messages globally where everyone can see them. 
If you choose 'no messaging,' or gunboat, you cannot send any messages at all. You can also play 'per rulebook,' which is full press during moves phases 
and no press at all during retreat and build phases. Face-to-face Diplomacy is generally played this way.
<br><br>
You can then choose which one of webDiplomacy's <a href='variants.php' class='light'>variants</a> you want to play, which <a href='points.php#DSS' class='light'>scoring system</a> 
you want to use to distribute the points at the end of the game, and whether or not other players in your game should be anonymous, which hides their display names. 
You can also set your game to show or hide all draw votes that other players cast and set the minimum <a href='intro.php#RR' class='light'>reliability rating</a> 
and set the number of phases in which a player can fail to enter orders before they are automatically removed from the game.
<br><br>
Lastly, you can make your game private by adding an invite code, which is like a password for your game. If you are playing a game with anyone that you know 
outside of webDiplomacy, our <a href='rules.php' class='light'>site rules</a> mandate that you add an invite code to your game to make it private. 
You can share the invite code with your friends, family, or colleagues so that you can all play your game within our rules.",

"What do the 'save' and 'ready' buttons do?" => "When you are playing a game, you can either save your orders, which means that the game will remember them 
and store them for you to change later if you decide to alter them again before the deadline, or ready your orders, which means that you do not intend to 
change your orders again. If every player has readied their orders, the game will process to the next phase regardless of when the deadline is, 
so if you ready your orders, make sure that you don't want to change them again!",

"Why are my order choices red?" => "Red order choices are unsaved, so if you decide to close your browser or tab, the game will not remember 
the orders that you put in. You should save or ready your orders in order to make sure that the game knows what you entered and that you don't end up 
failing to enter orders if the deadline passes.",

"How do I win?" => "The goal of every game is to 'solo' the board, or hold the majority of the supply centers available. This is how you win in Diplomacy. 
However, it is very difficult to solo the board and thus most Diplomacy games do not end in a solo. That is why there are other positive results, such as draws. 
Depending on the <a href='points.php#DSS' class='light'>scoring system</a> of the game, a draw will distribute a certain number of points to each surviving player.",

"What do those icons mean? ( <img src='images/icons/tick.png' />, <img src='images/icons/alert.png' /> , <img src='images/icons/mail.png' /> , etc.)" => "If you see an icon 
and don't understand what it means, try hovering your mouse over it. It may give you a hint as to what it means.",

"What is the 'notes' tab and why is it different colors in different games?" => "In a full press game, every country has tabs to message other players. 
Instead of simply removing the tab for the country that you are playing from your press toolbar, there is a section for you to take notes on the game. 
You can send notes to yourself just like you would send press to other players, but nobody can see your notes except for you! Feel free to use your notes 
tab as much or as little as you like in all of your games.",

"Why is it saying that I can't send someone a message?" => "Some players utilize the mute function, which allows them to prevent someone they don't like from sending 
them any more messages in that game. If you are muted by another player, the game is not broken and you are not in trouble, but you cannot send them any more messages unless they 
decide to unmute you again.",

"I entered an order but the game made me do something else!" => "It is not all that unusual for players to make mistakes when entering their orders. 
The site administrators have received complaints about orders not being adjudicated properly many times. However, since webDiplomacy was founded in 2004, 
there has not been a single instance where there was any evidence that our game adjudicator made an error. The best thing that you can do to minimize 
misorders in the future is to double check all of your orders, and even if you do, know that they do still occasionally happen as we are all humans and make mistakes.",

"I need to talk to a moderator in a game I'm playing!" => "You can contact the moderators at any time by using the 'Need help?' button located just below the order entry on the screen. 
That button will direct you to a form that you can send to the moderators and they will help you.",

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
"How are the points split in a draw?" => "This depends on the scoring system of the game in question.<br/>
	Read <a href='points.php' class='light'>the scoring system and points guide</a> for more info about how games our scored and how the points system works on webDiplomacy.",
"Can you draw the game, but give 2/3rds of the points to this player and ..." => "The way draws split points depends on the scoring system. 
Read <a href='points.php' class='light'>the scoring system and points guide</a> for more info about how points are split up depending on the result of each game.",

"Bugs" => "Sub-section",
"My game has crashed!" => "Sometimes (usually only shortly after code updates) a software bug or server error may occur while a
	game is being processed.
	When this happens the problem is detected, all changes are undone, and the game is marked as crashed.<br /><br />
	Admins will see a message whenever a game crashes, and information about the crash is saved so that the problem that caused it can be fixed quickly.
	Once a mod or admin has marked the game as OK the game will continue where it left off again.<br /><br />

	If your game has been crashed for a long time try asking about it in the forum.",
"The phase ends \"Now\"? \"Processing has been paused\"?" => "When the server detects that no games have processed for a while
	(over 30 minutes or so), or a moderator/admin sees a problem and hits the panic button, all game processing is disabled until
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
	not misunderstanding the rules. 99.999% of the time \"adjudicator bugs\" turn out to be a misunderstanding.<br />",
"A part of the site looks wrong in an alternative browser" => "webDiplomacy isn't currently completely web standards compliant,
	so there may be glitches. We would like to get webDiplomacy working on everything (within reason) but we need users
	of alternative browsers to let us know what's wrong and tell us how to make it look right in that browser.",

"Feature Requests" => "Sub-section",
"A point and click interactive map" => "This is being worked on, but progress is slow.",
"Translations" => "A translation system is built into our site and several people have created websites to play this game in Russian and Italian. Ask on the forum for links to these sites.",
"New variants" => "If a variant has lasting appeal, is well balanced, isn't gimmicky, has been tried and tested on another server, and was
	created by a reputable developer, then it's up for consideration to be included in the standard release.<br />
	<br />
	Also creating your own variants or porting
	existing variants to the webDiplomacy variants system is easier than ever, from simple map-change variants all the
	way to strange rule-changing variants, the system is flexible enough to accommodate your variant ideas.	",

"Helping out" => "Sub-section",
"Can I help develop the software?" => "You sure can: if you're an HTML/CSS/JavaScript/PHP 5/MySQL/SVG/Canvas developer,
	graphics/icon artist, or want to learn, check out the <a class='light' href='/developers.php'>dev info</a>,
	and if you get lost you can get help/discuss ideas in the <a class='light' href='/contrib/phpBB3/viewforum.php?f=16'>developer forums</a>.",
"How else can I help?" => "Tell your friends about webDiplomacy, put links on your website, help new players out in the forums,
	and give helpful feedback to developers. If you enjoy the game please consider buying a physical version of the board game. Thanks!",

	"What's the software license?" => "The <a href='AGPL.txt' class='light'>GNU Affero General License</a>
	(<a href='http://www.opensource.org/licenses/agpl-v3.html' class='light'>Open Source Initiative</a> approved),
	which basically says you can download and change the code as you like, and put it up on your
	own website, but you can't claim you wrote it, and you have to give any changes
	you make back to the community.<br /><br />
	See <a href='credits.php' class='light'>the credits</a> for information about the
	small elements which are under different licenses.",

"Map" => "Sub-section",
"Why are some orders missing from the map?" => "Not all orders are drawn on the small map. Below the small map there is a set of icons;
	the one in the middle (<img src='images/historyicons/external.png' alt='example' />) opens up the large map, which contains all orders.<br/>
	Also at the bottom of the board page is a link to open up a textual list of all the orders entered in the game, if you can't see
	something in the large map.",
"Do you have colorblind friendly maps?" => "We have several colorblind options available in the settings page of your profile. These settings
only work on the small map in games currently. Currently we have support for Protanope, Deuteranope, and Tritanope."
);

foreach($globalFaq as $Q=>$A)
{
	$faq[$Q]=$A;
}

$i=1;

print libHTML::pageTitle('Frequently Asked Questions','Answers to the questions people often ask in the forums; click on a question to expand the answer.');

$sections = array();
$section=0;

foreach( $faq as $q => $a )
{
	if ( $a == "Sub-section" )
	{
		$sections[] = '<a href="#faq_'.$section++.'" class="faq">'.$q.'</a>';
	}
}

print '<div class = "faq" style="text-align:center;"><strong>Sections:</strong></br> '.implode(' - ', $sections).'</div> <div class="hr"></div>';

$section=0;

foreach( $faq as $q => $a )
{
	if ( $a == "Sub-section" )
	{
		if( $section ) { print '</div>'; }

		print '<div class = "faq"><h2 class = "faq"><a name="faq_'.$section.'"></a><strong>'.$q.'</strong></h2>';

		$question=1;
		$section++;
	}
	else
	{
		print '<button class="faq_question" name="faq_'.$section.'_'.$question.'" onclick="FAQShow('.$section.', '.$question.'); return false;">'.$q.'</button>';
		print '<div class="faq_answer" style="margin-top:5px; margin-bottom:15px;"><p class = "faq">'.$a.'</p></div>';
		$question++;
	}
}

print '</ul></div>
</div>';

?>
<script type="text/javascript">
var coll = document.getElementsByClassName("faq_question");
var searchCounter;

for (searchCounter = 0; searchCounter < coll.length; searchCounter++) {
  coll[searchCounter].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
		if (content.style.display === "block") { content.style.display = "none"; } 
		else { content.style.display = "block"; }
  });
}
</script>

<?php
libHTML::footer();
?>
