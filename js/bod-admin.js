/**
 * FlipBox Admin
 */
jQuery(function($){	


// take the url, title and target and combine into a string

	function encodeLink(url, title, target){
		var result = 'url:' + encodeURIComponent(url);
		if (title) result += '|title:' + encodeURIComponent(title);
		if (target) result += '|target:' + encodeURIComponent(target);
		return result;
	}
		
// take a URL string and extract the url, title, and target into an array		
		
	function decodeLink(link){
		var atts = link.split('|'),
			result = {url: '', title: '', target: ''};
		atts.forEach(function(value, index){
			var param = value.split(':', 2);
			result[param[0]] = decodeURIComponent(param[1]).trim();
		});
		return result;
	}
	
// handle link dialog box
	
	$(document).on("click", ".bod-linkdialog-btn", function(event) {
			
			clickedEle = $( this );
    		textAreaID = $( clickedEle ).siblings( 'textarea' ).attr( 'id' );
			textAreaVal = $( clickedEle ).siblings( 'textarea' ).val();
			textArea = $( clickedEle ).siblings( 'textarea[name]' );
			textAreaField = $( clickedEle ).siblings( 'textarea' );

			// trap click on submit
			
			$('body').on('click', '#wp-link-submit', function(event) {		
				var linkAtts = wpLink.getAttrs();//the links attributes (href, target) are stored in an object, which can be access via  wpLink.getAttrs()
				
				// combine the linkatts into a single string 
				var linkText = $('#wp-link-text').val();
				var linkFull = encodeLink(linkAtts.href, linkText, linkAtts.target);
				
				$('.bod-linkdialog-url').text(linkAtts.href);//get the href attribute and add to a textfield, or use as you see fit
				$(textAreaField).text(linkFull); // pop textfield with full url details
				
				wpLink.textarea = jQuery('body'); //to close the link dialogue, it is again expecting an wp_editor instance, so you need to give it something to set focus back to. In this case, I'm using body, but the textfield with the URL would be fine
				wpLink.close();//close the dialogue
		//trap any events
				event.preventDefault ? event.preventDefault() : event.returnValue = false;
				event.stopPropagation();
				$('body').off('click', '#wp-link-cancel, #wp-link-close, #wp-link-submit'); // cancel events
				return false;
			});
			
			// detect click on cancel or close
				
			$('body').on('click', '#wp-link-cancel, #wp-link-close', function(event) {
				wpLink.textarea = $('body');
				wpLink.close();
				event.preventDefault ? event.preventDefault() : event.returnValue = false;
				event.stopPropagation();
				$('body').off('click', '#wp-link-cancel, #wp-link-close, #wp-link-submit'); // cancel events
				return false;				
			});
			
            wpActiveEditor = true; //we need to override this var as the link dialogue is expecting an actual wp_editor instance
            wpLink.open(textAreaID); //open the link popup
			wpLink.textarea = textArea;
			urlData = decodeLink(textAreaVal);
			$('#wp-link-url').val(urlData.url);
			$('#wp-link-text').val(urlData.title);
			$('#wp-link-target').prop('checked',(urlData.target=='_blank'));
					
            return false;
    });
	
// Attach color picker to fields with specificed class

	$(function() {
		$('.bod-color-picker').wpColorPicker();
		$('.bod-color-picker').addClass('test-class');
		$(document).ajaxComplete(function() {
			$('.bod-color-picker').wpColorPicker();
		});

	});

	
// Handle tabs

	$('body').on('click', '.bod-tabs-item', function(event) {
		
		// if clicked tab is already active then do nothing
		if ( $(this).hasClass("active") ) {
			return;
		}
		
		// remove active class
		$('.bod-tabs-item').removeClass("active");
		
		// add active class to current item 
		$(this).addClass("active");
		
		// hide all tabs
		$('.bod-tabs-section').hide();
		
		// show tab we clicked on. Extract element ID for clicked tab and show tab with same number
		var elId = $(this).attr('id');
		var intElId = parseInt (elId);
		$( '.bod-tabs-section:nth-child(' + intElId + ')' ).show();
				
	});		

// Handle media / image uploads 

	// add image

	$('body').on('click', '.bod-imgattach-add', bodShowMediaUploader);
	function bodShowMediaUploader(e){
		e.preventDefault();
		var that = this;
		var title = $(this).attr('title');

		var bod_custom_upload = wp.media({
			title: title,
			button: {text:title},
			multiple: false
		})
		.on('select' , function() {
			var attachment = bod_custom_upload.state().get('selection').first().toJSON(); // get the image details
			bodSetImage(that, attachment);
		})
		.open();
		var that = this;
	};

	function bodSetImage(e, attachment) {
		var $parentImageCont = $(e).closest('.bod-imgattach');
		var $imageDetails = $($parentImageCont).children('.bod-image-details');
		var $imageItem = $($parentImageCont).find('.bod-imgattach-list li');
		$imageDetails.val(attachment.id);
		
		// build html to replace the image 
		var html = '<li data-id="' + attachment.id + '">' +
			'<a class="bod-imgattach-delete" href="javascript:void(0)">&times;</a>' +
			'<img width="150" height="150" class="attachment-thumbnail" src="';
		if (attachment.sizes !== undefined) {
			var size = (attachment.sizes.thumbnail !== undefined) ? 'thumbnail' : 'full';
			html += attachment.sizes[size].url;
		}
		html += '"></li>';			
					
		$imageItem.replaceWith(html).show(); // display thumbnail of selected image		
	};
		
	// remove image
	
	$('body').on('click', '.bod-imgattach-delete', function(e) {
		e.preventDefault();
		var $parentImageCont = $(this).closest('.bod-imgattach');
		var $imageDetails = $($parentImageCont).children('.bod-image-details');
		var $imageItem = $($parentImageCont).find('.bod-imgattach-list li');
		
		$imageItem.hide(); // display thumbnail of selected image
		$imageDetails.val('');
	});
		
	// deal with checkbox
	
    $('body').on('change' ,'.bod-checkbox :checkbox', function() {	
		$inputField = $(this).parent().next();
        if(this.checked) {
			fieldVal = $(this).val();
            $($inputField).val(fieldVal);     
        } else {
			$($inputField).val(''); 
		}
           
    });
	
	// deal with optional fields i.e. display fields depending upon certain select field values
	
	// object 
	// select field : {
	// 		select field value : 'field to display'	
	
	var bodFieldDisplay = {
    'link_type': {
        container : '.for_link',
        btn : '.for_link, .for_back_btn_label, .for_back_btn_bgcolor, .for_back_btn_color'  
    },
    'front_icon_type': {
        font : '.for_front_icon_name, .for_front_icon_size, .for_front_icon_style, .for_front_icon_color, .for_front_icon_bgcolor',
        image : '.for_front_icon_image, .for_front_icon_image_width'  
    },
	'show_on': {
        btn : '.for_btn_label, .for_btn_bgcolor, .for_btn_color, .for_align',
        text : '.for_btn_label, .for_text_size, .for_text_color, .for_align',
		image : '.for_image, .for_image_size, .for_align',
		selector : '.for_trigger_selector',
		load : '.for_show_delay'         
    }}
	
	// build a string containing fields on which to place the event handler
	
	var bodFieldSelector = '';
	for (var bodProperty in bodFieldDisplay) {
		if (bodFieldSelector !== '') {
			bodFieldSelector += ' ,.for_' + bodProperty + ' select';
		} else {
			bodFieldSelector += '.for_' + bodProperty + ' select';
		}
	}
	
	if (bodFieldSelector !== '') {
		
		$('body').on('change' ,bodFieldSelector , function() {	
			
			var $row = $(this).closest('.bod-eform-row'); // traverse up to get the form row
			
			// loop round the object getting the name of the select field 
			for (var bodProperty in bodFieldDisplay) {
				var bodClassName  = 'for_' + bodProperty; // build the class name to look for. for_ + field name 
				// check if field which triggered the change event is the current field in the object
				if ($row.hasClass(bodClassName)) { 
				
				// if we are in here we have found the field 
				// loop round the inner properties which contain the possible values of the select field
				
					var bodFieldsToShow = '';
					for (var bodFieldValue in bodFieldDisplay[bodProperty]) {
						if (bodFieldValue == $(this).val() ) {
							bodFieldsToShow = bodFieldDisplay[bodProperty][bodFieldValue];
						} else {
							var bodFieldsToHide = bodFieldDisplay[bodProperty][bodFieldValue];
							$(bodFieldsToHide).hide();
						}
					}
					if (bodFieldsToShow !== '') $(bodFieldsToShow).show(); 
				}
			}
			
		});
		$(document).ajaxComplete(function() {
			$(bodFieldSelector).change();
		});
		
	
	}
	
});
