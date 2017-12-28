<?php
/*
Plugin Name: GP Cart Menu
Plugin URI: https://github.com/WestCoastDigital/GP-Cart-Menu
Description: Add cart total and quantity to the GP Premium WooCommerce Cart Icon function
Version: 0.1.0
Author: Jon Mather
Author URI: https://github.com/WestCoastDigital
Text Domain: generatepress
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function register_my_scripts(){
    wp_enqueue_style( 'gp-cart', plugins_url( 'style.css' , __FILE__ ) );
}
add_action('wp_enqueue_scripts','register_my_scripts');

function gpcommerce_cart_link() {
    ob_start();
	global $woocommerce;
	$viewing_cart = __('View your shopping cart', 'generatepress');
	$start_shopping = __('Start shopping', 'generatepress');
	$cart_url = $woocommerce->cart->get_cart_url();
	$shop_page_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
	$cart_contents = sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'generatepress'), $woocommerce->cart->cart_contents_count);
    $cart_total = $woocommerce->cart->get_cart_total();
    $icon = ( ! apply_filters( 'generate_fontawesome_essentials', false ) ) ? apply_filters( 'generate_woocommerce_menu_cart_icon', '<i class="fa fa-shopping-cart" aria-hidden="true"></i>' ) : __( 'Cart', 'generate-woocommerce' ); 

    $menu_item = '<li class="wc-menu-item">';
    $menu_item .= '<a class="cart-contents" href="'. $cart_url .'" title="'. $viewing_cart .'">';
    $menu_item .= $icon . ' ';
    $menu_item .= '<span class="count">';
    $menu_item .= $cart_contents;
    $menu_item .= '</span>';
    $menu_item .= ' - ';
    $menu_item .= '<span class="amount">';
    $menu_item .= $cart_total;
    $menu_item .= '</span>';
    $menu_item .= '</a>';
    $menu_item .= '</li>';

	echo $menu_item;
	$social = ob_get_clean();
	return $menu . $social;
}

function gpcommerce_cart_menu_item( $nav, $args ) {
    if ( $args->theme_location == 'primary' && generatepress_wc_get_setting( 'cart_menu_item' ) ) {
        return sprintf( 
            '%1$s 
            <li class="wc-menu-item %4$s" title="%2$s">
                %3$s
            </li>',
            $nav,
            esc_attr__( 'View your shopping cart','generate-woocommerce' ),
            gpcommerce_cart_link(),
            is_cart() ? 'current-menu-item' : ''
        );
    }
    return $nav;
}

function gpcommerce_mobile_cart_link() {
	if ( function_exists( 'generatepress_wc_get_setting' ) && ! generatepress_wc_get_setting( 'cart_menu_item' ) ) {
		return;
	}
	?>
	<div class="mobile-bar-items wc-mobile-cart-items">
		<?php do_action( 'generate_mobile_cart_items' ); ?>
		<?php echo gpcommerce_cart_link(); ?>
	</div><!-- .mobile-bar-items -->
	<?php

}

function gpcommerce_ajax_fragment( $fragments ) {
    global $woocommerce;
    $cart_contents = sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'generatepress'), $woocommerce->cart->cart_contents_count);
    
	$fragments['.cart-contents span.count'] = '<span class="count">' . $cart_contents . '</span>';

	$fragments['.cart-contents span.amount'] = ( WC()->cart->subtotal > 0 ) ? '<span class="amount">' . wp_kses_data( WC()->cart->get_cart_subtotal() ) . '</span>' : '<span class="amount"></span>';

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'gpcommerce_ajax_fragment' );

function gpcommerce_update_gp_function() {
    remove_filter( 'wp_nav_menu_items','generatepress_wc_menu_cart', 10, 2 );
    add_filter( 'wp_nav_menu_items','gpcommerce_cart_menu_item', 10, 2 );

    remove_action( 'generate_inside_navigation','generatepress_wc_mobile_cart_link' );
    remove_action( 'generate_inside_mobile_header','generatepress_wc_mobile_cart_link' );

    add_action( 'generate_inside_navigation','gpcommerce_mobile_cart_link' );
    add_action( 'generate_inside_mobile_header','gpcommerce_mobile_cart_link' );
}
add_action( 'after_setup_theme','gpcommerce_update_gp_function' );