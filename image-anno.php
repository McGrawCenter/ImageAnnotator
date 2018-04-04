<?php
/*
Plugin Name: Princeton Image Annotator
Plugin URI: http://www.princeton.edu
Description:
Version: 1.0
Author: Ben Johnston
Author URI: http://www.princeton.edu
License: GPL2
*/



add_action('wp_enqueue_scripts','image_annotator_init');
add_action('admin_enqueue_scripts','image_annotator_init');


function image_annotator_init() {

    wp_enqueue_script('jquery-ui-draggable');
    wp_enqueue_script( 'image_annotator_js', plugins_url( '/js/image_annotator.js', __FILE__ ), array('jquery'));

    wp_enqueue_style( 'jquery-ui-css', "//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" );
    wp_enqueue_style( 'style-name', plugins_url( '/css/image_annotator.css', __FILE__ ) );
}





/***************************
*  SHORTCODE
***************************/
function annotated_image_shortcode( $atts ){
   global $post;

   $image_id = get_post_meta( $post->ID, '_imganno_img', true );
   $markers_data = get_post_meta($post->ID, "_imganno_markers", true);
   $display_type = get_post_meta($post->ID, "_imganno_display", true);
   if( empty($display_type) ) { $display_type = "popup"; }

   if ( ! empty( $image_id ) ) {
	$attachment_url = wp_get_attachment_url( $image_id );

	$html .= "<div id='img_frame' style='position:relative;display:inline-block' style='margin-top:20px;border:solid 1px green;'>";
	$html .= "<input type='hidden' name='_imganno_markers' id='_imganno_markers' value='{$markers_data}'/>";

	foreach(json_decode($markers_data) as $key=>$val) {
	  $id = $key;
	  $num = str_replace("m","",$key)+1;
	  $valArr = explode(":",$val);
	  $top = $valArr[0];
	  $left = $valArr[1];

	  $popup_top = $valArr[0]-20;
	  $popup_left = $valArr[1]-20;
	  $text = get_post_meta( $post->ID, '_'.$id.'_content', true );

	  $html .= "<div id='{$id}' class='imganno_marker' style='position:absolute;top:{$top}px;left:{$left}px;display:none'>{$num}</div>";
	  if($display_type == 'popup') {
	      $html .= "<div class='annotation_popup' id='{$id}_popup' style='display:none;position:absolute;'><div class='annotation_popup_header'><a href='#' style='text-decoration:none;'><span class='dashicons dashicons-no-alt'></span></a></div><div class='annotation_popup_content'>{$text}</div></div>";
	  } // end if display_type
	}

	

	$html .= "<img id='myimg' src='{$attachment_url}' style='width:100%' />";

	if($display_type == 'footnote') {

		foreach(json_decode($markers_data) as $key=>$val) {
		  $id = $key;
		  $num = str_replace("m","",$key)+1;
		  $text = get_post_meta( $post->ID, '_'.$id.'_content', true );
		  $html .= "<p  class='annotation_footnote' id='{$id}_footnote'>{$num}. {$text}</p>";
		}
	}

	$html .= "</div>";

   }

  echo  $html;

}
add_shortcode( 'annotated_image', 'annotated_image_shortcode' );




/***************************
*  SAVE POST
***************************/

add_action( 'save_post', 'imganno_meta_box_save' );


function imganno_meta_box_save($post_id) {

    // get out if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    $post_id = $_POST['post_ID'];

    if (array_key_exists('_imganno_markers', $_POST)) {
	$marker_data = $_POST['_imganno_markers'];
        update_post_meta(
            $post_id,
            '_imganno_markers',
            $marker_data
        );
    }

    if (array_key_exists('_imganno_img', $_POST)) {
	$image_id = $_POST['_imganno_img'];
        update_post_meta(
            $post_id,
            '_imganno_img',
            $image_id
        );
    }

    if (array_key_exists('_imganno_display', $_POST)) {
	$image_id = $_POST['_imganno_display'];
        update_post_meta(
            $post_id,
            '_imganno_display',
            $image_id
        );
    }




  //  update the content for the image annotation markers
  for($x=0;$x<=9;$x++) {
    if(isset($_POST['m'.$x.'_content'])) { 
        update_post_meta(
            $post_id,
            '_m'.$x.'_content',
            $_POST['m'.$x.'_content']
        );
    }
  }

}






/*******************************************
 * Adds a box to the main column on the Post and Page edit screens.
 *******************************************/
function ImgAnno_add_meta_box() {

	$screens = array( 'post', 'page' );
	foreach ( $screens as $screen ) {
		add_meta_box(
			'ImgAnno_addlink', 'Annotated Image',
			'ImgAnno_meta_box_content',
			$screen
		);
	}
}

