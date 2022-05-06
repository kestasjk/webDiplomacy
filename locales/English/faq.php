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

"The Basics" => "Sub-section",

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
Moderators are also responsible for welcoming and assisting new players, assisting with tournaments and special games, and managing any player emergencies. 
If you are an experienced, patient Diplomacy player with an aptitude for spotting suspicious behavior and a desire to help out on webDiplomacy, 
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
Each rule is explained in detail there. If you have questions about a rule or need help, you can always <a href='mailto:".Config::$adminEMail."' class='light'>contact the moderators</a>.",


"Gameplay" => "Sub-section",

"I want to learn how to play Diplomacy!" => "While it is impossible to master the art of Diplomacy, the game itself is an easy game to begin to learn. 
The <a href='intro.php' class='light'>intro page</a> is a good starting point for learning the basic mechanics of the game of Diplomacy online. 
Once you have a good command of how the game works, <a href='gamelistings.php' class='light'>start playing</a>! There is no better way to learn 
beyond the basics than by actually playing the game.
<br><br>
If you really want to dive into the more advanced guide of how Diplomacy works, check out 
<a href='https://media.wizards.com/2015/downloads/ah/diplomacy_rules.pdf' class='light'>Avalon Hill's rulebook</a> on Diplomacy. 
Be mindful that Avalon Hill's rulebook is written for those who purchased the board game itself, not for those who are playing online, so there are some inconsistencies. 
For example, webDiplomacy does not offer an alternative for six players on the classic board. Instead, we offer different Diplomacy boards called 
<a href='variants.php' class='light'>variants</a> where you can play on a different map with different amounts of players. 
Using our variant boards, you can play with as many as 34 players or as few as 2.
<br><br>
If you have questions about the game of Diplomacy or want to talk strategy with the rest of our online Diplomacy community, 
check out <a href='/contrib/phpBB3/viewforum.php?f=6' class='light'>our forum</a>. You will find all sorts of insightful discussions there 
and are always welcome to ask your own questions as well.",

"I already know how to play. But where do I start?" => "If you already know what you're doing, <a href='gamelistings.php' class='light'>start playing</a>! 
You can join games that other people have created, take over open positions in ongoing games left vacant, or create your own game and allow others to join it.",

"What level of skill should I expect in my games?" => "Generally, the players that you play with can be similar to your skill level. 
As a new player, you will often find yourself playing with other new players, during which time you will be able to help each other learn and get better. 
As you get more comfortable, you will probably find yourself in some games with players more experienced than you are. You will notice these players 
and hopefully take the opportunity to learn from their tactics and diplomacy to make yourself a better player at the same time. Eventually, you may 
become a highly skilled, elite Diplomacy player, and perhaps will end up playing in site sponsored tournaments with other highly skilled, elite Diplomacy players.
<br><br>
Generally speaking, lower pot games, such as games where each player only bets 5 points, may have more players who are new or inexperienced. 
If you are more experienced and play in those games, we encourage you to be patient with new players. We were all new players once. 
The best way to welcome a newer player to the site is to play fairly and patiently, and perhaps offer constructive criticism that could help 
make them better in the long run.
<br><br>
If you find that you would like additional help with your Diplomacy strategy and tactics from a more experienced player on webDiplomacy, consider 
signing up for the <a href='https://docs.google.com/document/d/1dSq6zlizecb90F3OKSqyFUWGI32Or87ZNmkFmhCVz-c/edit' class='light'>
webDiplomacy Mentor Program</a>. In this program, you will be paired up with a more experienced player who will be available for you to ask questions, 
get constructive feedback, and receive advice and guidance.",

"What are all these settings on the game creation page?" => "When you create a game on webDiplomacy, you have to specify some settings. 
First and foremost, every game has a name, bet size, and phase length. The name is up to you, so long as it abides by our <a href='rules.php' class='light'>site rules</a>. 
You can bet as few as 5 or as many points as you have to your account. Every other player that joins will have to match your bet, 
and then when the game is over the points will be distributed according to the <a href='points.php#DSS' class='light'>scoring system</a> 
that you choose. Finally, the phase length is how long you have to enter your orders in each phase. Since everyone enters their orders simultaneously and in secret, 
pick a deadline that makes sense for how long you want to play. If you want to play an entire game today, try a live game with 5-30 minute phase lengths. 
If you only want to check the site every few days, play a game with a longer phase length so you have more time.
<br><br>
Next, you'll choose the type of game messaging you want to play. By default, your game will be set to 'all,' or full press messaging, which means everyone 
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

