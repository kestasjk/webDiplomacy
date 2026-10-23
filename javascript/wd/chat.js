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
 * wD.Chat: the board's press panel, drawn by the browser with the markup and styles the server-rendered Chatbox
 * had (board/chatbox.php's output(), as it was), from the same sources the /game/ board uses: everyone's messages
 * from messages.json, the player's own from game/playercontext (kept in localStorage, so only new ones are asked
 * for), sent with game/sendmessage and marked read with game/messagesseen.
 *
 * The last few turns' messages are shown to start with; a line at the top of the messages offers the earlier ones
 * when there are any, so scrolling to the top is how to read back.
 *
 *   var chat = new wD.Chat(document.getElementById('wdChat'), { source: source, live: live, config: {...} });
 *   chat.prepare();   // before anything asks the source for a context
 *   chat.start();
 *
 * config, from board/chatbox.php: { countryID, isMember, canPostGlobal, pressType, phase, turn,
 *   countries: [{countryID, name}], members: [{countryID, name: HTML of the player's name, tabName: HTML of the
 *   country's name as the tabs show it}], colourblind, variantName, turnText: function(turn) -> "Spring, 1901",
 *   markUnreadForm: id of a form posting MarkAsUnread, analyticsEventName }
 */
var wD = window.wD || {};
window.wD = wD;

wD.Chat = (function () {
	'use strict';

	// How many turns back the messages are shown to start with, and how many more each "earlier messages" asks for
	var FIRST_TURNS = 5;
	var MORE_TURNS = 10;
	var ALLOWED_TAGS = { BR: true, STRONG: true, B: true, EM: true, I: true, U: true, SPAN: true, A: true };

	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) node.className = className;
		if (text !== undefined && text !== null) node.textContent = text;
		return node;
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

	/** libTime::text()'s wording, for when the page's own timestamp code isn't there to localise it */
	function timeText(unixTime) {
		var date = new Date(unixTime * 1000);
		var difference = Math.abs(Date.now() / 1000 - unixTime);
		var pad = function (n) { return n < 10 ? '0' + n : String(n); };
		var hours12 = function () {
			var h = date.getHours() % 12;
			return (h === 0 ? 12 : h);
		};
		var ampm = date.getHours() >= 12 ? 'PM' : 'AM';
		var days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
		var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
		if (difference < 22 * 60 * 60) return pad(hours12()) + ':' + pad(date.getMinutes()) + ' ' + ampm;
		if (difference < 4 * 24 * 60 * 60) return days[date.getDay()] + ' ' + pad(hours12()) + ' ' + ampm;
		if (difference < 3 * 7 * 22 * 60 * 60) return days[date.getDay()] + ' ' + pad(date.getDate()) + ' ' + months[date.getMonth()];
		return pad(date.getDate()) + ' ' + months[date.getMonth()] + ' ' + String(date.getFullYear()).slice(2);
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
		this.loadingEarlier = false;

		// How many turns back are shown; grows when earlier messages are asked for
		this.windowTurns = FIRST_TURNS;
		// The earliest turn the player's private messages have been fetched from (0: all of them), once known
		this.loadedFromTurn = null;

		this.countries = {};
		(this.config.countries || []).forEach(function (country) { this.countries[country.countryID] = country.name; }, this);
		this.members = {};
		(this.config.members || []).forEach(function (member) { this.members[member.countryID] = member; }, this);

		// The tab last open in this browser tab, as the old chatbox remembered it in the session
		var savedTab = session('wD.chat.tab.' + this.source.gameID);
		this.tab = this.allowedTab(savedTab === null ? 0 : parseInt(savedTab, 10));
	}

	/** The earliest turn whose messages are shown */
	Chat.prototype.fromTurn = function () {
		return Math.max(0, this.currentTurn - this.windowTurns);
	};

	/** Decide what the first context request should ask for; call before anything loads the context */
	Chat.prototype.prepare = function () {
		this.source.initialMessageParams(this.isMember ? this.countryID : 0, this.windowTurns, this.currentTurn);
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

	/**
	 * @param {number} countryID The tab
	 * @param {boolean} everyTurn Whether to include messages from before the turns being shown
	 */
	Chat.prototype.messagesForTab = function (countryID, everyTurn) {
		var me = this.countryID;
		var muted = this.mutedCountryIDs;
		var fromTurn = everyTurn ? 0 : this.fromTurn();
		return this.messages.filter(function (m) {
			if (m.turn < fromTurn) return false;
			if (muted.indexOf(m.fromCountryID) !== -1 && m.fromCountryID !== me) return false;
			if (countryID === 0) return m.toCountryID === 0;
			if (countryID === me) return (m.fromCountryID === me && m.toCountryID === me) || (m.fromCountryID === 0 && m.toCountryID === me);
			return (m.toCountryID === me && m.fromCountryID === countryID) || (m.fromCountryID === me && m.toCountryID === countryID);
		});
	};

	/** Whether there are, or may be, messages for the open tab from before the turns being shown */
	Chat.prototype.hasEarlier = function () {
		var fromTurn = this.fromTurn();
		if (fromTurn <= 0) return false;
		if (this.messagesForTab(this.tab, true).some(function (m) { return m.turn < fromTurn; })) return true;
		// The player's own messages are only fetched back to loadedFromTurn; the server may hold older ones
		return this.tab !== 0 && this.isMember && (this.loadedFromTurn === null || this.loadedFromTurn > 0);
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

	/**
	 * Load whatever is new and redraw; one load at a time, with a load asked for meanwhile run afterwards.
	 * @param {Object} opts { keepOffset: keep the messages where they are on screen while earlier ones go in above }
	 */
	Chat.prototype.refresh = function (opts) {
		var self = this;
		opts = opts || {};
		if (this.loading) {
			this.refreshQueued = true;
			return this.loading;
		}
		var before = {};
		this.messages.forEach(function (m) { before[m.id] = true; });

		this.loading = this.source.getContext().then(function (context) {
			self.currentTurn = context.game.turn;
			if (context.member) self.mutedCountryIDs = context.member.mutedCountryIDs || [];
			return self.source.loadMessages({ countryID: self.isMember ? self.countryID : 0, turns: self.windowTurns });
		}).then(function (result) {
			self.messages = result.messages;
			self.newMessagesFrom = result.newMessagesFrom || [];
			if (self.isMember) self.loadedFromTurn = result.fromTurn;
			var arrived = result.messages.filter(function (m) { return !before[m.id]; });
			self.loadingEarlier = false;
			self.renderMessages(opts);
			self.renderTabs();
			self.announce(arrived, Object.keys(before).length > 0);
		}).catch(function (error) {
			if (window.console) console.error('Chat: ' + (error && error.message ? error.message : error));
			self.loadingEarlier = false;
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

	/** Show, and if need be fetch, the messages of the turns before those shown */
	Chat.prototype.loadEarlier = function () {
		if (this.loadingEarlier || this.fromTurn() <= 0) return;
		this.windowTurns += MORE_TURNS;
		this.loadingEarlier = true;
		this.renderMessages({ keepOffset: true });
		this.refresh({ keepOffset: true });
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

	/* Rendering: the Chatbox's own markup, so that its styles apply unchanged */

	Chat.prototype.render = function () {
		var self = this;
		var root = this.container;
		root.innerHTML = '';
		root.classList.add('wdchat');
		if (this.config.variantName) root.classList.add('variant' + this.config.variantName);

		this.tabsEl = null;
		if (this.isMember) {
			this.tabsEl = el('div', 'gamelistings-tabs');
			this.tabsEl.id = 'chatboxtabs';
			root.appendChild(this.tabsEl);
		}

		var wrapper = el('div', 'chatWrapper');
		root.appendChild(wrapper);

		// Who is being talked to
		var headerBox = el('div', 'chatbox' + (this.isMember ? '' : ' chatboxnotabs'));
		var headerTable = el('table', 'chatbox');
		this.headerRow = el('tr', 'barAlt2 membersList');
		headerTable.appendChild(this.headerRow);
		headerBox.appendChild(headerTable);
		wrapper.appendChild(headerBox);

		// The messages
		this.scrollEl = el('div', 'chatbox');
		this.scrollEl.id = 'chatboxscroll';
		this.listTable = el('table', 'chatbox');
		this.scrollEl.appendChild(this.listTable);
		wrapper.appendChild(this.scrollEl);

		// The send box
		this.composeBox = el('div', 'chatbox');
		var form = el('form', 'safeForm');
		form.id = 'chatForm';
		form.method = 'post';
		form.action = '#';
		form.addEventListener('submit', function (e) { e.preventDefault(); self.send(); });
		var table = el('table');
		var row1 = el('tr', 'barAlt2');
		this.markUnreadCell = el('td', 'left');
		row1.appendChild(this.markUnreadCell);
		var sendCell = el('td', 'right');
		sendCell.rowSpan = 2;
		this.textarea = el('textarea');
		this.textarea.id = 'sendbox';
		this.textarea.name = 'newmessage';
		this.textarea.tabIndex = 1;
		this.textarea.rows = 5;
		this.textarea.style.setProperty('width', '98%', 'important');
		this.textarea.addEventListener('keydown', function (e) {
			if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
				e.preventDefault();
				self.send();
			}
		});
		sendCell.appendChild(this.textarea);
		row1.appendChild(sendCell);
		table.appendChild(row1);
		var row2 = el('tr', 'barAlt2');
		var buttonCell = el('td', 'left send');
		this.sendButton = el('input', 'form-submit');
		this.sendButton.type = 'submit';
		this.sendButton.tabIndex = 2;
		this.sendButton.value = 'Send';
		this.sendButton.name = 'Send';
		this.sendButton.id = 'message-send';
		buttonCell.appendChild(this.sendButton);
		buttonCell.appendChild(el('br'));
		this.noticeEl = el('span', 'wdchat-notice');
		buttonCell.appendChild(this.noticeEl);
		row2.appendChild(buttonCell);
		table.appendChild(row2);
		form.appendChild(table);
		this.composeBox.appendChild(form);
		wrapper.appendChild(this.composeBox);

		// "Mark unread" on its own, for a rulebook-press game between diplomacy phases
		this.markUnreadBox = el('div', 'chatbox');
		var muTable = el('table');
		var muRow = el('tr', 'barAlt2');
		this.markUnreadOnlyCell = el('td', 'center');
		muRow.appendChild(this.markUnreadOnlyCell);
		muTable.appendChild(muRow);
		this.markUnreadBox.appendChild(muTable);
		wrapper.appendChild(this.markUnreadBox);

		this.renderTabs();
		this.renderHeader();
		this.renderCompose();
	};

	Chat.prototype.renderTabs = function () {
		var self = this;
		if (!this.tabsEl) return;
		this.tabsEl.innerHTML = '';
		this.tabsAllowed().forEach(function (countryID) {
			var current = countryID === self.tab;
			var link = el('a', 'country' + countryID + (current ? ' current' : ''));
			link.href = '#chatboxanchor';
			var member = self.members[countryID];
			if (countryID === self.countryID) link.appendChild(document.createTextNode('Notes'));
			else if (member && member.tabName) link.innerHTML = member.tabName;
			else link.appendChild(document.createTextNode(self.countryName(countryID)));
			if (!current) link.title = 'Open ' + (countryID === 0 ? 'the global' : self.countryName(countryID) + "'s") + ' chatbox tab';
			if (!current && self.newMessagesFrom.indexOf(countryID) !== -1) {
				link.appendChild(document.createTextNode(' '));
				var icon = el('img');
				icon.src = 'images/icons/mail.png';
				icon.alt = 'Unread message';
				icon.title = 'Unread message';
				link.appendChild(icon);
			}
			link.addEventListener('click', function (e) {
				e.preventDefault();
				self.openTab(countryID);
			});
			self.tabsEl.appendChild(document.createTextNode(' '));
			self.tabsEl.appendChild(link);
		});
	};

	/**
	 * The cells of a member's row in the members table at the foot of the page, which is the member bar the old
	 * chatbox showed above a country's messages.
	 */
	Chat.prototype.memberBarCells = function (countryID) {
		var rows = document.querySelectorAll('.membersFullTable tr.member');
		for (var i = 0; i < rows.length; i++) {
			if (!rows[i].querySelector('.memberCountryName .country' + countryID)) continue;
			return Array.prototype.map.call(rows[i].children, function (cell) {
				var copy = cell.cloneNode(true);
				Array.prototype.forEach.call(copy.querySelectorAll('[id]'), function (node) { node.removeAttribute('id'); });
				copy.removeAttribute('id');
				return copy;
			});
		}
		return null;
	};

	Chat.prototype.renderHeader = function () {
		var self = this;
		var row = this.headerRow;
		row.innerHTML = '';
		var cell = el('td');
		if (this.tab === 0) {
			var names = Object.keys(this.countries).map(Number).sort(function (a, b) { return a - b; }).map(function (countryID) {
				var member = self.members[countryID];
				return (member && member.name) || ('<span class="country' + countryID + '">' + self.countryName(countryID) + '</span>');
			});
			var list = el('div', 'chatboxMembersList');
			list.innerHTML = names.join(', ');
			cell.appendChild(list);
			row.appendChild(cell);
		} else if (this.tab === this.countryID) {
			row.appendChild(cell);
		} else {
			var cells = this.memberBarCells(this.tab);
			if (cells) {
				cells.forEach(function (c) { row.appendChild(c); });
			} else {
				var member = self.members[this.tab];
				var name = el('div', 'chatboxMembersList');
				name.innerHTML = (member && member.name) || self.countryName(this.tab);
				cell.appendChild(name);
				row.appendChild(cell);
			}
		}
	};

	Chat.prototype.renderCompose = function () {
		var self = this;
		var canPost = this.canPostTo(this.tab);
		var canMarkUnread = this.isMember && this.tab !== 0 && this.tab !== this.countryID && !!this.config.markUnreadForm;
		var markUnreadLink = function () {
			var link = el('a', null, 'Mark unread');
			link.href = '#';
			link.tabIndex = 3;
			link.addEventListener('click', function (e) {
				e.preventDefault();
				self.markUnread();
			});
			return link;
		};

		this.composeBox.style.display = canPost ? '' : 'none';
		this.markUnreadCell.innerHTML = '';
		if (canPost && canMarkUnread) this.markUnreadCell.appendChild(markUnreadLink());

		var unreadOnly = !canPost && canMarkUnread && this.config.pressType === 'RulebookPress' && this.config.phase !== 'Diplomacy';
		this.markUnreadBox.style.display = unreadOnly ? '' : 'none';
		this.markUnreadOnlyCell.innerHTML = '';
		if (unreadOnly) this.markUnreadOnlyCell.appendChild(markUnreadLink());
	};

	/**
	 * The messages of the open tab, as Chatbox::renderMessages() laid them out, with the "earlier messages" line
	 * above them when there are turns not shown.
	 */
	Chat.prototype.renderMessages = function (opts) {
		var self = this;
		opts = opts || {};
		var scroll = this.scrollEl;
		var table = this.listTable;
		var beforeHeight = scroll.scrollHeight;
		var beforeTop = scroll.scrollTop;
		var atEnd = (beforeHeight - beforeTop - scroll.clientHeight) < 40;
		table.innerHTML = '';

		if (this.hasEarlier() || this.loadingEarlier) {
			var moreRow = el('tr', 'barAlt1 wdchat-more');
			var moreCell = el('td', 'notice');
			moreCell.colSpan = 2;
			if (this.loadingEarlier) {
				moreCell.appendChild(document.createTextNode('Loading earlier messages...'));
			} else {
				var link = el('a', 'light', 'Show earlier messages');
				link.href = '#';
				link.title = 'Messages from before ' + (this.config.turnText ? this.config.turnText(this.fromTurn()) : 'the turns shown');
				link.addEventListener('click', function (e) {
					e.preventDefault();
					self.loadEarlier();
				});
				moreCell.appendChild(link);
			}
			moreRow.appendChild(moreCell);
			table.appendChild(moreRow);
		}

		var messages = this.messagesForTab(this.tab, false);
		if (!messages.length) {
			var emptyRow = el('tr', 'barAlt1');
			emptyRow.appendChild(el('td', 'notice', 'No messages yet posted.'));
			table.appendChild(emptyRow);
		}

		var alternate = false;
		messages.forEach(function (m) {
			alternate = !alternate;
			var fromMe = self.isMember && m.fromCountryID === self.countryID;
			var row = el('tr', 'replyalternate' + (alternate ? '1' : '2') + ' gameID' + self.source.gameID + 'countryID' + m.fromCountryID);

			var timeCell = el('td', 'left time');
			var stamp = el('span', 'timestamp', timeText(m.timeSent) + ' UTC');
			stamp.setAttribute('unixtime', String(m.timeSent));
			timeCell.appendChild(stamp);
			row.appendChild(timeCell);

			var textCell = el('td', 'right' + (m.fromCountryID !== 0 ? ' country' + m.fromCountryID : ''));
			if (m.turn < self.currentTurn && self.config.turnText) {
				textCell.appendChild(el('strong', null, self.config.turnText(m.turn)));
				textCell.appendChild(document.createTextNode(': '));
			}
			// The country's name in front of the text, for the colour-blind
			if (self.config.colourblind) {
				if (fromMe) textCell.appendChild(el('strong', null, 'You:'));
				else if (m.fromCountryID !== 0) textCell.appendChild(el('strong', null, self.countryName(m.fromCountryID) + ':'));
				if (fromMe || m.fromCountryID !== 0) textCell.appendChild(document.createTextNode(' '));
			}
			var text = sanitize(m.message);
			if (fromMe) {
				var mine = el('span', 'messageFromMe');
				mine.appendChild(text);
				textCell.appendChild(mine);
			} else {
				textCell.appendChild(text);
			}
			row.appendChild(textCell);
			table.appendChild(row);
		});

		// The page's own timestamp code puts the times into the viewer's timezone
		if (typeof window.updateTimestamps === 'function') {
			try { window.updateTimestamps(); } catch (e) { /* the UTC text stays */ }
		}

		if (opts.keepOffset) scroll.scrollTop = scroll.scrollHeight - beforeHeight + beforeTop;
		else if (atEnd) this.scrollToEnd();
	};

	Chat.prototype.scrollToEnd = function () {
		this.scrollEl.scrollTop = this.scrollEl.scrollHeight;
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
		if (this.canPostTo(countryID)) {
			try { this.textarea.focus({ preventScroll: true }); } catch (e) { /* older browsers */ }
		}
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
				self.showNotice('Not delivered: this country has muted you.');
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
