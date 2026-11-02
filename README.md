# Calico Drill Down Menu for Elementor

A WordPress navigation widget for Elementor Free or Pro. Deeper menu levels slide into the same dropdown panel instead of opening another flyout.

## Features

- Click an entire parent row to open its submenu. Back arrows return one level.
- A **View [parent]** link keeps the parent's original page accessible.
- Assign icons, images, or both to menu items at any depth.
- Style typography, normal/hover/active colors, spacing, backgrounds, borders, rounded corners, shadows, icons, images, and arrows in Elementor.
- Choose horizontal or stacked vertical layout with responsive device overrides.
- Collapse on phones, tablets and phones, a custom breakpoint, or never.
- Optionally hide top-level arrows while keeping submenu navigation arrows.
- Keyboard navigation, focus restoration, reduced-motion support, and right-to-left layouts.
- Use multiple widgets with separate menus and styling.
- GitHub-backed WordPress updates without a GitHub token.

## Download and install

[Download the latest installable ZIP](https://github.com/CalicoDesignCo/calico-drill-down-menu-for-elementor/releases/latest/download/calico-drilldown-navigation.zip)

1. Install and activate Elementor. Requires WordPress 6.5+ and PHP 7.4+.
2. In WordPress, open **Plugins → Add New → Upload Plugin**, select the ZIP, and activate it.
3. Create a classic WordPress menu under **Appearance → Menus**. Indent items to create levels. Block themes can access the classic menu editor at `/wp-admin/nav-menus.php`.
4. Add **Calico Drill Down Menu for Elementor** in Elementor and select your menu.
5. Under **Link icons & images**, choose menu items and assign media. Reopen Elementor after adding new WordPress menu items to refresh the choices.
6. Configure **Menu & behavior** and the **Style** tab, preview, and save.

Use the attached release ZIP. GitHub's automatic source archive is not the installation package.

## Updates

WordPress checks this repository's latest stable release and offers newer versions under **Dashboard → Updates**. Use **Check again** to refresh the release cache. Automatic-update scheduling remains under the site administrator's control.

The original local 0.1.0 and 0.2.0 builds have no updater. Upload 1.0.0 manually once to enable future WordPress updates. The installed folder, PHP basename, and Elementor widget identifier remain `calico-drilldown-navigation`, preserving the original widget settings despite the new display name.

Updater requests use HTTPS, bounded timeouts, cached metadata, and version-pinned release packages. Drafts, prereleases, missing packages, and downgrades are ignored. If GitHub is unavailable, the installed menu continues working.

## Layout and accessibility

Parent rows are submenu buttons; leaf items and View links are ordinary links. Tab follows visible controls. Enter/Space activates buttons, Escape goes back or closes, ArrowDown enters a top-level dropdown, and horizontal arrows enter or leave deeper levels. Decorative images and icons do not repeat the visible link label for assistive technology.

Set **Collapse to mobile menu → Never** to retain full navigation on tablets and phones. Links may wrap to fit the available header width. A parent header with `overflow: hidden` or `clip` can clip dropdowns; use visible overflow and suitable stacking.

## Verification and limits

PHP 7.4/8.3 isolated render checks and local Chrome interaction checks cover five menu levels, parent-label navigation, View links, focus, keyboard behavior, multiple instances, mobile breakpoint boundaries, never-collapse layouts, and the Elementor replacement-widget hook. Updater tests cover stable release parsing, pinned assets, version comparisons, caching, and safe failures. These checks do not establish compatibility with every theme, screen reader, or physical device.

## Recovery

Deactivate the plugin to stop its widget and updater. WordPress menus, media, and Elementor settings remain owned by WordPress/Elementor; the plugin creates no custom tables. Reinstall the prior saved package if rollback is needed.

## Changelog

### 1.2.3

- Let custom padding determine ordinary link and parent-button row heights equally, including zero padding. Remove the fixed parent-button minimum; preserve configured row gaps.


### 1.2.2

- Keep menu-row backgrounds visible beneath parent buttons when Elementor global button styles are configured.
- Verify normal/hover backgrounds and hover/focus text on top-level buttons and links under conflicting kit styles.


### 1.2.1

- Initialize popup menus when attached, including cloned and reopened Elementor widgets.
- Smooth vertical/mobile expand and collapse, controlled by the existing animation duration and reduced-motion preference.
- Regression checks cover detached popup rendering, copied ready attributes, reopen, and interrupted animation.


### 1.2.0

- Add responsive Horizontal / Stacked vertical menu layout under Menu & behavior.
- Vertical dropdowns expand beneath the parent at the menu width; deeper levels retain sliding navigation.

### 1.1.0

- Prevent dropdown closure during deeper-level focus transitions in sticky headers.
- Keep hover-open menus open when the top-level label is clicked.
- Add Show top-level arrows control.

### 1.0.0

- First GitHub release under the name Calico Drill Down Menu for Elementor.
- Added native WordPress GitHub update support.
- Includes sliding submenus, View parent links, icons/images, styling, and all mobile-collapse options from the local builds.

Licensed under GPL-2.0-or-later. See [LICENSE](LICENSE).
