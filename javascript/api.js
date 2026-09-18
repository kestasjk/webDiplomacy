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
// auth is the token the page was given for the SSE server, and authTime when that was (both unset to request one)
var configureSSE = function(gameID, countryID, turn, phase, renderTime, auth, authTime) {

    const overviewChannel = 'private-game' + gameID;
    const messageChannel = 'private-game' + gameID + '-country'+countryID;

    var showGameProcessedNotice = function() {
        var gameProcessedArea = document.getElementById('sseGameProcessed');
        if( gameProcessedArea )
        {
            gameProcessedArea.innerHTML = "Game has been processed: <a href='board.php?gameID="+gameID+"&monitorUpdated="+Math.round(10000.0*Math.random())+"#monitorUpdated'>Click here</a> to refresh the board.";
        }
    };
    var showMessageSentNotice = function() {
        var messageSentArea = document.getElementById('sseMessageSent');
        if( messageSentArea )
        {
            messageSentArea.innerHTML = "New message received: <a href='board.php?gameID="+gameID+"&monitorUpdated="+Math.round(10000.0*Math.random())+"#monitorUpdated'>Click here</a> to refresh the board.";
        }
    };

    // Events published while not connected are lost (the SSE server keeps no history). The SSE server is told
    // the turn, phase and time of this page when connecting, and sends the events this page missed, then a
    // catchup event. This does the same check here, for when it can't: it asked for a resync as it couldn't
    // tell (or had lost its Redis connection), or no catchup event came as it is an older SSE server.
    // Messages must be strictly newer than the page, as a message sent from this page re-renders it in
    // the same second.
    var checkForMissedUpdates = function() {
        if( turn === undefined ) return; // The page didn't give us its game state to compare against
        apiCall('game/pulse', 'GET', { gameID: gameID, countryID: countryID }, function(response) {
            var pulse = JSON.parse(response.responseText).data;
            if( pulse.turn != turn || pulse.phase != phase )
            {
                console.log('Game processed while not connected to the SSE server');
                showGameProcessedNotice();
            }
            if( pulse.lastMessageTimeSent > renderTime )
            {
                console.log('Message received while not connected to the SSE server');
                showMessageSentNotice();
            }
        });
    };

    if( auth && authTime === undefined ) authTime = new Date();

    var connect = function (auth, authTime) {
        console.log("Connecting to SSE server");

        var channels = overviewChannel;
        if( countryID > 0 ) channels = channels + ',' + messageChannel;

        // http is fine; nothing sensitive is sent over this connection
        var sseURL = `/events?auth=${encodeURIComponent(auth)}&channelList=${encodeURIComponent(channels)}`;
        if( turn !== undefined )
            sseURL += `&turn=${encodeURIComponent(turn)}&phase=${encodeURIComponent(phase)}&since=${encodeURIComponent(renderTime)}`;

        var eventSource = new EventSource(sseURL);
        var hasOpened = false;
        var catchupTimer = null;
        eventSource.onopen = () => {
            console.log('Connected to SSE server');
            hasOpened = true;
            // The SSE server should now send anything this page missed and then a catchup event
            catchupTimer = setTimeout(() => {
                console.log('No catchup event from the SSE server; checking for missed updates');
                checkForMissedUpdates();
            }, 5000);
        };

        // Set next reconnect time to now + 30 seconds:
        var nextReconnectTime = new Date();
        nextReconnectTime.setSeconds(nextReconnectTime.getSeconds() + 30);

        eventSource.onerror = (e) => {
            console.log('Connection error or closed. Will attempt reconnection in 5 seconds.');
            eventSource.close();
            nextReconnectTime = new Date();
            // If it never connected the token may have been refused, so request a new one
            if( !hasOpened ) auth = undefined;
        };

        // Every 5 seconds check if we need to reconnect:
        setInterval(() => {
            var now = new Date();
            if( eventSource != null && now >= nextReconnectTime )
            {
                console.log('Nothing received from server in reconnect timeout period. Reconnecting');
                eventSource.close();
                if( catchupTimer ) clearTimeout(catchupTimer);

                eventSource = null; // Ensure this timer won't keep reconnecting

                // Tokens are accepted for a day, so request a new one when this one is getting close to that
                if( auth && (now - authTime) > 23*60*60*1000 ) auth = undefined;

                configureSSE(gameID, countryID, turn, phase, renderTime, auth, authTime); // Reconfigure SSE connection
            }
        }, 5000);

        eventSource.onmessage = (e) => {
            try {
                const data = JSON.parse(e.data);
                // If message starts with "overview", it's an overview message:
                // Message = set-vote|processed|message

                console.log(`Message received via SSE: ${e.data}`);

                // Update the next reconnect time to 30 seconds from now:
                var newReconnectTime = new Date();
                newReconnectTime.setSeconds(newReconnectTime.getSeconds() + 30);
                nextReconnectTime = newReconnectTime;

                if (data.channel === 'resync') {
                    // The SSE server can't tell what this page missed: a key it checks wasn't set, or it lost
                    // its Redis connection
                    console.log(`Resync requested`);
                    checkForMissedUpdates();
                } else if (data.channel === 'catchup') {
                    console.log(`SSE server has sent anything this page missed`);
                    if( catchupTimer ) clearTimeout(catchupTimer);
                // If data.message contains "message":
                } else if (data.message && data.message.includes("message")) {
                    console.log(`New game message received`);
                    showMessageSentNotice();
                } else if (data.message && data.message.includes("set-vote")) {
                    console.log(`Vote cast in game.. ignore`);
                } else if (data.message && data.message.includes("processed")) {
                    console.log(`Game processed`);
                    showGameProcessedNotice();
                }
                else if (data.message && data.message.includes("ping")) {
                    console.log(`Ping received`);
                }
            } catch {
                console.log(`Raw message: ${e.data}`);
            }
        };
    };

    // Wait a few seconds before doing this, as unless the user is staying on this page they won't need to get notifications:
    setTimeout(() => {
        if( auth )
        {
            connect(auth, authTime);
            return;
        }
        // The page had no token, or the one it had expired or was refused
        apiCall(
        'sse/authentication',
        'JSON',
        { channel_name: messageChannel, gameID },
        function (response) {
            console.log("sse/authentication: Successfully authenticated");
            connect(response.responseJSON.data.auth, new Date());
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