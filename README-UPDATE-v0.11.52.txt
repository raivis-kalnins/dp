DIGITALPULSE v0.11.52 - COMBINED CHANGED-FILES PATCH
Base: DigitalPulse v0.11.50 full website
Includes the v0.11.51 changes plus the latest sharing and Services menu fixes.

NEW IN v0.11.52
- Updated social share image, based on the real orange/navy DigitalPulse logo.
  New file: assets/img/digitalpulse-social-share-2026.jpg (1200x630, lightweight JPEG).
- Home and language pages use the new image for Open Graph and Twitter cards.
- Added social image dimensions, alt text and organisation logo structured data.
- Replaced the unattractive Services menu glyph with a simple CSS chevron.
- Fixed the 10px mouse-hover gap; the desktop submenu stays open when entering it.
- Incremented CMS version to bust existing CSS, JavaScript and PWA caches.

INCLUDES PREVIOUS v0.11.51 PATCH
- 18th "Your App" tile linking to #contact for custom app enquiries.
- WordPress Hosting + Care: EUR 3.50/month, including up to 0.5h monthly maintenance.
- WooCommerce Hosting + Care: EUR 5/month, including up to 0.5h monthly maintenance.
- Smaller mobile admin-login button, balanced hero typography and white LPCMS CTA.
- Localised new app and hosting plan descriptions into all 14 supported languages.

FILES TO UPLOAD (keep their relative paths):
  index.php
  data.json
  assets/css/lpcms-app.css
  assets/js/lpcms-public.js
  assets/js/.lpcms-admin.js
  assets/img/digitalpulse-social-share-2026.jpg

INSTALLATION
1. Back up the live website and CMS data.json before updating.
2. Upload the files above to the SAME matching paths under the website root.
3. Keep your existing .secrets.php, environment configuration and uploads.
4. IMPORTANT: data.json contains full CMS content. If the live CMS has newer edits,
   merge the changed SEO image, hero, app and pricing fields rather than blindly
   overwriting it. Check for concurrent CMS edits before uploading the file.
5. Clear LPCMS/hosting/CDN caches. The new v0.11.52 asset URLs bypass old CSS/JS
   cache entries and the new JPG filename bypasses old social-image cache entries.
6. Social platforms may retain link previews after deployment. Re-scrape the URL
   in Facebook Sharing Debugger / LinkedIn Post Inspector if needed; other social
   apps may take time to refresh their preview cache.

VALIDATION: PHP and JavaScript syntax, local English/Latvian SEO HTML, OG JPEG
  dimensions and delivery, 14-language data checks, and browser hover-to-click
  dropdown traversal passed. Not deployed to the live domain.
