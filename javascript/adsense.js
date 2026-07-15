// Google AdSense loader and ad-gating rules.
//
// This file is plain JavaScript, deliberately kept out of the compiled
// React bundle so the ad rules can be changed without a rebuild.
//
// When editing this file bump the ?ver= query on the script tags that
// include it, so clients with a cached copy pick up the new one:
//  - beta-src/public/index.html (then rebuild the beta UI)
//  - contrib/phpBB3-files/.../template/overall_header.html (the forum)
(function() {
  // Configuration - edit these values
  var AD_CLIENT = 'ca-pub-7157909811195408';  // Your AdSense publisher ID

  // Ad slot IDs for the beta React UI's dedicated ad areas
  // (see beta-src/src/components/ui/WDAds.tsx). The top slot is a
  // horizontal banner shown above the UI on mobile/tablet; the left/right
  // slots are vertical rails shown beside the UI on wide desktop screens.
  // These can share one responsive slot, but separate slots give separate
  // reporting in AdSense.
  var AD_SLOT_TOP = '9130267947';
  var AD_SLOT_LEFT = '9130267947';
  var AD_SLOT_RIGHT = '9130267947';

  // Decide whether ads should be shown to this user. EDIT THIS FUNCTION
  // to change who sees ads.
  //
  // `user` is whatever user info is available on the current page:
  //  - On the beta React UI it is the `user` object from the game overview
  //    API, i.e. { member: { userID, username, bet, timeLoggedIn, ... } }.
  //  - On plain pages (e.g. the forum), or on the beta UI before/without
  //    game data, it is { userID } parsed from the wD-Key cookie.
  //  - null when not logged in / unknown, which should return false.
  function shouldShowAds(user) {
    var userID = getUserID(user);
    if (!userID) return false;

    // For this test check whether it's kestasjk or a new user:
    return userID === 10 || userID >= 326914;
  }

  // Pull a numeric user ID out of whichever shape of user info we got
  function getUserID(user) {
    if (!user) return null;
    var raw = user.userID;
    if (!raw && user.member) raw = user.member.userID;
    var userID = parseInt(raw, 10);
    return userID > 0 ? userID : null;
  }

  // Get wD-Key cookie value
  function getCookie(name) {
    var cookies = document.cookie.split(';');
    for (var i = 0; i < cookies.length; i++) {
      var cookie = cookies[i].trim();
      if (cookie.indexOf(name + '=') === 0) {
        return cookie.substring(name.length + 1);
      }
    }
    return null;
  }

  function getUserFromCookie() {
    var wdKey = getCookie('wD-Key');
    if (!wdKey) return null;
    return { userID: parseInt(wdKey.split('_')[0], 10) };
  }

  var isAdsenseScriptLoaded = false;
  function loadAdsenseScript() {
    if(isAdsenseScriptLoaded) return;
    isAdsenseScriptLoaded = true;
    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + AD_CLIENT;
    script.crossOrigin = 'anonymous';
    document.head.appendChild(script);
  }

  // Expose the config and gating to the beta React UI, which calls
  // shouldShowAds with the game API's user object once it has loaded
  // (falling back to the cookie until/unless that's available).
  window.wDAds = {
    client: AD_CLIENT,
    slots: {
      top: AD_SLOT_TOP,
      left: AD_SLOT_LEFT,
      right: AD_SLOT_RIGHT
    },
    shouldShowAds: function(user) {
      return shouldShowAds(user || getUserFromCookie());
    },
    loadScript: loadAdsenseScript
  };

  // Pages without the React UI (e.g. the forum) only have the cookie to
  // go on; load the AdSense script for qualifying users straight away.
  if (shouldShowAds(getUserFromCookie())) {
    loadAdsenseScript();
  }
})();
