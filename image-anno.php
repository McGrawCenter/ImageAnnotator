<?php
/*
	Plugin Name: Princeton Simple Image Annotation
	Plugin URI:
	Description: Allow annotations to be placed on featured images by dragging a marker and adding an annotation.
	Version: 1.0
	Author: Ben Johnston
*/




function pusia_add_scripts() {
    global $post;
    wp_enqueue_script("jquery-ui-draggable");

  if(is_user_logged_in()) {
    wp_register_script('pusia-js', plugins_url('js/pusia.js', __FILE__), array('jquery'),'1.1', true);
    wp_enqueue_script('pusia-js');
    wp_localize_script('pusia-js', 'scriptData', array('postid' => $post->ID, 'pluginsUrl' => plugins_url()));

    wp_register_style('pusia-css', plugins_url('css/pusia.css',__FILE__ ));
    wp_enqueue_style('pusia-css');

  }
}
add_action( 'wp_enqueue_scripts', 'pusia_add_scripts' );  






/******************************************
* shortcode for inserting featured image
******************************************/


/*
function pusia_shortcode( $atts ){
  global $post;

  if(isset($atts['display']) && $atts['display']=='list') {  $display = 'list';  } else { $display = 'bubble'; }

  if(!isset($atts['image'])) {
	  if ( has_post_thumbnail() ) {
	    $thumb_id = get_post_thumbnail_id();
	    $thumb_url = wp_get_attachment_image_src($thumb_id,'thumbnail-size', true);
	    $img = $thumb_url[0];


	    $html = "<div id='pu_simple_image_annotation_frame'>";
	    $html .= "<div id='pu_simple_image_annotation_toolbar'></div>";
	    //$html .= "<div class='pu_simple_image_annotation_marker draggable' ><span class='dashicons dashicons-edit imganno-marker' ></span></div>";
	    $html .= "<span class='dashicons dashicons-location imganno-marker draggable' title='drag this marker to create an annotation' style='color:lightgreen' ></span>";
	    $html .= "<img src='".$img."' style='width:100%;'/>";
	    $html .= pusia_toolbar();
	    $html .= pusia_add_markers();
	    $html .= pusia_add_bubbles();
	    $html .= "</div>";

	    return $html;
	  }
	  else { return "Featured image must be set."; }
  }
  else { // image url is indicated in atts
	    $img = $atts['image'];
	    $html = "<div id='pu_simple_image_annotation_frame' >";
	    //$html .= "<div id='pu_simple_image_annotation_toolbar' style='width:45px;height:45px; background:white;position:absolute;top:5px;left:5px;'></div>";
	    $html .= "<span class='dashicons dashicons-location imganno-marker draggable' title='drag this marker to create an annotation' style='color:lightgreen' ></span>";

	    $html .= "<img src='".$img."' style='width:100%;'/>";
	    $html .= pusia_toolbar();
	    $html .= pusia_add_markers();

	    if($display == 'bubble') {
	      $html .= pusia_add_bubbles();
	    }
	    $html .= "</div>";
	    if($display == 'list') {
	      $html .= pusia_add_list();
	    }
	    return $html;
  }
}
add_shortcode( 'simple_annotated_image', 'pusia_shortcode' );

*/





