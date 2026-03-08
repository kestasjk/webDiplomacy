// Google AdSense loader - conditionally loads ads based on wD-Key cookie
// Modify this file to change ad settings without recompiling the React app
(function() {
  // Configuration - edit these values
  var AD_CLIENT = 'ca-pub-XXXXXXXXXXXXXXXX';  // Your AdSense publisher ID
  var AD_SLOT = 'XXXXXXXXXX';                  // Your ad slot ID
  var AD_WIDTH = '728px';
  var AD_HEIGHT = '90px';

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

  // Check if user qualifies for ads
  function shouldShowAds() {
    var wdKey = getCookie('wD-Key');
    if (!wdKey) return false;

    var userID = parseInt(wdKey.split('_')[0], 10);
    return userID === 10 || userID >= 326914;
  }

  // Load AdSense and display ad
  function loadAds() {
    // Load AdSense script
    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + AD_CLIENT;
    script.crossOrigin = 'anonymous';
    document.head.appendChild(script);

    // Insert ad when DOM is ready
    function insertAd() {
      var container = document.getElementById('ad-container');
      if (!container) return;

      container.style.textAlign = 'center';
      container.style.background = '#f0f0f0';
      container.style.padding = '10px 0';
      container.innerHTML = '<ins class="adsbygoogle" ' +
        'style="display:inline-block;width:' + AD_WIDTH + ';height:' + AD_HEIGHT + '" ' +
        'data-ad-client="' + AD_CLIENT + '" ' +
        'data-ad-slot="' + AD_SLOT + '"></ins>';

      (window.adsbygoogle = window.adsbygoogle || []).push({});
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', insertAd);
    } else {
      insertAd();
    }
  }

  // Main
  if (shouldShowAds()) {
    loadAds();
  }
})();
