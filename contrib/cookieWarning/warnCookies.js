///////////////////////////////////////
// Author: Donatas Stonys            //
// WWW: http://www.BlueWhaleSEO.com  //
// Date: 26 July 2012                 //
// Version: 0.7                      //
///////////////////////////////////////

// Asign current date to variable //
var currentDate = new Date();

// Create some DOM elements
var newCookiesWarningDiv = document.createElement("div");

// Retrieving cookie's information
function checkCookie(cookieName) {
	"use strict";
	var cookieValue, cookieStartsAt, cookieEndsAt;

	// Get all coookies in one string
	cookieValue = document.cookie;
	// Check if cookie's name is within that string
	cookieStartsAt = cookieValue.indexOf(" " + cookieName + "=");
	if (cookieStartsAt === -1) {
		cookieStartsAt = cookieValue.indexOf(cookieName + "=");
	}
	if (cookieStartsAt === -1) {
		cookieValue = null;
	} else {
		cookieStartsAt = cookieValue.indexOf("=", cookieStartsAt) + 1;
		cookieEndsAt = cookieValue.indexOf(";", cookieStartsAt);

		if (cookieEndsAt === -1) {
			cookieEndsAt = cookieValue.length;
		}

		// Get and return cookie's value
		cookieValue = unescape(cookieValue.substring(cookieStartsAt, cookieEndsAt));
		return cookieValue;
	}
}

// Cookie setup function
function setCookie(cookieName, cookieValue, cookiePath, cookieExpires) {
	"use strict";

	// Convert characters that are not text or numbers into hexadecimal equivalent of their value in the Latin-1 character set
	cookieValue = escape(cookieValue);

	// If cookie's expire date is not set
	if (cookieExpires === "") {
		// Default expire date is set to 6 after the current date
		currentDate.setMonth(currentDate.getMonth() + 6);
		// Convert a date to a string, according to universal time (same as GMT)
		cookieExpires = currentDate.toUTCString();
	}

	// If cookie's path value has been passed
	if (cookiePath !== "") {
		cookiePath = ";path = " + cookiePath;
	}

	// Add cookie to visitors computer
	document.cookie = cookieName + "=" + cookieValue + ";expires = " + cookieExpires + cookiePath;

	// Call function to get cookie's information
	checkCookie(cookieName);
}

// Check if cookies are allowed by browser //
function checkCookiesEnabled() {
	"use strict";
	// Try to set temporary cookie
	setCookie("AllowCookiesExist", "Exist", "", "");
	// If temporary cookie has been set, delete it and return true
	if (checkCookie("AllowCookiesExist") === "Exist") {
		setCookie("AllowCookiesExist", "Exist", "", "1 Jan 2000 00:00:00");
		return true;
	// If temporary cookie hasn't been set, return false	
	}
	if (checkCookie("AllowCookiesExist") !== "Exist") {
		return false;
	}
}

// Add HTML form to the website		
function acceptCookies() {
	"use strict";

	document.getElementById("cookiesWarning").appendChild(newCookiesWarningDiv).setAttribute("id", "cookiesWarningActive");
	document.getElementById("cookiesWarningActive").innerHTML = "<strong id='text'>In order for this site to work properly we need to store small files (called cookies) on your computer </strong>.<br/> Over 90% of all websites do this, however, since the 25th of May 2011 we are required by EU regulations<br>to obtain your consent first (<span id='readMoreURL'></span>). What do you say?<form name='cookieAgreement'><input type='checkbox' name='agreed' value='Agreed' class='checkbox'>I accept cookies from this site.<input type='submit' value='Continue' onclick='getAgreementValue(); return false;' class='button'></form>";
	// Change the URL of "Read more..." here
	document.getElementById("readMoreURL").innerHTML = "<a href='http://www.ico.gov.uk/for_organisations/privacy_and_electronic_communications/the_guide/cookies.aspx' title='ICO - New EU cookie law (e-Privacy Directive)' target='_blank' rel='nofollow'>Read more...</a>";
}

function acceptCookiesTickBoxWarning() {
	"use strict";

	setCookie("AllowCookies", "Yes", "", "1 Jan 2000 00:00:00");
	document.getElementById("cookiesWarning").appendChild(newCookiesWarningDiv).setAttribute("id", "cookiesWarningActive");
	document.getElementById("cookiesWarningActive").innerHTML = "<strong id='text'>In order for this site to work properly we need to store small files (called cookies) on your computer </strong>.<br/> Over 90% of all websites do this, however, since the 25th of May 2011 we are required by EU regulations<br>to obtain your consent first (<span id='readMoreURL'></span>). What do you say?<form name='cookieAgreement'><p id='warning'><small>You must tick the 'I accept cookies from this site' box to accept. This site does not work without them.<br>If you do not want this site to make use of cookies please search on <a href='http://google.com'>Google</a> for alternatives.</small></p><input type='checkbox' name='agreed' value='Agreed' class='checkbox'>I accept cookies from this site.<input type='submit' value='Continue' onclick='getAgreementValue()' class='button'></form>";
	// Change the URL of "Read more..." here
	document.getElementById("readMoreURL").innerHTML = "<a href='http://www.ico.gov.uk/for_organisations/privacy_and_electronic_communications/the_guide/cookies.aspx' title='ICO - New EU cookie law (e-Privacy Directive)' target='_blank' rel='nofollow'>Read more...</a>";
}

// Check if cookie has been set before //
function checkCookieExist() {
	"use strict";
	// Call function to check if cookies are enabled in browser
	if (checkCookiesEnabled()) {
		// If cookies enabled, check if our cookie has been set before and if yes, leave HTML block empty
		if (checkCookie("AllowCookies") === "Yes") {
			document.getElementById("cookiesWarning").innerHTML = "";
		// If our cookie hasn't been set before, call cookies' agreement form to HTML block	
		} else {
			acceptCookies();
		}
	} else {
		// Display warning if cookies are disabled on browser
		document.getElementById("cookiesWarning").appendChild(newCookiesWarningDiv).setAttribute("id", "cookiesWarningActive");
		document.getElementById("cookiesWarningActive").innerHTML = "<span id='cookiesDisabled'><strong>Cookies are disabled. We use cookies to give you the best online experience.</strong><br /> Your browser currently not accepting cookies.</span>";
	}
}

// Get agreement results
function getAgreementValue() {
	"use strict";

	// If agreement box has been checked, set permanent cookie on visitors computer
	if (document.cookieAgreement.agreed.checked) {
		// Hide agreement form
		document.getElementById("cookiesWarning").innerHTML = "";
		setCookie("AllowCookies", "Yes", "", "");
	} else {
		// If agreement box hasn't been checked, delete cookie (if exist) and add extra warning to HTML form
		acceptCookiesTickBoxWarning();
	}
}