/**
 * Modal Popup
 */
!function($){
	"use strict";
	var BODPopup = function(container){
		this.$container = $(container);

		this._events = {
			show: this.show.bind(this),
			afterShow: this.afterShow.bind(this),
			hide: this.hide.bind(this),
			preventHide: function(e){
				e.stopPropagation();
			},
			afterHide: this.afterHide.bind(this),
			resize: this.resize.bind(this),
			keypress: function(e){
				if (e.keyCode == 27) this.hide();
			}.bind(this)
		};

		// Event name for triggering CSS transition finish
		this.transitionEndEvent = (navigator.userAgent.search(/webkit/i)>0) ? 'webkitTransitionEnd' : 'transitionend';
		this.isFixed = !$bod.isMobile;

		this.$trigger = this.$container.find('.bod-popup-trigger');
		this.triggerType = this.$trigger.bodMod('type');
		if (this.triggerType == 'load'){
			var delay = this.$trigger.data('delay') || 2;
			setTimeout(this.show.bind(this), delay * 1000);
		} else if (this.triggerType == 'selector') {
			var selector = this.$trigger.data('selector');
			if (selector) $bod.$body.on('click', selector, this._events.show);
		} else {
			this.$trigger.on('click', this._events.show);
		}
		this.$wrap = this.$container.find('.bod-popup-wrap')
			.bodMod('pos', this.isFixed ? 'fixed' : 'absolute')
			.on('click', this._events.hide);
		this.$box = this.$container.find('.bod-popup-box');
		this.$overlay = this.$container.find('.bod-popup-overlay')
			.bodMod('pos', this.isFixed ? 'fixed' : 'absolute')
			.on('click', this._events.hide);
		this.$container.find('.bod-popup-closer, .bod-popup-box-closer').on('click', this._events.hide);
		this.$box.on('click', this._events.preventHide);
		this.size = this.$box.bodMod('size');

		this.timer = null;
	};
	BODPopup.prototype = {
		_hasScrollbar: function(){
			return document.documentElement.scrollHeight > document.documentElement.clientHeight;
		},
		_getScrollbarSize: function(){
			if ($bod.scrollbarSize === undefined) {
				var scrollDiv = document.createElement('div');
				scrollDiv.style.cssText = 'width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;';
				document.body.appendChild(scrollDiv);
				$bod.scrollbarSize = scrollDiv.offsetWidth - scrollDiv.clientWidth;
				document.body.removeChild(scrollDiv);
			}
			return $bod.scrollbarSize;
		},
		show: function(){
			clearTimeout(this.timer);
			this.$overlay.appendTo($bod.$body).show();
			this.$wrap.appendTo($bod.$body).show();
			if (this.size != 'f') {
				this.resize();
			}
			if (this.isFixed) {
				$bod.$html.addClass('cloverlay_fixed');
				// Storing the value for the whole popup visibility session
				this.windowHasScrollbar = this._hasScrollbar();
				if (this.windowHasScrollbar && this._getScrollbarSize()) {
					$bod.$html.css('margin-right', this._getScrollbarSize());
				}
			} else {
				this.$overlay.css({
					height: $bod.$document.height()
				});
				this.$wrap.css('top', $bod.$window.scrollTop());
			}
			$bod.$body.on('keypress', this._events.keypress);
			this.timer = setTimeout(this._events.afterShow, 25);
		},
		afterShow: function(){
			clearTimeout(this.timer);
			this.$overlay.addClass('active');
			this.$box.addClass('active');
			// UpSolution Themes Compatibility
			// TODO Move to themes
			if (window.$us !== undefined && $us.$canvas !== undefined) {
				$us.$canvas.trigger('contentChange');
			}
			$bod.$window.trigger('resize');
			if (this.size != 'f') {
				$bod.$window.on('resize', this._events.resize);
			}
		},
		hide: function(){
			clearTimeout(this.timer);
			if (this.size != 'f') {
				$bod.$window.off('resize', this._events.resize);
			}
			$bod.$body.off('keypress', this._events.keypress);
			this.$box.on(this.transitionEndEvent, this._events.afterHide);
			this.$overlay.removeClass('active');
			this.$box.removeClass('active');
			// Closing it anyway
			this.timer = setTimeout(this._events.afterHide, 1000);
		},
		afterHide: function(){
			clearTimeout(this.timer);
			this.$box.off(this.transitionEndEvent, this._events.afterHide);
			this.$overlay.appendTo(this.$container).hide();
			this.$wrap.appendTo(this.$container).hide();
			if (this.isFixed) {
				$bod.$html.removeClass('cloverlay_fixed');
				if (this.windowHasScrollbar) $bod.$html.css('margin-right', '');
				// To properly resize 3-rd party elements
				$bod.$window.trigger('resize');
			}
		},
		resize: function(){
			var animation = this.$box.bodMod('animation'),
				padding = parseInt(this.$box.css('padding-top')),
				winHeight = $bod.$window.height(),
				popupHeight = this.$box.height();
			if (!this.isFixed) {
				this.$overlay.css('height', $bod.$document.height());
			}
			this.$box.css('top', Math.max(0, (winHeight - popupHeight) / 2 - padding));
		}
	};
	if (window.$bod === undefined) window.$bod = {};
	if ($bod.elements === undefined) $bod.elements = {};
	$bod.elements['bod-popup'] = BODPopup;
	if ($bod.maybeInit !== undefined) $bod.maybeInit();
}(jQuery);