/*******************************************
 * Inserts new, custom featured image input
 *******************************************/

function ImgAnno_book_cover_metabox ( $post ) { 

?>

<p>Add the following shortcode to the content area of this page to include the image and annotations: [annotated_image]</p>


<?php


	global $content_width, $_wp_additional_image_sizes;

	$image_id = get_post_meta( $post->ID, '_imganno_img', true );

	$markers_data = get_post_meta($post->ID, "_imganno_markers", true);

	if ( ! empty( $image_id ) ) {

		$attachment_url = wp_get_attachment_url( $image_id );

		if ( ! empty( $attachment_url ) ) {

			?>

<div id="img_frame" style="position:relative" style="margin-top:20px;border:solid 1px green;">

  <div id='m9' class='draggable'>10</div>
  <div id='m8' class='draggable'>9</div>
  <div id='m7' class='draggable'>8</div>
  <div id='m6' class='draggable'>7</div>
  <div id='m5' class='draggable'>6</div>
  <div id='m4' class='draggable'>5</div>
  <div id='m3' class='draggable'>4</div>
  <div id='m2' class='draggable'>3</div>
  <div id='m1' class='draggable'>2</div>
  <div id='m0' class='draggable'>1</div>

  <img id="myimg" src="<?php echo $attachment_url; ?>" style="width:500px" />

  <p class="hide-if-no-js"><a href="javascript:;" id="remove_annotated_image_button" ><?php echo esc_html__( 'Remove annotated image', 'text-domain' ); ?></a></p>
  <input type="hidden" id="upload_annotated_image" name="_listing_annotated_image" value="<?php echo esc_attr( $image_id ); ?>" />
<?php

		}

		$content_width = $old_content_width;
	} else {

		$content = '<img src="" style="width:' . esc_attr( $content_width ) . 'px;height:auto;border:0;display:none;" />';
		$content .= '<p class="hide-if-no-js"><a title="' . esc_attr__( 'Set annotated image', 'text-domain' ) . '" href="javascript:;" id="upload_annotated_image_button" id="set-listing-image" data-uploader_title="' . esc_attr__( 'Choose an image', 'text-domain' ) . '" data-uploader_button_text="' . esc_attr__( 'Set Annotated Image', 'text-domain' ) . '">' . esc_html__( 'Set annotated image', 'text-domain' ) . '</a></p>';
		$content .= '<input type="hidden" id="upload_annotated_image" name="annotated_image" value="" />';

	}
	$content .="</div>";
	return $content;
}





/********************************************
 * Prints out the contents of the metabox
 *******************************************/
function ImgAnno_meta_box_content() {

  global $post;

  echo ImgAnno_book_cover_metabox($post);

  $postID = $post->ID;
  $image_id = get_post_meta( $post->ID, '_imganno_img', true );
  $markers_data = get_post_meta($post->ID, "_imganno_markers", true);
  $display_type = get_post_meta($post->ID, "_imganno_display", true);
  ?>

<input type="hidden" name="_imganno_markers" id="_imganno_markers" value='<?php echo $markers_data; ?>'/>
<input type="hidden" name="_imganno_img" id="_imganno_img" value='<?php echo $image_id; ?>'/>

<input type="radio" name="_imganno_display" value="popup"<?php if($display_type=='popup'||empty( $display_type)) { echo 'checked="checked"';} ?> /> Show annotations as popups<br />
<input type="radio" name="_imganno_display" value="footnote"<?php if($display_type=='footnote') { echo 'checked="checked"';} ?>/>  Show annotations as footnotes 

<?php 
  if(!empty($image_id) && !empty($markers_data) ) {

  // add the text areas for each marker

  foreach(json_decode($markers_data) as $key=>$marker) {
    if($marker != '0:0') {
      $num = str_replace("m","",$key)+1;
      echo "<h4>Marker {$num}</h4>";
      wp_editor( htmlspecialchars_decode( get_post_meta($postID, '_'.$key.'_content' , true ) ), $key.'_content', $settings = array('textarea_name'=>$key.'_content','textarea_rows'=>2,'teeny'=>true) );
    }
  }
  } // end if isset image path
  ?>

<?php

}

add_action( 'add_meta_boxes', 'ImgAnno_add_meta_box' );









/***********************************
 * Shortcode
***********************************/

add_shortcode( '', 'img_anno_shortcode_content' );

function img_anno_shortcode_content($atts = [], $content = null)
{
    global $htmlcontent;
    $htmlcontent .= $content;

}

echo "{$htmlcontent}";

?>
