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

/*
A function to make it easy to interact with the API
*/
function apiCall(route, method, parameters, onSuccess, onFailure) {
	console.log('API call: '+route);

	new Ajax.Request('api.php?route='+route, 
		{
			method: method, 
			asynchronous : true,
			parameters: parameters,
			onFailure: function(response) {
				console.error('Failure calling API: '+response.responseText);

				if( onFailure )
					onFailure(response);
				else
					console.error('Error calling API: '+response.responseText);
			},
			onSuccess: function(response) {
				console.log('Success calling API: '+response.responseText);

				if( onSuccess ) onSuccess(response);
			},
            postBody: method.toLowerCase() == 'json' ? JSON.stringify(parameters) : null
		}
	);
}
function createSandboxGame(variantID)
{
    apiCall('sandbox/create', 'GET', {variantID: ( variantID ? variantID : 1 )}, function(response) {
        var data = JSON.parse(response.responseText);
        if( data.gameID )
        {
            window.location.href = 'board.php?gameID='+data.gameID;
        }
    }
    );
}

function copySandboxFromGame(gameID)
{
    apiCall('sandbox/copy', 'GET', {copyGameID: gameID}, function(response) {
        var data = JSON.parse(response.responseText);
        if( data.gameID )
        {
            window.location.href = 'board.php?gameID='+data.gameID;
        }
    }
    );
}

function moveSandboxTurnBack(gameID)
{
    apiCall('sandbox/moveTurnBack', 'GET', {gameID: gameID}, function(response) {
        window.location.href = 'board.php?gameID='+gameID+"&movedBack"+Math.round(10000.0*Math.random())+"#movedBack"; // Random number to force a reload
    }
    );
}

function deleteSandbox(gameID)
{
    if( confirm("Are you sure you want to delete this sandbox?") )
    {
        apiCall('sandbox/delete', 'GET', {gameID: gameID}, function(response) {
                window.location.href = '/';
            }
        );
    }
}
