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
        $this->add_responsive_control( 'menu_layout', array( 'label' => 'Menu layout', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'row', 'options' => array( 'row' => 'Horizontal', 'column' => 'Stacked vertical' ), 'render_type' => 'template', 'selectors' => array( '{{WRAPPER}} .cdn-nav' => '--cdn-direction: {{VALUE}};' ), 'description' => 'Vertical menus stack the main links and expand the dropdown beneath its parent. Deeper levels keep sliding within that panel.' ) );
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
        $this->start_controls_section( 'header_content', array( 'label' => 'Header & logos' ) );
        $this->add_control( 'header_enabled', array( 'label' => 'Enable header controls', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'render_type' => 'template', 'description' => 'Add a logo and coordinate header backgrounds, shrinking and sticky behavior. Uses the shared Switch after threshold under Style > Scroll colors.' ) );
        $header_condition = array( 'header_enabled' => 'yes' );
        $this->add_control( 'header_scope', array( 'label' => 'Header to control', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'widget', 'options' => array( 'widget' => 'This widget (logo + menu)', 'container' => 'Elementor header container', 'selector' => 'Ancestor CSS selector' ), 'condition' => $header_condition, 'description' => 'Container mode controls the outer header container in Theme Builder, or the nearest container elsewhere. Enable this on one menu per container.' ) );
        $this->add_control( 'header_selector', array( 'label' => 'Header container selector', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '', 'placeholder' => '#site-header', 'condition' => array( 'header_enabled' => 'yes', 'header_scope' => 'selector' ), 'description' => 'Select a container that surrounds this widget. Invalid or unrelated selectors fall back to this widget.' ) );
        $this->add_control( 'header_sticky', array( 'label' => 'Sticky header', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => $header_condition, 'description' => 'Disable Elementor Sticky on the same container. This controller yields to an existing Elementor sticky setting.' ) );
        $this->add_control( 'header_sticky_start', array( 'label' => 'Start sticking', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'top', 'options' => array( 'top' => 'When header reaches the top', 'threshold' => 'After shared scroll threshold' ), 'condition' => array( 'header_enabled' => 'yes', 'header_sticky' => 'yes' ) ) );
        $this->add_control( 'header_offset', array( 'label' => 'Sticky top offset (px)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 0, 'min' => 0, 'max' => 500, 'condition' => array( 'header_enabled' => 'yes', 'header_sticky' => 'yes' ), 'description' => 'The WordPress admin bar offset is added automatically when it is visible.' ) );
        $this->add_control( 'header_z_index', array( 'label' => 'Header z-index', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 1000, 'min' => 1, 'max' => 2147483647, 'condition' => $header_condition ) );
        $this->add_control( 'header_transparent', array( 'label' => 'Transparent before scroll', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => $header_condition ) );
        $this->add_control( 'header_shrink', array( 'label' => 'Shrink logo and header after scroll', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => $header_condition ) );
        $this->add_control( 'logo_location', array( 'label' => 'Logo location', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'internal', 'options' => array( 'internal' => 'Inside this widget', 'external' => 'Existing logo by CSS ID' ), 'condition' => $header_condition ) );
        $this->add_control( 'logo_external_id', array( 'label' => 'Existing logo CSS ID', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'header-logo', 'condition' => array( 'header_enabled' => 'yes', 'logo_location' => 'external' ), 'description' => 'In the existing Elementor Image or Site Logo widget, set Advanced > CSS ID to header-logo. Enter the same ID here, with or without #. Keep the logo and this menu in the same header container. Its link and accessible text stay unchanged.' ) );
        $this->add_control( 'logo_before', array( 'label' => 'Logo before scroll', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => $header_condition ) );
        $this->add_control( 'logo_after', array( 'label' => 'Logo after scroll', 'type' => \Elementor\Controls_Manager::MEDIA, 'condition' => $header_condition, 'description' => 'Optional light/dark variant. Inside the widget, one supplied logo serves both states. For an existing logo, an empty Before field preserves its original image; an empty After field keeps the Before image.' ) );
        $this->add_control( 'logo_url', array( 'label' => 'Logo link (inside widget only)', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '', 'placeholder' => 'https://example.com/', 'condition' => $header_condition, 'description' => 'Leave blank to link to the site homepage.' ) );
        $this->add_control( 'logo_label', array( 'label' => 'Logo accessible label (inside widget only)', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Home', 'condition' => $header_condition ) );
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
        $this->start_controls_section( 'scroll_style', array( 'label' => 'Scroll colors', 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
        $this->add_control( 'scroll_colors', array( 'label' => 'Change colors on scroll', 'type' => \Elementor\Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'render_type' => 'template', 'description' => 'Change top-level link, icon, arrow and mobile-toggle colors for transparent headers. Dropdown panels retain their own styling.' ) );
        $this->add_control( 'scroll_threshold', array( 'label' => 'Switch after (px)', 'type' => \Elementor\Controls_Manager::NUMBER, 'min' => 0, 'max' => 100000, 'default' => 80, 'render_type' => 'template', 'description' => 'Shared by menu colors, header backgrounds, logo changes and shrinking.', 'conditions' => array( 'relation' => 'or', 'terms' => array( array( 'name' => 'scroll_colors', 'operator' => '===', 'value' => 'yes' ), array( 'name' => 'header_enabled', 'operator' => '===', 'value' => 'yes' ) ) ) ) );
        $this->start_controls_tabs( 'scroll_color_states' );
        foreach ( array( 'top' => array( 'At top', ':not(.cdn-scrolled)', '#FFFFFF', '#E5E7EB' ), 'scrolled' => array( 'After scrolling', '.cdn-scrolled', '#202632', '#174BBB' ) ) as $phase => $config ) {
            $this->start_controls_tab( 'scroll_phase_' . $phase, array( 'label' => $config[0], 'condition' => array( 'scroll_colors' => 'yes' ) ) );
            foreach ( array( 'normal' => '', 'hover' => ':is(:hover,:focus-within)' ) as $state => $suffix ) {
                $base = '{{WRAPPER}} .cdn-nav[data-scroll-colors="yes"]' . $config[1];
                foreach ( array( 'color' => 'text color', 'background-color' => 'background' ) as $property => $label ) {
                    $args = array( 'label' => ucfirst( $state ) . ' ' . $label, 'type' => \Elementor\Controls_Manager::COLOR, 'condition' => array( 'scroll_colors' => 'yes' ), 'selectors' => array( $base . ' .cdn-root > li > .cdn-row' . $suffix => $property . ': {{VALUE}} !important;', $base . ' .cdn-mobile-toggle' . ( 'hover' === $state ? ':is(:hover,:focus-visible)' : '' ) => $property . ': {{VALUE}} !important;' ) );
                    if ( 'color' === $property ) { $args['default'] = $config[ 'hover' === $state ? 3 : 2 ]; }
                    $this->add_control( 'scroll_' . $phase . '_' . $state . '_' . str_replace( '-', '_', $property ), $args );
                }
            }
            $this->end_controls_tab();
        }
        $this->end_controls_tabs();
        $this->end_controls_section();
        $this->start_controls_section( 'header_style', array( 'label' => 'Header bar & logo sizes', 'tab' => \Elementor\Controls_Manager::TAB_STYLE, 'condition' => $header_condition ) );
        $this->add_control( 'header_bg_before', array( 'label' => 'Background before scroll', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#FFFFFF', 'condition' => array( 'header_transparent' => '' ), 'selectors' => array( '{{WRAPPER}} .cdn-header-shell' => '--cdn-header-bg-before: {{VALUE}};' ) ) );
        $this->add_control( 'header_bg_after', array( 'label' => 'Background after scroll', 'type' => \Elementor\Controls_Manager::COLOR, 'default' => '#FFFFFF', 'selectors' => array( '{{WRAPPER}} .cdn-header-shell' => '--cdn-header-bg-after: {{VALUE}};' ) ) );
        foreach ( array( 'before' => array( 'Before scroll', 96, 16, 180, 80 ), 'after' => array( 'After scroll', 64, 8, 120, 48 ) ) as $phase => $values ) {
            $this->add_responsive_control( 'header_height_' . $phase, array( 'label' => $values[0] . ' minimum bar height', 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 400 ) ), 'default' => array( 'size' => $values[1], 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .cdn-header-shell' => '--cdn-header-height-' . $phase . ': {{SIZE}}px;' ) ) );
            $this->add_responsive_control( 'header_padding_' . $phase, array( 'label' => $values[0] . ' bar padding', 'type' => \Elementor\Controls_Manager::DIMENSIONS, 'size_units' => array( 'px' ), 'default' => array( 'top' => $values[2], 'right' => 24, 'bottom' => $values[2], 'left' => 24, 'unit' => 'px', 'isLinked' => false ), 'selectors' => array( '{{WRAPPER}} .cdn-header-shell' => '--cdn-header-padding-' . $phase . ': {{TOP}}px {{RIGHT}}px {{BOTTOM}}px {{LEFT}}px;' ) ) );
            $this->add_responsive_control( 'logo_width_' . $phase, array( 'label' => $values[0] . ' logo width', 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 20, 'max' => 600 ) ), 'default' => array( 'size' => $values[3], 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .cdn-header-shell' => '--cdn-logo-width-' . $phase . ': {{SIZE}}px;' ) ) );
            $this->add_responsive_control( 'logo_height_' . $phase, array( 'label' => $values[0] . ' logo maximum height', 'type' => \Elementor\Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 10, 'max' => 300 ) ), 'default' => array( 'size' => $values[4], 'unit' => 'px' ), 'selectors' => array( '{{WRAPPER}} .cdn-header-shell' => '--cdn-logo-height-' . $phase . ': {{SIZE}}px;' ) ) );
        }
        $this->add_control( 'header_duration', array( 'label' => 'Header transition (ms)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 250, 'min' => 0, 'max' => 1500, 'selectors' => array( '{{WRAPPER}} .cdn-header-shell' => '--cdn-header-duration: {{VALUE}}ms;' ) ) );
        $this->end_controls_section();
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
    private function header_logo( $image, $class ) {
        if ( ! empty( $image['id'] ) ) { echo wp_get_attachment_image( absint( $image['id'] ), 'full', false, array( 'class' => $class, 'alt' => '', 'aria-hidden' => 'true', 'loading' => 'eager' ) ); }
        elseif ( ! empty( $image['url'] ) ) { echo '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $image['url'] ) . '" alt="" aria-hidden="true" loading="eager" />'; }
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
        $layout = 'column' === ( $s['menu_layout'] ?? 'row' ) ? 'column' : 'row';
        $scroll_colors = 'yes' === ( $s['scroll_colors'] ?? '' ) ? 'yes' : 'no';
        $scroll_threshold = max( 0, min( 100000, (int) ( $s['scroll_threshold'] ?? 80 ) ) );
        $header = 'yes' === ( $s['header_enabled'] ?? '' );
        if ( $header ) {
            $scope = in_array( $s['header_scope'] ?? 'widget', array( 'widget', 'container', 'selector' ), true ) ? ( $s['header_scope'] ?? 'widget' ) : 'widget';
            $external = 'external' === ( $s['logo_location'] ?? '' );
            $external_data = '';
            if ( $external ) {
                foreach ( array( 'before', 'after' ) as $phase ) {
                    $image = $s[ 'logo_' . $phase ] ?? array();
                    $url = ! empty( $image['id'] ) ? wp_get_attachment_image_url( absint( $image['id'] ), 'full' ) : ( $image['url'] ?? '' );
                    $external_data .= ' data-logo-' . $phase . '="' . esc_url( $url ) . '"';
                }
            }
            echo '<div class="cdn-header-shell" data-header-scope="' . esc_attr( $scope ) . '" data-header-selector="' . esc_attr( $s['header_selector'] ?? '' ) . '" data-header-sticky="' . esc_attr( $s['header_sticky'] ?? 'yes' ) . '" data-sticky-start="' . esc_attr( $s['header_sticky_start'] ?? 'top' ) . '" data-header-offset="' . max( 0, min( 500, (int) ( $s['header_offset'] ?? 0 ) ) ) . '" data-header-z="' . max( 1, min( 2147483647, (int) ( $s['header_z_index'] ?? 1000 ) ) ) . '" data-transparent="' . esc_attr( $s['header_transparent'] ?? 'yes' ) . '" data-shrink="' . esc_attr( $s['header_shrink'] ?? 'yes' ) . '" data-logo-location="' . ( $external ? 'external' : 'internal' ) . '" data-logo-id="' . esc_attr( ltrim( trim( $s['logo_external_id'] ?? '' ), '#' ) ) . '"' . $external_data . '><div class="cdn-header-bar">';
            $before = $s['logo_before'] ?? array(); $after = $s['logo_after'] ?? array();
            $has_before = ! empty( $before['id'] ) || ! empty( $before['url'] ); $has_after = ! empty( $after['id'] ) || ! empty( $after['url'] );
            if ( ! $external && ( $has_before || $has_after ) ) {
                echo '<a class="cdn-header-logo" href="' . esc_url( ! empty( $s['logo_url'] ) ? $s['logo_url'] : home_url( '/' ) ) . '" aria-label="' . esc_attr( ! empty( $s['logo_label'] ) ? $s['logo_label'] : 'Home' ) . '">';
                if ( $has_before && $has_after ) { $this->header_logo( $before, 'cdn-logo-before' ); $this->header_logo( $after, 'cdn-logo-after' ); }
                else { $this->header_logo( $has_before ? $before : $after, 'cdn-logo-single' ); }
                echo '</a>';
            }
        }
        echo '<nav class="cdn-nav" data-layout="' . esc_attr( $layout ) . '" aria-label="' . esc_attr( $s['label'] ?: 'Main navigation' ) . '" data-collapse="' . esc_attr( $collapse ) . '" data-breakpoint="' . $breakpoint . '" data-trigger="' . esc_attr( $s['open_on'] ) . '" data-align="' . esc_attr( $s['panel_align'] ) . '" data-scroll-colors="' . $scroll_colors . '" data-scroll-threshold="' . $scroll_threshold . '">';
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
        if ( $header ) { echo '</div></div>'; }
    }
}
