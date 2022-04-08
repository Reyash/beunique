<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Output Post Comments
 */

if ( is_admin() AND ( ! defined( 'DOING_AJAX' ) OR ! DOING_AJAX ) ) {
	return;
}

global $us_grid_object_type;

// Cases when the Comments shouldn't be shown
if ( $us_elm_context == 'grid' AND $us_grid_object_type == 'term' ) {
	return;
} elseif ( $us_elm_context == 'shortcode' AND is_archive() ) {
	return;
} elseif ( get_post_format() == 'link' ) {
	return;
} elseif ( ! comments_open() AND ! usb_is_preview_page() ) {
	return;
}

// Exclude 'comments_template' layout for Grid context
if ( $us_elm_context != 'shortcode' ) {
	$layout = 'amount';
}

$_atts['class'] = 'w-post-elm post_comments';
$_atts['class'] .= isset( $classes ) ? $classes : '';
$_atts['class'] .= ' layout_' . $layout;

if ( ! empty( $el_id ) AND $us_elm_context == 'shortcode' ) {
	$_atts['id'] = $el_id;
}

if ( $layout == 'amount' ) {

	$link_atts = array( 'class' => 'smooth-scroll' );

	// Link
	if ( $link === 'post' ) {
		if ( get_post_type() == 'product' ) {
			$link_atts['href'] = apply_filters( 'the_permalink', get_permalink() ) . '#reviews';
		} else {
			$link_atts['href'] = get_comments_link();
		}
	} elseif ( $link === 'custom' ) {
		$link_atts = us_generate_link_atts( $custom_link );
	}

	if ( $color_link ) {
		$_atts['class'] .= ' color_link_inherit';
	}

	// Define no comments indication
	$comments_none = '0';
	if ( ! $number ) {
		$_atts['class'] .= ' with_word';
		$comments_none = us_translate( 'No Comments' );
	}

	$comments_number = get_comments_number();

	// "Hide this element if no comments"
	if ( $hide_zero AND empty( $comments_number ) ) {

		// Output empty container for Live Builder
		if ( usb_is_preview_page() ) {
			echo '<div class="w-post-elm"></div>';
		}
		return;
	}
}

// Output the element
$output = '<div' . us_implode_atts( $_atts ) . '>';

if ( $layout == 'comments_template' ) {
	if ( ! us_amp() ) {
		wp_enqueue_script( 'comment-reply' );
	}

	ob_start();
	comments_template();
	$output .= ob_get_clean();

} else {
	if ( ! empty( $icon ) ) {
		$output .= us_prepare_icon_tag( $icon );
	}
	if ( ! empty( $link_atts['href'] ) ) {
		$output .= '<a' . us_implode_atts( $link_atts ) . '>';
	}

	if ( class_exists( 'woocommerce' ) AND get_post_type() == 'product' ) {

		// "screen-reader-text" is needed for working "Show only number" option
		$output .= sprintf( us_translate_n( '%s customer review', '%s customer reviews', $comments_number, 'woocommerce' ), '<span class="count">' . strip_tags( $comments_number ) . '</span><span class="screen-reader-text">' );
		$output .= '</span>';
	} else {
		ob_start();
		$comments_label = sprintf( us_translate_n( '%s <span class="screen-reader-text">Comment</span>', '%s <span class="screen-reader-text">Comments</span>', $comments_number ), $comments_number );
		comments_number( $comments_none, $comments_label, $comments_label );
		$output .= ob_get_clean();
	}

	if ( ! empty( $link_atts['href'] ) ) {
		$output .= '</a>';
	}
}

$output .= '</div>';

echo $output;
