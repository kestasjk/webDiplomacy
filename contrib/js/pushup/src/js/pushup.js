/* Pushup
 * Copyright (c) 2009 Nick Stakenburg (www.nickstakenburg.com)
 *
 * License: MIT-style license.
 * Website: http://www.pushuptheweb.com
 *
 */

var Pushup = {
  Version: '1.0.3',
  options: {
    appearDelay: .5,
    fadeDelay: 6,
    images: '../images/pushup/',
    message: 'Your old browser version may not work well with this website, please update.',
    reminder: {
      hours: 6,
      message: 'Remind me again in #{hours}'
    },
    skip: true
  },
  updateLinks: {
    IE: 'http://www.microsoft.com/windows/downloads/ie/',
    Firefox: 'http://www.getfirefox.com',
    Safari: 'http://www.apple.com/safari/download/',
    Opera: 'http://www.opera.com/download/'
  },
  Browser: {
    IE: !!(window.attachEvent &&
      navigator.userAgent.indexOf('Opera') === -1),
    Firefox: navigator.userAgent.indexOf('Firefox') > -1,
    Safari: navigator.userAgent.indexOf('AppleWebKit/') > -1 &&
      /Apple/.test(navigator.vendor),
    Opera: navigator.userAgent.indexOf('Opera') > -1
  }
};

Pushup.conditions = {
  IE: (function(agent) {
    var version = /MSIE ([\d.]+)/.exec(agent);
    return version && parseFloat(version[1]) < 7;
  })(navigator.userAgent),
  Firefox: Pushup.Browser.Firefox &&
    parseFloat(navigator.userAgent.match(/Firefox[\/\s](\d+)/)[1]) < 3,
  Safari: Pushup.Browser.Safari &&
    parseFloat(navigator.userAgent.match(/AppleWebKit\/(\d+)/)[1]) < 500,
  Opera: Pushup.Browser.Opera && (!window.opera.version ||
    parseFloat(window.opera.version()) < 9.5)
};