"What happens when I run out of points?" => "Every player has a certain amount of available points and total points. These numbers might be different. 
Your total number of points include the number of points which you have 'bet' into games you're currently playing in as well as the points you have in your account. 
Your available number of points includes only those that you could use to continue joining new games. While your available points may reach 0 if you have 
spent all of your points on ongoing games, your total number of points never falls below 100. When it does, you will automatically be topped off so that you have 100 total points again. 
Thus, while all your points might be tied up in games you're still playing right now, you will never truly run out of points, and once those games are finished, you will 
have more points to spend again even if you don't get points back from those games.",

"What do those icons mean? ( <img src='images/icons/tick.svg' />, <img src='images/icons/alert.svg' /> , <img src='images/icons/mail.svg' /> , etc.)" => "If you see an icon 
and don't understand what it means, try hovering your mouse over it. It may give you a hint as to what it means.",

"What is the 'notes' tab and why is it different colors in different games?" => "In a full press game, every country has tabs to message other players. 
Instead of simply removing the tab for the country that you are playing from your press toolbar, there is a section for you to take notes on the game. 
You can send notes to yourself just like you would send press to other players, but nobody can see your notes except for you! Feel free to use your notes 
tab as much or as little as you like in all of your games.",

"Why are some orders missing from the small map?" => "Not all orders are drawn on the small map. Below the small map there is a set of icons - 
the one in the middle (<img src='images/historyicons/external.svg' alt='example' />) opens up the large map, which contains all orders.",

"Why is it saying that I can't send someone a message?" => "Some players utilize the mute function, which allows them to prevent someone they don't like from sending 
them any more messages in that game. If you are muted by another player, the game is not broken and you are not in trouble, but you cannot send them any more messages unless they 
decide to unmute you again.",

"I entered an order but the game made me do something else!" => "It is not all that unusual for players to make mistakes when entering their orders. 
The site administrators have received complaints about orders not being adjudicated properly many times. However, since webDiplomacy was founded in 2004, 
there has not been a single instance where there was any evidence that our game adjudicator made an error. The best thing that you can do to minimize 
misorders in the future is to double check all of your orders, and even if you do, know that they do still occasionally happen as we are all humans and make mistakes.",

"I need to talk to a moderator in a game I'm playing!" => "You can contact the moderators at any time by using the 'Need help?' button located just below the order entry on the screen. 
That button will direct you to a form that you can send to the moderators and they will help you.",


"Our AI" => "Sub-section",

"What is AI?" => "AI stands for artificial intelligence. Artificial intelligence are computer programs designed to make decisions based on a range of possible outcomes and consequences just like a human 
would. Typical artificial intelligence you hear about regularly is driven by machine learning, which simply populates the AI program with data and statistics that allow it to \"improve\" 
at a task, or deep learning, which uses neural networks with layers and hidden information, just like a human brain has, and encourages the program to make connections between pieces of 
data and learn which results are good and which results are not. Artificial intelligence is in its infancy, but webDiplomacy has participated in some groundbreaking research into artificial 
intelligence that set us apart from other gaming sites across the internet.",

"So webDiplomacy supports artificial intelligence?" => "Yes! webDiplomacy was the first online Diplomacy site to support artificial intelligence. You can play no press games against 
bot users on our 7 player classic map or on either of our 1v1 maps.",

"How is your AI different from a computer player?" => "A computer player thinks programatically, and will always take the statistically optimal route in a given scenario without appreciating 
the context in which it exists. It is predictable, and thus it can be gamed. Our artificial intelligence, on the other hand, thinks like a human - while the statistically optimal route is often 
taken, our artificial intelligence users are capable of acting unpredictably. They do not always make the perfect decision, and sometimes even make bad decisions. While that may make it sound like 
a computer player would be more challenging, it actually means that our artificial intelligence users are a more accurate representation of a real human, because they are trained by hundreds of thousands 
of real humans just like you making all kinds of good and bad decisions.",

"How did you train the AI?" => "The webDiplomacy AI was developed in tandem with university researchers and trained on over 160,000 Diplomacy games across different scoring systems, 
different phase lengths, and on various platforms. The AI's strategies and tactics are up to standard and provide a challenge for any user, even the most experienced, and also provide an opportunity 
for real humans like you to experiment, learn, and perfect your game.",

