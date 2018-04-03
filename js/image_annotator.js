
jQuery( document ).ready(function() {



       // populate
	if(jQuery("#_imganno_markers").val()) { var markers = jQuery.parseJSON(jQuery("#_imganno_markers").val()); }
        else { var markers = {}; }



	jQuery.each(markers, function( index, value ) {
	    var coords = value.split(":");
	    // get the width and height of the image
	    var w = jQuery("#myimg").width();
	    var h = jQuery("#myimg").height();

	    // position the marker according to width and height of image
	    var pixelsleft = parseInt(w * (coords[0]/100));
	    var pixelstop = parseInt(h * (coords[1]/100));
	    
	    jQuery("#"+index).css({"top":pixelstop,"left":pixelsleft});
	    jQuery("#"+index+"_popup").css({"top":pixelstop-310,"left":pixelsleft-140});
	});



	// roll over img
	jQuery("#myimg").mouseover(function() {
	  jQuery(".imganno_marker").show();
	});

	jQuery("#myimg").mouseout(function() {
	  jQuery(".imganno_marker").hide();
	});

	jQuery(".imganno_marker").mouseover(function() {
	  var id = jQuery(this).attr("id");
	  jQuery(".imganno_marker").show();
	  jQuery(this).addClass("marker_highlight");
	  jQuery("#"+id+"_footnote").addClass("footnote_highlight");
	});
	jQuery(".imganno_marker").mouseout(function() {
	  jQuery(".imganno_marker").hide();
	  jQuery(this).removeClass("marker_highlight");
	  jQuery(".annotation_footnote").removeClass("footnote_highlight");
	});




	// image marker poups
	jQuery(".imganno_marker").click(function(event) {
		var id = jQuery(this).attr("id");
		jQuery('.annotation_popup').hide();
		jQuery("#"+id+"_popup").toggle();
		event.preventDefault();

	});

	// image marker close
	jQuery(".annotation_popup_header a").click(function(event) {
		jQuery('.annotation_popup').hide();
		event.preventDefault();

	});



	jQuery( ".draggable" ).draggable({
	   containment: "#myimg",
	   stop: handleDragStop
	});



	function handleDragStop( ) {
	  var markerid = jQuery(this).attr('id');
	  var iconwidth = 16;

	  var w = jQuery("#myimg").width();
	  var h = jQuery("#myimg").height();

	  var l = jQuery(this).position().left + (iconwidth / 2);
	  var t = jQuery(this).position().top + (iconwidth / 2);

	  var percentleft = parseInt((l/w)*100);
	  var percenttop =  parseInt((t/h)*100);
	  markers[markerid] = percentleft+":"+percenttop;
	  jQuery('#_imganno_markers').val( JSON.stringify(markers));

	  var editingwindow = jQuery('#imganno-editbox');
	  editingwindow.css({top: t-210, left: l+20});
	}


       /**** IMAGE UPLOAD *******/

	jQuery( "#upload_annotated_image_button" ).click(function(event) {
		event.preventDefault();
		jQuery.fn.upload_annotation_image( jQuery(this) );
	});

	jQuery('#img_frame').on( 'click', '#remove_annotated_image_button', function( event ) {
		console.log('OK');
		event.preventDefault();
		jQuery( '#upload_annotation_image' ).val( '' );
		jQuery( '#img_frame img' ).attr( 'src', '' );
		jQuery( '#img_frame img' ).hide();
		  jQuery("#_imganno_img").val('');
		jQuery( this ).attr( 'id', 'upload_annotation_image_button' );
		jQuery( '#upload_annotation_image_button' ).text( 'Set annotated image' );
	});



	// Uploading files
	var file_frame;

	jQuery.fn.upload_annotation_image = function( button ) {

		var button_id = button.attr('id');
		var field_id = button_id.replace( '_button', '' );

		// If the media frame already exists, reopen it.
		if ( file_frame ) {
		  file_frame.open();
		  return;
		}

		// Create the media frame.
		file_frame = wp.media.frames.file_frame = wp.media({
		  title: jQuery( this ).data( 'uploader_title' ),
		  button: {
		    text: jQuery( this ).data( 'uploader_button_text' ),
		  },
		  multiple: false
		});

		// When an image is selected, run a callback.
		file_frame.on( 'select', function() {
		  var attachment = file_frame.state().get('selection').first().toJSON();
		  jQuery("#"+field_id).val(attachment.id);

		  jQuery("#img_frame img").attr('src',attachment.url);
		  jQuery("#_imganno_img").val(attachment.id);
		  jQuery( '#img_frame img' ).show();
		  jQuery( '#' + button_id ).attr( 'id', 'remove_annotation_image_button' );
		  jQuery( '#remove_annotation_image_button' ).text( 'Remove annotated image' );
		});

		// Finally, open the modal
		file_frame.open();
	};







});



