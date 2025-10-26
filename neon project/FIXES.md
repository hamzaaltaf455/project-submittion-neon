# VA Broken Theme — Fixes (short)

Date: 2025-10-27

Summary: 5 issues were fixed so the child theme loads and displays correctly.

Fixes applied

- Replaced undefined `va_primary_menu()` with `wp_nav_menu()` — `header.php`
- Corrected CSS path to `assets/css/theme.css` — `functions.php`
- Removed enqueue for non-existent `assets/js/app.js` — `functions.php`
- Fixed parent theme `Template:` header to `twentytwentyfive` — `style.css`
- Registered `primary` menu location (`register_nav_menus`) — `functions.php`

Quick verification

# VA Broken Theme — Fixes (short)

Date: 2025-10-27

Summary: 5 issues were fixed so the child theme loads and displays correctly.

Fixes applied

- Replaced undefined `va_primary_menu()` with `wp_nav_menu()` — `header.php`
- Corrected CSS path to `assets/css/theme.css` — `functions.php`
- Removed enqueue for non-existent `assets/js/app.js` — `functions.php`
- Fixed parent theme `Template:` header to `twentytwentyfive` — `style.css`
- Registered `primary` menu location (`register_nav_menus`) — `functions.php`

Quick verification

- Activate the theme in WP admin
- Assign a menu to Appearance → Menus → Primary
- Confirm no PHP errors in logs and CSS loads (no 404s)
