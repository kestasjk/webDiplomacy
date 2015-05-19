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
// See doc/javascript.txt for information on JavaScript in webDiplomacy

// Current turn, -2 is undefined, -1 is pre-game
var turn=-2;

var noMoves='';
var preview='';

// Toggle the display of the Move arrows.
function toggleMoves(gameID, currentTurn) {
	if (noMoves == '') {
		noMoves = '&hideMoves';
		$('NoMoves').src = 'images/historyicons/showmoves.png';
	} else {
		noMoves = '';
		$('NoMoves').src = 'images/historyicons/hidemoves.png';
	}
	loadMapStep(gameID, currentTurn, 0)	
	loadMap(gameID, currentTurn, turn)
}

// Toggle the display of the Move arrows.
function togglePreview(gameID, currentTurn) {
	turn = currentTurn
	if (preview == '') {
		preview = '&preview&noCache=' + Math.floor((Math.random()*10000)+1); ;
		$('Start').up().style.visibility    = 'hidden';
		$('Backward').up().style.visibility = 'hidden';
        if($('NoMoves')) { // NoMoves might not exist on the map
          $('NoMoves').up().style.visibility  = 'hidden';
        }
		$('Forward').up().style.visibility  = 'hidden';
		$('End').up().style.visibility      = 'hidden';
	} else {
		preview = '';
		$('Start').up().style.visibility    = 'visible';
		$('Backward').up().style.visibility = 'visible';
		if($('NoMoves')) {
          $('NoMoves').up().style.visibility  = 'visible';
		}
        $('Forward').up().style.visibility  = 'visible';
		$('End').up().style.visibility      = 'visible';
	}
	loadMapStep(gameID, currentTurn, 0)	
	loadMap(gameID, currentTurn, turn)
}

// Increment or decrement the turn safely, factoring in the limits, then load the new turn
function loadMapStep(gameID, currentTurn, step)
{
	var oldTurn = turn;
	
	if( turn==-2 ) turn=currentTurn; // Initializing, display current turn
	
	turn += step;
	
	// Respect limits
	if ( turn < -1 )
		turn = -1;
	else if ( turn > currentTurn )
		turn = currentTurn;
	
	// Turn has changed
	if( oldTurn != turn )
		loadMap(gameID, currentTurn, turn);
}

// Update the map arrows for the new turn, making the disabled arrows gray
function mapArrows(currentTurn, newTurn)
{
	if ( newTurn == -1 )
	{
		$('Start').src = l_s("images/historyicons/Start_disabled.png");
		$('Backward').src = l_s("images/historyicons/Backward_disabled.png");
	}
	else
	{
		$('Start').src = l_s("images/historyicons/Start.png");
		$('Backward').src = l_s("images/historyicons/Backward.png");
	}
	
	// Draw the greyed icons if the user can go no further forward
	if ( newTurn == currentTurn )
	{
		$('Forward').src = l_s("images/historyicons/Forward_disabled.png");
		$('End').src = l_s("images/historyicons/End_disabled.png");
	}
	else
	{
		$('Forward').src = l_s("images/historyicons/Forward.png");
		$('End').src = l_s("images/historyicons/End.png");
	}
}
turnToText='';//() { return ''; }

// Load the map for the specified turn, refresh arrows. Assumes newTurn is valid, sets turn=newTurn
function loadMap(gameID, currentTurn, newTurn)
{
	turn=newTurn;
	
	// Draw the greyed icons if the user can go no further back
	mapArrows(currentTurn, newTurn);
	
	// Display the current date being viewed
	if( turn == currentTurn )
		$('History').hide(); // .. if viewing an old turn
	else
	{
		$('History').innerHTML = turnToText(turn);
		
		$('History').show();
	}
	
	// Add the Hide parameter if we have HideMoves activated
	newTurn = newTurn + noMoves
	
	// Add the Preview parameter if we have Preview activated
	newTurn = newTurn + preview
	
	// Update the link to the large map
	$('LargeMapLink').innerHTML = 
			' <a href="map.php?gameID='+gameID+'&turn='+newTurn+'&mapType=large'+(useroptions.showMoves =='No'?'&hideMoves':'')+'" target="blank" class="light">'+
			'<img src="'+l_s('images/historyicons/external.png')+'" alt="'+l_t('Open large map')+'" ' +
			'title="'+l_t('This button will open the large map in a new window. The large ' +
			'map shows all the moves, and is useful when the small map isn\'t clear enough.')+'" /><\/a>';
	
	// Update the source for the map image
	$('mapImage').src = 'map.php?gameID='+gameID+'&turn='+newTurn + (useroptions.showMoves=='No'?'&hideMoves':'');
}

function recolorMap() 
{
	if ($('mapImage').complete && useroptions.colourblind != 'No' && $('mapImage').src.substring(0,4) == 'http' ) {
	        Color.Vision.Daltonize($('mapImage'),
				{'type':useroptions.colourblind,
				'callback': function (c) {$('mapImage').src = c.toDataURL();}
				});
	}
}
Event.observe(window, "load", function() { Event.observe($('mapImage'),'load',recolorMap)} );
recolorMap();
