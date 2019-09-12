;(function () {
	'use strict';
	
	// init main navigation
	document.addEventListener("DOMContentLoaded", function(event) {
		var cc = new CookieConsent();
	});

	// Constructor
	function CookieConsent() {
		
		var self = this;

		// Save a reference to the popup
		this.popup = document.getElementById('CookieConsent');
		
		// handle acceptance buttons
		this.buttons = this.menu.querySelectorAll('.js-cookie-consent-button');
		if (this.buttons.length > 0) {
			Array.prototype.forEach.call(this.buttons, function (button, index) {
				button.addEventListener('click', function (e) {
					e.preventDefault();
					this.accept();
				}.bind(this))
			}.bind(this))
		}
		
		// handle window scroll
		window.addEventListener('scroll', throttle(function() {
			self.accept();
		}, 200));

		// initiate listeners object for public events
		this._listeners = {}
	}
	
	CookieConsent.prototype.accept = function () {
		Cookie.set('CookieConsent', 'true');
		if (this.popup) {
			this.popup.style.display = 'none';
		}
		this._fire('accept');
		return this;
	}

	CookieConsent.prototype._fire = function (type, data) {
		if (typeof this._listeners === 'undefined') {
			this._listeners = [];
		}
		var listeners = this._listeners[type] || [];

		listeners.forEach(function (listener) {
			listener(data);
		})
	}

	CookieConsent.prototype.on = function (type, handler) {
		if (typeof this._listeners[type] === 'undefined') {
			this._listeners[type] = [];
		}

		this._listeners[type].push(handler);

		return this;
	}

	CookieConsent.prototype.off = function (type, handler) {
		var index = this._listeners[type].indexOf(handler);

		if (index > -1) {
			this._listeners[type].splice(index, 1);
		}

		return this;
	}

	// Export CookieConsent
	if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
		module.exports = CookieConsent;
	} else if (typeof define === 'function' && define.amd) {
		define('CookieConsent', [], function () {
			return CookieConsent;
		})
	} else {
		// attach to window
		window.CookieConsent = CookieConsent;
	}
}());
