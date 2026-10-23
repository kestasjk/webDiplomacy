/*
    Copyright (C) 2026 Kestas J. Kuliukas

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

/**
 * wD.Chat: the board's press panel, drawn by the browser from the same sources the /game/ board uses: everyone's
 * messages from messages.json, the player's own from game/playercontext (kept in localStorage, so only new ones
 * are asked for), sent with game/sendmessage and marked read with game/messagesseen. It replaces the
 * server-rendered Chatbox on board.php.
 *
 *   var chat = new wD.Chat(document.getElementById('wdChat'), { source: source, live: live, config: {...} });
 *   chat.prepare();   // before anything asks the source for a context
 *   chat.start();
 *
 * config, from board.php: { countryID, isMember, canPostGlobal, pressType, phase, countries: [{countryID, name}],
 *   members: [{countryID, name: HTML of the player's name or the country}], colourblind, variantName,
 *   turnText: function(turn) -> "Spring, 1901", markUnreadForm: id of a form posting MarkAsUnread }
 */
var wD = window.wD || {};
window.wD = wD;

wD.Chat = (function () {
	'use strict';

	var TURN_FILTERS = [
		{ value: '0', label: 'This turn' },
		{ value: '3', label: 'Last 3 turns' },
		{ value: '10', label: 'Last 10 turns' },
		{ value: 'all', label: 'All turns' }
	];
	var DEFAULT_FILTER = '10';
	var FILTER_STORAGE_KEY = 'wD.chat.turns';
	var ALLOWED_TAGS = { BR: true, STRONG: true, B: true, EM: true, I: true, U: true, SPAN: true, A: true };

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) node.className = className;
		if (text !== undefined && text !== null) node.textContent = text;
		return node;
	}

	function storage(key, value) {
		try {
			if (value === undefined) return window.localStorage.getItem(key);
			window.localStorage.setItem(key, value);
		} catch (e) { /* unavailable */ }
		return null;
	}

	function session(key, value) {
		try {
			if (value === undefined) return window.sessionStorage.getItem(key);
			window.sessionStorage.setItem(key, value);
		} catch (e) { /* unavailable */ }
		return null;
	}

	/**
	 * Messages are stored HTML-escaped with line breaks as <br />, but they have been written by other players, so
	 * only a few harmless tags are let through, as the /game/ board does with DOMPurify.
	 */
	function sanitize(html) {
		var template = document.createElement('template');
		template.innerHTML = html;
		var walk = function (node) {
			var children = Array.prototype.slice.call(node.childNodes);
			children.forEach(function (child) {
				if (child.nodeType === 1) {
					if (!ALLOWED_TAGS[child.tagName]) {
						while (child.firstChild) node.insertBefore(child.firstChild, child);
						node.removeChild(child);
						return;
					}
					Array.prototype.slice.call(child.attributes).forEach(function (attr) {
						var keep = false;
						if (child.tagName === 'A' && attr.name === 'href' && /^(https?:\/\/|\/|[a-z0-9_\-]+\.php)/i.test(attr.value)) keep = true;
						if (child.tagName === 'SPAN' && attr.name === 'class' && /^[a-z0-9 _\-]*$/i.test(attr.value)) keep = true;
						if (!keep) child.removeAttribute(attr.name);
					});
					if (child.tagName === 'A') { child.setAttribute('rel', 'noopener'); child.setAttribute('target', '_blank'); }
					walk(child);
				} else if (child.nodeType !== 3) {
					node.removeChild(child);
				}
			});
		};
		walk(template.content);
		return template.content;
	}

	function formatTime(unixTime) {
		var date = new Date(unixTime * 1000);
		var now = new Date();
		var sameDay = date.toDateString() === now.toDateString();
		var time = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
		if (sameDay) return time;
		return date.toLocaleDateString([], { day: 'numeric', month: 'short' }) + ' ' + time;
	}

	function Chat(container, options) {
		this.container = container;
		this.source = options.source;
		this.live = options.live || null;
		this.toast = options.toast || null;
		this.config = options.config || {};

		this.countryID = parseInt(this.config.countryID, 10) || 0;
		this.isMember = !!this.config.isMember && this.countryID > 0;
		this.currentTurn = parseInt(this.config.turn, 10) || 0;

		this.messages = [];
		this.newMessagesFrom = [];
		this.mutedCountryIDs = [];
		this.loading = null;
		this.refreshQueued = false;

		this.filter = storage(FILTER_STORAGE_KEY) || DEFAULT_FILTER;
		if (!TURN_FILTERS.some(function (f) { return f.value === this.filter; }, this)) this.filter = DEFAULT_FILTER;

		this.countries = {};
		(this.config.countries || []).forEach(function (country) { this.countries[country.countryID] = country.name; }, this);
		this.memberNames = {};
		(this.config.members || []).forEach(function (member) { this.memberNames[member.countryID] = member.name; }, this);

		// The tab last open in this browser tab, as the old chatbox remembered it in the session
		var savedTab = session('wD.chat.tab.' + this.source.gameID);
		this.tab = this.allowedTab(savedTab === null ? 0 : parseInt(savedTab, 10));
	}

	Chat.prototype.filterTurns = function () {
		return this.filter === 'all' ? null : parseInt(this.filter, 10);
	};

	/** Decide what the first context request should ask for; call before anything loads the context */
	Chat.prototype.prepare = function () {
		this.source.initialMessageParams(this.isMember ? this.countryID : 0, this.filterTurns(), this.currentTurn);
	};

	/* Who may talk to whom */

	Chat.prototype.tabsAllowed = function () {
		var tabs = [0];
		var pressType = this.config.pressType;
		var allCountries = (pressType === 'Regular' || pressType === 'RulebookPress');
		if (this.isMember) {
			Object.keys(this.countries).map(Number).sort(function (a, b) { return a - b; }).forEach(function (countryID) {
				if (countryID === this.countryID || allCountries) tabs.push(countryID);
			}, this);
		}
		return tabs;
	};

	Chat.prototype.allowedTab = function (countryID) {
		return this.tabsAllowed().indexOf(countryID) !== -1 ? countryID : 0;
	};

	/** Whether the viewer may send to this tab now; the same rule board.php's Chatbox applied */
	Chat.prototype.canPostTo = function (countryID) {
		var pressType = this.config.pressType;
		var phase = this.config.phase;
		if (!this.isMember) return countryID === 0 && !!this.config.canPostGlobal;
		if (pressType === 'Regular') return true;
		if (countryID === this.countryID) return true; // Notes
		if (pressType === 'RulebookPress') return phase === 'Diplomacy' || phase === 'Finished';
		if (countryID === 0) return pressType === 'PublicPressOnly' || (pressType === 'NoPress' && phase === 'Finished');
		return false;
	};

	Chat.prototype.messagesForTab = function (countryID) {
		var me = this.countryID;
		var muted = this.mutedCountryIDs;
		var fromTurn = this.filterTurns() === null ? 0 : Math.max(0, this.currentTurn - this.filterTurns());
		return this.messages.filter(function (m) {
			if (m.turn < fromTurn) return false;
			if (muted.indexOf(m.fromCountryID) !== -1 && m.fromCountryID !== me) return false;
			if (countryID === 0) return m.toCountryID === 0;
			if (countryID === me) return (m.fromCountryID === me && m.toCountryID === me) || (m.fromCountryID === 0 && m.toCountryID === me);
			return (m.toCountryID === me && m.fromCountryID === countryID) || (m.fromCountryID === me && m.toCountryID === countryID);
		});
	};

	/* Loading */

	Chat.prototype.start = function () {
		var self = this;
		this.render();
		this.refresh().then(function () { self.scrollToEnd(); });

		if (this.live) {
			this.live.on('message', function () { self.refresh(); });
			this.live.on('files', function (changed) {
				if (changed.indexOf('messages') !== -1 || changed.indexOf('game') !== -1) self.refresh();
			});
			this.live.on('gap', function () { self.refresh(); });
			this.live.on('processed', function () { self.refresh(); });
		}
	};

	/** Load whatever is new and redraw; one load at a time, with a load asked for meanwhile run afterwards */
	Chat.prototype.refresh = function () {
		var self = this;
		if (this.loading) {
			this.refreshQueued = true;
			return this.loading;
		}
		var before = {};
		this.messages.forEach(function (m) { before[m.id] = true; });

		this.loading = this.source.getContext().then(function (context) {
			self.currentTurn = context.game.turn;
			if (context.member) self.mutedCountryIDs = context.member.mutedCountryIDs || [];
			return self.source.loadMessages({ countryID: self.isMember ? self.countryID : 0, turns: self.filterTurns() });
		}).then(function (result) {
			self.messages = result.messages;
			self.newMessagesFrom = result.newMessagesFrom || [];
			var arrived = result.messages.filter(function (m) { return !before[m.id]; });
			self.renderMessages();
			self.renderTabs();
			self.announce(arrived, Object.keys(before).length > 0);
		}).catch(function (error) {
			if (window.console) console.error('Chat: ' + (error && error.message ? error.message : error));
			self.showNotice('Could not load messages: ' + (error && error.message ? error.message : 'unknown error'));
		}).finally(function () {
			self.loading = null;
			if (self.refreshQueued) {
				self.refreshQueued = false;
				self.refresh();
			}
		});
		return this.loading;
	};

	/** Tell the viewer about messages which arrived for a tab they don't have open */
	Chat.prototype.announce = function (arrived, afterFirstLoad) {
		if (!afterFirstLoad || !this.toast) return;
		var me = this.countryID;
		var self = this;
		var byTab = {};
		arrived.forEach(function (m) {
			if (m.fromCountryID === me) return; // Sent from here, or from another of this player's tabs
			var tab = (m.toCountryID === 0) ? 0 : m.fromCountryID;
			if (tab === self.tab) return;
			byTab[tab] = (byTab[tab] || 0) + 1;
		});
		Object.keys(byTab).forEach(function (tab) {
			tab = parseInt(tab, 10);
			var from = tab === 0 ? 'everyone' : (self.countryName(tab) || 'the GameMaster');
			self.toast.show({
				text: 'New message' + (byTab[tab] > 1 ? 's' : '') + (tab === 0 ? ' to ' : ' from ') + from,
				key: 'chat-' + tab,
				timeout: 10000,
				actions: [{ label: 'Open', onClick: function () { self.openTab(tab); self.scrollIntoView(); } }]
			});
		});
	};

	Chat.prototype.countryName = function (countryID) {
		if (countryID === 0) return 'Global';
		return this.countries[countryID] || ('Country ' + countryID);
	};

	/* Rendering */

	Chat.prototype.render = function () {
		var self = this;
		var root = this.container;
		root.innerHTML = '';
		root.classList.add('wdchat');
		if (this.config.variantName) root.classList.add('variant' + this.config.variantName);

		this.tabsEl = el('div', 'wdchat-tabs gamelistings-tabs');
		root.appendChild(this.tabsEl);

		var panel = el('div', 'wdchat-panel chatWrapper');
		root.appendChild(panel);

		this.headerEl = el('div', 'wdchat-header barAlt2');
		panel.appendChild(this.headerEl);

		this.listEl = el('div', 'wdchat-messages');
		panel.appendChild(this.listEl);

		var footer = el('div', 'wdchat-footer barAlt2');
		panel.appendChild(footer);

		this.composeEl = el('div', 'wdchat-compose');
		this.textarea = el('textarea', 'wdchat-input');
		this.textarea.rows = 3;
		this.textarea.setAttribute('aria-label', 'Message');
		this.textarea.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && !e.shiftKey) {
				e.preventDefault();
				self.send();
			}
		});
		this.composeEl.appendChild(this.textarea);
		this.sendButton = el('button', 'form-submit wdchat-send', 'Send');
		this.sendButton.type = 'button';
		this.sendButton.addEventListener('click', function () { self.send(); });
		this.composeEl.appendChild(this.sendButton);
		footer.appendChild(this.composeEl);

		var tools = el('div', 'wdchat-tools');
		var filterLabel = el('label', 'wdchat-filter-label', 'Show: ');
		this.filterSelect = el('select', 'wdchat-filter');
		TURN_FILTERS.forEach(function (f) {
			var option = el('option', null, f.label);
			option.value = f.value;
			if (f.value === self.filter) option.selected = true;
			self.filterSelect.appendChild(option);
		});
		this.filterSelect.addEventListener('change', function () {
			self.filter = self.filterSelect.value;
			storage(FILTER_STORAGE_KEY, self.filter);
			self.refresh().then(function () { self.scrollToEnd(); });
		});
		filterLabel.appendChild(this.filterSelect);
		tools.appendChild(filterLabel);

		this.markUnreadLink = el('a', 'wdchat-markunread light', 'Mark unread');
		this.markUnreadLink.href = '#';
		this.markUnreadLink.addEventListener('click', function (e) {
			e.preventDefault();
			self.markUnread();
		});
		tools.appendChild(this.markUnreadLink);

		this.noticeEl = el('span', 'wdchat-notice');
		tools.appendChild(this.noticeEl);
		footer.appendChild(tools);

		this.renderTabs();
		this.renderHeader();
		this.renderCompose();
	};

	Chat.prototype.renderTabs = function () {
		var self = this;
		this.tabsEl.innerHTML = '';
		var tabs = this.tabsAllowed();
		if (tabs.length <= 1 && !this.isMember) {
			this.tabsEl.style.display = 'none';
			return;
		}
		tabs.forEach(function (countryID) {
			var link = el('a', 'country' + countryID + (countryID === self.tab ? ' current' : ''));
			link.href = '#chatbox';
			var label = countryID === self.countryID ? 'Notes' : self.countryName(countryID);
			link.appendChild(document.createTextNode(label));
			link.title = countryID === 0 ? 'Open the global chatbox tab' : 'Open ' + label + "'s chatbox tab";
			if (countryID !== self.tab && self.newMessagesFrom.indexOf(countryID) !== -1) {
				link.appendChild(document.createTextNode(' '));
				var icon = el('img', 'wdchat-unread');
				icon.src = 'images/icons/mail.png';
				icon.alt = 'Unread message';
				icon.title = 'Unread message';
				link.appendChild(icon);
			}
			link.addEventListener('click', function (e) {
				e.preventDefault();
				self.openTab(countryID);
			});
			self.tabsEl.appendChild(link);
		});
	};

	Chat.prototype.renderHeader = function () {
		this.headerEl.innerHTML = '';
		var self = this;
		if (this.tab === 0) {
			var names = Object.keys(this.countries).map(Number).sort(function (a, b) { return a - b; }).map(function (countryID) {
				return self.memberNames[countryID] || ('<span class="country' + countryID + '">' + self.countryName(countryID) + '</span>');
			});
			this.headerEl.innerHTML = '<div class="chatboxMembersList">' + names.join(', ') + '</div>';
		} else if (this.tab === this.countryID) {
			this.headerEl.innerHTML = '<div class="chatboxMembersList">Notes to yourself, and messages from the GameMaster</div>';
		} else {
			this.headerEl.innerHTML = '<div class="chatboxMembersList">' + (this.memberNames[this.tab] || this.countryName(this.tab)) + '</div>';
		}
	};

	Chat.prototype.renderCompose = function () {
		var canPost = this.canPostTo(this.tab);
		this.composeEl.style.display = canPost ? '' : 'none';
		this.markUnreadLink.style.display = (this.isMember && this.tab !== 0 && this.tab !== this.countryID && this.config.markUnreadForm) ? '' : 'none';
	};

	Chat.prototype.renderMessages = function () {
		var self = this;
		var list = this.listEl;
		var atEnd = (list.scrollHeight - list.scrollTop - list.clientHeight) < 40;
		list.innerHTML = '';
		var messages = this.messagesForTab(this.tab);

		if (!messages.length) {
			list.appendChild(el('div', 'wdchat-empty notice', 'No messages yet posted.'));
			return;
		}

		var alternate = false;
		messages.forEach(function (m) {
			alternate = !alternate;
			var row = el('div', 'wdchat-message replyalternate' + (alternate ? '1' : '2') + ' gameID' + self.source.gameID + 'countryID' + m.fromCountryID);
			if (m.fromCountryID === self.countryID && self.isMember) row.classList.add('wdchat-mine');

			var time = el('span', 'wdchat-time', formatTime(m.timeSent));
			time.title = new Date(m.timeSent * 1000).toLocaleString();
			row.appendChild(time);

			var body = el('span', 'wdchat-text' + (m.fromCountryID !== 0 ? ' country' + m.fromCountryID : ''));

			if (m.turn < self.currentTurn && self.config.turnText) {
				var turn = el('strong', 'wdchat-turn', self.config.turnText(m.turn) + ': ');
				body.appendChild(turn);
			}

			// The sender's name, for everyone in the global tab and for the colour-blind elsewhere
			var showName = self.tab === 0 || self.config.colourblind || m.fromCountryID === 0;
			if (showName) {
				var name = m.fromCountryID === 0 ? 'GameMaster' : (m.fromCountryID === self.countryID && self.isMember ? 'You' : self.countryName(m.fromCountryID));
				var fromEl = el('strong', 'wdchat-from', name + ': ');
				body.appendChild(fromEl);
			}

			var text = el('span', m.fromCountryID === self.countryID && self.isMember ? 'messageFromMe' : null);
			text.appendChild(sanitize(m.message));
			body.appendChild(text);
			row.appendChild(body);
			list.appendChild(row);
		});

		if (atEnd) this.scrollToEnd();
	};

	Chat.prototype.scrollToEnd = function () {
		this.listEl.scrollTop = this.listEl.scrollHeight;
	};

	Chat.prototype.scrollIntoView = function () {
		if (this.container.scrollIntoView) this.container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	};

	Chat.prototype.showNotice = function (text) {
		var self = this;
		this.noticeEl.textContent = text || '';
		if (text) setTimeout(function () { if (self.noticeEl.textContent === text) self.noticeEl.textContent = ''; }, 6000);
	};

	/* Actions */

	Chat.prototype.openTab = function (countryID) {
		countryID = this.allowedTab(countryID);
		this.tab = countryID;
		session('wD.chat.tab.' + this.source.gameID, String(countryID));
		this.renderTabs();
		this.renderHeader();
		this.renderCompose();
		this.renderMessages();
		this.scrollToEnd();
		if (this.isMember && this.newMessagesFrom.indexOf(countryID) !== -1) this.markSeen(countryID);
	};

	Chat.prototype.markSeen = function (countryID) {
		var self = this;
		this.newMessagesFrom = this.newMessagesFrom.filter(function (c) { return c !== countryID; });
		this.renderTabs();
		this.source.markMessagesSeen(this.countryID, countryID).then(function () {
			self.source.invalidateContext(); // Its newMessagesFrom is out of date
		}).catch(function () { /* The icon will come back on the next load if it didn't take */ });
	};

	Chat.prototype.markUnread = function () {
		var form = this.config.markUnreadForm ? document.getElementById(this.config.markUnreadForm) : null;
		if (!form) return;
		var input = form.querySelector('[name=msgCountryID]');
		if (input) input.value = String(this.tab);
		form.submit();
	};

	Chat.prototype.send = function () {
		var self = this;
		var text = this.textarea.value.trim();
		if (!text || !this.canPostTo(this.tab)) return;

		var fromCountryID = this.isMember ? this.countryID : 0;
		this.sendButton.disabled = true;
		this.textarea.disabled = true;
		this.source.sendMessage(fromCountryID, this.tab, text).then(function (messages) {
			self.textarea.value = '';
			if (!messages.length) {
				self.showNotice('Message not delivered: this country has muted you.');
				return;
			}
			var known = {};
			self.messages.forEach(function (m) { known[m.id] = true; });
			messages.forEach(function (m) { if (!known[m.id]) self.messages.push(m); });
			self.messages.sort(function (a, b) { return a.id - b.id; });
			self.renderMessages();
			self.scrollToEnd();
			if (window.gtag && self.config.analyticsEventName && self.tab !== self.countryID) {
				try { window.gtag('event', self.config.analyticsEventName); } catch (e) { /* ignore */ }
			}
		}).catch(function (error) {
			self.showNotice('Could not send: ' + (error && error.message ? error.message : 'unknown error'));
		}).finally(function () {
			self.sendButton.disabled = false;
			self.textarea.disabled = false;
			self.textarea.focus();
		});
	};

	return Chat;
})();