function pusia_content_filter($content) {


  if(is_single() && has_category('annotation')) {


	  global $post;

	  if(isset($atts['display']) && $atts['display']=='list') {  $display = 'list';  } else { $display = 'bubble'; }

	  if(!isset($atts['image'])) {
		  if ( has_post_thumbnail() ) {
		    $thumb_id = get_post_thumbnail_id();
		    $thumb_url = wp_get_attachment_image_src($thumb_id,'thumbnail-size', true);
		    $img = $thumb_url[0];


		    $html = "<div id='pu_simple_image_annotation_frame'>";
		    $html .= "<div id='pu_simple_image_annotation_toolbar'></div>";
		    //$html .= "<div class='pu_simple_image_annotation_marker draggable' ><span class='dashicons dashicons-edit imganno-marker' ></span></div>";
		    $html .= "<span class='dashicons dashicons-location imganno-marker draggable' title='drag this marker to create an annotation' style='color:lightgreen' ></span>";
		    $html .= "<img src='".$img."' style='width:100%;'/>";
		    $html .= pusia_toolbar();
		    $html .= pusia_add_markers();
		    $html .= pusia_add_bubbles();
		    $html .= "</div>";

		    $content = $html.$content;
		  }

		  else { return "Featured image must be set."; }
	  }
	  else { // image url is indicated in atts
		    $img = $atts['image'];
		    $html = "<div id='pu_simple_image_annotation_frame' >";
		    //$html .= "<div id='pu_simple_image_annotation_toolbar' style='width:45px;height:45px; background:white;position:absolute;top:5px;left:5px;'></div>";
		    $html .= "<span class='dashicons dashicons-location imganno-marker draggable' title='drag this marker to create an annotation' style='color:lightgreen' ></span>";

		    $html .= "<img src='".$img."' style='width:100%;'/>";
		    $html .= pusia_toolbar();
		    $html .= pusia_add_markers();

		    if($display == 'bubble') {
		      $html .= pusia_add_bubbles();
		    }
		    $html .= "</div>";
		    if($display == 'list') {
		      $html .= pusia_add_list();
		    }
		    $content = $html.$content;
	  }

  }
return $content;
}

add_filter( 'the_content', 'pusia_content_filter' );








/******************************************
* add markers to the image
******************************************/

function pusia_add_markers() {
  global $post;

  $annotations = get_post_meta($post->ID,'img_annotation');
  $html = "";
  foreach($annotations as $anno) {
    $a = json_decode($anno);
    //$html .= "<div rel ='anno{$a->id}' class='pu_simple_image_annotation_marker gloss' style='top:{$a->top}%;left:{$a->left}%;'><span class='dashicons dashicons-location imganno-marker'></span></div>\n";
    $html .= "<span class='dashicons dashicons-location imganno-marker' style='top:{$a->top}%;left:{$a->left}%;'rel ='anno{$a->id}'></span>\n";
  }
  return $html;
}


/******************************************
* add bubbles to the image
******************************************/

function pusia_add_bubbles() {
  global $post;
  global $current_user;
  get_currentuserinfo();

  if(( is_user_logged_in() && $current_user->ID == $post->post_author ) || current_user_can('administrator')) {
    $trashcan = true;
  }

  $html = "";
  if($annotations = get_post_meta($post->ID,'img_annotation')) {

    foreach($annotations as $anno) {
      $a = json_decode($anno);
      $a->annotation = str_replace("**BR**","<br />",$a->annotation);
      $a->annotation = str_replace("**APOS**","&apos;",$a->annotation);
      $a->annotation = str_replace("**QUOT**","&quot;",$a->annotation);

      $user = get_userdata($a->userid);
      $html .= "<div class='pu-simple-annotation-bubble' id='anno{$a->id}'>";
      if($trashcan) {
        $html .= "<div class='pu-simple-annotation-bubble-nav'><span style='font-size:1.3em;margin-right:0px;margin-bottom: 10px;' class='dashicons dashicons-trash pu-simple-annotation-bubble-trash' rel='anno{$a->id}' postid='{$post->ID}'></span></div>";
      }
      $html .= "<div class='pu-simple-annotation-list-meta'>{$user->user_login} on {$a->date}</div>";
      $html .= "<div class='pu-simple-annotation-list-body'>{$a->annotation}</div>";
      $html .= "</div>";
    }
  }
  return $html;
}



/******************************************
* add list below the image
******************************************/

