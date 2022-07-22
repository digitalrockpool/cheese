<?php

//*** Enqueue script and styles for child theme
function child_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri().'/style.css' );
}
add_action( 'wp_enqueue_scripts', 'child_enqueue_styles' );

//*** Add includes
// require_once get_stylesheet_directory().'/inc/gravityforms.php';


//*** Add pricing prefix and suffix
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
function woocommerce_product_custom_fields() {

	global $woocommerce, $post;

  woocommerce_wp_text_input(
    array(
      'id' => '_custom_price_prefix',
      'placeholder' => '',
      'label' => __('Price Prefix', 'woocommerce'),
      'desc_tip' => 'true'
    )
  );

	woocommerce_wp_text_input(
        array(
            'id' => '_custom_price_suffix',
            'placeholder' => '',
            'label' => __('Price Suffix', 'woocommerce'),
            'desc_tip' => 'true'
        )
    );

	woocommerce_wp_text_input(
        array(
            'id' => '_custom_price_override',
            'placeholder' => '',
            'label' => __('Price Override', 'woocommerce'),
            'desc_tip' => 'true'
        )
    );
}

add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');
function woocommerce_product_custom_fields_save($post_id) {

  $woocommerce_product_price_prefix= $_POST['_custom_price_prefix'];
	update_post_meta($post_id, '_custom_price_prefix', esc_attr($woocommerce_product_price_prefix));

	$woocommerce_product_price_suffix = $_POST['_custom_price_suffix'];
  update_post_meta($post_id, '_custom_price_suffix', esc_attr($woocommerce_product_price_suffix));

	$woocommerce_product_price_override = $_POST['_custom_price_override'];
  update_post_meta($post_id, '_custom_price_override', $woocommerce_product_price_override);

}

add_filter( 'woocommerce_get_price_html', 'add_price_prefix', 99, 2 );
function add_price_prefix( $price, $product ){

	$prefix = get_post_meta( get_the_ID(), '_custom_price_prefix', true);
	$override = get_post_meta( get_the_ID(), '_custom_price_override', true);
  $price = $prefix.' '.$price;

	if( $override ) : return $override; else : return $price; endif;
}

add_filter( 'woocommerce_get_price_suffix', 'add_price_suffix', 99, 4 );
function add_price_suffix( $html, $product, $price, $qty ){

	$suffix = get_post_meta( get_the_ID(), '_custom_price_suffix', true);
	$html .= ' '.$suffix;
    return $html;
}



//*** Add plus minus to quantity
add_action( 'woocommerce_after_add_to_cart_quantity', 'woo_quantity_plus_sign' );
function woo_quantity_plus_sign() {
  echo '<button type="button" class="woo-quanitiy-plus" ><i class="fas fa-plus"></i></button>';
}

add_action( 'woocommerce_before_add_to_cart_quantity', 'woo_quantity_minus_sign' );
function woo_quantity_minus_sign() {
  echo '<button type="button" class="woo-quanitiy-minus" ><i class="fas fa-minus"></i></button>';
}

add_action( 'wp_footer', 'woo_quantity_plus_minus' );
function woo_quantity_plus_minus() {

  if( is_product() || is_shop() ) ?>
    <script type="text/javascript">
      jQuery(document).ready(function($){

        $('form.cart').on( 'click', 'button.woo-quanitiy-plus, button.woo-quanitiy-minus', function() {

          // Get current quantity values
          var qty = $( this ).closest( 'form.cart' ).find( '.qty' );
          var val = parseFloat(qty.val());
          var max = parseFloat(qty.attr( 'max' ));
          var min = parseFloat(qty.attr( 'min' ));
          var step = parseFloat(qty.attr( 'step' ));

          // Change the value if plus or minus
          if ( $( this ).is( '.woo-quanitiy-plus' ) ) {
            if ( max && ( max <= val ) ) {
              qty.val( max );
            }
            else {
              qty.val( val + step );
            }
          }
          else {
            if ( min && ( min >= val ) ) {
              qty.val( min );
            }
            else if ( val > 1 ) {
              qty.val( val - step );
            }
          }

        });
      });
  </script> <?php
}

//*** Register Sidebars
add_action( 'widgets_init', 'product_filters_sidebar' );
function product_filters_sidebar() {
  $args = array(
    'name'          => 'Product Filters',
    'id'            => 'product-filters-sidebar',
    'description'   => '',
    'class'         => '',
    'before_widget' => '<li id="%1$s" class="widget %2$s">',
    'after_widget'  => '</li>',
    'before_title'  => '<h5 class="widgettitle">',
    'after_title'   => '</h5>'
  );

  register_sidebar( $args );

}

/* add_filter( 'woocommerce_add_to_cart_redirect', 'wp_get_referer' );

add_filter( 'woocommerce_add_to_cart_redirect', 'custom_redirect_function' );
    function custom_redirect_function() {
    return get_permalink( wc_get_page_id( 'shop' ) );
} */

//*** Remove product links and thumbnails from basket
add_filter( 'woocommerce_cart_item_permalink', '__return_null' );
add_filter( 'woocommerce_cart_item_thumbnail', '__return_false' );

//*** Remove shipping weight from products
add_filter( 'wc_product_enable_dimensions_display', '__return_false' );