"What research has webDiplomacy's AI been featured in?" => "Our artificial intelligence has been the subject of research by a couple of different artificial intelligence research teams, including 
Deepmind, a Google subsidiary, and Facebook AI. You can read Deepmind's research paper on training a gunboat AI model <a href='https://arxiv.org/pdf/2006.04635.pdf' class='light'>here</a>, and 
Facebook AI's research on building their own artificial intelligence user and how it stood up against our artificial intelligence and our actual human members 
<a href='https://arxiv.org/pdf/2010.02923.pdf' class='light'>here</a>.",

"How do I play against the AI?" => "You can play no press games against bot users on the classic map or on either 1v1 map. In order to play against the AI, create a new game and select 
\"Play a Game Against Bots.\" Once you're there, you can pick your variant and pick the country that you play, or simply play as a random country by default.",

"Where can I find more in depth information about the webDiplomacy's AI?" => "You can find more information by reading 
<a href='https://github.com/diplomacy/research/blob/master/neurips_paper_v1.pdf' class='light'>this research paper</a>, or you can hear a discussion with jmo1121109 and peterwiggin 
<a href='https://diplomacygames.com/rise-of-the-bots/' class='light'>here</a>.",


"Scoring" => "Sub-section",

"How are players ranked on webDiplomacy?" => "We have a number of different methods of ranking players on webDiplomacy. Our oldest and simplest method is points. A player with a higher 
number of points is, in theory, a better player than someone with fewer points. However, this is not always the case, and over time a true skill scoring system called the Ghost Ratings 
has gained popularity and since been integrated fully into our site.",

"What are the Ghost Ratings?" => "The Ghost Ratings were developed by TheGhostmaker as an alternative scoring system to points on webDiplomacy. 
The purpose of the ratings is to more accurately measure the true skill and ability of players by weighting games not by the size of the pot but 
by the the type of game they are playing and the ability and skill of the other players in that game. It also allows an accurate measurement depicting 
either improvement or regression over time, whereas once players have gained a significant amount of points, they generally do not lose all of them again. 
You can see your current overall rating <a href='grCategoryStats.php' class='light'>here</a>, as well how you rank in each Ghost Ratings category, which are broken down 
by Full Press games, Gunboat games, Live games, combined 1v1 games, France vs Austria 1v1 games, and Germany vs Austria 1v1 games.
<br><br>
For more detailed information on the Ghost Ratings, visit our dedicated <a href='ghostRatings.php' class='light'>ghost ratings page.</a>.",

"How are individual games scored on webDiplomacy?" => "Individual games are scored based on the scoring system that the game's creator chose when they made the game. 
The options are <a href='points.php#DSS' class='light'>draw-size scoring</a> and <a href='points.php#SoS' class='light'>sum-of-squares scoring</a>. 
<br><br>
Draw-size scoring is the default scoring system on webDiplomacy previous known as winner-takes-all. In a draw-size scoring game, points are split evenly between remaining 
powers in the event of a draw, and in the event of a solo the winner takes the entire pot. [ghost ratings info goes here]. 
<br><br>
Sum-of-squares scoring is an alternative scoring system to draw-size scoring where each surviving player in a draw receives points proportional to the number of supply 
centers they have remaining, and in the event of a solo the winner takes the entire pot. [ghost ratings info goes here]. ",


"The Forum" => "Sub-section",

"Didn't the forum look different before?" => "Yes, it did! webDiplomacy used to have a very basic forum that slowly became more and more problematic 
as webDiplomacy grew. When the site was small, the server load the forum caused was fairly small as well, but as the site got bigger and bigger and 
the forum became more popular, it was nearly impossible for the server to operate efficiently without erasing significant amounts of site data, 
including most of the old forum. Instead of continuously erasing the forum over and over again as time went on, we decided that it was best 
to change the forum entirely. The current forum is much more user friendly than the old one and very easy to navigate, plus it does not overload our server.",

"I'm looking for something on the forum. Where do I find it?" => "The forum is broken up into categories. If you are looking to start or join a new 
private game on the forum, or you're looking for some good Diplomacy strategy to read about, you may find it under the 'Diplomacy' category. 
You'll also find information about webDiplomacy's tournaments and the face-to-face community's tournaments and meetups there. 
If you're looking for some general off topic banter or politics, you'll find it under the 'miscellaneous' category. You will also find 
some players playing mafia, a popular game that some Diplomacy players have made a staple on webDiplomacy, in the 'forum games' section. 
You can also find webDiplomacy's news and announcements, as well as a place to leave feedback on features or development, at the top of the forum.",