function pusia_add_list() {
  global $post;
  $html = "";
  if($annotations = get_post_meta($post->ID,'img_annotation')) {
    $html .= "<div class='pu-simple-annotation-list' style='margin:10px 0 30px 0;'>";
    foreach($annotations as $anno) {
      $a = json_decode($anno);

      $user = get_userdata($a->userid);
      $html .= "<div class='pu-simple-annotation-list-item' id='list{$a->id}' style='display:none;'>";
      $html .= "<div class='pu-simple-annotation-list-meta'>{$user->user_login} on {$a->date}</div>";
      $html .= "<div class='pu-simple-annotation-list-body'>{$a->annotation}</div>";
      $html .= "</div>";
    }
    $html .= "</div>";
  }
  return $html;
}


/******************************************
* add annotation form
******************************************/

function pusia_toolbar() {
  global $post;
  $html = "";
  $html .= "<div id='simple_image_annotation-toolbar' style='top:0px;left:0px;display:none;'>";
  $html .= " <div id='simple_image_annotation-toolbar-nav'><span id='simple_image_annotation-toolbar-close' style='font-size:1.3em;margin-right:0px;margin-bottom: 10px;' class='dashicons dashicons-dismiss'></span></div>";

  // the form
  $html .= "<form name='pusia-anno' id='pusia-anno' method='POST' action='' style='margin-bottom:0px;'>";
  $html .= "  <input id='pusia-postid' type='hidden' name='postid' value='{$post->ID}'/>";
  $html .= "  <input id='pusia-left' type='hidden' name='left'/>";
  $html .= "  <input id='pusia-top' type='hidden' name='top'/>";
  $html .= "  <textarea name='annotation' id='annotation' style='height:120px;width:100%' required></textarea>";
  $html .= "  <input type='submit' style='margin-top:5px;width:100%;height:40px;background:#333;color:white;' value='Add'/>";
  $html .= "</form>";
  // end the form

  $html .= "</div>";

  return $html;
}




/******************************************
* process form input
******************************************/

add_action( 'init', 'sprachpraxis_process_post' );

function sprachpraxis_process_post() {

     if( isset( $_POST['annotation'] ) ) {

	  $postid = $_POST['postid'];

	  $anno = new StdClass();


	  $anno->id = str_pad(mt_rand(1,99999999),8,'0',STR_PAD_LEFT);
	  // add current user id
	  $anno->userid = get_current_user_id();
	  // add current date to post data
	  $anno->date = date("Y/m/d");

	  $anno->top = $_POST['top'];
	  $anno->left = $_POST['left'];

	  $annotation = $_POST['annotation'];
	  $annotation = str_replace("\r\n","**BR**",$annotation);
	  $annotation = str_replace("\r","**BR**",$annotation);
	  $annotation = str_replace("\n","**BR**",$annotation);
	  $annotation = str_replace("'","**APOS**",$annotation);
	  $annotation = str_replace('"',"**QUOT**",$annotation);
	  //$annotation = nl2br($_POST['annotation']);

	  // $annotation  = str_replace("<br />","**BR**",$annotation);
	  //$annotation  = str_replace("\r","",$annotation);
	  // $annotation  = str_replace("\n","",$annotation);
	  //$anno->annotation = htmlspecialchars(addslashes($annotation), ENT_QUOTES);
	  $anno->annotation = stripslashes($annotation);
	  $data = json_encode($anno, JSON_UNESCAPED_UNICODE);

	  add_post_meta( $postid, 'img_annotation', $data );

     } // end if


     if( isset( $_POST['action'] ) && $_POST['action'] == 'remove' ) {
	$postid = $_POST['pid'];
	$rel = str_replace('anno','',$_POST['rel']);

	$annos = get_post_meta($postid, 'img_annotation');
	foreach($annos as $anno) {
	  $c = json_decode($anno);
	  if($c->id == $rel) { delete_post_meta($postid, 'img_annotation', $anno); }
	  $arr = array('success'=>'1');
	  echo json_encode($arr);
	  die();
	  wp_die();
	}

     } // end if


}





