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
 * Original timer.js written by jayp
 */

// Update timestamps to be in the local time, taking the unixtime attribute and converting it via dateToText()
function updateTimestamps() {
	$$(".timestamp").map(function(c) {
		var cDate = new Date( parseInt(c.getAttribute("unixtime"))*1000 );
		c.update( dateToText(cDate) );
	},this);
}

var dayNames=["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun"];
var monthNames=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];

// Convert a JavaScript (new Date()) into text
function dateToText(date) {
	var secondDifference = ((date - (new Date()))/1000);
	if( secondDifference < 0 ) secondDifference *= -1;

	var a = dayNames[date.getDay()];
	
	if ( secondDifference < 4*24*60*60 )
	{
		var I = date.getHours();
		var M = date.getMinutes();
		if( M<10 ) M="0"+M;
	
		var p = "AM";
		if( I >= 12 ) {
			I -= 12;
			p = "PM";
			if( I==0 ) I="12";
		}
		
		// apply leading zero to single digit hour
		if( I<10 ) I="0"+I.toString();

		if ( secondDifference < 22*60*60 ) // YW: should 22 be replaced with 24?
			return I+":"+M+" "+p; // HH:MM AM/PM
		else
			return a+" "+I+" "+p; // Day HH AM/PM
	}
	else
	{
		var d = date.getDate();
		var b = monthNames[date.getMonth()];
		var y = date.getYear();
		if ( y < 1900 ) y+=1900;
		
		if ( secondDifference < 3*7*22*60*60 ) // YW: should 22 be replaced with 24?
			return a+" "+d+" "+b; // Day Day# Month
		else
			return d+" "+b+" "+y; // Day# Month Year
	} 
}

// Update the timezone offset info in the footer, which tells the user which timezone the page's times are in
function updateUTCOffset() {
	// Time needed to add to UTC times to get our time
	var UTCHoursOffset = -1*((new Date).getTimezoneOffset()/60);
	var sign='+';
	if( UTCHoursOffset < 0 )
	{
		UTCHoursOffset *= -1;
		sign='-';
	}
	
	var hours = Math.floor(UTCHoursOffset);
	var minutes = (UTCHoursOffset*60 - hours*60);
	
	if( hours < 10 ) hours = '0'+hours.toString();
	if( minutes < 10 ) minutes = '0'+minutes.toString();
	
	var utcE = $("UTCOffset");
	if( !Object.isUndefined(utcE) && utcE != null )
		utcE.update('UTC'+sign.toString()+hours.toString()+':'+minutes.toString());
}


// Above are timestamp functions, below are countdown functions:

var timerCheck=false; // Stores the PeriodicalExecutor which updates countdowns
var timerCheckMinTime=7*24*60*60; // The refresh time for the PeriodicalExecutor
var newTimerCheckMinTime=7*24*60*60; // The new refresh time, to detect when it has to be restarted at a new rate

// Update countdown timers, needs to be run repeatedly. The first time it is run it will set up future runs
function updateTimers() {
	
	var timeFrom = Math.floor((new Date).getTime() / 1000);
	
	$$(".gameTimeRemaining .timeremaining").map(function(c) {
		
		var givenTime = parseInt(c.getAttribute("unixtime"));
		var secondsRemaining = givenTime - timeFrom;

		if( secondsRemaining < 300 )
			c.setStyle({'color': '#a00'});
		
		c.update(remainingText(secondsRemaining));
		
	},this);
	
	// If the timer interval has changed update it
	if( newTimerCheckMinTime != timerCheckMinTime )
	{
		timerCheckMinTime = newTimerCheckMinTime;
		
		if( typeof timerCheck == "object" )
			timerCheck.stop();
		
		timerCheck = new PeriodicalExecuter(updateTimers, timerCheckMinTime);
	}
}

// Update the timer update period, if 1 the countdowns are updated every second. The smallest update period has to be used
function setMinimumTimerInterval(newInterval) {
	if( newInterval<1.0 ) newInterval=1;
	
	if( newTimerCheckMinTime >= newInterval )
		newTimerCheckMinTime = newInterval;
}

// Textual time remaining for a given number of seconds to pass. Also sets the minimum timer interval
function remainingText(secondsRemaining)
{
	if ( secondsRemaining <= 0 ) return 'Now';
		
	var seconds = Math.floor( secondsRemaining % 60); 
	var minutes = Math.floor(( secondsRemaining % (60*60) )/60);
	var hours = Math.floor( secondsRemaining % (24*60*60)/(60*60) );
	var days = Math.floor( secondsRemaining /(24*60*60) );
		
	if ( days > 0 ) // D, H
	{
		minutes += Math.round(seconds/60); // Add a minute if the seconds almost give a minute
		seconds = 0;
			
		hours += Math.round(minutes/60); // Add an hour if the minutes almost gives an hour
		minutes = 0;
		
		if ( days < 2 )
		{
			setMinimumTimerInterval(60*minutes);
			return days+' day, '+hours+' hours';
		}
		else
		{
			setMinimumTimerInterval(60*60*hours);
			return days+' days';
		}
	}
	else if ( hours > 0 ) // H, M
	{
		minutes += Math.round(seconds/60); // Add a minute if the seconds almost give a minute)
		
		if ( hours < 4 )
		{
			setMinimumTimerInterval(seconds);
			return hours+' hours, '+minutes+' mins';
		}
		else
		{
			setMinimumTimerInterval(minutes*60);
			
			hours += Math.round(minutes/60); // Add an hour if the minutes almost gives an hour
			
			return hours+' hours';
		}
	}
	else // M, S
	{
		if( minutes >= 5 )
		{
			setMinimumTimerInterval(seconds);
			return minutes+' mins';
		}
		else
		{
			setMinimumTimerInterval(1);
			
			if( minutes < 0 )
				return seconds+' secs';
			else if ( minutes < 5 )
				return minutes+' mins, '+seconds+' secs';
		}
	}
}