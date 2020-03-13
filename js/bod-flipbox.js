/**
 * Version: 1.0.2 - Set front image height and width so we can use object-fit in css
 * 1.0.1 - Force align-itens to stretch on row
 * 
 */


/**
 * Bod: FlipBox
 */

 
!function($){
	"use strict";
	var BODFlipbox = function(container){
		// Common dom elements
		this.$container = $(container);
		this.$front = this.$container.find('.bod-flipbox-front');
		this.$frontH = this.$container.find('.bod-flipbox-front-h');
		this.$back = this.$container.find('.bod-flipbox-back');
		this.$backH = this.$container.find('.bod-flipbox-back-h');
		this.$btn = this.$container.find('.bod-btn');
		
		// check if force 100% height set for flipbox
		
		this.forceFullH = this.$container.hasClass('bod-full-h');
		if (this.forceFullH) {			
		
		// we have made some assumptions here 
		// set add display: flex to the parent element (widget wrapper), parent of parent (column) and parent of parent of parent (row)
			this.$container.parent().css('display', 'flex').parent().css('display', 'flex').parent().css({'display': 'flex', 'align-items' : 'stretch'});
		}

		// Simplified animation for IE11
		if (!!window.MSInputMethodContext && !!document.documentMode){
			this.$container.bodMod('animation', 'cardflip').find('.bod-flipbox-h').css({
				'transition-duration': '0s',
				'-webkit-transition-duration': '0s'
			});
		}

		// In chrome cube flip animation makes button not clickable. Replacing it with cube tilt
		var isWebkit = 'WebkitAppearance' in document.documentElement.style;
		if (isWebkit && this.$container.bodMod('animation') === 'cubeflip' && this.$btn.length){
			this.$container.bodMod('animation', 'cubetilt');
		}

		// For diagonal cube animations height should equal width (heometrical restriction)
		var animation = this.$container.bodMod('animation'),
			direction = this.$container.bodMod('direction');
		this.forceSquare = (animation == 'cubeflip' && ['ne', 'se', 'sw', 'nw'].indexOf(direction) != -1);

		// Container height is determined by the maximum content height
		this.autoSize = (this.$front[0].style.height == '' && !this.forceSquare);

		// Content is centered
		this.centerContent = (this.$container.bodMod('valign') == 'center');

		if (this._events === undefined) this._events = {};
		$.extend(this._events, {
			resize: this.resize.bind(this)
		});
		
		if (this.centerContent || this.autoSize) {
			this.padding = parseInt(this.$back.css('padding-top'));
			this.originalPadding  = this.$back.css('padding-top');
			this.paddingPercent = this.originalPadding.indexOf('%') != -1 ? true : false;
		}
		
		// if we are not centering content then apply a different transform on bod-flipbox-front-h
		// if we then are not dealing with % padding then it must be px and fixed so apply here so we only do it once
		
		if (!this.centerContent) {
			this.$frontH.css('transform' , 'translate(-50%,0%)');
			if (!this.paddingPercent) {
				this.$frontH.css('top' , this.padding + 'px');
			}
		}
		
		if (this.centerContent || this.forceSquare || this.autoSize) {
			$bod.$window.on('resize load', this._events.resize);
			this.resize();
		}

		this.makeHoverable('.bod-btn');

		// Fixing css3 animations rendering glitch on page load
		setTimeout(function(){
			this.$back.css('display', '');
			this.resize();
		}.bind(this), 250);
	};
	BODFlipbox.prototype = {
		resize: function(){
			// reset height and width of front image so it does not impact new calcs
			this.$front.find('.bod-flipbox-fill-image').css('width','100%').css('height','auto');
			var width = this.$container.width(),
				height;
			if (this.autoSize || this.centerContent) {
				var frontContentHeight = this.$frontH.height(),
					backContentHeight = this.$backH.height();
					
				// if we have a percentage padding then convert to px amount
				if (this.paddingPercent) {
					this.padding = parseInt((parseInt(this.originalPadding) / 100) * this.$container.width());
				}	
				
			}
			

			
			// Changing the whole container height
			if (this.forceSquare || this.autoSize) {
				height = this.forceSquare ? width : (Math.max(frontContentHeight, backContentHeight) + 2 * this.padding);
				this.$front.css('height', height + 'px');	
				// we also need to set height and width of front image so we can use object-fit
				
				this.$front.find('.bod-flipbox-fill-image').css('width',this.$container.width() + 'px').css('height',height+'px');
							
			} else {
				height = this.$container.height();
			}

			if (this.centerContent) {
				// this.$front.css('padding-top', Math.max(this.padding, (height - frontContentHeight) / 2));
				// this.$back.css('padding-top', Math.max(this.padding, (height - backContentHeight) / 2));
				this.$back.css('padding-top', Math.max(this.padding, (this.$container.height() - backContentHeight) / 2));

			} else if (this.paddingPercent) {
				
				// if we get here then we are not centering the content and we are using a percentage padding
				 
				this.$frontH.css('top' , this.padding + 'px');
			}

		}
	};
	$.extend(BODFlipbox.prototype, $bod.mutators.Hoverable);
	if (window.$bod === undefined) window.$bod = {};
	if ($bod.elements === undefined) $bod.elements = {};
	$bod.elements['bod-flipbox'] = BODFlipbox;
	if ($bod.maybeInit !== undefined) $bod.maybeInit();
}(jQuery);
