<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Maps
{

	function mapHTML($turn)
	{
		global $Game;
		return '<p style="text-align:center">
			<img src="map.php?gameID='.$Game->id.'&turn='.$turn.'" title="Small map for this turn" /><br />
			Large map: <a href="map.php?gameID='.$Game->id.'&largemap=on&turn='.$turn.'">
						<img src="images/historyicons/external.png" alt="Large map"
							title="This button will open the large map in a new window. The large map shows all the moves, and is useful when the small map isn\'t clear enough."
						/></a>
			</p>';
	}
	
	function outputHTML()
	{
		global $Game;
		
		for($i=$Game->turn;$i>=0;$i--)
		{
			if($i<$Game->turn && ($i%2)!=0) print '<div class="hr"></div>';

			print '<h4>'.$Game->datetxt($i).'</h4>';
			print $this->mapHTML($i);
		}
	}
}
 
print '<h3>'.l_t('Maps').'</h3>';

$MA=$Game->Variant->Maps();
print '<table>'.$MA->OutputHTML().'</table>';
print '</div>';

?>