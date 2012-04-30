<?php

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * @package Base
 * @subpackage Static
 */


$faq = array();

$globalFaq = array(
"Variants" => "Sub-section",
"Why are here so many variants?" => "With the new variant-framework of the webdip-code it's easy to make custom variants.
	The main webdiplomacy.net is very carefull about adding new variants, so this is a place where developers can test their whacy ideas.",
"Who did the variants" => "You can check the variant-description of each variant using the \"Variants\"-tab.",
"What is the best variant?" => "Diffenet variants are for different tastes. You can check the variants-page for the overall rating of a variant or see what variant is played most at the moment.",
"Interface" => "Sub-section",
"Pregame-Chat" => "There is a chat-window in your games during the pregame phase too. So you can discuss custom rules and kill some time bevore the game starts. Make sure to check the chat bevore the game starts.",
	);

foreach($globalFaq as $Q=>$A)
	$faq[$Q]=$A;

$i=1;

print libHTML::pageTitle('Features','Features you should be aware of not available at webdiplomacy.net');


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
