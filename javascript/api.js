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
    apiCall('sandbox/delete', 'GET', {gameID: gameID}, function(response) {
        window.location.href = '/';
    }
    );
}
var configurePusher = function(appKey, host, wsPort, wssPort, gameID, countryID) {

    // This resolves within docker but not outside it, so if using the docker config allow the browser to connect using localhost:
    if( host == "webdiplomacy-websocket") host = "127.0.0.1";

    console.log(
      "PUSHER_APP_KEY, PUSHER_HOST, PUSHER_PORT, gameID, countryID",
      appKey,
      host,
      wsPort,
      wssPort,
      gameID,
      countryID
    );
    //private-game{gameID}
    //private-game{gameID}-country{countryID}
    
    // Pusher.logToConsole = true;
    const client = new Pusher(appKey || "app-key", {
      wsHost: host || "127.0.0.1",
      wsPort: Number(wsPort) || 6001,
      wssPort: Number(wssPort) || 6002,
      forceTLS: false,
      // encrypted: true,
      disableStats: true,
      enabledTransports: ["ws", "wss"],
      authorizer: (channel, options) => {
        const gameID = channel.name.split("-")[1].replace("game", "");
    
        return {
          authorize: async (socketId, callback) => {
            apiCall(
              'websockets/authentication',
              'JSON',
              { socket_id: socketId, channel_name: channel.name, gameID },
              function (response) {
                console.log("websockets/authentication: Successfully authenticated");
                var auth = response.responseJSON.data.auth;
                console.log("websockets/authentication: Key: " + auth);
                console.log(auth);
                console.log("websockets/authentication: Running callback");
                callback(null, { auth: response.responseJSON.data.auth });
                console.log("websockets/authentication: Done");
              },
              function (response) {
                console.error("websockets/authentication: Got error authenticating against websockets/authentication: " + response);
              }
            );
          },
        };
      },
    });
    
    client.connection.bind("connected", () => {
      console.log("websockets connected");
    });
    const overviewChannel = client.subscribe('private-game'+gameID);
    const channel = client.subscribe('private-game' + gameID + '-country'+countryID);
    console.log("websockets creating overview socket");
        
            overviewChannel.bind("overview", async (content) => {
                console.log("websockets overview: " + content);
                if( content == 'processed')
                {
                    var gameProcessedArea = document.getElementById('websocketsGameProcessed');
                    if( gameProcessedArea )
                    {
                        gameProcessedArea.innerHTML = "Game has been processed: <a href='board.php?gameID="+gameID+"&monitorUpdated="+Math.round(10000.0*Math.random())+"#monitorUpdated'>Click here</a> to refresh the board.";
                    }
                }
                
            });
        
            overviewChannel.bind("pusher:pong", () => {
                console.log("websockets overview: pong");
            });

            overviewChannel.bind("pusher:subscription_succeeded", () => {
                console.log("websockets overview pusher:subscription_succeeded");
            });
        
            overviewChannel.bind("pusher:subscription_error", (error) => {
                console.log("websockets overview pusher:subscription_error: " + error);
            });
        
        if( countryID >= 0 )
        {
            console.log("websockets creating press socket");
            
        
            channel.bind("message", (message) => {
                console.log("websockets press: " + message);
                var messageSentArea = document.getElementById('websocketsMessageSent');
                if( messageSentArea )
                {
                    messageSentArea.innerHTML = "New message received: <a href='board.php?gameID="+gameID+"&monitorUpdated="+Math.round(10000.0*Math.random())+"#monitorUpdated'>Click here</a> to refresh the board.";
                }
            });
            channel.bind("pusher:pong", () => {
                console.log("websockets press: pong");
            });
            channel.bind("pusher:subscription_succeeded", () => {
                console.log("websockets press: pusher:subscription_succeeded");
            });
        
            channel.bind("pusher:subscription_error", (error) => {
                console.log("websockets press: pusher:subscription_error: " + error);
            });
        }
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