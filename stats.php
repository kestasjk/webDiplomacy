<?php

require_once('header.php');
include('contrib/FusionChartsFree/FusionCharts.php');
define('DELETECACHE',1);

// all possible parameters:
$variantID = (isset($_REQUEST['variantID'])) ? (int) $_REQUEST['variantID'] : '0';      // The Variant-ID for the map

libHTML::starthtml();

if ($variantID == 0)
	Config::$variants[0]=' Choose a variant...';		
asort(Config::$variants);

print '<SCRIPT LANGUAGE="Javascript" SRC="contrib/FusionChartsFree/FusionCharts.js"></SCRIPT>';
print '<div class="content">';

//MapID
print '<b>Map: </b>
	<form style="display: inline" method="get" name="set_map">
	<input type="hidden" name="variantID" value="'.$variantID.'">
	<select name=variantID onchange="this.form.submit();">';
foreach ( Config::$variants as $id=>$name ) {
	print '<option value="'.$id.'"';
	if ($id == $variantID) 
		print ' selected';
	print '>'.$name.'</option>';
}
print '</select></form>';

if ($variantID != 0)
{
	$variant=libVariant::loadFromVariantID($variantID);
	$mapID=$variant->mapID;
	libVariant::setGlobals($variant);
	
	$c_info = array();
	$g_info = array();

	$g_info['All']   = 0;
	$g_info['Solo']  = 0;
	$g_info['Drawn'] = 0;
	$g_info['Winner-takes-all']         = 0;
	$g_info['Points-per-supply-center'] = 0;
	
	for ($i=1; $i<=count($variant->countries)+1; $i++)		
	{
		$c_info[$i]['Won']      = 0;
		$c_info[$i]['Drawn']    = 0;
		$c_info[$i]['Survived'] = 0;
		$c_info[$i]['Resigned'] = 0;
		$c_info[$i]['Defeated'] = 0;
		$g_info[$i.'-way']      = 0;	
		$c_info[$i]['SC']       = 0;
	}

	$tabl = $DB->sql_tabl('SELECT m.countryID, m.status, COUNT(*) FROM wD_Members m
							LEFT JOIN wD_Games g ON (m.gameID = g.id) 
							WHERE g.variantID='. $variant->id .' AND g.phase = "Finished"
							GROUP BY m.countryID, m.status');
							
	while(list($countryID,$status,$count) = $DB->tabl_row($tabl))
		$c_info[$countryID][$status] = $count;

	$tabl = $DB->sql_tabl(
		'SELECT status, COUNT(*), potType
			FROM (SELECT COUNT(*) as status, potType FROM wD_Members m
					LEFT JOIN wD_Games g ON (m.gameID = g.id) 
					WHERE g.variantID='. $variant->id .' AND (m.status IN ("Won","Drawn"))
					GROUP BY m.gameID
				) fin
			GROUP BY status');

	while(list($status,$count,$pot) = $DB->tabl_row($tabl))
	{
		if ($status == 1)
		{
			$g_info['Solo'] += $count;
		}
		else
		{
			$g_info[$status.'-way']  = $count;
			$g_info['Drawn'] += $count;
		}
		$g_info['All']   += $count;
		$g_info[$pot]    += $count;
	}

	$tabl = $DB->sql_tabl(
		'SELECT m.countryID, SUM(m.supplyCenterNo) FROM wD_Members m
			LEFT JOIN wD_Games g ON (m.gameID = g.id) 
			WHERE g.variantID='. $variant->id .' AND g.phase = "Finished"
			GROUP BY m.countryID');
			
	while(list($countryID,$SC) = $DB->tabl_row($tabl))
		$c_info[$countryID]['SC'] = $SC;
	
	list($turns) = $DB->sql_row('SELECT SUM(turn) FROM wD_Games WHERE variantID='.$variant->id.' AND phase = "Finished"');
	
	print '<ul><li><b>Number of games finished:</b> '.$g_info['All'].'</li>';

	if ($g_info['All'] > 0)
	{
	
		print '<li><b>Avg. turns played:</b> '.number_format($turns/$g_info['All'],2).' turns</li>';

		$count=array('Sea'=>0,'Land'=>0,'Coast'=>0,'All'=>0);
		$tabl = $DB->sql_tabl(
			'SELECT TYPE,count(TYPE) FROM wD_Territories t
				WHERE EXISTS (SELECT * FROM wD_Borders b WHERE b.fromTerrID = t.id && b.mapID = t.mapID) 
				&& t.mapID ='.$variant->mapID.' && t.name NOT LIKE "% Coast)%" 
			GROUP BY TYPE');
		while(list($type,$counter) = $DB->tabl_row($tabl))
		{
			$count[$type]=$counter;
			$count['All']+=$counter;
		}	
		print '<li><b>Territories:</b> '.$count['All'];
		print '<ul><li>Land: '.$count['Land'].' ('.number_format($count['Land']/$count['All']*100,2).'%)</li>';
		print '<li>Coast: '.$count['Coast'].' ('.number_format($count['Coast']/$count['All']*100,2).'%)</li>';
		print '<li>Sea: '.$count['Sea'].' ('.number_format($count['Sea']/$count['All']*100,2).'%)</li></ul>';

		// Get the hex-color of the country for the tables
		$id=1;
		$css=fopen('variants/'. $variant->name .'/resources/style.css', 'r');
		while (!(feof($css)))
		{
			$line = fgets($css);
			if ( strpos($line,'occupationBar') > 0)
				$color[$id++]=substr($line,strpos($line,'#')+1,6);
		}
		fclose($css);

		print '<li><b>Pottype:</b><ul><li>Winner-takes-all: '.$g_info['Winner-takes-all'].' game'.($g_info['Winner-takes-all']!=1?'s':'').'</li>';
		print '<li>Points-per-supply-center: '.$g_info['Points-per-supply-center'].' game'.($g_info['Points-per-supply-center']!=1?'s':'').'</li></ul>';

		print '<li><b>Results:</b><ul><li>Solo: '.$g_info['Solo'].' game'.($g_info['Solo']!=1?'s':'').'</li>';
		print '<li>Drawn:'.$g_info['Drawn'].' game'.($g_info['Drawn']!=1?'s':'').'</li>';
		if ($g_info['Drawn'] > 0)
		{
			print '<ul>';
			foreach ($g_info as $type=>$count)
			{
				if (strpos($type,'way') && ($count>0))
				{
					print '<li> '.$type.' draw: '.$count.'</li>';
				}
			}
			print '</ul>';
		}
		print '</ul>';
		
		print '<li><b>Results by country:</b></li>';	
		print '<table border="1" rules="groups">
				<thead>
					<tr>
					<th align="left">Country</th>
					<th align="center">Solos</th>
					<th align="center">Draws</th>
					<th align="center">Survivals</th>
					<th align="center">Eliminated</th>
					<th align="center">SCs &Oslash</th>
					<th align="center">Performance*</th></tr>
				</thead>
				<tfoot>
					<tr><td colspan=6><b>*Performance</b> = (15 x Solos + 5 x Draws + 1 x Survivals) / Games</td></tr>
				</tfoot>
				<tbody>';
				
		$alternate = false;
				
		for ($i=1; $i<=count($variant->countries); $i++)		
		{
			$c_info[$i]['Eliminated'] = $c_info[$i]['Resigned'] + $c_info[$i]['Defeated'];
			$c_info[$i]['Performance'] = round(($c_info[$i]['Won']*15 + $c_info[$i]['Drawn']*5 + $c_info[$i]['Survived']) / $g_info['All'],2);
			$c_info[$i]['SC'] = round($c_info[$i]['SC'] / $g_info['All'],2);
			
			$alternate = !$alternate;
			
			print '<tr class="replyalternate'.($alternate ? '1' : '2' ).'">
					<td>'.$variant->countries[$i-1].'</td>
					<td align="center">'.$c_info[$i]['Won'].'</td>
					<td align="center">'.$c_info[$i]['Drawn'].'</td>
					<td align="center">'.$c_info[$i]['Survived'].'</td>
					<td align="center">'.$c_info[$i]['Eliminated'].'</td>
					<td align="center">'.$c_info[$i]['SC'].'</td>			
					<td align="center">'.$c_info[$i]['Performance'].'</td>			
					</tr>';
		}
		print'</table><br>';

		$strXML['Drawn']       = "<graph caption='Draws by Country'          xAxisName='Country' yAxisName='Draws'       decimalPrecision='0' formatNumberScale='0'>";
		$strXML['Solo']        = "<graph caption='Solo Victories by Country' xAxisName='Country' yAxisName='Solos'       decimalPrecision='0' formatNumberScale='0'>";
		$strXML['Performance'] = "<graph caption='Performance by Country'    xAxisName='Country' yAxisName='Performance' decimalPrecision='0' formatNumberScale='0'>";
		$strXML['Result']      = "<graph caption='Result Types by Map'       xAxisName='Result'  yAxisName='Count'       decimalPrecision='0' formatNumberScale='0'>";
		$strXML['Result']     .= "<set name='Solo' value='".$g_info['Solo']."' color='".$color[1]."'/>";
		for ($i=1; $i<=count($variant->countries); $i++)		
		{
			$strXML['Drawn']        .= "<set name='".$variant->countries[$i-1]."' value='";
			$strXML['Drawn']        .= $c_info[$i]['Drawn'];
			$strXML['Drawn']        .= "' color='".$color[$i]."'/>";
			$strXML['Solo']         .= "<set name='".$variant->countries[$i-1]."' value='".$c_info[$i]['Won'].        "' color='".$color[$i]."'/>";
			$strXML['Performance']  .= "<set name='".$variant->countries[$i-1]."' value='".$c_info[$i]['Performance']."' color='".$color[$i]."'/>";
			if ($i > 1)
				$strXML['Result']   .= "<set name='".$i.                   "-way' value='".$g_info[$i.'-way'].        "' color='".$color[$i]."'/>";
		}
		$strXML['Drawn']       .=  "</graph>";
		$strXML['Solo']        .=  "</graph>";
		$strXML['Performance'] .=  "</graph>";
		$strXML['Result']      .=  "</graph>";
		
		echo renderChart("contrib/FusionChartsFree/FCF_Column3D.swf", "", $strXML['Performance'], "Performance", 700, 300);
		if ($g_info['Solo'] > 0)
			echo renderChart("contrib/FusionChartsFree/FCF_Column3D.swf", "", $strXML['Solo'] , "Solo", 700, 300);
		if ($g_info['Drawn'] > 0)
			echo renderChart("contrib/FusionChartsFree/FCF_Column3D.swf", "", $strXML['Drawn'], "Drawn", 700, 300);
		echo renderChart("contrib/FusionChartsFree/FCF_Column3D.swf", "", $strXML['Result'], "Result", 700, 300);
	
	}
}

?>