(function() {
// find current browser and check if it needs an update
for (var browser in Pushup.Browser)
  if (Pushup.Browser[browser]) Pushup._browserUsed = browser;
Pushup._updateBrowser = Pushup.conditions[Pushup._browserUsed] &&
  Pushup._browserUsed;

// stop if no update is required and we want to skip build
if (!Pushup._updateBrowser && Pushup.options.skip) return;

function Extend(destination, source) {
  for (var property in source)
    destination[property] = source[property];
  return destination;
}

Extend(Pushup, {
  start: function() {
    // get the image directory
    if (/^(https?:\/\/|\/)/.test(this.options.images))
      this.images = this.options.images;
    else {
      var srcMatch = /pushup(?:-[\w\d.]+)?\.js(.*)/,
       scripts = document.getElementsByTagName('script');
      for (var i = 0, l = scripts.length; i < l; i++) {
        var s = scripts[i];
        if (s.src && s.src.match(srcMatch))
          this.images = s.src.replace(srcMatch, '') + this.options.images;
      }
    }
    if (Pushup._updateBrowser) this.show();
  },

  build: function() {
    this.pushup = document.createElement('div');
    Opacity.set(this.pushup, 0);
    this.pushup.id = 'pushup';

    this.messageLink = this.pushup.appendChild(document.createElement('a'));
    this.messageLink.className = 'pushup_messageLink';
    this.messageLink.target = '_blank';

    this.messageLink.appendChild(this.icon = document.createElement('div'));
    this.icon.className = 'pushup_icon';

    this.messageLink.appendChild(this.message = document.createElement('span'));
    this.message.className = 'pushup_message';
    this.message.innerHTML = this.options.message;

    // reminder message if cookies are enabled
    var hours = this.options.reminder.hours;
    if (hours && Pushup.cookiesEnabled) {
      this.pushup.appendChild(this.reminder = document.createElement('a'));
      this.reminder.href = '#';
      this.reminder.className = 'pushup_reminder';
      this.pushup.className = 'withReminder';
      var H = hours + ' hour' + (hours > 1 ? 's' : ''),
       message = this.options.reminder.message.replace('#{hours}', H);
      this.reminder.innerHTML = message;
    }

    // Older Opera doesn't handle float correctly
    if (Pushup.Browser.Opera &&
       (!window.opera.version || parseFloat(window.opera.version()) < 9.25)) {
      this.messageLink.style.cssFloat = 'none';
      this.reminder.style.cssFloat = 'none';
    }

    Pushup.setBrowser(Pushup._updateBrowser);
    document.body.appendChild(this.pushup);
    Pushup.addEvents();
  },

  addEvents: function() {
    if (this.reminder) {
      Event.add(this.reminder, 'click', function(event) {
        Event.stop(event);
        Pushup.setReminder(Pushup.options.reminder.hours);
        Pushup.fade();
      });
    }
    Event.add(this.pushup, 'mouseover', Pushup.clearFade);
    Event.add(this.pushup, 'mouseout', function() {
      Pushup.fade({ delay: Pushup.options.fadeDelay })
    });
  },

  setBrowser: function(browser) {
    browser = browser || 'IE';
    setPngBackground(this.icon, this.images + browser.toLowerCase() + '.png');
    this.messageLink.href = this.updateLinks[browser];
  },

  show: function() {
    // default to IE if no browser was detected
    var browser = typeof arguments[0] == 'string' ?
      arguments[0] : Pushup._browserUsed || 'IE',
     options = arguments[browser ? 1 : 0] || {};

    if (options.resetReminder) Pushup.resetReminder();

    // show if not blocked by cookie
    if (!options.ignoreReminder && Pushup.cookiesEnabled &&
      Cookie.get('_pushupBlocked')) return;

    if (!Pushup.pushup) Pushup.build();
    Opacity.set(Pushup.pushup, 0);
    Pushup.pushup.style.display = 'block';
    if (browser) Pushup.setBrowser(browser);
    this.appear({ fadeAfter: true, delay: Pushup.options.appearDelay });
  },

  appear: function(delay) {
    Pushup.clearFade();
    var options = arguments[0] || {};
    return window.setTimeout(function() {
      Appear(Pushup.pushup, { afterFinish: function() {
        if (options.fadeAfter)
          Pushup.fade({ delay: Pushup.options.fadeDelay });
      }});
    }, (options.delay || 0.01) * 1000);
  },

  clearFade: function() {
    if (Pushup._fadeTimer) {
      window.clearTimeout(Pushup._fadeTimer);
      Pushup._fadeTimer = null;
    }
  },

  fade: function() {
    var options = arguments[0] || {};
    Pushup._fadeTimer = window.setTimeout(function() {
      Fade(Pushup.pushup);
    }, (options.delay || 0.01) * 1000);
  },

  setReminder: function(hours) {
    Cookie.set('_pushupBlocked', 'blocked', { duration: 1 / 24 * hours })
  },

  resetReminder: function() { Cookie.remove('_pushupBlocked') }
});

// Opacity adapted from the Prototype JavaScript framework
// http://www.prototypejs.org
var Opacity = {
  set: function(element, value) {
    element.style.opacity = (value == 1 || value === '') ? '' :
      (value < 0.00001) ? 0 : value;
  },

  get:  function(element) {
    var opacity = element.style.opacity;
    return opacity ? parseFloat(opacity) : 1.0;
  }
};

if (Pushup.Browser.IE) {
  Opacity.get = function(element) {
    var opacity = element.style.opacity;
    if (!opacity && element.currentStyle) opacity = element.currentStyle[opacity];

    if (opacity = (element.style.filter || '').match(/alpha\(opacity=(.*)\)/))
      if (opacity[1]) return parseFloat(opacity[1]) / 100;
    return 1.0;
  };

  Opacity.set = function(element, value) {
    function stripAlpha(filter) {
      return filter.replace(/alpha\([^\)]*\)/gi,'')
    }
    var currentStyle = element.currentStyle;
    if ((currentStyle && !currentStyle.hasLayout) ||
      (!currentStyle && element.style.zoom == 'normal'))
        element.style.zoom = 1;
    var filter = element.style.filter,
     style = element.style;
    if (value == 1 || value === '') (filter = stripAlpha(filter)) ?
      style.filter = filter : style.filter = '';
    else style.filter = stripAlpha(filter) +
      'alpha(opacity=' + (value * 100) + ')';
  };
}

function Appear(element) {
  var current = Opacity.get(element),
   options = arguments[1] || {};
  if (element.style.display != 'block')
    element.style.display = 'block';
  if (current < 1) {
    setTimeout(function() {
      Opacity.set(element, current += 0.05);
      Appear(element, options);
    }, 0.01);
  }
  else {
    if (Pushup.Browser.IE && element.style.filter)
      element.style.removeAttribute('filter');
    if (options.afterFinish) options.afterFinish.call();
  }
}

function Fade(element) {
  var current = Opacity.get(element),
   options = arguments[1] || {};
  if (current > 0) {
    setTimeout(function() {
      Opacity.set(element, current -= 0.05);
      Fade(element, options);
    }, 0.01);
  }
  else {
    element.style.display = 'none';
    if (options.afterFinish) options.afterFinish.call();
  }
}

function setPngBackground(element, url) {
  var options = Extend({
    align: 'top left',
    repeat: 'no-repeat',
    sizingMethod: 'crop',
    backgroundColor: ''
  }, arguments[2] || {});

  Extend(element.style, arguments.callee.IEBelow7 ? {
    filter: 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' +
      url + '\'\', sizingMethod=\'' + options.sizingMethod + '\')'
  } : {
    background: options.backgroundColor + ' url(' + url + ') ' +
      options.align + ' ' + options.repeat
  });
}
setPngBackground.IEBelow7 = Pushup.Browser.IE &&
  parseFloat(/MSIE ([\d.]+)/.exec(navigator.userAgent)[1]) < 7;

// Based on the work of Peter-Paul Koch - http://www.quirksmode.org
var Cookie = {
  set: function(name, value) {
    var expires = '', options = arguments[2] || {};
    if (options.duration) {
      var date = new Date();
      date.setTime(date.getTime() + options.duration * 1000 * 60 * 60 * 24);
      value += '; expires=' + date.toGMTString();
    }
    document.cookie = name + "=" + value + expires + "; path=/";
  },

  remove: function(name) { this.set(name, '', -1) },

  get: function(name) {
    var cookies = document.cookie.split(';'), nameEQ = name + "=";
    for (var i = 0, l = cookies.length; i < l; i++) {
      var c = cookies[i];
      while (c.charAt(0) == ' ')
        c = c.substring(1,c.length);
      if (c.indexOf(nameEQ) == 0)
        return c.substring(nameEQ.length, c.length);
    }
    return null;
  }
};

// check if cookies are enabled
Pushup.cookiesEnabled = (function(test) {
  if (Cookie.get(test)) return true;
  Cookie.set(test, 'test', { duration: 15 });
  return Cookie.get(test);
})('_pushupCookiesEnabled');

var Event = {
  add: function(obj, type, fn) {
    if (obj.attachEvent) {
      obj['e' + type + fn] = fn;
      obj[type + fn] = function(){ obj['e' + type +fn](window.event) };
      obj.attachEvent('on' + type, obj[type + fn]);
    }
    else obj.addEventListener(type, fn, false);
  },

  stop: function(event) {
    if (Pushup.Browser.IE) {
      event.cancelBubble = true;
      event.returnValue = false;
    }
    else {
      event.preventDefault();
      event.stopPropagation();
    }
  }
};

Event.add(window, 'load', function() { Pushup.start() });
})();