=== Calico Drill Down Menu for Elementor ===
Contributors: calicodesign
Tags: elementor, navigation, menu, dropdown, icons
Requires at least: 6.5
Requires PHP: 7.4
Requires Plugins: elementor
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress menu navigation with icons, images, and sliding dropdown levels for Elementor.

== Description ==

Display an existing WordPress navigation menu using Elementor Free or Pro. Click anywhere on a parent row, including its label, image, icon, or arrow, to open its submenu. Top-level submenus open as dropdowns. Deeper submenus slide horizontally within that same panel, with a back button at each deeper level. A View [parent] link inside each submenu opens the parent's original destination, when one is supplied.

* Menu items at any depth can have an Elementor library icon, a WordPress media image, or both.
* Style top-level links, dropdown links, back navigation, and the mobile toggle separately.
* Typography, normal/hover/active colors, backgrounds, borders, padding, and corner radius.
* Panel alignment, width, padding, offset, height limit, border, shadow, and radius.
* Icon position, spacing, size, colors; image width, height, fit, and radius.
* Custom forward, back, and mobile icons; slide duration and back label.
* Responsive style controls and phone-only, tablet-and-phone, custom breakpoint, or never-collapse options.
* Keyboard focus, Escape to go back/close, ArrowDown to enter a dropdown, horizontal arrows on submenu buttons, reduced motion, and right-to-left direction.
* Multiple independent widgets. Hidden panels are inert so keyboard navigation stays in the visible level.
* No Elementor Pro dependency, remote service, tracking, or additional icon service.

== Installation ==

1. Install and activate Elementor.
2. Upload the plugin ZIP through Plugins > Add New > Upload Plugin and activate it.
3. Create or edit your menu under Appearance > Menus. Indent items to create submenu levels. On a block theme, you may need to open /wp-admin/nav-menus.php to create a classic menu.
4. Edit a page or header with Elementor and add Calico Drill Down Menu for Elementor.
5. Under Menu & behavior, select the WordPress menu and set the dropdown trigger. Set Collapse to mobile menu to Phone only, Tablet and phone, Custom breakpoint, or Never. Choose Never to keep full navigation on both tablets and phones.
6. Set Menu layout to Horizontal or Stacked vertical. Use the device icon for tablet/phone overrides. Vertical dropdowns expand beneath their parent and use the menu width. Choose Never under Collapse to mobile menu to keep the stack visible on small screens.
7. Under Link icons & images, add an entry, choose the menu item, and choose an icon, image, or both. Menu-item IDs distinguish links with the same label. Reopen Elementor after creating new menu items so the selector refreshes.
8. Use the Style tab for appearance and responsive overrides. Each icon/image assignment applies only to this widget instance.
9. Preview desktop and mobile and save your page/template.

== Frequently Asked Questions ==

= Do parent labels still link to their pages? =
Clicking a parent label opens its submenu. Use View [parent] inside that submenu to visit the parent's page. Leaf items still link directly. Empty URLs and a standalone # do not get View links. New-tab targets and link relationships are retained on the View link.

= Is there a fixed depth limit? =
There is no configured depth limit. Five menu levels are covered by the local browser fixture. All items come from the selected WordPress menu.

= Are link images decorative? =
Yes. The visible link label supplies the accessible name, so adjacent images/icons are hidden from assistive technology to avoid repeated labels.

= Why is a dropdown clipped by my header? =
An ancestor container with overflow hidden/clip can clip dropdown content. Give the header/container visible overflow and appropriate stacking. The widget constrains panel width and shifts desktop dropdowns inside the browser viewport.

= Does it replace WordPress menus or require a separate menu builder? =
No. Hierarchy, labels, order, destinations, new-tab targets, and link relationships remain in your existing WordPress menu. Per-link media is stored in the Elementor widget settings.

= Does this build provide automatic updates? =
Yes. WordPress checks stable releases from https://github.com/CalicoDesignCo/calico-drill-down-menu-for-elementor without a GitHub token. Use Dashboard > Updates > Check again. Drafts, prereleases and downgrades are ignored. Upload this updater-enabled release manually once if you installed local 0.1.0 or 0.2.0. Automatic-update scheduling stays under the administrator's control.

== Changelog ==

= 1.2.1 =
* Fix initialization of popup menus, cloned widgets, and reopened Elementor overlays.
* Animate vertical/mobile dropdown expansion and collapse using the existing animation duration; respect reduced motion.

= 1.2.0 =
* Add responsive horizontal / stacked vertical menu layout.
* Vertical dropdowns expand below the parent within the menu width; deeper levels retain drill-down navigation.

= 1.1.0 =
* Fix submenu focus transitions that could close dropdowns in sticky headers.
* Preserve hover-open dropdowns when their top-level button is clicked.
* Add Show top-level arrows switch; deeper arrows remain available.

= 1.0.0 =
* First GitHub release as Calico Drill Down Menu for Elementor, with native WordPress update support.

= 0.2.0 =
* Parent rows open submenus across their full width, including labels, images, icons, and arrows.
* Added View parent links inside submenus.
* Added phone, tablet, custom, and never-collapse settings.

= 0.1.0 =
* Initial local build with drill-down panels, icons and images, responsive styles, and keyboard navigation.
