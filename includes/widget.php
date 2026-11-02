<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Calico_Drilldown_Navigation_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'calico-drilldown-navigation'; }
    public function get_title() { return esc_html__( 'Calico Drill Down Menu for Elementor', 'calico-drilldown-navigation' ); }
    public function get_icon() { return 'eicon-nav-menu'; }
    public function get_categories() { return array( 'general' ); }
    public function get_keywords() { return array( 'menu', 'navigation', 'drilldown', 'dropdown', 'calico' ); }
    public function get_script_depends() { return array( 'calico-drilldown-navigation' ); }
    public function get_style_depends() { return array( 'calico-drilldown-navigation' ); }
    protected function register_controls() {
        $menus = array(); $items = array();
        foreach ( wp_get_nav_menus() as $menu ) {
            $menus[ $menu->term_id ] = $menu->name;
            foreach ( (array) wp_get_nav_menu_items( $menu->term_id ) as $item ) {
                $items[ $item->ID ] = $menu->name . ' — ' . $item->title . ' (#' . $item->ID . ')';
            }
        }
        $this->start_controls_section( 'content', array( 'label' => 'Menu & behavior' ) );
        $this->add_control( 'menu', array( 'label' => 'WordPress menu', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => $menus, 'default' => key( $menus ) ) );
        $this->add_control( 'label', array( 'label' => 'Accessible navigation label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Main navigation' ) );
        $this->add_control( 'open_on', array( 'label' => 'Desktop dropdown trigger', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => array( 'click' => 'Click', 'hover' => 'Hover and click' ), 'default' => 'click' ) );
        $this->add_control( 'show_top_arrows', array( 'label' => 'Show top-level arrows', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'description' => 'Hide arrows beside top-level labels while retaining full-row submenu buttons and deeper navigation arrows.' ) );
        $this->add_control( 'collapse', array( 'label' => 'Collapse to mobile menu', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'custom', 'options' => array( 'phone' => 'Phone only (767px and below)', 'tablet' => 'Tablet and phone (1024px and below)', 'custom' => 'Custom breakpoint', 'never' => 'Never — keep full menu on all devices' ), 'description' => 'Never keeps the full navigation visible on tablets and phones.' ) );
        $this->add_control( 'breakpoint', array( 'label' => 'Custom mobile breakpoint (px)', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 320, 'max' => 1600, 'default' => 767, 'condition' => array( 'collapse' => 'custom' ) ) );
        $this->add_control( 'mobile_label', array( 'label' => 'Mobile toggle label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Menu' ) );
        $this->add_control( 'back_label', array( 'label' => 'Back label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Back' ) );
        $this->add_control( 'duration', array( 'label' => 'Slide duration (ms)', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 0, 'max' => 1000, 'default' => 260, 'selectors' => array( '{{WRAPPER}} .cdn-nav' => '--cdn-duration: {{VALUE}}ms;' ) ) );
        foreach ( array( 'forward' => 'Forward arrow', 'back' => 'Back arrow', 'toggle' => 'Mobile toggle icon' ) as $key => $label ) {
            $this->add_control( $key . '_icon', array( 'label' => $label, 'type' => \Elementor\Controls_Manager::ICONS ) );
        }
        $this->end_controls_section();
        $this->start_controls_section( 'icons', array( 'label' => 'Link icons & images' ) );
        $repeater = new \Elementor\Repeater();
        $repeater->add_control( 'item', array( 'label' => 'Menu item', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => $items, 'label_block' => true ) );
        $repeater->add_control( 'icon', array( 'label' => 'Icon', 'type' => \Elementor\Controls_Manager::ICONS ) );
        $repeater->add_control( 'image', array( 'label' => 'Image (optional, with or without icon)', 'type' => \Elementor\Controls_Manager::MEDIA ) );
        $this->add_control( 'item_icons', array( 'label' => 'Assign icons to links', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(), 'title_field' => 'Menu item {{{ item }}}' ) );
        $this->add_control( 'icon_position', array( 'label' => 'Icon position', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => array( 'before' => 'Before label', 'after' => 'After label' ), 'default' => 'before', 'selectors_dictionary' => array( 'before' => 'row', 'after' => 'row-reverse' ), 'selectors' => array( '{{WRAPPER}} .cdn-link-content' => 'flex-direction: {{VALUE}};' ) ) );
        $this->end_controls_section();
        $this->start_controls_section( 'layout', array( 'label' => 'Layout & panel', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
        $this->add_responsive_control( 'alignment', array( 'label' => 'Menu alignment', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => array( 'flex-start' => 'Start', 'center' => 'Center', 'flex-end' => 'End', 'space-between' => 'Space between' ), 'selectors' => array( '{{WRAPPER}} .cdn-root' => 'justify-content: {{VALUE}};' ) ) );
        $this->slider( 'gap', 'Top-level gap', '--cdn-gap', 0, 100, 8 );
        $this->slider( 'width', 'Dropdown width', '--cdn-width', 160, 900, 300 );
        $this->slider( 'offset', 'Dropdown offset', '--cdn-offset', 0, 80, 8 );
        $this->slider( 'max_height', 'Maximum panel height', '--cdn-max-height', 100, 1000, 480 );
        $this->add_control( 'panel_align', array( 'label' => 'Dropdown alignment', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'start', 'options' => array( 'start' => 'Start', 'end' => 'End' ) ) );
        $this->add_control( 'panel_bg', array( 'label' => 'Panel background', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .cdn-dropdown' => 'background: {{VALUE}};' ) ) );
        $this->add_group_control( \Elementor\Group_Control_Border::get_type(), array( 'name' => 'panel_border', 'selector' => '{{WRAPPER}} .cdn-dropdown' ) );
        $this->add_group_control( \Elementor\Group_Control_Box_Shadow::get_type(), array( 'name' => 'panel_shadow', 'selector' => '{{WRAPPER}} .cdn-dropdown' ) );
        $this->slider( 'radius', 'Panel corner radius', '--cdn-radius', 0, 80, 12 );
        $this->dimensions( 'panel_padding', 'Panel padding', '{{WRAPPER}} .cdn-panel', 'padding' );
        $this->end_controls_section();
        foreach ( array( 'top' => array( 'Top-level links', '.cdn-root > li > .cdn-row' ), 'sub' => array( 'Dropdown links', '.cdn-panel .cdn-row' ), 'back' => array( 'Back navigation', '.cdn-back' ), 'mobile' => array( 'Mobile toggle', '.cdn-mobile-toggle' ) ) as $key => $group ) {
            $selector = '{{WRAPPER}} ' . $group[1];
            $this->start_controls_section( 'style_' . $key, array( 'label' => $group[0], 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
            $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), array( 'name' => $key . '_type', 'selector' => $selector ) );
            $this->dimensions( $key . '_padding', 'Padding', $selector . ' .cdn-link, ' . $selector . ' .cdn-next, ' . $selector . ':is(button)', 'padding' );
            $this->add_group_control( \Elementor\Group_Control_Border::get_type(), array( 'name' => $key . '_border', 'selector' => $selector ) );
            $this->add_responsive_control( $key . '_radius', array( 'label' => 'Corner radius', 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'max' => 80 ) ), 'selectors' => array( $selector => 'border-radius: {{SIZE}}px;' ) ) );
            $this->start_controls_tabs( $key . '_states' );
            foreach ( array( 'normal' => '', 'hover' => ':is(:hover,:focus-within)', 'active' => '.cdn-current' ) as $state => $suffix ) {
                $this->start_controls_tab( $key . '_' . $state, array( 'label' => ucfirst( $state ) ) );
                foreach ( array( 'color' => 'Text color', 'background-color' => 'Background', 'border-color' => 'Border color' ) as $property => $label ) {
                    $this->add_control( $key . '_' . $state . '_' . $property, array( 'label' => $label, 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( $selector . $suffix => $property . ': {{VALUE}};' ) ) );
                }
                $this->end_controls_tab();
            }
            $this->end_controls_tabs(); $this->end_controls_section();
        }
        $this->start_controls_section( 'icon_style', array( 'label' => 'Icons & arrows', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
        $this->slider( 'icon_size', 'Link icon size', '--cdn-icon-size', 8, 100, 18 );
        $this->slider( 'icon_gap', 'Icon spacing', '--cdn-icon-gap', 0, 60, 10 );
        $this->slider( 'arrow_size', 'Arrow size', '--cdn-arrow-size', 8, 60, 18 );
        $this->slider( 'image_width', 'Image width', '--cdn-image-width', 12, 200, 32 );
        $this->slider( 'image_height', 'Image height', '--cdn-image-height', 12, 200, 32 );
        $this->slider( 'image_radius', 'Image corner radius', '--cdn-image-radius', 0, 100, 4 );
        $this->add_control( 'image_fit', array( 'label' => 'Image fit', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'contain', 'options' => array( 'contain' => 'Contain', 'cover' => 'Cover' ), 'selectors' => array( '{{WRAPPER}} .cdn-image' => 'object-fit: {{VALUE}};' ) ) );
        foreach ( array( 'normal' => '', 'hover' => ':is(:hover,:focus-within)', 'active' => '.cdn-current' ) as $state => $suffix ) {
            $this->add_control( 'icon_color_' . $state, array( 'label' => ucfirst( $state ) . ' icon color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .cdn-row' . $suffix . ' .cdn-icon' => 'color: {{VALUE}};' ) ) );
        }
        $this->add_control( 'arrow_color', array( 'label' => 'Arrow color', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .cdn-arrow' => 'color: {{VALUE}};' ) ) );
        $this->end_controls_section();
    }
    private function slider( $id, $label, $var, $min, $max, $default ) {
        $this->add_responsive_control( $id, array( 'label' => $label, 'type' => \Elementor\Controls_Manager::SLIDER, 'default' => array( 'size' => $default, 'unit' => 'px' ), 'range' => array( 'px' => array( 'min' => $min, 'max' => $max ) ), 'selectors' => array( '{{WRAPPER}} .cdn-nav' => $var . ': {{SIZE}}px;' ) ) );
    }
    private function dimensions( $id, $label, $selector, $property ) {
        $this->add_responsive_control( $id, array( 'label' => $label, 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em', '%' ), 'selectors' => array( $selector => $property . ': {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
    }
    private function icon( $icon, $fallback = '', $class = 'cdn-arrow' ) {
        echo '<span class="' . esc_attr( $class ) . '" aria-hidden="true">';
        if ( ! empty( $icon['value'] ) ) { \Elementor\Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) ); }
        else { echo esc_html( $fallback ); }
        echo '</span>';
    }
    private function row( $item, $has_children, $s, $icons, $prefix, $top = false, $view = false ) {
        $current = ! empty( $item->current );
        echo '<div class="cdn-row' . ( $current ? ' cdn-current' : '' ) . '">';
        if ( $has_children ) {
            echo '<button class="cdn-link cdn-next cdn-parent" type="button" data-target="' . esc_attr( $prefix . $item->ID ) . '" aria-controls="' . esc_attr( $prefix . $item->ID ) . '" aria-expanded="false"' . ( $top ? ' data-root-toggle' : '' );
        } else {
            echo '<a class="cdn-link' . ( $view ? ' cdn-view-parent' : '' ) . '" href="' . esc_url( $item->url ) . '"';
            if ( $current ) { echo ' aria-current="page"'; }
            if ( $item->target ) { echo ' target="' . esc_attr( $item->target ) . '"'; }
            $rel = trim( $item->xfn . ( '_blank' === $item->target ? ' noopener noreferrer' : '' ) );
            if ( $rel ) { echo ' rel="' . esc_attr( $rel ) . '"'; }
            if ( $item->attr_title ) { echo ' title="' . esc_attr( $item->attr_title ) . '"'; }
        }
        echo '><span class="cdn-link-content">';
        if ( ! empty( $icons[ $item->ID ]['icon']['value'] ) ) { $this->icon( $icons[ $item->ID ]['icon'], '', 'cdn-icon' ); }
        $image = $icons[ $item->ID ]['image'] ?? array();
        if ( ! empty( $image['id'] ) ) {
            echo wp_get_attachment_image( absint( $image['id'] ), 'thumbnail', false, array( 'class' => 'cdn-image', 'alt' => '', 'aria-hidden' => 'true' ) );
        } elseif ( ! empty( $image['url'] ) ) {
            echo '<img class="cdn-image" src="' . esc_url( $image['url'] ) . '" alt="" aria-hidden="true" loading="lazy" />';
        }
        echo '<span class="cdn-label">' . esc_html( $view ? sprintf( __( 'View %s', 'calico-drilldown-navigation' ), $item->title ) : $item->title ) . '</span></span>';
        if ( $has_children ) {
            if ( ! $top || 'yes' === ( $s['show_top_arrows'] ?? 'yes' ) ) { $this->icon( $s['forward_icon'] ?? array(), $top ? '⌄' : '›' ); }
            echo '</button>';
        } else { echo '</a>'; }
        echo '</div>';
    }
    protected function render() {
        $s = $this->get_settings_for_display();
        $items = wp_get_nav_menu_items( absint( $s['menu'] ?? 0 ) );
        if ( ! $items ) { if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) { echo '<p>Select a WordPress menu under Menu &amp; behavior.</p>'; } return; }
        if ( function_exists( '_wp_menu_item_classes_by_context' ) ) { _wp_menu_item_classes_by_context( $items ); }
        $children = array(); $icons = array();
        foreach ( $items as $item ) { $children[ (int) $item->menu_item_parent ][] = $item; }
        foreach ( (array) ( $s['item_icons'] ?? array() ) as $entry ) { $icons[ absint( $entry['item'] ?? 0 ) ] = $entry; }
        $prefix = wp_unique_id( 'cdn-' . $this->get_id() . '-' ) . '-';
        $collapse = $s['collapse'] ?? 'custom';
        $breakpoint = 'phone' === $collapse ? 767 : ( 'tablet' === $collapse ? 1024 : max( 320, min( 1600, absint( $s['breakpoint'] ?? 767 ) ) ) );
        echo '<nav class="cdn-nav" aria-label="' . esc_attr( $s['label'] ?: 'Main navigation' ) . '" data-collapse="' . esc_attr( $collapse ) . '" data-breakpoint="' . $breakpoint . '" data-trigger="' . esc_attr( $s['open_on'] ) . '" data-align="' . esc_attr( $s['panel_align'] ) . '">';
        echo '<button class="cdn-mobile-toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr( $prefix . 'root' ) . '">';
        $this->icon( $s['toggle_icon'] ?? array(), '☰' ); echo '<span>' . esc_html( $s['mobile_label'] ?: 'Menu' ) . '</span></button>';
        echo '<ul class="cdn-root" id="' . esc_attr( $prefix . 'root' ) . '">';
        foreach ( $children[0] ?? array() as $item ) {
            echo '<li>'; $this->row( $item, ! empty( $children[ $item->ID ] ), $s, $icons, $prefix, true );
            if ( ! empty( $children[ $item->ID ] ) ) {
                echo '<div class="cdn-dropdown"><div class="cdn-viewport">';
                $queue = array( $item ); $seen = array();
                while ( $queue ) {
                    $parent = array_shift( $queue ); if ( isset( $seen[ $parent->ID ] ) ) { continue; } $seen[ $parent->ID ] = true;
                    echo '<div class="cdn-panel" id="' . esc_attr( $prefix . $parent->ID ) . '" aria-label="' . esc_attr( $parent->title ) . '">';
                    if ( $parent->ID !== $item->ID ) {
                        echo '<button class="cdn-back" type="button">'; $this->icon( $s['back_icon'] ?? array(), '‹' );
                        echo '<span>' . esc_html( ( $s['back_label'] ?: 'Back' ) . ' — ' . $parent->title ) . '</span></button>';
                    }
                    echo '<ul>';
                    if ( ! empty( $parent->url ) && '#' !== trim( $parent->url ) ) {
                        echo '<li>'; $this->row( $parent, false, $s, $icons, $prefix, false, true ); echo '</li>';
                    }
                    foreach ( $children[ $parent->ID ] ?? array() as $child ) {
                        echo '<li>'; $this->row( $child, ! empty( $children[ $child->ID ] ), $s, $icons, $prefix ); echo '</li>';
                        if ( ! empty( $children[ $child->ID ] ) ) { $queue[] = $child; }
                    }
                    echo '</ul></div>';
                }
                echo '</div></div>';
            }
            echo '</li>';
        }
        echo '</ul></nav>';
    }
}
