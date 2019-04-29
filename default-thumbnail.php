<?php

/**
 Plugin Name: Default thumbnail
 Plugin URI: http://github.com/benignware/wp-default-thumbnail
 Description: Placeholder images
 Version: 0.0.1
 Author: Rafael Nowrotek, Benignware
 Author URI: http://benignware.com
 License: MIT
*/

require_once 'lib/settings.php';

/**
 * Show a placeholder image with empty thumbnails
 */

add_filter( 'post_thumbnail_html', function($html, $post_id = null, $post_thumbnail_id = null, $size = null, $attr = array()) {
  global $_wp_additional_image_sizes;

  $post_thumbnail_id = $post_thumbnail_id ?: get_post_thumbnail_id($post_id);

  $options = get_option('default_thumbnail_options');

  // Parameters
  $min_height = $options['min_height'] || 230;
  $max_height = $options['max_height'] || 800;

  // Implementation
  $attr = is_array($attr) ? $attr : array();
  $file = null;
  $width = 0;
  $height = 0;

  $is_regular = false;
  $is_broken = false;
  $is_empty = false;

  if ($size) {
    $sizes = get_intermediate_image_sizes();
    foreach ( $sizes as $_size ) {
      if ($_size === $size) {
        if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
          $width  = get_option( "{$_size}_size_w" );
          $height = get_option( "{$_size}_size_h" );
        } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
          $width = $_wp_additional_image_sizes[ $_size ]['width'];
          $height = $_wp_additional_image_sizes[ $_size ]['height'];
        }
      }
    }
  }

  if ($post_id && $size) {
    $thumb = wp_get_attachment_image_src($post_thumbnail_id, $size);

    if ($thumb) {
      // Check if file exists
      $url = $thumb[0];
      $width = $thumb[1] ? $thumb[1] : $width;
      $height = $thumb[2] ? $thumb[2] : $height;
      $filename = parse_url($url, PHP_URL_PATH);

      if ($filename) {
        $file = $_SERVER['DOCUMENT_ROOT'] . "/" . $filename;
        if (file_exists($file)) {
          // Regular image
          $is_regular = true;
        } else {
          $is_broken = true;
        }
      }
    } else {
      $is_empty = true;
    }
  }

  if ($is_regular && !$options['regular']) {
    return $html;
  }

  if ($is_broken && !$options['broken']) {
    return $html;
  }

  if ($is_empty && !$options['empty']) {
    return $html;
  }

  if ($height === 9999) {
    // Thumbnail format allows for arbitrary heights, so we provide something random...
    $height = rand($min_height, $max_height);
  }

  // Service template
  $service = $options['service'] ? sprintf($options['service'], $width, $height) : null;

  // $default_image = get_template_directory_uri() . '/svg/logo.svg';
  // $default_image = get_header_image();

  $src = $service;

  $attr = array_merge(array(
    //'src' => $default_image,
    'src' => $src,
    //'src' => null,
    'class' => 'default-thumbnail',
    'style' => array(
      /*'background' => '#efefef',
      'font-family' => 'Arial',
      'color' => '#cdcdcd',
      'font-size' => '22px'*/
    ),
    /*'width' => $width,
    'height' => $height
    */
  ), $attr);

  $attr = apply_filters('default_thumbnail_atts', $attr);

  $style = $attr['style'];

  $svg_attr = array(
    'style' => $style,
    'width' => $width,
    'height' => $height
  );

  array_walk($svg_attr['style'], function(&$value, $key) { $value = "$key: $value"; });
  $svg_attr['style'] = implode('; ', $svg_attr['style']);

  $src = $attr['src'];

  if (!$src) {
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 $width $height'";
    foreach ($svg_attr as $key => $value) {
      $svg.= " $key='" . $value . "'";
    }
    $svg.= ">";
    $svg.= "<text x='" . $width/2 . "px' y='" . $height/2 . "px' text-anchor='middle' alignment-baseline='central'>$width x $height</text>";
    $svg.= "</svg>";
  }

  $extension = pathinfo($src, PATHINFO_EXTENSION);
  if ($extension === 'svg') {
    $filename = parse_url($src, PHP_URL_PATH);
    if ($filename) {
      $file = $_SERVER['DOCUMENT_ROOT'] . $filename;
      if (file_exists($file)) {
        $svg = file_get_contents($file);
        if ($svg) {
          $svg_dom = new DOMDocument();
          $svg_dom->loadXML($svg);
          foreach ($svg_attr as $key => $value) {
            $svg_dom->documentElement->setAttribute($key, $value);
          }
          $svg = $svg_dom->saveXML();
        }
      }
    }
  }

  if ($svg) {
    $attr['src'] = "data:image/svg+xml;utf8," . rawurlencode($svg);
  }

  $html = "<img";
  foreach ($attr as $key => $value) {
    if ($key == 'style') {
      array_walk($value, function(&$value, $key) { $value = "$key: $value"; });
      $value = implode("; ", array_values($value));
    }
    $html.= " $key=\"$value\"";
  }

  $html.= "/>";
  return $html;

  return '<img src="' . $placeholder_src . '"  class="' . $class_name . '" width="' . $width . 'px" height="' . $height . 'px"/>';
}, 1, 5 );


add_filter( 'get_post_metadata', function( $result, $object_id, $meta_key, $single ) {
  if ( '_thumbnail_id' === $meta_key) {
    // Plugin takes care of it
    // return -1;
  }

  return $result;
}, 10, 4 );

?>