"Where can I send and check my private messages?" => "You can send, read, and manage your private messages <a href='/contrib/phpBB3/ucp.php?i=pm' class='light'>here</a>. 
Currently, you must visit our forum before you will be able to send or receive private messages. Once you visit our forum, you will have access to your 
private message inbox and will get notifications when you receive new messages from other players.",

"Where can I find the rules for the forum?" => "You can find the forum rules <a href='rules.php#Forum%20Rules' class='light'>here</a>, 
as well as information on how the moderators manage the forum further down the page.",

"I can't see the forum. What's going on?" => "If a player breaks the <a href='rules.php#Forum%20Rules' class='light'>forum rules</a>, 
they will be unable to visit the forum for a certain amount of time. If this happens to you, you will have received an email to your site registered address 
from the moderators explaining what happened, how long it will persist, and what you can do to prevent it from happening again in the future.",


"Miscellaneous" => "Sub-section",

"Do you have colorblind friendly maps?" => "We have several colorblind options available in the <a href='usercp.php' class='light'>account settings page</a>. These settings
only work on the small map in games currently. Currently we have support for Protanope, Deuteranope, and Tritanope.",

"How does webDiplomacy's adjudicator work and what is it based on?" => "webDiplomacy's adjudication software is based on the official Diplomacy rules. 
However, in order to adapt to online gameplay where it would be extremely impractical to require players to adjudicate games manually, 
webDiplomacy developed a series of scripted tests called the Diplomacy Adjudicator Test Cases, or DATC, to lay out exactly how all sorts of tricky situations are processed, 
particularly in the cases where there is ambiguity in the rules. 
<br><br>
While examining the outputted results of the DATC is not useful or practical for most players, you can see the results <a href='datc.php' class='light'>here</a>. 
If you have a question about how a certain scenario would be processed, feel free to ask a question on our forum.",

"Someone says their orders got messed up, and now I'm paying the price!" => "Unfortunately, it does seem that sometimes people will claim that their orders 
came out wrong to cover up the intention of their actions. For example, they may say \"I was going to stab you, then read your message and changed 
my orders so I wasn't going to stab you, but my old orders came out instead of the new ones! Oh so sorry about that!\"
This is not allowed under <a href='rules.php' class='light'>the site rules</a> as it puts an unnecessary load on the site moderators by 
falsely claiming that a bug has been introduced on the site. If you are told that a bug caused a mistake in their orders, you should reserve some skepticism, 
and remember that the official server alone receives and processes tens of thousands of orders every single day yet has never made a mistake processing orders. 
Misorders can and do happen, but they have always been found to be the result of human error, not a software error.",

"Can I submit a new feature request?" => "webDiplomacy is always in constant development. We have a group of developers that have put in countless hours of work 
for the site without any expectation of pay, and because of their help we have an extremely well put together site, are constantly adding new features, 
and have the ability to change the site we have now for the better. If you have a feature request, you are more than welcome to post in our 
<a href='/contrib/phpBB3/viewforum.php?f=16' class='light'>developer forum</a>. However, you should know that we have a significant amount of development work 
- both fixes and improvements - that are in the works, some of which will take up a massive amount of time. As such, new feature requests should be 
limited to things that are feasible and ultimately necessary. Likewise, your feature request should be well thought out. You should be prepared to answer 
any questions that our developers or other players have about your feature request, and you should expect some constructive criticism as well.",

"When are you going to add new variants?" => "Every once in awhile, webDiplomacy will import another variant map. 
However, there is no set time period for this as many variants, even those that are already programmed on other Diplomacy sites, 
must be reviewed thoroughly before being added.",

"What is webDiplomacy's software license?" => "webDiplomacy is licensed under the <a href='AGPL.txt' class='light'>GNU Affero General License</a> 
(<a href='http://www.opensource.org/licenses/agpl-v3.html' class='light'>Open Source Initiative</a> approved). Open source means that you can download 
and change the code as you like and put it up on your own website, but you can't claim that you wrote it. Likewise, any changes that you make to webDiplomacy's code base must be 
made available to the webDiplomacy community.",


);

foreach($globalFaq as $Q=>$A)
{
	$faq[$Q]=$A;
}

$i=1;

print libHTML::pageTitle('Frequently Asked Questions','Answers to the questions people often ask in the forums; click on a question to show the answer.');

$sections = array();
$section=0;

foreach( $faq as $q => $a )
{
	if ( $a == "Sub-section" )
	{
		$sections[] = '<a href="#faq_'.$section++.'" class="faq">'.$q.'</a>';
	}
}

print '<div class = "faq" style="text-align:center;">'.implode(' - ', $sections).'</div> <div class="hr"></div>';

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
