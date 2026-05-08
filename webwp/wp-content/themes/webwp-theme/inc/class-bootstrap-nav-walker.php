<?php
/**
 * Minimal Bootstrap 5 nav walker for WordPress menus.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WebWP_Bootstrap_Nav_Walker extends Walker_Nav_Menu {

    public function start_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '<ul class="dropdown-menu">';
    }

    public function end_lvl( &$output, $depth = 0, $args = null ) {
        $output .= '</ul>';
    }

    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $has_children = in_array( 'menu-item-has-children', (array) $item->classes, true );
        $li_classes   = [ 'nav-item' ];
        if ( $depth === 0 && $has_children ) $li_classes[] = 'dropdown';
        if ( in_array( 'current-menu-item', (array) $item->classes, true ) ) $li_classes[] = 'active';

        $a_classes = [ $depth === 0 ? 'nav-link' : 'dropdown-item' ];
        if ( $depth === 0 && $has_children ) $a_classes[] = 'dropdown-toggle';

        $output .= sprintf(
            '<li class="%s"><a href="%s" class="%s"%s>%s</a>',
            esc_attr( implode( ' ', $li_classes ) ),
            esc_url( $item->url ),
            esc_attr( implode( ' ', $a_classes ) ),
            $has_children && $depth === 0 ? ' data-bs-toggle="dropdown" aria-expanded="false"' : '',
            esc_html( $item->title )
        );
    }

    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= '</li>';
    }
}
