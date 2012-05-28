<?php

defined('IN_CODE') or die('This script can not be run by itself.');

$faq = array();

$globalFaq = array(
"Variants" => "Sub-section",
	"Why are here so many variants?" => 
		"With the new variant-framework of the webdip-code it's easy to make custom variants.
		The main webdiplomacy.net is very careful about adding new variants, so this is a place
		where developers can test their ideas. ",
	"Who did the variants" => 
		"You can check the variant-description of each variant using the \"Variants\"-tab.",
	"What is the best variant?" => 
		"Different variants are for different tastes. 
		You can check the variants-page for the overall rating of a variant or see what variant is played most at the moment.",
	"Why is it that 2-player variants only allow 1-DPoint bets?" => 
		"To prevent abuse the 2-player variants are limited to a very low DPoint-count. 
		People abused this to gain massive amounts of points in the early days...",
	"There is a bug in a variant." => 
		"Please report this in the <a href=\"modforum.php\">Modforum</a>.",
	"Some player took advantage of an bug, and now the game is screwed." => 
		"If the game processed only one turn the last turn will be reprocessed. 
		The bugged order will be set to Hold/Destoy. 
		If the game progressed more than one turn the bug will be fixed, but no action on that game will be taken.",

"Interface" => "Sub-section",
	"Pregame-Chat" => 
		"There is a chat-window in your games during the pregame phase too. 
		So you can discuss custom rules and kill some time bevore the game starts. 
		Make sure to check the chat bevore the game starts.",
	"You can turn off the move-arrows" => 
		"In the board-view there is an option to turn off the move-arrows (<img src=\"images/historyicons/hidemoves.png\" alt=\" \">),
		so you can get a plain board with just the units.",
	"PM notification" =>
		"If you receive a new PM you get an alert.",
	"Block user" =>
		"If you encounter an unpleasant user to play agains you can choose to \"block\" him.
		To do this enter his profile and click on the smilyface behind it's name.
		You can unblock the user by clicking the face again.
		In your settings page there are all blocked users listed and you can choose th unblock any users there too.",
	"Mod forum" =>
		"Instead of sending an EMail the preferred way to contact the mods is opening a thread in the <a href=\"modforum.php\">Modforum</a>.
		You can access this from the \"Mods\"-tab.
		A message postet there will alert all mods to check on this issue and you can keep track of your issue in the thread.
		You are only able to view threads you started and nobody else but the mods can see your threads there.",
	"Country switch" =>
		"If you can't play your games for some time please try to find a sitter.
		You can send your games temporary to a different player using your settings-page. (better explanation needed)",
	"Much improved variant-page" =>
		"You can browse many information and statistics for all variants and view and download the code for the variant.",
		
"Reliability-rating" => "Sub-section",
	"What's this numbers behind my name?" =>
		"Playing on this site you get an reliability rating (more info needed)",
	"Why is the RR needed?" =>
		"Players can cosse to play only reliable players and the better your rating, the more games you can join (more info needed)",
		
"Game options" => "Sub-section",
	"Select your country" =>
		"When creating a game, one of the advanced options allows you to choose your country,
		choosing random will give the usual random country distribution for all players,
		however choosing a country will allow each player the pick the country they want on a first come first serve basis.",
	"Set target SC's and target end-turn" =>
		"When creating a game, one of the advanced options allows you to limit the maximum number of turns that can be played
		and/or how many SCs need to be conquered before a winner is declared.
		Please check the variant-description for information about the average turns or the default SCs for a win.
		The winning player is decided by who has the most SCs after that turn's diplomacy phase.
		If 2 or more player have the same SCs at the end of the game, the game checks for the turn before, and so on.
		If player's SC counts are the same throughout the whole game the winner is decided at random.",
	"Special NMR-CD-phase-extend" =>
		"This special rule sends a country into civil disorder (CD) if it does not enter an order (NMR) and extend the phase so a replacement can be found.
		This works on all phases of a turn (diplomacy, retreat, build).
		Be careful, this might lead to locked games, if players leave and no replacement is found.",

"Special votes" => "Sub-section",
	"Concede" =>
		"If everyone (but one) votes Concede the game will end and the player _not_ voting Concede will get all the points.
		Everybody else will get a defeat.
		The main purpose is in 2-player games that have a clear winner you don't need to play many fake-moves till one player reach the winning SC-count.",
	"Extend" =>
		"If 2/3 of the active players vote Extend the the current phase will be extended by 4 days.
		You can extend the same phase more than once to push the process-date back even further.",

"rlGroups/rlPolicy" => "Sub-section",
	"What is a rlGroup?" =>
		"A rlGroup is a list of players on this site who know each other in real life (most likely from school or work, but could also be friends or family).",
	"Why have rlGroups?" =>
		"The purpose of the rlGroup is to make it easier for the Mods to track people who know each other in order to make it hard for multis and metas to work.
		RlGroups are not meant to stop friends from playing against each other, it just is to make the other players and mods aware that you know each other in person.
		This is esp. important if the mods need to resolve Metagaming/Multiaccount issues.<br>
		Remember, if you know your ally in real life, it is easier to communicate verbally, than just online, this gives players an advantage that they shouldn't have in an online game.<br>
		The rlGroups makes the other players aware of this, which should reduce peoples ability to metagame or have multiple account, reducing the amount of cheaters on the site.",
	"What if I want to play with my friends?" =>
		"Don't worry, friends will still be allowed to play with each other.
		Playing with someone in your rlGroup just means that there will be a message at the start of the game informing players that you know each other.<br>
		However during game creation, it is now possible to make it so friends cannot join the same game. In these games it is no longer possible to join with rlFriends.<br>
		The default setting for non-anon games has this turned off, but for those players who are scared of metagaming, they can turn on this setting.",
	"I created a game for my friends only, but other players joined before it started..." =>
		"During gamecreation you can choose to allow only players in the same group as you to join your games,
		however make sure you have enough friends who will join this game, otherwise the game may be canceled because not enough people joined.",
	"How do I add/remove friends to my group?" =>
		"You can't change the members of your group.
		You need to contact the mods at the <a href='modforum.php'>modforum</a>.",
	"Help! I don't know all these users in my group. How do I leave this group?" =>
		"You need to contact the mods at the <a href='modforum.php'>modforum</a> too. ",
);
foreach($globalFaq as $Q=>$A)
	$faq[$Q]=$A;

$i=1;

print libHTML::pageTitle('Features','Features you should be aware of (not available at webdiplomacy.net)');

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

function FAQInit() {
	FAQHide();
	ancor = self.document.location.hash.substring(1).match(/\d+/g);
	section  = ancor[0];
	question = ancor[1];
	if ( question != undefined)
	{
		$$('#faq_answer_'+section+'_'+question+' .faq_answer').map(function (e) {e.show();});
		$$('#faq_answer_'+section+'_'+question+' .faq_question').map(function (e) {e.setStyle({fontWeight:'bold'});});
	}
}

</script>
<?php libHTML::$footerScript[] = 'FAQInit();'; ?>

