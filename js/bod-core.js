
/**
 * Retrieve/set/erase dom modificator class <mod>_<value> for the CSS Framework
 * @param {String} mod Modificator namespace
 * @param {String} [value] Value
 * @returns {string|jQuery}
 */
jQuery.fn.bodMod = function(mod, value){
	if (this.length == 0) return this;
	// Remove class modificator (delete)
	if (value === false) {
		return this.each(function(){
			// REGEX match = (start or space) mod_ + one or more of any char or num or -_ (space or end)
			// the $2 is a capture group from the regex, $1 = start or space and $2 = space or end
			// so below says remove classnames starting with mod_ and replace with space or end 
			this.className = this.className.replace(new RegExp('(^| )' + mod + '\_[a-zA-Z0-9\_\-]+( |$)'), '$2');
		});
	}
	
	// regex = any char except line breaks + mod_ ( and char or num or -_ one or more times) any char except line breaks one or more times
	var pcre = new RegExp('^.*?' + mod + '\_([a-zA-Z0-9\_\-]+).*?$'),
		arr;
	// Retrieve modificator (get)
	if (value === undefined) {
		return (arr = pcre.exec(this.get(0).className)) ? arr[1] : false; // return capture group 1 i.e. stuff after mod_
	}
	// Set modificator
	else {
		// REGEX match = (start or space) mod_ + one or more of any char or num or -_ (space or end)
		// the $1 & $2 is a capture group from the regex, $1 = start or space and $2 = space or end
		// so below says replace current mod class with new value
		// e.g. mod = animate value = flip would replace current animate class with animate_flip
		var regexp = new RegExp('(^| )' + mod + '\_[a-zA-Z0-9\_\-]+( |$)');
		return this.each(function(){
			if (this.className.match(regexp)) {
				this.className = this.className.replace(regexp, '$1' + mod + '_' + value + '$2');
			}
			else {
				this.className += ' ' + mod + '_' + value;
			}
		});
	}
};


/**
 * Make mobile and desktop hoverable
 */
!function($){
	if (window.$bod === undefined) window.$bod = {};

	$bod.isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

	// jQuery objects of commonly used DOM-elements
	$bod.$window = $(window);
	$bod.$document = $(document);
	$bod.$html = $(document.documentElement);
	$bod.$body = $(document.body);

	// Known elements and their constructors
	$bod.elements = {};

	var inited = [];
	$bod.maybeInit = function(){
		for (var elm in $bod.elements) {
			if (!$bod.elements.hasOwnProperty(elm) || inited.indexOf(elm) != -1) continue;
			$('.' + elm).each(function(){
				$(this).data(elm, new $bod.elements[elm](this));
			});
			inited.push(elm);
		}
	};
	$($bod.maybeInit);
	
	// Class mutators
	$bod.mutators = {};
	$bod.mutators.Hoverable = {
		/**
		 * Allows to hover the whole element both by desktop mouse and touch hoverable devices.
		 * Hovered element gets additional "hover" class at this moment.
		 *
		 * @var {String} Selector of inner elements that will be excluded from the touch event
		 */
		makeHoverable: function(exclude){
			if (this._events === undefined) this._events = {}; // functions for events 
			if ($bod.isMobile) {
				// Mobile: Touch hover
				this._events.touchHoverStart = function(){
					this.$container.toggleClass('hover');
				}.bind(this);
				this.$container.on('touchstart', this._events.touchHoverStart); // call on touchstart
				if (exclude) {
					this._events.touchHoverPrevent = function(e){
						e.stopPropagation();
					};
					this.$container.find(exclude).on('touchstart', this._events.touchHoverPrevent);
				}
			} else {
				// Desktop: Mouse hover
				this._mouseInside = false;
				this._focused = false;

				$.extend(this._events, {
					mouseHoverStart: function(){
						this.$container.addClass('hover');
						this._mouseInside = true;
					}.bind(this),
					mouseHoverEnd: function(){
						if (!this._focused) this.$container.removeClass('hover');
						this._mouseInside = false;
					}.bind(this),
					focus: function(){
						this.$container.addClass('hover');
						this._focused = true;
					}.bind(this),
					blur: function(){
						if (!this._mouseInside) this.$container.removeClass('hover');
						this._focused = false;
					}.bind(this)
				});
				this.$container.on('mouseenter', this._events.mouseHoverStart);
				this.$container.on('mouseleave', this._events.mouseHoverEnd);
				this.$focusable = this.$container.find('a').addBack('a');
				this.$focusable.on('focus', this._events.focus);
				this.$focusable.on('blur', this._events.blur);
			}
		}
	};
}(jQuery);

