<?php

namespace PluginName\PublicArea;

use PluginName\Includes\Core\PostType;
use PluginName\Includes\Functions\Helpers\ContentHelper;
use PluginName\Includes\Settings\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class Frontend {
    public function filter_content( string $content ): string {
        if ( ! is_singular( PostType::PAGE ) || ! is_main_query() || ! in_the_loop() ) {
            return $content;
        }

        $classes = [ 'pluginname-page' ];
        if ( Settings::get_key( 'sidebar_position', 'left' ) === 'right' ) {
            $classes[] = 'pluginname-sidebar-right';
        }
        $parts = [ '<article class="' . esc_attr( implode( ' ', $classes ) ) . '">' ];
        if ( Settings::get_bool( 'show_search', true ) ) {
            $parts[] = $this->render_search_form();
        }
        if ( Settings::get_bool( 'page_show_title', true ) ) {
            $parts[] = '<h1 class="pluginname-page-title">' . esc_html( get_the_title() ) . '</h1>';
        }

        if ( Settings::get_bool( 'show_breadcrumbs', true ) ) {
            $parts[] = $this->render_breadcrumbs();
        }

        if ( Settings::get_bool( 'show_reading_time', false ) ) {
            $minutes = ContentHelper::reading_time( $content, Settings::get_int( 'reading_time_wpm', 200 ) );
            /* translators: %d is the estimated reading time in minutes. */
            $parts[] = '<p class="pluginname-reading-time">' . esc_html( sprintf( esc_html__( '%d min read', 'pluginname' ), $minutes ) ) . '</p>';
        }

        if ( Settings::get_bool( 'show_last_updated', true ) || Settings::get_bool( 'show_author', false ) ) {
            $parts[] = $this->render_page_meta();
        }
        $parts[] = '<div class="pluginname-content">' . $content . '</div>';
        $parts[] = '</article>';

        return (string) apply_filters( 'pluginname_frontend_content', implode( '', $parts ), $content );
    }

    public function body_classes( array $classes ): array {
        if ( is_singular( PostType::PAGE ) ) {
            $classes[] = 'pluginname-page-template';
            if ( Settings::get_bool( 'show_search', true ) ) {
                $classes[] = 'pluginname-search-enabled';
            }
        }

        return array_values( array_unique( array_map( 'sanitize_html_class', $classes ) ) );
    }

    public function render_search_form(): string {
        $form = '<form class="pluginname-search" method="get" action="' . esc_url( home_url( '/' ) ) . '">';
        $form .= '<label class="screen-reader-text" for="pluginname-search-input">' . esc_html__( 'Search PluginName', 'pluginname' ) . '</label>';
        $form .= '<input id="pluginname-search-input" name="s" type="search" minlength="' . esc_attr( (string) Settings::get_int( 'search_min_chars', 2 ) ) . '" placeholder="' . esc_attr( Settings::get_string( 'search_placeholder', __( 'Search the Wiki', 'pluginname' ) ) ) . '">';
        $form .= '<button type="submit">' . esc_html( Settings::get_string( 'search_button_text', __( 'Search', 'pluginname' ) ) ) . '</button>';
        $form .= '</form>';

        return (string) apply_filters( 'pluginname_search_form', $form );
    }

    private function render_breadcrumbs(): string {
        $items = [ '<a href="' . esc_url( get_post_type_archive_link( PostType::PAGE ) ?: home_url( '/' ) ) . '">' . esc_html( Settings::get_string( 'root_name', __( 'PluginName', 'pluginname' ) ) ) . '</a>' ];
        $ancestors = array_reverse( get_post_ancestors( get_the_ID() ) );
        foreach ( $ancestors as $ancestor ) {
            $items[] = '<a href="' . esc_url( get_permalink( $ancestor ) ) . '">' . esc_html( get_the_title( $ancestor ) ) . '</a>';
        }
        $items[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';

        return '<nav class="pluginname-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumbs', 'pluginname' ) . '"><ol><li>' . implode( '</li><li>', $items ) . '</li></ol></nav>';
    }

    private function render_page_meta(): string {
        $items = [];
        if ( Settings::get_bool( 'show_last_updated', true ) ) {
            /* translators: %s is the date the Wiki page was last updated. */
            $items[] = sprintf( esc_html__( 'Updated %s', 'pluginname' ), esc_html( get_the_modified_date() ) );
        }
        if ( Settings::get_bool( 'show_author', false ) ) {
            /* translators: %s is the author name. */
            $items[] = sprintf( esc_html__( 'by %s', 'pluginname' ), esc_html( get_the_author() ) );
        }

        return '<p class="pluginname-page-meta">' . implode( ' &middot; ', $items ) . '</p>';
    }
}
