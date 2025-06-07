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
var configureSSE = function(gameID, countryID) {

    const overviewChannel = 'private-game' + gameID;
    const messageChannel = 'private-game' + gameID + '-country'+countryID;

    // Wait a few seconds before doing this, as unless the user is staying on this page they won't need to get notifications:
    setTimeout(() => {
        apiCall(
        'sse/authentication',
        'JSON',
        { channel_name: messageChannel, gameID },
        function (response) {
            console.log("sse/authentication: Successfully authenticated");
            var auth = response.responseJSON.data.auth;
            console.log("sse/authentication: Auth: ");
            console.log(auth);
            console.log("sse/authentication: Connecting to SSE server with auth");

            var channels = overviewChannel;
            if( countryID > 0 ) channels = channels + ',' + messageChannel;
            
            // http is fine; nothing sensitive is sent over this connection
            var sseURL = `/events?auth=${encodeURIComponent(auth)}&channelList=${encodeURIComponent(channels)}`;
            
            var eventSource = new EventSource(sseURL);
            eventSource.onopen = () => {
                console.log('Connected to SSE server');
            };

            // Set next reconnect time to now + 30 seconds:
            var nextReconnectTime = new Date();
            nextReconnectTime.setSeconds(nextReconnectTime.getSeconds() + 30);

            eventSource.onerror = (e) => {
                console.log('Connection error or closed. Will attempt reconnection in 5 seconds.');
                eventSource.close();
                nextReconnectTime = new Date();
            };

            // Every 5 seconds check if we need to reconnect:
            setInterval(() => {
                var now = new Date();
                if( eventSource != null && now >= nextReconnectTime )
                {
                    console.log('Nothing received from server in reconnect timeout period. Reconnecting');
                    eventSource.close();

                    eventSource = null; // Ensure this timer won't keep reconnecting
                    
                    configureSSE(gameID, countryID); // Reconfigure SSE connection
                }
            }, 5000);

            eventSource.onmessage = (e) => {
                try {
                    const data = JSON.parse(e.data);
                    // If message starts with "overview", it's an overview message:
                    // Message = vote-sent|processed|message
                    
                    console.log(`Message received via SSE: ${e.data}`);

                    // Update the next reconnect time to 30 seconds from now:
                    var newReconnectTime = new Date();
                    newReconnectTime.setSeconds(newReconnectTime.getSeconds() + 30);
                    nextReconnectTime = newReconnectTime;

                    // If data.message contains "message":
                    if (data.message && data.message.includes("message")) {
                        console.log(`New game message received`);
                        var messageSentArea = document.getElementById('websocketsMessageSent');
                        if( messageSentArea )
                        {
                            messageSentArea.innerHTML = "New message received: <a href='board.php?gameID="+gameID+"&monitorUpdated="+Math.round(10000.0*Math.random())+"#monitorUpdated'>Click here</a> to refresh the board.";
                        }
                    } else if (data.message && data.message.includes("vote-sent")) {
                        console.log(`Vote cast in game.. ignore`);
                    } else if (data.message && data.message.includes("processed")) {
                        console.log(`Game processed`);

                        var gameProcessedArea = document.getElementById('websocketsGameProcessed');
                        if( gameProcessedArea )
                        {
                            gameProcessedArea.innerHTML = "Game has been processed: <a href='board.php?gameID="+gameID+"&monitorUpdated="+Math.round(10000.0*Math.random())+"#monitorUpdated'>Click here</a> to refresh the board.";
                        }
                    }
                    else if (data.message && data.message.includes("ping")) {
                        console.log(`Ping received`);
                    }
                } catch {
                    console.log(`Raw message: ${e.data}`);
                }
            };
        },
        function (response) {
            console.error("sse/authentication: Got error authenticating against sse/authentication: " + response);
        }
        );
    }, 7000);
}
/*
function monitorForUpdate(gameID, turn, phase, checkInterval)
{
    console.log('Monitoring for update: '+gameID+' '+turn+' '+phase+' '+checkInterval);

    var turnHasChanged = false;
    var haltAfterRequestNumber = 1000;
    
    var d = new Date();
    var time = d.getTime();

    var monitorForUpdateStatus = document.getElementById('monitorForUpdateStatus');
    if( monitorForUpdateStatus ) {
        monitorForUpdateStatus.innerHTML = '...';
    }

    timerInterval = setInterval(function() {
        if( monitorForUpdateStatus ) {
            monitorForUpdateStatus.innerHTML = '...';
        }
        apiCall(
            'game/getLastUpdateTime', 
            'GET', 
            {
                monitorGameID: gameID,
                lastUpdateTime: lastUpdateTime
            },
            function(response) {
                if( response.turn != turn )
                {
                    window.location.href = 'board.php?gameID='+gameID+"&monitorUpdated="+Math.round(10000.0*Math.random())+"#monitorUpdated"; // Random number to force a reload
                }
                if( monitorForUpdateStatus ) {
                    monitorForUpdateStatus.innerHTML = '...';
                }
            },
            function(response) {
                if( monitorForUpdateStatus ) {
                    monitorForUpdateStatus.innerHTML = '...';
                }
            }
        );
    }, checkInterval);

    
}*/