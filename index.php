<?php
// Production safety: PHP diagnostics must never be injected into HTML/JSON responses.
// Keep diagnostics in the server error log instead, especially for API requests.
@ini_set('display_errors', '0');
@ini_set('display_startup_errors', '0');
@ini_set('log_errors', '1');
$GLOBALS['CMS_API_BUFFER_LEVEL'] = null;
if (isset($_GET['api'])) {
  $GLOBALS['CMS_API_BUFFER_LEVEL'] = ob_get_level();
  ob_start();
}
/**
 * Flat Headless React + JSON CMS in one index.php
 * ------------------------------------------------
 * Upload this file as index.php. It auto-creates:
 *   data.json
 *   .htaccess
 *   assets/.htaccess
 *   assets/uploads/
 *   assets/gallery/
 *   assets/img/
 *   assets/css/
 *   assets/js/
 *   assets/fonts/
 *   assets/docs/
 *   assets/cache/
 *
 * IMPORTANT BEFORE PRODUCTION:
 * 1) Replace CMS_SECRET with a unique secret for this deployment.
 * 2) Confirm BREAKGLASS_ADMIN_EMAIL and add editor users from the Users tab.
 * 3) Configure Admin > Mailer for PHP mail() or SMTP delivery.
 * 4) Ensure GD/Imagick/ffmpeg can encode AVIF when browser conversion is unavailable.
 * 5) Visit /?admin for super admin login; editor users use email security links.
 * 6) Enable Products > Shop mode for catalogue, cart, quote flow, payment instructions, and commerce integration profiles.
 */

$cmsSecureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'secure' => $cmsSecureCookie,
  'httponly' => true,
  'samesite' => 'Lax',
]);
session_start();

define('CMS_VERSION', '0.11.28');
define('DATA_FILE', __DIR__ . '/data.json');
define('MAIL_LOG_FILE', __DIR__ . '/.lpcms-mail.log');
define('HTACCESS_FILE', __DIR__ . '/.htaccess');
define('ASSETS_DIR', __DIR__ . '/assets');
define('I18N_FILE', ASSETS_DIR . '/i18n.json');
define('ASSETS_HTACCESS_FILE', ASSETS_DIR . '/.htaccess');
define('UPLOAD_DIR', ASSETS_DIR . '/uploads');
define('GALLERY_DIR', ASSETS_DIR . '/gallery');
define('IMG_DIR', ASSETS_DIR . '/img');
define('CSS_DIR', ASSETS_DIR . '/css');
define('JS_DIR', ASSETS_DIR . '/js');
define('FONTS_DIR', ASSETS_DIR . '/fonts');
define('DOCS_DIR', ASSETS_DIR . '/docs');
define('VIDEO_DIR', ASSETS_DIR . '/video');
define('CACHE_DIR', ASSETS_DIR . '/cache');
define('THEMES_DIR', ASSETS_DIR . '/themes');
define('THEME_MAX_UPLOAD_BYTES', 40 * 1024 * 1024);
define('THEME_MAX_FILES', 3000);
define('THEME_MAX_FILE_BYTES', 8 * 1024 * 1024);

define('CMS_SECRET', '3562811c9a621f22e0868716c33ac1ced123412f5ac7429ff8278672900692a7');
define('DEV_SHOW_LOGIN_LINK', false);
define('CMS_TIMEZONE', 'Europe/London');
define('BREAKGLASS_PREFIX', 'admin');
define('BREAKGLASS_PASSWORD', 'LPCMS@admin');
define('BREAKGLASS_ADMIN_EMAIL', 'raivis@63.lv');
define('BREAKGLASS_OTP_TTL', 10 * 60);
define('BREAKGLASS_MAX_CODE_ATTEMPTS', 5);
define('BREAKGLASS_CODE_LENGTH', 5);
define('IMAGE_MAX_WIDTH', 1920);
define('IMAGE_AVIF_QUALITY', 70);
define('IMAGE_MAX_UPLOAD_BYTES', 20 * 1024 * 1024);
define('VIDEO_MAX_UPLOAD_BYTES', 50 * 1024 * 1024);

const ADMIN_EMAILS = [
  'raivis@63.lv',
];

function cms_demo_products(): array {
  return json_decode('[{"title":"Starter Website Package","slug":"starter-website-package","id":"1001","sku":"STARTER-WEBSITE-PA","type":"variable","category":"website-packages","brand":"LPCMS","tags":["website-packages","digital","blue","xs"],"price":550,"oldPrice":690,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Blue","size":"XS","material":"Digital","license":"Standard"},"variations":[{"id":"starter-website-package-a","sku":"STARTER-WEBSITE-PA-A","attributes":{"size":"XS","colour":"Blue"},"price":550,"oldPrice":690,"stock":5,"image":""},{"id":"starter-website-package-b","sku":"STARTER-WEBSITE-PA-B","attributes":{"size":"S","colour":"Navy"},"price":600,"oldPrice":760,"stock":3,"image":""}],"stock":10,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Starter Website Package is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Starter Website Package","description":"Starter Website Package demo product.","image":""}},{"title":"Business Website Package","slug":"business-website-package","id":"1002","sku":"BUSINESS-WEBSITE-P","type":"variable","category":"website-packages","brand":"LPCMS","tags":["website-packages","service","black","s"],"price":990,"oldPrice":1290,"discountLabel":"-23%","currency":"EUR","image":"https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Black","size":"S","material":"Service","license":"Business"},"variations":[{"id":"business-website-package-a","sku":"BUSINESS-WEBSITE-P-A","attributes":{"size":"S","colour":"Black"},"price":990,"oldPrice":1290,"stock":6,"image":""},{"id":"business-website-package-b","sku":"BUSINESS-WEBSITE-P-B","attributes":{"size":"M","colour":"Purple"},"price":1040,"oldPrice":1360,"stock":4,"image":""}],"stock":12,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Business Website Package is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Business Website Package","description":"Business Website Package demo product.","image":""}},{"title":"Custom Quote Project","slug":"custom-quote-project","id":"1003","sku":"CUSTOM-QUOTE-PROJE","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","cotton","white","m"],"price":0,"oldPrice":0,"discountLabel":"Quote only","currency":"EUR","image":"https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"White","size":"M","material":"Cotton","duration":"Project"},"variations":[],"stock":0,"inStock":true,"text":"Quote-only custom product.","body":"<p>Custom Quote Project is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":false,"quoteButton":true,"related":[],"seo":{"title":"Custom Quote Project","description":"Custom Quote Project demo product.","image":""}},{"title":"SEO Audit Service","slug":"seo-audit-service","id":"1004","sku":"SEO-AUDIT-SERVICE","type":"simple","category":"seo-tools","brand":"LPCMS","tags":["seo-tools","leather","navy","l"],"price":180,"oldPrice":240,"discountLabel":"-25%","currency":"EUR","image":"https://images.unsplash.com/photo-1432888622747-4eb9a8f2c287?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Navy","size":"L","material":"Leather","duration":"One-off"},"variations":[],"stock":16,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>SEO Audit Service is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"SEO Audit Service","description":"SEO Audit Service demo product.","image":""}},{"title":"Speed Optimization","slug":"speed-optimization","id":"1005","sku":"SPEED-OPTIMIZATION","type":"simple","category":"seo-tools","brand":"LPCMS","tags":["seo-tools","aluminum","purple","xl"],"price":320,"oldPrice":420,"discountLabel":"-24%","currency":"EUR","image":"https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Purple","size":"XL","material":"Aluminum","duration":"Monthly"},"variations":[],"stock":18,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Speed Optimization is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Speed Optimization","description":"Speed Optimization demo product.","image":""}},{"title":"Monthly Care Plan","slug":"monthly-care-plan","id":"1006","sku":"MONTHLY-CARE-PLAN","type":"variable","category":"subscriptions","brand":"LPCMS","tags":["subscriptions","glass","green","custom"],"price":79,"oldPrice":99,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Green","size":"Custom","material":"Glass","duration":"Project"},"variations":[{"id":"monthly-care-plan-a","sku":"MONTHLY-CARE-PLAN-A","attributes":{"size":"Custom","colour":"Green"},"price":79,"oldPrice":99,"stock":10,"image":""},{"id":"monthly-care-plan-b","sku":"MONTHLY-CARE-PLAN-B","attributes":{"size":"XS","colour":"Orange"},"price":84,"oldPrice":107,"stock":6,"image":""}],"stock":20,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Monthly Care Plan is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Monthly Care Plan","description":"Monthly Care Plan demo product.","image":""}},{"title":"Logo Design Add-on","slug":"logo-design-add-on","id":"1007","sku":"LOGO-DESIGN-ADD-ON","type":"simple","category":"custom-services","brand":"Studio","tags":["custom-services","digital","red","xs"],"price":250,"oldPrice":0,"discountLabel":"","currency":"EUR","image":"https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Red","size":"XS","material":"Digital","duration":"One-off"},"variations":[],"stock":22,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Logo Design Add-on is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Logo Design Add-on","description":"Logo Design Add-on demo product.","image":""}},{"title":"Brochure PDF Design","slug":"brochure-pdf-design","id":"1008","sku":"BROCHURE-PDF-DESIG","type":"simple","category":"custom-services","brand":"Studio","tags":["custom-services","service","gold","s"],"price":390,"oldPrice":490,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Gold","size":"S","material":"Service","duration":"Monthly"},"variations":[],"stock":24,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Brochure PDF Design is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Brochure PDF Design","description":"Brochure PDF Design demo product.","image":""}},{"title":"Cotton Promo T-Shirt","slug":"cotton-promo-t-shirt","id":"1009","sku":"COTTON-PROMO-T-SHI","type":"variable","category":"merch","brand":"Garilla","tags":["merch","cotton","orange","m"],"price":24,"oldPrice":34,"discountLabel":"-29%","currency":"EUR","image":"https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Orange","size":"M","material":"Cotton"},"variations":[{"id":"cotton-promo-t-shirt-a","sku":"COTTON-PROMO-T-SHI-A","attributes":{"size":"M","colour":"Orange"},"price":24,"oldPrice":34,"stock":13,"image":""},{"id":"cotton-promo-t-shirt-b","sku":"COTTON-PROMO-T-SHI-B","attributes":{"size":"L","colour":"Silver"},"price":29,"oldPrice":42,"stock":8,"image":""}],"stock":26,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Cotton Promo T-Shirt is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Cotton Promo T-Shirt","description":"Cotton Promo T-Shirt demo product.","image":""}},{"title":"Premium Hoodie","slug":"premium-hoodie","id":"1010","sku":"PREMIUM-HOODIE","type":"variable","category":"merch","brand":"Garilla","tags":["merch","leather","grey","l"],"price":54,"oldPrice":69,"discountLabel":"-22%","currency":"EUR","image":"https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Grey","size":"L","material":"Leather"},"variations":[{"id":"premium-hoodie-a","sku":"PREMIUM-HOODIE-A","attributes":{"size":"L","colour":"Grey"},"price":54,"oldPrice":69,"stock":14,"image":""},{"id":"premium-hoodie-b","sku":"PREMIUM-HOODIE-B","attributes":{"size":"XL","colour":"Pink"},"price":59,"oldPrice":77,"stock":9,"image":""}],"stock":28,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Premium Hoodie is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Premium Hoodie","description":"Premium Hoodie demo product.","image":""}},{"title":"Leather Notebook","slug":"leather-notebook","id":"1011","sku":"LEATHER-NOTEBOOK","type":"simple","category":"merch","brand":"Garilla","tags":["merch","aluminum","brown","xl"],"price":18,"oldPrice":25,"discountLabel":"-28%","currency":"EUR","image":"https://images.unsplash.com/photo-1517842645767-c639042777db?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Brown","size":"XL","material":"Aluminum"},"variations":[],"stock":30,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Leather Notebook is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Leather Notebook","description":"Leather Notebook demo product.","image":""}},{"title":"Aluminium Water Bottle","slug":"aluminium-water-bottle","id":"1012","sku":"ALUMINIUM-WATER-BO","type":"variable","category":"merch","brand":"Garilla","tags":["merch","glass","silver","custom"],"price":15,"oldPrice":22,"discountLabel":"-32%","currency":"EUR","image":"https://images.unsplash.com/photo-1523362628745-0c100150b504?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Silver","size":"Custom","material":"Glass"},"variations":[{"id":"aluminium-water-bottle-a","sku":"ALUMINIUM-WATER-BO-A","attributes":{"size":"Custom","colour":"Silver"},"price":15,"oldPrice":22,"stock":16,"image":""},{"id":"aluminium-water-bottle-b","sku":"ALUMINIUM-WATER-BO-B","attributes":{"size":"XS","colour":"Beige"},"price":20,"oldPrice":30,"stock":10,"image":""}],"stock":32,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Aluminium Water Bottle is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Aluminium Water Bottle","description":"Aluminium Water Bottle demo product.","image":""}},{"title":"Glass Desk Sign","slug":"glass-desk-sign","id":"1013","sku":"GLASS-DESK-SIGN","type":"simple","category":"merch","brand":"Studio","tags":["merch","digital","pink","xs"],"price":48,"oldPrice":0,"discountLabel":"","currency":"EUR","image":"https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Pink","size":"XS","material":"Digital"},"variations":[],"stock":34,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Glass Desk Sign is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Glass Desk Sign","description":"Glass Desk Sign demo product.","image":""}},{"title":"Digital Download Pack","slug":"digital-download-pack","id":"1014","sku":"DIGITAL-DOWNLOAD-P","type":"simple","category":"digital-products","brand":"LPCMS","tags":["digital-products","service","sky","s"],"price":39,"oldPrice":59,"discountLabel":"-34%","currency":"EUR","image":"https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Sky","size":"S","material":"Service","license":"Business"},"variations":[],"stock":36,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Digital Download Pack is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Digital Download Pack","description":"Digital Download Pack demo product.","image":""}},{"title":"Premium Template Bundle","slug":"premium-template-bundle","id":"1015","sku":"PREMIUM-TEMPLATE-B","type":"variable","category":"digital-products","brand":"LPCMS","tags":["digital-products","cotton","beige","m"],"price":89,"oldPrice":129,"discountLabel":"-31%","currency":"EUR","image":"https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Beige","size":"M","material":"Cotton","license":"Enterprise"},"variations":[{"id":"premium-template-bundle-a","sku":"PREMIUM-TEMPLATE-B-A","attributes":{"size":"M","colour":"Beige"},"price":89,"oldPrice":129,"stock":19,"image":""},{"id":"premium-template-bundle-b","sku":"PREMIUM-TEMPLATE-B-B","attributes":{"size":"L","colour":"Lime"},"price":94,"oldPrice":199,"stock":12,"image":""}],"stock":38,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Premium Template Bundle is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Premium Template Bundle","description":"Premium Template Bundle demo product.","image":""}},{"title":"Photo Gallery Setup","slug":"photo-gallery-setup","id":"1016","sku":"PHOTO-GALLERY-SETU","type":"simple","category":"custom-services","brand":"Studio","tags":["custom-services","leather","rose","l"],"price":160,"oldPrice":0,"discountLabel":"","currency":"EUR","image":"https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Rose","size":"L","material":"Leather","duration":"One-off"},"variations":[],"stock":40,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Photo Gallery Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Photo Gallery Setup","description":"Photo Gallery Setup demo product.","image":""}},{"title":"Contact Form Setup","slug":"contact-form-setup","id":"1017","sku":"CONTACT-FORM-SETUP","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","aluminum","yellow","xl"],"price":120,"oldPrice":150,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Yellow","size":"XL","material":"Aluminum","duration":"Monthly"},"variations":[],"stock":42,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Contact Form Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Contact Form Setup","description":"Contact Form Setup demo product.","image":""}},{"title":"Multilingual Setup","slug":"multilingual-setup","id":"1018","sku":"MULTILINGUAL-SETUP","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","glass","lime","custom"],"price":280,"oldPrice":350,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Lime","size":"Custom","material":"Glass","duration":"Project"},"variations":[],"stock":44,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Multilingual Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Multilingual Setup","description":"Multilingual Setup demo product.","image":""}},{"title":"PWA Install Setup","slug":"pwa-install-setup","id":"1019","sku":"PWA-INSTALL-SETUP","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","digital","clear","xs"],"price":210,"oldPrice":260,"discountLabel":"-19%","currency":"EUR","image":"https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Clear","size":"XS","material":"Digital","duration":"One-off"},"variations":[],"stock":46,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>PWA Install Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"PWA Install Setup","description":"PWA Install Setup demo product.","image":""}},{"title":"Enterprise Custom Build","slug":"enterprise-custom-build","id":"1020","sku":"ENTERPRISE-CUSTOM-","type":"variable","category":"website-packages","brand":"LPCMS","tags":["website-packages","service","cream","s"],"price":2500,"oldPrice":0,"discountLabel":"Popular","currency":"EUR","image":"https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Cream","size":"S","material":"Service","license":"Custom"},"variations":[{"id":"enterprise-custom-build-a","sku":"ENTERPRISE-CUSTOM--A","attributes":{"size":"S","colour":"Cream"},"price":2500,"oldPrice":0,"stock":24,"image":""},{"id":"enterprise-custom-build-b","sku":"ENTERPRISE-CUSTOM--B","attributes":{"size":"M","colour":"White"},"price":2550,"oldPrice":0,"stock":16,"image":""}],"stock":48,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Enterprise Custom Build is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Enterprise Custom Build","description":"Enterprise Custom Build demo product.","image":""}}]', true) ?: [];
}


function cms_demo_blog_posts(): array {
  $posts = [
    ['How this flat CMS works','how-flat-cms-works','PHP handles JSON storage and admin APIs while React renders the public website.','This CMS keeps content in JSON and renders it with React. The same index.php serves the frontend, APIs, admin login, uploads, forms, redirects, SEO metadata and app features.','https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=900&q=80'],
    ['Planning a business website that converts','planning-business-website-that-converts','Start with one clear goal, a simple path to contact, and content that answers real customer questions.','A useful business website makes the next step obvious. Lead with the customer problem, explain the service in plain language, show trust signals, and finish each important section with a clear action such as booking, calling or requesting a quote.','https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=80'],
    ['Business website or online shop?','business-website-or-online-shop','Choose Premium for a service-led site and Premium Pro when visitors need products, cart and checkout.','A business website is ideal for presenting services, enquiries, booking and company information. An e-commerce website adds catalogue, product variations, cart, shipping, discounts and checkout. Start with the flow your customers actually need.','https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80'],
    ['Writing clearer pages with AI assistance','writing-clearer-pages-with-ai','Use AI to draft, shorten, rewrite and translate content while keeping final approval with the website owner.','AI works best as an editing assistant. Give it accurate business facts, ask for a specific tone and audience, then review the result before publishing. This keeps pages consistent without inventing services, prices or promises.','https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80'],
    ['Why abandoned-cart reminders matter','why-abandoned-cart-reminders-matter','A polite reminder can bring shoppers back when they were interrupted before checkout.','Cart recovery should be helpful rather than aggressive. Save the customer cart only after an email is supplied, send a first reminder after a reasonable delay, and use a second reminder later if the order is still incomplete.','https://images.unsplash.com/photo-1512436991641-6745cdb1723f?auto=format&fit=crop&w=900&q=80'],
    ['Getting an online shop ready for launch','getting-online-shop-ready-for-launch','Check products, prices, delivery, discounts, payment instructions and order emails before opening the shop.','Before launch, test a complete purchase on mobile and desktop. Confirm variation pricing, stock behaviour, shipping costs, discount rules, checkout totals and the messages received by both the customer and administrator.','https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?auto=format&fit=crop&w=900&q=80'],
    ['Install your website like an app','install-your-website-like-an-app','A progressive web app lets visitors add the website to Android or Apple home screens.','An installable website gives customers a convenient home-screen icon without requiring a traditional app-store release. Keep the manifest icons valid, serve the site securely over HTTPS and provide simple Android and Apple install guidance.','https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&fit=crop&w=900&q=80'],
    ['Real-time booking reduces admin','real-time-booking-reduces-admin','Let visitors choose an available service, date and time and receive confirmation immediately.','A clear booking flow removes back-and-forth messages. Publish only genuine availability, capture the details needed to deliver the appointment, and send confirmation to the customer and the business as soon as the slot is reserved.','https://images.unsplash.com/photo-1506784983877-45594efa4cbe?auto=format&fit=crop&w=900&q=80'],
    ['WhatsApp chat or AI chatbot?','whatsapp-chat-or-ai-chatbot','Use WhatsApp for direct human conversation or an AI chatbot for instant answers from approved website content.','WhatsApp is a strong choice when a person should take over the conversation quickly. An AI chatbot is useful for repeated questions about services, products, pricing and booking. The best option depends on how the business wants to support visitors.','https://images.unsplash.com/photo-1525182008055-f88b95ff7980?auto=format&fit=crop&w=900&q=80'],
  ];
  $out = [];
  foreach ($posts as $i => $p) {
    $out[] = [
      'title' => $p[0],
      'slug' => $p[1],
      'date' => date('Y-m-d', strtotime('-' . ($i * 7) . ' days')),
      'image' => $p[4],
      'excerpt' => $p[2],
      'body' => '<p>' . htmlspecialchars($p[3], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>',
      'seo' => ['title' => $p[0], 'description' => $p[2], 'image' => $p[4]],
    ];
  }
  return $out;
}

function cms_i18n_pack(): array {
  static $pack = null;
  if ($pack !== null) return $pack;
  $fallback = [
    'languages' => [
      ['code'=>'en','label'=>'EN','name'=>'English'],
      ['code'=>'lv','label'=>'LV','name'=>'Latviešu'],
      ['code'=>'ru','label'=>'RU','name'=>'Русский'],
      ['code'=>'fr','label'=>'FR','name'=>'Français'],
      ['code'=>'es','label'=>'ES','name'=>'Español'],
      ['code'=>'de','label'=>'DE','name'=>'Deutsch'],
      ['code'=>'pl','label'=>'PL','name'=>'Polski'],
      ['code'=>'lt','label'=>'LT','name'=>'Lietuvių'],
      ['code'=>'et','label'=>'ET','name'=>'Eesti'],
      ['code'=>'da','label'=>'DA','name'=>'Dansk'],
      ['code'=>'no','label'=>'NO','name'=>'Norsk'],
      ['code'=>'sv','label'=>'SV','name'=>'Svenska'],
      ['code'=>'fi','label'=>'FI','name'=>'Suomi'],
      ['code'=>'is','label'=>'IS','name'=>'Íslenska'],
    ],
    'strings' => [],
    'templates' => [],
  ];
  if (!is_file(I18N_FILE)) return $pack = $fallback;
  $raw = json_decode((string)@file_get_contents(I18N_FILE), true);
  if (!is_array($raw)) return $pack = $fallback;
  return $pack = array_replace_recursive($fallback, $raw);
}

function cms_language_options(): array {
  return cms_i18n_pack()['languages'] ?? [];
}

function cms_language_codes(array $data = []): array {
  $langs = $data['settings']['languages'] ?? cms_language_options();
  $codes = [];
  foreach ($langs as $l) {
    $c = strtolower(trim((string)($l['code'] ?? '')));
    if (preg_match('/^[a-z]{2}$/', $c)) $codes[] = $c;
  }
  return array_values(array_unique($codes ?: ['en']));
}

function cms_current_language(array $data): string {
  $default = strtolower((string)($data['settings']['defaultLang'] ?? 'en'));
  $codes = cms_language_codes($data);
  $query = strtolower(trim((string)($_GET['cms_lang'] ?? '')));
  if ($query !== '' && in_array($query, $codes, true)) return $query;
  $path = trim((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? ''), '/');
  $base = trim(cms_route_prefix(), '/');
  if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) $path = trim(substr($path, strlen($base)), '/');
  $first = strtolower((string)(explode('/', $path)[0] ?? ''));
  if ($first !== '' && in_array($first, $codes, true)) return $first;
  return in_array($default, $codes, true) ? $default : ($codes[0] ?? 'en');
}

function cms_i18n_text(array $data, string $text, ?string $lang = null): string {
  if ($text === '') return $text;
  $lang = strtolower($lang ?: cms_current_language($data));
  $default = strtolower((string)($data['settings']['defaultLang'] ?? 'en'));
  if ($lang === $default || $lang === 'en') return $text;
  $pack = cms_i18n_pack();
  $map = $pack['strings'][$lang] ?? [];
  if (isset($map[$text]) && is_string($map[$text])) return $map[$text];
  return $text;
}

function cms_localized_value(array $data, array $object, string $key, ?string $lang = null, $fallback = '') {
  $lang = strtolower($lang ?: cms_current_language($data));
  $default = strtolower((string)($data['settings']['defaultLang'] ?? 'en'));
  if ($lang !== $default && isset($object['i18n'][$lang]) && is_array($object['i18n'][$lang]) && array_key_exists($key, $object['i18n'][$lang])) {
    return $object['i18n'][$lang][$key];
  }
  $value = array_key_exists($key, $object) ? $object[$key] : $fallback;
  if (is_string($value)) return cms_i18n_text($data, $value, $lang);
  if (is_array($value) && $lang !== $default) {
    return array_map(static fn($v) => is_string($v) ? cms_i18n_text($data, $v, $lang) : $v, $value);
  }
  return $value;
}

function cms_language_prefix(array $data, ?string $lang = null): string {
  $lang = strtolower($lang ?: cms_current_language($data));
  $default = strtolower((string)($data['settings']['defaultLang'] ?? 'en'));
  return ($lang !== '' && $lang !== $default) ? '/' . $lang : '';
}

function cms_language_home_path(array $data, ?string $lang = null): string {
  $prefix = cms_language_prefix($data, $lang);
  $path = $prefix . '/';
  return $path === '' ? '/' : $path;
}

function cms_default_data(): array {
  return [
    'version' => CMS_VERSION,
    'settings' => [
      'siteName' => 'Flat React JSON CMS',
      'baseUrl' => '',
      'defaultLang' => 'en',
      'languages' => cms_language_options(),
      'localization' => [
        'enabled' => true,
        'autoDetect' => true,
        'persistChoice' => true,
        'urlPrefixes' => true,
        'defaultAtRoot' => true,
      ],
      'branding' => [
        'favicon' => '',
        'placeholderMark' => 'LP',
      ],
      'mailer' => [
        'mode' => 'mail',
        'fromName' => 'LPCMS',
        'fromEmail' => '',
        'replyTo' => '',
        'smtp' => [
          'host' => '',
          'port' => 587,
          'encryption' => 'tls',
          'username' => '',
          'password' => '',
          'timeout' => 12,
          'verifyPeer' => true,
        ],
      ],
      'commerceIntegrations' => [
        'checkout' => [
          'mode' => 'order-form',
          'paymentLink' => '',
          'adapterUrl' => '',
          'bearerToken' => '',
        ],
        'webhook' => [
          'enabled' => false,
          'url' => '',
          'bearerToken' => '',
        ],
        'providers' => [],
      ],
      'seo' => [
        'title' => 'Flat React JSON CMS - Fast one page CMS',
        'description' => 'A fast single-file PHP and JSON headless CMS rendered by React.',
        'keywords' => 'flat file cms, react cms, json cms, bootstrap cms',
        'image' => '/assets/img/og.jpg',
        'canonical' => '',
        'robots' => 'index,follow'
      ],
      'htaccess' => [
        'autoSync' => true,
        'prettyUrls' => true,
        'forceHttps' => false,
        'removeWww' => false,
        'assetCacheDays' => 365,
        'protectJson' => true
      ],
      'theme' => [
        'brand' => '#0d6efd',
        'brand2' => '#6610f2',
        'accent' => '#20c997',
        'text' => '#1f2937',
        'muted' => '#6b7280',
        'bg' => '#ffffff',
        'softBg' => '#f6f8fb',
        'radius' => '22px',
        'grid' => 'container',
        'font' => 'Inter, system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif',
        'customScss' => '',
        'customCss' => ''
      ],
      'themeManager' => [
        'active' => 'lpcms-default',
        'loadImportedCss' => true,
        'loadImportedJs' => false,
        'installed' => [],
      ],
      'headless' => [
        'enabled' => true,
        'restEnabled' => true,
        'graphqlEnabled' => true,
        'corsOrigins' => [],
        'cacheSeconds' => 60,
      ],
      'libraries' => [
        'bootstrap' => true,
        'bootstrapIcons' => true,
        'swiper' => false,
        'aos' => true,
        'glightbox' => false,
        'sassBuilder' => false,
      ],
      'analytics' => [
        'ga4Id' => '',
        'loadAfterCookieConsent' => true
      ],
      'cookies' => [
        'enabled' => true,
        'message' => 'We use cookies for analytics and site functionality.',
        'acceptText' => 'Accept',
        'declineText' => 'Decline'
      ],
      'hcaptcha' => [
        'enabled' => false,
        'siteKey' => '',
        'secret' => ''
      ],
      'mediaSources' => [
        'provider' => 'free',
        'free' => ['enabled' => true],
        'openverse' => ['enabled' => true],
        'commons' => ['enabled' => true],
        // Pexels, Pixabay and Unsplash use a configurable no-key proxy by default.
        // A provider API key is optional and, when present, switches that provider
        // to its official API directly.
        'proxy' => [
          'enabled' => true,
          'baseUrl' => 'https://proxy.getinstantimages.com/api/',
          'schemaVersion' => '7.2.0',
        ],
        'pexels' => ['enabled' => true, 'apiKey' => ''],
        'pixabay' => ['enabled' => true, 'apiKey' => ''],
        'unsplash' => ['enabled' => true, 'apiKey' => ''],
      ],
      'ai' => [
        'enabled' => false,
        'accessTier' => 'free',
        'clientName' => '',
        'clientEmail' => '',
        'provider' => 'openai',
        'model' => '',
        'apiKey' => '',
        'endpoint' => '',
        'systemPrompt' => 'You are a concise website content assistant. Return polished website copy only unless the user asks for explanations.',
      ],
      'sitePlan' => [
        'selected' => 'premium',
        'platformDomain' => 'dev4.click',
        'subdomain' => 'yourbusiness',
        'customDomain' => '',
        'premiumGbp' => '3.50',
        'premiumEur' => '4.00',
        'premiumProGbp' => '6.50',
        'premiumProEur' => '7.00',
      ],
      'abandonedCart' => [
        'enabled' => false,
        'firstDelayMinutes' => 60,
        'secondDelayMinutes' => 1440,
        'firstSubject' => 'You left something in your cart',
        'firstMessage' => "You still have items waiting in your cart. Use the link below to return and finish checkout.",
        'secondSubject' => 'Your cart is still waiting',
        'secondMessage' => "A quick reminder that your saved cart is still available. Return to the shop whenever you are ready.",
        'cronKey' => '',
        'carts' => [],
      ],
      'demoIntakeAdmin' => [
        'notifyEmail' => '',
        'billingSubject' => 'Your Digital Pulse website plan is ready',
        'billingMessage' => 'Your website demo has been approved and prepared. Please use the billing details below to activate the selected monthly plan.',
        'paymentInstructions' => 'Add your payment link, bank transfer details, or other billing instructions here.',
        'invoicePrefix' => 'DP',
        'dueDays' => 7,
        'requests' => [],
      ],
      'sectionOrder' => ['hero','about','adminShowcase','pricing','stats','features','services','process','gallery','testimonials','products','demoIntake','blog','faq','contact','cta','cards','contentList','team','growth','industries','logos','booking'],
      'sectionPatterns' => [
        'hero' => '1', 'about' => '1', 'stats' => '1', 'features' => '1', 'services' => '1', 'cards' => '1', 'contentList' => '1', 'logos' => '1', 'process' => '1', 'gallery' => '1',
        'testimonials' => '1', 'products' => '1', 'booking' => '1', 'adminShowcase' => '1', 'pricing' => '1', 'demoIntake' => '1', 'blog' => '1', 'faq' => '1', 'contact' => '1', 'cta' => '1'
      ],
      'app' => [
        'enabled' => true,
        'name' => 'LPCMS',
        'shortName' => 'LPCMS',
        'description' => 'Install this website as a mobile app.',
        'themeColor' => '#0d6efd',
        'backgroundColor' => '#ffffff',
        'icon' => '/assets/img/app-icon-512.png',
        'icon192' => '/assets/img/app-icon-192.png',
        'icon512' => '/assets/img/app-icon-512.png',
        'appleIcon' => '/assets/img/apple-touch-icon.png',
        'androidInstallUrl' => '/?install=android',
        'appleInstallUrl' => '/?install=apple',
        'footerBadgesEnabled' => true,
        'offlineMessage' => 'You are offline. Please reconnect to continue editing.'
      ],
      'cache' => [
        'enabled' => true,
        'htmlTtl' => 300,
        'autoClearOnSave' => true,
        'maxAgeDays' => 14
      ],
      'media' => [
        'maxImageWidth' => 1920,
        'boxWidth' => 1920,
        'boxHeight' => 1920,
        'boxPreset' => 'large',
        'avifQuality' => 72,
        'browserAvif' => true,
        'strictAvif' => true,
        'keepOriginals' => false
      ],
      'users' => [
        'items' => [
          ['email' => 'raivis@63.lv', 'name' => 'Super administrator', 'role' => 'super', 'active' => true],
        ]
      ],
      'forms' => [
        'emailTo' => '',
        'storeSubmissions' => true,
        'submissions' => []
      ]
    ],
    'header' => [
      'logoText' => 'LPCMS',
      'logoImage' => '',
      'sticky' => true,
      'menu' => [
        ['label' => 'Home', 'href' => '#home', 'target' => '_self', 'children' => []],
        ['label' => 'About', 'href' => '#about', 'target' => '_self', 'children' => []],
        ['label' => 'Services', 'href' => '#services', 'target' => '_self', 'children' => []],
        ['label' => 'Process', 'href' => '#process', 'target' => '_self', 'children' => []],
        ['label' => 'Pricing', 'href' => '#pricing', 'target' => '_self', 'children' => []],
        ['label' => 'Gallery', 'href' => '#gallery', 'target' => '_self', 'children' => []],
        ['label' => 'Blog', 'href' => '#blog', 'target' => '_self', 'children' => []],
        ['label' => 'Contact', 'href' => '#contact', 'target' => '_self', 'children' => []],
      ]
    ],
    'hero' => [
      'enabled' => true,
      'slides' => [
        [
          'eyebrow' => 'One file CMS',
          'title' => 'Flat Headless React CMS powered by JSON',
          'text' => 'Inline edit content, manage SEO, build sections, galleries, blogs, products, and forms without a database.',
          'buttonText' => 'Start editing',
          'buttonHref' => '#about',
          'mediaType' => 'video',
          'video' => '/assets/video/hero-cms-demo-v4.mp4',
          'videoPoster' => '/assets/video/hero-cms-demo-poster-v4.avif',
          'videoAutoplay' => true,
          'videoLoop' => true,
          'image' => '',
          'bg' => '',
          'seoH1' => true
        ]
      ]
    ],
    'about' => [
      'enabled' => true,
      'preheader' => 'About',
      'title' => 'About us',
      'subtitle' => 'A clean Bootstrap-style landing page with inline admin UX.',
      'image' => '',
      'body' => '<p>Login securely, then click text or image blocks directly on the page. Images can be clicked or dragged into place. LPCMS converts to AVIF when available, otherwise it safely saves the original JPG/PNG/WebP file.</p>'
    ],
    'stats' => [
      'enabled' => true,
      'preheader' => 'Highlights',
      'title' => 'Numbers that matter',
      'subtitle' => 'Small animated counters for trust, speed, and proof points.',
      'items' => [
        ['number' => '100%', 'label' => 'Flat-file JSON content'],
        ['number' => '1', 'label' => 'PHP application file'],
        ['number' => '0', 'label' => 'Database tables'],
        ['number' => 'SEO', 'label' => 'Clean URLs and meta controls'],
      ]
    ],
    'features' => [
      'enabled' => true,
      'preheader' => 'Features',
      'title' => 'Built for fast landing pages',
      'subtitle' => 'Reusable editable feature rows with icons, text, and actions.',
      'image' => '',
      'items' => [
        ['title' => 'Visual inline editing', 'text' => 'Click headings, paragraphs, cards, and modal content directly on the page.', 'icon' => 'bi bi-magic', 'image' => ''],
        ['title' => 'Product catalogue mode', 'text' => 'Run products like blog posts with modals, prices, discounts, galleries, buy links, and quote baskets.', 'icon' => 'bi bi-bag-check', 'image' => ''],
        ['title' => 'Performance first', 'text' => 'Optional libraries, asset cache headers, compression rules, and JSON-only content keep the page lightweight.', 'icon' => 'bi bi-speedometer2', 'image' => ''],
      ]
    ],
    'cards' => [
      'enabled' => false,
      'preheader' => 'Highlights',
      'title' => 'Image cards',
      'subtitle' => 'Flexible visual cards for services, categories, offers, or featured content.',
      'columns' => 3,
      'items' => [
        ['title' => 'Card one', 'text' => 'Add a short description and optional image.', 'image' => '', 'icon' => 'lucide:layout-template', 'buttonText' => 'Learn more', 'buttonHref' => '#demo-request'],
        ['title' => 'Card two', 'text' => 'Use cards to guide visitors to the next step.', 'image' => '', 'icon' => 'tabler:route', 'buttonText' => 'Learn more', 'buttonHref' => '#demo-request'],
        ['title' => 'Card three', 'text' => 'The layout stays responsive automatically.', 'image' => '', 'icon' => 'material-symbols:responsive-layout', 'buttonText' => 'Learn more', 'buttonHref' => '#contact'],
      ]
    ],
    'contentList' => [
      'enabled' => false,
      'preheader' => 'Details',
      'title' => 'Content list',
      'subtitle' => 'A clean structured list for services, team members, products, or benefits.',
      'items' => [
        ['title' => 'Structured content item', 'text' => 'Keep information aligned and easy to scan on desktop and mobile.', 'image' => '', 'icon' => 'lucide:list-checks', 'buttonText' => 'Read more', 'buttonHref' => '#contact']
      ]
    ],
    'logos' => [
      'enabled' => false,
      'preheader' => 'Trusted by',
      'title' => 'Trusted by',
      'subtitle' => 'Show client, partner, supplier, or certification logos in a balanced responsive row.',
      'items' => [
        ['label' => 'Partner logo', 'image' => '', 'href' => '#']
      ]
    ],
    'process' => [
      'enabled' => true,
      'preheader' => 'How it works',
      'title' => 'How it works',
      'subtitle' => 'A simple editable process timeline.',
      'items' => [
        ['title' => 'Upload', 'text' => 'Upload index.php and open the site once to create JSON and assets.', 'icon' => 'bi bi-cloud-arrow-up', 'image' => ''],
        ['title' => 'Edit', 'text' => 'Login securely and edit content inline or from the admin tabs.', 'icon' => 'bi bi-pencil-square', 'image' => ''],
        ['title' => 'Publish', 'text' => 'Save JSON, sync .htaccess, and publish fast SEO-ready pages.', 'icon' => 'bi bi-rocket-takeoff', 'image' => ''],
      ]
    ],
    'adminShowcase' => [
      'enabled' => true,
      'preheader' => 'Admin & editing',
      'title' => 'Manage your website without touching code',
      'subtitle' => 'Edit content on the page or in Admin. Daily changes are simple and technical settings stay protected.',
      'superAdminTitle' => 'Super Admin control centre',
      'superAdminText' => 'Super Admin keeps advanced website controls in one protected panel: themes, products, booking, chat, media, SEO, plans, demos, mail, APIs, cache, redirects and server tools.',
      'superAdminImage' => '/assets/img/admin/super-admin-controls.avif',
      'superAdminItems' => [
        ['title' => 'Design & themes', 'text' => 'Switch themes, adjust design tokens and control imported theme assets.', 'icon' => 'bi bi-palette2'],
        ['title' => 'Commerce', 'text' => 'Manage products, checkout, shipping, discounts and recovery tools.', 'icon' => 'bi bi-bag-check'],
        ['title' => 'Booking & chat', 'text' => 'Configure booking services, AI chatbot or WhatsApp visitor support.', 'icon' => 'bi bi-calendar2-check'],
        ['title' => 'Media & icons', 'text' => 'Upload AVIF media, import free images and choose open-source icons.', 'icon' => 'bi bi-images'],
        ['title' => 'SEO & languages', 'text' => 'Control metadata, multilingual content and shareable language URLs.', 'icon' => 'bi bi-translate'],
        ['title' => 'Plans & client workflow', 'text' => 'Load demos, approve client requests and send billing emails.', 'icon' => 'bi bi-receipt-cutoff'],
        ['title' => 'Mail & integrations', 'text' => 'Protect SMTP, API keys, webhooks and provider-specific settings.', 'icon' => 'bi bi-plug'],
        ['title' => 'Technical tools', 'text' => 'Manage cache, redirects, headless APIs, app settings and server checks.', 'icon' => 'bi bi-sliders2-vertical'],
      ],
      'domainTitle' => 'Free subdomain or your own domain',
      'domainText' => 'Start free with {custom_name}.dev4.click, or connect a domain from Name.com, NIC.LV or any other domain provider.',
      'domainOptions' => [
        ['title' => 'Free subdomain', 'text' => 'Choose your name and publish without buying a domain.', 'example' => '{custom_name}.dev4.click', 'icon' => 'bi bi-globe2'],
        ['title' => 'Own domain', 'text' => 'Connect any domain you already own.', 'example' => 'yourcompany.com · yourcompany.lv · any TLD', 'icon' => 'bi bi-link-45deg'],
      ],
      'items' => [
        ['title' => 'Site & content', 'text' => 'Manage site identity, languages, sections and app settings.', 'icon' => 'bi bi-window-stack'],
        ['title' => 'Content & media', 'text' => 'Edit text, sections and images with inline editing or Admin fields.', 'icon' => 'bi bi-images'],
        ['title' => 'Shop & integrations', 'text' => 'Manage products, booking, chat, checkout and integrations.', 'icon' => 'bi bi-bag-check'],
      ],
      'tableTitle' => 'What can you manage?',
      'tableSubtitle' => 'Editors get simple controls. Super Admin keeps technical settings protected.',
      'rows' => [
        ['title' => 'Text, images and sections', 'editor' => true, 'super' => true, 'ease' => 'Click or use simple fields'],
        ['title' => 'Blog, SEO and languages', 'editor' => true, 'super' => true, 'ease' => 'Edit each language with its own URL'],
        ['title' => 'Products and e-commerce', 'editor' => true, 'super' => true, 'ease' => 'Manage cards, prices, stock and variations'],
        ['title' => 'Booking and AI chat / WhatsApp', 'editor' => true, 'super' => true, 'ease' => 'Switch on and edit content'],
        ['title' => 'Domains and deployment', 'editor' => false, 'super' => true, 'ease' => 'Free subdomain or connect your domain'],
        ['title' => 'SMTP, API keys and integrations', 'editor' => false, 'super' => true, 'ease' => 'Protected technical settings'],
        ['title' => 'Plans, demos and client billing', 'editor' => false, 'super' => true, 'ease' => 'Super Admin approval workflow'],
      ]
    ],
    'pricing' => [
      'enabled' => true,
      'preheader' => 'Pricing',
      'title' => 'Simple plans for your website',
      'subtitle' => 'Choose a business website or an e-commerce website. Both plans include the core editor and app install option.',
      'items' => [
        ['title' => 'Premium', 'badge' => 'Business website', 'price' => '£3.50', 'secondaryPrice' => '€4', 'period' => '/ month', 'text' => 'For a professional business website without shop checkout.', 'features' => ['Editable website sections', 'Blog and SEO controls', 'Contact forms', 'Theme and media tools', 'Install on Android & Apple (PWA)', 'Free {custom_name}.dev4.click subdomain or your own domain'], 'buttonText' => 'Choose Premium', 'buttonHref' => '#contact'],
        ['title' => 'Premium Pro', 'badge' => 'E-commerce website', 'price' => '£6.50', 'secondaryPrice' => '€7', 'period' => '/ month', 'text' => 'For selling products online with the full commerce toolkit.', 'features' => ['Everything in Premium', 'Product catalogue and variations', 'Shopping cart and quote basket', 'Checkout, shipping and discount options', 'Abandoned-cart reminders', 'Booking and AI chatbot modules'], 'buttonText' => 'Choose Premium Pro', 'buttonHref' => '#contact'],
      ]
    ],
    'faq' => [
      'enabled' => true,
      'preheader' => 'FAQ',
      'title' => 'Frequently asked questions',
      'subtitle' => 'Bootstrap-style accordion content managed from JSON.',
      'items' => [
        ['question' => 'Can LPCMS run without a database?', 'answer' => 'Yes. Content, settings, SEO, products, redirects, and forms are stored in JSON.'],
        ['question' => 'Can products be quote-only?', 'answer' => 'Yes. Set product price to 0 and the buy button is hidden while Add to quote stays available.'],
        ['question' => 'Can I disable libraries?', 'answer' => 'Yes. Admin library settings let you connect or disconnect optional frontend libraries.'],
      ]
    ],
    'services' => [
      'enabled' => true,
      'preheader' => 'Services',
      'title' => 'Services',
      'subtitle' => 'Cards with AJAX-style load more.',
      'batch' => 3,
      'showButton' => true,
      'buttonText' => 'Load more services',
      'items' => [
        ['title' => 'Headless JSON content', 'text' => 'All editable content is stored in data.json.', 'icon' => 'bi bi-braces', 'image' => '', 'buttonText' => 'Read more', 'buttonHref' => '#contact'],
        ['title' => 'Inline editing', 'text' => 'Edit headings, text, cards, and HTML blocks directly on the page.', 'icon' => 'bi bi-pencil-square', 'image' => '', 'buttonText' => 'Read more', 'buttonHref' => '#contact'],
        ['title' => 'SEO controls', 'text' => 'Set global and post-level meta title, descriptions, OG images, canonical URLs, and JSON-LD.', 'icon' => 'bi bi-search-heart', 'image' => '', 'buttonText' => 'Read more', 'buttonHref' => '#contact'],
      ]
    ],
    'gallery' => [
      'enabled' => true,
      'preheader' => 'Gallery',
      'title' => 'Gallery',
      'subtitle' => 'Directory-based photos from assets/gallery or uploaded files.',
      'homeLimit' => 6,
      'batch' => 3,
      'showButton' => true,
      'buttonText' => 'Load more photos',
      'items' => [
        ['src' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=900&q=80', 'alt' => 'Modern office workspace'],
        ['src' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=900&q=80', 'alt' => 'Team planning meeting'],
        ['src' => 'https://images.unsplash.com/photo-1521737852567-6949f3f9f2b5?auto=format&fit=crop&w=900&q=80', 'alt' => 'Creative team collaboration'],
        ['src' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=900&q=80', 'alt' => 'Bright studio office'],
        ['src' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=900&q=80', 'alt' => 'Contemporary business interior'],
        ['src' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=900&q=80', 'alt' => 'Open workspace and team'],
        ['src' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=900&q=80', 'alt' => 'Team working together'],
        ['src' => 'https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80', 'alt' => 'Business consultation'],
        ['src' => 'https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=900&q=80', 'alt' => 'Project presentation meeting'],
      ]
    ],
    'testimonials' => [
      'enabled' => true,
      'preheader' => 'Testimonials',
      'title' => 'Testimonials',
      'subtitle' => 'Customer quote cards with load more.',
      'homeLimit' => 6,
      'batch' => 3,
      'showButton' => true,
      'buttonText' => 'Load more testimonials',
      'items' => [
        ['name' => 'Olivia Carter', 'role' => 'Founder, North & Co.', 'text' => 'The website was simple to review and update, and the whole launch process felt organised from the first draft.', 'photo' => ''],
        ['name' => 'Daniel Wright', 'role' => 'Operations Manager', 'text' => 'We can manage everyday content ourselves while the technical settings stay safely out of the way.', 'photo' => ''],
        ['name' => 'Sophie Bennett', 'role' => 'Studio Director', 'text' => 'The mobile layout is clean, fast and much easier for our customers to use than our previous site.', 'photo' => ''],
        ['name' => 'James Patel', 'role' => 'Retail Owner', 'text' => 'Products, enquiries and quote requests are all in one place now, which has made daily administration much simpler.', 'photo' => ''],
        ['name' => 'Amelia Green', 'role' => 'Marketing Lead', 'text' => 'Inline editing means our team can keep promotions and landing page content current without waiting for a developer.', 'photo' => ''],
        ['name' => 'Noah Evans', 'role' => 'Managing Director', 'text' => 'The demo gave us a clear picture of the finished website before approval, which made the decision process straightforward.', 'photo' => ''],
        ['name' => 'Emily Roberts', 'role' => 'Consultant', 'text' => 'Booking confirmations and the website chat options give clients a much smoother way to get in touch.', 'photo' => ''],
        ['name' => 'Lucas Martin', 'role' => 'E-commerce Manager', 'text' => 'The catalogue controls are practical and the checkout experience works well across desktop and mobile.', 'photo' => ''],
        ['name' => 'Grace Thompson', 'role' => 'Business Owner', 'text' => 'It feels like a professional website builder without making the client-facing admin unnecessarily complicated.', 'photo' => ''],
      ]
    ],
    'products' => [
      'enabled' => true,
      'preheader' => 'Products',
      'title' => 'Your Products',
      'subtitle' => 'Ajax catalogue with product search, categories, variation filters, modal details, cart, quote basket, and direct debit/bank transfer order flow.',
      'mode' => 'catalog',
      'shopEnabled' => true,
      'defaultPurchaseMode' => 'quote',
      'catalogEnabled' => true,
      'searchEnabled' => true,
      'filterEnabled' => true,
      'homeLimit' => 8,
      'batch' => 8,
      'desktopColumns' => 4,
      'tabletColumns' => 2,
      'mobileColumns' => 1,
      'showButton' => true,
      'buttonText' => 'Load more products',
      'showBuyButton' => true,
      'showQuoteButton' => true,
      'buyButtonText' => 'Buy',
      'quoteButtonText' => 'Add to quote',
      'basketEnabled' => true,
      'basketTitle' => 'Quote basket',
      'basketButtonText' => 'Send quote request',
      'cartEnabled' => true,
      'cartTitle' => 'Shopping cart',
      'cartButtonText' => 'Checkout',
      'checkoutButtonText' => 'Send order',
      'currency' => 'EUR',
      'shippingMethods' => [
        ['id' => 'pickup', 'label' => 'Local pickup', 'price' => 0, 'enabled' => true],
        ['id' => 'standard', 'label' => 'Standard delivery', 'price' => 4.99, 'enabled' => true],
      ],
      'discountCodes' => [
        ['code' => 'WELCOME10', 'type' => 'percent', 'value' => 10, 'enabled' => false],
      ],
      'categories' => [
        ['slug' => 'website-packages', 'title' => 'Website Packages'],
        ['slug' => 'custom-services', 'title' => 'Custom Services'],
        ['slug' => 'seo-tools', 'title' => 'SEO & Performance'],
        ['slug' => 'subscriptions', 'title' => 'Subscriptions'],
        ['slug' => 'merch', 'title' => 'Merchandise'],
        ['slug' => 'digital-products', 'title' => 'Digital Products'],
      ],
      'attributeFilters' => [
        ['key' => 'colour', 'label' => 'Colour', 'options' => ['Blue', 'Black', 'White', 'Navy', 'Purple', 'Green', 'Red', 'Gold', 'Orange', 'Grey', 'Brown', 'Silver', 'Pink', 'Sky', 'Beige', 'Rose', 'Yellow', 'Lime', 'Clear', 'Cream']],
        ['key' => 'size', 'label' => 'Size', 'options' => ['XS', 'S', 'M', 'L', 'XL', 'Custom']],
        ['key' => 'material', 'label' => 'Material', 'options' => ['Digital', 'Service', 'Cotton', 'Leather', 'Aluminum', 'Glass']],
        ['key' => 'license', 'label' => 'License', 'options' => ['Standard', 'Business', 'Enterprise', 'Custom']],
        ['key' => 'duration', 'label' => 'Duration', 'options' => ['One-off', 'Monthly', 'Project']],
      ],
      'payment' => [
        'directDebit' => [
          'enabled' => true,
          'title' => 'Direct bank transfer / direct debit',
          'instructions' => 'Submit the order form and we will confirm payment details. Add your order reference to the payment message.',
          'accountName' => 'Your Company Ltd',
          'iban' => '',
          'swift' => '',
          'bankName' => '',
          'referencePrefix' => 'LPCMS'
        ]
      ],
      'items' => cms_demo_products(),
    ],
    'booking' => [
      'enabled' => false,
      'preheader' => 'Booking',
      'title' => 'Book an appointment',
      'subtitle' => 'Choose a service, date and available time. Confirmation appears immediately and is sent by email.',
      'services' => [
        ['id' => 'consultation', 'title' => 'Consultation', 'durationMinutes' => 60, 'price' => 0, 'enabled' => true],
        ['id' => 'demo', 'title' => 'Product demo', 'durationMinutes' => 30, 'price' => 0, 'enabled' => true],
      ],
      'weeklySlots' => [
        'monday' => ['09:00','10:30','12:00','14:00','15:30','17:00'],
        'tuesday' => ['09:00','10:30','12:00','14:00','15:30','17:00'],
        'wednesday' => ['09:00','10:30','12:00','14:00','15:30','17:00'],
        'thursday' => ['09:00','10:30','12:00','14:00','15:30','17:00'],
        'friday' => ['09:00','10:30','12:00','14:00','15:30'],
        'saturday' => [],
        'sunday' => [],
      ],
      'confirmationText' => 'Booking confirmed. Check your email for the details.',
      'technical' => [
        'notifyEmail' => '',
        'webhookUrl' => '',
      ],
      'bookings' => [],
    ],
    'chatbot' => [
      'enabled' => true,
      'mode' => 'chatbot',
      'position' => 'left',
      'buttonText' => 'Ask AI',
      'title' => 'How can we help?',
      'welcome' => 'Hi! Ask me about our services, products, prices, booking, or contact details.',
      'placeholder' => 'Type your question…',
      'fallback' => 'I could not find a confident answer in the website content. Please use the contact form and the team will help you.',
      'suggestedQuestions' => ['What services do you offer?', 'How much does it cost?', 'Can I book an appointment?', 'How do I contact you?'],
      'whatsapp' => [
        'number' => '',
        'buttonText' => 'WhatsApp',
        'message' => 'Hi! I would like to ask about your services.',
      ],
      'technical' => [
        'aiEnabled' => true,
        'maxContextChars' => 9000,
        'systemPrompt' => 'You are the website assistant. Answer only from the supplied website knowledge, keep answers concise, do not invent facts, reply in the visitor language, and suggest booking or contact when useful.',
      ],
    ],
    'blog' => [
      'enabled' => true,
      'preheader' => 'Blog',
      'title' => 'Blog',
      'subtitle' => 'Nine demo posts with six shown first and three more per click.',
      'homeLimit' => 6,
      'batch' => 3,
      'showButton' => true,
      'buttonText' => 'Load more posts',
      'items' => cms_demo_blog_posts(),
    ],
    'demoIntake' => [
      'enabled' => true,
      'preheader' => 'Free demo',
      'title' => 'Get a ready website demo',
      'subtitle' => 'Complete four short steps with your business details, content and logo. A private draft is generated for Super Admin review before anything is published.',
      'buttonText' => 'Create my demo request',
      'thankYou' => 'Thank you. Your website draft was created and sent for administrator review.',
      'privacyText' => 'I confirm that I am allowed to provide this business information and logo for preparing my website demo.',
      'plans' => [
        ['id' => 'premium', 'title' => 'Premium', 'description' => 'Business website', 'price' => '£3.50 / €4 per month'],
        ['id' => 'premium-pro', 'title' => 'Premium Pro', 'description' => 'E-commerce website', 'price' => '£6.50 / €7 per month'],
      ],
      'steps' => ['Your business', 'Website content', 'Branding & tools', 'Review'],
    ],
    'pages' => [
      'enabled' => true,
      'items' => [
        [
          'title' => 'Privacy Policy',
          'slug' => 'privacy-policy',
          'excerpt' => 'Privacy policy page managed from JSON.',
          'body' => '<p>Update this privacy policy from the JSON editor or through future page block controls.</p>',
          'seo' => ['title' => 'Privacy Policy', 'description' => 'Privacy policy for this website.', 'image' => '']
        ]
      ]
    ],
    'contact' => [
      'enabled' => true,
      'preheader' => 'Contact',
      'title' => 'Contact us',
      'subtitle' => 'Map, details, modal form, and social share/follow.',
      'mapEmbed' => 'https://www.google.com/maps?q=London%2C%20UK&output=embed',
      'details' => [
        'address' => 'London, UK',
        'phone' => '+44 00 000 000',
        'email' => 'hello@example.com',
        'hours' => 'Mon-Fri 09:00-18:00'
      ],
      'formButtonText' => 'Open contact form'
    ],
    'cta' => [
      'enabled' => true,
      'title' => 'Ready to launch a fast flat-file site?',
      'text' => 'Open the admin panel, change the content, upload images, and publish.',
      'buttonText' => 'Open contact form'
    ],
    'footer' => [
      'copy' => 'Copyright {year} FlatCMS. All rights reserved.',
      'menu' => [
        ['label' => 'Privacy', 'href' => '/page/privacy-policy'],
        ['label' => 'Contact', 'href' => '#contact']
      ],
      'social' => [
        ['label' => 'Facebook', 'url' => '#', 'icon' => 'bi bi-facebook'],
        ['label' => 'Instagram', 'url' => '#', 'icon' => 'bi bi-instagram'],
        ['label' => 'LinkedIn', 'url' => '#', 'icon' => 'bi bi-linkedin'],
      ]
    ],
    'redirects' => [
      ['from' => '/old-page', 'to' => '/', 'status' => 301, 'enabled' => false]
    ],
    'embeds' => [],
  ];
}

function cms_asset_dirs(): array {
  return [ASSETS_DIR, UPLOAD_DIR, GALLERY_DIR, IMG_DIR, CSS_DIR, JS_DIR, FONTS_DIR, DOCS_DIR, VIDEO_DIR, CACHE_DIR, THEMES_DIR];
}

function cms_route_prefix(): string {
  $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
  return $dir === '' ? '/' : $dir . '/';
}


function cms_asset_public_url(string $targetDir, string $name): string {
  $base = rtrim(cms_route_prefix(), '/');
  return ($base !== '' ? $base : '') . '/assets/' . rawurlencode(basename($targetDir)) . '/' . rawurlencode($name);
}

function cms_resolve_remote_url(string $baseUrl, string $location): string {
  $location = trim($location);
  if ($location === '') return '';
  if (preg_match('#^https?://#i', $location)) return $location;
  $base = parse_url($baseUrl);
  $scheme = (string)($base['scheme'] ?? 'https');
  $host = (string)($base['host'] ?? '');
  if ($host === '') return '';
  $port = isset($base['port']) ? ':' . (int)$base['port'] : '';
  if (str_starts_with($location, '//')) return $scheme . ':' . $location;
  if (str_starts_with($location, '/')) return $scheme . '://' . $host . $port . $location;
  $path = (string)($base['path'] ?? '/');
  $dir = preg_replace('#/[^/]*$#', '/', $path) ?: '/';
  $combined = $dir . $location;
  $parts = [];
  foreach (explode('/', $combined) as $part) {
    if ($part === '' || $part === '.') continue;
    if ($part === '..') { array_pop($parts); continue; }
    $parts[] = $part;
  }
  return $scheme . '://' . $host . $port . '/' . implode('/', $parts);
}

function cms_htaccess_settings(array $data): array {
  return array_replace([
    'autoSync' => true,
    'prettyUrls' => true,
    'forceHttps' => false,
    'removeWww' => false,
    'assetCacheDays' => 365,
    'protectJson' => true,
  ], $data['settings']['htaccess'] ?? []);
}

function cms_root_htaccess(array $data): string {
  $s = cms_htaccess_settings($data);
  $cacheDays = max(1, min(365, (int)($s['assetCacheDays'] ?? 365)));
  $cacheSeconds = $cacheDays * 86400;
  $forceHttps = !empty($s['forceHttps']) ? "RewriteCond %{HTTPS} !=on\nRewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]\n" : "# To force HTTPS from the CMS admin, enable settings.htaccess.forceHttps.\n";
  $removeWww = !empty($s['removeWww']) ? "RewriteCond %{HTTP_HOST} ^www\\.(.+)$ [NC]\nRewriteRule ^ https://%1%{REQUEST_URI} [L,R=301]\n" : "# To redirect www to non-www, enable settings.htaccess.removeWww.\n";
  $protectJson = !empty($s['protectJson']) ? <<<'TXT'
<FilesMatch "(^\.|data\.json$|.*\.tmp$|.*\.bak$|.*\.old$|composer\.(json|lock)$|package(-lock)?\.json$|yarn\.lock$|vite\.config\.|webpack\.config\.|phpunit\.)">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</FilesMatch>
TXT : '';

  return rtrim("# BEGIN FlatCMS\n" . <<<'HTA'
# Managed by index.php. Custom rules can be placed before BEGIN or after END.
Options -Indexes
DirectoryIndex index.php
FileETag None

<IfModule mod_negotiation.c>
  Options -MultiViews
</IfModule>

<IfModule mod_rewrite.c>
  RewriteEngine On
HTA
  . "\n" . $forceHttps . $removeWww . <<<'HTA'

  # PWA app endpoints served by index.php.
  RewriteRule ^manifest\.webmanifest$ index.php?pwa=manifest [L,QSA]
  RewriteRule ^service-worker\.js$ index.php?pwa=sw [L,QSA]

  # Repair legacy relative asset URLs saved before v0.11.12. New uploads are
  # root-relative, but these rules keep older data working on /lv/, /de/blog/..., etc.
  RewriteRule ^([A-Za-z]{2})/assets/(.*)$ assets/$2 [L,NC]
  RewriteRule ^([A-Za-z]{2})/(blog|posts|product|products|page|pages)/assets/(.*)$ assets/$3 [L,NC]
  RewriteRule ^([A-Za-z]{2})/(blog|posts|product|products|page|pages)/[A-Za-z0-9-]+/assets/(.*)$ assets/$3 [L,NC]
  RewriteRule ^(blog|posts|product|products|page|pages)/assets/(.*)$ assets/$2 [L,NC]
  RewriteRule ^(blog|posts|product|products|page|pages)/[A-Za-z0-9-]+/assets/(.*)$ assets/$2 [L,NC]

  # Let real files and directories load directly.
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]

  # Language-prefixed public URLs. English remains canonical at /; /en/ is accepted as an alias.
  RewriteRule ^([A-Za-z]{2})/?$ index.php?cms_lang=$1 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/blog/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=blog&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/posts/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=blog&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/product/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=products&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/products/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=products&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/page/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=pages&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/pages/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=pages&slug=$2 [L,QSA,B]
  RewriteRule ^([A-Za-z]{2})/([A-Za-z0-9-]+)/?$ index.php?cms_lang=$1&cms_route=pages&slug=$2 [L,QSA,B]

  # Pretty SEO URLs for JSON content.
  RewriteRule ^blog/([A-Za-z0-9-]+)/?$ index.php?cms_route=blog&slug=$1 [L,QSA,B]
  RewriteRule ^posts/([A-Za-z0-9-]+)/?$ index.php?cms_route=blog&slug=$1 [L,QSA,B]
  RewriteRule ^product/([A-Za-z0-9-]+)/?$ index.php?cms_route=products&slug=$1 [L,QSA,B]
  RewriteRule ^products/([A-Za-z0-9-]+)/?$ index.php?cms_route=products&slug=$1 [L,QSA,B]
  RewriteRule ^page/([A-Za-z0-9-]+)/?$ index.php?cms_route=pages&slug=$1 [L,QSA,B]
  RewriteRule ^pages/([A-Za-z0-9-]+)/?$ index.php?cms_route=pages&slug=$1 [L,QSA,B]

  # Optional short page URL: /about-us -> JSON page slug about-us.
  RewriteRule ^([A-Za-z0-9-]+)/?$ index.php?cms_route=pages&slug=$1 [L,QSA,B]

  # Fallback to the React shell.
  RewriteRule . index.php [L,QSA]
</IfModule>

HTA
  . $protectJson . "\n" . <<<'HTA'
<FilesMatch "^\.lpcms-mail\.log$">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</FilesMatch>

<IfModule mod_headers.c>
  Header unset ETag
  Header always set X-Content-Type-Options "nosniff"
  Header always set X-Frame-Options "SAMEORIGIN"
  Header always set Referrer-Policy "strict-origin-when-cross-origin"
  Header always set Permissions-Policy "camera=(), microphone=(), payment=()"
</IfModule>

<IfModule mod_mime.c>
  AddType image/avif avif
  AddType image/webp webp
  AddType image/svg+xml svg svgz
  AddType font/woff2 woff2
  AddType application/manifest+json webmanifest
  AddType video/mp4 mp4
  AddType video/webm webm
</IfModule>

<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/plain text/html text/xml text/css text/javascript application/javascript application/json application/xml application/rss+xml image/svg+xml font/ttf font/otf font/woff font/woff2
</IfModule>

<IfModule mod_brotli.c>
  AddOutputFilterByType BROTLI_COMPRESS text/plain text/html text/xml text/css text/javascript application/javascript application/json application/xml application/rss+xml image/svg+xml font/ttf font/otf font/woff font/woff2
</IfModule>

<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresDefault "access plus 1 month"
  ExpiresByType text/html "access plus 0 seconds"
  ExpiresByType application/json "access plus 0 seconds"
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType image/avif "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType font/woff "access plus 1 year"
  ExpiresByType application/pdf "access plus 1 month"
  ExpiresByType video/mp4 "access plus 1 year"
  ExpiresByType video/webm "access plus 1 year"
</IfModule>

<IfModule mod_headers.c>
  <FilesMatch "\.(?:css|js|mjs|jpg|jpeg|png|gif|webp|avif|svg|ico|woff|woff2|ttf|otf|mp4|webm)$">
HTA
  . "\n" . '    Header set Cache-Control "public, max-age=' . $cacheSeconds . ', immutable"' . "\n" . <<<'HTA'
  </FilesMatch>
  <FilesMatch "\.(?:html|php|json)$">
    Header set Cache-Control "no-cache, no-store, must-revalidate"
  </FilesMatch>
</IfModule>
HTA
  . "\n# END FlatCMS\n");
}

function cms_assets_htaccess(array $data): string {
  $s = cms_htaccess_settings($data);
  $cacheDays = max(1, min(365, (int)($s['assetCacheDays'] ?? 365)));
  $cacheSeconds = $cacheDays * 86400;
  return "# BEGIN FlatCMS\n" . <<<'HTA'
# Managed by index.php. Protects uploads while allowing static assets.
Options -Indexes

<FilesMatch "(^\.|\.(?:php|phtml|php[0-9]?|phar|cgi|pl|py|sh|asp|aspx|jsp)$)">
  <IfModule mod_authz_core.c>
    Require all denied
  </IfModule>
  <IfModule !mod_authz_core.c>
    Deny from all
  </IfModule>
</FilesMatch>

<IfModule mod_php.c>
  php_flag engine off
</IfModule>

<IfModule mod_mime.c>
  AddType image/avif avif
  AddType image/webp webp
  AddType image/svg+xml svg svgz
  AddType font/woff2 woff2
  AddType video/mp4 mp4
  AddType video/webm webm
</IfModule>

<IfModule mod_headers.c>
  Header always set X-Content-Type-Options "nosniff"
  <FilesMatch "\.(?:css|js|mjs|jpg|jpeg|png|gif|webp|avif|svg|ico|woff|woff2|ttf|otf|mp4|webm)$">
HTA
  . "\n" . '    Header set Cache-Control "public, max-age=' . $cacheSeconds . ', immutable"' . "\n" . <<<'HTA'
  </FilesMatch>
</IfModule>

<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresDefault "access plus 1 month"
  ExpiresByType text/css "access plus 1 year"
  ExpiresByType application/javascript "access plus 1 year"
  ExpiresByType image/avif "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/gif "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType font/woff "access plus 1 year"
  ExpiresByType video/mp4 "access plus 1 year"
  ExpiresByType video/webm "access plus 1 year"
</IfModule>
HTA
  . "\n# END FlatCMS\n";
}

function cms_upsert_managed_block(string $file, string $block): array {
  $start = '# BEGIN FlatCMS';
  $end = '# END FlatCMS';
  $existing = file_exists($file) ? (string)file_get_contents($file) : '';
  $new = $existing;
  if (str_contains($existing, $start) && str_contains($existing, $end)) {
    $new = preg_replace_callback('/# BEGIN FlatCMS.*?# END FlatCMS\s*/s', fn() => rtrim($block) . "\n", $existing, 1);
  } else {
    $new = rtrim($existing) . ($existing !== '' ? "\n\n" : '') . rtrim($block) . "\n";
  }
  if ($new === $existing) return ['file' => basename($file), 'changed' => false, 'ok' => true, 'message' => 'Already synced'];
  $ok = @file_put_contents($file, $new, LOCK_EX) !== false;
  return ['file' => basename($file), 'changed' => $ok, 'ok' => $ok, 'message' => $ok ? 'Synced' : 'Could not write file'];
}

function cms_sync_htaccess_files(array $data): array {
  $settings = cms_htaccess_settings($data);
  if (empty($settings['autoSync'])) {
    return ['ok' => true, 'skipped' => true, 'message' => 'Auto sync disabled'];
  }
  if (!is_dir(ASSETS_DIR)) @mkdir(ASSETS_DIR, 0755, true);
  $root = cms_upsert_managed_block(HTACCESS_FILE, cms_root_htaccess($data));
  $assets = cms_upsert_managed_block(ASSETS_HTACCESS_FILE, cms_assets_htaccess($data));
  return ['ok' => !empty($root['ok']) && !empty($assets['ok']), 'root' => $root, 'assets' => $assets];
}

function cms_htaccess_status(array $data): array {
  return [
    'rootExists' => file_exists(HTACCESS_FILE),
    'assetsExists' => file_exists(ASSETS_HTACCESS_FILE),
    'rootWritable' => is_writable(__DIR__) || is_writable(HTACCESS_FILE),
    'assetsWritable' => is_writable(ASSETS_DIR) || is_writable(ASSETS_HTACCESS_FILE),
    'settings' => cms_htaccess_settings($data),
  ];
}

function cms_ensure_files(): void {
  foreach (cms_asset_dirs() as $dir) {
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
      error_log('LPCMS: could not create required directory: ' . $dir);
    }
  }
  if (!file_exists(DATA_FILE)) {
    $defaultJson = json_encode(cms_default_data(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($defaultJson === false || @file_put_contents(DATA_FILE, $defaultJson, LOCK_EX) === false) {
      error_log('LPCMS: could not create data.json. Check web-root ownership and write permissions.');
    }
  }
}

function cms_load_data(): array {
  cms_ensure_files();
  $raw = @file_get_contents(DATA_FILE);
  $data = json_decode($raw ?: '', true);
  if (!is_array($data)) {
    $data = cms_default_data();
  }
  return array_replace_recursive(cms_default_data(), $data);
}

function cms_save_data(array $data): bool {
  $data['version'] = CMS_VERSION;
  $tmp = DATA_FILE . '.tmp';
  $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  if ($json === false) {
    return false;
  }
  if (file_put_contents($tmp, $json, LOCK_EX) === false) {
    return false;
  }
  return rename($tmp, DATA_FILE);
}

function cms_json($payload, int $status = 200): void {
  $baseLevel = $GLOBALS['CMS_API_BUFFER_LEVEL'] ?? null;
  if (is_int($baseLevel)) {
    while (ob_get_level() > $baseLevel) @ob_end_clean();
  }
  $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  if ($json === false) {
    $status = 500;
    $json = '{"ok":false,"error":"JSON encoding failed on the server."}';
  }
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  echo $json;
  exit;
}

function cms_b64u(string $s): string {
  return rtrim(strtr(base64_encode($s), '+/', '-_'), '=');
}

function cms_b64u_dec(string $s): string|false {
  $pad = strlen($s) % 4;
  if ($pad) $s .= str_repeat('=', 4 - $pad);
  return base64_decode(strtr($s, '-_', '+/'));
}

function cms_make_token(string $email): string {
  $payload = cms_b64u(json_encode([
    'email' => strtolower(trim($email)),
    'exp' => time() + 15 * 60,
    'nonce' => bin2hex(random_bytes(12))
  ]));
  $sig = cms_b64u(hash_hmac('sha256', $payload, CMS_SECRET, true));
  return $payload . '.' . $sig;
}

function cms_verify_token(string $token): ?string {
  $parts = explode('.', $token, 2);
  if (count($parts) !== 2) return null;
  [$payload, $sig] = $parts;
  $expected = cms_b64u(hash_hmac('sha256', $payload, CMS_SECRET, true));
  if (!hash_equals($expected, $sig)) return null;
  $json = cms_b64u_dec($payload);
  if (!$json) return null;
  $data = json_decode($json, true);
  if (!is_array($data) || empty($data['email']) || empty($data['exp'])) return null;
  if ((int)$data['exp'] < time()) return null;
  $email = strtolower(trim($data['email']));
  if (!in_array($email, array_map('strtolower', ADMIN_EMAILS), true)) return null;
  return $email;
}

function cms_base_url(array $data): string {
  $configured = trim((string)($data['settings']['baseUrl'] ?? ''));
  if ($configured !== '') return rtrim($configured, '/');
  $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') == 443);
  $scheme = $https ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
  $path = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
  return $scheme . '://' . $host . ($path === '' ? '' : $path);
}

function cms_mailer_settings(array $data): array {
  $defaults = [
    'mode' => 'mail',
    'fromName' => 'LPCMS',
    'fromEmail' => '',
    'replyTo' => '',
    'smtp' => [
      'host' => '', 'port' => 587, 'encryption' => 'tls', 'username' => '', 'password' => '',
      'timeout' => 12, 'verifyPeer' => true,
    ],
  ];
  return array_replace_recursive($defaults, $data['settings']['mailer'] ?? []);
}

function cms_mail_default_from(array $data): array {
  $settings = cms_mailer_settings($data);
  $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? 'localhost'));
  $host = preg_replace('/:\d+$/', '', $host);
  $host = preg_replace('/^www\./', '', $host);
  $host = trim((string)preg_replace('/[^a-z0-9.-]/', '', $host), '.-');
  if ($host === '' || !str_contains($host, '.')) $host = 'localhost.localdomain';
  $email = trim((string)($settings['fromEmail'] ?? ''));
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $email = 'no-reply@' . $host;
  $name = trim((string)($settings['fromName'] ?? 'LPCMS')) ?: 'LPCMS';
  return [$email, $name];
}

function cms_mail_log(string $event, array $context = []): void {
  $clean = [];
  foreach ($context as $key => $value) {
    if (preg_match('/pass|secret|token/i', (string)$key)) continue;
    if (is_scalar($value) || $value === null) $clean[$key] = $value;
  }
  $line = json_encode(['time' => date('c'), 'event' => $event] + $clean, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  if ($line !== false) @file_put_contents(MAIL_LOG_FILE, $line . "\n", FILE_APPEND | LOCK_EX);
}

function cms_mail_logs(int $limit = 100): array {
  if (!is_file(MAIL_LOG_FILE)) return [];
  $lines = @file(MAIL_LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
  $lines = array_slice($lines, -max(1, min(300, $limit)));
  $out = [];
  foreach ($lines as $line) {
    $row = json_decode($line, true);
    $out[] = is_array($row) ? $row : ['time' => '', 'event' => 'log', 'message' => $line];
  }
  return array_reverse($out);
}

function cms_smtp_read($socket): array {
  $text = '';
  $code = 0;
  while (!feof($socket)) {
    $line = fgets($socket, 4096);
    if ($line === false) break;
    $text .= $line;
    if (preg_match('/^(\d{3})([ -])/', $line, $m)) {
      $code = (int)$m[1];
      if ($m[2] === ' ') break;
    }
  }
  return [$code, trim($text)];
}

function cms_smtp_command($socket, string $command, array $expected, string $label = ''): array {
  if ($command !== '') fwrite($socket, $command . "\r\n");
  [$code, $text] = cms_smtp_read($socket);
  if (!in_array($code, $expected, true)) throw new RuntimeException('SMTP ' . ($label !== '' ? $label : ($command !== '' ? strtok($command, ' ') : 'response')) . ' failed: ' . $text);
  return [$code, $text];
}

function cms_smtp_connect(array $smtp): array {
  $host = trim((string)($smtp['host'] ?? ''));
  $port = max(1, min(65535, (int)($smtp['port'] ?? 587)));
  $encryption = strtolower((string)($smtp['encryption'] ?? 'tls'));
  if ($host === '') throw new RuntimeException('SMTP host is not configured.');
  if (!in_array($encryption, ['none','tls','ssl'], true)) $encryption = 'tls';
  $timeout = max(3, min(60, (int)($smtp['timeout'] ?? 12)));
  $verifyPeer = !array_key_exists('verifyPeer', $smtp) || !empty($smtp['verifyPeer']);
  $context = stream_context_create(['ssl' => [
    'verify_peer' => $verifyPeer,
    'verify_peer_name' => $verifyPeer,
    'allow_self_signed' => !$verifyPeer,
    'SNI_enabled' => true,
    'peer_name' => $host,
  ]]);
  $scheme = $encryption === 'ssl' ? 'ssl' : 'tcp';
  $errno = 0; $errstr = '';
  $socket = @stream_socket_client($scheme . '://' . $host . ':' . $port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
  if (!$socket) throw new RuntimeException('SMTP connection failed: ' . ($errstr ?: ('error ' . $errno)));
  stream_set_timeout($socket, $timeout);
  cms_smtp_command($socket, '', [220]);
  $helo = preg_replace('/[^A-Za-z0-9.-]/', '', (string)($_SERVER['SERVER_NAME'] ?? 'localhost')) ?: 'localhost';
  cms_smtp_command($socket, 'EHLO ' . $helo, [250]);
  if ($encryption === 'tls') {
    cms_smtp_command($socket, 'STARTTLS', [220]);
    $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
    if ($crypto !== true) throw new RuntimeException('SMTP STARTTLS negotiation failed.');
    cms_smtp_command($socket, 'EHLO ' . $helo, [250]);
  }
  $username = (string)($smtp['username'] ?? '');
  $password = (string)($smtp['password'] ?? '');
  if ($username !== '') {
    cms_smtp_command($socket, 'AUTH LOGIN', [334]);
    cms_smtp_command($socket, base64_encode($username), [334], 'AUTH');
    cms_smtp_command($socket, base64_encode($password), [235], 'AUTH');
  }
  return [$socket, ['host' => $host, 'port' => $port, 'encryption' => $encryption]];
}

function cms_smtp_send(array $data, string $to, string $subject, string $body): bool {
  $settings = cms_mailer_settings($data);
  $smtp = $settings['smtp'] ?? [];
  [$fromEmail, $fromName] = cms_mail_default_from($data);
  [$socket] = cms_smtp_connect($smtp);
  try {
    cms_smtp_command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
    cms_smtp_command($socket, 'RCPT TO:<' . $to . '>', [250,251]);
    cms_smtp_command($socket, 'DATA', [354]);
    $safeSubject = trim(preg_replace('/[\r\n]+/', ' ', $subject));
    $safeName = trim(preg_replace('/[\r\n]+/', ' ', $fromName));
    $replyTo = trim((string)($settings['replyTo'] ?? ''));
    $headers = [
      'MIME-Version: 1.0',
      'Content-Type: text/plain; charset=UTF-8',
      'Content-Transfer-Encoding: 8bit',
      'From: ' . $safeName . ' <' . $fromEmail . '>',
      'To: <' . $to . '>',
      'Subject: ' . $safeSubject,
      'Date: ' . date(DATE_RFC2822),
      'Message-ID: <' . bin2hex(random_bytes(10)) . '@' . (parse_url(cms_base_url($data), PHP_URL_HOST) ?: 'localhost') . '>',
    ];
    if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $headers[] = 'Reply-To: <' . $replyTo . '>';
    $normalized = preg_replace("/\r\n|\r|\n/", "\r\n", $body);
    $normalized = preg_replace('/^\./m', '..', (string)$normalized);
    fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . $normalized . "\r\n.\r\n");
    cms_smtp_command($socket, '', [250], 'message body');
    cms_smtp_command($socket, 'QUIT', [221]);
    return true;
  } finally {
    if (is_resource($socket)) fclose($socket);
  }
}

function cms_send_mail(string $to, string $subject, string $body, ?array $data = null): bool {
  $data = $data ?: cms_load_data();
  $settings = cms_mailer_settings($data);
  $mode = strtolower((string)($settings['mode'] ?? 'mail'));
  try {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Recipient email is invalid.');
    if ($mode === 'smtp') {
      $ok = cms_smtp_send($data, $to, $subject, $body);
    } else {
      [$fromEmail, $fromName] = cms_mail_default_from($data);
      $headers = "MIME-Version: 1.0\r\n";
      $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
      $headers .= 'From: ' . preg_replace('/[\r\n]+/', ' ', $fromName) . ' <' . $fromEmail . ">\r\n";
      $replyTo = trim((string)($settings['replyTo'] ?? ''));
      if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $headers .= 'Reply-To: <' . $replyTo . ">\r\n";
      $ok = @mail($to, trim(preg_replace('/[\r\n]+/', ' ', $subject)), $body, $headers);
    }
    cms_mail_log($ok ? 'sent' : 'failed', ['mode' => $mode, 'to' => $to, 'subject' => substr($subject,0,160)]);
    return $ok;
  } catch (Throwable $e) {
    cms_mail_log('error', ['mode' => $mode, 'to' => $to, 'subject' => substr($subject,0,160), 'message' => substr($e->getMessage(),0,500)]);
    return false;
  }
}

function cms_test_mailer_connection(array $data): array {
  $settings = cms_mailer_settings($data);
  $mode = strtolower((string)($settings['mode'] ?? 'mail'));
  if ($mode !== 'smtp') return ['ok' => function_exists('mail'), 'message' => function_exists('mail') ? 'PHP mail() is available.' : 'PHP mail() is not available.', 'mode' => 'mail'];
  try {
    [$socket, $meta] = cms_smtp_connect($settings['smtp'] ?? []);
    cms_smtp_command($socket, 'QUIT', [221]);
    if (is_resource($socket)) fclose($socket);
    cms_mail_log('connection-ok', $meta);
    return ['ok' => true, 'message' => 'SMTP connection and authentication succeeded.', 'mode' => 'smtp'] + $meta;
  } catch (Throwable $e) {
    cms_mail_log('connection-error', ['mode' => 'smtp', 'message' => substr($e->getMessage(),0,500)]);
    return ['ok' => false, 'message' => $e->getMessage(), 'mode' => 'smtp'];
  }
}

function cms_favicon_href(array $data): string {
  $custom = trim((string)($data['settings']['branding']['favicon'] ?? ''));
  if ($custom !== '') return $custom;
  $brand = (string)($data['settings']['theme']['brand'] ?? '#0d6efd');
  if (!preg_match('/^#[0-9a-f]{6}$/i', $brand)) $brand = '#0d6efd';
  $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="16" fill="' . $brand . '"/><text x="32" y="41" text-anchor="middle" font-family="Arial,sans-serif" font-size="25" font-weight="700" fill="white">LP</text></svg>';
  return 'data:image/svg+xml;base64,' . base64_encode($svg);
}

function cms_post_commerce_webhook(array $data, array $payload): array {
  $cfg = $data['settings']['commerceIntegrations']['webhook'] ?? [];
  if (empty($cfg['enabled'])) return ['ok' => false, 'skipped' => true, 'message' => 'Commerce webhook disabled.'];
  $url = trim((string)($cfg['url'] ?? ''));
  if (!preg_match('#^https://#i', $url)) return ['ok' => false, 'message' => 'Commerce webhook must use HTTPS.'];
  $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  if ($json === false) return ['ok' => false, 'message' => 'Could not encode webhook payload.'];
  $token = trim((string)($cfg['bearerToken'] ?? ''));
  $headers = ['Content-Type: application/json', 'User-Agent: LPCMS/' . CMS_VERSION];
  if ($token !== '') $headers[] = 'Authorization: Bearer ' . $token;
  try {
    if (function_exists('curl_init')) {
      $ch = curl_init($url);
      curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $json, CURLOPT_HTTPHEADER => $headers, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5]);
      $body = curl_exec($ch); $err = curl_error($ch); $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE); curl_close($ch);
      if ($body === false || $status < 200 || $status >= 300) throw new RuntimeException($err ?: ('HTTP ' . $status));
      return ['ok' => true, 'status' => $status];
    }
    $ctx = stream_context_create(['http' => ['method' => 'POST', 'header' => implode("\r\n", $headers), 'content' => $json, 'timeout' => 10, 'ignore_errors' => true]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) throw new RuntimeException('Webhook request failed.');
    return ['ok' => true, 'status' => 200];
  } catch (Throwable $e) {
    return ['ok' => false, 'message' => $e->getMessage()];
  }
}


function cms_http_json_request(string $url, string $method = 'GET', array $headers = [], ?array $payload = null, int $timeout = 20): array {
  if (!preg_match('#^https://#i', $url)) throw new RuntimeException('External API endpoint must use HTTPS.');
  $headers[] = 'Accept: application/json';
  $body = $payload === null ? null : json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  if ($payload !== null) $headers[] = 'Content-Type: application/json';
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    $opts = [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>$timeout,CURLOPT_HTTPHEADER=>$headers,CURLOPT_USERAGENT=>'LPCMS/'.CMS_VERSION,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2];
    if ($method !== 'GET') { $opts[CURLOPT_CUSTOMREQUEST]=$method; if ($body !== null) $opts[CURLOPT_POSTFIELDS]=$body; }
    if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) $opts[CURLOPT_PROTOCOLS]=CURLPROTO_HTTPS;
    curl_setopt_array($ch,$opts);
    $raw=curl_exec($ch); $err=curl_error($ch); $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE); curl_close($ch);
    if ($raw===false) throw new RuntimeException($err ?: 'External API request failed.');
  } else {
    $ctx=stream_context_create(['http'=>['method'=>$method,'header'=>implode("\r\n",$headers),'content'=>$body??'','timeout'=>$timeout,'ignore_errors'=>true],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);
    $raw=@file_get_contents($url,false,$ctx);
    if ($raw===false) throw new RuntimeException('External API request failed.');
    $status=200;
    foreach (($http_response_header??[]) as $line) if (preg_match('#^HTTP/\S+\s+(\d{3})#',$line,$m)) $status=(int)$m[1];
  }
  $json=json_decode((string)$raw,true);
  if ($status<200 || $status>=300) {
    $msg=is_array($json)?(string)($json['error']['message']??$json['message']??$json['error']??''):'';
    throw new RuntimeException($msg!==''?$msg:('External API returned HTTP '.$status.'.'));
  }
  if (!is_array($json)) throw new RuntimeException('External API returned invalid JSON.');
  return $json;
}

function cms_public_remote_url(string $url): bool {
  if (!preg_match('#^https?://#i',$url)) return false;
  $host=(string)parse_url($url,PHP_URL_HOST);
  if ($host==='') return false;
  $ips=[];
  if (filter_var($host,FILTER_VALIDATE_IP)) $ips=[$host]; else $ips=gethostbynamel($host)?:[];
  if (!$ips) return false;
  foreach ($ips as $ip) if (filter_var($ip,FILTER_VALIDATE_IP,FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE)===false) return false;
  return true;
}

function cms_remote_image_bytes(string $url, int $maxBytes = 15728640): array {
  $current=trim($url);
  if ($current==='') throw new RuntimeException('No remote image URL was supplied.');
  $accept='Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8';
  for($hop=0;$hop<5;$hop++){
    if (!cms_public_remote_url($current)) throw new RuntimeException('Remote image URL is not allowed.');
    if (function_exists('curl_init')) {
      $ch=curl_init($current);
      $opts=[
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_FOLLOWLOCATION=>false,
        CURLOPT_CONNECTTIMEOUT=>8,
        CURLOPT_TIMEOUT=>30,
        CURLOPT_USERAGENT=>'Mozilla/5.0 (compatible; LPCMS/'.CMS_VERSION.'; +https://lpcms.dev4.click)',
        CURLOPT_HTTPHEADER=>[$accept,'Accept-Language: en-US,en;q=0.8'],
        CURLOPT_SSL_VERIFYPEER=>true,
        CURLOPT_SSL_VERIFYHOST=>2,
        CURLOPT_HEADER=>false,
      ];
      if (defined('CURLOPT_PROTOCOLS')) $opts[CURLOPT_PROTOCOLS]=(defined('CURLPROTO_HTTPS')?CURLPROTO_HTTPS:0)|(defined('CURLPROTO_HTTP')?CURLPROTO_HTTP:0);
      curl_setopt_array($ch,$opts);
      $raw=curl_exec($ch); $err=curl_error($ch); $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE); $type=(string)curl_getinfo($ch,CURLINFO_CONTENT_TYPE); $redir=(string)curl_getinfo($ch,CURLINFO_REDIRECT_URL); curl_close($ch);
      if ($raw===false) throw new RuntimeException($err?:'Remote image download failed.');
      if ($status>=300 && $status<400 && $redir!=='') {
        $next=cms_resolve_remote_url($current,$redir);
        if($next==='') throw new RuntimeException('Remote image returned an invalid redirect.');
        $current=$next; continue;
      }
      if ($status<200 || $status>=300) throw new RuntimeException('Remote image returned HTTP '.$status.'.');
    } else {
      $ctx=stream_context_create(['http'=>[
        'timeout'=>30,'follow_location'=>0,'ignore_errors'=>true,
        'user_agent'=>'Mozilla/5.0 (compatible; LPCMS/'.CMS_VERSION.'; +https://lpcms.dev4.click)',
        'header'=>$accept."\r\nAccept-Language: en-US,en;q=0.8\r\n"
      ],'ssl'=>['verify_peer'=>true,'verify_peer_name'=>true]]);
      $raw=@file_get_contents($current,false,$ctx,0,$maxBytes+1); if($raw===false) throw new RuntimeException('Remote image download failed.');
      $status=200;$type='';$redir='';
      foreach(($http_response_header??[]) as $line){
        if(preg_match('#^HTTP/\S+\s+(\d{3})#',$line,$m))$status=(int)$m[1];
        if(stripos($line,'Content-Type:')===0)$type=trim(substr($line,13));
        if(stripos($line,'Location:')===0)$redir=trim(substr($line,9));
      }
      if($status>=300&&$status<400&&$redir!==''){
        $next=cms_resolve_remote_url($current,$redir);
        if($next==='') throw new RuntimeException('Remote image returned an invalid redirect.');
        $current=$next; continue;
      }
      if($status<200||$status>=300) throw new RuntimeException('Remote image returned HTTP '.$status.'.');
    }
    $raw=(string)$raw;
    if(strlen($raw)>$maxBytes) throw new RuntimeException('Remote image exceeds 15MB.');
    if(strlen($raw)<16) throw new RuntimeException('Remote image download was empty.');
    $tmp=tempnam(sys_get_temp_dir(),'lpcms-media-'); if($tmp===false) throw new RuntimeException('Could not create temporary image file.');
    $written=@file_put_contents($tmp,$raw,LOCK_EX);
    if($written===false || $written<1){ @unlink($tmp); throw new RuntimeException('Could not save the downloaded image temporarily.'); }
    $mime=cms_detect_mime($tmp);
    if(!str_starts_with($mime,'image/')){ @unlink($tmp); throw new RuntimeException('Selected URL did not return an image.'); }
    return ['tmp'=>$tmp,'mime'=>$mime,'url'=>$current,'bytes'=>(int)$written,'contentType'=>$type];
  }
  throw new RuntimeException('Too many image redirects.');
}

function cms_media_proxy_search(array $sources, string $name, string $q, int $page, callable $clean): array {
  $proxy=is_array($sources['proxy']??null)?$sources['proxy']:[];
  if(empty($proxy['enabled'])) throw new RuntimeException('No-key stock image proxy is disabled.');
  $base=trim((string)($proxy['baseUrl']??'https://proxy.getinstantimages.com/api/'));
  if(!preg_match('#^https://#i',$base)) throw new RuntimeException('Stock image proxy must use HTTPS.');
  $version=trim((string)($proxy['schemaVersion']??'7.2.0'))?:'7.2.0';
  $params=['type'=>'search','page'=>$page,'term'=>$q,'version'=>$version];
  if($name==='unsplash') $params['content_filter']='high';
  if($name==='pixabay') $params['safesearch']='true';
  $url=rtrim($base,'/').'/'.rawurlencode($name).'?'.http_build_query($params,'','&',PHP_QUERY_RFC3986);
  $j=cms_http_json_request($url);
  $licences=[
    'pexels'=>['Pexels License','https://www.pexels.com/license/'],
    'pixabay'=>['Pixabay Content License','https://pixabay.com/service/license-summary/'],
    'unsplash'=>['Unsplash License','https://unsplash.com/license'],
  ];
  [$licence,$licenceUrl]=$licences[$name]??['',''];
  $items=[];
  foreach(($j['results']??[]) as $r){
    if(!is_array($r)) continue;
    $urls=is_array($r['urls']??null)?$r['urls']:[];
    $user=is_array($r['user']??null)?$r['user']:[];
    $thumb=(string)($urls['thumb']??$urls['small']??$r['thumb']??'');
    $full=(string)($urls['full']??$urls['regular']??$r['url']??$thumb);
    if($full==='' || $thumb==='') continue;
    $title=$clean($r['alt']??$r['title']??$r['caption']??(ucfirst($name).' image')) ?: (ucfirst($name).' image');
    $items[]=[
      'id'=>(string)($r['id']??md5($full)),
      'title'=>$title,
      'thumb'=>$thumb,
      'url'=>$full,
      'creator'=>$clean($user['name']??$r['creator']??''),
      'license'=>$licence,
      'licenseUrl'=>$licenceUrl,
      'landing'=>(string)($r['permalink']??($r['links']['html']??$user['url']??'')),
      'source'=>ucfirst($name),
      'downloadTrack'=>(string)($urls['download_url']??''),
    ];
  }
  if(!$items && !empty($j['error'])){
    $msg=is_array($j['error'])?(string)($j['error']['message']??''):(string)$j['error'];
    if($msg!=='') throw new RuntimeException($msg);
  }
  return $items;
}

function cms_media_search(array $data, string $provider, string $q, int $page = 1): array {
  $q=trim($q); if($q==='') return [];
  $page=max(1,min(30,$page)); $provider=strtolower(trim($provider)); if($provider==='') $provider='free';
  $sources=$data['settings']['mediaSources']??[];
  $clean=function($value): string {
    $value=is_array($value)?($value['value']??''):$value;
    return trim(html_entity_decode(strip_tags((string)$value),ENT_QUOTES|ENT_HTML5,'UTF-8'));
  };
  $searchOne=function(string $name) use ($data,$sources,$q,$page,$clean): array {
    $cfg=$sources[$name]??[];
    $proxyEnabled=!empty($sources['proxy']['enabled']);
    $hasDirectKey=trim((string)($cfg['apiKey']??''))!=='';
    if(!in_array($name,['openverse','commons'],true) && empty($cfg['enabled']) && !$proxyEnabled && !$hasDirectKey) throw new RuntimeException(ucfirst($name).' media source is not enabled.');
    $items=[];
    if($name==='openverse'){
      // Openverse deprecated the old `mature` search parameter. Omitting it keeps
      // anonymous searches compatible with the current public API defaults.
      $url='https://api.openverse.org/v1/images/?q='.rawurlencode($q).'&page_size=24&page='.$page;
      $j=cms_http_json_request($url);
      foreach(($j['results']??[]) as $r) $items[]=[
        'id'=>(string)($r['id']??''),
        'title'=>$clean($r['title']??'Openverse image')?:'Openverse image',
        'thumb'=>(string)($r['thumbnail']??$r['url']??''),
        'url'=>(string)($r['url']??''),
        'creator'=>$clean($r['creator']??''),
        'license'=>$clean($r['license']??''),
        'licenseUrl'=>(string)($r['license_url']??''),
        'landing'=>(string)($r['foreign_landing_url']??''),
        'source'=>'Openverse'
      ];
    } elseif($name==='commons'){
      $params=[
        'action'=>'query','format'=>'json','formatversion'=>'2','generator'=>'search',
        'gsrsearch'=>$q,'gsrnamespace'=>'6','gsrlimit'=>'24','gsroffset'=>(string)(($page-1)*24),
        'prop'=>'imageinfo','iiprop'=>'url|mime|extmetadata','iiurlwidth'=>'900'
      ];
      $j=cms_http_json_request('https://commons.wikimedia.org/w/api.php?'.http_build_query($params,'','&',PHP_QUERY_RFC3986));
      foreach(($j['query']['pages']??[]) as $r){
        $ii=$r['imageinfo'][0]??[]; $mime=strtolower((string)($ii['mime']??''));
        $allowedCommons=['image/jpeg','image/png','image/gif','image/webp','image/avif'];
        if($mime!=='' && !in_array($mime,$allowedCommons,true)) continue;
        $meta=$ii['extmetadata']??[];
        $title=$clean($meta['ObjectName']??'');
        if($title==='') $title=preg_replace('/^File:/i','',(string)($r['title']??'Wikimedia Commons image'));
        $creator=$clean($meta['Artist']??($meta['Credit']??''));
        $license=$clean($meta['LicenseShortName']??($meta['UsageTerms']??''));
        $licenseUrl=(string)(($meta['LicenseUrl']['value']??'')?:'');
        $landing=(string)($ii['descriptionurl']??'');
        if($landing==='') $landing='https://commons.wikimedia.org/wiki/'.rawurlencode(str_replace(' ','_', (string)($r['title']??'')));
        $items[]=[
          'id'=>'commons-'.(string)($r['pageid']??md5((string)($r['title']??''))),
          'title'=>$title?:'Wikimedia Commons image',
          'thumb'=>(string)($ii['thumburl']??$ii['url']??''),
          'url'=>(string)($ii['url']??''),
          'creator'=>$creator,
          'license'=>$license?:'See source licence',
          'licenseUrl'=>$licenseUrl,
          'landing'=>$landing,
          'source'=>'Wikimedia Commons'
        ];
      }
    } elseif($name==='pexels'){
      $key=trim((string)($cfg['apiKey']??''));
      if($key==='') return cms_media_proxy_search($sources,$name,$q,$page,$clean);
      $j=cms_http_json_request('https://api.pexels.com/v1/search?query='.rawurlencode($q).'&per_page=24&page='.$page,'GET',['Authorization: '.$key]);
      foreach(($j['photos']??[]) as $r) $items[]=['id'=>(string)($r['id']??''),'title'=>$clean($r['alt']??'Pexels image')?:'Pexels image','thumb'=>(string)($r['src']['medium']??''),'url'=>(string)($r['src']['large2x']??$r['src']['original']??''),'creator'=>$clean($r['photographer']??''),'license'=>'Pexels License','licenseUrl'=>'https://www.pexels.com/license/','landing'=>(string)($r['url']??''),'source'=>'Pexels'];
    } elseif($name==='pixabay'){
      $key=trim((string)($cfg['apiKey']??''));
      if($key==='') return cms_media_proxy_search($sources,$name,$q,$page,$clean);
      $j=cms_http_json_request('https://pixabay.com/api/?key='.rawurlencode($key).'&q='.rawurlencode($q).'&image_type=photo&safesearch=true&per_page=24&page='.$page);
      foreach(($j['hits']??[]) as $r) $items[]=['id'=>(string)($r['id']??''),'title'=>$clean($r['tags']??'Pixabay image')?:'Pixabay image','thumb'=>(string)($r['webformatURL']??''),'url'=>(string)($r['largeImageURL']??$r['webformatURL']??''),'creator'=>$clean($r['user']??''),'license'=>'Pixabay Content License','licenseUrl'=>'https://pixabay.com/service/license-summary/','landing'=>(string)($r['pageURL']??''),'source'=>'Pixabay'];
    } elseif($name==='unsplash'){
      $key=trim((string)($cfg['apiKey']??''));
      if($key==='') return cms_media_proxy_search($sources,$name,$q,$page,$clean);
      $j=cms_http_json_request('https://api.unsplash.com/search/photos?query='.rawurlencode($q).'&per_page=24&page='.$page,'GET',['Authorization: Client-ID '.$key,'Accept-Version: v1']);
      foreach(($j['results']??[]) as $r) $items[]=['id'=>(string)($r['id']??''),'title'=>$clean($r['alt_description']??$r['description']??'Unsplash image')?:'Unsplash image','thumb'=>(string)($r['urls']['small']??''),'url'=>(string)($r['urls']['regular']??''),'creator'=>$clean($r['user']['name']??''),'license'=>'Unsplash License','licenseUrl'=>'https://unsplash.com/license','landing'=>(string)($r['links']['html']??''),'source'=>'Unsplash'];
    } else throw new RuntimeException('Unknown media provider.');
    return array_values(array_filter($items,fn($x)=>!empty($x['url'])&&!empty($x['thumb'])));
  };

  if($provider==='free'){
    $errors=[];
    // Openverse is tried first for broad CC/open-media coverage. Wikimedia Commons
    // is the automatic no-key fallback when Openverse is unavailable or returns no results.
    foreach(['openverse','commons'] as $name){
      try { $items=$searchOne($name); if($items) return $items; }
      catch(Throwable $e){ $errors[]=$name.': '.$e->getMessage(); }
    }
    if($errors) throw new RuntimeException('Free image search could not reach its public sources. '.implode(' | ',$errors));
    return [];
  }
  return $searchOne($provider);
}

function cms_ai_generate(array $data, string $prompt, string $context = '', string $tone = '', string $systemOverride = ''): array {
  $cfg=$data['settings']['ai']??[]; if(($cfg['accessTier']??'free')!=='pro') throw new RuntimeException('Connected AI generation is available only in Pro mode. Free mode uses manual AI shortcuts.'); if(empty($cfg['enabled'])) throw new RuntimeException('AI Assistant is disabled.');
  $provider=strtolower((string)($cfg['provider']??'openai')); $key=trim((string)($cfg['apiKey']??'')); if($key==='') throw new RuntimeException('Add an API key for the selected AI provider.');
  $system=trim($systemOverride!==''?$systemOverride:(string)($cfg['systemPrompt']??''));
  $input=trim($prompt).($tone!==''?"\n\nTone: ".$tone:'').($context!==''?"\n\nExisting content/context:\n".$context:'');
  if($provider==='gemini'){
    $model=trim((string)($cfg['model']??''))?:'gemini-3.7-flash';
    $url='https://generativelanguage.googleapis.com/v1beta/models/'.rawurlencode($model).':generateContent';
    $j=cms_http_json_request($url,'POST',['x-goog-api-key: '.$key],['system_instruction'=>['parts'=>[['text'=>$system]]],'contents'=>[['role'=>'user','parts'=>[['text'=>$input]]]]]);
    $text=(string)($j['candidates'][0]['content']['parts'][0]['text']??'');
  } else {
    $defaults=['openai'=>['https://api.openai.com/v1/chat/completions','gpt-5'],'kimi'=>['https://api.moonshot.cn/v1/chat/completions','kimi-k3'],'mistral'=>['https://api.mistral.ai/v1/chat/completions','mistral-small-latest'],'custom'=>['','']];
    if(!isset($defaults[$provider])) throw new RuntimeException('This AI provider needs a custom OpenAI-compatible endpoint.');
    [$defaultUrl,$defaultModel]=$defaults[$provider]; $url=trim((string)($cfg['endpoint']??''))?:$defaultUrl; $model=trim((string)($cfg['model']??''))?:$defaultModel;
    if($url===''||$model==='') throw new RuntimeException('Configure the AI endpoint and model.');
    $j=cms_http_json_request($url,'POST',['Authorization: Bearer '.$key],['model'=>$model,'messages'=>[['role'=>'system','content'=>$system],['role'=>'user','content'=>$input]],'temperature'=>0.7]);
    $text=(string)($j['choices'][0]['message']['content']??'');
  }
  if(trim($text)==='') throw new RuntimeException('The AI provider returned no text.');
  return ['ok'=>true,'text'=>trim($text),'provider'=>$provider,'model'=>$model??''];
}

function cms_checkout_cart(array $data, array $submitted, array $options = []): array {
  $products=$data['products']['items']??[]; $lines=[]; $subtotal=0.0; $currency=(string)($data['products']['currency']??'EUR');
  foreach(array_slice($submitted,0,50) as $row){
    $qty=max(1,min(99,(int)($row['qty']??1))); $found=null;
    foreach($products as $p){ if((string)($p['id']??'')===(string)($row['id']??'') || ((string)($p['slug']??'')!=='' && (string)($p['slug']??'')===(string)($row['slug']??''))){$found=$p;break;} }
    if(!$found) continue;
    $mode=(string)($found['purchaseMode']??$data['products']['defaultPurchaseMode']??'quote'); if($mode!=='checkout') continue;
    $price=(float)($found['price']??0); $variationId=(string)($row['variationId']??''); $variation=null;
    if($variationId!=='' && is_array($found['variations']??null)) foreach($found['variations'] as $v){ if((string)($v['id']??$v['sku']??'')===$variationId){$variation=$v;break;} }
    if($variation && isset($variation['price'])) $price=(float)$variation['price']; if($price<=0) continue;
    $line=['id'=>(string)($found['id']??''),'slug'=>(string)($found['slug']??''),'title'=>(string)($found['title']??'Product'),'sku'=>(string)(($variation['sku']??null)?:($found['sku']??'')),'variationId'=>$variationId,'qty'=>$qty,'price'=>$price,'currency'=>(string)(($variation['currency']??null)?:($found['currency']??$currency)),'image'=>(string)(($variation['image']??null)?:($found['image']??''))];
    $lines[]=$line; $subtotal+=$price*$qty;
  }
  if(!$lines) throw new RuntimeException('No checkout-enabled products were found in the cart.');

  $discountCode=strtoupper(trim((string)($options['discountCode']??''))); $discount=0.0;
  if($discountCode!==''){
    $matched=null;
    foreach(($data['products']['discountCodes']??[]) as $code){ if(!empty($code['enabled']) && strtoupper(trim((string)($code['code']??'')))===$discountCode){$matched=$code;break;} }
    if(!$matched) throw new RuntimeException('Discount code is not valid.');
    $value=max(0,(float)($matched['value']??0)); $type=(string)($matched['type']??'percent');
    $discount=$type==='fixed' ? min($subtotal,$value) : min($subtotal,$subtotal*min(100,$value)/100);
  }

  $shippingId=trim((string)($options['shippingId']??'')); $shipping=0.0; $shippingLabel=''; $enabledShipping=[];
  foreach(($data['products']['shippingMethods']??[]) as $method) if(!empty($method['enabled'])) $enabledShipping[]=$method;
  if($enabledShipping){
    $selected=null;
    foreach($enabledShipping as $method) if((string)($method['id']??'')===$shippingId){$selected=$method;break;}
    if(!$selected) $selected=$enabledShipping[0];
    $shippingId=(string)($selected['id']??''); $shippingLabel=(string)($selected['label']??'Delivery'); $shipping=max(0,(float)($selected['price']??0));
  }
  $total=max(0,$subtotal-$discount+$shipping);
  return ['lines'=>$lines,'subtotal'=>round($subtotal,2),'discount'=>round($discount,2),'discountCode'=>$discountCode,'shipping'=>round($shipping,2),'shippingId'=>$shippingId,'shippingLabel'=>$shippingLabel,'total'=>round($total,2),'currency'=>$currency];
}

function cms_start_checkout(array $data, array $payload): array {
  $cfg=$data['settings']['commerceIntegrations']['checkout']??[]; $mode=(string)($cfg['mode']??'order-form');
  $cart=cms_checkout_cart($data,is_array($payload['items']??null)?$payload['items']:[],$payload);
  if($mode==='payment-link'){
    $url=trim((string)($cfg['paymentLink']??'')); if(!preg_match('#^https://#i',$url)) throw new RuntimeException('Configure an HTTPS hosted payment link.');
    return ['ok'=>true,'mode'=>$mode,'url'=>$url,'cart'=>$cart];
  }
  if($mode==='adapter'){
    $url=trim((string)($cfg['adapterUrl']??'')); if(!preg_match('#^https://#i',$url)) throw new RuntimeException('Configure an HTTPS checkout adapter URL.');
    $headers=[]; $token=trim((string)($cfg['bearerToken']??'')); if($token!=='')$headers[]='Authorization: Bearer '.$token;
    $j=cms_http_json_request($url,'POST',$headers,['event'=>'checkout.start','cart'=>$cart,'customer'=>is_array($payload['customer']??null)?$payload['customer']:[],'returnUrl'=>cms_base_url($data)]);
    $redirect=(string)($j['url']??$j['redirect_url']??$j['checkout_url']??''); if(!preg_match('#^https://#i',$redirect)) throw new RuntimeException('Checkout adapter did not return an HTTPS redirect URL.');
    return ['ok'=>true,'mode'=>$mode,'url'=>$redirect,'cart'=>$cart];
  }
  return ['ok'=>true,'mode'=>'order-form','cart'=>$cart];
}

function cms_abandoned_cart_capture(array &$data, string $email, array $items, array $options = []): array {
  if(empty($data['settings']['abandonedCart']['enabled'])) return ['ok'=>true,'skipped'=>true];
  $email=strtolower(trim($email)); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('A valid email is required to save the cart.');
  $cart=cms_checkout_cart($data,$items,$options); $now=date('c'); $carts=is_array($data['settings']['abandonedCart']['carts']??null)?$data['settings']['abandonedCart']['carts']:[]; $index=-1;
  foreach($carts as $i=>$row){ if(strtolower((string)($row['email']??''))===$email && empty($row['completedAt']) && (strtotime((string)($row['updatedAt']??$row['createdAt']??''))?:0)>time()-172800){$index=$i;break;} }
  $entry=['id'=>'cart-'.bin2hex(random_bytes(6)),'token'=>bin2hex(random_bytes(18)),'email'=>$email,'createdAt'=>$now,'updatedAt'=>$now,'completedAt'=>'','firstSentAt'=>'','secondSentAt'=>'','cart'=>$cart];
  if($index>=0){$entry=array_replace($carts[$index],$entry,['id'=>$carts[$index]['id']??$entry['id'],'token'=>$carts[$index]['token']??$entry['token'],'createdAt'=>$carts[$index]['createdAt']??$now]);$carts[$index]=$entry;} else $carts[]=$entry;
  $data['settings']['abandonedCart']['carts']=array_slice($carts,-500); if(!cms_save_data($data)) throw new RuntimeException('Could not save the cart reminder.');
  return ['ok'=>true,'saved'=>true,'token'=>$entry['token'],'cart'=>$cart];
}

function cms_abandoned_cart_complete(array &$data, string $email): array {
  $email=strtolower(trim($email)); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) return ['ok'=>true,'skipped'=>true]; $changed=0;
  foreach(($data['settings']['abandonedCart']['carts']??[]) as $i=>$row){ if(strtolower((string)($row['email']??''))===$email && empty($row['completedAt'])){$data['settings']['abandonedCart']['carts'][$i]['completedAt']=date('c');$changed++;} }
  if($changed) cms_save_data($data); return ['ok'=>true,'completed'=>$changed];
}

function cms_abandoned_cart_recover(array $data, string $token): ?array {
  $token=trim($token); if($token==='') return null;
  foreach(($data['settings']['abandonedCart']['carts']??[]) as $row){ if(!empty($row['token']) && hash_equals((string)$row['token'],$token) && empty($row['completedAt'])) return ['items'=>$row['cart']['lines']??[],'email'=>$row['email']??'','cart'=>$row['cart']??[]]; }
  return null;
}

function cms_process_abandoned_carts(array &$data): array {
  $cfg=$data['settings']['abandonedCart']??[]; if(empty($cfg['enabled'])) return ['ok'=>true,'processed'=>0,'sent'=>0,'message'=>'Abandoned-cart reminders are disabled.'];
  $first=max(5,(int)($cfg['firstDelayMinutes']??60))*60; $second=max((int)($cfg['secondDelayMinutes']??1440), (int)($cfg['firstDelayMinutes']??60)+5)*60; $sent=0; $processed=0; $now=time();
  foreach(($data['settings']['abandonedCart']['carts']??[]) as $i=>$row){
    if(!empty($row['completedAt'])) continue; $created=strtotime((string)($row['createdAt']??''))?:0; if(!$created) continue; $processed++;
    $email=(string)($row['email']??''); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) continue;
    $items=[]; foreach(($row['cart']['lines']??[]) as $line) $items[]=(int)($line['qty']??1).' × '.(string)($line['title']??'Product');
    $resume=cms_base_url($data).'/?recover-cart='.rawurlencode((string)($row['token']??'')); $list=$items?"\n\n".implode("\n",$items):'';
    if(empty($row['firstSentAt']) && $now-$created >= $first){
      $body=trim((string)($cfg['firstMessage']??'Your cart is waiting.')).$list."\n\nReturn to your cart:\n".$resume;
      if(cms_send_mail($email,(string)($cfg['firstSubject']??'You left something in your cart'),$body,$data)){$data['settings']['abandonedCart']['carts'][$i]['firstSentAt']=date('c');$sent++;}
    }
    if(empty($row['secondSentAt']) && $now-$created >= $second){
      $body=trim((string)($cfg['secondMessage']??'Your saved cart is still available.')).$list."\n\nReturn to your cart:\n".$resume;
      if(cms_send_mail($email,(string)($cfg['secondSubject']??'Your cart is still waiting'),$body,$data)){$data['settings']['abandonedCart']['carts'][$i]['secondSentAt']=date('c');$sent++;}
    }
  }
  if($sent) cms_save_data($data); return ['ok'=>true,'processed'=>$processed,'sent'=>$sent,'message'=>'Processed '.$processed.' pending cart(s); sent '.$sent.' reminder(s).'];
}

function cms_booking_slots(array $data, string $date, string $serviceId = ''): array {
  if(empty($data['booking']['enabled'])) return [];
  $tz=new DateTimeZone(CMS_TIMEZONE); $dt=DateTimeImmutable::createFromFormat('!Y-m-d',$date,$tz); if(!$dt || $dt->format('Y-m-d')!==$date) throw new RuntimeException('Choose a valid booking date.');
  $today=(new DateTimeImmutable('today',$tz)); if($dt<$today) return [];
  $day=(string)$dt->format('N'); $dayName=strtolower($dt->format('l')); $weekly=$data['booking']['weeklySlots']??[]; $slots=array_values(array_filter($weekly[$dayName]??$weekly[$day]??[],fn($x)=>preg_match('/^\d{2}:\d{2}$/',(string)$x)));
  $taken=[]; foreach(($data['booking']['bookings']??[]) as $booking){ if(($booking['date']??'')===$date && empty($booking['cancelledAt'])) $taken[]=(string)($booking['time']??''); }
  if($date===$today->format('Y-m-d')){ $current=(new DateTimeImmutable('now',$tz))->format('H:i'); $slots=array_values(array_filter($slots,fn($slot)=>$slot>$current)); }
  return array_values(array_filter($slots,fn($slot)=>!in_array((string)$slot,$taken,true)));
}

function cms_booking_create(array &$data, array $payload): array {
  if(empty($data['booking']['enabled'])) throw new RuntimeException('Online booking is not enabled.');
  $name=trim(strip_tags((string)($payload['name']??''))); $email=strtolower(trim((string)($payload['email']??''))); $phone=trim(strip_tags((string)($payload['phone']??''))); $serviceId=trim((string)($payload['serviceId']??'')); $date=trim((string)($payload['date']??'')); $time=trim((string)($payload['time']??''));
  if($name===''||strlen($name)>120) throw new RuntimeException('Name is required.'); if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Valid email is required.');
  $service=null; foreach(($data['booking']['services']??[]) as $item) if((string)($item['id']??'')===$serviceId){$service=$item;break;} if(!$service || ($service['enabled']??true)===false) throw new RuntimeException('Choose a valid service.');
  $slots=cms_booking_slots($data,$date,$serviceId); if(!in_array($time,$slots,true)) throw new RuntimeException('That time is no longer available. Please choose another slot.');
  $code='BK-'.strtoupper(bin2hex(random_bytes(3))); $entry=['code'=>$code,'createdAt'=>date('c'),'serviceId'=>$serviceId,'serviceName'=>(string)($service['title']??$service['name']??'Service'),'date'=>$date,'time'=>$time,'name'=>$name,'email'=>$email,'phone'=>substr($phone,0,80),'cancelledAt'=>''];
  if(!isset($data['booking']['bookings'])||!is_array($data['booking']['bookings'])) $data['booking']['bookings']=[]; $data['booking']['bookings'][]=$entry; $data['booking']['bookings']=array_slice($data['booking']['bookings'],-1000);
  if(!cms_save_data($data)) throw new RuntimeException('Could not save the booking.');
  $subject='Booking confirmed - '.$entry['serviceName'].' - '.$date.' '.$time; $body="Booking confirmed.\n\nReference: {$code}\nService: {$entry['serviceName']}\nDate: {$date}\nTime: {$time}\nName: {$name}"; cms_send_mail($email,$subject,$body,$data);
  $notify=trim((string)($data['booking']['technical']['notifyEmail']??'')); if($notify==='') $notify=trim((string)($data['settings']['forms']['emailTo']??'')); if($notify==='') $notify=BREAKGLASS_ADMIN_EMAIL;
  foreach(preg_split('/[;,]+/',$notify)?:[] as $to){$to=trim($to);if(filter_var($to,FILTER_VALIDATE_EMAIL)) cms_send_mail($to,'New website booking - '.$entry['serviceName'],$body."\nEmail: {$email}\nPhone: {$phone}",$data);}
  $webhook=trim((string)($data['booking']['technical']['webhookUrl']??'')); if($webhook!=='' && preg_match('#^https://#i',$webhook) && cms_public_remote_url($webhook)){try{cms_http_json_request($webhook,'POST',[],['event'=>'booking.created','booking'=>$entry]);}catch(Throwable $e){}}
  return ['ok'=>true,'booking'=>['code'=>$code,'serviceName'=>$entry['serviceName'],'date'=>$date,'time'=>$time],'message'=>(string)cms_localized_value($data, $data['booking'] ?? [], 'confirmationText', cms_current_language($data), 'Booking confirmed.')];
}


function cms_demo_substr(string $text, int $max): string {
  return function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
}

function cms_demo_clean_text($value, int $max = 3000): string {
  $text = trim(strip_tags((string)$value));
  $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? $text;
  return cms_demo_substr($text, $max);
}

function cms_demo_lines(string $text, int $limit = 12): array {
  $rows = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
  if (count($rows) <= 1 && str_contains($text, ';')) $rows = explode(';', $text);
  $out = [];
  foreach ($rows as $row) {
    $row = trim($row);
    if ($row === '') continue;
    $parts = preg_split('/\s+[—–-]\s+/', $row, 2) ?: [$row];
    $title = trim((string)($parts[0] ?? ''));
    $desc = trim((string)($parts[1] ?? ''));
    if ($title === '') continue;
    $out[] = ['title' => cms_demo_substr($title,120), 'text' => cms_demo_substr($desc ?: 'Professional service tailored to your requirements.',500), 'icon' => 'bi bi-check2-circle', 'image' => '', 'buttonText' => 'Contact us', 'buttonHref' => '#contact'];
    if (count($out) >= $limit) break;
  }
  return $out;
}

function cms_demo_products_from_text(string $text, string $currency = 'GBP', int $limit = 18): array {
  $rows = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
  $out = [];
  foreach ($rows as $i => $row) {
    $row = trim($row); if ($row === '') continue;
    $parts = array_map('trim', explode('|', $row));
    $title = cms_demo_clean_text($parts[0] ?? '', 120); if ($title === '') continue;
    $priceRaw = str_replace(',', '.', (string)($parts[1] ?? '0'));
    $price = preg_match('/-?[0-9]+(?:\.[0-9]+)?/', $priceRaw, $m) ? max(0, (float)$m[0]) : 0;
    $desc = cms_demo_clean_text($parts[2] ?? 'Demo product prepared from the client website brief.', 500);
    $slug = cms_slug($title) ?: ('product-' . ($i+1));
    $out[] = [
      'title'=>$title,'slug'=>$slug,'id'=>'demo-'.($i+1), 'sku'=>strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','',$slug),0,18)),
      'type'=>'simple','category'=>'demo-products','brand'=>'','tags'=>['demo-products'],'price'=>$price,'oldPrice'=>0,'discountLabel'=>'','currency'=>$currency,
      'image'=>'','gallery'=>[],'attributes'=>[],'variations'=>[],'stock'=>0,'inStock'=>true,'text'=>$desc,'body'=>'<p>'.htmlspecialchars($desc,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'</p>','features'=>[],
      'purchaseMode'=>$price>0?'checkout':'quote','buyButton'=>$price>0,'quoteButton'=>$price<=0,'related'=>[],
      'seo'=>['title'=>$title,'description'=>$desc,'image'=>'']
    ];
    if (count($out) >= $limit) break;
  }
  return $out;
}

function cms_demo_build_draft(array $entry, array $data): array {
  $company = cms_demo_clean_text($entry['company'] ?? 'Your Business', 120) ?: 'Your Business';
  $industry = cms_demo_clean_text($entry['industry'] ?? '', 120);
  $about = cms_demo_clean_text($entry['about'] ?? '', 2500);
  $goals = cms_demo_clean_text($entry['goals'] ?? '', 1000);
  $tagline = cms_demo_clean_text($entry['tagline'] ?? '', 180);
  if ($tagline === '') $tagline = $industry !== '' ? $company . ' · ' . $industry : 'Welcome to ' . $company;
  $services = cms_demo_lines((string)($entry['services'] ?? ''), 12);
  if (!$services) $services = [['title'=>'Our service','text'=>'Tell visitors clearly what you provide and how to get started.','icon'=>'bi bi-check2-circle','image'=>'','buttonText'=>'Contact us','buttonHref'=>'#contact']];
  $plan = ($entry['plan'] ?? 'premium') === 'premium-pro' ? 'premium-pro' : 'premium';
  $currency = in_array(($entry['currency'] ?? 'GBP'), ['GBP','EUR'], true) ? (string)$entry['currency'] : 'GBP';
  $products = $plan === 'premium-pro' ? cms_demo_products_from_text((string)($entry['products'] ?? ''), $currency) : [];
  $preferred = cms_demo_clean_text($entry['preferredDomain'] ?? '', 180);
  $customDomain = '';
  $subdomain = cms_slug($company) ?: 'yourbusiness';
  if ($preferred !== '') {
    $clean = preg_replace('#^https?://#i','',$preferred);
    $clean = trim((string)preg_replace('#/.*$#','',$clean),'. ');
    if (str_contains($clean,'.')) $customDomain = strtolower($clean); else $subdomain = cms_slug($clean) ?: $subdomain;
  }
  $brand = preg_match('/^#[0-9a-fA-F]{6}$/',(string)($entry['primaryColor'] ?? '')) ? (string)$entry['primaryColor'] : (string)($data['settings']['theme']['brand'] ?? '#0d6efd');
  $chatChoice = in_array(($entry['chatChoice'] ?? 'chatbot'),['chatbot','whatsapp','none'],true)?(string)$entry['chatChoice']:'chatbot';
  $logo = (string)($entry['logo'] ?? '');
  return [
    'siteName'=>$company,
    'header'=>['logoText'=>$company,'logoImage'=>$logo],
    'hero'=>['eyebrow'=>$industry ?: 'Welcome','title'=>$tagline,'text'=>$goals ?: ($about !== '' ? cms_demo_substr($about,420) : 'Professional solutions from '.$company.'.'),'buttonText'=>'Contact us','buttonHref'=>'#contact'],
    'about'=>['title'=>'About '.$company,'subtitle'=>$industry,'body'=>'<p>'.htmlspecialchars($about ?: ('Tell visitors about '.$company.' here.'),ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8').'</p>'],
    'services'=>$services,
    'products'=>$products,
    'contact'=>['address'=>cms_demo_clean_text($entry['address'] ?? '',300),'phone'=>cms_demo_clean_text($entry['phone'] ?? '',80),'email'=>cms_demo_clean_text($entry['email'] ?? '',160)],
    'brand'=>$brand,
    'style'=>cms_demo_clean_text($entry['style'] ?? 'clean',40),
    'plan'=>$plan,
    'subdomain'=>$subdomain,
    'customDomain'=>$customDomain,
    'chatChoice'=>$chatChoice,
    'whatsapp'=>cms_demo_clean_text($entry['whatsapp'] ?? '',80),
    'bookingNeeded'=>!empty($entry['bookingNeeded']),
  ];
}

function cms_demo_apply_request(array &$data, int $index): array {
  $requests = $data['settings']['demoIntakeAdmin']['requests'] ?? [];
  if (!isset($requests[$index]) || !is_array($requests[$index])) throw new RuntimeException('Demo request not found.');
  $r = $requests[$index];
  $draft = is_array($r['draft'] ?? null) ? $r['draft'] : cms_demo_build_draft($r,$data);
  $data['settings']['siteName'] = (string)($draft['siteName'] ?? $data['settings']['siteName'] ?? 'Website');
  $data['header']['logoText'] = (string)($draft['header']['logoText'] ?? $data['header']['logoText'] ?? '');
  if (!empty($draft['header']['logoImage'])) $data['header']['logoImage'] = (string)$draft['header']['logoImage'];
  if (!isset($data['hero']['slides'][0]) || !is_array($data['hero']['slides'][0])) $data['hero']['slides'][0] = [];
  $data['hero']['slides'][0] = array_replace($data['hero']['slides'][0], [
    'eyebrow'=>(string)($draft['hero']['eyebrow']??''),'title'=>(string)($draft['hero']['title']??''),'text'=>(string)($draft['hero']['text']??''),
    'buttonText'=>(string)($draft['hero']['buttonText']??'Contact us'),'buttonHref'=>(string)($draft['hero']['buttonHref']??'#contact')
  ]);
  $data['about']['enabled'] = true;
  $data['about']['title'] = (string)($draft['about']['title'] ?? 'About us');
  $data['about']['subtitle'] = (string)($draft['about']['subtitle'] ?? '');
  $data['about']['body'] = (string)($draft['about']['body'] ?? '');
  $data['services']['enabled'] = true;
  $data['services']['items'] = is_array($draft['services'] ?? null) ? $draft['services'] : [];
  $data['contact']['enabled'] = true;
  $data['contact']['details']['address'] = (string)($draft['contact']['address'] ?? '');
  $data['contact']['details']['phone'] = (string)($draft['contact']['phone'] ?? '');
  $data['contact']['details']['email'] = (string)($draft['contact']['email'] ?? '');
  $data['settings']['theme']['brand'] = (string)($draft['brand'] ?? $data['settings']['theme']['brand'] ?? '#0d6efd');
  $data['settings']['sitePlan']['selected'] = (string)($draft['plan'] ?? 'premium');
  $data['settings']['sitePlan']['subdomain'] = (string)($draft['subdomain'] ?? 'yourbusiness');
  $data['settings']['sitePlan']['customDomain'] = (string)($draft['customDomain'] ?? '');
  $isPro = ($draft['plan'] ?? '') === 'premium-pro';
  $data['products']['enabled'] = $isPro;
  $data['products']['shopEnabled'] = $isPro;
  $data['products']['catalogEnabled'] = $isPro;
  $data['products']['cartEnabled'] = $isPro;
  if ($isPro && !empty($draft['products'])) $data['products']['items'] = $draft['products'];
  $chatChoice = (string)($draft['chatChoice'] ?? 'none');
  $data['chatbot']['enabled'] = $chatChoice !== 'none';
  if ($chatChoice !== 'none') $data['chatbot']['mode'] = $chatChoice;
  if ($chatChoice === 'whatsapp') $data['chatbot']['whatsapp']['number'] = (string)($draft['whatsapp'] ?? '');
  $data['booking']['enabled'] = !empty($draft['bookingNeeded']);
  $platform = trim((string)($data['settings']['sitePlan']['platformDomain'] ?? 'dev4.click')) ?: 'dev4.click';
  $custom = trim((string)($draft['customDomain'] ?? ''));
  $host = $custom !== '' ? $custom : ((string)($draft['subdomain'] ?? 'yourbusiness') . '.' . $platform);
  $requests[$index]['status'] = 'approved';
  $requests[$index]['approvedAt'] = date('c');
  $requests[$index]['websiteUrl'] = 'https://' . $host;
  $requests[$index]['draft'] = $draft;
  $data['settings']['demoIntakeAdmin']['requests'] = $requests;
  return $requests[$index];
}

function cms_demo_billing_amount(array $data, array $request): array {
  $currency = in_array(($request['currency'] ?? 'GBP'),['GBP','EUR'],true)?(string)$request['currency']:'GBP';
  $pro = ($request['plan'] ?? '') === 'premium-pro';
  if ($currency === 'EUR') $amount = (string)($data['settings']['sitePlan'][$pro?'premiumProEur':'premiumEur'] ?? ($pro?'7.00':'4.00'));
  else $amount = (string)($data['settings']['sitePlan'][$pro?'premiumProGbp':'premiumGbp'] ?? ($pro?'6.50':'3.50'));
  return [$currency,$amount,$currency==='EUR'?'€':'£'];
}

function cms_demo_send_bill(array &$data, int $index): array {
  $requests = $data['settings']['demoIntakeAdmin']['requests'] ?? [];
  if (!isset($requests[$index]) || !is_array($requests[$index])) throw new RuntimeException('Demo request not found.');
  $r = $requests[$index];
  if (($r['status'] ?? '') !== 'approved') throw new RuntimeException('Approve and build the website before sending billing.');
  $email = strtolower(trim((string)($r['email'] ?? '')));
  if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Client email is invalid.');
  [$currency,$amount,$symbol] = cms_demo_billing_amount($data,$r);
  $cfg = $data['settings']['demoIntakeAdmin'] ?? [];
  $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i','',(string)($cfg['invoicePrefix']??'DP'))) ?: 'DP';
  $invoice = (string)($r['invoiceNumber'] ?? '');
  if ($invoice === '') $invoice = $prefix . '-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)),0,6));
  $dueDays = max(0,min(90,(int)($cfg['dueDays']??7)));
  $due = date('Y-m-d', strtotime('+' . $dueDays . ' days'));
  $subject = trim((string)($cfg['billingSubject'] ?? 'Your website plan is ready')) ?: 'Your website plan is ready';
  $intro = trim((string)($cfg['billingMessage'] ?? 'Your website has been approved and prepared.'));
  $payment = trim((string)($cfg['paymentInstructions'] ?? ''));
  $planTitle = ($r['plan'] ?? '') === 'premium-pro' ? 'Premium Pro — E-commerce website' : 'Premium — Business website';
  $body = "Hello " . ((string)($r['name']??'') ?: 'there') . ",\n\n" . $intro . "\n\nInvoice reference: {$invoice}\nPlan: {$planTitle}\nAmount: {$symbol}{$amount} {$currency} / month\nPayment due: {$due}\nWebsite: " . ((string)($r['websiteUrl']??'') ?: cms_base_url($data)) . ($payment!==''?"\n\nPayment instructions:\n{$payment}":'') . "\n\nThank you.";
  if (!cms_send_mail($email,$subject,$body,$data)) throw new RuntimeException('Billing email could not be sent. Check Super Admin > Mailer settings and logs.');
  $requests[$index]['invoiceNumber'] = $invoice;
  $requests[$index]['billSentAt'] = date('c');
  $requests[$index]['billAmount'] = $amount;
  $requests[$index]['billCurrency'] = $currency;
  $data['settings']['demoIntakeAdmin']['requests'] = $requests;
  return $requests[$index];
}

function cms_chatbot_context(array $data, int $maxChars = 9000): string {
  $chunks=[]; $clean=fn($v)=>trim(preg_replace('/\s+/u',' ',strip_tags((string)$v))??'');
  $chunks[]='Site: '.$clean($data['settings']['siteName']??'');
  foreach(['about','services','features','pricing','faq','products','contact'] as $key){
    $section=$data[$key]??[]; if(!is_array($section)) continue; if(isset($section['title'])) $chunks[]=ucfirst($key).': '.$clean($section['title']).' '.$clean($section['subtitle']??$section['text']??'');
    foreach(array_slice(is_array($section['items']??null)?$section['items']:[],0,60) as $item){ if(!is_array($item)) continue; $bits=[]; foreach(['title','name','question','answer','text','excerpt','body','price','secondaryPrice','period'] as $field) if(!empty($item[$field])) $bits[]=$clean($item[$field]); if(!empty($item['features'])&&is_array($item['features'])) $bits[]=implode(', ',array_map($clean,$item['features'])); if($bits)$chunks[]=implode(' | ',$bits); }
  }
  if(!empty($data['contact']['details'])&&is_array($data['contact']['details'])) $chunks[]='Contact: '.implode(' | ',array_map($clean,$data['contact']['details']));
  foreach(($data['booking']['services']??[]) as $service) $chunks[]='Booking service: '.$clean($service['title']??$service['name']??'').' '.$clean($service['price']??'').' '.((int)($service['durationMinutes']??$service['duration']??0)).' minutes';
  return substr(implode("\n",array_filter($chunks)),0,max(1000,min(20000,$maxChars)));
}

function cms_chatbot_fallback(array $data, string $question): string {
  $lower=function($v){$v=(string)$v;return function_exists('mb_strtolower')?mb_strtolower($v,'UTF-8'):strtolower($v);}; $q=$lower(strip_tags($question)); $tokens=array_values(array_unique(array_filter(preg_split('/[^\pL\pN]+/u',$q)?:[],fn($x)=>strlen($x)>=3))); $candidates=[];
  foreach(['faq','services','products','pricing'] as $group) foreach(($data[$group]['items']??[]) as $item){ if(!is_array($item))continue; $text=implode(' ',array_filter([(string)($item['title']??''),(string)($item['question']??''),(string)($item['answer']??''),(string)($item['text']??''),(string)($item['body']??''),implode(' ',is_array($item['features']??null)?$item['features']:[])])); $low=$lower(strip_tags($text)); $score=0; foreach($tokens as $token) if(str_contains($low,$token))$score++; if($score>0)$candidates[]=[$score,$text]; }
  usort($candidates,fn($a,$b)=>$b[0]<=>$a[0]); if($candidates){$answer=trim(preg_replace('/\s+/u',' ',strip_tags((string)$candidates[0][1]))??'');return substr($answer,0,700);}
  return (string)($data['chatbot']['fallback']??$data['chatbot']['fallbackText']??'Please use the contact form and the team will help you.');
}

function cms_chatbot_reply(array $data, array $payload): array {
  if(empty($data['chatbot']['enabled']) || (($data['chatbot']['mode']??'chatbot')!=='chatbot')) throw new RuntimeException('AI chatbot is not enabled.'); $question=trim(strip_tags((string)($payload['message']??''))); if($question==='') throw new RuntimeException('Write a question first.'); if(strlen($question)>1200) throw new RuntimeException('Question is too long.');
  $tech=$data['chatbot']['technical']??[]; $knowledge=cms_chatbot_context($data,(int)($tech['maxContextChars']??9000)); $history=[]; foreach(array_slice(is_array($payload['history']??null)?$payload['history']:[],-8) as $row){$role=(string)($row['role']??'');$text=trim(strip_tags((string)($row['text']??'')));if(in_array($role,['user','assistant'],true)&&$text!=='')$history[]=ucfirst($role).': '.substr($text,0,500);} $prompt="Visitor question: {$question}".($history?"\n\nRecent conversation:\n".implode("\n",$history):'');
  $ai=$data['settings']['ai']??[]; if(!empty($tech['aiEnabled'])&&!empty($ai['enabled'])&&($ai['accessTier']??'free')==='pro'&&!empty($ai['apiKey'])){ try{$res=cms_ai_generate($data,$prompt,$knowledge,'',(string)($tech['systemPrompt']??''));return ['ok'=>true,'reply'=>$res['text'],'mode'=>'ai'];}catch(Throwable $e){} }
  return ['ok'=>true,'reply'=>cms_chatbot_fallback($data,$question),'mode'=>'local'];
}

function cms_now(): DateTimeImmutable {
  static $timezone = null;
  if (!$timezone) $timezone = new DateTimeZone(CMS_TIMEZONE);
  return new DateTimeImmutable('now', $timezone);
}

function cms_breakglass_slug(?DateTimeInterface $date = null): string {
  $date = $date ?: cms_now();
  return BREAKGLASS_PREFIX . $date->format('dmY');
}

function cms_request_relative_path(): string {
  $path = rawurldecode((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/'));
  $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
  if ($scriptDir !== '' && $scriptDir !== '/' && ($path === $scriptDir || str_starts_with($path, $scriptDir . '/'))) {
    $path = substr($path, strlen($scriptDir));
  }
  return trim($path, '/');
}

function cms_is_breakglass_request(): bool {
  if (array_key_exists('admin', $_GET)) return true;
  $path = cms_request_relative_path();
  if ($path !== '' && hash_equals(cms_breakglass_slug(), $path)) return true;
  $storedPath = (string)($_SESSION['cms_breakglass_path'] ?? '');
  $expires = (int)($_SESSION['cms_breakglass_exp'] ?? 0);
  return $storedPath !== '' && $expires >= time() && hash_equals($storedPath, $path);
}

function cms_client_ip(): string {
  return substr((string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 100);
}

function cms_rate_limit_consume(string $bucket, int $limit, int $windowSeconds): bool {
  $key = hash('sha256', CMS_SECRET . '|' . $bucket . '|' . cms_client_ip());
  $file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'lpcms-rate-' . $key . '.json';
  $now = time();
  $fp = @fopen($file, 'c+');
  if (!$fp) return true;
  if (!@flock($fp, LOCK_EX)) {
    fclose($fp);
    return true;
  }
  rewind($fp);
  $timestamps = json_decode((string)stream_get_contents($fp), true);
  if (!is_array($timestamps)) $timestamps = [];
  $timestamps = array_values(array_filter($timestamps, fn($ts) => is_numeric($ts) && (int)$ts > $now - $windowSeconds));
  $allowed = count($timestamps) < $limit;
  if ($allowed) $timestamps[] = $now;
  rewind($fp);
  ftruncate($fp, 0);
  fwrite($fp, json_encode($timestamps));
  fflush($fp);
  flock($fp, LOCK_UN);
  fclose($fp);
  return $allowed;
}

function cms_mask_email(string $email): string {
  [$local, $domain] = array_pad(explode('@', $email, 2), 2, '');
  if ($domain === '') return 'configured administrator email';
  $first = $local !== '' ? substr($local, 0, 1) : '*';
  return $first . str_repeat('*', max(4, strlen($local) - 1)) . '@' . $domain;
}

function cms_breakglass_csrf(): string {
  if (empty($_SESSION['cms_breakglass_csrf'])) $_SESSION['cms_breakglass_csrf'] = bin2hex(random_bytes(24));
  return (string)$_SESSION['cms_breakglass_csrf'];
}

function cms_breakglass_clear_challenge(): void {
  unset(
    $_SESSION['cms_breakglass_code_hash'],
    $_SESSION['cms_breakglass_exp'],
    $_SESSION['cms_breakglass_attempts'],
    $_SESSION['cms_breakglass_path']
  );
}

function cms_render_breakglass(string $stage = 'password', string $message = '', bool $error = false): void {
  header('Content-Type: text/html; charset=utf-8');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  header('X-Robots-Tag: noindex, nofollow, noarchive', true);
  $csrf = cms_breakglass_csrf();
  $maskedEmail = cms_mask_email(BREAKGLASS_ADMIN_EMAIL);
  $messageHtml = $message !== '' ? '<div class="notice ' . ($error ? 'error' : 'success') . '">' . cms_e($message) . '</div>' : '';
  $form = $stage === 'code'
    ? '<form method="post" autocomplete="off">'
      . '<input type="hidden" name="csrf" value="' . cms_e($csrf) . '">'
      . '<input type="hidden" name="action" value="code">'
      . '<label for="code">5-character email code</label>'
      . '<input id="code" name="code" type="text" inputmode="numeric" pattern="[A-Za-z0-9]{5}" minlength="5" maxlength="5" autocomplete="one-time-code" required autofocus>'
      . '<button type="submit">Verify and open admin</button>'
      . '</form>'
      . '<form method="post" class="secondary">'
      . '<input type="hidden" name="csrf" value="' . cms_e($csrf) . '">'
      . '<input type="hidden" name="action" value="reset">'
      . '<button type="submit" class="link-button">Start again</button>'
      . '</form>'
    : '<form method="post" autocomplete="off">'
      . '<input type="hidden" name="csrf" value="' . cms_e($csrf) . '">'
      . '<input type="hidden" name="action" value="password">'
      . '<label for="password">Super admin password</label>'
      . '<input id="password" name="password" type="password" autocomplete="current-password" required autofocus>'
      . '<button type="submit">Send verification code</button>'
      . '</form>';
  echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
    . '<meta name="robots" content="noindex,nofollow"><title>Administrator verification</title><style>'
    . ':root{color-scheme:light}*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px;background:radial-gradient(circle at top left,#dbeafe,transparent 40%),radial-gradient(circle at bottom right,#ede9fe,transparent 42%),#f8fafc;color:#172033;font:16px/1.55 Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.card{width:min(100%,440px);background:rgba(255,255,255,.94);border:1px solid rgba(15,23,42,.09);border-radius:26px;padding:30px;box-shadow:0 28px 90px rgba(15,23,42,.16)}.mark{width:48px;height:48px;border-radius:16px;display:grid;place-items:center;margin-bottom:20px;background:linear-gradient(135deg,#0d6efd,#6610f2);color:white;font-weight:800;box-shadow:0 14px 30px rgba(13,110,253,.24)}h1{margin:0 0 8px;font-size:1.7rem;letter-spacing:-.035em}p{margin:0 0 20px;color:#64748b}label{display:block;margin:0 0 7px;font-weight:700;color:#334155}input{width:100%;border:1px solid #cbd5e1;border-radius:14px;padding:13px 14px;font:inherit;outline:none;background:#fff}input:focus{border-color:#0d6efd;box-shadow:0 0 0 4px rgba(13,110,253,.1)}button{width:100%;border:0;border-radius:999px;margin-top:14px;padding:13px 18px;font:inherit;font-weight:750;cursor:pointer;background:linear-gradient(135deg,#0d6efd,#6610f2);color:#fff;box-shadow:0 14px 34px rgba(13,110,253,.22)}.notice{border-radius:14px;padding:11px 13px;margin:0 0 18px;font-weight:650}.notice.error{background:#fff1f2;color:#9f1239;border:1px solid #fecdd3}.notice.success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}.secondary{margin-top:6px}.link-button{background:transparent;color:#475569;box-shadow:none;margin-top:5px;padding:9px}.small{font-size:.86rem;color:#64748b;margin-top:18px}

</style></head><body><main class="card">'
    . '<div class="mark">LP</div><h1>Administrator verification</h1><p>Super admin access is protected by the LPCMS password and a one-time code sent to ' . cms_e($maskedEmail) . '.</p>'
    . $messageHtml . $form
    . '<div class="small">The 5-character code expires after 10 minutes. This page is not indexed and responses are not cached.</div>'
    . '</main></body></html>';
  exit;
}

function cms_handle_breakglass(array $data): void {
  $hasChallenge = !empty($_SESSION['cms_breakglass_code_hash']) && (int)($_SESSION['cms_breakglass_exp'] ?? 0) >= time();
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') cms_render_breakglass($hasChallenge ? 'code' : 'password');

  $postedCsrf = (string)($_POST['csrf'] ?? '');
  if ($postedCsrf === '' || !hash_equals(cms_breakglass_csrf(), $postedCsrf)) {
    cms_render_breakglass($hasChallenge ? 'code' : 'password', 'The form expired. Reload the page and try again.', true);
  }

  $action = (string)($_POST['action'] ?? '');
  if ($action === 'reset') {
    cms_breakglass_clear_challenge();
    cms_render_breakglass('password');
  }

  if ($action === 'password') {
    if (!cms_rate_limit_consume('breakglass-password', 20, 3600)) {
      cms_render_breakglass('password', 'Too many attempts. Try again later.', true);
    }
    $expected = BREAKGLASS_PASSWORD;
    $password = (string)($_POST['password'] ?? '');
    if (!hash_equals($expected, $password)) {
      usleep(350000);
      cms_render_breakglass('password', 'Invalid credentials.', true);
    }
    $lastSend = (int)($_SESSION['cms_breakglass_last_send'] ?? 0);
    if ($lastSend > time() - 60) {
      cms_render_breakglass('password', 'A code was sent recently. Wait one minute before requesting another.', true);
    }
    if (!cms_rate_limit_consume('breakglass-send', 6, 3600)) {
      cms_render_breakglass('password', 'The hourly code limit has been reached. Try again later.', true);
    }

    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < BREAKGLASS_CODE_LENGTH; $i++) $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    $_SESSION['cms_breakglass_code_hash'] = hash_hmac('sha256', $code, CMS_SECRET);
    $_SESSION['cms_breakglass_exp'] = time() + BREAKGLASS_OTP_TTL;
    $_SESSION['cms_breakglass_attempts'] = 0;
    $_SESSION['cms_breakglass_path'] = cms_request_relative_path();
    $timeText = cms_now()->format('Y-m-d H:i:s T');
    $body = "Your LPCMS super administrator verification code is: {$code}\n\n"
      . "It expires in 10 minutes and can be used once.\n\n"
      . "Requested: {$timeText}\nIP: " . cms_client_ip() . "\n\n"
      . "If you did not request this code, ignore this email.";
    $sent = cms_send_mail(BREAKGLASS_ADMIN_EMAIL, 'LPCMS administrator verification code', $body);
    if (!$sent) {
      cms_breakglass_clear_challenge();
      cms_render_breakglass('password', 'The verification email could not be sent. Configure PHP mail or SMTP and try again.', true);
    }
    $_SESSION['cms_breakglass_last_send'] = time();
    cms_render_breakglass('code', 'A 5-character verification code was sent to ' . cms_mask_email(BREAKGLASS_ADMIN_EMAIL) . '.');
  }

  if ($action === 'code') {
    $expires = (int)($_SESSION['cms_breakglass_exp'] ?? 0);
    $expectedHash = (string)($_SESSION['cms_breakglass_code_hash'] ?? '');
    if ($expectedHash === '' || $expires < time()) {
      cms_breakglass_clear_challenge();
      cms_render_breakglass('password', 'The verification code expired. Start again.', true);
    }
    if (!cms_rate_limit_consume('breakglass-code', 25, 3600)) {
      cms_breakglass_clear_challenge();
      cms_render_breakglass('password', 'Too many verification attempts. Start again later.', true);
    }
    $attempts = (int)($_SESSION['cms_breakglass_attempts'] ?? 0) + 1;
    $_SESSION['cms_breakglass_attempts'] = $attempts;
    $code = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', (string)($_POST['code'] ?? '')));
    $valid = strlen($code) === BREAKGLASS_CODE_LENGTH && hash_equals($expectedHash, hash_hmac('sha256', $code, CMS_SECRET));
    if (!$valid) {
      if ($attempts >= BREAKGLASS_MAX_CODE_ATTEMPTS) {
        cms_breakglass_clear_challenge();
        cms_render_breakglass('password', 'Too many incorrect codes. Start again.', true);
      }
      $remaining = BREAKGLASS_MAX_CODE_ATTEMPTS - $attempts;
      cms_render_breakglass('code', 'Incorrect code. ' . $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' remaining.', true);
    }

    session_regenerate_id(true);
    $_SESSION['cms_admin_email'] = BREAKGLASS_ADMIN_EMAIL;
    $_SESSION['cms_admin_role'] = 'super';
    $_SESSION['cms_csrf'] = bin2hex(random_bytes(20));
    cms_breakglass_clear_challenge();
    unset($_SESSION['cms_breakglass_csrf'], $_SESSION['cms_breakglass_last_send']);
    header('Location: ' . cms_route_prefix(), true, 303);
    exit;
  }

  cms_render_breakglass($hasChallenge ? 'code' : 'password', 'Invalid request.', true);
}

function cms_detect_mime(string $file): string {
  $detected = '';
  if (function_exists('finfo_open')) {
    $finfo = @finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
      $detected = strtolower((string)@finfo_file($finfo, $file));
      finfo_close($finfo);
      if ($detected !== '' && !in_array($detected, ['application/octet-stream', 'binary/octet-stream'], true)) return $detected;
    }
  }
  $size = @getimagesize($file);
  $imageMime = strtolower((string)($size['mime'] ?? ''));
  if ($imageMime !== '') return $imageMime;

  // Some shared hosts do not identify AVIF/HEIC correctly. Inspect the ISO-BMFF brand as a fallback.
  $head = (string)@file_get_contents($file, false, null, 0, 64);
  if (strlen($head) >= 12 && substr($head, 4, 4) === 'ftyp') {
    $brands = substr($head, 8);
    if (str_contains($brands, 'avif') || str_contains($brands, 'avis')) return 'image/avif';
    foreach (['heic', 'heix', 'hevc', 'hevx', 'heis', 'heim'] as $brand) {
      if (str_contains($brands, $brand)) return 'image/heic';
    }
    if (str_contains($brands, 'mif1') || str_contains($brands, 'msf1')) return 'image/heif';
  }
  return $detected !== '' ? $detected : 'application/octet-stream';
}

function cms_is_avif_file(string $file): bool {
  if (cms_detect_mime($file) === 'image/avif') return true;
  $head = (string)@file_get_contents($file, false, null, 0, 64);
  return str_contains($head, 'ftypavif') || str_contains($head, 'ftypavis');
}

function cms_image_dimensions(string $file): array {
  $size = @getimagesize($file);
  if (!is_array($size)) return ['width' => 0, 'height' => 0];
  return ['width' => (int)($size[0] ?? 0), 'height' => (int)($size[1] ?? 0)];
}

function cms_validate_image_dimensions(array $dimensions): void {
  $width = (int)($dimensions['width'] ?? 0);
  $height = (int)($dimensions['height'] ?? 0);
  if ($width > 20000 || $height > 20000 || ($width > 0 && $height > 0 && $width * $height > 100000000)) {
    throw new RuntimeException('Image dimensions are too large.');
  }
}

function cms_media_resize_options(?array $data = null, array $input = []): array {
  $media = is_array($data) ? ($data['settings']['media'] ?? []) : [];
  $preset = strtolower(trim((string)($input['boxPreset'] ?? $media['boxPreset'] ?? 'large')));
  $presets = [
    'large' => [1920, 1920], 'hero' => [1600, 900], 'content' => [1200, 900],
    'card' => [900, 675], 'square' => [800, 800], 'logo' => [800, 400], 'custom' => [0, 0]
  ];
  [$presetW,$presetH] = $presets[$preset] ?? $presets['large'];
  $legacy = (int)($media['maxImageWidth'] ?? IMAGE_MAX_WIDTH);
  $w = (int)($input['boxWidth'] ?? ($presetW ?: ($media['boxWidth'] ?? $legacy)));
  $h = (int)($input['boxHeight'] ?? ($presetH ?: ($media['boxHeight'] ?? $legacy)));
  $w = max(64, min(4096, $w ?: $legacy ?: IMAGE_MAX_WIDTH));
  $h = max(64, min(4096, $h ?: $w));
  $quality = (int)($input['quality'] ?? $media['avifQuality'] ?? IMAGE_AVIF_QUALITY);
  $quality = max(35, min(95, $quality));
  return ['boxWidth'=>$w,'boxHeight'=>$h,'boxPreset'=>$preset,'quality'=>$quality];
}

function cms_fit_image_dimensions(array $dimensions, int $maxWidth, int $maxHeight): array {
  $width=max(1,(int)($dimensions['width']??1)); $height=max(1,(int)($dimensions['height']??1));
  $ratio=min(1, $maxWidth/$width, $maxHeight/$height);
  return ['width'=>max(1,(int)round($width*$ratio)),'height'=>max(1,(int)round($height*$ratio))];
}

function cms_convert_image_imagick(string $source, string $destination, int $targetWidth, int $targetHeight, int $quality): bool {
  if (!class_exists('Imagick')) return false;
  try {
    if (method_exists('Imagick', 'queryFormats') && count(Imagick::queryFormats('AVIF')) === 0) return false;
    $image = new Imagick(); $image->readImage($source);
    if ($image->getNumberImages() > 1) $image->setIteratorIndex(0);
    if (method_exists($image, 'autoOrientImage')) $image->autoOrientImage();
    if ($image->getImageWidth() !== $targetWidth || $image->getImageHeight() !== $targetHeight) $image->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1, false);
    $image->setImagePage(0,0,0,0); $image->stripImage(); $image->setImageFormat('avif'); $image->setImageCompressionQuality($quality);
    $ok=$image->writeImage($destination); $image->clear(); $image->destroy();
    return $ok && is_file($destination) && filesize($destination)>0;
  } catch(Throwable $e){ @unlink($destination); return false; }
}

function cms_gd_open_image(string $source, string $mime) {
  return match ($mime) {
    'image/jpeg' => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($source) : false,
    'image/png' => function_exists('imagecreatefrompng') ? @imagecreatefrompng($source) : false,
    'image/gif' => function_exists('imagecreatefromgif') ? @imagecreatefromgif($source) : false,
    'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
    'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($source) : false,
    default => false,
  };
}

function cms_gd_auto_orient($image, string $source, string $mime) {
  if ($mime !== 'image/jpeg' || !function_exists('exif_read_data')) return $image;
  $exif=@exif_read_data($source); $orientation=(int)($exif['Orientation']??1);
  $rotate=static function($current,int $degrees){$rotated=@imagerotate($current,$degrees,0);if(!$rotated)return $current;imagedestroy($current);return $rotated;};
  if($orientation===2&&function_exists('imageflip'))@imageflip($image,IMG_FLIP_HORIZONTAL); if($orientation===3)$image=$rotate($image,180); if($orientation===4&&function_exists('imageflip'))@imageflip($image,IMG_FLIP_VERTICAL);
  if($orientation===5){if(function_exists('imageflip'))@imageflip($image,IMG_FLIP_HORIZONTAL);$image=$rotate($image,90);} if($orientation===6)$image=$rotate($image,-90);
  if($orientation===7){if(function_exists('imageflip'))@imageflip($image,IMG_FLIP_HORIZONTAL);$image=$rotate($image,-90);} if($orientation===8)$image=$rotate($image,90); return $image;
}

function cms_convert_image_gd(string $source, string $destination, string $mime, int $targetWidth, int $targetHeight, int $quality): bool {
  if (!function_exists('imageavif')) return false; $image=null; $output=null;
  try {
    $image=cms_gd_open_image($source,$mime); if(!$image)return false; $image=cms_gd_auto_orient($image,$source,$mime); if(!$image)return false;
    $width=imagesx($image);$height=imagesy($image);if($width<1||$height<1)return false;
    $output=@imagecreatetruecolor($targetWidth,$targetHeight);if(!$output)return false;@imagealphablending($output,false);@imagesavealpha($output,true);
    $transparent=@imagecolorallocatealpha($output,0,0,0,127);if($transparent!==false)@imagefilledrectangle($output,0,0,$targetWidth,$targetHeight,$transparent);
    if(!@imagecopyresampled($output,$image,0,0,0,0,$targetWidth,$targetHeight,$width,$height))return false;
    $ok=@imageavif($output,$destination,$quality); return $ok&&is_file($destination)&&filesize($destination)>0;
  } catch(Throwable $e){@unlink($destination);return false;} finally{if($output)@imagedestroy($output);if($image)@imagedestroy($image);}
}

function cms_run_process(array $command, ?string &$error = null): bool {
  if (!function_exists('proc_open')) return false;
  try { $pipes=[];$process=@proc_open($command,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);if(!is_resource($process))return false;
    if(isset($pipes[0])&&is_resource($pipes[0]))fclose($pipes[0]); if(isset($pipes[1])&&is_resource($pipes[1])){stream_get_contents($pipes[1]);fclose($pipes[1]);}
    if(isset($pipes[2])&&is_resource($pipes[2])){$error=trim((string)stream_get_contents($pipes[2]));fclose($pipes[2]);} return proc_close($process)===0;
  } catch(Throwable $e){$error=$e->getMessage();return false;}
}
function cms_find_binary(string $name, array $commonPaths = []): ?string { foreach($commonPaths as $path)if(is_file($path)&&is_executable($path))return $path;return $name; }
function cms_convert_image_cli(string $source, string $destination, int $targetWidth, int $targetHeight, int $quality): bool {
  $magick=cms_find_binary('magick',['/usr/bin/magick','/usr/local/bin/magick','/opt/imagemagick/bin/magick']);$error='';
  if(cms_run_process([$magick,$source.'[0]','-auto-orient','-resize',$targetWidth.'x'.$targetHeight.'>','-strip','-quality',(string)$quality,$destination],$error)&&is_file($destination)&&filesize($destination)>0&&cms_is_avif_file($destination))return true;
  @unlink($destination); $ffmpeg=cms_find_binary('ffmpeg',['/usr/bin/ffmpeg','/usr/local/bin/ffmpeg','/opt/homebrew/bin/ffmpeg']);
  $ok=cms_run_process([$ffmpeg,'-hide_banner','-loglevel','error','-y','-i',$source,'-map_metadata','-1','-frames:v','1','-vf','scale='.$targetWidth.':'.$targetHeight,'-c:v','libaom-av1','-still-picture','1','-crf','30','-b:v','0','-cpu-used','6',$destination],$error);
  return $ok&&is_file($destination)&&filesize($destination)>0&&cms_is_avif_file($destination);
}

function cms_store_image_as_avif(string $source, string $originalName, string $targetDir, array $options = []): array {
  $mime=cms_detect_mime($source); $allowed=['image/jpeg','image/png','image/gif','image/webp','image/avif','image/heic','image/heif','image/heic-sequence','image/heif-sequence','image/x-heic','image/x-heif'];
  if(!in_array($mime,$allowed,true))throw new RuntimeException('Please upload a JPG, PNG, GIF, WebP, HEIC, or AVIF image.');
  $dimensions=cms_image_dimensions($source);cms_validate_image_dimensions($dimensions);if(!is_dir($targetDir)&&!@mkdir($targetDir,0755,true)&&!is_dir($targetDir))throw new RuntimeException('Upload directory could not be created.');
  $opts=cms_media_resize_options(null,$options);$target=cms_fit_image_dimensions($dimensions,$opts['boxWidth'],$opts['boxHeight']);
  $base=cms_slug(pathinfo($originalName,PATHINFO_FILENAME));$name=date('Ymd-His').'-'.$base.'-'.bin2hex(random_bytes(3)).'.avif';$destination=rtrim($targetDir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;$temporary=$destination.'.tmp-'.bin2hex(random_bytes(3)).'.avif';$method='';
  $alreadySuitable=$mime==='image/avif'&&(int)($dimensions['width']??0)===$target['width']&&(int)($dimensions['height']??0)===$target['height'];
  if($alreadySuitable&&@copy($source,$temporary))$method='existing-avif';
  elseif(cms_convert_image_imagick($source,$temporary,$target['width'],$target['height'],$opts['quality']))$method='imagick';
  elseif(cms_convert_image_gd($source,$temporary,$mime,$target['width'],$target['height'],$opts['quality']))$method='gd';
  elseif(cms_convert_image_cli($source,$temporary,$target['width'],$target['height'],$opts['quality']))$method='cli';
  else{@unlink($temporary);throw new RuntimeException('AVIF conversion is required but unavailable on this server. Enable GD/Imagick AVIF support or ImageMagick/ffmpeg.');}
  if(!cms_is_avif_file($temporary)){@unlink($temporary);throw new RuntimeException('The uploaded image could not be converted to AVIF.');}
  if(!@rename($temporary,$destination)){@unlink($temporary);throw new RuntimeException('The converted AVIF image could not be saved.');}@chmod($destination,0644);
  $outputDimensions=cms_image_dimensions($destination);if(($outputDimensions['width']??0)===0)$outputDimensions=$target;
  return ['name'=>$name,'path'=>$destination,'width'=>(int)$outputDimensions['width'],'height'=>(int)$outputDimensions['height'],'bytes'=>(int)filesize($destination),'method'=>$method,'format'=>'avif','boxPreset'=>$opts['boxPreset']];
}

function cms_store_hero_video(string $source, string $originalName, string $targetDir): array {
  if (!is_dir($targetDir) && !@mkdir($targetDir,0755,true) && !is_dir($targetDir)) throw new RuntimeException('Video upload directory could not be created.');
  $mime=cms_detect_mime($source); $ext=strtolower(pathinfo($originalName,PATHINFO_EXTENSION));
  $allowedMime=['video/mp4','video/webm','application/octet-stream'];
  if(!in_array($mime,$allowedMime,true) && !in_array($ext,['mp4','webm'],true)) throw new RuntimeException('Please upload an MP4 or WebM video.');
  $base=cms_slug(pathinfo($originalName,PATHINFO_FILENAME)) ?: 'hero-video';
  $name=date('Ymd-His').'-'.$base.'-'.bin2hex(random_bytes(3)).'.mp4';
  $destination=rtrim($targetDir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$name;
  $temporary=$destination.'.tmp-'.bin2hex(random_bytes(3)).'.mp4';
  $ffmpeg=cms_find_binary('ffmpeg',['/usr/bin/ffmpeg','/usr/local/bin/ffmpeg','/opt/homebrew/bin/ffmpeg']); $error='';
  $ok=cms_run_process([$ffmpeg,'-hide_banner','-loglevel','error','-y','-i',$source,'-map_metadata','-1','-an','-vf','scale=1280:-2:force_original_aspect_ratio=decrease','-c:v','libx264','-preset','medium','-crf','26','-pix_fmt','yuv420p','-movflags','+faststart',$temporary],$error);
  if($ok && is_file($temporary) && filesize($temporary)>0){
    if(!@rename($temporary,$destination)){@unlink($temporary);throw new RuntimeException('The optimized video could not be saved.');}
    @chmod($destination,0644);
    return ['name'=>$name,'path'=>$destination,'bytes'=>(int)filesize($destination),'format'=>'mp4','optimized'=>true];
  }
  @unlink($temporary);
  $fallbackExt=$ext==='webm'?'webm':'mp4';
  $fallbackName=date('Ymd-His').'-'.$base.'-'.bin2hex(random_bytes(3)).'.'.$fallbackExt;
  $fallback=rtrim($targetDir,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$fallbackName;
  if(!@move_uploaded_file($source,$fallback)) throw new RuntimeException('Video upload failed. Install ffmpeg for automatic HD optimization.');
  @chmod($fallback,0644);
  return ['name'=>$fallbackName,'path'=>$fallback,'bytes'=>(int)filesize($fallback),'format'=>$fallbackExt,'optimized'=>false];
}

function cms_admin_role(): string {
  if (empty($_SESSION['cms_admin_email'])) return '';
  $sessionRole = strtolower((string)($_SESSION['cms_admin_role'] ?? ''));
  if (in_array($sessionRole, ['super', 'editor'], true)) return $sessionRole;
  $user = cms_user_from_email(cms_load_data(), (string)$_SESSION['cms_admin_email']);
  $role = $user['role'] ?? '';
  if (in_array($role, ['super', 'editor'], true)) {
    $_SESSION['cms_admin_role'] = $role;
    return $role;
  }
  return '';
}

function cms_is_admin(): bool {
  return cms_admin_role() !== '';
}

function cms_is_super_admin(): bool {
  return cms_admin_role() === 'super';
}

function cms_require_admin(): void {
  if (!cms_is_admin()) cms_json(['ok' => false, 'error' => 'Unauthorized'], 401);
  $csrf = $_SERVER['HTTP_X_CMS_CSRF'] ?? '';
  if ($_SERVER['REQUEST_METHOD'] !== 'GET' && (!isset($_SESSION['cms_csrf']) || !hash_equals($_SESSION['cms_csrf'], $csrf))) {
    cms_json(['ok' => false, 'error' => 'Bad CSRF token'], 419);
  }
}

function cms_require_super_admin(): void {
  cms_require_admin();
  if (!cms_is_super_admin()) cms_json(['ok' => false, 'error' => 'Super administrator access required.'], 403);
}

function cms_filter_editor_payload(array $payload, array $current): array {
  $locks = [
    ['settings','users'], ['settings','htaccess'], ['settings','libraries'], ['settings','analytics'], ['settings','hcaptcha'], ['settings','forms'],
    ['settings','cache'], ['settings','media'], ['settings','mediaSources'], ['settings','ai'], ['settings','app'], ['settings','sitePlan'], ['settings','abandonedCart'], ['settings','demoIntakeAdmin'], ['demoIntake'],
    ['settings','themeManager'], ['settings','headless'], ['settings','mailer'], ['settings','commerceIntegrations'], ['booking','technical'], ['chatbot','technical'], ['redirects'], ['embeds']
  ];
  foreach ($locks as $path) {
    $src = $current;
    foreach ($path as $part) {
      if (!is_array($src) || !array_key_exists($part, $src)) { $src = null; break; }
      $src = $src[$part];
    }
    $ref =& $payload;
    foreach ($path as $i => $part) {
      if ($i === count($path)-1) { $ref[$part] = $src; break; }
      if (!isset($ref[$part]) || !is_array($ref[$part])) $ref[$part] = [];
      $ref =& $ref[$part];
    }
    unset($ref);
  }
  if (!empty($current['settings']['forms']['submissions'])) {
    $payload['settings']['forms']['submissions'] = $current['settings']['forms']['submissions'];
  }
  return $payload;
}

function cms_strip_private_settings(array $data): array {
  if (isset($data['settings']['mailer']['smtp'])) {
    $has = !empty($data['settings']['mailer']['smtp']['password']);
    $data['settings']['mailer']['smtp']['password'] = '';
    $data['settings']['mailer']['smtp']['passwordSet'] = $has;
  }
  if (isset($data['settings']['commerceIntegrations']['webhook'])) {
    $has = !empty($data['settings']['commerceIntegrations']['webhook']['bearerToken']);
    $data['settings']['commerceIntegrations']['webhook']['bearerToken'] = '';
    $data['settings']['commerceIntegrations']['webhook']['bearerTokenSet'] = $has;
  }
  if (isset($data['settings']['commerceIntegrations']['checkout'])) {
    $has = !empty($data['settings']['commerceIntegrations']['checkout']['bearerToken']);
    $data['settings']['commerceIntegrations']['checkout']['bearerToken'] = '';
    $data['settings']['commerceIntegrations']['checkout']['bearerTokenSet'] = $has;
  }
  if (isset($data['settings']['ai'])) {
    $has = !empty($data['settings']['ai']['apiKey']);
    $data['settings']['ai']['apiKey'] = '';
    $data['settings']['ai']['apiKeySet'] = $has;
  }
  if (isset($data['settings']['abandonedCart'])) {
    $has = !empty($data['settings']['abandonedCart']['cronKey']);
    $data['settings']['abandonedCart']['cronKey'] = '';
    $data['settings']['abandonedCart']['cronKeySet'] = $has;
  }
  foreach (['pexels','pixabay','unsplash'] as $provider) {
    if (isset($data['settings']['mediaSources'][$provider])) {
      $has = !empty($data['settings']['mediaSources'][$provider]['apiKey']);
      $data['settings']['mediaSources'][$provider]['apiKey'] = '';
      $data['settings']['mediaSources'][$provider]['apiKeySet'] = $has;
    }
  }
  return $data;
}

function cms_public_data(array $data): array {
  $data = cms_strip_private_settings($data);
  $admin = cms_is_admin();
  $super = cms_is_super_admin();
  if ($super) return $data;

  unset($data['settings']['hcaptcha']['secret']);
  unset($data['settings']['forms']);
  unset($data['settings']['users']);
  unset($data['settings']['mailer']);
  unset($data['settings']['demoIntakeAdmin']);
  unset($data['settings']['commerceIntegrations']);
  unset($data['settings']['mediaSources']);

  $ai = $data['settings']['ai'] ?? [];
  $data['settings']['ai'] = $admin ? [
    'enabled' => !empty($ai['enabled']),
    'accessTier' => (string)($ai['accessTier'] ?? 'free'),
    'providerConnected' => !empty($ai['apiKeySet']),
  ] : [];

  $abandoned = $data['settings']['abandonedCart'] ?? [];
  $data['settings']['abandonedCart'] = ['enabled' => !empty($abandoned['enabled'])];
  if (!$admin) unset($data['settings']['sitePlan']);

  if (isset($data['booking']) && is_array($data['booking'])) {
    unset($data['booking']['technical']);
    if (!$admin) unset($data['booking']['bookings']);
  }
  if (isset($data['chatbot']) && is_array($data['chatbot'])) unset($data['chatbot']['technical']);
  return $data;
}

function cms_headless_public_data(array $data): array {
  $data = cms_strip_private_settings($data);
  unset($data['settings']['hcaptcha']['secret']);
  unset($data['settings']['forms']);
  unset($data['settings']['users']);
  unset($data['settings']['mailer']);
  unset($data['settings']['demoIntakeAdmin']);
  unset($data['settings']['commerceIntegrations']);
  unset($data['settings']['ai']);
  unset($data['settings']['mediaSources']);
  unset($data['settings']['sitePlan']);
  $data['settings']['abandonedCart'] = ['enabled' => !empty($data['settings']['abandonedCart']['enabled'])];
  if (isset($data['booking']) && is_array($data['booking'])) { unset($data['booking']['bookings'], $data['booking']['technical']); }
  if (isset($data['chatbot']) && is_array($data['chatbot'])) unset($data['chatbot']['technical']);
  return $data;
}

function cms_theme_presets(): array {
  return [
    ['id'=>'lpcms-default','title'=>'LPCMS Default','description'=>'Original LPCMS blue/violet design.','palette'=>['brand'=>'#0d6efd','brand2'=>'#6610f2','accent'=>'#20c997','text'=>'#1f2937','muted'=>'#6b7280','bg'=>'#ffffff','softBg'=>'#f6f8fb','radius'=>'22px']],
    ['id'=>'lpcms-cobalt','title'=>'Cobalt Studio','description'=>'Deep blue corporate theme with compact corners.','palette'=>['brand'=>'#0f4c81','brand2'=>'#2563eb','accent'=>'#14b8a6','text'=>'#172033','muted'=>'#64748b','bg'=>'#ffffff','softBg'=>'#f1f5f9','radius'=>'16px']],
    ['id'=>'lpcms-forest','title'=>'Forest Business','description'=>'Green and charcoal business theme.','palette'=>['brand'=>'#166534','brand2'=>'#15803d','accent'=>'#d97706','text'=>'#1f2937','muted'=>'#6b7280','bg'=>'#ffffff','softBg'=>'#f0fdf4','radius'=>'18px']],
    ['id'=>'lpcms-slate','title'=>'Slate Minimal','description'=>'Neutral monochrome theme for professional sites.','palette'=>['brand'=>'#334155','brand2'=>'#0f172a','accent'=>'#64748b','text'=>'#0f172a','muted'=>'#64748b','bg'=>'#ffffff','softBg'=>'#f8fafc','radius'=>'12px']],
    ['id'=>'lpcms-sunset','title'=>'Sunset Creative','description'=>'Warm orange/pink palette for marketing sites.','palette'=>['brand'=>'#ea580c','brand2'=>'#db2777','accent'=>'#f59e0b','text'=>'#292524','muted'=>'#78716c','bg'=>'#fffdfa','softBg'=>'#fff7ed','radius'=>'24px']],
  ];
}

function cms_free_theme_catalog(): array {
  $items = [
    ['agency','Agency','startbootstrap-agency','One-page agency and services design.'],
    ['creative','Creative','startbootstrap-creative','Bold one-page marketing and portfolio design.'],
    ['landing-page','Landing Page','startbootstrap-landing-page','Lead-generation and product landing page.'],
    ['business-frontpage','Business Frontpage','startbootstrap-business-frontpage','General company and SME front page.'],
    ['small-business','Small Business','startbootstrap-small-business','Simple small-business starter layout.'],
    ['shop-homepage','Shop Homepage','startbootstrap-shop-homepage','Bootstrap shop/catalogue card layout.'],
    ['modern-business','Modern Business','startbootstrap-modern-business','Multipage corporate/business design.'],
    ['grayscale','Grayscale','startbootstrap-grayscale','Dark/monochrome one-page design.'],
    ['new-age','New Age','startbootstrap-new-age','App/PWA landing page design.'],
    ['blog-home','Blog Home','startbootstrap-blog-home','Blog archive/homepage layout.'],
  ];
  return array_map(function($x) {
    [$id,$title,$repo,$description] = $x;
    $downloads = [
      'https://codeload.github.com/StartBootstrap/' . $repo . '/zip/refs/heads/master',
      'https://codeload.github.com/StartBootstrap/' . $repo . '/zip/refs/heads/main',
      'https://github.com/StartBootstrap/' . $repo . '/archive/refs/heads/master.zip',
      'https://github.com/StartBootstrap/' . $repo . '/archive/refs/heads/main.zip',
    ];
    return [
      'id' => 'free-' . $id,
      'title' => $title,
      'description' => $description,
      'provider' => 'Start Bootstrap',
      'license' => 'MIT',
      'source' => 'https://github.com/StartBootstrap/' . $repo,
      'download' => $downloads[0],
      'downloads' => $downloads,
      'manualDownload' => $downloads[0],
    ];
  }, $items);
}

function cms_headless_settings(array $data): array {
  return array_replace([
    'enabled' => true,
    'restEnabled' => true,
    'graphqlEnabled' => true,
    'corsOrigins' => [],
    'cacheSeconds' => 60,
  ], $data['settings']['headless'] ?? []);
}

function cms_apply_headless_cors(array $data): void {
  $settings = cms_headless_settings($data);
  $origins = is_array($settings['corsOrigins'] ?? null) ? $settings['corsOrigins'] : [];
  $origin = trim((string)($_SERVER['HTTP_ORIGIN'] ?? ''));
  if ($origin !== '' && in_array('*', $origins, true)) {
    header('Access-Control-Allow-Origin: *');
  } elseif ($origin !== '' && in_array($origin, $origins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
  }
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type, X-CMS-CSRF');
  header('X-LPCMS-Headless: 1');
  $seconds = max(0, min(3600, (int)($settings['cacheSeconds'] ?? 60)));
  if (!cms_is_admin()) header('Cache-Control: public, max-age=' . $seconds);
}

function cms_data_path($value, string $path) {
  if ($path === '' || $path === '.') return $value;
  foreach (explode('.', $path) as $part) {
    if (!is_array($value) || !array_key_exists($part, $value)) return null;
    $value = $value[$part];
  }
  return $value;
}

function cms_headless_resource(array $data, string $resource, string $slug = '') {
  $public = cms_headless_public_data($data);
  if ($resource === '' || $resource === 'site') return $public;
  if ($resource === 'settings') return $public['settings'] ?? [];
  if ($resource === 'navigation' || $resource === 'header') return $public['header'] ?? [];
  if ($resource === 'sections') {
    $out = $public;
    unset($out['settings'], $out['header'], $out['footer'], $out['redirects'], $out['embeds']);
    return $out;
  }
  $groups = ['products','blog','pages','services','gallery','testimonials'];
  if (in_array($resource, $groups, true)) {
    $group = $public[$resource] ?? [];
    if ($slug === '') return $group;
    foreach (($group['items'] ?? []) as $item) {
      if (cms_slug((string)($item['slug'] ?? $item['title'] ?? '')) === cms_slug($slug)) return $item;
    }
    return null;
  }
  return cms_data_path($public, $resource);
}

function cms_graphql_schema(): string {
  return <<<'GQL'
type Query {
  site: JSON
  settings: JSON
  header: JSON
  footer: JSON
  sections: JSON
  products: JSON
  product(slug: String!): JSON
  blog: JSON
  post(slug: String!): JSON
  pages: JSON
  page(slug: String!): JSON
  search(query: String!): JSON
  path(value: String!): JSON
}
scalar JSON
GQL;
}

function cms_graphql_tokens(string $query): array {
  $query = preg_replace('/#[^\r\n]*/', '', $query) ?? $query;
  $pattern = <<<'REGEX'
~\.\.\.|\$[A-Za-z_][A-Za-z0-9_]*|[A-Za-z_][A-Za-z0-9_]*|-?\d+(?:\.\d+)?|"(?:\\.|[^"\\])*"|[!$():=@\[\]{|},]~
REGEX;
  preg_match_all($pattern, $query, $m);
  return $m[0] ?? [];
}

function cms_graphql_parse_value(array $tokens, int &$i, array $variables) {
  $tok = $tokens[$i] ?? null;
  if ($tok === null) return null;
  if (str_starts_with($tok, '$')) { $i++; return $variables[substr($tok, 1)] ?? null; }
  if (($tok[0] ?? '') === '"') { $i++; $v = json_decode($tok, true); return is_string($v) ? $v : trim($tok, '"'); }
  if ($tok === 'true' || $tok === 'false') { $i++; return $tok === 'true'; }
  if ($tok === 'null') { $i++; return null; }
  if (is_numeric($tok)) { $i++; return str_contains($tok, '.') ? (float)$tok : (int)$tok; }
  if ($tok === '[') {
    $i++; $out = [];
    while (($tokens[$i] ?? null) !== ']' && $i < count($tokens)) {
      $out[] = cms_graphql_parse_value($tokens, $i, $variables);
      if (($tokens[$i] ?? null) === ',') $i++;
    }
    if (($tokens[$i] ?? null) === ']') $i++;
    return $out;
  }
  $i++; return $tok;
}

function cms_graphql_skip_definition(array $tokens, int &$i): void {
  if (($tokens[$i] ?? '') === 'query') {
    $i++;
    if (($tokens[$i] ?? '') !== '{' && ($tokens[$i] ?? '') !== '(') $i++;
    if (($tokens[$i] ?? '') === '(') {
      $depth = 0;
      do { $t = $tokens[$i++] ?? ''; if ($t === '(') $depth++; elseif ($t === ')') $depth--; } while ($i < count($tokens) && $depth > 0);
    }
  }
  while (($tokens[$i] ?? null) !== '{' && $i < count($tokens)) $i++;
}

function cms_graphql_parse_selection(array $tokens, int &$i, array $variables): array {
  if (($tokens[$i] ?? null) !== '{') return [];
  $i++; $fields = [];
  while ($i < count($tokens) && ($tokens[$i] ?? null) !== '}') {
    if (($tokens[$i] ?? null) === ',') { $i++; continue; }
    $first = $tokens[$i++] ?? '';
    if ($first === '' || $first === '...') { while ($i < count($tokens) && !in_array($tokens[$i], ['{','}'], true)) $i++; continue; }
    $alias = null; $name = $first;
    if (($tokens[$i] ?? null) === ':') { $i++; $alias = $first; $name = $tokens[$i++] ?? $first; }
    $args = [];
    if (($tokens[$i] ?? null) === '(') {
      $i++;
      while ($i < count($tokens) && ($tokens[$i] ?? null) !== ')') {
        $argName = $tokens[$i++] ?? '';
        if (($tokens[$i] ?? null) === ':') $i++;
        $args[$argName] = cms_graphql_parse_value($tokens, $i, $variables);
        if (($tokens[$i] ?? null) === ',') $i++;
      }
      if (($tokens[$i] ?? null) === ')') $i++;
    }
    $children = (($tokens[$i] ?? null) === '{') ? cms_graphql_parse_selection($tokens, $i, $variables) : [];
    $fields[] = ['name'=>$name,'alias'=>$alias ?: $name,'args'=>$args,'children'=>$children];
  }
  if (($tokens[$i] ?? null) === '}') $i++;
  return $fields;
}

function cms_graphql_project($value, array $children) {
  if (!$children) return $value;
  if (!is_array($value)) return null;
  if ($value === []) return [];
  $isList = array_keys($value) === range(0, count($value)-1);
  if ($isList) return array_map(fn($row) => cms_graphql_project($row, $children), $value);
  $out = [];
  foreach ($children as $field) {
    $name = $field['name'];
    $alias = $field['alias'];
    $childValue = is_array($value) && array_key_exists($name, $value) ? $value[$name] : null;
    $out[$alias] = cms_graphql_project($childValue, $field['children']);
  }
  return $out;
}

function cms_graphql_search(array $data, string $query): array {
  $q = strtolower(trim($query));
  if ($q === '') return [];
  $out = [];
  foreach (['services','products','blog','pages','gallery'] as $group) {
    foreach (($data[$group]['items'] ?? []) as $i => $item) {
      $text = strtolower((string)json_encode($item, JSON_UNESCAPED_UNICODE));
      if (str_contains($text, $q)) $out[] = ['group'=>$group,'index'=>$i,'item'=>$item];
    }
  }
  return $out;
}

function cms_graphql_root_value(array $data, string $name, array $args) {
  $public = cms_headless_public_data($data);
  if ($name === 'site') return $public;
  if ($name === 'settings') return $public['settings'] ?? [];
  if ($name === 'header') return $public['header'] ?? [];
  if ($name === 'footer') return $public['footer'] ?? [];
  if ($name === 'sections') return cms_headless_resource($data, 'sections');
  if ($name === 'products') return $public['products'] ?? [];
  if ($name === 'blog') return $public['blog'] ?? [];
  if ($name === 'pages') return $public['pages'] ?? [];
  if ($name === 'product') return cms_headless_resource($data, 'products', (string)($args['slug'] ?? ''));
  if ($name === 'post') return cms_headless_resource($data, 'blog', (string)($args['slug'] ?? ''));
  if ($name === 'page') return cms_headless_resource($data, 'pages', (string)($args['slug'] ?? ''));
  if ($name === 'search') return cms_graphql_search($public, (string)($args['query'] ?? ''));
  if ($name === 'path') return cms_data_path($public, (string)($args['value'] ?? ''));
  return null;
}

function cms_graphql_execute(array $data, string $query, array $variables = []): array {
  $tokens = cms_graphql_tokens($query);
  if (!$tokens) return ['data'=>null,'errors'=>[['message'=>'Empty GraphQL query.']]];
  $i = 0;
  cms_graphql_skip_definition($tokens, $i);
  $selection = cms_graphql_parse_selection($tokens, $i, $variables);
  if (!$selection) return ['data'=>null,'errors'=>[['message'=>'GraphQL selection set is required.']]];
  $out = [];
  foreach ($selection as $field) {
    $value = cms_graphql_root_value($data, $field['name'], $field['args']);
    $out[$field['alias']] = cms_graphql_project($value, $field['children']);
  }
  return ['data'=>$out];
}

function cms_function_enabled(string $name): bool {
  if (!function_exists($name)) return false;
  $disabled = array_filter(array_map('trim', explode(',', (string)ini_get('disable_functions'))));
  return !in_array($name, $disabled, true);
}

function cms_theme_command_path(string $name): string {
  foreach (['/usr/local/bin/' . $name, '/usr/bin/' . $name, '/bin/' . $name] as $path) if (is_file($path) && is_executable($path)) return $path;
  if (!cms_function_enabled('shell_exec')) return '';
  $out = trim((string)@shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null'));
  return ($out !== '' && is_file($out) && is_executable($out)) ? $out : '';
}

function cms_theme_download_support(): array {
  $methods = [];
  if (function_exists('curl_init')) $methods[] = 'PHP cURL';
  if ((bool)filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) $methods[] = 'PHP HTTPS streams';
  if (cms_function_enabled('proc_open') && cms_theme_command_path('curl') !== '') $methods[] = 'system curl';
  if (cms_function_enabled('proc_open') && cms_theme_command_path('wget') !== '') $methods[] = 'system wget';
  return ['supported'=>count($methods)>0,'methods'=>$methods,'preferred'=>$methods[0] ?? null];
}

function cms_theme_zip_signature_ok(string $file): bool {
  if (!is_file($file) || filesize($file) < 4) return false;
  $fh = @fopen($file, 'rb');
  if (!$fh) return false;
  $head = (string)fread($fh, 4); fclose($fh);
  return str_starts_with($head, "PK\x03\x04") || str_starts_with($head, "PK\x05\x06") || str_starts_with($head, "PK\x07\x08");
}

function cms_theme_download_candidates(string $url): array {
  foreach (cms_free_theme_catalog() as $item) {
    $downloads = array_values(array_unique(array_filter((array)($item['downloads'] ?? [$item['download'] ?? '']))));
    if (in_array($url, $downloads, true) || $url === ($item['download'] ?? '')) return $downloads;
  }
  throw new RuntimeException('Remote theme URL is not in the LPCMS allowlist.');
}

function cms_theme_proc_download(array $command, string $tmp, string $label, array &$errors): bool {
  if (!cms_function_enabled('proc_open')) { $errors[] = $label . ': proc_open disabled'; return false; }
  @file_put_contents($tmp, '');
  $pipes = [];
  $proc = @proc_open($command, [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
  if (!is_resource($proc)) { $errors[] = $label . ': could not start command'; return false; }
  if (isset($pipes[0])) fclose($pipes[0]);
  $stdout = isset($pipes[1]) ? stream_get_contents($pipes[1]) : ''; if (isset($pipes[1])) fclose($pipes[1]);
  $stderr = isset($pipes[2]) ? stream_get_contents($pipes[2]) : ''; if (isset($pipes[2])) fclose($pipes[2]);
  $code = proc_close($proc);
  if ($code === 0 && cms_theme_zip_signature_ok($tmp)) return true;
  $detail = trim((string)$stderr) ?: trim((string)$stdout);
  $errors[] = $label . ': exit ' . $code . ($detail !== '' ? ' - ' . substr(preg_replace('/\\s+/', ' ', $detail), 0, 240) : '');
  return false;
}

function cms_theme_download_remote(string $url): string {
  $candidates = cms_theme_download_candidates($url);
  $tmp = tempnam(sys_get_temp_dir(), 'lpcms-theme-');
  if ($tmp === false) throw new RuntimeException('Could not create a temporary theme file.');
  $errors = [];

  foreach ($candidates as $candidate) {
    if (function_exists('curl_init')) {
      @file_put_contents($tmp, '');
      $fh = @fopen($tmp, 'wb');
      if ($fh) {
        $ch = curl_init($candidate);
        $opts = [
          CURLOPT_FILE=>$fh,
          CURLOPT_FOLLOWLOCATION=>true,
          CURLOPT_MAXREDIRS=>5,
          CURLOPT_CONNECTTIMEOUT=>12,
          CURLOPT_TIMEOUT=>60,
          CURLOPT_USERAGENT=>'LPCMS/' . CMS_VERSION,
          CURLOPT_FAILONERROR=>false,
          CURLOPT_SSL_VERIFYPEER=>true,
          CURLOPT_SSL_VERIFYHOST=>2,
        ];
        if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) $opts[CURLOPT_PROTOCOLS] = CURLPROTO_HTTPS;
        curl_setopt_array($ch, $opts);
        $executed = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch); fclose($fh);
        if ($executed && $code >= 200 && $code < 400 && cms_theme_zip_signature_ok($tmp)) {
          if (filesize($tmp) > THEME_MAX_UPLOAD_BYTES) { @unlink($tmp); throw new RuntimeException('Theme archive exceeds the 40MB limit.'); }
          return $tmp;
        }
        $errors[] = 'PHP cURL ' . parse_url($candidate, PHP_URL_HOST) . ': HTTP ' . ($code ?: 'n/a') . ($err ? ' - ' . $err : '') . (is_file($tmp) && filesize($tmp) > 0 && !cms_theme_zip_signature_ok($tmp) ? ' - response was not a ZIP' : '');
      } else $errors[] = 'PHP cURL: temporary file is not writable';
    }

    if ((bool)filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
      @file_put_contents($tmp, '');
      $ssl = ['verify_peer'=>true,'verify_peer_name'=>true];
      if (is_file('/etc/ssl/certs/ca-certificates.crt')) $ssl['cafile'] = '/etc/ssl/certs/ca-certificates.crt';
      $ctx = stream_context_create([
        'http'=>['timeout'=>60,'follow_location'=>1,'max_redirects'=>5,'user_agent'=>'LPCMS/' . CMS_VERSION,'ignore_errors'=>true],
        'ssl'=>$ssl,
      ]);
      $in = @fopen($candidate, 'rb', false, $ctx);
      if ($in) {
        $out = @fopen($tmp, 'wb');
        $copied = $out ? stream_copy_to_stream($in, $out, THEME_MAX_UPLOAD_BYTES + 1) : false;
        fclose($in); if ($out) fclose($out);
        if ($copied !== false && $copied > 0 && $copied <= THEME_MAX_UPLOAD_BYTES && cms_theme_zip_signature_ok($tmp)) return $tmp;
        $errors[] = 'PHP HTTPS streams ' . parse_url($candidate, PHP_URL_HOST) . ': ' . ($copied === false ? 'copy failed' : ($copied > THEME_MAX_UPLOAD_BYTES ? 'archive exceeded 40MB' : 'response was not a ZIP'));
      } else {
        $last = error_get_last();
        $errors[] = 'PHP HTTPS streams ' . parse_url($candidate, PHP_URL_HOST) . ': ' . substr((string)($last['message'] ?? 'connection failed'), 0, 240);
      }
    }

    $curlBin = cms_theme_command_path('curl');
    if ($curlBin !== '') {
      if (cms_theme_proc_download([$curlBin,'--fail','--location','--silent','--show-error','--retry','2','--connect-timeout','12','--max-time','60','--user-agent','LPCMS/' . CMS_VERSION,'--output',$tmp,$candidate], $tmp, 'system curl', $errors)) {
        if (filesize($tmp) > THEME_MAX_UPLOAD_BYTES) { @unlink($tmp); throw new RuntimeException('Theme archive exceeds the 40MB limit.'); }
        return $tmp;
      }
    }

    $wgetBin = cms_theme_command_path('wget');
    if ($wgetBin !== '') {
      if (cms_theme_proc_download([$wgetBin,'--quiet','--tries=2','--timeout=60','--max-redirect=5','--user-agent=LPCMS/' . CMS_VERSION,'-O',$tmp,$candidate], $tmp, 'system wget', $errors)) {
        if (filesize($tmp) > THEME_MAX_UPLOAD_BYTES) { @unlink($tmp); throw new RuntimeException('Theme archive exceeds the 40MB limit.'); }
        return $tmp;
      }
    }
  }

  @unlink($tmp);
  $detail = implode(' | ', array_slice(array_unique($errors), 0, 8));
  throw new RuntimeException('Could not download the free theme over HTTPS. ' . ($detail !== '' ? $detail . '. ' : '') . 'If outbound HTTPS/DNS is blocked, use “Download ZIP manually” in the theme catalogue and then import it with “Import Bootstrap / ThemeForest ZIP”.');
}

function cms_theme_safe_rel(string $name): string {
  $name = str_replace('\\', '/', $name);
  $name = preg_replace('#/+#', '/', $name) ?? $name;
  $name = ltrim($name, '/');
  if ($name === '' || str_contains($name, "\0")) return '';
  foreach (explode('/', $name) as $part) if ($part === '..') return '';
  return $name;
}

function cms_theme_allowed_file(string $name): bool {
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  $allowed = ['html','htm','css','scss','js','mjs','json','txt','md','svg','png','jpg','jpeg','gif','webp','avif','ico','woff','woff2','ttf','eot','map','xml','webmanifest'];
  if (in_array($ext, $allowed, true)) return true;
  $base = strtoupper(basename($name));
  return in_array($base, ['LICENSE','LICENSE.MD','LICENSE.TXT','README','README.MD'], true);
}

function cms_theme_remove_dir(string $dir): void {
  if (!is_dir($dir)) return;
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
  foreach ($it as $item) { if ($item->isDir()) @rmdir($item->getPathname()); else @unlink($item->getPathname()); }
  @rmdir($dir);
}

function cms_theme_archive_support(): array {
  $engines = [];
  if (class_exists('ZipArchive')) $engines[] = 'ZipArchive';
  // PharData can read ZIP archives without php-zip on normal PHP builds. LPCMS
  // copies extensionless upload temp files to a temporary .zip path before opening.
  if (class_exists('PharData') && extension_loaded('zlib')) $engines[] = 'PharData';
  return ['supported'=>!empty($engines),'preferred'=>$engines[0] ?? null,'engines'=>$engines];
}

function cms_theme_store_stream(string $name, int $declaredSize, $in, string $target, array &$state): ?string {
  $name = cms_theme_safe_rel($name);
  if ($name === '' || str_ends_with($name, '/') || !cms_theme_allowed_file($name)) return null;
  if ($declaredSize > THEME_MAX_FILE_BYTES) throw new RuntimeException('Theme contains a file larger than the 8MB per-file limit: ' . basename($name));
  $state['count']++;
  if ($state['count'] > THEME_MAX_FILES) throw new RuntimeException('Theme exceeds the 3,000-file extraction limit.');

  // Keep imported HTML as non-executable source material. LPCMS only uses it to
  // discover Bootstrap/assets and never serves it as a live same-origin page.
  $storedName = $name;
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  if (in_array($ext, ['html','htm'], true)) $storedName .= '.lpcms-source.txt';
  $dest = $target . '/' . $storedName;
  $dir = dirname($dest); if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) throw new RuntimeException('Could not create a theme asset directory.');
  $out = @fopen($dest, 'wb');
  if (!$out) throw new RuntimeException('Could not write imported theme asset: ' . basename($storedName));
  $copied = stream_copy_to_stream($in, $out, THEME_MAX_FILE_BYTES + 1);
  fclose($out);
  if ($copied === false) throw new RuntimeException('Could not extract imported theme asset: ' . basename($storedName));
  if ($copied > THEME_MAX_FILE_BYTES) throw new RuntimeException('Theme contains a file larger than the 8MB per-file limit: ' . basename($name));
  $state['bytes'] += (int)$copied;
  if ($state['bytes'] > THEME_MAX_UPLOAD_BYTES) throw new RuntimeException('Theme exceeds the 40MB extraction limit.');
  @chmod($dest, 0644);
  $state['files'][] = $storedName;
  return $storedName;
}

function cms_theme_extract_zip_ziparchive(string $zipFile, string $target): array {
  $zip = new ZipArchive();
  if ($zip->open($zipFile) !== true) throw new RuntimeException('The uploaded file is not a readable ZIP archive.');
  cms_theme_remove_dir($target); @mkdir($target, 0755, true);
  $state = ['count'=>0,'bytes'=>0,'files'=>[]];
  try {
    for ($i=0; $i<$zip->numFiles; $i++) {
      $stat = $zip->statIndex($i); if (!$stat) continue;
      $rawName = (string)($stat['name'] ?? '');
      $safeName = cms_theme_safe_rel($rawName);
      if ($safeName === '' || str_ends_with($safeName, '/') || !cms_theme_allowed_file($safeName)) continue;
      $in = $zip->getStream($rawName); if (!$in) continue;
      try { cms_theme_store_stream($safeName, (int)($stat['size'] ?? 0), $in, $target, $state); }
      finally { fclose($in); }
    }
  } catch (Throwable $e) { $zip->close(); cms_theme_remove_dir($target); throw $e; }
  $zip->close();
  if (!$state['files']) { cms_theme_remove_dir($target); throw new RuntimeException('No safe HTML/CSS/theme assets were found in the ZIP.'); }
  return $state + ['engine'=>'ZipArchive'];
}

function cms_theme_extract_zip_phar(string $zipFile, string $target): array {
  $tmpDir = rtrim(sys_get_temp_dir(), '/\\') . '/lpcms-phar-' . bin2hex(random_bytes(8));
  if (!@mkdir($tmpDir, 0700, true) && !is_dir($tmpDir)) throw new RuntimeException('Could not create a temporary archive directory.');
  $pharZip = $tmpDir . '/theme.zip';
  if (!@copy($zipFile, $pharZip)) { @rmdir($tmpDir); throw new RuntimeException('Could not prepare the ZIP archive for the PharData fallback.'); }
  cms_theme_remove_dir($target); @mkdir($target, 0755, true);
  $state = ['count'=>0,'bytes'=>0,'files'=>[]];
  try {
    $archive = new PharData($pharZip);
    $prefix = 'phar://' . str_replace('\\','/',$pharZip) . '/';
    $iterator = new RecursiveIteratorIterator($archive, RecursiveIteratorIterator::LEAVES_ONLY);
    foreach ($iterator as $key => $file) {
      if (!$file instanceof SplFileInfo || !$file->isFile()) continue;
      $uri = str_replace('\\','/', (string)$key);
      if (!str_starts_with($uri, $prefix)) continue;
      $rawName = substr($uri, strlen($prefix));
      $safeName = cms_theme_safe_rel($rawName);
      if ($safeName === '' || !cms_theme_allowed_file($safeName)) continue;
      $in = @fopen($uri, 'rb'); if (!$in) continue;
      try { cms_theme_store_stream($safeName, (int)$file->getSize(), $in, $target, $state); }
      finally { fclose($in); }
    }
  } catch (Throwable $e) {
    cms_theme_remove_dir($target); @unlink($pharZip); @rmdir($tmpDir);
    throw new RuntimeException('PharData could not read this ZIP archive: ' . $e->getMessage(), 0, $e);
  }
  @unlink($pharZip); @rmdir($tmpDir);
  if (!$state['files']) { cms_theme_remove_dir($target); throw new RuntimeException('No safe HTML/CSS/theme assets were found in the ZIP.'); }
  return $state + ['engine'=>'PharData'];
}

function cms_theme_extract_zip(string $zipFile, string $target): array {
  $support = cms_theme_archive_support();
  if (!$support['supported']) throw new RuntimeException('No ZIP extraction engine is available. Enable PHP ZipArchive or the standard Phar/PharData extension.');
  if (class_exists('ZipArchive')) return cms_theme_extract_zip_ziparchive($zipFile, $target);
  if (class_exists('PharData') && extension_loaded('zlib')) return cms_theme_extract_zip_phar($zipFile, $target);
  throw new RuntimeException('No supported ZIP extraction engine is available.');
}

function cms_theme_find_index(string $target): ?string {
  $candidates = [];
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS));
  foreach ($it as $file) if ($file->isFile() && in_array(strtolower($file->getFilename()), ['index.html.lpcms-source.txt','index.htm.lpcms-source.txt'], true)) {
    $rel = ltrim(str_replace('\\','/',substr($file->getPathname(), strlen($target))), '/');
    $score = str_contains($rel, '/dist/index.html.lpcms-source.txt') || str_starts_with($rel, 'dist/index.html.lpcms-source.txt') ? 0 : (substr_count($rel, '/') + 1);
    $candidates[] = [$score, strlen($rel), $rel];
  }
  if (!$candidates) return null;
  usort($candidates, fn($a,$b)=>$a[0]<=>$b[0] ?: $a[1]<=>$b[1]);
  return $candidates[0][2];
}

function cms_theme_resolve_asset(string $target, string $indexRel, string $asset): ?string {
  $asset = html_entity_decode(trim($asset), ENT_QUOTES | ENT_HTML5, 'UTF-8');
  $asset = preg_replace('/[?#].*$/', '', $asset) ?? $asset;
  if ($asset === '' || preg_match('#^(?:https?:)?//#i', $asset) || str_starts_with($asset, 'data:') || str_starts_with($asset, '#')) return null;
  $base = dirname($target . '/' . $indexRel);
  $candidate = $asset[0] === '/' ? $target . '/' . ltrim($asset, '/') : $base . '/' . $asset;
  $real = realpath($candidate); $root = realpath($target);
  if (!$real || !$root || !str_starts_with(str_replace('\\','/',$real), rtrim(str_replace('\\','/',$root), '/') . '/')) return null;
  return ltrim(str_replace('\\','/',substr($real, strlen($root))), '/');
}

function cms_theme_analyse(string $target, string $indexRel): array {
  $html = @file_get_contents($target . '/' . $indexRel) ?: '';
  $version = 'unknown';
  if (preg_match('/bootstrap(?:@|[-_.])?5(?:\.|\b)/i', $html) || str_contains($html, 'data-bs-toggle')) $version = '5';
  elseif (preg_match('/bootstrap(?:@|[-_.])?4(?:\.|\b)/i', $html) || str_contains($html, 'data-toggle=')) $version = '4';
  elseif (preg_match('/bootstrap(?:@|[-_.])?3(?:\.|\b)/i', $html)) $version = '3';
  elseif (stripos($html, 'bootstrap') !== false) $version = 'present/unknown';
  $css = []; $js = [];
  if (preg_match_all('/<link\b[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) foreach ($m[1] as $asset) { $r = cms_theme_resolve_asset($target,$indexRel,$asset); if ($r && strtolower(pathinfo($r,PATHINFO_EXTENSION))==='css') $css[]=$r; }
  if (preg_match_all('/<script\b[^>]*src=["\']([^"\']+)["\'][^>]*>/i', $html, $m)) foreach ($m[1] as $asset) { $r = cms_theme_resolve_asset($target,$indexRel,$asset); if ($r && in_array(strtolower(pathinfo($r,PATHINFO_EXTENSION)),['js','mjs'],true)) $js[]=$r; }
  $css = array_values(array_unique($css)); $js = array_values(array_unique($js));
  if ($version === 'unknown' || $version === 'present/unknown') {
    foreach ($css as $rel) {
      if (stripos(basename($rel), 'bootstrap') === false) continue;
      $probe = @file_get_contents($target . '/' . $rel);
      if (!is_string($probe)) continue;
      $probe = substr($probe, 0, 12000);
      if (preg_match('/Bootstrap\s+v?([345])(?:\.|\b)/i', $probe, $vm)) { $version = $vm[1]; break; }
    }
  }
  $warnings = [];
  $score = 100;
  if ($version === '4') { $score -= 15; $warnings[] = 'Bootstrap 4 detected. LPCMS uses Bootstrap 5; common visual CSS is imported, but Bootstrap 4 JavaScript/data attributes are not auto-loaded.'; }
  elseif ($version === '3') { $score -= 35; $warnings[] = 'Bootstrap 3 detected. Expect manual CSS adjustments for grid/components.'; }
  elseif ($version === 'unknown') { $score -= 20; $warnings[] = 'Bootstrap version could not be detected.'; }
  if (!$css) { $score -= 30; $warnings[] = 'No local CSS files were discovered from index.html.'; }
  if ($js) $warnings[] = 'Theme JavaScript was found but is disabled by default for safety. Enable imported JS only after reviewing it.';
  // Prefer the theme's own stylesheet colors over Bootstrap's vendor defaults.
  $paletteCss = array_values(array_filter($css, fn($rel)=>stripos(basename((string)$rel), 'bootstrap') === false));
  if (!$paletteCss) $paletteCss = $css;
  $allCss = '';
  foreach (array_slice($paletteCss,0,8) as $rel) { $chunk=@file_get_contents($target.'/'.$rel); if (is_string($chunk)) $allCss .= substr($chunk,0,500000); }
  preg_match_all('/#[0-9a-fA-F]{6}\b/', $allCss, $cm); $freq=[];
  foreach ($cm[0] ?? [] as $c) {
    $c=strtolower($c);
    if (in_array($c,['#ffffff','#000000','#f8f9fa','#212529'],true)) continue;
    $r=hexdec(substr($c,1,2)); $g=hexdec(substr($c,3,2)); $b=hexdec(substr($c,5,2));
    if (($r>238 && $g>238 && $b>238) || ($r<20 && $g<20 && $b<20)) continue;
    $freq[$c]=($freq[$c]??0)+1;
  }
  arsort($freq); $colors=array_slice(array_keys($freq),0,4);
  $palette = ['brand'=>$colors[0]??'#0d6efd','brand2'=>$colors[1]??($colors[0]??'#6610f2'),'accent'=>$colors[2]??'#20c997'];
  return ['bootstrap'=>$version,'css'=>$css,'js'=>$js,'score'=>max(0,$score),'warnings'=>$warnings,'palette'=>$palette];
}

function cms_theme_adapter_css(array $analysis, string $themeId): string {
  $p=$analysis['palette'] ?? [];
  $brand=$p['brand']??'#0d6efd'; $brand2=$p['brand2']??$brand; $accent=$p['accent']??'#20c997';
  return "/* LPCMS generated adapter for {$themeId}. Original theme assets remain unchanged. */\n"
    . ":root{--cms-brand:{$brand};--cms-brand2:{$brand2};--cms-accent:{$accent};}\n"
    . ".lpcms-imported-theme .btn-cms{background:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2));}\n"
    . ".lpcms-imported-theme .text-primary{color:var(--cms-brand)!important}.lpcms-imported-theme .bg-primary{background-color:var(--cms-brand)!important}.lpcms-imported-theme .border-primary{border-color:var(--cms-brand)!important}\n";
}

function cms_theme_install_archive(string $zipFile, string $title, string $sourceType, string $sourceLabel = '', string $license = ''): array {
  $baseId = cms_slug($title ?: 'imported-theme');
  $id = 'theme-' . $baseId . '-' . substr(bin2hex(random_bytes(4)),0,6);
  $target = THEMES_DIR . '/' . $id;
  $extract = cms_theme_extract_zip($zipFile, $target);
  $indexRel = cms_theme_find_index($target);
  if (!$indexRel) { cms_theme_remove_dir($target); throw new RuntimeException('No index.html was found in the theme archive.'); }
  $analysis = cms_theme_analyse($target, $indexRel);
  $adapter = 'lpcms-adapter.css';
  file_put_contents($target . '/' . $adapter, cms_theme_adapter_css($analysis, $id), LOCK_EX);
  $manifest = [
    'id'=>$id,'title'=>$title ?: ucfirst(str_replace('-',' ',$baseId)),'sourceType'=>$sourceType,'sourceLabel'=>$sourceLabel,'license'=>$license,
    'installedAt'=>date('c'),'index'=>$indexRel,'bootstrap'=>$analysis['bootstrap'],'compatibilityScore'=>$analysis['score'],'warnings'=>$analysis['warnings'],
    'css'=>array_values(array_unique(array_merge($analysis['css'],[$adapter]))),'js'=>$analysis['js'],'palette'=>$analysis['palette'],'files'=>$extract['count'],'bytes'=>$extract['bytes'],'archiveEngine'=>$extract['engine']??'unknown'
  ];
  file_put_contents($target . '/lpcms-theme.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), LOCK_EX);
  return $manifest;
}

function cms_theme_manager_settings(array $data): array {
  return array_replace(['active'=>'lpcms-default','loadImportedCss'=>true,'loadImportedJs'=>false,'installed'=>[]], $data['settings']['themeManager'] ?? []);
}

function cms_theme_register(array $data, array $manifest, bool $activate = true): array {
  $mgr = cms_theme_manager_settings($data);
  $installed = is_array($mgr['installed'] ?? null) ? $mgr['installed'] : [];
  $installed = array_values(array_filter($installed, fn($x)=>is_array($x) && ($x['id']??'') !== ($manifest['id']??'')));
  $installed[] = $manifest;
  $data['settings']['themeManager'] = array_replace($mgr, ['installed'=>$installed]);
  if ($activate) {
    $data['settings']['themeManager']['active'] = $manifest['id'];
    foreach (($manifest['palette'] ?? []) as $k=>$v) if (isset($data['settings']['theme'][$k])) $data['settings']['theme'][$k]=$v;
  }
  return $data;
}

function cms_theme_activate(array $data, string $id): array {
  foreach (cms_theme_presets() as $preset) if (($preset['id']??'') === $id) {
    $data['settings']['themeManager']['active'] = $id;
    foreach (($preset['palette'] ?? []) as $k=>$v) $data['settings']['theme'][$k]=$v;
    return $data;
  }
  foreach ((cms_theme_manager_settings($data)['installed'] ?? []) as $theme) if (($theme['id']??'') === $id) {
    if (!is_dir(THEMES_DIR . '/' . $id)) throw new RuntimeException('Theme files are missing from assets/themes.');
    $data['settings']['themeManager']['active'] = $id;
    foreach (($theme['palette'] ?? []) as $k=>$v) if (isset($data['settings']['theme'][$k])) $data['settings']['theme'][$k]=$v;
    return $data;
  }
  throw new RuntimeException('Theme not found.');
}

function cms_active_theme_assets(array $data): array {
  $mgr = cms_theme_manager_settings($data); $active=(string)($mgr['active']??'lpcms-default');
  foreach (($mgr['installed'] ?? []) as $theme) if (($theme['id']??'') === $active) {
    $prefix = 'assets/themes/' . rawurlencode($active) . '/';
    $css = [];$js=[];
    if (!empty($mgr['loadImportedCss'])) foreach (($theme['css']??[]) as $rel) if (cms_theme_safe_rel((string)$rel)!=='') $css[]=$prefix.str_replace('%2F','/',rawurlencode(cms_theme_safe_rel((string)$rel)));
    if (!empty($mgr['loadImportedJs'])) foreach (($theme['js']??[]) as $rel) if (cms_theme_safe_rel((string)$rel)!=='') $js[]=$prefix.str_replace('%2F','/',rawurlencode(cms_theme_safe_rel((string)$rel)));
    return ['id'=>$active,'imported'=>true,'css'=>$css,'js'=>$js,'meta'=>$theme];
  }
  return ['id'=>$active,'imported'=>false,'css'=>[],'js'=>[],'meta'=>null];
}

function cms_theme_delete(array $data, string $id): array {
  if (!str_starts_with($id, 'theme-')) throw new RuntimeException('Built-in themes cannot be deleted.');
  $mgr = cms_theme_manager_settings($data);
  $mgr['installed'] = array_values(array_filter($mgr['installed'] ?? [], fn($x)=>($x['id']??'')!==$id));
  if (($mgr['active']??'') === $id) $mgr['active']='lpcms-default';
  $data['settings']['themeManager']=$mgr;
  cms_theme_remove_dir(THEMES_DIR . '/' . $id);
  return $data;
}

function cms_slug(string $s): string {
  $s = strtolower(trim($s));
  $s = preg_replace('/[^a-z0-9]+/', '-', $s);
  return trim($s ?: 'item', '-');
}

function cms_e(?string $s): string {
  return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function cms_pretty_path(string $group, string $slug, array $data = [], ?string $lang = null): string {
  $slug = cms_slug($slug);
  $prefix = $data ? cms_language_prefix($data, $lang) : '';
  $base = $prefix;
  if ($group === 'blog') return $base . '/blog/' . $slug;
  if ($group === 'products') return $base . '/product/' . $slug;
  if ($group === 'pages') return $base . '/page/' . $slug;
  return $base . '/?slug=' . rawurlencode($slug);
}

function cms_request_route(array $data): array {
  $map = ['blog' => 'blog', 'posts' => 'blog', 'post' => 'blog', 'products' => 'products', 'product' => 'products', 'pages' => 'pages', 'page' => 'pages'];
  $route = strtolower(trim((string)($_GET['cms_route'] ?? ''), '/'));
  $slug = trim((string)($_GET['slug'] ?? ''), '/');
  $path = trim((string)(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? ''), '/');
  $base = trim(cms_route_prefix(), '/');
  if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) $path = trim(substr($path, strlen($base)), '/');
  $parts = $path === '' ? [] : array_values(array_filter(explode('/', $path), 'strlen'));
  $codes = cms_language_codes($data);
  $lang = cms_current_language($data);
  if (!empty($parts) && in_array(strtolower((string)$parts[0]), $codes, true)) array_shift($parts);
  if ($route !== '' && isset($map[$route])) $route = $map[$route];
  if ($slug === '' && count($parts) >= 2 && isset($map[strtolower($parts[0])])) {
    $route = $map[strtolower($parts[0])];
    $slug = $parts[1];
  } elseif ($slug === '' && count($parts) === 1 && !str_contains($parts[0], '.')) {
    $route = 'pages';
    $slug = $parts[0];
  }
  return ['group' => $route, 'slug' => $slug === '' ? '' : cms_slug($slug), 'path' => implode('/', $parts), 'lang' => $lang];
}

function cms_find_route_item(array $data): array {
  $r = cms_request_route($data);
  $groups = $r['group'] ? [$r['group']] : ['pages', 'blog', 'products'];
  $candidates = array_filter([$r['slug'], basename($r['path'] ?? '')]);
  foreach ($groups as $group) {
    foreach (($data[$group]['items'] ?? []) as $i => $item) {
      $itemSlug = cms_slug((string)($item['slug'] ?? ($item['title'] ?? 'item')));
      if (in_array($itemSlug, $candidates, true)) {
        return ['group' => $group, 'index' => $i, 'slug' => $itemSlug, 'item' => $item, 'lang'=>$r['lang'], 'path' => cms_pretty_path($group, $itemSlug, $data, $r['lang'])];
      }
    }
  }
  return ['group' => $r['group'], 'slug' => $r['slug'], 'item' => null, 'lang'=>$r['lang'], 'path' => $r['slug'] ? cms_pretty_path($r['group'] ?: 'pages', $r['slug'], $data, $r['lang']) : cms_language_home_path($data, $r['lang'])];
}

function cms_find_meta(array $data): array {
  $default = $data['settings']['seo'] ?? [];
  $route = cms_find_route_item($data);
  $lang = $route['lang'] ?? cms_current_language($data);
  if (!empty($route['item']) && is_array($route['item'])) {
    $item = $route['item'];
    $group = $route['group'];
    $seo = $item['seo'] ?? [];
    $type = $group === 'products' ? 'product' : ($group === 'blog' ? 'article' : 'website');
    $seoTitle = cms_localized_value($data, is_array($seo) ? $seo : [], 'title', $lang, '');
    $seoDescription = cms_localized_value($data, is_array($seo) ? $seo : [], 'description', $lang, '');
    $title = (string)($seoTitle !== '' ? $seoTitle : cms_localized_value($data, $item, 'title', $lang, ($default['title'] ?? '')));
    $description = (string)($seoDescription !== '' ? $seoDescription : (cms_localized_value($data, $item, 'excerpt', $lang, '') ?: cms_localized_value($data, $item, 'text', $lang, ($default['description'] ?? ''))));
    return array_replace($default, [
      'title' => $title,
      'description' => $description,
      'image' => $seo['image'] ?? $item['image'] ?? ($default['image'] ?? ''),
      'type' => $type,
      'item' => $item,
      'group' => $group,
      'slug' => $route['slug'],
      'lang' => $lang,
      'path' => $route['path'],
    ]);
  }
  $out = array_replace(['type' => 'website'], $default);
  foreach (['title','description','keywords'] as $k) {
    if (isset($default['i18n'][$lang]) && is_array($default['i18n'][$lang]) && array_key_exists($k, $default['i18n'][$lang])) $out[$k] = $default['i18n'][$lang][$k];
    elseif (!empty($out[$k])) $out[$k] = cms_i18n_text($data, (string)$out[$k], $lang);
  }
  $out['lang'] = $lang;
  $out['path'] = cms_language_home_path($data, $lang);
  return $out;
}

function cms_apply_redirects(array $data): void {
  if (isset($_GET['api']) || isset($_GET['login'])) return;
  $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
  foreach (($data['redirects'] ?? []) as $r) {
    if (empty($r['enabled'])) continue;
    $from = $r['from'] ?? '';
    $to = $r['to'] ?? '';
    if ($from && $to && rtrim($from, '/') === rtrim($path, '/')) {
      $status = in_array((int)($r['status'] ?? 301), [301,302,307,308], true) ? (int)$r['status'] : 301;
      header('Location: ' . $to, true, $status);
      exit;
    }
  }
}

function cms_verify_hcaptcha(array $data, string $token): bool {
  $enabled = !empty($data['settings']['hcaptcha']['enabled']);
  $secret = trim((string)(getenv('HCAPTCHA_SECRET') ?: ($data['settings']['hcaptcha']['secret'] ?? '')));
  if ($secret === '') {
    $secretFile = __DIR__ . '/.secrets.php';
    if (is_file($secretFile)) {
      $private = include $secretFile;
      if (is_array($private)) $secret = trim((string)($private['hcaptchaSecret'] ?? ''));
    }
  }
  if (!$enabled) return true;
  if ($secret === '' || $token === '') return false;
  $post = http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '']);
  $opts = ['http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 'content' => $post, 'timeout' => 8]];
  $res = @file_get_contents('https://hcaptcha.com/siteverify', false, stream_context_create($opts));
  $json = json_decode($res ?: '', true);
  return !empty($json['success']);
}

function cms_app_settings(array $data): array {
  return array_replace([
    'enabled' => true,
    'name' => 'LPCMS',
    'shortName' => 'LPCMS',
    'description' => 'Install this website as a mobile app.',
    'themeColor' => '#0d6efd',
    'backgroundColor' => '#ffffff',
    'icon' => '/assets/img/app-icon-512.png',
    'icon192' => '/assets/img/app-icon-192.png',
    'icon512' => '/assets/img/app-icon-512.png',
    'appleIcon' => '/assets/img/apple-touch-icon.png',
    'androidInstallUrl' => '/?install=android',
    'appleInstallUrl' => '/?install=apple',
    'footerBadgesEnabled' => true,
    'offlineMessage' => 'You are offline. Please reconnect to continue editing.'
  ], $data['settings']['app'] ?? []);
}

function cms_render_manifest(array $data): void {
  $app = cms_app_settings($data);
  $base = cms_base_url($data);
  $icon192 = trim((string)($app['icon192'] ?? $app['icon'] ?? '')) ?: '/assets/img/app-icon-192.png';
  $icon512 = trim((string)($app['icon512'] ?? $app['icon'] ?? '')) ?: '/assets/img/app-icon-512.png';
  if (!preg_match('/^https?:\/\//', $icon192)) $icon192 = rtrim($base, '/') . '/' . ltrim($icon192, '/');
  if (!preg_match('/^https?:\/\//', $icon512)) $icon512 = rtrim($base, '/') . '/' . ltrim($icon512, '/');
  $manifest = [
    'name' => (string)$app['name'],
    'short_name' => (string)$app['shortName'],
    'description' => (string)$app['description'],
    'start_url' => cms_route_prefix(),
    'scope' => cms_route_prefix(),
    'display' => 'standalone',
    'background_color' => (string)$app['backgroundColor'],
    'theme_color' => (string)$app['themeColor'],
    'icons' => [
      ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
      ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable']
    ]
  ];
  header('Content-Type: application/manifest+json; charset=utf-8');
  header('Cache-Control: no-cache, must-revalidate');
  echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
  exit;
}

function cms_render_service_worker(array $data): void {
  $app = cms_app_settings($data);
  $version = 'lpcms-' . CMS_VERSION . '-' . (string)@filemtime(__FILE__) . '-' . (file_exists(DATA_FILE) ? (string)filemtime(DATA_FILE) : time());
  $offline = json_encode((string)($app['offlineMessage'] ?? 'Offline'), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  header('Content-Type: application/javascript; charset=utf-8');
  header('Service-Worker-Allowed: ' . cms_route_prefix());
  header('Cache-Control: no-cache, must-revalidate');
  echo "const LPCMS_CACHE = " . json_encode($version) . ";
";
  echo "self.addEventListener('install', e => { self.skipWaiting(); e.waitUntil(caches.open(LPCMS_CACHE).then(c => c.addAll(['./']))); });
";
  echo "self.addEventListener('activate', e => { e.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(k => k.startsWith('lpcms-') && k !== LPCMS_CACHE).map(k => caches.delete(k))))); self.clients.claim(); });
";
  echo "self.addEventListener('fetch', e => { const req = e.request; if (req.method !== 'GET') return; const url = new URL(req.url); if (url.protocol !== 'http:' && url.protocol !== 'https:') return; if (url.searchParams.has('api') || url.searchParams.has('admin') || url.searchParams.has('login')) return; e.respondWith(fetch(req).then(res => { const copy = res.clone(); if (res.ok && (req.destination === 'document' || req.destination === 'image' || req.destination === 'style' || req.destination === 'script')) caches.open(LPCMS_CACHE).then(c => c.put(req, copy)).catch(() => {}); return res; }).catch(() => caches.match(req).then(cached => cached || new Response('<!doctype html><title>Offline</title><meta name=viewport content=width=device-width,initial-scale=1><body style=font-family:system-ui;padding:2rem><h1>Offline</h1><p>'+" . $offline . "+'</p></body>', {headers:{'Content-Type':'text/html; charset=utf-8'}})))); });
";
  exit;
}

function cms_render_install_page(array $data, string $platform): void {
  $app = cms_app_settings($data);
  $isApple = $platform === 'apple';
  $title = ($isApple ? 'Install on iPhone / iPad' : 'Install on Android') . ' - ' . (string)$app['name'];
  $manifest = rtrim(cms_route_prefix(), '/') . '/manifest.webmanifest';
  $sw = rtrim(cms_route_prefix(), '/') . '/service-worker.js';
  header('Content-Type: text/html; charset=utf-8');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
  echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="theme-color" content="' . cms_e((string)$app['themeColor']) . '"><link rel="manifest" href="' . cms_e($manifest) . '"><title>' . cms_e($title) . '</title><style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:linear-gradient(135deg,#eef5ff,#f7f3ff);font:16px/1.6 system-ui,-apple-system,Segoe UI,sans-serif;color:#172033;padding:24px}.card{width:min(100%,720px);background:#fff;border-radius:28px;padding:32px;box-shadow:0 30px 90px rgba(15,23,42,.16);border:1px solid rgba(15,23,42,.08)}.badge{display:inline-flex;border-radius:999px;padding:.35rem .7rem;background:#eef4ff;color:#0d6efd;font-weight:700}h1{font-size:clamp(2rem,5vw,3.4rem);line-height:1.02;margin:.8rem 0 1rem;letter-spacing:-.045em}li{margin:.6rem 0}.actions{display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1.4rem}button,a{border:0;border-radius:999px;padding:.85rem 1.15rem;text-decoration:none;font-weight:750;cursor:pointer}.primary{background:linear-gradient(135deg,#0d6efd,#6610f2);color:#fff}.ghost{background:#fff;color:#243449;border:1px solid rgba(15,23,42,.12)}</style></head><body><main class="card"><span class="badge">LPCMS mobile app</span><h1>' . cms_e($title) . '</h1>';
  if ($isApple) {
    echo '<p>Open this website in Safari, tap <strong>Share</strong>, choose <strong>Add to Home Screen</strong>, then tap <strong>Add</strong>.</p><ol><li>Use Safari on iPhone or iPad.</li><li>Open the website URL.</li><li>Tap the Share icon.</li><li>Select Add to Home Screen.</li><li>Launch the app from the home screen.</li></ol><div class="actions">';
  } else {
    echo '<p>Open this website in Chrome/Edge on Android, then use <strong>Install app</strong> or <strong>Add to Home screen</strong>.</p><ol><li>Open the website URL on Android.</li><li>Tap the browser menu.</li><li>Choose Install app or Add to Home screen.</li><li>Launch the app from your home screen.</li></ol><div class="actions"><button class="primary" id="installBtn">Install app</button>';
  }
  echo '<a class="ghost" href="' . cms_e(cms_route_prefix()) . '">Back to website</a></div><script>let p=null;window.addEventListener("beforeinstallprompt",e=>{e.preventDefault();p=e;const b=document.getElementById("installBtn");if(b)b.style.display="inline-flex"});const b=document.getElementById("installBtn");if(b)b.onclick=async()=>{if(!p){alert("Use your browser menu and choose Install app / Add to Home screen.");return}p.prompt();await p.userChoice;p=null};if("serviceWorker"in navigator&&(location.protocol==="https:"||location.hostname==="localhost")){navigator.serviceWorker.register("' . cms_e($sw) . '").catch(()=>{})}</script></main></body></html>';
  exit;
}

function cms_cache_settings(array $data): array {
  return array_replace(['enabled' => true, 'htmlTtl' => 300, 'autoClearOnSave' => true, 'maxAgeDays' => 14], $data['settings']['cache'] ?? []);
}

function cms_cache_key(): string {
  $uri = (string)($_SERVER['HTTP_HOST'] ?? 'localhost') . '|' . (string)($_SERVER['REQUEST_URI'] ?? '/');
  return hash('sha256', CMS_VERSION . '|' . (file_exists(DATA_FILE) ? filemtime(DATA_FILE) : 0) . '|' . $uri);
}

function cms_cache_enabled_for_request(array $data): bool {
  $s = cms_cache_settings($data);
  if (empty($s['enabled'])) return false;
  if (cms_is_admin()) return false;
  if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return false;
  foreach (['api','login','admin','install','pwa'] as $key) if (array_key_exists($key, $_GET)) return false;
  return true;
}

function cms_try_serve_page_cache(array $data): void {
  if (!cms_cache_enabled_for_request($data)) return;
  $ttl = max(0, min(86400, (int)(cms_cache_settings($data)['htmlTtl'] ?? 300)));
  if ($ttl <= 0) return;
  $file = CACHE_DIR . '/page-' . cms_cache_key() . '.html';
  if (is_file($file) && filemtime($file) >= time() - $ttl) {
    header('Content-Type: text/html; charset=utf-8');
    header('X-LPCMS-Cache: HIT');
    readfile($file);
    exit;
  }
}

function cms_start_page_cache(array $data): void {
  if (!cms_cache_enabled_for_request($data)) return;
  $file = CACHE_DIR . '/page-' . cms_cache_key() . '.html';
  @ob_start(function ($html) use ($file) {
    if (http_response_code() < 400 && is_string($html) && strlen($html) > 200) {
      if (!is_dir(CACHE_DIR)) @mkdir(CACHE_DIR, 0755, true);
      @file_put_contents($file, $html, LOCK_EX);
    }
    return $html;
  });
  header('X-LPCMS-Cache: MISS');
}

function cms_dir_size(string $dir, int &$files = 0): int {
  $bytes = 0;
  if (!is_dir($dir)) return 0;
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
  foreach ($it as $file) if ($file->isFile()) { $files++; $bytes += $file->getSize(); }
  return $bytes;
}

function cms_cache_stats(): array {
  $files = 0;
  $bytes = cms_dir_size(CACHE_DIR, $files);
  return ['files' => $files, 'bytes' => $bytes, 'mb' => round($bytes / 1048576, 2), 'dir' => 'assets/cache'];
}

function cms_clear_cache(): int {
  $deleted = 0;
  if (!is_dir(CACHE_DIR)) return 0;
  foreach (glob(CACHE_DIR . '/*') ?: [] as $file) {
    if (is_file($file) && @unlink($file)) $deleted++;
  }
  return $deleted;
}

function cms_media_stats(array $data): array {
  $dirs = ['img' => IMG_DIR, 'gallery' => GALLERY_DIR, 'uploads' => UPLOAD_DIR, 'docs' => DOCS_DIR, 'cache' => CACHE_DIR];
  $out = [];
  foreach ($dirs as $key => $dir) {
    $files = 0; $bytes = cms_dir_size($dir, $files);
    $out[$key] = ['files' => $files, 'bytes' => $bytes, 'mb' => round($bytes / 1048576, 2)];
  }
  $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '';
  $orphanCache = 0;
  foreach (glob(CACHE_DIR . '/*') ?: [] as $file) if (is_file($file)) $orphanCache++;
  return ['directories' => $out, 'cache' => cms_cache_stats(), 'referencesChecked' => strlen($json), 'orphanCacheFiles' => $orphanCache];
}

$data = cms_load_data();
cms_sync_htaccess_files($data);
$requestPath = cms_request_relative_path();
if ((isset($_GET['pwa']) && $_GET['pwa'] === 'manifest') || $requestPath === 'manifest.webmanifest') cms_render_manifest($data);
if ((isset($_GET['pwa']) && $_GET['pwa'] === 'sw') || $requestPath === 'service-worker.js') cms_render_service_worker($data);
if (isset($_GET['install']) && in_array(strtolower((string)$_GET['install']), ['android','apple'], true)) cms_render_install_page($data, strtolower((string)$_GET['install']));
if (cms_is_breakglass_request()) cms_handle_breakglass($data);
cms_apply_redirects($data);

if (isset($_GET['login'])) {
  $email = cms_verify_token((string)$_GET['login']);
  if (!$email) {
    http_response_code(403);
    echo 'Invalid or expired login link.';
    exit;
  }
  session_regenerate_id(true);
  $_SESSION['cms_admin_email'] = $email;
  $loginUser = cms_user_from_email(cms_load_data(), $email);
  $_SESSION['cms_admin_role'] = $loginUser['role'] ?? 'editor';
  $_SESSION['cms_csrf'] = bin2hex(random_bytes(20));
  header('Location: ' . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
  exit;
}

if (isset($_GET['api'])) {
  $api = (string)$_GET['api'];
  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

  if ($api === 'headless' || $api === 'graphql' || $api === 'graphql-schema') {
    $headless = cms_headless_settings($data);
    if (empty($headless['enabled'])) cms_json(['ok'=>false,'error'=>'Headless API is disabled.'], 404);
    cms_apply_headless_cors($data);
    if ($method === 'OPTIONS') { http_response_code(204); exit; }
  }

  if ($api === 'headless') {
    if (empty(cms_headless_settings($data)['restEnabled'])) cms_json(['ok'=>false,'error'=>'REST headless API is disabled.'], 404);
    $resource = trim((string)($_GET['resource'] ?? 'site'));
    $slug = trim((string)($_GET['slug'] ?? ''));
    $value = cms_headless_resource(cms_load_data(), $resource, $slug);
    if ($value === null) cms_json(['ok'=>false,'error'=>'Resource not found.'], 404);
    cms_json(['ok'=>true,'resource'=>$resource,'slug'=>$slug?:null,'data'=>$value,'version'=>CMS_VERSION]);
  }

  if ($api === 'graphql-schema') {
    if (empty(cms_headless_settings($data)['graphqlEnabled'])) cms_json(['ok'=>false,'error'=>'GraphQL API is disabled.'], 404);
    header('Content-Type: text/plain; charset=utf-8'); echo cms_graphql_schema(); exit;
  }

  if ($api === 'graphql') {
    if (empty(cms_headless_settings($data)['graphqlEnabled'])) cms_json(['ok'=>false,'error'=>'GraphQL API is disabled.'], 404);
    $payload = $method === 'POST' ? (json_decode(file_get_contents('php://input') ?: '', true) ?: []) : $_GET;
    $query = (string)($payload['query'] ?? '');
    $variables = is_array($payload['variables'] ?? null) ? $payload['variables'] : [];
    $result = cms_graphql_execute(cms_load_data(), $query, $variables);
    cms_json($result, !empty($result['errors']) ? 400 : 200);
  }

  if ($api === 'theme-catalog') {
    cms_require_super_admin();
    $archiveSupport = cms_theme_archive_support();
    $downloadSupport = cms_theme_download_support();
    cms_json(['ok'=>true,'presets'=>cms_theme_presets(),'free'=>cms_free_theme_catalog(),'manager'=>cms_theme_manager_settings(cms_load_data()),'zipSupported'=>$archiveSupport['supported'],'archiveSupport'=>$archiveSupport,'downloadSupport'=>$downloadSupport]);
  }

  if ($api === 'theme-activate' && $method === 'POST') {
    cms_require_super_admin();
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    try { $fresh = cms_theme_activate(cms_load_data(), (string)($payload['id'] ?? '')); }
    catch (RuntimeException $e) { cms_json(['ok'=>false,'error'=>$e->getMessage()], 422); }
    if (!cms_save_data($fresh)) cms_json(['ok'=>false,'error'=>'Could not save active theme.'],500);
    cms_clear_cache();
    cms_json(['ok'=>true,'message'=>'Theme activated.','data'=>cms_public_data(cms_load_data()),'manager'=>cms_theme_manager_settings(cms_load_data())]);
  }

  if ($api === 'theme-delete' && $method === 'POST') {
    cms_require_super_admin();
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    try { $fresh = cms_theme_delete(cms_load_data(), (string)($payload['id'] ?? '')); }
    catch (RuntimeException $e) { cms_json(['ok'=>false,'error'=>$e->getMessage()], 422); }
    if (!cms_save_data($fresh)) cms_json(['ok'=>false,'error'=>'Could not update theme registry.'],500);
    cms_clear_cache();
    cms_json(['ok'=>true,'message'=>'Imported theme deleted.','manager'=>cms_theme_manager_settings(cms_load_data())]);
  }

  if ($api === 'theme-import' && $method === 'POST') {
    cms_require_super_admin();
    $archiveSupport = cms_theme_archive_support();
    if (!$archiveSupport['supported']) cms_json(['ok'=>false,'error'=>'No ZIP extraction engine is available. Enable PHP ZipArchive or Phar/PharData.'], 501);
    $sourceType = (string)($_POST['sourceType'] ?? 'upload'); $zipFile=''; $cleanup=false; $title=''; $label=''; $license='';
    try {
      if ($sourceType === 'free') {
        $themeId = (string)($_POST['themeId'] ?? ''); $found=null;
        foreach (cms_free_theme_catalog() as $item) if (($item['id']??'')===$themeId) { $found=$item; break; }
        if (!$found) throw new RuntimeException('Free theme was not found in the allowlisted catalogue.');
        $zipFile = cms_theme_download_remote($found['download']); $cleanup=true; $title=$found['title']; $label=$found['source']; $license=$found['license'];
      } else {
        if (empty($_FILES['themeZip']['tmp_name']) || !is_uploaded_file($_FILES['themeZip']['tmp_name'])) throw new RuntimeException('Choose a Bootstrap HTML theme ZIP file.');
        if ((int)($_FILES['themeZip']['size'] ?? 0) <= 0 || (int)($_FILES['themeZip']['size'] ?? 0) > THEME_MAX_UPLOAD_BYTES) throw new RuntimeException('Theme ZIP must be between 1 byte and 40MB.');
        $zipFile = $_FILES['themeZip']['tmp_name']; $title=trim((string)($_POST['title'] ?? pathinfo((string)($_FILES['themeZip']['name'] ?? 'Imported theme'), PATHINFO_FILENAME))); $label=(string)($_FILES['themeZip']['name'] ?? 'Uploaded ZIP'); $license=trim((string)($_POST['license'] ?? 'User-provided / verify licence'));
      }
      $manifest = cms_theme_install_archive($zipFile,$title,$sourceType,$label,$license);
      $fresh = cms_theme_register(cms_load_data(),$manifest,true);
      if (!cms_save_data($fresh)) throw new RuntimeException('Theme files imported, but data.json could not be updated.');
      cms_clear_cache();
      if ($cleanup) @unlink($zipFile);
      cms_json(['ok'=>true,'message'=>'Theme imported, analysed, adapted, registered and activated.','theme'=>$manifest,'data'=>cms_public_data(cms_load_data())]);
    } catch (Throwable $e) {
      if ($cleanup && $zipFile) @unlink($zipFile);
      cms_json(['ok'=>false,'error'=>$e->getMessage()],422);
    }
  }

  if ($api === 'site') {
    cms_json([
      'ok' => true,
      'admin' => cms_is_admin(),
      'email' => $_SESSION['cms_admin_email'] ?? '',
      'role' => cms_admin_role(),
      'isSuper' => cms_is_super_admin(),
      'csrf' => $_SESSION['cms_csrf'] ?? '',
      'route' => cms_find_route_item(cms_load_data()),
      'lang' => cms_current_language(cms_load_data()),
      'server' => cms_htaccess_status(cms_load_data()),
      'data' => cms_public_data(cms_load_data())
    ]);
  }

  if ($api === 'login' && $method === 'POST') {
    if (!cms_rate_limit_consume('magic-link-login', 6, 900)) {
      cms_json(['ok' => false, 'error' => 'Too many login emails requested. Try again later.'], 429);
    }
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    if (!cms_verify_hcaptcha($data, (string)($payload['hcaptcha'] ?? ''))) cms_json(['ok' => false, 'error' => 'Captcha verification failed.'], 422);
    $email = strtolower(trim((string)($payload['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) cms_json(['ok' => false, 'error' => 'Please enter a valid email.'], 422);
    $user = cms_user_from_email($data, $email);
    if (!$user || ($user['role'] ?? 'editor') !== 'editor') {
      cms_json(['ok' => false, 'error' => $email === strtolower(BREAKGLASS_ADMIN_EMAIL) ? 'Super administrator must login with /?admin.' : 'Please ask administrator access for your email.'], 403);
    }
    $token = cms_make_token($email);
    $link = cms_base_url($data) . '/index.php?login=' . rawurlencode($token);
    $sent = cms_send_mail($email, 'Your secure CMS login link', "Open this secure login link within 15 minutes:\n\n" . $link . "\n\nIf you did not request this link, ignore this email.");
    cms_json(['ok' => true, 'sent' => $sent, 'message' => $sent ? 'Secure login link sent. Please check your email.' : 'Mail was not sent. Configure PHP mail or SMTP.', 'devLink' => DEV_SHOW_LOGIN_LINK ? $link : null]);
  }

  if ($api === 'logout' && $method === 'POST') {
    cms_require_admin();
    $_SESSION = [];
    session_destroy();
    cms_json(['ok' => true]);
  }

  if ($api === 'save' && $method === 'POST') {
    cms_require_admin();
    $payload = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($payload)) cms_json(['ok' => false, 'error' => 'Invalid JSON'], 422);
    if (strlen(json_encode($payload)) > 5 * 1024 * 1024) cms_json(['ok' => false, 'error' => 'JSON too large'], 413);
    $current = cms_load_data();
    if (!cms_is_super_admin()) {
      $payload = cms_filter_editor_payload($payload, $current);
    } else {
      $postedPassword = (string)($payload['settings']['mailer']['smtp']['password'] ?? '');
      if ($postedPassword === '' && !empty($current['settings']['mailer']['smtp']['password'])) $payload['settings']['mailer']['smtp']['password'] = $current['settings']['mailer']['smtp']['password'];
      $postedToken = (string)($payload['settings']['commerceIntegrations']['webhook']['bearerToken'] ?? '');
      if ($postedToken === '' && !empty($current['settings']['commerceIntegrations']['webhook']['bearerToken'])) $payload['settings']['commerceIntegrations']['webhook']['bearerToken'] = $current['settings']['commerceIntegrations']['webhook']['bearerToken'];
      $checkoutToken = (string)($payload['settings']['commerceIntegrations']['checkout']['bearerToken'] ?? '');
      if ($checkoutToken === '' && !empty($current['settings']['commerceIntegrations']['checkout']['bearerToken'])) $payload['settings']['commerceIntegrations']['checkout']['bearerToken'] = $current['settings']['commerceIntegrations']['checkout']['bearerToken'];
      $aiKey = (string)($payload['settings']['ai']['apiKey'] ?? '');
      if ($aiKey === '' && !empty($current['settings']['ai']['apiKey'])) $payload['settings']['ai']['apiKey'] = $current['settings']['ai']['apiKey'];
      foreach (['pexels','pixabay','unsplash'] as $provider) {
        $postedKey = (string)($payload['settings']['mediaSources'][$provider]['apiKey'] ?? '');
        if ($postedKey === '' && !empty($current['settings']['mediaSources'][$provider]['apiKey'])) $payload['settings']['mediaSources'][$provider]['apiKey'] = $current['settings']['mediaSources'][$provider]['apiKey'];
      }
      unset($payload['settings']['mailer']['smtp']['passwordSet'], $payload['settings']['commerceIntegrations']['webhook']['bearerTokenSet'], $payload['settings']['commerceIntegrations']['checkout']['bearerTokenSet'], $payload['settings']['ai']['apiKeySet']);
      foreach (['pexels','pixabay','unsplash'] as $provider) unset($payload['settings']['mediaSources'][$provider]['apiKeySet']);
      $cronKey = (string)($payload['settings']['abandonedCart']['cronKey'] ?? '');
      if ($cronKey === '' && !empty($current['settings']['abandonedCart']['cronKey'])) $payload['settings']['abandonedCart']['cronKey'] = $current['settings']['abandonedCart']['cronKey'];
      unset($payload['settings']['abandonedCart']['cronKeySet']);
    }
    $payload['settings']['abandonedCart']['carts'] = $current['settings']['abandonedCart']['carts'] ?? [];
    $payload['booking']['bookings'] = $current['booking']['bookings'] ?? [];
    if (!empty($current['settings']['forms']['submissions']) && empty($payload['settings']['forms']['submissions'])) {
      $payload['settings']['forms']['submissions'] = $current['settings']['forms']['submissions'];
    }
    if (!cms_save_data($payload)) cms_json(['ok' => false, 'error' => 'Could not save data.json'], 500);
    if (!empty(($payload['settings']['cache'] ?? [])['autoClearOnSave'])) cms_clear_cache();
    cms_sync_htaccess_files($payload);
    cms_json(['ok' => true, 'data' => cms_public_data(cms_load_data()), 'server' => cms_htaccess_status($payload), 'role' => cms_admin_role()]);
  }

  if ($api === 'mailer-tools') {
    cms_require_super_admin();
    $fresh = cms_load_data();
    if ($method === 'GET') {
      $ms = cms_mailer_settings($fresh);
      cms_json(['ok' => true, 'status' => [
        'mode' => $ms['mode'] ?? 'mail',
        'mailFunction' => function_exists('mail'),
        'openssl' => extension_loaded('openssl'),
        'sockets' => function_exists('stream_socket_client'),
        'smtpConfigured' => !empty($ms['smtp']['host']),
        'passwordSet' => !empty($ms['smtp']['password']),
        'logWritable' => is_writable(__DIR__) || (is_file(MAIL_LOG_FILE) && is_writable(MAIL_LOG_FILE)),
      ], 'logs' => cms_mail_logs(120)]);
    }
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    $action = (string)($payload['action'] ?? 'status');
    if ($action === 'connection') cms_json(cms_test_mailer_connection($fresh));
    if ($action === 'send-test') {
      $to = trim((string)($payload['to'] ?? ''));
      if (!filter_var($to, FILTER_VALIDATE_EMAIL)) cms_json(['ok' => false, 'message' => 'Enter a valid test email address.'], 422);
      $ok = cms_send_mail($to, 'LPCMS mailer test', "This is an LPCMS mailer test.\n\nTime: " . date('c') . "\nMode: " . (cms_mailer_settings($fresh)['mode'] ?? 'mail'), $fresh);
      cms_json(['ok' => $ok, 'message' => $ok ? 'Test email accepted by the configured mailer.' : 'Test email failed. Check the Mailer logs below.', 'logs' => cms_mail_logs(120)], $ok ? 200 : 502);
    }
    if ($action === 'clear-logs') {
      if (is_file(MAIL_LOG_FILE)) @file_put_contents(MAIL_LOG_FILE, '', LOCK_EX);
      cms_json(['ok' => true, 'message' => 'Mailer logs cleared.', 'logs' => []]);
    }
    cms_json(['ok' => false, 'message' => 'Unknown mailer tool action.'], 422);
  }


  if ($api === 'media-search') {
    cms_require_admin();
    $provider=(string)($_GET['provider']??'openverse'); $q=(string)($_GET['q']??''); $page=(int)($_GET['page']??1);
    try { cms_json(['ok'=>true,'provider'=>$provider,'results'=>cms_media_search(cms_load_data(),$provider,$q,$page)]); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'media-import' && $method === 'POST') {
    cms_require_admin();
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[]; $targetKey=(string)($payload['target']??'uploads');
    $targets=['uploads'=>UPLOAD_DIR,'gallery'=>GALLERY_DIR,'img'=>IMG_DIR]; if(!isset($targets[$targetKey])) cms_json(['ok'=>false,'error'=>'Invalid media target.'],422);
    $candidates=array_values(array_unique(array_filter([
      trim((string)($payload['url']??'')),
      trim((string)($payload['thumb']??'')),
    ],fn($v)=>$v!=='')));
    if(!$candidates) cms_json(['ok'=>false,'error'=>'No image URL was supplied.'],422);
    $remote=null; $downloadErrors=[];
    try {
      foreach($candidates as $candidate){
        try { $remote=cms_remote_image_bytes($candidate); break; }
        catch(Throwable $downloadError){ $downloadErrors[]=$downloadError->getMessage(); }
      }
      if(!$remote) throw new RuntimeException('Could not download the selected image. '.implode(' | ',array_values(array_unique($downloadErrors))));
      $name=trim((string)($payload['title']??''))?:basename((string)parse_url($remote['url'],PHP_URL_PATH))?:'imported-image';
      $mediaData=cms_load_data(); $resize=cms_media_resize_options($mediaData,$payload);
      $stored=cms_store_image_as_avif($remote['tmp'],$name,$targets[$targetKey],$resize);
      $message='Free media imported, scaled to the selected box, and converted to AVIF.';
      @unlink($remote['tmp']); $remote=null;
      $asset=cms_asset_public_url($targets[$targetKey],$stored['name']);
      cms_json(['ok'=>true,'url'=>$asset,'format'=>$stored['format'],'width'=>$stored['width'],'height'=>$stored['height'],'bytes'=>$stored['bytes'],'message'=>$message,'credit'=>['creator'=>(string)($payload['creator']??''),'source'=>(string)($payload['source']??''),'license'=>(string)($payload['license']??''),'licenseUrl'=>(string)($payload['licenseUrl']??''),'landing'=>(string)($payload['landing']??'')]]);
    } catch(Throwable $e){ if(is_array($remote)&&!empty($remote['tmp']))@unlink($remote['tmp']); cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'icon-search') {
    cms_require_admin(); $q=trim((string)($_GET['q']??'')); $prefix=trim((string)($_GET['prefix']??'')); if($q==='')cms_json(['ok'=>true,'icons'=>[]]);
    try { $url='https://api.iconify.design/search?query='.rawurlencode($q).'&limit=72'.($prefix!==''?'&prefix='.rawurlencode($prefix):''); $j=cms_http_json_request($url); cms_json(['ok'=>true,'icons'=>array_values(array_slice($j['icons']??[],0,72))]); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'ai-generate' && $method === 'POST') {
    cms_require_admin(); $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    try { cms_json(cms_ai_generate(cms_load_data(),(string)($payload['prompt']??''),(string)($payload['context']??''),(string)($payload['tone']??''))); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }


  if ($api === 'demo-intake' && $method === 'POST') {
    if(!cms_rate_limit_consume('demo-intake',6,3600)) cms_json(['ok'=>false,'error'=>'Too many demo requests. Please try again later.'],429);
    $fresh = cms_load_data();
    if (empty($fresh['demoIntake']['enabled'])) cms_json(['ok'=>false,'error'=>'Demo requests are not enabled.'],422);
    if (!empty($_POST['_hp'])) cms_json(['ok'=>true,'message'=>(string)($fresh['demoIntake']['thankYou']??'Thank you.')]);
    if (!cms_verify_hcaptcha($fresh,(string)($_POST['hcaptcha']??''))) cms_json(['ok'=>false,'error'=>'Captcha verification failed.'],422);
    $plan = (string)($_POST['plan']??'premium'); if(!in_array($plan,['premium','premium-pro'],true))$plan='premium';
    $currency = strtoupper((string)($_POST['currency']??'GBP')); if(!in_array($currency,['GBP','EUR'],true))$currency='GBP';
    $entry = [
      'id'=>'DEMO-'.date('Ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,8)),
      'createdAt'=>date('c'),'status'=>'pending','plan'=>$plan,'planTitle'=>$plan==='premium-pro'?'Premium Pro':'Premium','currency'=>$currency,
      'name'=>cms_demo_clean_text($_POST['name']??'',120),'email'=>strtolower(cms_demo_clean_text($_POST['email']??'',160)),'phone'=>cms_demo_clean_text($_POST['phone']??'',80),
      'company'=>cms_demo_clean_text($_POST['company']??'',120),'industry'=>cms_demo_clean_text($_POST['industry']??'',120),'tagline'=>cms_demo_clean_text($_POST['tagline']??'',180),
      'about'=>cms_demo_clean_text($_POST['about']??'',2500),'services'=>cms_demo_clean_text($_POST['services']??'',3000),'products'=>cms_demo_clean_text($_POST['products']??'',3000),
      'address'=>cms_demo_clean_text($_POST['address']??'',300),'existingWebsite'=>cms_demo_clean_text($_POST['existingWebsite']??'',300),'preferredDomain'=>cms_demo_clean_text($_POST['preferredDomain']??'',180),
      'language'=>cms_demo_clean_text($_POST['language']??'English',80),'goals'=>cms_demo_clean_text($_POST['goals']??'',1500),'primaryColor'=>cms_demo_clean_text($_POST['primaryColor']??'',20),
      'style'=>cms_demo_clean_text($_POST['style']??'clean',40),'chatChoice'=>cms_demo_clean_text($_POST['chatChoice']??'chatbot',20),'whatsapp'=>cms_demo_clean_text($_POST['whatsapp']??'',80),
      'bookingNeeded'=>!empty($_POST['bookingNeeded']),'socials'=>cms_demo_clean_text($_POST['socials']??'',1200),'logo'=>'',
    ];
    if ($entry['company']==='' || $entry['name']==='') cms_json(['ok'=>false,'error'=>'Business name and contact name are required.'],422);
    if (!filter_var($entry['email'],FILTER_VALIDATE_EMAIL)) cms_json(['ok'=>false,'error'=>'Valid email required.'],422);
    if ($entry['about']==='' || $entry['services']==='') cms_json(['ok'=>false,'error'=>'Business description and services are required.'],422);
    if (empty($_POST['consent'])) cms_json(['ok'=>false,'error'=>'Demo preparation consent is required.'],422);
    if ($entry['chatChoice']==='whatsapp' && $entry['whatsapp']==='') cms_json(['ok'=>false,'error'=>'WhatsApp number is required for the WhatsApp chat option.'],422);
    if (!empty($_FILES['logo']) && (int)($_FILES['logo']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE) {
      $f=$_FILES['logo']; if((int)($f['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) cms_json(['ok'=>false,'error'=>'Logo upload did not complete.'],422);
      if((int)($f['size']??0)<=0 || (int)$f['size']>IMAGE_MAX_UPLOAD_BYTES) cms_json(['ok'=>false,'error'=>'Logo must be between 1 byte and 20MB.'],413);
      try { $stored=cms_store_image_as_avif($f['tmp_name'],(string)($f['name']??'logo'),IMG_DIR,cms_media_resize_options($fresh,['boxPreset'=>'logo'])); }
      catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
      $entry['logo']=cms_asset_public_url(IMG_DIR,$stored['name']);
    }
    $entry['draft']=cms_demo_build_draft($entry,$fresh);
    if(!isset($fresh['settings']['demoIntakeAdmin']['requests'])||!is_array($fresh['settings']['demoIntakeAdmin']['requests']))$fresh['settings']['demoIntakeAdmin']['requests']=[];
    $fresh['settings']['demoIntakeAdmin']['requests'][]=$entry;
    if(count($fresh['settings']['demoIntakeAdmin']['requests'])>250)$fresh['settings']['demoIntakeAdmin']['requests']=array_slice($fresh['settings']['demoIntakeAdmin']['requests'],-250);
    if(!cms_save_data($fresh)) cms_json(['ok'=>false,'error'=>'Could not save the demo request.'],500);
    $notify=trim((string)($fresh['settings']['demoIntakeAdmin']['notifyEmail']??'')); if($notify==='')$notify=trim((string)($fresh['settings']['forms']['emailTo']??'')); if($notify==='')$notify=BREAKGLASS_ADMIN_EMAIL;
    foreach(preg_split('/[;,]+/',$notify)?:[] as $to){$to=trim($to);if(filter_var($to,FILTER_VALIDATE_EMAIL))cms_send_mail($to,'New website demo request - '.$entry['company'],"Reference: {$entry['id']}\nClient: {$entry['name']}\nEmail: {$entry['email']}\nPlan: {$entry['planTitle']} ({$entry['currency']})\nBusiness: {$entry['company']}\n\nOpen Super Admin > Demo requests to review, approve and build the draft.",$fresh);}
    cms_json(['ok'=>true,'message'=>(string)($fresh['demoIntake']['thankYou']??'Thank you. Your website draft was created for review.'),'requestId'=>$entry['id']]);
  }

  if ($api === 'demo-requests') {
    cms_require_super_admin();
    $fresh=cms_load_data(); $requests=$fresh['settings']['demoIntakeAdmin']['requests']??[];
    if($method==='GET') cms_json(['ok'=>true,'requests'=>array_values($requests)]);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[]; $action=(string)($payload['action']??''); $id=(string)($payload['id']??'');
    $index=null; foreach($requests as $i=>$r){if((string)($r['id']??'')===$id){$index=$i;break;}} if($index===null)cms_json(['ok'=>false,'error'=>'Demo request not found.'],404);
    try{
      if($action==='approve'){$request=cms_demo_apply_request($fresh,$index);if(!cms_save_data($fresh))throw new RuntimeException('Could not save approved website draft.');cms_clear_cache();cms_json(['ok'=>true,'message'=>'Demo approved and generated content loaded into the current website.','request'=>$request,'requests'=>array_values($fresh['settings']['demoIntakeAdmin']['requests']??[]),'data'=>cms_public_data($fresh)]);}
      if($action==='bill'){$request=cms_demo_send_bill($fresh,$index);if(!cms_save_data($fresh))throw new RuntimeException('Billing email was sent but the billing status could not be saved.');cms_json(['ok'=>true,'message'=>'Billing email sent to the client.','request'=>$request,'requests'=>array_values($fresh['settings']['demoIntakeAdmin']['requests']??[])]);}
      if($action==='reject'){$fresh['settings']['demoIntakeAdmin']['requests'][$index]['status']='rejected';$fresh['settings']['demoIntakeAdmin']['requests'][$index]['rejectedAt']=date('c');cms_save_data($fresh);cms_json(['ok'=>true,'message'=>'Demo request marked as rejected.','requests'=>array_values($fresh['settings']['demoIntakeAdmin']['requests'])]);}
      if($action==='delete'){array_splice($fresh['settings']['demoIntakeAdmin']['requests'],$index,1);cms_save_data($fresh);cms_json(['ok'=>true,'message'=>'Demo request deleted.','requests'=>array_values($fresh['settings']['demoIntakeAdmin']['requests'])]);}
      cms_json(['ok'=>false,'error'=>'Unknown demo request action.'],422);
    }catch(Throwable $e){cms_json(['ok'=>false,'error'=>$e->getMessage()],422);}
  }

  if ($api === 'abandoned-cart-recover' && $method === 'GET') {
    $token = trim((string)($_GET['token'] ?? ''));
    if ($token === '') cms_json(['ok'=>false,'error'=>'Recovery token required.'],422);
    $cart = cms_abandoned_cart_recover(cms_load_data(), $token);
    if (!$cart) cms_json(['ok'=>false,'error'=>'Saved cart was not found or is no longer available.'],404);
    cms_json(['ok'=>true,'cart'=>$cart['cart'] ?? [],'email'=>$cart['email'] ?? '']);
  }

  if ($api === 'abandoned-cart' && $method === 'POST') {
    if(!cms_rate_limit_consume('abandoned-cart',30,900)) cms_json(['ok'=>false,'error'=>'Too many cart updates. Try again later.'],429);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    $fresh=cms_load_data();
    if(empty($fresh['settings']['abandonedCart']['enabled'])) cms_json(['ok'=>false,'error'=>'Cart recovery is not enabled.'],422);
    $action=(string)($payload['action']??'capture');
    $email=trim((string)($payload['email']??''));
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) cms_json(['ok'=>false,'error'=>'Valid email required.'],422);
    if($action==='complete') cms_json(['ok'=>true,'result'=>cms_abandoned_cart_complete($fresh,$email)]);
    try {
      $result=cms_abandoned_cart_capture($fresh,$email,is_array($payload['items']??null)?$payload['items']:[],['shippingId'=>$payload['shippingId']??'','discountCode'=>$payload['discountCode']??'']);
      cms_json(['ok'=>true,'token'=>$result['token']??'','cart'=>$result['cart']??[]]);
    } catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'abandoned-cart-tools') {
    cms_require_super_admin();
    $fresh=cms_load_data();
    $carts=$fresh['settings']['abandonedCart']['carts']??[];
    if($method==='GET') cms_json(['ok'=>true,'enabled'=>!empty($fresh['settings']['abandonedCart']['enabled']),'count'=>count($carts),'carts'=>array_values(array_slice(array_reverse($carts),0,50))]);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    $action=(string)($payload['action']??'process');
    if($action==='process') cms_json(['ok'=>true,'result'=>cms_process_abandoned_carts($fresh),'carts'=>$fresh['settings']['abandonedCart']['carts']??[]]);
    if($action==='clear-completed'){
      $fresh['settings']['abandonedCart']['carts']=array_values(array_filter($carts,fn($c)=>empty($c['completedAt'])));
      cms_save_data($fresh); cms_json(['ok'=>true,'message'=>'Completed recovered carts cleared.','carts'=>$fresh['settings']['abandonedCart']['carts']]);
    }
    cms_json(['ok'=>false,'error'=>'Unknown cart recovery action.'],422);
  }

  if ($api === 'abandoned-cart-cron' && $method === 'GET') {
    $fresh=cms_load_data();
    $saved=(string)($fresh['settings']['abandonedCart']['cronKey']??'');
    $key=(string)($_GET['key']??'');
    if($saved==='' || $key==='' || !hash_equals($saved,$key)) cms_json(['ok'=>false,'error'=>'Invalid scheduler key.'],403);
    if(empty($fresh['settings']['abandonedCart']['enabled'])) cms_json(['ok'=>false,'error'=>'Cart recovery is disabled.'],422);
    cms_json(['ok'=>true,'result'=>cms_process_abandoned_carts($fresh)]);
  }

  if ($api === 'booking-slots' && $method === 'GET') {
    try { cms_json(['ok'=>true,'slots'=>cms_booking_slots(cms_load_data(),(string)($_GET['date']??''),(string)($_GET['serviceId']??''))]); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'booking-create' && $method === 'POST') {
    if(!cms_rate_limit_consume('booking-create',12,900)) cms_json(['ok'=>false,'error'=>'Too many booking attempts. Try again later.'],429);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[]; $fresh=cms_load_data();
    if (!cms_verify_hcaptcha($fresh, (string)($payload['hcaptcha'] ?? ''))) cms_json(['ok'=>false,'error'=>'Captcha verification failed.'],422);
    try { cms_json(cms_booking_create($fresh,$payload)); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'chatbot' && $method === 'POST') {
    if(!cms_rate_limit_consume('chatbot',30,600)) cms_json(['ok'=>false,'error'=>'Too many chat messages. Try again later.'],429);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    try { cms_json(cms_chatbot_reply(cms_load_data(),$payload)); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'checkout-start' && $method === 'POST') {
    if(!cms_rate_limit_consume('checkout-start',20,900)) cms_json(['ok'=>false,'error'=>'Too many checkout attempts. Try again later.'],429);
    $payload=json_decode(file_get_contents('php://input')?:'',true)?:[];
    try { cms_json(cms_start_checkout(cms_load_data(),$payload)); }
    catch(Throwable $e){ cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
  }

  if ($api === 'sync-htaccess' && $method === 'POST') {
    cms_require_super_admin();
    $fresh = cms_load_data();
    $result = cms_sync_htaccess_files($fresh);
    cms_json(['ok' => !empty($result['ok']), 'message' => !empty($result['ok']) ? 'Root and assets .htaccess files are synced.' : 'Could not sync one or more .htaccess files.', 'result' => $result, 'server' => cms_htaccess_status($fresh)]);
  }

  if ($api === 'upload' && $method === 'POST') {
    cms_require_admin();
    $targets = ['uploads' => UPLOAD_DIR, 'gallery' => GALLERY_DIR, 'img' => IMG_DIR, 'docs' => DOCS_DIR, 'video' => VIDEO_DIR];
    $targetKey = (string)($_POST['target'] ?? 'uploads');
    if (!isset($targets[$targetKey])) cms_json(['ok' => false, 'error' => 'Invalid upload target'], 422);
    $target = $targets[$targetKey];
    if (empty($_FILES['file']) || !isset($_FILES['file']['tmp_name'])) cms_json(['ok' => false, 'error' => 'No file uploaded'], 422);
    $f = $_FILES['file'];
    $uploadError = (int)($f['error'] ?? UPLOAD_ERR_OK);
    if ($uploadError !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) cms_json(['ok' => false, 'error' => 'The upload did not complete.'], 422);
    if (($f['size'] ?? 0) <= 0) cms_json(['ok' => false, 'error' => 'The uploaded file is empty.'], 422);
    $maxBytes = $targetKey === 'video' ? VIDEO_MAX_UPLOAD_BYTES : IMAGE_MAX_UPLOAD_BYTES;
    if (($f['size'] ?? 0) > $maxBytes) cms_json(['ok' => false, 'error' => $targetKey === 'video' ? 'Maximum video upload size is 50MB.' : 'Maximum upload size is 20MB.'], 413);

    if ($targetKey === 'video') {
      try { $stored = cms_store_hero_video($f['tmp_name'], (string)($f['name'] ?? 'hero-video.mp4'), $target); }
      catch (RuntimeException $e) { cms_json(['ok'=>false,'error'=>$e->getMessage()],422); }
      $url=cms_asset_public_url($target,$stored['name']);
      cms_json(['ok'=>true,'url'=>$url,'name'=>$stored['name'],'format'=>$stored['format'],'bytes'=>$stored['bytes'],'optimized'=>$stored['optimized'],'message'=>$stored['optimized']?'Video optimized to HD H.264 MP4.':'Video uploaded. Install ffmpeg for automatic HD optimization.']);
    }

    if ($targetKey === 'docs') {
      if (cms_detect_mime($f['tmp_name']) !== 'application/pdf') cms_json(['ok' => false, 'error' => 'Only PDF files are allowed in documents.'], 422);
      $name = date('Ymd-His') . '-' . cms_slug(pathinfo((string)$f['name'], PATHINFO_FILENAME)) . '-' . bin2hex(random_bytes(3)) . '.pdf';
      $dest = $target . '/' . $name;
      if (!move_uploaded_file($f['tmp_name'], $dest)) cms_json(['ok' => false, 'error' => 'Upload failed'], 500);
      @chmod($dest, 0644);
      $url = cms_asset_public_url($target,$name);
      cms_json(['ok' => true, 'url' => $url, 'name' => $name, 'format' => 'pdf', 'bytes' => (int)filesize($dest)]);
    }

    $mediaData=cms_load_data();
    $resize=cms_media_resize_options($mediaData,$_POST);
    try {
      $stored = cms_store_image_as_avif($f['tmp_name'], (string)($f['name'] ?? 'image'), $target, $resize);
      $message = 'Image scaled to ' . $resize['boxWidth'] . '×' . $resize['boxHeight'] . ' box and converted to AVIF.';
    } catch (RuntimeException $e) {
      cms_json(['ok' => false, 'error' => $e->getMessage()], 422);
    }
    $url = cms_asset_public_url($target,$stored['name']);
    cms_json([
      'ok' => true,
      'url' => $url,
      'name' => $stored['name'],
      'format' => $stored['format'],
      'width' => $stored['width'],
      'height' => $stored['height'],
      'bytes' => $stored['bytes'],
      'convertedBy' => $stored['method'],
      'message' => $message,
    ]);
  }

  if ($api === 'form' && $method === 'POST') {
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    if (!cms_verify_hcaptcha($data, (string)($payload['hcaptcha'] ?? ''))) cms_json(['ok' => false, 'error' => 'Captcha verification failed.'], 422);
    $entry = [
      'time' => date('c'),
      'type' => substr((string)($payload['type'] ?? 'contact'), 0, 40),
      'name' => substr(strip_tags((string)($payload['name'] ?? '')), 0, 120),
      'email' => substr(strip_tags((string)($payload['email'] ?? '')), 0, 160),
      'phone' => substr(strip_tags((string)($payload['phone'] ?? '')), 0, 80),
      'message' => substr(strip_tags((string)($payload['message'] ?? '')), 0, 3000),
    ];
    if (!filter_var($entry['email'], FILTER_VALIDATE_EMAIL)) cms_json(['ok' => false, 'error' => 'Valid email required.'], 422);
    if (!empty($data['settings']['forms']['storeSubmissions'])) {
      $data['settings']['forms']['submissions'][] = $entry;
      cms_save_data($data);
    }
    $toRaw = trim((string)($data['settings']['forms']['emailTo'] ?? ''));
    if ($toRaw === '') $toRaw = BREAKGLASS_ADMIN_EMAIL;
    $sentCount = 0;
    foreach (preg_split('/[;,]+/', $toRaw) ?: [] as $recipient) {
      $recipient = trim((string)$recipient);
      if ($recipient !== '' && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        if (cms_send_mail($recipient, 'New website form submission', "Type: {$entry['type']}\nName: {$entry['name']}\nEmail: {$entry['email']}\nPhone: {$entry['phone']}\n\n{$entry['message']}", $data)) $sentCount++;
      }
    }
    $isOrder = str_contains(strtolower($entry['type']), 'order');
    $webhook = $isOrder ? cms_post_commerce_webhook($data, ['event' => 'order.submitted', 'submission' => $entry]) : ['skipped' => true];
    if ($isOrder && !empty($data['settings']['abandonedCart']['enabled'])) cms_abandoned_cart_complete($data, $entry['email']);
    cms_json(['ok' => true, 'message' => $sentCount > 0 ? 'Thank you. Your request was sent.' : 'Thank you. Your request was saved, but email delivery needs mail/SMTP configuration.', 'commerceWebhook' => $webhook]);
  }

  if ($api === 'maintenance' && $method === 'POST') {
    cms_require_super_admin();
    $payload = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
    $action = (string)($payload['action'] ?? 'stats');
    if ($action === 'clear-cache') {
      $deleted = cms_clear_cache();
      cms_json(['ok' => true, 'message' => 'Cache cleared. Deleted ' . $deleted . ' file(s).', 'cache' => cms_cache_stats(), 'media' => cms_media_stats(cms_load_data())]);
    }
    if ($action === 'stats') {
      cms_json(['ok' => true, 'cache' => cms_cache_stats(), 'media' => cms_media_stats(cms_load_data())]);
    }
    if ($action === 'cleanup-old-cache') {
      $settings = cms_cache_settings(cms_load_data());
      $maxAge = max(1, (int)($settings['maxAgeDays'] ?? 14)) * 86400;
      $deleted = 0;
      foreach (glob(CACHE_DIR . '/*') ?: [] as $file) {
        if (is_file($file) && filemtime($file) < time() - $maxAge && @unlink($file)) $deleted++;
      }
      cms_json(['ok' => true, 'message' => 'Old cache cleanup complete. Deleted ' . $deleted . ' file(s).', 'cache' => cms_cache_stats(), 'media' => cms_media_stats(cms_load_data())]);
    }
    cms_json(['ok' => false, 'error' => 'Unknown maintenance action'], 422);
  }

  if ($api === 'search') {
    $q = strtolower(trim((string)($_GET['q'] ?? '')));
    $out = [];
    if ($q !== '') {
      foreach (['services','products','blog','gallery'] as $group) {
        foreach (($data[$group]['items'] ?? []) as $i => $item) {
          $text = strtolower(json_encode($item, JSON_UNESCAPED_UNICODE));
          if (str_contains($text, $q)) {
            $out[] = [
              'group' => $group,
              'title' => $item['title'] ?? $item['alt'] ?? ucfirst($group) . ' item',
              'text' => $item['excerpt'] ?? $item['text'] ?? $item['alt'] ?? '',
              'href' => '#' . $group,
              'index' => $i
            ];
          }
        }
      }
    }
    cms_json(['ok' => true, 'results' => $out]);
  }

  cms_json(['ok' => false, 'error' => 'Unknown API'], 404);
}

cms_try_serve_page_cache($data);
cms_start_page_cache($data);
$meta = cms_find_meta($data);
$base = cms_base_url($data);
$metaImage = (string)($meta['image'] ?? '');
if ($metaImage && !preg_match('/^https?:\/\//', $metaImage)) $metaImage = rtrim($base, '/') . '/' . ltrim($metaImage, '/');
$canonical = (string)($meta['canonical'] ?? '') ?: (!empty($meta['path']) ? $base . $meta['path'] : $base . strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
$theme = $data['settings']['theme'] ?? [];
$libs = $data['settings']['libraries'] ?? [];
$activeThemeAssets = cms_active_theme_assets($data);
$activeThemeId = (string)($activeThemeAssets['id'] ?? 'lpcms-default');
$currentLang = cms_current_language($data);
$routeInfo = cms_find_route_item($data);
$i18nPack = cms_i18n_pack();
?>
<!doctype html>
<html lang="<?= cms_e($currentLang) ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="<?= cms_e($meta['robots'] ?? 'index,follow') ?>">
  <title><?= cms_e($meta['title'] ?? ($data['settings']['siteName'] ?? 'Flat CMS')) ?></title>
  <meta name="description" content="<?= cms_e($meta['description'] ?? '') ?>">
  <meta name="keywords" content="<?= cms_e($meta['keywords'] ?? '') ?>">
  <link rel="canonical" href="<?= cms_e($canonical) ?>">
  <?php foreach (cms_language_options() as $languageOption):
    $altLang = strtolower((string)($languageOption['code'] ?? ''));
    if ($altLang === '') continue;
    $altPath = !empty($routeInfo['item']) ? cms_pretty_path((string)$routeInfo['group'], (string)$routeInfo['slug'], $data, $altLang) : cms_language_home_path($data, $altLang);
    $altUrl = rtrim($base, '/') . $altPath;
  ?><link rel="alternate" hreflang="<?= cms_e($altLang) ?>" href="<?= cms_e($altUrl) ?>">
  <?php endforeach; ?>
  <link rel="alternate" hreflang="x-default" href="<?= cms_e(rtrim($base, '/') . (!empty($routeInfo['item']) ? cms_pretty_path((string)$routeInfo['group'], (string)$routeInfo['slug'], $data, $data['settings']['defaultLang'] ?? 'en') : cms_language_home_path($data, $data['settings']['defaultLang'] ?? 'en'))) ?>">
  <link rel="icon" href="<?= cms_e(cms_favicon_href($data)) ?>">
  <link rel="shortcut icon" href="<?= cms_e(cms_favicon_href($data)) ?>">
  <meta property="og:title" content="<?= cms_e($meta['title'] ?? '') ?>">
  <meta property="og:description" content="<?= cms_e($meta['description'] ?? '') ?>">
  <meta property="og:type" content="<?= cms_e($meta['type'] ?? 'website') ?>">
  <meta property="og:url" content="<?= cms_e($canonical) ?>">
  <?php if ($metaImage): ?><meta property="og:image" content="<?= cms_e($metaImage) ?>"><?php endif; ?>
  <meta name="twitter:card" content="summary_large_image">
  <?php $appSettings = cms_app_settings($data); if (!empty($appSettings['enabled'])): ?>
  <meta name="theme-color" content="<?= cms_e((string)$appSettings['themeColor']) ?>">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="<?= cms_e((string)$appSettings['shortName']) ?>">
  <link rel="manifest" href="<?= cms_e(rtrim(cms_route_prefix(), '/') . '/manifest.webmanifest') ?>">
  <?php if (!empty($appSettings['appleIcon']) || !empty($appSettings['icon'])): ?><link rel="apple-touch-icon" href="<?= cms_e((string)($appSettings['appleIcon'] ?? $appSettings['icon'])) ?>"><?php endif; ?>
  <?php endif; ?>
  <?php if (!empty($libs['bootstrap'])): ?><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><?php endif; ?>
  <?php if (!empty($libs['bootstrapIcons'])): ?><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet"><?php endif; ?>
  <?php if (!empty($libs['aos'])): ?><link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet"><?php endif; ?>
  <?php if (!empty($libs['swiper'])): ?><link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet"><?php endif; ?>
  <?php if (!empty($libs['glightbox'])): ?><link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet"><?php endif; ?>
  <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
  <style>
    :root{
      --cms-brand: <?= cms_e($theme['brand'] ?? '#0d6efd') ?>;
      --cms-brand2: <?= cms_e($theme['brand2'] ?? '#6610f2') ?>;
      --cms-accent: <?= cms_e($theme['accent'] ?? '#20c997') ?>;
      --cms-text: <?= cms_e($theme['text'] ?? '#1f2937') ?>;
      --cms-muted: <?= cms_e($theme['muted'] ?? '#6b7280') ?>;
      --cms-bg: <?= cms_e($theme['bg'] ?? '#fff') ?>;
      --cms-soft: <?= cms_e($theme['softBg'] ?? '#f6f8fb') ?>;
      --cms-radius: <?= cms_e($theme['radius'] ?? '22px') ?>;
      --cms-font: <?= cms_e($theme['font'] ?? 'system-ui') ?>;
    }
    *{box-sizing:border-box}
    html{scroll-behavior:smooth}
    body{margin:0;background:var(--cms-bg);color:var(--cms-text);font-family:var(--cms-font);font-size:16px;line-height:1.65;overflow-x:hidden;-webkit-font-smoothing:antialiased}
    a{color:inherit;text-decoration:none}.cms-soft{background:linear-gradient(180deg,var(--cms-soft),#fff)}.muted{color:var(--cms-muted)}
    h1,h2,h3,h4,h5,h6{letter-spacing:-.025em;color:#172033}.eyebrow{color:var(--cms-brand);font-weight:720;letter-spacing:.12em;text-transform:uppercase;font-size:.74rem}
    .site-header{position:sticky;top:0;z-index:50;background:rgba(255,255,255,.82);backdrop-filter:blur(18px);border-bottom:1px solid rgba(15,23,42,.07);box-shadow:0 10px 28px rgba(15,23,42,.035)}
    .brand-mark{display:flex;gap:.65rem;align-items:center;font-weight:740;font-size:1.05rem}.brand-mark img{max-height:40px;width:auto}.brand-dot{min-width:38px;width:auto;padding:0 9px;height:38px;border-radius:11px;background:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2));display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:780;box-shadow:0 10px 22px rgba(13,110,253,.24)}
    .main-nav{display:flex;align-items:center;gap:.8rem}.main-nav ul{display:flex;align-items:center;gap:.2rem;list-style:none;margin:0;padding:0}.main-nav li{position:relative}.nav-linkx{display:inline-flex;align-items:center;padding:.62rem .82rem;border-radius:999px;color:#42526b;font-weight:620;font-size:.94rem;transition:.18s ease}.nav-linkx:hover{background:rgba(13,110,253,.075);color:var(--cms-brand)}.submenu{display:block!important;position:absolute;left:0;top:calc(100% + 10px);background:rgba(255,255,255,.98);box-shadow:0 28px 80px rgba(15,23,42,.16);border:1px solid rgba(15,23,42,.08);min-width:250px;border-radius:18px;padding:.55rem!important;opacity:0;visibility:hidden;transform:translateY(8px) scale(.985);transition:.18s ease;pointer-events:none}.submenu:before{content:"";position:absolute;left:24px;top:-7px;width:14px;height:14px;background:#fff;border-left:1px solid rgba(15,23,42,.08);border-top:1px solid rgba(15,23,42,.08);transform:rotate(45deg)}.has-sub:hover>.submenu,.has-sub:focus-within>.submenu{opacity:1;visibility:visible;transform:none;pointer-events:auto}.submenu li,.submenu a{width:100%}.submenu .nav-linkx{border-radius:12px;padding:.72rem .8rem;justify-content:space-between}.submenu .nav-linkx:after{content:"↗";opacity:.35;font-size:.78rem}.nav-actions{display:flex;align-items:center;gap:.5rem}.icon-btn{border:1px solid rgba(15,23,42,.08);background:#fff;border-radius:999px;width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;color:#40516b;box-shadow:0 8px 22px rgba(15,23,42,.04);transition:.18s ease}.icon-btn:hover{transform:translateY(-1px);color:var(--cms-brand)}.lang-picker{position:relative}.lang-flag-btn{font-size:1rem;line-height:1;padding:0}.flag-img{width:22px;height:16px;display:block;object-fit:cover;border-radius:2px;box-shadow:0 0 0 1px rgba(15,23,42,.08)}.lang-flag-btn .flag-img{width:22px;height:16px}.lang-option .flag-img{width:24px;height:18px;flex:0 0 24px}.lang-menu{position:absolute;right:0;top:calc(100% + 10px);width:210px;max-height:min(66vh,520px);overflow:auto;background:rgba(255,255,255,.98);backdrop-filter:blur(18px);border:1px solid rgba(15,23,42,.09);border-radius:18px;padding:.45rem;box-shadow:0 24px 70px rgba(15,23,42,.17);z-index:90}.lang-menu:before{content:"";position:absolute;right:14px;top:-6px;width:12px;height:12px;background:#fff;border-left:1px solid rgba(15,23,42,.08);border-top:1px solid rgba(15,23,42,.08);transform:rotate(45deg)}.lang-option{width:100%;border:0;background:transparent;border-radius:12px;padding:.62rem .7rem;display:flex;align-items:center;gap:.7rem;text-align:left;color:#334155;font-weight:650}.lang-option:hover,.lang-option.active{background:rgba(13,110,253,.075);color:var(--cms-brand)}.lang-option .lang-code{margin-left:auto;font-size:.68rem;letter-spacing:.08em;opacity:.52;text-transform:lowercase}.hamburger{display:none}.mobile-panel{display:none}
    @media(max-width:1199px){.main-nav{display:none}.hamburger{display:inline-flex}.mobile-panel{display:block;position:fixed;top:0;right:0;width:min(390px,92vw);height:100vh;background:#fff;z-index:80;box-shadow:-20px 0 70px rgba(15,23,42,.16);transform:translateX(110%);transition:.25s ease;padding:1.25rem;overflow:auto}.mobile-panel.open{transform:translateX(0)}.mobile-panel ul{list-style:none;padding:0;margin:1rem 0}.mobile-panel a{display:block;padding:.9rem;border-bottom:1px solid rgba(15,23,42,.07)}.mobile-panel .mobile-submenu{display:block;list-style:none;margin:.1rem 0 .45rem;padding:.25rem .35rem .35rem .85rem;border-left:2px solid rgba(13,110,253,.15);background:#f8fbff;border-radius:0 14px 14px 0}.mobile-panel .mobile-submenu a{padding:.7rem .8rem;border-bottom:0;border-radius:10px;color:#52627a}.mobile-panel .mobile-submenu a:hover{background:#fff;color:var(--cms-brand)}}
    .btn-cms{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;border:0;border-radius:999px;padding:.78rem 1.15rem;background:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2));color:#fff;font-weight:700;box-shadow:0 14px 34px rgba(13,110,253,.21);transition:.18s ease}.btn-cms:hover{color:#fff;transform:translateY(-2px);box-shadow:0 18px 40px rgba(13,110,253,.28)}.btn-ghost{border:1px solid rgba(15,23,42,.1);background:#fff;color:#27364d;border-radius:999px;padding:.72rem 1.05rem;font-weight:650;box-shadow:0 8px 24px rgba(15,23,42,.035)}.btn-ghost:hover{border-color:rgba(13,110,253,.28);color:var(--cms-brand)}
    .section{padding:96px 0;position:relative}.section-title{max-width:740px;margin:0 auto 2.75rem;text-align:center}.section-title h2{font-size:clamp(2rem,4vw,3rem);font-weight:760;line-height:1.08;margin:.35rem 0}.section-title p{color:var(--cms-muted);font-size:1.05rem;margin:0}.section-title,.cms-card,.hero-card,.contact-box,.cta{animation:cmsFadeUp .65s ease both}@keyframes cmsFadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important;scroll-behavior:auto!important}}
    .hero{min-height:88vh;display:flex;align-items:center;position:relative;overflow:hidden;background:radial-gradient(circle at 12% 8%,rgba(13,110,253,.16),transparent 35%),radial-gradient(circle at 88% 18%,rgba(102,16,242,.15),transparent 34%),linear-gradient(180deg,#f8fbff,#fff)}.hero-particles{position:absolute;inset:0;width:100%;height:100%;pointer-events:none;z-index:0;opacity:.9;filter:saturate(1.05)}.hero>.container,.hero>.container-fluid{position:relative;z-index:2}.hero:before{content:"";position:absolute;inset:auto -12% -18% -12%;height:36%;background:linear-gradient(90deg,rgba(13,110,253,.06),rgba(102,16,242,.07));filter:blur(45px)}.hero-card{border:1px solid rgba(15,23,42,.08);background:rgba(255,255,255,.78);box-shadow:0 28px 78px rgba(15,23,42,.12);border-radius:calc(var(--cms-radius) + 12px);overflow:hidden}.hero h1,.hero .h1{font-size:clamp(2.55rem,6.5vw,5rem);font-weight:780;letter-spacing:-.055em;line-height:.98}.hero p{font-size:1.12rem;color:var(--cms-muted);max-width:640px}.hero-img{min-height:410px;background-size:cover;background-position:center;background-image:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2));border-radius:var(--cms-radius);box-shadow:inset 0 0 0 1px rgba(255,255,255,.28)}.hero-media-card{overflow:hidden}.hero-video-wrap{border-radius:var(--cms-radius);overflow:hidden;background:#0f172a;position:relative}.hero-video{display:block;width:100%;aspect-ratio:16/9;object-fit:cover;background:#eef4ff}.hero-video-steps{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.4rem;padding:.7rem .45rem .15rem}.hero-video-step{display:flex;align-items:center;justify-content:center;gap:.35rem;min-width:0;padding:.42rem .35rem;border-radius:12px;background:var(--cms-soft);color:#334155;font-size:.69rem;font-weight:750;line-height:1.1;text-align:center}.hero-video-step i{color:var(--cms-brand);font-size:.82rem;flex:0 0 auto}.hero-video-step span{white-space:normal}.hero-dots{display:flex;gap:.45rem;margin-top:1.25rem}.hero-dots button{width:10px;height:10px;border:0;border-radius:999px;background:#cbd5e1}.hero-dots button.active{background:var(--cms-brand);width:28px}
    .cms-card{height:100%;border:1px solid rgba(15,23,42,.075);border-radius:var(--cms-radius);background:rgba(255,255,255,.92);padding:1.35rem;box-shadow:0 16px 42px rgba(15,23,42,.055);transition:.22s ease}.cms-card:hover{transform:translateY(-5px);box-shadow:0 24px 70px rgba(15,23,42,.1)}.cms-icon{width:48px;height:48px;border-radius:15px;display:inline-flex;align-items:center;justify-content:center;background:rgba(13,110,253,.09);color:var(--cms-brand);font-size:1.35rem;margin-bottom:1rem}.stat-card{text-align:center;padding:1.2rem}.stat-number{font-size:clamp(2rem,4vw,3rem);font-weight:770;color:var(--cms-brand);letter-spacing:-.04em}.feature-panel{border-radius:calc(var(--cms-radius) + 10px);background:linear-gradient(135deg,rgba(13,110,253,.92),rgba(102,16,242,.92));min-height:430px;box-shadow:0 25px 70px rgba(13,110,253,.18)}.timeline-step{position:relative}.timeline-step:before{content:"";position:absolute;left:24px;top:58px;bottom:-28px;width:1px;background:rgba(13,110,253,.14)}.timeline-step:last-child:before{display:none}.pricing-card.featured{border-color:rgba(13,110,253,.28);box-shadow:0 28px 90px rgba(13,110,253,.15)}.pricing-price{font-size:2.1rem;font-weight:780;color:#172033}.faq-item{border:1px solid rgba(15,23,42,.08);border-radius:18px;background:#fff;margin-bottom:.85rem;overflow:hidden}.faq-q{width:100%;border:0;background:#fff;padding:1rem 1.1rem;text-align:left;font-weight:690;display:flex;justify-content:space-between;align-items:center}.faq-a{padding:0 1.1rem 1.1rem;color:var(--cms-muted)}
    .gallery-img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:var(--cms-radius);cursor:zoom-in;box-shadow:0 16px 38px rgba(15,23,42,.08)}.avatar{width:56px;height:56px;border-radius:50%;object-fit:cover;background:var(--cms-soft)}.map-frame{width:100%;min-height:380px;border:0;border-radius:var(--cms-radius);box-shadow:0 16px 42px rgba(15,23,42,.055)}.contact-box{border-radius:var(--cms-radius);background:#fff;border:1px solid rgba(15,23,42,.08);box-shadow:0 18px 45px rgba(15,23,42,.06);padding:1.5rem}.contact-detail-list{display:grid;gap:.72rem}.contact-detail-row{display:flex;align-items:flex-start;gap:.7rem}.contact-detail-icon{width:34px;height:34px;flex:0 0 34px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#f1f5f9;color:#64748b}.contact-detail-row p{margin:0;line-height:1.5}.contact-form-btn{margin-top:56px!important}.contact-socials{display:flex;align-items:center;gap:.55rem;margin-top:1.25rem}.contact-social-link{width:38px;height:38px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;background:#f1f5f9;border:1px solid #e2e8f0;color:#64748b!important;font-size:1rem;transition:.18s ease}.contact-social-link:hover{background:#e2e8f0;color:#334155!important;transform:translateY(-1px)}.cta{border-radius:calc(var(--cms-radius) + 14px);background:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2));color:#fff;padding:3rem;overflow:hidden;position:relative}.cta:after{content:"";position:absolute;right:-10%;top:-25%;width:45%;height:80%;background:rgba(255,255,255,.14);border-radius:50%;filter:blur(12px)}.cta p{color:rgba(255,255,255,.84)}.site-footer{padding:34px 0;background:#0f172a;color:#cbd5e1}.site-footer a{color:#cbd5e1;text-decoration:none}.site-footer a:hover{color:#fff}.footer-layout{display:grid;grid-template-columns:minmax(250px,1.45fr) minmax(140px,.75fr) minmax(330px,1.55fr) auto;gap:1.5rem;align-items:center}.footer-layout.no-apps{grid-template-columns:minmax(250px,1.5fr) minmax(180px,1fr) auto}.footer-copyline{font-size:.9rem;line-height:1.45}.footer-menu{display:flex;gap:1rem;align-items:center;flex-wrap:wrap}.footer-menu a{white-space:nowrap}.footer-apps-label{font-size:.72rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8;margin-bottom:.5rem}.footer-app-badges{display:flex;gap:.55rem;flex-wrap:wrap}.footer-app-badge{appearance:none;border:1px solid rgba(255,255,255,.14);background:#fff;color:#0f172a;border-radius:13px;min-width:154px;padding:.58rem .72rem;display:inline-flex;align-items:center;gap:.62rem;text-align:left;box-shadow:0 10px 28px rgba(0,0,0,.16);transition:.18s ease;cursor:pointer}.footer-app-badge:hover{transform:translateY(-2px);box-shadow:0 14px 34px rgba(0,0,0,.24)}.footer-app-icon{font-size:1.48rem;line-height:1;display:inline-flex}.footer-app-copy{display:flex;flex-direction:column;line-height:1.05}.footer-app-copy small{font-size:.61rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.04em}.footer-app-copy strong{font-size:.86rem;font-weight:800;margin-top:.16rem}.footer-app-badge:focus-visible{outline:3px solid rgba(255,255,255,.45);outline-offset:3px}.footer-socials{display:flex;gap:.55rem;align-items:center;justify-content:flex-end}.footer-social-link{width:36px;height:36px;border:1px solid rgba(255,255,255,.16);border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;transition:.18s ease}.footer-social-link:hover{background:rgba(255,255,255,.08);transform:translateY(-1px)}.scroll-up{position:fixed;right:18px;bottom:18px;z-index:60}.admin-login{position:fixed;left:18px;bottom:18px;z-index:60}.role-badge{display:inline-flex;align-items:center;border-radius:999px;background:#eef4ff;color:#0d6efd;padding:.18rem .55rem;font-size:.72rem;font-weight:750;text-transform:uppercase;letter-spacing:.04em}
    .cms-modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.58);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem}.cms-save-backdrop{z-index:140}.cms-modal{background:#fff;border-radius:24px;max-width:860px;width:100%;max-height:90vh;overflow:auto;box-shadow:0 30px 100px rgba(0,0,0,.25);padding:1.5rem}.cms-modal.small{max-width:480px}.cms-editable.admin{outline:1.5px dashed rgba(13,110,253,.34);outline-offset:4px;border-radius:8px;min-height:1.2em;cursor:text}.cms-editable.admin:focus{outline-color:var(--cms-brand);background:rgba(13,110,253,.055)}
    .admin-panel{position:fixed;top:0;left:0;height:100vh;width:min(720px,100vw);background:#f8fafc;z-index:110;box-shadow:26px 0 90px rgba(15,23,42,.18);transform:translateX(-110%);transition:.25s ease;overflow:auto}.admin-panel.open{transform:translateX(0)}.admin-panel header{position:sticky;top:0;background:rgba(248,250,252,.92);backdrop-filter:blur(14px);z-index:2;padding:1rem 1rem .85rem;border-bottom:1px solid rgba(15,23,42,.08)}.admin-title{font-weight:760;font-size:1.05rem}.admin-subtitle{font-size:.78rem;color:#64748b}.admin-panel .body{padding:1rem}.admin-actions{background:#fff;border:1px solid rgba(15,23,42,.07);border-radius:18px;padding:.75rem;box-shadow:0 8px 28px rgba(15,23,42,.045)}.admin-nav{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.42rem;margin-top:.85rem}.admin-tab{border:1px solid rgba(15,23,42,.08);background:#fff;color:#41526b;padding:.58rem .65rem;border-radius:13px;font-weight:650;font-size:.84rem;text-align:left;display:flex;gap:.45rem;align-items:center;box-shadow:0 6px 18px rgba(15,23,42,.035)}.admin-tab.active{background:var(--cms-brand);border-color:var(--cms-brand);color:#fff;box-shadow:0 14px 30px rgba(13,110,253,.2)}.admin-card{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:18px;padding:1rem;margin-bottom:1rem;box-shadow:0 8px 28px rgba(15,23,42,.045)}.admin-card>h4:first-child,.admin-card>h5:first-child,.admin-card>h6:first-child{margin-top:0}.design-token-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.7rem;margin:.75rem 0 1rem}.design-token-field{display:flex;flex-direction:column;gap:.35rem;min-width:0;margin:0}.design-token-field .form-control-color{display:block;width:100%!important;max-width:none;height:44px;padding:.3rem;border-radius:11px}.design-token-text-grid{display:grid;grid-template-columns:minmax(0,.7fr) minmax(0,1.3fr);gap:.7rem;align-items:start}.design-token-text-grid .form-label{margin-bottom:.8rem}.theme-grid .theme-card p:last-of-type{margin-bottom:.65rem}.theme-card .theme-warnings{margin-bottom:.7rem}.theme-card-actions{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center}.theme-card-actions .btn-cms,.theme-card-actions .btn-ghost{min-height:44px}@media(max-width:480px){.design-token-grid,.design-token-text-grid{grid-template-columns:1fr 1fr}.theme-grid{grid-template-columns:1fr}}.form-control,.form-select{border-radius:12px;border-color:rgba(15,23,42,.12)}.form-control:focus,.form-select:focus{border-color:rgba(13,110,253,.5);box-shadow:0 0 0 .2rem rgba(13,110,253,.08)}.form-control-color{width:100%}.form-label .small{color:#40516b}.editor-card{border:1px solid rgba(15,23,42,.1);border-radius:18px;background:#fff;overflow:hidden;box-shadow:0 10px 34px rgba(15,23,42,.05)}.editor-toolbar{display:flex;gap:.3rem;flex-wrap:wrap;align-items:center;padding:.55rem;border-bottom:1px solid rgba(15,23,42,.08);background:linear-gradient(180deg,#fbfdff,#f6f8fb)}.editor-toolbar button,.editor-toolbar select{min-height:34px;border:1px solid rgba(15,23,42,.09);background:#fff;border-radius:9px;padding:.38rem .55rem;font-weight:650;color:#334155}.editor-toolbar button:hover{border-color:rgba(13,110,253,.3);color:var(--cms-brand);background:#f6faff}.editor-toolbar .editor-sep{width:1px;height:24px;background:rgba(15,23,42,.1);margin:0 .1rem}.rich-editor{min-height:190px;padding:1rem 1.1rem;outline:0;line-height:1.65}.rich-editor.large{min-height:330px}.rich-editor:focus{background:#fbfdff}.html-editor{border:0;border-top:1px solid rgba(15,23,42,.08);border-radius:0;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.82rem;min-height:170px}.commerce-integration-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}.integration-card{border:1px solid rgba(15,23,42,.08);border-radius:15px;padding:.75rem;background:#fff;display:flex;align-items:center;justify-content:space-between;gap:.65rem}.integration-card strong{display:block}.integration-card .form-check{margin:0}.mailer-log{font:12px/1.5 ui-monospace,SFMono-Regular,Menlo,monospace;background:#0f172a;color:#e2e8f0;border-radius:14px;padding:.8rem;max-height:320px;overflow:auto}.brand-preview{display:flex;gap:.8rem;align-items:center;padding:.85rem;border:1px solid rgba(15,23,42,.08);border-radius:16px;background:#fff}.favicon-admin-field{width:88px!important;min-height:88px!important;aspect-ratio:1!important;border-radius:20px!important;margin:0!important}.favicon-placeholder{font-weight:800;font-size:1.2rem}.admin-panel .body{padding:1.05rem}.admin-card{transition:.15s ease}.admin-card:hover{border-color:rgba(13,110,253,.14)}@media(max-width:620px){.commerce-integration-grid{grid-template-columns:1fr}.admin-nav{grid-template-columns:repeat(3,minmax(0,1fr))}}.cookie-box{position:fixed;left:50%;bottom:16px;transform:translateX(-50%);z-index:90;background:#fff;border-radius:18px;box-shadow:0 20px 70px rgba(15,23,42,.18);border:1px solid rgba(15,23,42,.08);padding:1rem;max-width:720px;width:calc(100% - 32px)}
    .search-result{display:block;padding:.85rem;border-bottom:1px solid rgba(15,23,42,.08)}.share-row{display:flex;gap:.5rem;flex-wrap:wrap;margin-top:1rem}.share-row a{border:1px solid rgba(15,23,42,.1);border-radius:999px;padding:.5rem .8rem;font-weight:650}.load-more-wrap{text-align:center;margin-top:2rem}.product-card-img{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:var(--cms-radius);background:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2))}.product-card{position:relative;display:flex;flex-direction:column;min-height:100%}.product-card>p.muted{flex:1 1 auto}.product-card>.product-actions{width:100%;margin-top:auto;display:grid;grid-template-columns:1fr;gap:.45rem;padding-top:.35rem}.product-card>.product-actions>.btn-ghost{width:100%;min-height:40px;padding:.56rem .8rem;font-size:.9rem;text-align:center}.product-card>.product-actions>.product-actions{width:100%;margin:0;display:grid;grid-template-columns:repeat(auto-fit,minmax(108px,1fr));gap:.45rem}.product-card>.product-actions>.product-actions>*{width:100%;min-height:40px;padding:.56rem .72rem;font-size:.88rem;white-space:normal;text-align:center}.product-card>.product-actions>.product-actions:empty{display:none}.product-price{font-size:1.1rem;color:var(--cms-brand);font-weight:760}.old-price{text-decoration:line-through;color:var(--cms-muted);font-size:.92rem}.discount-badge{display:inline-flex;align-items:center;border-radius:999px;padding:.25rem .55rem;background:rgba(32,201,151,.12);color:#047857;font-size:.78rem;font-weight:720}.product-actions{display:flex;gap:.55rem;flex-wrap:wrap;margin-top:1rem}.product-gallery{display:grid;grid-template-columns:repeat(4,1fr);gap:.55rem;margin:.75rem 0 1rem}.product-gallery img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:14px;cursor:pointer}.basket-btn{position:fixed;right:18px;bottom:82px;z-index:60}.basket-count{display:inline-flex;min-width:22px;height:22px;align-items:center;justify-content:center;border-radius:999px;background:#fff;color:var(--cms-brand);font-size:.78rem;font-weight:800}.basket-line{display:grid;grid-template-columns:1fr auto;gap:.75rem;align-items:center;border-bottom:1px solid rgba(15,23,42,.08);padding:.75rem 0}.quote-floating{bottom:82px}.cart-floating{bottom:140px}.catalog-toolbar{display:grid;grid-template-columns:2fr repeat(4,minmax(150px,1fr));gap:.75rem;margin:-.5rem 0 1.25rem;background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:22px;padding:1rem;box-shadow:0 14px 38px rgba(15,23,42,.05)}.catalog-search{min-width:220px}.product-badges{display:flex;flex-wrap:wrap;gap:.35rem;margin:.35rem 0 .75rem}.product-badges span{display:inline-flex;border:1px solid rgba(15,23,42,.08);border-radius:999px;padding:.15rem .45rem;font-size:.72rem;color:#64748b;background:#f8fafc}.product-badges span.colour-badge{align-items:center;gap:.25rem;padding:.2rem;background:#fff}.colour-swatch{width:22px;height:22px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(15,23,42,.16);box-shadow:inset 0 0 0 2px rgba(255,255,255,.65)}.colour-swatch.is-white,.colour-swatch.is-light{border-color:rgba(15,23,42,.22);box-shadow:inset 0 0 0 2px rgba(248,250,252,.95),0 1px 3px rgba(15,23,42,.08)}.colour-filter{display:flex;align-items:center;gap:.45rem;flex-wrap:wrap;min-height:42px;padding:.35rem .45rem;border:1px solid rgba(15,23,42,.1);border-radius:16px;background:#fff}.colour-filter-label{font-size:.72rem;font-weight:750;color:#64748b;margin-right:.15rem}.colour-chip{width:34px;height:34px;border:1px solid rgba(15,23,42,.12);border-radius:999px;background:#fff;display:inline-flex;align-items:center;justify-content:center;padding:0;cursor:pointer;transition:.16s ease}.colour-chip:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(15,23,42,.08)}.colour-chip.active{border-color:var(--cms-brand);box-shadow:0 0 0 4px rgba(13,110,253,.12)}.colour-chip-all{width:auto;padding:0 .7rem;font-size:.8rem;font-weight:720;color:#42526b}.variation-swatches{display:flex;flex-wrap:wrap;gap:.55rem;margin-top:.35rem}.variation-choice{display:inline-flex;align-items:center;gap:.5rem;border:1px solid rgba(15,23,42,.1);border-radius:999px;background:#fff;padding:.45rem .7rem;cursor:pointer;transition:.16s ease}.variation-choice:hover{transform:translateY(-1px);box-shadow:0 10px 24px rgba(15,23,42,.08)}.variation-choice.active{border-color:var(--cms-brand);box-shadow:0 0 0 4px rgba(13,110,253,.1)}.variation-choice small{color:#64748b;font-weight:650}.variation-box,.qty-box{margin:.75rem 0}.qty-box{max-width:150px}.cart-total{font-size:1.1rem;font-weight:780;color:#172033;text-align:right;margin-top:1rem}.payment-note{background:#f8fafc;border:1px solid rgba(15,23,42,.08);border-radius:16px;padding:.85rem;margin-top:1rem}.payment-note p{margin:.35rem 0 0;color:var(--cms-muted)}@media(max-width:1199px){.catalog-toolbar{grid-template-columns:1fr 1fr}.catalog-search{grid-column:1/-1}}@media(max-width:767px){.catalog-toolbar{grid-template-columns:1fr}.cart-floating{bottom:136px}.quote-floating{bottom:82px}}
    .cms-image-editor{position:relative;display:block;overflow:hidden;background:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2));isolation:isolate}.cms-image-editor>img{display:block;width:100%;height:100%;object-fit:cover}.cms-image-editor .cms-image-fallback{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.35rem}.cms-image-editor.is-admin{outline:1.5px dashed rgba(13,110,253,.38);outline-offset:3px}.cms-image-overlay{position:absolute;inset:0;z-index:3;border:0;border-radius:inherit;background:rgba(15,23,42,.66);color:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.4rem;padding:1rem;font:inherit;font-weight:750;cursor:pointer;opacity:0;transition:.18s ease;backdrop-filter:blur(2px)}.cms-image-overlay i{font-size:1.5rem}.cms-image-editor.is-admin:hover .cms-image-overlay,.cms-image-editor.is-dragging .cms-image-overlay,.cms-image-editor.is-empty .cms-image-overlay,.cms-image-editor.is-busy .cms-image-overlay{opacity:1}.cms-image-editor.is-dragging{outline:3px solid var(--cms-accent);outline-offset:3px}.cms-image-editor.is-busy .cms-image-overlay{cursor:progress}.cms-image-remove,.cms-image-free,.cms-image-icon-choice,.cms-image-view{position:absolute;top:.55rem;z-index:5;width:34px;height:34px;border:0;border-radius:999px;background:rgba(255,255,255,.94);box-shadow:0 8px 24px rgba(15,23,42,.2);display:grid;place-items:center;cursor:pointer;opacity:0;transition:.18s ease}.cms-image-remove{right:.55rem;color:#be123c}.cms-image-view{right:2.9rem;color:#0f5fb8}.cms-image-free{left:.55rem;color:var(--cms-brand)}.cms-image-icon-choice{left:2.9rem;color:#7c3aed}.cms-image-editor.is-admin:hover .cms-image-remove,.cms-image-editor.is-admin:hover .cms-image-free,.cms-image-editor.is-admin:hover .cms-image-icon-choice,.cms-image-editor.is-admin:hover .cms-image-view{opacity:1}.cms-image-editor.cms-image-icon{width:52px;height:52px;min-height:52px;border-radius:16px;flex:0 0 52px}.cms-image-editor.cms-image-icon .cms-image-overlay{padding:.25rem}.cms-image-editor.cms-image-icon .cms-image-overlay span{display:none}.cms-image-editor.cms-image-icon .cms-image-overlay i{font-size:1rem}.cms-image-editor.cms-image-avatar{width:56px;height:56px;min-height:56px;border-radius:50%;flex:0 0 56px;background:var(--cms-soft)}.cms-image-editor.cms-image-avatar .cms-image-overlay{padding:.25rem}.cms-image-editor.cms-image-avatar .cms-image-overlay span{display:none}.cms-image-editor.hero-img,.cms-image-editor.feature-panel{width:100%}.cms-image-editor.product-card-img,.cms-image-editor.gallery-img{padding:0}.cms-image-editor.gallery-img{display:block}.cms-image-editor.admin-image-field{width:100%;min-height:190px;aspect-ratio:16/9;border-radius:18px;margin-bottom:.75rem}.cms-upload-dropzone{position:relative;border:1.5px dashed rgba(13,110,253,.36);border-radius:20px;background:linear-gradient(180deg,#f8fbff,#fff);padding:2rem 1rem;text-align:center;cursor:pointer;transition:.18s ease}.cms-upload-dropzone:hover,.cms-upload-dropzone.dragging{border-color:var(--cms-brand);background:#f3f8ff;box-shadow:0 12px 34px rgba(13,110,253,.09)}.cms-upload-dropzone i{display:block;font-size:2rem;color:var(--cms-brand);margin-bottom:.45rem}.cms-upload-dropzone input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}.cms-upload-result img{width:100%;max-height:220px;object-fit:cover;border-radius:16px;margin-top:.75rem}

    .catalog-toolbar{display:flex!important;align-items:stretch;gap:.7rem;flex-wrap:wrap;margin:-.5rem 0 1.25rem;background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:24px;padding:.85rem;box-shadow:0 18px 48px rgba(15,23,42,.06)}
    .catalog-toolbar{display:flex!important;flex-direction:column;align-items:stretch;gap:.7rem}.catalog-search-wrap{width:100%;flex:0 0 100%;min-width:0}.catalog-filter-row{display:flex;align-items:center;gap:.55rem;flex-wrap:wrap}.catalog-filter-row .filter-trigger{min-width:122px;height:42px;padding:.45rem .7rem}.catalog-filter-row .catalog-select{height:42px;min-width:142px;flex:0 0 158px}.catalog-filter-row .filter-reset{height:42px;padding:.45rem .75rem}.catalog-filter-row .filter-dropdown{flex:0 0 auto}@media(max-width:767px){.catalog-filter-row{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.catalog-filter-row .filter-dropdown,.catalog-filter-row .catalog-select,.catalog-filter-row .filter-trigger,.catalog-filter-row .filter-reset{width:100%;min-width:0;max-width:none;flex:auto}.catalog-filter-row .filter-reset{grid-column:1/-1}}
    .cms-crop-modal{max-width:760px}.cms-crop-preview{width:100%;max-height:54vh;overflow:hidden;border-radius:18px;background:#e2e8f0;position:relative;margin-bottom:1rem}.cms-crop-preview img{display:block;width:100%;height:100%;object-fit:cover;transform-origin:center;transition:transform .12s ease,object-position .12s ease}.cms-crop-controls{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem}.cms-crop-controls label{font-size:.76rem;font-weight:760;color:#64748b}.cms-crop-controls input[type=range]{width:100%}.cms-crop-aspects{display:flex;gap:.4rem;flex-wrap:wrap}.cms-crop-aspects button{border:1px solid rgba(15,23,42,.1);background:#fff;border-radius:999px;padding:.42rem .68rem;font-size:.8rem;font-weight:700}.cms-crop-aspects button.active{background:var(--cms-brand);border-color:var(--cms-brand);color:#fff}@media(max-width:576px){.cms-crop-controls{grid-template-columns:1fr}}
    .theme-quick-btn{position:fixed;left:18px;bottom:126px;z-index:61}.theme-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem;align-items:start}.theme-card{border:1px solid rgba(15,23,42,.09);border-radius:16px;background:#fff;padding:.8rem;align-self:start;min-width:0}.theme-card>.d-flex:first-child{align-items:flex-start}.theme-card .theme-score{align-self:flex-start;flex:0 0 auto}.theme-library-modal{max-width:980px}.theme-library-toolbar{display:flex;gap:.65rem;flex-wrap:wrap}.theme-library-toolbar .btn-cms,.theme-library-toolbar .btn-ghost{display:inline-flex;align-items:center;gap:.45rem}.theme-modal-note{border:1px solid rgba(13,110,253,.13);background:#f7fbff;border-radius:14px;padding:.7rem .8rem}.theme-import-choice{display:grid;grid-template-columns:1fr 1fr;gap:.65rem}.theme-import-choice .btn-cms,.theme-import-choice .btn-ghost{justify-content:center}@media(max-width:620px){.theme-import-choice{grid-template-columns:1fr}}.theme-card.active{border-color:var(--cms-brand);box-shadow:0 0 0 3px rgba(13,110,253,.08)}.theme-swatch-row{display:flex;gap:.25rem;margin:.5rem 0}.theme-swatch-row span{width:22px;height:22px;border-radius:7px;border:1px solid rgba(15,23,42,.08)}.theme-score{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:.28rem .55rem;background:#eef6ff;color:#1d4ed8;font-size:.72rem;font-weight:750;line-height:1;white-space:nowrap;min-height:26px}.theme-warnings{font-size:.78rem;color:#64748b;padding-left:1rem}.theme-import-zone{border:1.5px dashed rgba(13,110,253,.28);border-radius:18px;padding:1rem;background:#f8fbff}.headless-code{font:12px/1.5 ui-monospace,SFMono-Regular,Menlo,monospace;white-space:pre-wrap;background:#0f172a;color:#e2e8f0;border-radius:14px;padding:.8rem;overflow:auto}@media(max-width:520px){.theme-grid{grid-template-columns:1fr}}
    .cms-inline-toolbar{position:fixed;z-index:140;display:flex;align-items:center;gap:.25rem;padding:.36rem;border:1px solid rgba(15,23,42,.12);border-radius:12px;background:rgba(255,255,255,.98);box-shadow:0 14px 42px rgba(15,23,42,.18);backdrop-filter:blur(10px)}.cms-inline-toolbar button{width:31px;height:31px;border:0;border-radius:8px;background:#f8fafc;color:#334155;display:grid;place-items:center}.cms-inline-toolbar button:hover,.cms-inline-toolbar button.active{background:#eaf3ff;color:var(--cms-brand)}.cms-inline-toolbar select{height:31px;max-width:108px;border:1px solid rgba(15,23,42,.1);border-radius:8px;background:#fff;padding:0 .4rem;font-size:.78rem;color:#334155}.cms-inline-toolbar input[type=color]{width:31px;height:31px;border:1px solid rgba(15,23,42,.1);border-radius:8px;padding:3px;background:#fff}.cms-inline-toolbar .toolbar-divider{width:1px;height:22px;background:rgba(15,23,42,.1);margin:0 .1rem}
.catalog-search-wrap{position:relative;flex:0 0 auto;min-width:0;width:100%}.catalog-search-wrap i{position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#94a3b8;z-index:2;pointer-events:none}.catalog-search{height:48px;border-radius:16px;padding-left:2.6rem}.catalog-select{height:48px;border-radius:16px;min-width:150px;flex:0 0 170px}.filter-dropdown{position:relative;flex:0 0 auto}.filter-trigger{height:48px;border:1px solid rgba(15,23,42,.1);background:#fff;border-radius:16px;padding:.55rem .8rem;display:inline-flex;align-items:center;gap:.55rem;min-width:136px;justify-content:space-between;color:#26364d;font-weight:680;box-shadow:0 8px 20px rgba(15,23,42,.035);transition:.16s ease}.filter-trigger:hover,.filter-trigger.open,.filter-trigger.active{border-color:rgba(13,110,253,.35);box-shadow:0 0 0 4px rgba(13,110,253,.08)}.filter-trigger strong{display:inline-flex;align-items:center;gap:.3rem;max-width:92px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--cms-brand);font-size:.82rem}.filter-panel{position:absolute;z-index:80;top:calc(100% + 8px);left:0;width:min(92vw,340px);background:#fff;border:1px solid rgba(15,23,42,.1);border-radius:20px;padding:.85rem;box-shadow:0 24px 72px rgba(15,23,42,.16)}.filter-panel-title{font-weight:780;color:#172033}.filter-clear{border:0;background:transparent;color:var(--cms-brand);font-weight:720;font-size:.82rem}.filter-option-list{display:grid;gap:.35rem}.filter-option{width:100%;border:1px solid transparent;background:#f8fafc;border-radius:14px;padding:.62rem .7rem;text-align:left;font-weight:650;color:#43536b;display:flex;align-items:center;justify-content:space-between;gap:.75rem}.filter-option:hover{background:#eef6ff}.filter-option.active{border-color:rgba(13,110,253,.32);background:#eef6ff;color:var(--cms-brand)}.swatch-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.45rem}.swatch-option{border:1px solid rgba(15,23,42,.08);background:#fff;border-radius:16px;padding:.55rem .35rem;display:flex;flex-direction:column;align-items:center;gap:.35rem;min-height:72px;color:#42526b;font-weight:650;font-size:.75rem;transition:.16s ease}.swatch-option:hover{transform:translateY(-1px);box-shadow:0 10px 24px rgba(15,23,42,.08)}.swatch-option.active{border-color:var(--cms-brand);box-shadow:0 0 0 4px rgba(13,110,253,.1)}.swatch-option.is-all{justify-content:center}.swatch-option .colour-swatch{width:28px;height:28px}.price-filter-fields{display:grid;grid-template-columns:1fr 1fr;gap:.55rem;margin-bottom:.75rem}.price-filter-fields label{font-size:.72rem;font-weight:760;color:#64748b}.price-filter-fields input{border-radius:14px}.range-stack{position:relative;height:36px;margin:.25rem .2rem .75rem}.range-stack:before{content:"";position:absolute;left:0;right:0;top:16px;height:6px;border-radius:999px;background:#e2e8f0}.range-stack input[type=range]{position:absolute;left:0;right:0;top:0;width:100%;height:36px;margin:0;background:transparent;pointer-events:none;-webkit-appearance:none;appearance:none}.range-stack input[type=range]::-webkit-slider-thumb{pointer-events:auto;-webkit-appearance:none;appearance:none;width:20px;height:20px;border-radius:999px;background:#fff;border:5px solid var(--cms-brand);box-shadow:0 4px 14px rgba(13,110,253,.28);cursor:pointer}.range-stack input[type=range]::-moz-range-thumb{pointer-events:auto;width:20px;height:20px;border-radius:999px;background:#fff;border:5px solid var(--cms-brand);box-shadow:0 4px 14px rgba(13,110,253,.28);cursor:pointer}.active-filter-chips{display:flex;flex-wrap:wrap;gap:.4rem;margin:-.25rem 0 1rem}.active-filter-chip{display:inline-flex;align-items:center;gap:.35rem;border:1px solid rgba(13,110,253,.18);background:#f3f8ff;color:#1d4ed8;border-radius:999px;padding:.35rem .6rem;font-size:.82rem;font-weight:650}.active-filter-chip button{border:0;background:transparent;color:inherit;padding:0;line-height:1}.filter-reset{height:48px;border-radius:16px;padding:.55rem .85rem}.price-label-row{display:flex;align-items:center;justify-content:space-between;color:#64748b;font-size:.82rem}.filter-colour-inline{display:inline-flex;align-items:center;gap:.35rem}@media(max-width:1199px){.catalog-toolbar{align-items:flex-start}.catalog-search-wrap{flex-basis:auto}.filter-trigger,.catalog-select{min-width:148px}}@media(max-width:767px){.catalog-toolbar{display:grid!important;grid-template-columns:1fr}.catalog-search-wrap,.filter-dropdown,.catalog-select,.filter-trigger,.filter-reset{width:100%;min-width:0}.filter-panel{position:static;width:100%;margin-top:.5rem;box-shadow:0 12px 34px rgba(15,23,42,.1)}.swatch-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}


    .cms-live-icon{width:1.15em;height:1.15em;display:inline-block;background:currentColor;vertical-align:-.16em;-webkit-mask-repeat:no-repeat;mask-repeat:no-repeat;-webkit-mask-position:center;mask-position:center;-webkit-mask-size:contain;mask-size:contain}.cms-icon .cms-live-icon{width:1.45rem;height:1.45rem}
    .menu-editor-card{border:1px solid rgba(15,23,42,.08);border-radius:16px;padding:.85rem;background:#fbfdff;margin-bottom:.75rem}.submenu-editor{margin:.7rem 0 0 1.2rem;padding:.7rem;border-left:2px solid rgba(13,110,253,.18);background:#fff;border-radius:0 14px 14px 0}.submenu-editor-row{display:grid;grid-template-columns:minmax(120px,.8fr) minmax(150px,1.25fr) 115px 40px;gap:.45rem;align-items:center;margin-bottom:.45rem}.menu-row{display:grid;grid-template-columns:minmax(120px,.8fr) minmax(150px,1.25fr) 115px;gap:.5rem;align-items:center}.mini-remove{width:38px;height:38px;border-radius:10px;border:1px solid rgba(15,23,42,.1);background:#fff}
    .section-sort-row{display:grid;grid-template-columns:34px minmax(100px,1fr) 150px 90px;gap:.55rem;align-items:center;border:1px solid rgba(15,23,42,.08);border-radius:14px;padding:.55rem .65rem;margin-bottom:.5rem;background:#fff}.section-sort-row.dragging{opacity:.45}.drag-handle{cursor:grab;color:#94a3b8;font-size:1.1rem}.section-pattern-2>.section{background:linear-gradient(145deg,var(--cms-soft),#fff 60%)}.section-pattern-2>.section .section-title{text-align:left;margin-left:0;max-width:840px}.section-pattern-2>.section .cms-card{border-radius:calc(var(--cms-radius) + 8px);box-shadow:0 22px 62px rgba(15,23,42,.085)}.section-pattern-2>#features .row.align-items-center,.section-pattern-2>#about .row.align-items-center{flex-direction:row-reverse}.section-pattern-2>#stats .stat-card{text-align:left}.section-pattern-2>#cta .cta{text-align:left}
    .media-source-toolbar{display:grid;grid-template-columns:150px 1fr auto;gap:.55rem;align-items:center}.topic-chips{display:flex;gap:.4rem;flex-wrap:wrap;margin:.7rem 0}.topic-chip{border:1px solid rgba(15,23,42,.1);background:#fff;border-radius:999px;padding:.42rem .7rem;font-size:.8rem}.free-media-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.7rem}.free-media-card{border:1px solid rgba(15,23,42,.08);border-radius:15px;overflow:hidden;background:#fff;display:flex;flex-direction:column}.free-media-card img{width:100%;aspect-ratio:4/3;object-fit:cover;background:#eef2f7}.free-media-card .meta{padding:.65rem;display:flex;flex-direction:column;gap:.4rem;flex:1}.free-media-card .title{font-weight:700;font-size:.84rem;line-height:1.25}.free-media-card .credit{font-size:.72rem;color:#64748b}.free-media-card button{margin-top:auto}
    .icon-picker-grid{display:grid;grid-template-columns:repeat(8,minmax(0,1fr));gap:.45rem}.icon-pick{border:1px solid rgba(15,23,42,.09);background:#fff;border-radius:12px;min-height:70px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.35rem;padding:.4rem;color:#334155}.icon-pick:hover{border-color:rgba(13,110,253,.35);color:var(--cms-brand);background:#f8fbff}.icon-pick .cms-live-icon{font-size:1.55rem}.icon-pick small{font-size:.58rem;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.ai-output{min-height:190px;font-family:inherit}.ai-provider-links{display:flex;gap:.5rem;flex-wrap:wrap}.plan-pill{display:inline-flex;padding:.28rem .55rem;border-radius:999px;background:rgba(13,110,253,.09);color:var(--cms-brand);font-size:.72rem;font-weight:750;text-transform:uppercase}
    @media(max-width:720px){.submenu-editor-row,.menu-row{grid-template-columns:1fr}.submenu-editor-row .mini-remove{width:100%}.section-sort-row{grid-template-columns:30px 1fr}.section-sort-row select,.section-sort-row .form-check{grid-column:2}.free-media-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.media-source-toolbar{grid-template-columns:1fr}.icon-picker-grid{grid-template-columns:repeat(5,minmax(0,1fr))}}

    @media(max-width:767px){.section{padding:72px 0}.hero{min-height:auto;padding-top:70px}.admin-nav{grid-template-columns:repeat(2,1fr)}.hero-img{min-height:280px}.hero-video-steps{grid-template-columns:repeat(2,minmax(0,1fr))}.hero-video-step{font-size:.72rem}}
    /* v0.11.19: fixed lightbox CSS placement, global image viewer and post-render hash scrolling */
    .lang-flag-btn{display:inline-grid!important;place-items:center!important;padding:0!important;line-height:1!important}.lang-flag-btn .flag-img{margin:auto!important;transform:none!important}
    .reveal-section .section-title,.reveal-section .row>[class*='col-'],.reveal-section .timeline-step,.reveal-section .content-list-row,.reveal-section .logo-item,.reveal-section .booking-service,.reveal-section .admin-domain-card,.reveal-section .admin-shot-card,.reveal-section .admin-cap-table-wrap,.reveal-section .faq-item,.reveal-section .demo-intake-shell,.reveal-section .cta{animation:none!important;transition:opacity .68s ease,transform .68s cubic-bezier(.2,.75,.25,1)}
    .reveal-section.reveal-left:not(.is-visible) .section-title,.reveal-section.reveal-left:not(.is-visible) .row>[class*='col-'],.reveal-section.reveal-left:not(.is-visible) .timeline-step,.reveal-section.reveal-left:not(.is-visible) .content-list-row,.reveal-section.reveal-left:not(.is-visible) .logo-item,.reveal-section.reveal-left:not(.is-visible) .booking-service,.reveal-section.reveal-left:not(.is-visible) .admin-domain-card,.reveal-section.reveal-left:not(.is-visible) .admin-shot-card,.reveal-section.reveal-left:not(.is-visible) .admin-cap-table-wrap,.reveal-section.reveal-left:not(.is-visible) .faq-item,.reveal-section.reveal-left:not(.is-visible) .demo-intake-shell,.reveal-section.reveal-left:not(.is-visible) .cta{opacity:0;transform:translate3d(-30px,0,0)}
    .reveal-section.reveal-right:not(.is-visible) .section-title,.reveal-section.reveal-right:not(.is-visible) .row>[class*='col-'],.reveal-section.reveal-right:not(.is-visible) .timeline-step,.reveal-section.reveal-right:not(.is-visible) .content-list-row,.reveal-section.reveal-right:not(.is-visible) .logo-item,.reveal-section.reveal-right:not(.is-visible) .booking-service,.reveal-section.reveal-right:not(.is-visible) .admin-domain-card,.reveal-section.reveal-right:not(.is-visible) .admin-shot-card,.reveal-section.reveal-right:not(.is-visible) .admin-cap-table-wrap,.reveal-section.reveal-right:not(.is-visible) .faq-item,.reveal-section.reveal-right:not(.is-visible) .demo-intake-shell,.reveal-section.reveal-right:not(.is-visible) .cta{opacity:0;transform:translate3d(30px,0,0)}
    .reveal-section.is-visible .row>[class*='col-']:nth-child(2),.reveal-section.is-visible .admin-shot-card:nth-child(2),.reveal-section.is-visible .booking-service:nth-child(2){transition-delay:.06s}.reveal-section.is-visible .row>[class*='col-']:nth-child(3),.reveal-section.is-visible .admin-shot-card:nth-child(3),.reveal-section.is-visible .booking-service:nth-child(3){transition-delay:.12s}.reveal-section.is-visible .row>[class*='col-']:nth-child(4),.reveal-section.is-visible .booking-service:nth-child(4){transition-delay:.18s}
    @media(prefers-reduced-motion:reduce){.reveal-section .section-title,.reveal-section .row>[class*='col-'],.reveal-section .timeline-step,.reveal-section .content-list-row,.reveal-section .logo-item,.reveal-section .booking-service,.reveal-section .admin-domain-card,.reveal-section .admin-shot-card,.reveal-section .admin-cap-table-wrap,.reveal-section .faq-item,.reveal-section .demo-intake-shell,.reveal-section .cta{opacity:1!important;transform:none!important;transition:none!important}}

    .admin-showcase-shell{display:grid;gap:1.5rem}.admin-shot-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.admin-shot-card{border:1px solid rgba(15,23,42,.08);border-radius:20px;background:#fff;padding:1.15rem;display:flex;align-items:flex-start;gap:.9rem;min-height:142px;box-shadow:0 12px 32px rgba(15,23,42,.04)}.admin-shot-icon{width:54px;height:54px;flex:0 0 54px;border-radius:16px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(13,110,253,.12),rgba(102,16,242,.12));color:var(--cms-brand);font-size:1.35rem}.admin-shot-icon i{line-height:1}.admin-shot-copy{min-width:0;padding-top:.1rem}.admin-shot-copy h3{margin-bottom:.3rem}@media(max-width:991px){.admin-shot-grid{grid-template-columns:1fr}}.admin-domain-card{border:1px solid rgba(13,110,253,.14);background:linear-gradient(135deg,rgba(13,110,253,.055),rgba(102,16,242,.045));border-radius:calc(var(--cms-radius) + 4px);padding:1.35rem}.admin-domain-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;margin-top:1rem}.admin-domain-option{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:18px;padding:1rem;display:flex;gap:.85rem;align-items:flex-start}.admin-domain-option .cms-icon{width:42px;height:42px;min-width:42px;margin:0}.domain-example{display:inline-flex;margin-top:.45rem;padding:.35rem .6rem;border-radius:999px;background:#0f172a;color:#fff;font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.78rem}.admin-super-showcase{display:grid;grid-template-columns:minmax(0,.92fr) minmax(360px,1.08fr);gap:1.75rem;align-items:center;margin:1.5rem 0 2rem}.admin-super-image{display:block;border:1px solid rgba(15,23,42,.08);border-radius:24px;overflow:hidden;background:#fff;box-shadow:0 22px 60px rgba(15,23,42,.12);aspect-ratio:4/3;cursor:zoom-in}.admin-super-image img{display:block;width:100%;height:100%;object-fit:cover;object-position:top center}.admin-super-copy{padding:.35rem 0}.admin-super-feature-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.65rem}.admin-super-feature{display:flex;gap:.65rem;align-items:flex-start;padding:.75rem;border:1px solid rgba(15,23,42,.08);border-radius:16px;background:#fff}.admin-super-feature .cms-icon{width:38px;height:38px;min-height:38px;flex:0 0 38px}.content-list-icon{width:72px;height:72px;min-height:72px;flex:0 0 72px}.admin-super-feature strong,.admin-super-feature span{display:block}.admin-super-feature span.small{margin-top:.15rem}@media(max-width:991px){.admin-super-showcase{grid-template-columns:1fr}.admin-super-image{aspect-ratio:16/10}.admin-super-feature-grid{grid-template-columns:1fr}}
    .admin-shot-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.admin-shot-card{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:20px;overflow:hidden;box-shadow:0 14px 40px rgba(15,23,42,.06);height:100%}.admin-shot-card a{display:block;background:#eef3f8;cursor:zoom-in}.admin-shot-card img{width:100%;aspect-ratio:16/10;object-fit:cover;object-position:top center;display:block;transition:transform .2s ease}.admin-shot-card:hover img{transform:scale(1.012)}.admin-shot-copy{padding:1rem}.admin-cap-table-wrap{background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:20px;overflow:hidden;box-shadow:0 14px 40px rgba(15,23,42,.05)}.admin-cap-table{width:100%;margin:0;border-collapse:collapse}.admin-cap-table th,.admin-cap-table td{padding:.9rem 1rem;border-bottom:1px solid rgba(15,23,42,.07);vertical-align:middle}.admin-cap-table th{background:#f8fafc;font-size:.78rem;text-transform:uppercase;letter-spacing:.045em;color:#64748b}.admin-cap-table tr:last-child td{border-bottom:0}.permission-yes,.permission-no{display:inline-flex;align-items:center;gap:.38rem;font-weight:750;font-size:.86rem}.permission-yes{color:#087443}.permission-no{color:#8a3340}.admin-ease{color:var(--cms-muted);font-size:.9rem}@media(max-width:991px){.admin-shot-grid{grid-template-columns:1fr 1fr}.admin-domain-options{grid-template-columns:1fr}}@media(max-width:767px){.admin-shot-grid{grid-template-columns:1fr}.admin-cap-table th:nth-child(2),.admin-cap-table td:nth-child(2),.admin-cap-table th:nth-child(3),.admin-cap-table td:nth-child(3){white-space:nowrap}.admin-cap-table th,.admin-cap-table td{padding:.75rem .72rem;font-size:.82rem}}
    .pricing-grid{max-width:940px;margin-inline:auto}.plan-badge{display:inline-flex;align-items:center;border-radius:999px;padding:.3rem .62rem;background:rgba(13,110,253,.09);color:var(--cms-brand);font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.04em}.pricing-dual-price{display:flex;align-items:baseline;gap:.55rem;flex-wrap:wrap}.pricing-dual-price .secondary{font-size:1.05rem;color:var(--cms-muted);font-weight:700}.pricing-card .plan-feature{display:flex;gap:.5rem;align-items:flex-start}.pricing-card .plan-feature i{color:var(--cms-brand);margin-top:.15rem}
    .image-card-media{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:calc(var(--cms-radius) - 4px);background:var(--cms-soft)}.content-list-row{display:grid;grid-template-columns:minmax(180px,.8fr) minmax(0,1.2fr);gap:2rem;align-items:center;padding:1.1rem 0}.content-list-row:nth-child(even){grid-template-columns:minmax(0,1.2fr) minmax(180px,.8fr)}.content-list-row:nth-child(even) .content-list-media{order:2}.content-list-media{width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:var(--cms-radius);background:var(--cms-soft)}.logo-strip{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:1rem;align-items:center}.logo-item{min-height:96px;border:1px solid rgba(15,23,42,.07);border-radius:18px;background:#fff;display:grid;place-items:center;padding:1rem;text-align:center;font-weight:750;color:#64748b}.logo-item img{max-width:130px;max-height:54px;object-fit:contain;filter:grayscale(.2)}
    .booking-shell{max-width:980px;margin:auto}.booking-service-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:.7rem}.booking-service{border:1px solid rgba(15,23,42,.1);border-radius:18px;background:#fff;padding:1rem;text-align:left}.booking-service.active{border-color:var(--cms-brand);box-shadow:0 0 0 4px rgba(13,110,253,.08)}.booking-slots{display:flex;flex-wrap:wrap;gap:.5rem}.booking-slot{border:1px solid rgba(15,23,42,.1);background:#fff;border-radius:999px;padding:.55rem .8rem;font-weight:700}.booking-slot.active{background:var(--cms-brand);border-color:var(--cms-brand);color:#fff}.booking-confirm{border:1px solid rgba(32,201,151,.25);background:rgba(32,201,151,.08);border-radius:18px;padding:1rem}
    .chatbot-launcher{position:fixed;bottom:18px;z-index:75;width:56px;height:56px;border-radius:50%;border:0;background:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2));color:#fff;box-shadow:0 18px 45px rgba(13,110,253,.3);font-size:1.2rem;display:inline-flex;align-items:center;justify-content:center}.chatbot-launcher.left{left:18px}.chatbot-launcher.right{right:82px}.chatbot-launcher.whatsapp{background:#25d366}.chatbot-panel{position:fixed;bottom:86px;z-index:95;width:min(390px,calc(100vw - 36px));height:min(590px,72vh);background:#fff;border:1px solid rgba(15,23,42,.1);border-radius:24px;box-shadow:0 28px 90px rgba(15,23,42,.22);display:flex;flex-direction:column;overflow:hidden}.chatbot-panel.left{left:18px}.chatbot-panel.right{right:18px}.chatbot-head{padding:1rem;border-bottom:1px solid rgba(15,23,42,.08);display:flex;align-items:center;justify-content:space-between}.chatbot-messages{flex:1;overflow:auto;padding:1rem;display:flex;flex-direction:column;gap:.7rem;background:#f8fafc}.chat-msg{max-width:86%;border-radius:17px;padding:.7rem .85rem;white-space:pre-wrap;font-size:.92rem}.chat-msg.bot{align-self:flex-start;background:#fff;border:1px solid rgba(15,23,42,.07)}.chat-msg.user{align-self:flex-end;background:var(--cms-brand);color:#fff}.chatbot-questions{padding:.6rem .75rem 0;display:flex;gap:.4rem;flex-wrap:wrap;background:#fff;border-top:1px solid rgba(15,23,42,.06)}.chatbot-questions button{border:1px solid rgba(15,23,42,.1);background:#fff;border-radius:999px;padding:.35rem .55rem;font-size:.76rem;color:#40516b}.chatbot-compose{padding:.75rem;border-top:1px solid rgba(15,23,42,.08);display:flex;gap:.5rem}.chatbot-compose input{border-radius:999px}.chatbot-compose button{width:44px;height:44px;flex:0 0 44px}
    .cart-options{border:1px solid rgba(15,23,42,.08);border-radius:18px;padding:.9rem;background:#f8fafc}.cart-summary-row{display:flex;justify-content:space-between;gap:1rem;padding:.2rem 0}.cart-summary-row.total{font-size:1.08rem;font-weight:800;border-top:1px solid rgba(15,23,42,.1);margin-top:.45rem;padding-top:.65rem}
    .section-library-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem}.section-preview-card{border:1px solid rgba(15,23,42,.08);border-radius:16px;background:#fff;padding:.7rem;display:flex;flex-direction:column;gap:.5rem}.section-preview-mini{height:72px;border-radius:12px;background:linear-gradient(145deg,#f2f6fb,#fff);border:1px solid rgba(15,23,42,.05);padding:.6rem;display:grid;gap:.35rem}.section-preview-mini span{display:block;height:7px;border-radius:999px;background:#d7e2ef}.section-preview-mini span:nth-child(1){width:58%;background:rgba(13,110,253,.24)}.section-preview-mini span:nth-child(2){width:90%}.section-preview-mini span:nth-child(3){width:72%}.section-preview-card .meta{display:flex;align-items:center;justify-content:space-between;gap:.5rem}.section-preview-card strong{text-transform:capitalize;font-size:.86rem}
    .plan-admin-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.plan-admin-card{border:1px solid rgba(15,23,42,.09);border-radius:18px;background:#fff;padding:1rem}.plan-admin-card.active{border-color:var(--cms-brand);box-shadow:0 0 0 4px rgba(13,110,253,.08)}.technical-box{border:1px dashed rgba(15,23,42,.18);border-radius:16px;background:#fbfdff;padding:1rem}.booking-admin-row,.simple-list-row{border:1px solid rgba(15,23,42,.08);border-radius:14px;padding:.75rem;margin-bottom:.6rem;background:#fff}.ai-inline-btn{border-left:1px solid rgba(15,23,42,.1)!important;margin-left:.1rem!important;width:38px!important}.ai-inline-modal textarea{min-height:150px}
    @media(max-width:991px){.footer-layout,.footer-layout.no-apps{grid-template-columns:1fr 1fr}.footer-apps{grid-column:1 / -1}.footer-socials{justify-content:flex-start}}@media(max-width:767px){.footer-layout,.footer-layout.no-apps{grid-template-columns:1fr}.footer-apps{grid-column:auto}.footer-app-badges{display:grid;grid-template-columns:1fr 1fr}.footer-app-badge{min-width:0;width:100%}.footer-socials{justify-content:flex-start}.content-list-row,.content-list-row:nth-child(even){grid-template-columns:1fr}.content-list-row:nth-child(even) .content-list-media{order:0}.section-library-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.plan-admin-grid{grid-template-columns:1fr}.chatbot-panel{bottom:82px;width:calc(100vw - 24px)}.chatbot-panel.left{left:12px}.chatbot-panel.right{right:12px}.chatbot-launcher.left{left:12px}.chatbot-launcher.right{right:72px}}
    .demo-intake-shell{max-width:980px;margin-inline:auto;background:#fff;border:1px solid rgba(15,23,42,.08);border-radius:28px;padding:clamp(1rem,3vw,2rem);box-shadow:0 24px 70px rgba(15,23,42,.08)}.demo-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:.5rem;margin-bottom:1.5rem}.demo-step{display:flex;align-items:center;gap:.5rem;color:#64748b;min-width:0}.demo-step>span{width:30px;height:30px;flex:0 0 30px;border-radius:50%;display:grid;place-items:center;background:#eef2f7;font-weight:800}.demo-step small{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.demo-step.active{color:var(--cms-brand)}.demo-step.active>span,.demo-step.done>span{background:var(--cms-brand);color:#fff}.demo-form-body{min-height:310px}.demo-plan-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.demo-plan-choice{text-align:left;border:1px solid rgba(15,23,42,.1);border-radius:18px;background:#fff;padding:1rem;display:flex;flex-direction:column;gap:.2rem}.demo-plan-choice.active{border-color:var(--cms-brand);box-shadow:0 0 0 4px rgba(13,110,253,.08)}.demo-plan-choice span,.demo-plan-choice small{color:#64748b}.demo-form-actions{display:flex;justify-content:space-between;align-items:center;gap:.75rem;margin-top:1.5rem;padding-top:1rem;border-top:1px solid rgba(15,23,42,.08)}.demo-review-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem}.demo-review-grid>div{border:1px solid rgba(15,23,42,.08);border-radius:14px;padding:.75rem;background:#f8fafc}.demo-review-grid small{display:block;color:#64748b;margin-bottom:.2rem}.demo-review-grid strong{display:block;overflow-wrap:anywhere}.demo-success{text-align:center;padding:3rem}.demo-success>i{font-size:3rem;color:#10b981}.demo-success .plan-pill{margin-top:.75rem}@media(max-width:767px){.demo-steps{grid-template-columns:repeat(4,30px);justify-content:space-between}.demo-step small{display:none}.demo-plan-grid,.demo-review-grid{grid-template-columns:1fr}.demo-form-body{min-height:0}}

    /* v0.11.20: clearer icon editing + premium Super Admin capability cards */
    .cms-external-icon{display:inline-grid;place-items:center;width:1.25em;height:1.25em;line-height:1;vertical-align:-.12em}.cms-live-icon-img{display:block;width:100%;height:100%;object-fit:contain}.cms-external-icon-fallback{display:none;font-size:.95em}
    /* v0.11.21: normalize glyph centering inside all CMS icon tiles */.cms-icon{line-height:1}.cms-icon>i,.cms-icon>.cms-external-icon{display:flex!important;align-items:center;justify-content:center;margin:0!important;padding:0!important;line-height:1!important}.cms-icon>.bi::before{display:block!important;margin:0!important;padding:0!important;line-height:1!important;vertical-align:0!important}.cms-icon>.cms-external-icon{width:1.28em;height:1.28em}.cms-icon>.cms-external-icon .cms-live-icon-img{margin:auto}
    .cms-image-editor.has-icon-choice.is-empty{background:#fff;border:1px solid rgba(15,23,42,.09);box-shadow:0 8px 24px rgba(15,23,42,.055)}.cms-image-editor.has-icon-choice.is-empty .cms-image-fallback{position:absolute;inset:0;background:linear-gradient(145deg,#f8fbff,#eef5ff);color:var(--cms-brand);font-size:1.45rem}.cms-image-editor.has-icon-choice.is-empty .cms-image-overlay{opacity:0!important;pointer-events:none}.cms-icon-select-primary{position:absolute;inset:0;z-index:4;border:0;border-radius:inherit;background:transparent;cursor:pointer}.cms-icon-select-primary:hover{box-shadow:inset 0 0 0 2px rgba(13,110,253,.38)}.cms-icon-select-badge{position:absolute;right:4px;bottom:4px;width:18px;height:18px;border-radius:999px;background:var(--cms-brand);color:#fff;display:grid;place-items:center;font-size:.62rem;box-shadow:0 3px 10px rgba(13,110,253,.28)}.cms-image-editor.cms-image-icon.has-icon-choice.is-empty .cms-image-free{display:none}.cms-image-editor.cms-image-icon.has-icon-choice.is-empty:hover .cms-image-fallback{background:linear-gradient(145deg,#f3f8ff,#e8f1ff)}
    .icon-current-preview{display:flex;align-items:center;gap:.8rem;padding:.8rem;border:1px solid rgba(13,110,253,.14);background:#f8fbff;border-radius:16px}.icon-current-glyph{width:48px;height:48px;border-radius:14px;background:#fff;color:var(--cms-brand);display:grid;place-items:center;font-size:1.5rem;box-shadow:0 8px 22px rgba(15,23,42,.07)}.icon-current-preview code{display:block;margin-top:.12rem;font-size:.72rem}.icon-picker-grid{grid-template-columns:repeat(6,minmax(0,1fr));gap:.6rem}.icon-pick{position:relative;min-height:88px;padding:.65rem;border-radius:15px}.icon-pick-glyph{font-size:1.8rem;line-height:1;color:var(--cms-brand)}.icon-pick.is-selected{border-color:var(--cms-brand);background:#eef5ff;box-shadow:0 0 0 2px rgba(13,110,253,.08)}.icon-selected-check{position:absolute;right:6px;top:6px;width:20px;height:20px;border-radius:999px;background:var(--cms-brand);color:#fff;display:grid;place-items:center;font-size:.65rem}
    .icon-target-context,.icon-target-empty{display:flex;align-items:center;gap:.8rem;padding:.8rem .9rem;border-radius:16px}.icon-target-context{border:1px solid rgba(13,110,253,.18);background:linear-gradient(135deg,#f7fbff,#eef5ff)}.icon-target-empty{border:1px dashed rgba(100,116,139,.28);background:#f8fafc}.icon-target-context-glyph,.icon-target-empty-glyph{width:44px;height:44px;min-width:44px;border-radius:13px;display:grid;place-items:center;font-size:1.35rem}.icon-target-context-glyph{background:#fff;color:var(--cms-brand);box-shadow:0 7px 18px rgba(15,23,42,.06)}.icon-target-empty-glyph{background:#eef2f7;color:#64748b}.icon-target-advanced{border-top:1px solid rgba(15,23,42,.07);padding-top:.7rem}.icon-target-advanced summary{cursor:pointer;color:#64748b}.icon-pick:disabled{opacity:.45;cursor:not-allowed}
    .admin-super-feature{position:relative;align-items:center;gap:.8rem;padding:.9rem 1rem;border:1px solid rgba(15,23,42,.07);border-radius:18px;background:linear-gradient(180deg,#fff,#fbfdff);box-shadow:0 10px 28px rgba(15,23,42,.045);overflow:hidden}.admin-super-feature:after{content:"";position:absolute;inset:auto 0 0 0;height:2px;background:linear-gradient(90deg,var(--cms-brand),var(--cms-brand2));opacity:.28}.admin-super-feature-icon{position:relative;width:46px;height:46px;min-width:46px;flex:0 0 46px;border-radius:14px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--cms-brand),var(--cms-brand2));color:#fff;font-size:1.18rem;line-height:1;box-shadow:0 10px 24px rgba(13,110,253,.18)}.admin-super-feature-icon>i,.admin-super-feature-icon>.cms-external-icon{position:absolute!important;left:50%!important;top:50%!important;display:flex!important;align-items:center;justify-content:center;width:1.2em;height:1.2em;min-width:1.2em;min-height:1.2em;margin:0!important;padding:0!important;line-height:1!important;vertical-align:middle!important;transform:translate(-50%,-50%)!important}.admin-super-feature-icon>.bi::before{display:block!important;margin:0!important;padding:0!important;line-height:1!important;vertical-align:0!important;transform:none!important}.admin-super-feature-icon .cms-live-icon-img{display:block;width:1.12em;height:1.12em;margin:auto;object-fit:contain}.admin-super-feature strong{font-size:.94rem;line-height:1.2}.admin-super-feature span.small{line-height:1.45;margin-top:.22rem}.admin-super-feature-grid{gap:.8rem}
    @media(max-width:720px){.icon-picker-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.admin-super-feature-icon{width:42px;height:42px;min-width:42px}}

    /* v0.11.19 global image viewer */
    section[id]{scroll-margin-top:88px}
    section img[data-lightbox],.admin-super-image img,.admin-shot-card img,.product-main-lightbox{cursor:zoom-in}
    .cms-image-editor.is-admin img,.no-lightbox img{cursor:default}
    .cms-global-lightbox{position:fixed;inset:0;z-index:9999;background:rgba(3,7,18,.92);display:flex;align-items:center;justify-content:center;padding:clamp(.75rem,3vw,2.5rem);backdrop-filter:blur(9px);-webkit-backdrop-filter:blur(9px)}
    .cms-global-lightbox-frame{position:relative;width:min(1180px,96vw);height:min(88vh,900px);display:flex;align-items:center;justify-content:center;padding:clamp(.7rem,2vw,1.4rem)}
    .cms-global-lightbox-image{display:block;max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;border-radius:18px;box-shadow:0 30px 90px rgba(0,0,0,.48);background:#fff}
    .cms-global-lightbox-close,.cms-global-lightbox-nav{position:absolute;border:0;background:rgba(255,255,255,.97);color:#0f172a;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 10px 32px rgba(0,0,0,.24);z-index:3;line-height:1;padding:0!important;cursor:pointer}
    .cms-global-lightbox-close{top:.25rem;right:.25rem;width:46px;height:46px;border-radius:50%;font-size:1.2rem}
    .cms-global-lightbox-close i,.cms-global-lightbox-nav i{display:block;line-height:1;margin:0}
    .cms-global-lightbox-nav{top:50%;width:46px;height:54px;border-radius:14px;transform:translateY(-50%);font-size:1.15rem}.cms-global-lightbox-prev{left:.25rem}.cms-global-lightbox-next{right:.25rem}
    .cms-global-lightbox-caption{position:absolute;left:50%;bottom:.25rem;transform:translateX(-50%);max-width:min(760px,82vw);padding:.5rem .8rem;border-radius:999px;background:rgba(15,23,42,.92);color:#fff;font-size:.86rem;text-align:center;pointer-events:none}
    .cms-global-lightbox-status{display:flex;align-items:center;justify-content:center;min-width:260px;min-height:140px;padding:1.25rem 1.5rem;border-radius:18px;background:#fff;color:#334155;text-align:center;box-shadow:0 24px 80px rgba(0,0,0,.35)}
    .cms-modal-backdrop .cms-modal [aria-label="Close"],.cms-modal-backdrop .cms-modal > .icon-btn.float-end,.cms-modal-backdrop .cms-modal > .d-flex:first-child > .icon-btn:last-child{width:42px;height:42px;min-width:42px;padding:0!important;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;line-height:1;margin:0}.admin-panel>header .d-flex:first-child>.icon-btn:last-child{width:42px;height:42px;min-width:42px;padding:0!important;display:inline-flex;align-items:center;justify-content:center;border-radius:50%;line-height:1}
    @media(max-width:767px){section[id]{scroll-margin-top:76px}.cms-global-lightbox{padding:.4rem}.cms-global-lightbox-frame{width:98vw;height:88vh;padding:.45rem}.cms-global-lightbox-close{top:.55rem;right:.55rem}.cms-global-lightbox-prev{left:.55rem}.cms-global-lightbox-next{right:.55rem}.cms-global-lightbox-caption{bottom:.55rem;max-width:calc(100% - 7rem);border-radius:12px}}

    <?= $theme['customCss'] ?? '' ?>
  </style>
  <?php foreach (($activeThemeAssets['css'] ?? []) as $themeCss): ?>
  <link rel="stylesheet" href="<?= cms_e($themeCss) ?>" data-lpcms-theme-asset="css">
  <?php endforeach; ?>
  <?php
    $ld = [
      '@context' => 'https://schema.org',
      '@type' => 'WebSite',
      'name' => $data['settings']['siteName'] ?? 'Flat CMS',
      'url' => $base,
      'potentialAction' => ['@type' => 'SearchAction', 'target' => $base . '/?search={search_term_string}', 'query-input' => 'required name=search_term_string']
    ];
    if (($meta['type'] ?? '') === 'article' && !empty($meta['item'])) {
      $ld = ['@context'=>'https://schema.org','@type'=>'BlogPosting','headline'=>$meta['item']['title'] ?? $meta['title'],'description'=>$meta['description'] ?? '','datePublished'=>$meta['item']['date'] ?? date('c'),'image'=>$metaImage ?: null,'mainEntityOfPage'=>$canonical];
    } elseif (($meta['type'] ?? '') === 'product' && !empty($meta['item'])) {
      $productPrice = $meta['item']['price'] ?? 0;
      $productCurrency = $meta['item']['currency'] ?? ($data['products']['currency'] ?? 'EUR');
      $ld = ['@context'=>'https://schema.org','@type'=>'Product','name'=>$meta['item']['title'] ?? $meta['title'],'description'=>$meta['description'] ?? '','sku'=>$meta['item']['sku'] ?? null,'image'=>$metaImage ?: null,'offers'=>['@type'=>'Offer','price'=>(string)$productPrice,'priceCurrency'=>$productCurrency,'availability'=>'https://schema.org/InStock']];
    }
  ?>
  <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body class="<?= !empty($activeThemeAssets['imported']) ? 'lpcms-imported-theme ' : '' ?>lpcms-theme-<?= cms_e(cms_slug($activeThemeId)) ?>">
  <div id="root"></div>
  <script id="__CMS_SEED__" type="application/json"><?= json_encode(cms_public_data($data), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <script id="__CMS_I18N__" type="application/json"><?= json_encode($i18nPack, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <script src="https://cdn.jsdelivr.net/npm/react@18/umd/react.production.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/react-dom@18/umd/react-dom.production.min.js"></script>
  <?php if (!empty($libs['bootstrap'])): ?><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script><?php endif; ?>
  <?php if (!empty($libs['swiper'])): ?><script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script><?php endif; ?>
  <?php if (!empty($libs['aos'])): ?><script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script><?php endif; ?>

  <?php if (!empty($libs['glightbox'])): ?><script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script><?php endif; ?>
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <?php if (!empty($data['settings']['hcaptcha']['enabled'])): ?><script src="https://js.hcaptcha.com/1/api.js" async defer></script><?php endif; ?>
  <?php if (!empty($libs['sassBuilder'])): ?><script src="https://cdn.jsdelivr.net/npm/sass.js@0.11.1/dist/sass.sync.js"></script><?php endif; ?>
  <?php foreach (($activeThemeAssets['js'] ?? []) as $themeJs): ?><script src="<?= cms_e($themeJs) ?>" data-lpcms-theme-asset="js"></script><?php endforeach; ?>
  <script>
  (() => {
    const rh = React.createElement;
    const {useEffect, useMemo, useState, useRef} = React;
    // Let Chromium expose its native PWA install UI. We intentionally do not
    // call preventDefault() on beforeinstallprompt on normal website pages,
    // which avoids the misleading "Banner not shown" console diagnostic.
    window.__lpcmsInstallPrompt=null;
    window.addEventListener('appinstalled',()=>{ window.__lpcmsInstallPrompt=null; window.dispatchEvent(new CustomEvent('lpcms:install-ready')); });
    const rootEl = document.getElementById('root');
    const seed = JSON.parse(document.getElementById('__CMS_SEED__').textContent);
    const I18N = JSON.parse(document.getElementById('__CMS_I18N__')?.textContent||'{"languages":[],"strings":{}}');
    async function cmsResponseJson(response){
      const body=await response.text();
      try{return JSON.parse(body)}catch(err){
        const preview=body.replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim().slice(0,220);
        throw new Error(preview ? `Server returned non-JSON (HTTP ${response.status}): ${preview}` : `Server returned invalid JSON (HTTP ${response.status}).`);
      }
    }
    const CMS_BASE_PATH = <?= json_encode(rtrim(cms_route_prefix(), '/'), JSON_UNESCAPED_SLASHES) ?>;
    const SERVER_LANG = <?= json_encode($currentLang, JSON_UNESCAPED_UNICODE) ?>;
    const DEFAULT_LANG = String(seed.settings?.defaultLang||'en').toLowerCase();
    const LANGUAGE_CODES = (seed.settings?.languages||I18N.languages||[]).map(x=>String(x.code||'').toLowerCase()).filter(Boolean);
    const ADMIN_UI_STRINGS=new Set([" / Assets .htaccess: "," / Assets writable: "," / Root writable: "," / month"," Add menu item"," Add submenu"," Browse free Bootstrap themes"," Download ZIP manually"," Import & rebuild for LPCMS"," Import Bootstrap / ThemeForest ZIP"," Import downloaded ZIP"," Import online"," Remove menu item"," Save JSON"," demo settings? Existing content stays, but commerce mode may change."," times (comma separated)"," total"," uploaded"," · completed"," · reminder 1 sent"," · reminder 2 sent"," · sent ","#contact","#eee","#section","#section or https://...","&page=","&prefix=","&q=",".apiKey",".apiKeySet",".enabled",".items",".zip,application/zip","/new","/old","/old-page","192×192 PNG icon URL","512×512 PNG icon URL","<p>Product description.</p>","<p>Write the post content here.</p>","?api=abandoned-cart-tools","?api=ai-generate","?api=demo-requests","?api=graphql","?api=headless&resource=site\n?api=headless&resource=products\n?api=headless&resource=products&slug=starter-website-package\n?api=headless&resource=settings.seo","?api=icon-search&q=","?api=mailer-tools","?api=maintenance","?api=media-import","?api=media-search&provider=","?api=sync-htaccess","?api=theme-activate","?api=theme-catalog","?api=theme-delete","?api=theme-import","?api=upload","AI Writer","AI chatbot","AI content applied. Save JSON when ready.","AI enabled","AI generation failed.","AI image tools","AI off","AI prompt copied.","AI write / improve / translate","AI writing tools","APC Postal","API key","API key (saved - enter to replace)","Abandoned cart recovery","About body","About image","About text","About title","About: ","Access mode","Account name","Action failed.","Active","Adapter bearer token","Adapter bearer token (saved - enter to replace)","Add blog post","Add editor","Add item","Add product","Add redirect","Add service","Adjust the active theme palette and layout tokens.","Airterra","All headless responses use cms_public_data(), so users, form submissions and hCaptcha secrets stay private. APIs are read-only.","All libraries","Allowed CORS origins (one per line; use * only for fully public use)","Android install URL","App","App name","Apple 180×180 PNG icon URL","Apple Pay","Apple install URL","Apply JSON","Apply selected icon to","Apply to selected content","Approve & build site","Approve this request and load its generated draft into the current website?","Asset cache days","Attribute filters JSON","Australia Post","Auto-sync .htaccess files","Automatic free search uses Openverse first and Wikimedia Commons as a no-key fallback. Enable optional commercial sources and add their API keys; saved keys are masked in the browser.","Available","AxleHire","Background color","Bank name","Base URL","Bearer token (optional)","Bearer token (saved - enter to replace)","Better Trucks","Billing email introduction","Billing email subject","Billing: ","Block direct access to data.json","Blog","Blog and product SEO fields live inside each item in JSON. Pretty routes: /blog/post-slug, /product/product-slug, and /page/page-slug. Fallback query route: ?slug=slug.","BlueOrange","Booking","Booking title","Booking → real-time confirmation","Booking: ","Bootstrap Icons","Bootstrap grid class","Bottom left","Bottom right","Braintree","Browser AVIF quality 1-100","Browser resize max width","Build clean dropdown menus without editing JSON. Each menu and submenu link can open normally or in a new browser tab.","Building…","Business website","Button label","Button position","Button text","Buttons and checkout","Buy button text","CTA text / HTML","Cache","Canada Post","Canonical override","Captured carts","Card text","Card text.","Cart checkout button","Cart recovery","Cart title","Categories JSON","Category slug","Chat / WhatsApp","Chat title","Chat type","Chat: ","Chatbot button label","Chatbot system instruction","Checkout adapter HTTPS URL","Checkout connection","Checkout mode","Checkout now","Choose PHP mail() or direct SMTP. SMTP credentials stay server-side and the saved password is masked after save.","Choose a Bootstrap HTML ZIP first.","Choose a theme below. LPCMS downloads the allowlisted source ZIP itself, analyses Bootstrap compatibility, rebuilds an adapter, installs the theme, and activates it. No local ZIP selection is required.","Choose cover / crop options","Choose one visitor chat option. The launcher stays in a bottom corner and does not overlap the cart or scroll-up controls.","Choose uploads, img, or gallery before importing an image.","Chronopost","Clean old cache","Clear cache","Clear cache after Save JSON","Clear completed","Clear logs","Click or drag & drop PDF","Click or drag & drop image","Client / partner","Client address: ","Client demo requests","Client logo","Client: ","Cloudflare","Colissimo","Compile SCSS to CSS","Confirmation text","Connected AI","Contact text","Contact title","Content","Content generator","Content list","Content text.","Content-Type","Convert images to AVIF before upload in browser","Coop Pank","Copy failed.","Copy prompt","Correos","Could not load theme catalogue","Create a clean, modern website hero image for my business, no text, high quality.","Crop or upload original; LPCMS optimizes the local copy","Current:","Custom API / webhook","Custom CSS","Custom OpenAI-compatible API","Custom SCSS source (stored only unless Sass library is enabled)","Custom domain (optional)","Custom favicon","DHL Express","DPD","Decta","Default","Default currency","Default meta description","Default meta title","Default product action","Delete","Delete failed","Delete this demo request?","Delete this imported theme and its files?","Delivery method","Demo intake & billing","Demo mode loaded. Review it, then Save JSON.","Demo request","Demo requests","Deutsche Post","Direct checkout URL (optional)","Domain","Domain and deployment settings are managed by the super administrator.","Done","Done.","Download JSON","Drop file here","E-commerce integrations","E-commerce website","EPREL Database","ESTO","EU VIES VAT validation","EUR","Editor","Editors can adjust colours below. Theme installation and switching requires super administrator access.","Eksprespasts","Enable ","Enable AI tools","Enable Ajax product search","Enable GraphQL read API","Enable REST-style content API","Enable Sass Builder in Libraries, save, and reload.","Enable Shop mode to run products like a flat-file WooCommerce-style catalogue: product IDs/SKUs, categories, variation filters, discounts, gallery/modal view, cart, quote basket, and direct bank transfer checkout.","Enable anonymous page cache","Enable blog section","Enable booking section","Enable category/variation filters","Enable checkout buttons","Enable cookie banner","Enable direct debit / bank transfer instructions","Enable hCaptcha on forms","Enable headless APIs","Enable mobile app install option","Enable public multi-step demo form","Enable quote basket","Enable quote buttons","Enable saved-cart reminder emails","Enable shop mode","Enable shopping cart","Enable website chat button","Enabled","Enabled profile","Encryption","Endpoint","Endpoint: ?api=abandoned-cart-cron&key=YOUR_SCHEDULER_KEY","Endpoint: ?api=graphql · Schema: ?api=graphql-schema","Enter","Evri","Exactly","Excerpt","Fallback app icon URL","Fallback reply","Favicon","Favicon image URL (optional)","Features","Features image","Features title","FedEx","First email message","First email subject","First reminder (minutes)","Fluent UI","For automatic reminders, call the scheduler URL from server cron. LPCMS also lets you run processing manually.","Force HTTPS redirect","Forms","Free","Free image","Free image search","Free images · automatic","Free online Bootstrap themes","Free · copy prompt","From email","From name","Frontend libraries","Full data.json editor","GBP","Generate key","Generate with connected AI","Generated content","Generated website: ","Generating...","Giropay","Google Analytics","Google Analytics GA4 ID","Google Gemini API","Google Pay","GraphQL playground","HTML","HTML cache TTL seconds","HTTPS payment adapter","HTTPS webhook URL","Heading 2","Heading 3","Headless","Headless API","Hero slide ","Hero subtitle","Hero title","Hosted payment URL","Hosted payment link","IBAN","IBSC Resico","Icon search failed.","Icons","If no favicon is uploaded, LPCMS automatically renders an LP placeholder using the active brand colour.","If the client does not buy a domain, their chosen subdomain is used on dev4.click.","Image","Image URL","Image URL (optional)","Image cards","Image prompt","Image prompt copied.","Implicit TLS / SSL","Import 20 demo products","Import Bootstrap HTML / ThemeForest ZIP","Import failed.","Import image","Import themes","Imported JavaScript remains disabled by default. LPCMS keeps source HTML as non-executable reference material and adapts the theme assets to the CMS layout.","Imported asset policy","Imported image","Importing & rebuilding…","Importing...","Improve this content while keeping it concise and useful.","Initial product count","Inline editor","Input placeholder","Instruction","Invoice prefix","Item ","JSON","JSON error: ","Keywords","Kimi / Moonshot API","Klarna","Klix by Citadele","LHV","LP","LP Express","LP placeholder favicon","LPCMS Admin ","LPCMS design tokens","Label","LaserShip","Learn more","Libraries","Licence / purchase reference","Licence / purchase reference note","Link URL","Live icon libraries","Load GA only after cookie consent","Load demo","Load imported theme CSS","Load imported theme JavaScript (review first)","Load more text","Load the ","Load-more amount","Loading theme catalogue…","Loading…","Logo","Logo image URL (optional)","Logo strip","Logo text","Logout","Lucide","Luminor","MIT","Mailer","Mailer logs","Mailer test completed.","Mailer test failed.","Main image URL","MakeCommerce","Mans Pasts","Mark this request as rejected?","Material Design","Material Symbols","Maximum site context characters","Media","Media library","Media optimization settings","Media search failed.","Media source connections","Menu","Menu label","Meta","Minutes","Mistral API","MobilePay","Model","N26","Name","Navigation menu","New card","New item","New post","New product","New request notification email","New service","New tab","Next","No","No ZIP extraction engine is available. Enable PHP ZipArchive or the standard Phar/PharData extension.","No bookings yet.","No demo requests yet.","No mailer log entries yet.","None","Nordea","Notification email","OG image","Off","Offline message","Old cache cleanup days","Old price","Omniva","On","OnTrac","Online downloader: ","Only super admin can choose the client plan or load a demo. Premium is the business-site mode; Premium Pro enables the e-commerce demo.","Open ChatGPT","Open Copilot","Open Gemini","OpenAI API","Openverse · no key","Order form / bank transfer","Order form submit button","Order webhook","PDF documents up to 20MB","PDF uploaded","PHP ZipArchive is not enabled, so LPCMS will use its PharData ZIP fallback for online theme imports.","PHP ZipArchive is not enabled. LPCMS will import this ZIP with the PharData fallback.","PHP mail()","POST","Packeta","Page ","Paragraph","Parcelforce","Password","Password (saved - enter to replace)","Pattern ","Pattern 1 · Standard","Pattern 2 · Alternate","Pay / Checkout now","PayPal","Payment due days","Payment instructions","Payment instructions / payment link","Payment title","Paysera","Paytraq","Pexels","PharData","Pixabay","Plan: ","Plans & demo","Plans & demo mode","Platform domain","Port","Post","Post title","Poste Italiane","Posts added per click","Posts shown initially","Pre-filled WhatsApp message","Premium","Premium Pro","Premium Pro e-commerce","Premium business","Prepare an image prompt and open a consumer AI image tool in a new tab. Download the generated image there, then drag it into Media above.","Preparing upload...","Pretty SEO URLs enabled","Previous","Price","Pricing title","Pro · connected API","Process","Process reminders now","Processing...","Product","Product ID","Product action","Product title","Products","Products title","Products: ","Provider","Provider, API key and access mode are managed by the super administrator.","Przelewy24","Public API cache seconds","Public guides: /?install=android and /?install=apple. PWA files are generated by index.php as /manifest.webmanifest and /service-worker.js.","Purolator","QWQER","Quote","Quote button text","Quote product","REST examples","Radius","Recent bookings","Redirect www to non-www","Redirects","Reference prefix","Refresh","Refresh logs","Refresh stats","Reject","Remove","Remove submenu","Replace current products with 20 demo products that show all filters?","Reply-To email","Revolut","Robots","Root .htaccess: ","Royal Mail","Run query","SCSS error","SEB","SEO","SEO description","SEO title","SKU","SMTP","SMTP host","SMTP settings","STARTTLS","SWIFT/BIC","Save JSON after changing these settings. Requests themselves are private and visible only to Super Admin.","Scheduler key","Scheduler key (saved - enter to replace)","Search","Search Iconify live collections and assign any open-source icon to feature, service, or process items. Existing Bootstrap icon classes continue to work.","Search free images...","Search free/open media, preview it, then import a local optimized copy with one click.","Search icons...","Search to load icons.","Searching...","Second email message","Second email subject","Second reminder (minutes)","Secrets are masked in the browser after saving. Order submissions are posted as JSON with event order.submitted.","Section images","Section order","Section subtitle","Section title","Select plan","Selected","Send bill again","Send billing email","Send form submissions to email","Send order submissions to HTTPS webhook","Send test","Send test email to","Sending...","Sending…","Sendle","Server","Server config checked.","Server uploads are protected and converted to AVIF when available. If AVIF is unavailable, original JPG/PNG/WebP files are accepted. Existing files are scanned here so you can monitor media weight.","Service","Services","Services title","Services: ","ShipStation","Shippo","Shop / catalogue settings","Short name","Short post excerpt.","Short product text.","Show ","Show Android / Apple install badges in footer","Show load more button","Show products/catalogue section","Siauliu bankas","Site","Site identity","Site name","Smartpost","Source / licence","Spam protection","Start Bootstrap","Stats","Store submissions in data.json","Stripe","Submenu","Submenu item","Submenu label","Subtitle","Suggested visitor questions — one per line","Super","Super admin login: open /?admin, enter password LPCMS@admin, then confirm the 5-character code sent to raivis@63.lv. Editors use the footer email login link and can edit basic content only.","Super admin · AI provider","Super admin · booking integrations","Super admin · categories, attributes and payment","Super admin · chatbot AI","Super admin · full product JSON (variations, attributes, gallery, stock, SEO)","Super admin · scheduler","Swedbank","Switch built-in or imported themes. Theme activation saves immediately and reloads the public design.","Switch theme","Swotzy","Swyft","Sync .htaccess now","System instruction","Tabler","Test connection","Testing...","Text","Text type","Theme ZIP","Theme activated. Reloading design…","Theme activation failed","Theme color","Theme deleted.","Theme import failed","Theme name","Theme switcher","Themes","This PHP container has no usable outbound downloader. Use Download ZIP manually, then import the downloaded file with the local ZIP importer.","Timeout seconds","Title","Tone","Tools & diagnostics","Turn sections on/off from visual previews, then choose the alternate layout. Drag the order below.","UPS","URL or #section","URL: ","USPS","Unisend","Unknown","Unsaved changes","Unsplash","Upload / import target","Upload a theme ZIP you are licensed to use. LPCMS will analyse it and build a switchable adapter.","Upload failed.","Urbo","Use ","Use connected AI when available","Use separate import flows for licensed ZIP files and the built-in free online Bootstrap catalogue.","Use the simple fields above to edit product content, price, image and action. Technical integration settings are managed by the super administrator.","Used in browser tabs and bookmarks.","User","User-provided / verify licence","Username","Users","Variable product settings and direct checkout URLs are available here for super admin. Editors keep the simple product fields and action selector.","Veho","Venipak","Verify TLS certificate","Visual","Visual post body","Visual product description","Visual section library","Visual text editor","Webhook URL","Website chat button","Weekly availability","Welcome message","WhatsApp","WhatsApp number with country code","When no logo image is set, LPCMS shows the built-in LP mark next to your logo text.","Wikimedia Commons · no key","Working...","Working…","Write into","Write new copy, improve or shorten text, translate it, or prepare SEO-friendly content. The sparkle button also appears beside inline text when connected AI is enabled.","X-CMS-CSRF","YYYY-MM-DD","Yes","_blank","_self","a","abandoned","about","about.body","about.image","about.text","about.title","abstract","accent","active","adapter","admin-actions mb-3","admin-card","admin-card technical-box","admin-image-field","admin-image-field mb-2","admin-nav","admin-panel","admin-subtitle","admin-tab","admin-title","ai","ai-inline-btn","airterra","alert alert-info","alert alert-light border mb-0","alert alert-light border mt-3 mb-0","alert alert-warning","alert alert-warning py-2 mb-2","alert mt-3","alert-danger","alert-info","all","apc-postal","api","app","apple-pay","application/json","application/pdf","approve","approved","architecture","aria-label","article","aside","assets/docs","assets/gallery","assets/img","assets/uploads","australia-post","axlehire","better-trucks","bg","bi","bi bi-bag","bi bi-braces","bi bi-calendar-check","bi bi-cart-x","bi bi-chat-dots","bi bi-circle","bi bi-cloud-arrow-down","bi bi-crop","bi bi-diagram-3","bi bi-download","bi bi-envelope","bi bi-eraser","bi bi-file-earmark-pdf","bi bi-file-earmark-zip","bi bi-gem","bi bi-grid","bi bi-grip-vertical","bi bi-hdd-network","bi bi-house","bi bi-image","bi bi-images","bi bi-journal-text","bi bi-layout-text-window","bi bi-link-45deg","bi bi-list-ol","bi bi-list-ul","bi bi-palette","bi bi-people","bi bi-phone","bi bi-plugin","bi bi-plus-lg","bi bi-save","bi bi-search","bi bi-send-check","bi bi-signpost","bi bi-speedometer2","bi bi-stars","bi bi-text-center","bi bi-text-left","bi bi-text-right","bi bi-trash","bi bi-type-bold","bi bi-type-italic","bi bi-type-underline","bi bi-ui-checks-grid","bi bi-x-lg","bill","blockquote","blog","blog.batch","blog.enabled","blog.homeLimit","blog.items","blog.showButton","blueorange","body","bold","booking","booking-admin-row","booking.confirmationText","booking.enabled","booking.services","booking.subtitle","booking.technical.notifyEmail","booking.technical.webhookUrl","booking.title","booking.weeklySlots.","border rounded-4 p-3 mb-2","border rounded-4 p-3 mb-3","br","braintree","brand","brand-preview mb-2","brand2","btn-cms","btn-cms flex-fill","btn-cms flex-shrink-0","btn-cms mb-2","btn-cms me-2","btn-cms mt-2","btn-cms w-100","btn-ghost","btn-ghost text-center","btn-ghost text-decoration-none","btn-ghost w-100","business","button","buttonHref","buttonText","cache","canada-post","cards","category","chatbot","chatbot.buttonText","chatbot.enabled","chatbot.fallback","chatbot.mode","chatbot.placeholder","chatbot.position","chatbot.suggestedQuestions","chatbot.technical.aiEnabled","chatbot.technical.maxContextChars","chatbot.technical.systemPrompt","chatbot.title","chatbot.welcome","chatbot.welcomeMessage","chatbot.whatsapp.buttonText","chatbot.whatsapp.message","chatbot.whatsapp.number","checkbox","checkout","chronopost","cleanup-old-cache","clear-cache","clear-completed","clear-logs","cloudflare","cms-modal small","cms-modal theme-library-modal","cms-modal-backdrop","cms-upload-dropzone","cms-upload-result","code","col-12","col-4","col-5","col-6","col-7","col-8","col-md-1","col-md-3","col-md-5","col-md-6","colissimo","color","commerce-integration-grid","commons","concise","connection","contact","contact.text","contact.title","contain","content","content list","contentList","coop-pank","copy","correos","createLink","credit","cta","cta.text","custom","d-flex align-items-center gap-2 mb-3","d-flex align-items-center justify-content-between gap-2 mb-2","d-flex align-items-start justify-content-between gap-2","d-flex flex-wrap gap-2","d-flex flex-wrap gap-2 mt-2","d-flex gap-2","d-flex gap-2 flex-wrap","d-flex gap-2 flex-wrap mb-3","d-flex gap-2 mt-2 flex-wrap","d-flex justify-content-between align-items-center","d-flex justify-content-between align-items-center mb-2","d-flex justify-content-between align-items-start gap-2","d-flex justify-content-between align-items-start gap-3 mb-2","d-flex justify-content-between align-items-start gap-3 mb-3","d-flex justify-content-between gap-2","d-flex justify-content-between gap-2 mb-2","d-flex justify-content-between mb-2","d-flex justify-content-between mt-3","d-grid gap-2","data.json","date","decta","delete","demo","demoIntake.enabled","design-token-field","design-token-grid","design-token-text-grid","deutsche-post","dhl","dev4.click","dev4.click subdomain","discountLabel","div","docs","document.pdf","dpd","drag-handle","dragging","durationMinutes","ePost Global","editor","editor-card","editor-sep","editor-toolbar","editor@example.com","education","eksprespasts","email","email@example.com","epost-global","eprel","esto","evri","exactly","excerpt","faq","fashion","favicon-admin-field","favicon-placeholder","features","features.image","features.items.0.icon","features.title","fedex","file","fitness","fluent","food","form-check form-switch","form-check form-switch mb-0","form-check form-switch mb-2","form-check-input","form-check-label","form-control","form-control ai-output","form-control form-control-color","form-control html-editor","form-control mb-2","form-control mb-3","form-label w-100 mb-3","form-select","form-select form-select-sm","form-select mb-2","formatBlock","forms","free","free-media-card","free-media-grid","friday","friendly","from","ga","gallery","gallery.items","gemini","giropay","google-pay","h2","h3","h4","h4 mb-1","h5","h5 mb-0","h5 mb-1","h6","hCaptcha is configured only here in Forms; it is no longer a general frontend Library toggle. The secret is never exposed publicly.","hCaptcha is intentionally managed under Forms only.","hCaptcha secret","hCaptcha site key","hcaptcha","header","header.logoImage","header.logoText","header.menu","headless","headless-code","headless-code mb-0","health","hero","hero.subtitle","hero.title","home","hr","href","https://","https://chatgpt.com/","https://copilot.microsoft.com/","https://gemini.google.com/","iDEAL","icon-btn","icon-pick","icon-picker-grid","icons","id","ideal","image","image/*,.avif,.heic,.heif","img","imported","index.php auto-generates root .htaccess and assets/.htaccess. Custom rules outside the FlatCMS marker block are preserved.","input","insertImage","insertOrderedList","insertUnorderedList","integration-card","italic","json","justifyCenter","justifyLeft","justifyRight","kimi","klarna","klix","label","large","lasership","lazy","left","lhv","li","libraries","license","logos","lp-express","lpcms-default","lucide","luminor","mail","mailer","mailer-log","makecommerce","mans-pasts","material-symbols","mb-0","mb-3","mdi","media","media-source-toolbar","menu","menu-editor-card","menu-row","meta","min-w-0","mini-remove","mistral","mobilepay","monday","ms-auto","ms-auto active","mt-2","muted","muted mb-0","n26","name","nature","new-post","new-product","no","none","noopener noreferrer","nordea","number","office","oldPrice","omniva","ontrac","open","openai","openverse","option","order-form","other","p","packeta","parcelforce","password","payment-link","payments","paypal","pays.cz","payscz","paysera","paytraq","pdf","pending","people","pexels","pixabay","plan","plan-admin-card","plan-admin-grid","plan-badge","plan-pill","playful","poste-italiane","pre","pre-wrap","premium","premium-pro","preset","price","pricing","pricing.title","pro","process","products","products.attributeFilters","products.basketEnabled","products.batch","products.buttonText","products.buyButtonText","products.cartButtonText","products.cartEnabled","products.cartTitle","products.categories","products.checkoutButtonText","products.currency","products.defaultPurchaseMode","products.enabled","products.filterEnabled","products.homeLimit","products.items","products.payment.directDebit.accountName","products.payment.directDebit.bankName","products.payment.directDebit.enabled","products.payment.directDebit.iban","products.payment.directDebit.instructions","products.payment.directDebit.referencePrefix","products.payment.directDebit.swift","products.payment.directDebit.title","products.quoteButtonText","products.searchEnabled","products.shopEnabled","products.showBuyButton","products.showQuoteButton","products.subtitle","products.title","professional","przelewy24","purchaseMode","purolator","quote","qwqer","raivis@63.lv","redirects","reject","rejected","removeFormat","resico","revolut","rich-editor","right","role","role-badge","row g-2","row g-2 mb-2","row g-2 small mb-3","royal-mail","sales","saturday","seb","section-library-grid","section-preview-card","section-preview-mini","section-sort-row","select","send-test","sendle","seo","server","service-","services","services.title","settings.abandonedCart","settings.abandonedCart.cronKey","settings.abandonedCart.enabled","settings.abandonedCart.firstDelayMinutes","settings.abandonedCart.firstMessage","settings.abandonedCart.firstSubject","settings.abandonedCart.secondDelayMinutes","settings.abandonedCart.secondMessage","settings.abandonedCart.secondSubject","settings.ai","settings.ai.accessTier","settings.ai.apiKey","settings.ai.enabled","settings.ai.endpoint","settings.ai.model","settings.ai.provider","settings.ai.systemPrompt","settings.analytics.ga4Id","settings.analytics.loadAfterCookieConsent","settings.app.androidInstallUrl","settings.app.appleIcon","settings.app.appleInstallUrl","settings.app.backgroundColor","settings.app.enabled","settings.app.footerBadgesEnabled","settings.app.icon","settings.app.icon192","settings.app.icon512","settings.app.name","settings.app.offlineMessage","settings.app.shortName","settings.app.themeColor","settings.baseUrl","settings.branding.favicon","settings.cache.autoClearOnSave","settings.cache.enabled","settings.cache.htmlTtl","settings.cache.maxAgeDays","settings.commerceIntegrations.checkout.adapterUrl","settings.commerceIntegrations.checkout.bearerToken","settings.commerceIntegrations.checkout.bearerTokenSet","settings.commerceIntegrations.checkout.mode","settings.commerceIntegrations.checkout.paymentLink","settings.commerceIntegrations.providers","settings.commerceIntegrations.webhook.bearerToken","settings.commerceIntegrations.webhook.bearerTokenSet","settings.commerceIntegrations.webhook.enabled","settings.commerceIntegrations.webhook.url","settings.cookies.enabled","settings.demoIntakeAdmin","settings.demoIntakeAdmin.billingMessage","settings.demoIntakeAdmin.billingSubject","settings.demoIntakeAdmin.dueDays","settings.demoIntakeAdmin.invoicePrefix","settings.demoIntakeAdmin.notifyEmail","settings.demoIntakeAdmin.paymentInstructions","settings.forms.emailTo","settings.forms.storeSubmissions","settings.hcaptcha.enabled","settings.hcaptcha.secret","settings.hcaptcha.siteKey","settings.headless.cacheSeconds","settings.headless.corsOrigins","settings.headless.enabled","settings.headless.graphqlEnabled","settings.headless.restEnabled","settings.htaccess.assetCacheDays","settings.htaccess.autoSync","settings.htaccess.forceHttps","settings.htaccess.prettyUrls","settings.htaccess.protectJson","settings.htaccess.removeWww","settings.libraries.","settings.mailer.fromEmail","settings.mailer.fromName","settings.mailer.mode","settings.mailer.replyTo","settings.mailer.smtp.encryption","settings.mailer.smtp.host","settings.mailer.smtp.password","settings.mailer.smtp.passwordSet","settings.mailer.smtp.port","settings.mailer.smtp.timeout","settings.mailer.smtp.username","settings.mailer.smtp.verifyPeer","settings.media.avifQuality","settings.media.browserAvif","settings.media.maxImageWidth","settings.mediaSources","settings.mediaSources.","settings.sectionOrder","settings.sectionPatterns.","settings.seo.canonical","settings.seo.description","settings.seo.image","settings.seo.keywords","settings.seo.robots","settings.seo.title","settings.siteName","settings.sitePlan","settings.sitePlan.customDomain","settings.sitePlan.platformDomain","settings.sitePlan.selected","settings.sitePlan.subdomain","settings.theme.","settings.theme.customCss","settings.theme.customScss","settings.theme.grid","settings.theme.radius","settings.themeManager.active","settings.themeManager.installed","settings.themeManager.loadImportedCss","settings.themeManager.loadImportedJs","settings.users.items","shipping","shippo","shipstation","siauliu","simple","simple-list-row","site","sku","slug","small","small fw-bold","small fw-bold d-block mb-1","small fw-bold mb-1","small fw-bold mb-2","small fw-bold text-uppercase mb-2","small mb-0","small mt-1","small mt-3","small muted","small muted align-self-center","small muted mb-0","small muted mb-2","small muted mb-3","small muted mt-2","small muted mt-3","small muted mt-3 mb-0","smartpost","smtp","softBg","sourceType","span","ssl","stats","status","stripe","strong","submenu-editor","submenu-editor-row","sunday","super","swedbank","swotzy","swyft","tabler","target","technical","technology","testimonials","text","textarea","theme","theme-card","theme-card-actions","theme-grid","theme-grid mt-3","theme-import-choice","theme-import-zone","theme-modal-note small muted mt-3","theme-score","theme-swatch-row","theme-warnings","themeId","themeZip","thursday","title","tls","to","topic-chip","topic-chips","travel","tuesday","ul","underline","unisend","unknown","unsplash","upload","uploads","ups","urbo","users","usps","veho","venipak","vies","wednesday","whatsapp","yes","you@example.com","yourbusiness","zip","{ site { settings { siteName defaultLang } products { title items } } }"]);
    function adminTranslatedText(code,text){
      if(typeof text!=='string'||!text)return text;
      const lead=(text.match(/^\s*/)||[''])[0], trail=(text.match(/\s*$/)||[''])[0];
      const core=text.slice(lead.length,text.length-trail.length||text.length);
      if(!core)return text;
      const exact=I18N.adminStrings?.[code]?.[core];
      if(typeof exact==='string')return lead+exact+trail;
      // Keep technical samples, routes, identifiers and provider URLs byte-for-byte.
      // Human-facing explanations that contain technical text are covered by exact translations above.
      const technicalLiteral=/^(?:https?:\/\/|\?api=|settings\.|products\.|blog\.|header\.|services\.|pricing\.|hero\.|contact\.|footer\.|demoIntake\.|redirects\.)/i.test(core)
        || core.includes('?api=') || core.includes('{ site {') || core.includes('=>');
      if(technicalLiteral)return text;
      const dynamicAdmin=/^(?:User|Product|Service|Item|Blog post|Request|Reminder|Step|Option|Page|Feature|Gallery|Testimonial)\s+\d+\b/i.test(core);
      if(!ADMIN_UI_STRINGS.has(core)&&!ADMIN_UI_STRINGS.has(text)&&!dynamicAdmin)return text;
      const terms=I18N.adminTerms?.[code]||{};
      const caseWord=(dst,src)=>{ dst=String(dst); if(src===src.toUpperCase()&&/[A-Z]/.test(src))return dst.toUpperCase(); if(src[0]&&src[0]===src[0].toUpperCase())return dst.charAt(0).toUpperCase()+dst.slice(1); return dst; };
      // Replace against the original English text in one pass. Sequential replacement could
      // accidentally re-translate fragments inside an already translated Unicode word.
      const entries=Object.entries(terms).sort((a,b)=>b[0].length-a[0].length);
      if(!entries.length)return text;
      const byLower=Object.fromEntries(entries.map(([src,dst])=>[String(src).toLowerCase(),dst]));
      const alt=entries.map(([src])=>String(src).replace(/[.*+?^${}()|[\]\\]/g,'\\$&')).join('|');
      const rx=new RegExp('(^|[^A-Za-z0-9_])('+alt+')(?=$|[^A-Za-z0-9_])','gi');
      const out=core.replace(rx,(m,prefix,matched)=>prefix+caseWord(byLower[String(matched).toLowerCase()]??matched,matched));
      return lead+out+trail;
    }
    function translatedText(lang,text){ if(typeof text!=='string'||!text)return text; const code=String(lang||DEFAULT_LANG).toLowerCase(); if(code===DEFAULT_LANG||code==='en')return text; const direct=I18N.strings?.[code]?.[text]; if(typeof direct==='string')return direct; const trimmed=text.trim(); const adminExact=I18N.adminStrings?.[code]?.[trimmed]; if(typeof adminExact==='string'){ const lead=(text.match(/^\s*/)||[''])[0],trail=(text.match(/\s*$/)||[''])[0]; return lead+adminExact+trail; } return adminTranslatedText(code,text); }
    function localizeHtml(lang,html){ return translatedText(lang,html); }
    function h(type,props,...children){
      const lang=window.__lpcmsLang||SERVER_LANG||DEFAULT_LANG;
      let p=props;
      if(p){ p={...p}; ['placeholder','title','aria-label','alt'].forEach(k=>{if(typeof p[k]==='string')p[k]=translatedText(lang,p[k]);}); if(p.dangerouslySetInnerHTML?.__html){p.dangerouslySetInnerHTML={...p.dangerouslySetInnerHTML,__html:localizeHtml(lang,p.dangerouslySetInnerHTML.__html)};} }
      const preserveLiteral=type==='pre'||type==='code';
      const mapChild=v=>typeof v==='string'?(preserveLiteral?v:translatedText(lang,v)):(Array.isArray(v)?v.map(mapChild):v);
      return rh(type,p,...children.map(mapChild));
    }
    function pathPartsRelative(){ let path=location.pathname; if(CMS_BASE_PATH&&path.startsWith(CMS_BASE_PATH))path=path.slice(CMS_BASE_PATH.length); return path.split('/').filter(Boolean); }
    function explicitLangFromPath(){ const first=String(pathPartsRelative()[0]||'').toLowerCase(); return LANGUAGE_CODES.includes(first)?first:''; }
    function browserLanguage(){ const map={nb:'no',nn:'no',no:'no'}; for(const raw of (navigator.languages||[navigator.language||''])){const code=String(raw||'').toLowerCase().split('-')[0];const c=map[code]||code;if(LANGUAGE_CODES.includes(c))return c;} return DEFAULT_LANG; }
    function languageUrl(code){
      code=String(code||DEFAULT_LANG).toLowerCase();
      if(!LANGUAGE_CODES.includes(code))code=DEFAULT_LANG;
      let parts=pathPartsRelative();
      if(parts.length&&LANGUAGE_CODES.includes(String(parts[0]).toLowerCase()))parts.shift();
      const segments=code===DEFAULT_LANG?parts:[code,...parts];
      let pathname=((CMS_BASE_PATH||'')+'/'+segments.join('/')).replace(/\/+/g,'/')||'/';
      if(!segments.length) pathname=(CMS_BASE_PATH||'')+'/' || '/';
      else if(location.pathname.endsWith('/')&&!pathname.endsWith('/')) pathname+='/';
      return pathname+location.search+location.hash;
    }
    function internalHref(lang,href){ if(typeof href!=='string'||!href||href.startsWith('#')||/^(?:https?:|mailto:|tel:|javascript:)/i.test(href))return href; if(!href.startsWith('/'))return href; const code=String(lang||DEFAULT_LANG).toLowerCase(); const base=CMS_BASE_PATH||''; let rel=href; if(base&&rel.startsWith(base+'/'))rel=rel.slice(base.length); const parts=rel.split('/').filter(Boolean); if(parts.length&&LANGUAGE_CODES.includes(String(parts[0]).toLowerCase()))parts.shift(); const pref=code===DEFAULT_LANG?[]:[code]; let out=((base||'')+'/'+[...pref,...parts].join('/')).replace(/\/+/g,'/')||'/'; if(href.endsWith('/')&&out!=='/'&&!out.endsWith('/'))out+='/'; return out; }

    const LPCMS_DEMO_PRODUCTS = [{"title":"Starter Website Package","slug":"starter-website-package","id":"1001","sku":"STARTER-WEBSITE-PA","type":"variable","category":"website-packages","brand":"LPCMS","tags":["website-packages","digital","blue","xs"],"price":550,"oldPrice":690,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Blue","size":"XS","material":"Digital","license":"Standard"},"variations":[{"id":"starter-website-package-a","sku":"STARTER-WEBSITE-PA-A","attributes":{"size":"XS","colour":"Blue"},"price":550,"oldPrice":690,"stock":5,"image":""},{"id":"starter-website-package-b","sku":"STARTER-WEBSITE-PA-B","attributes":{"size":"S","colour":"Navy"},"price":600,"oldPrice":760,"stock":3,"image":""}],"stock":10,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Starter Website Package is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Starter Website Package","description":"Starter Website Package demo product.","image":""}},{"title":"Business Website Package","slug":"business-website-package","id":"1002","sku":"BUSINESS-WEBSITE-P","type":"variable","category":"website-packages","brand":"LPCMS","tags":["website-packages","service","black","s"],"price":990,"oldPrice":1290,"discountLabel":"-23%","currency":"EUR","image":"https://images.unsplash.com/photo-1556761175-b413da4baf72?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Black","size":"S","material":"Service","license":"Business"},"variations":[{"id":"business-website-package-a","sku":"BUSINESS-WEBSITE-P-A","attributes":{"size":"S","colour":"Black"},"price":990,"oldPrice":1290,"stock":6,"image":""},{"id":"business-website-package-b","sku":"BUSINESS-WEBSITE-P-B","attributes":{"size":"M","colour":"Purple"},"price":1040,"oldPrice":1360,"stock":4,"image":""}],"stock":12,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Business Website Package is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Business Website Package","description":"Business Website Package demo product.","image":""}},{"title":"Custom Quote Project","slug":"custom-quote-project","id":"1003","sku":"CUSTOM-QUOTE-PROJE","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","cotton","white","m"],"price":0,"oldPrice":0,"discountLabel":"Quote only","currency":"EUR","image":"https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"White","size":"M","material":"Cotton","duration":"Project"},"variations":[],"stock":0,"inStock":true,"text":"Quote-only custom product.","body":"<p>Custom Quote Project is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":false,"quoteButton":true,"related":[],"seo":{"title":"Custom Quote Project","description":"Custom Quote Project demo product.","image":""}},{"title":"SEO Audit Service","slug":"seo-audit-service","id":"1004","sku":"SEO-AUDIT-SERVICE","type":"simple","category":"seo-tools","brand":"LPCMS","tags":["seo-tools","leather","navy","l"],"price":180,"oldPrice":240,"discountLabel":"-25%","currency":"EUR","image":"https://images.unsplash.com/photo-1432888622747-4eb9a8f2c287?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Navy","size":"L","material":"Leather","duration":"One-off"},"variations":[],"stock":16,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>SEO Audit Service is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"SEO Audit Service","description":"SEO Audit Service demo product.","image":""}},{"title":"Speed Optimization","slug":"speed-optimization","id":"1005","sku":"SPEED-OPTIMIZATION","type":"simple","category":"seo-tools","brand":"LPCMS","tags":["seo-tools","aluminum","purple","xl"],"price":320,"oldPrice":420,"discountLabel":"-24%","currency":"EUR","image":"https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Purple","size":"XL","material":"Aluminum","duration":"Monthly"},"variations":[],"stock":18,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Speed Optimization is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Speed Optimization","description":"Speed Optimization demo product.","image":""}},{"title":"Monthly Care Plan","slug":"monthly-care-plan","id":"1006","sku":"MONTHLY-CARE-PLAN","type":"variable","category":"subscriptions","brand":"LPCMS","tags":["subscriptions","glass","green","custom"],"price":79,"oldPrice":99,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1519389950473-47ba0277781c?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Green","size":"Custom","material":"Glass","duration":"Project"},"variations":[{"id":"monthly-care-plan-a","sku":"MONTHLY-CARE-PLAN-A","attributes":{"size":"Custom","colour":"Green"},"price":79,"oldPrice":99,"stock":10,"image":""},{"id":"monthly-care-plan-b","sku":"MONTHLY-CARE-PLAN-B","attributes":{"size":"XS","colour":"Orange"},"price":84,"oldPrice":107,"stock":6,"image":""}],"stock":20,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Monthly Care Plan is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Monthly Care Plan","description":"Monthly Care Plan demo product.","image":""}},{"title":"Logo Design Add-on","slug":"logo-design-add-on","id":"1007","sku":"LOGO-DESIGN-ADD-ON","type":"simple","category":"custom-services","brand":"Studio","tags":["custom-services","digital","red","xs"],"price":250,"oldPrice":0,"discountLabel":"","currency":"EUR","image":"https://images.unsplash.com/photo-1561070791-2526d30994b5?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Red","size":"XS","material":"Digital","duration":"One-off"},"variations":[],"stock":22,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Logo Design Add-on is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Logo Design Add-on","description":"Logo Design Add-on demo product.","image":""}},{"title":"Brochure PDF Design","slug":"brochure-pdf-design","id":"1008","sku":"BROCHURE-PDF-DESIG","type":"simple","category":"custom-services","brand":"Studio","tags":["custom-services","service","gold","s"],"price":390,"oldPrice":490,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Gold","size":"S","material":"Service","duration":"Monthly"},"variations":[],"stock":24,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Brochure PDF Design is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Brochure PDF Design","description":"Brochure PDF Design demo product.","image":""}},{"title":"Cotton Promo T-Shirt","slug":"cotton-promo-t-shirt","id":"1009","sku":"COTTON-PROMO-T-SHI","type":"variable","category":"merch","brand":"Garilla","tags":["merch","cotton","orange","m"],"price":24,"oldPrice":34,"discountLabel":"-29%","currency":"EUR","image":"https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Orange","size":"M","material":"Cotton"},"variations":[{"id":"cotton-promo-t-shirt-a","sku":"COTTON-PROMO-T-SHI-A","attributes":{"size":"M","colour":"Orange"},"price":24,"oldPrice":34,"stock":13,"image":""},{"id":"cotton-promo-t-shirt-b","sku":"COTTON-PROMO-T-SHI-B","attributes":{"size":"L","colour":"Silver"},"price":29,"oldPrice":42,"stock":8,"image":""}],"stock":26,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Cotton Promo T-Shirt is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Cotton Promo T-Shirt","description":"Cotton Promo T-Shirt demo product.","image":""}},{"title":"Premium Hoodie","slug":"premium-hoodie","id":"1010","sku":"PREMIUM-HOODIE","type":"variable","category":"merch","brand":"Garilla","tags":["merch","leather","grey","l"],"price":54,"oldPrice":69,"discountLabel":"-22%","currency":"EUR","image":"https://images.unsplash.com/photo-1556821840-3a63f95609a7?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Grey","size":"L","material":"Leather"},"variations":[{"id":"premium-hoodie-a","sku":"PREMIUM-HOODIE-A","attributes":{"size":"L","colour":"Grey"},"price":54,"oldPrice":69,"stock":14,"image":""},{"id":"premium-hoodie-b","sku":"PREMIUM-HOODIE-B","attributes":{"size":"XL","colour":"Pink"},"price":59,"oldPrice":77,"stock":9,"image":""}],"stock":28,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Premium Hoodie is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Premium Hoodie","description":"Premium Hoodie demo product.","image":""}},{"title":"Leather Notebook","slug":"leather-notebook","id":"1011","sku":"LEATHER-NOTEBOOK","type":"simple","category":"merch","brand":"Garilla","tags":["merch","aluminum","brown","xl"],"price":18,"oldPrice":25,"discountLabel":"-28%","currency":"EUR","image":"https://images.unsplash.com/photo-1517842645767-c639042777db?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Brown","size":"XL","material":"Aluminum"},"variations":[],"stock":30,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Leather Notebook is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Leather Notebook","description":"Leather Notebook demo product.","image":""}},{"title":"Aluminium Water Bottle","slug":"aluminium-water-bottle","id":"1012","sku":"ALUMINIUM-WATER-BO","type":"variable","category":"merch","brand":"Garilla","tags":["merch","glass","silver","custom"],"price":15,"oldPrice":22,"discountLabel":"-32%","currency":"EUR","image":"https://images.unsplash.com/photo-1523362628745-0c100150b504?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Silver","size":"Custom","material":"Glass"},"variations":[{"id":"aluminium-water-bottle-a","sku":"ALUMINIUM-WATER-BO-A","attributes":{"size":"Custom","colour":"Silver"},"price":15,"oldPrice":22,"stock":16,"image":""},{"id":"aluminium-water-bottle-b","sku":"ALUMINIUM-WATER-BO-B","attributes":{"size":"XS","colour":"Beige"},"price":20,"oldPrice":30,"stock":10,"image":""}],"stock":32,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Aluminium Water Bottle is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Aluminium Water Bottle","description":"Aluminium Water Bottle demo product.","image":""}},{"title":"Glass Desk Sign","slug":"glass-desk-sign","id":"1013","sku":"GLASS-DESK-SIGN","type":"simple","category":"merch","brand":"Studio","tags":["merch","digital","pink","xs"],"price":48,"oldPrice":0,"discountLabel":"","currency":"EUR","image":"https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Pink","size":"XS","material":"Digital"},"variations":[],"stock":34,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Glass Desk Sign is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Glass Desk Sign","description":"Glass Desk Sign demo product.","image":""}},{"title":"Digital Download Pack","slug":"digital-download-pack","id":"1014","sku":"DIGITAL-DOWNLOAD-P","type":"simple","category":"digital-products","brand":"LPCMS","tags":["digital-products","service","sky","s"],"price":39,"oldPrice":59,"discountLabel":"-34%","currency":"EUR","image":"https://images.unsplash.com/photo-1516321497487-e288fb19713f?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Sky","size":"S","material":"Service","license":"Business"},"variations":[],"stock":36,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Digital Download Pack is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Digital Download Pack","description":"Digital Download Pack demo product.","image":""}},{"title":"Premium Template Bundle","slug":"premium-template-bundle","id":"1015","sku":"PREMIUM-TEMPLATE-B","type":"variable","category":"digital-products","brand":"LPCMS","tags":["digital-products","cotton","beige","m"],"price":89,"oldPrice":129,"discountLabel":"-31%","currency":"EUR","image":"https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Beige","size":"M","material":"Cotton","license":"Enterprise"},"variations":[{"id":"premium-template-bundle-a","sku":"PREMIUM-TEMPLATE-B-A","attributes":{"size":"M","colour":"Beige"},"price":89,"oldPrice":129,"stock":19,"image":""},{"id":"premium-template-bundle-b","sku":"PREMIUM-TEMPLATE-B-B","attributes":{"size":"L","colour":"Lime"},"price":94,"oldPrice":199,"stock":12,"image":""}],"stock":38,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Premium Template Bundle is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Premium Template Bundle","description":"Premium Template Bundle demo product.","image":""}},{"title":"Photo Gallery Setup","slug":"photo-gallery-setup","id":"1016","sku":"PHOTO-GALLERY-SETU","type":"simple","category":"custom-services","brand":"Studio","tags":["custom-services","leather","rose","l"],"price":160,"oldPrice":0,"discountLabel":"","currency":"EUR","image":"https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Rose","size":"L","material":"Leather","duration":"One-off"},"variations":[],"stock":40,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Photo Gallery Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Photo Gallery Setup","description":"Photo Gallery Setup demo product.","image":""}},{"title":"Contact Form Setup","slug":"contact-form-setup","id":"1017","sku":"CONTACT-FORM-SETUP","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","aluminum","yellow","xl"],"price":120,"oldPrice":150,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1551836022-d5d88e9218df?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Yellow","size":"XL","material":"Aluminum","duration":"Monthly"},"variations":[],"stock":42,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Contact Form Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Contact Form Setup","description":"Contact Form Setup demo product.","image":""}},{"title":"Multilingual Setup","slug":"multilingual-setup","id":"1018","sku":"MULTILINGUAL-SETUP","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","glass","lime","custom"],"price":280,"oldPrice":350,"discountLabel":"-20%","currency":"EUR","image":"https://images.unsplash.com/photo-1521791136064-7986c2920216?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Lime","size":"Custom","material":"Glass","duration":"Project"},"variations":[],"stock":44,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Multilingual Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Multilingual Setup","description":"Multilingual Setup demo product.","image":""}},{"title":"PWA Install Setup","slug":"pwa-install-setup","id":"1019","sku":"PWA-INSTALL-SETUP","type":"simple","category":"custom-services","brand":"LPCMS","tags":["custom-services","digital","clear","xs"],"price":210,"oldPrice":260,"discountLabel":"-19%","currency":"EUR","image":"https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Clear","size":"XS","material":"Digital","duration":"One-off"},"variations":[],"stock":46,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>PWA Install Setup is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"PWA Install Setup","description":"PWA Install Setup demo product.","image":""}},{"title":"Enterprise Custom Build","slug":"enterprise-custom-build","id":"1020","sku":"ENTERPRISE-CUSTOM-","type":"variable","category":"website-packages","brand":"LPCMS","tags":["website-packages","service","cream","s"],"price":2500,"oldPrice":0,"discountLabel":"Popular","currency":"EUR","image":"https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=900&q=80","gallery":[],"attributes":{"colour":"Cream","size":"S","material":"Service","license":"Custom"},"variations":[{"id":"enterprise-custom-build-a","sku":"ENTERPRISE-CUSTOM--A","attributes":{"size":"S","colour":"Cream"},"price":2500,"oldPrice":0,"stock":24,"image":""},{"id":"enterprise-custom-build-b","sku":"ENTERPRISE-CUSTOM--B","attributes":{"size":"M","colour":"White"},"price":2550,"oldPrice":0,"stock":16,"image":""}],"stock":48,"inStock":true,"text":"Demo product showing catalogue filters, modal details and order/quote flow.","body":"<p>Enterprise Custom Build is a demo product for testing LPCMS product search, category filters, attribute dropdowns, price range, modal view, cart and quote basket.</p>","features":["Ajax filter demo","Modal product details","Cart / quote support"],"buyUrl":"#contact","buyButton":true,"quoteButton":true,"related":[],"seo":{"title":"Enterprise Custom Build","description":"Enterprise Custom Build demo product.","image":""}}];
    const initialBoot = { ok:true, admin:false, email:'', csrf:'', route:null, server:null, lang:SERVER_LANG, data: seed };

    function cls(...xs){ return xs.filter(Boolean).join(' '); }
    function get(obj, path){ return String(path).split('.').reduce((o,k)=> o && o[k] !== undefined ? o[k] : undefined, obj); }
    function set(obj, path, val){ const keys=String(path).split('.'); const copy=Array.isArray(obj)?[...obj]:{...obj}; let cur=copy; keys.forEach((k,i)=>{ if(i===keys.length-1){cur[k]=val;return;} cur[k]=Array.isArray(cur[k])?[...cur[k]]:{...cur[k]}; cur=cur[k]; }); return copy; }
    const LOCALIZED_CONTENT_ROOTS=new Set(['header','hero','about','stats','features','services','process','gallery','testimonials','cards','contentList','team','growth','industries','logos','products','booking','adminShowcase','pricing','demoIntake','blog','faq','contact','cta','footer','chatbot','pages']);
    const LOCALIZED_CONTENT_LEAVES=new Set(['title','subtitle','text','body','label','role','badge','buttonText','excerpt','question','answer','description','caption','heading','subheading','placeholder','confirmationText','welcome','welcomeMessage','fallback','message','instructions','address','hours','cartTitle','buyButtonText','quoteButtonText','cartButtonText','checkoutButtonText','discountLabel','suggestedQuestions','copy','period','formButtonText','thankYou','acceptText','declineText','eyebrow','alt','name','logoText','price','secondaryPrice','features','shortName','offlineMessage','domainTitle','domainText','tableTitle','tableSubtitle','ease','preheader','superAdminTitle','superAdminText','noteTitle','noteText','footerText']);
    function isLocalizableContentPath(path){
      const parts=String(path||'').split('.').filter(Boolean); if(!parts.length||parts.includes('i18n'))return false;
      const leaf=parts[parts.length-1];
      const specialSettings=parts.length>=3&&parts[0]==='settings'&&((parts[1]==='seo'&&['title','description','keywords'].includes(leaf))||(parts[1]==='cookies'&&['message','acceptText','declineText'].includes(leaf))||(parts[1]==='app'&&['name','shortName','description','offlineMessage'].includes(leaf)));
      if(!specialSettings&&!LOCALIZED_CONTENT_ROOTS.has(parts[0]))return false;
      if(/^\d+$/.test(leaf))return parts.length>1;
      return LOCALIZED_CONTENT_LEAVES.has(leaf);
    }
    function localizedDataPath(path,lang=(window.__lpcmsLang||SERVER_LANG||DEFAULT_LANG)){
      const code=String(lang||DEFAULT_LANG).toLowerCase(); const raw=String(path||'');
      if(code===DEFAULT_LANG||!isLocalizableContentPath(raw))return raw;
      const parts=raw.split('.'); const leaf=parts.pop();
      if(/^\d+$/.test(leaf)&&parts.length){ const prop=parts.pop(); return [...parts,'i18n',code,prop,leaf].join('.'); }
      return [...parts,'i18n',code,leaf].join('.');
    }
    function localizedDataValue(data,path,lang=(window.__lpcmsLang||SERVER_LANG||DEFAULT_LANG)){
      const code=String(lang||DEFAULT_LANG).toLowerCase(); const raw=String(path||''); const base=get(data,raw);
      if(code===DEFAULT_LANG||!isLocalizableContentPath(raw))return base;
      const lp=localizedDataPath(raw,code); const local=get(data,lp);
      if(local!==undefined&&local!==null)return local;
      if(Array.isArray(base))return base.map(v=>typeof v==='string'?translatedText(code,v):v);
      return typeof base==='string'?translatedText(code,base):base;
    }
    function absolute(url){ if(!url) return location.href; try{return new URL(url, location.href).href}catch(e){return location.href} }
    function mediaUrl(value){
      if(typeof value!=='string')return value;
      let v=value.trim(); if(!v)return v;
      if(v.startsWith('./assets/'))v=v.slice(2);
      if(v.startsWith('/assets/'))return (((CMS_BASE_PATH||'')+v).replace(/\/+/g,'/'));
      if(v.startsWith('assets/'))return (((CMS_BASE_PATH||'')+'/'+v).replace(/\/+/g,'/'));
      return v;
    }
    function slug(s){ return String(s||'item').toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''); }
    function pretty(group,item,lang=(window.__lpcmsLang||DEFAULT_LANG)){ const s=slug(item?.slug||item?.title||'item'); const root=CMS_BASE_PATH||''; const lp=lang===DEFAULT_LANG?'':'/'+lang; if(group==='blog') return root+lp+'/blog/'+s; if(group==='products') return root+lp+'/product/'+s; if(group==='pages') return root+lp+'/page/'+s; return root+lp+'/?slug='+encodeURIComponent(s); }
    function icon(name, fallback){
      const ref=String(name||fallback||'bi bi-circle');
      if(ref.includes(':')){
        const [prefix,...rest]=ref.split(':'); const n=rest.join(':');
        const url='https://api.iconify.design/'+encodeURIComponent(prefix)+'/'+encodeURIComponent(n)+'.svg';
        return h('span',{className:'cms-external-icon','aria-hidden':'true'},
          h('img',{className:'cms-live-icon-img',src:url,alt:'',loading:'lazy',decoding:'async',onError:e=>{e.currentTarget.style.display='none';const fb=e.currentTarget.nextElementSibling;if(fb)fb.style.display='inline-block'}}),
          h('i',{className:'bi bi-stars cms-external-icon-fallback'})
        );
      }
      return h('i',{className:ref});
    }

    async function prepareImageForUpload(file){
      const filename=String(file?.name||'');
      const mime=String(file?.type||'').toLowerCase();
      const imageLike=mime.startsWith('image/')||/\.(?:jpe?g|png|gif|webp|avif|heic|heif)$/i.test(filename);
      if(!file || !imageLike) throw new Error('Please choose a JPG, PNG, GIF, WebP, HEIC, or AVIF image.');
      if((window.__LPCMS_MEDIA_SETTINGS||{}).browserAvif===false) return file;
      if(mime==='image/avif'||/\.avif$/i.test(filename)) return file;
      let cleanup=()=>{};
      try{
        let source, width, height;
        if(window.createImageBitmap){
          source=await createImageBitmap(file,{imageOrientation:'from-image'});
          width=source.width; height=source.height;
          cleanup=()=>source.close?.();
        }else{
          const url=URL.createObjectURL(file);
          cleanup=()=>URL.revokeObjectURL(url);
          source=await new Promise((resolve,reject)=>{ const img=new Image(); img.onload=()=>resolve(img); img.onerror=()=>reject(new Error('Image could not be read.')); img.src=url; });
          width=source.naturalWidth; height=source.naturalHeight;
        }
        const mediaSettings=(window.__LPCMS_MEDIA_SETTINGS||{}); const maxW=Number(mediaSettings.boxWidth||mediaSettings.maxImageWidth||1920); const maxH=Number(mediaSettings.boxHeight||maxW); const ratio=Math.min(1,maxW/Math.max(1,width),maxH/Math.max(1,height));
        const canvas=document.createElement('canvas');
        canvas.width=Math.max(1,Math.round(width*ratio));
        canvas.height=Math.max(1,Math.round(height*ratio));
        const ctx=canvas.getContext('2d',{alpha:true});
        if(!ctx) throw new Error('Image processing is unavailable in this browser.');
        ctx.drawImage(source,0,0,canvas.width,canvas.height);
        const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/avif',Math.max(.1,Math.min(1,Number((window.__LPCMS_MEDIA_SETTINGS||{}).avifQuality||72)/100))));
        if(blob && blob.type==='image/avif'){
          const base=filename.replace(/\.[^.]+$/,'')||'image';
          return new File([blob],base+'.avif',{type:'image/avif',lastModified:Date.now()});
        }
      }catch(err){
        // The server has GD/Imagick/ffmpeg fallbacks when browser AVIF encoding is unavailable.
      }finally{
        cleanup();
      }
      return file;
    }

    async function uploadCmsImage(api,file,target='img'){
      const prepared=await prepareImageForUpload(file);
      const fd=new FormData();
      fd.append('file',prepared,prepared.name||file.name||'image');
      fd.append('target',target);
      const ms=(window.__LPCMS_MEDIA_SETTINGS||{}); fd.append('boxPreset',String(ms.boxPreset||'large')); fd.append('boxWidth',String(ms.boxWidth||ms.maxImageWidth||1920)); fd.append('boxHeight',String(ms.boxHeight||ms.boxWidth||ms.maxImageWidth||1920)); fd.append('quality',String(ms.avifQuality||72));
      const response=await fetch('?api=upload',{method:'POST',headers:{'X-CMS-CSRF':api.csrf},body:fd});
      let result={};
      try{ result=await cmsResponseJson(response); }catch(err){ throw new Error('The upload server returned an invalid response.'); }
      if(!response.ok || !result.ok) throw new Error(result.error||'Image upload failed.');
      return result;
    }


    async function uploadCmsVideo(api,file){
      if(!file) throw new Error('Choose an MP4 or WebM video.');
      if(!/\.(mp4|webm)$/i.test(String(file.name||'')) && !['video/mp4','video/webm'].includes(String(file.type||'').toLowerCase())) throw new Error('Please choose an MP4 or WebM video.');
      const fd=new FormData(); fd.append('file',file,file.name||'hero-video.mp4'); fd.append('target','video');
      const response=await fetch('?api=upload',{method:'POST',headers:{'X-CMS-CSRF':api.csrf},body:fd}); let result={};
      try{result=await cmsResponseJson(response)}catch(err){throw new Error('The upload server returned an invalid response.')}
      if(!response.ok||!result.ok) throw new Error(result.error||'Video upload failed.'); return result;
    }

    function VideoUploadField({api,path,label='Hero video'}){
      const inputRef=useRef(null); const [busy,setBusy]=useState(false); const [status,setStatus]=useState(''); const value=String(get(api.data,path)||'');
      async function upload(file){if(!file||busy)return;setBusy(true);setStatus('Uploading & optimizing video...');try{const result=await uploadCmsVideo(api,file);api.update(path,result.url);setStatus(result.message||'Video uploaded.')}catch(err){const msg=err?.message||'Video upload failed.';setStatus(msg);api.setNotice(msg)}finally{setBusy(false);if(inputRef.current)inputRef.current.value=''}}
      return h('div',{className:'mb-3'},h('label',{className:'form-label fw-semibold'},api.t(label)),h('div',{className:'input-group'},h('input',{className:'form-control',value,onChange:e=>api.update(path,e.target.value),placeholder:'/assets/video/hero-demo.mp4'}),h('button',{type:'button',className:'btn btn-outline-secondary',disabled:busy,onClick:()=>inputRef.current?.click()},busy?api.t('Uploading...'):api.t('Upload video'))),h('input',{ref:inputRef,type:'file',accept:'video/mp4,video/webm,.mp4,.webm',hidden:true,onChange:e=>upload(e.target.files?.[0])}),status&&h('div',{className:'small muted mt-2'},api.t(status)),h('div',{className:'small muted mt-1'},api.t('MP4/WebM up to 50MB. When ffmpeg is available LPCMS re-encodes the hero video to lightweight 1280px H.264 MP4.')));
    }


    async function cmsCropImageFile(file,{aspect='4:3',x=50,y=50,zoom=1}={}){
      const url=URL.createObjectURL(file);
      try{
        const img=await new Promise((resolve,reject)=>{ const el=new Image(); el.onload=()=>resolve(el); el.onerror=()=>reject(new Error('This image cannot be cropped in the browser. Try uploading the original.')); el.src=url; });
        const sw=img.naturalWidth||img.width, sh=img.naturalHeight||img.height;
        if(!sw||!sh) throw new Error('Image dimensions could not be read.');
        const ratios={'1:1':1,'4:3':4/3,'16:9':16/9,'3:2':3/2};
        const targetAspect=aspect==='original' ? sw/sh : (ratios[aspect]||4/3);
        let cropW=sw, cropH=sh;
        if(sw/sh>targetAspect) cropW=sh*targetAspect; else cropH=sw/targetAspect;
        const z=Math.max(1,Math.min(3,Number(zoom)||1));
        cropW/=z; cropH/=z;
        const px=Math.max(0,Math.min(100,Number(x)||50))/100;
        const py=Math.max(0,Math.min(100,Number(y)||50))/100;
        const sx=(sw-cropW)*px, sy=(sh-cropH)*py;
        const ms=(window.__LPCMS_MEDIA_SETTINGS||{}); const maxW=Number(ms.boxWidth||ms.maxImageWidth||1920); const maxH=Number(ms.boxHeight||maxW);
        const ratioOut=Math.min(1,maxW/Math.max(1,cropW),maxH/Math.max(1,cropH));
        const outW=Math.max(1,Math.round(cropW*ratioOut));
        const outH=Math.max(1,Math.round(cropH*ratioOut));
        const canvas=document.createElement('canvas'); canvas.width=outW; canvas.height=outH;
        const ctx=canvas.getContext('2d',{alpha:true}); if(!ctx) throw new Error('Image cropping is unavailable in this browser.');
        ctx.drawImage(img,sx,sy,cropW,cropH,0,0,outW,outH);
        const blob=await new Promise(resolve=>canvas.toBlob(resolve,'image/png',.94));
        if(!blob) throw new Error('The cropped image could not be created.');
        const base=String(file.name||'image').replace(/\.[^.]+$/,'')||'image';
        return new File([blob],base+'-cropped.png',{type:'image/png',lastModified:Date.now()});
      }finally{ URL.revokeObjectURL(url); }
    }

    function ImageCropDialog({file,onCancel,onOriginal,onCrop}){
      const [aspect,setAspect]=useState('4:3'); const [x,setX]=useState(50); const [y,setY]=useState(50); const [zoom,setZoom]=useState(1); const [natural,setNatural]=useState(null); const [working,setWorking]=useState(false);
      const url=useMemo(()=>file?URL.createObjectURL(file):'',[file]);
      useEffect(()=>()=>{ if(url) URL.revokeObjectURL(url); },[url]);
      const ratios={'1:1':1,'4:3':4/3,'16:9':16/9,'3:2':3/2};
      const ratio=aspect==='original'&&natural ? natural.w/natural.h : (ratios[aspect]||4/3);
      async function confirmCrop(){ setWorking(true); try{ const cropped=await cmsCropImageFile(file,{aspect,x,y,zoom}); await onCrop(cropped); } finally{ setWorking(false); } }
      const dialog=h('div',{className:'cms-modal-backdrop',onClick:working?undefined:onCancel}, h('div',{className:'cms-modal cms-crop-modal',onClick:e=>e.stopPropagation()},
        h('div',{className:'d-flex justify-content-between align-items-center gap-3 mb-3'}, h('div',null,h('h3',{className:'h5 mb-1'},'Cover & crop image'),h('div',{className:'small muted'},'Choose a crop if needed. Every uploaded image is then scaled to the selected media box and stored as optimized AVIF.')), h('button',{type:'button',className:'icon-btn',disabled:working,onClick:onCancel},icon('bi bi-x-lg'))),
        h('div',{className:'cms-crop-preview',style:{aspectRatio:String(ratio)}}, h('img',{src:url,alt:'Crop preview',onLoad:e=>setNatural({w:e.currentTarget.naturalWidth,h:e.currentTarget.naturalHeight}),style:{objectPosition:`${x}% ${y}%`,transform:`scale(${zoom})`}})),
        h('div',{className:'mb-3'}, h('div',{className:'small fw-bold mb-2'},'Crop shape'), h('div',{className:'cms-crop-aspects'}, ['original','1:1','4:3','3:2','16:9'].map(a=>h('button',{type:'button',key:a,className:aspect===a?'active':'',onClick:()=>setAspect(a)},a==='original'?'Original ratio':a)))),
        h('div',{className:'cms-crop-controls'},
          h('label',null,'Horizontal focus',h('input',{type:'range',min:0,max:100,value:x,onChange:e=>setX(Number(e.target.value))})),
          h('label',null,'Vertical focus',h('input',{type:'range',min:0,max:100,value:y,onChange:e=>setY(Number(e.target.value))})),
          h('label',null,'Zoom',h('input',{type:'range',min:1,max:3,step:.05,value:zoom,onChange:e=>setZoom(Number(e.target.value))})),
          h('div',{className:'small muted align-self-end'},'Cover crop keeps the chosen frame filled without stretching the image.')
        ),
        h('div',{className:'d-flex flex-wrap justify-content-end gap-2 mt-4'}, h('button',{type:'button',className:'btn-ghost',disabled:working,onClick:onCancel},'Cancel'), h('button',{type:'button',className:'btn-ghost',disabled:working,onClick:onOriginal},'Keep full image'), h('button',{type:'button',className:'btn-cms',disabled:working,onClick:confirmCrop},working?'Cropping...':'Crop & upload'))
      ));
      return ReactDOM.createPortal(dialog,document.body);
    }

    function FreeImageDialog({api,path,target='img',onClose}){
      const [provider,setProvider]=useState('free'); const [q,setQ]=useState('business'); const [results,setResults]=useState([]); const [busy,setBusy]=useState(false); const [importing,setImporting]=useState(''); const [error,setError]=useState('');
      const topics=['business','technology','nature','food','travel','people','office','architecture'];
      async function search(term=q){const text=String(term||'').trim();if(!text)return;setBusy(true);setError('');try{const r=await fetch('?api=media-search&provider='+encodeURIComponent(provider)+'&q='+encodeURIComponent(text)+'&page=1',{cache:'no-store'});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Media search failed.');const rows=j.results||[];setResults(rows);setQ(text);if(!rows.length)setError('No free images found for this search. Try another keyword or source.')}catch(e){const msg=e?.message||'Media search failed.';setResults([]);setError(msg);api.setNotice(msg)}finally{setBusy(false)}}
      async function choose(item){setImporting(item.id||item.url);setError('');try{const r=await fetch('?api=media-import',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},body:JSON.stringify({url:item.url,thumb:item.thumb,target,title:item.title,creator:item.creator,source:item.source,license:item.license,licenseUrl:item.licenseUrl,landing:item.landing,...(window.__LPCMS_MEDIA_SETTINGS||{})})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Import failed.');api.update(path,j.url);api.setNotice('Free image imported into '+path+'.');onClose()}catch(e){const msg=e?.message||'Import failed.';setError(msg);api.setNotice(msg)}finally{setImporting('')}}
      useEffect(()=>{search('business')},[provider]);
      const dialog=h('div',{className:'cms-modal-backdrop',onClick:onClose},h('div',{className:'cms-modal',onClick:e=>e.stopPropagation()},
        h('div',{className:'d-flex justify-content-between align-items-start gap-3 mb-3'},h('div',null,h('h3',{className:'h4 mb-1'},'Choose a free image'),h('p',{className:'small muted mb-0'},'Search public free/open image libraries, preview the result, then import a local optimized copy.')),h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))),
        h('div',{className:'media-source-toolbar'},h('select',{className:'form-select',value:provider,onChange:e=>{setProvider(e.target.value);setResults([]);setError('')}},h('option',{value:'free'},'Free images · automatic'),h('option',{value:'openverse'},'Openverse · no key'),h('option',{value:'commons'},'Wikimedia Commons · no key'),h('option',{value:'pexels'},'Pexels · no key'),h('option',{value:'pixabay'},'Pixabay · no key'),h('option',{value:'unsplash'},'Unsplash · no key')),h('input',{className:'form-control',value:q,onChange:e=>setQ(e.target.value),onKeyDown:e=>{if(e.key==='Enter')search()},placeholder:'Search free images...'}),h('button',{className:'btn-cms',disabled:busy,onClick:()=>search()},busy?'Searching...':'Search')),
        h('div',{className:'topic-chips'},topics.map(t=>h('button',{type:'button',className:'topic-chip',key:t,onClick:()=>search(t)},t))),
        error&&h('div',{className:'alert alert-warning py-2 small'},error),
        results.length?h('div',{className:'free-media-grid'},results.slice(0,18).map(item=>h('article',{className:'free-media-card',key:item.id||item.url},h('img',{src:item.thumb,alt:item.title||'',loading:'lazy',decoding:'async',fetchPriority:'low'}),h('div',{className:'meta'},h('div',{className:'title'},item.title||'Free image'),h('div',{className:'credit'},[item.creator,item.source,item.license].filter(Boolean).join(' · ')),item.landing&&h('a',{className:'small',href:item.landing,target:'_blank',rel:'noopener noreferrer'},'Source / licence'),h('button',{className:'btn-ghost',disabled:!!importing,onClick:()=>choose(item)},importing===(item.id||item.url)?'Importing...':'Use this image'))))):!error&&h('div',{className:'small muted py-4 text-center'},busy?'Searching...':'Search to load free images.')
      ));
      return ReactDOM.createPortal(dialog,document.body);
    }

    function EditableImage({api,path,alt='',className='',fallback=null,fallbackSrc='',target='img',hideWhenEmpty=false,onView=null,eager=false,iconPath=''}){
      const inputRef=useRef(null);
      const [busy,setBusy]=useState(false);
      const [dragging,setDragging]=useState(false);
      const [status,setStatus]=useState('');
      const [pendingFile,setPendingFile]=useState(null);
      const [freeOpen,setFreeOpen]=useState(false);
      const stored=String(get(api.data,path)||'');
      const selectedIcon=iconPath?String(get(api.data,iconPath)||''):'';
      const value=mediaUrl(stored||fallbackSrc||'');
      if(!api.admin && !value && hideWhenEmpty) return null;
      async function performUpload(file){
        if(!file || busy) return;
        setBusy(true); setStatus('Uploading image...');
        try{
          const result=await uploadCmsImage(api,file,target);
          api.update(path,result.url);
          setStatus(result.width?`${String(result.format||'image').toUpperCase()} ${result.width}×${result.height}`:`${String(result.format||'image').toUpperCase()} uploaded`);
          setPendingFile(null);
        }catch(err){
          const message=err?.message||'Image upload failed.';
          setStatus(message); api.setNotice(message);
        }finally{
          setBusy(false); setDragging(false);
          if(inputRef.current) inputRef.current.value='';
        }
      }
      function accept(file){ if(!file || busy) return; setDragging(false); setStatus('Choose cover / crop options'); setPendingFile(file); }
      const wrapperProps={className:cls('cms-image-editor',className,api.admin&&'is-admin',!value&&'is-empty',iconPath&&'has-icon-choice',selectedIcon&&'has-selected-icon',dragging&&'is-dragging',busy&&'is-busy'),title:status||undefined};
      if(api.admin){
        wrapperProps.onDragEnter=e=>{e.preventDefault();setDragging(true)};
        wrapperProps.onDragOver=e=>{e.preventDefault();e.dataTransfer.dropEffect='copy';setDragging(true)};
        wrapperProps.onDragLeave=e=>{e.preventDefault();if(!e.currentTarget.contains(e.relatedTarget))setDragging(false)};
        wrapperProps.onDrop=e=>{e.preventDefault();setDragging(false);accept(e.dataTransfer.files?.[0])};
      }else if(onView && value){
        wrapperProps.onClick=onView; wrapperProps.role='button'; wrapperProps.tabIndex=0;
        wrapperProps.onKeyDown=e=>{if(e.key==='Enter'||e.key===' '){e.preventDefault();onView(e)}};
      }
      return h(React.Fragment,null,
        h('div',wrapperProps,
          value?h('img',{src:value,alt,loading:eager?'eager':'lazy',decoding:'async',fetchPriority:eager?'high':'low','data-lightbox':'site','data-lightbox-src':value}):h('div',{className:'cms-image-fallback'},iconPath?icon(selectedIcon||'bi bi-plus-lg'):(fallback||icon('bi bi-image'))),
          api.admin&&h(React.Fragment,null,
            iconPath&&!value&&h('button',{type:'button',className:'cms-icon-select-primary','aria-label':selectedIcon?'Change icon':'Choose icon',title:selectedIcon?'Change icon':'Choose icon',onClick:e=>{e.preventDefault();e.stopPropagation();try{sessionStorage.setItem('lpcms_icon_target',iconPath)}catch(x){}api.setAdminRequestedTab('icons');api.setAdminOpen(true)}},h('span',{className:'cms-icon-select-badge'},icon(selectedIcon?'bi bi-pencil':'bi bi-plus-lg')),h('span',{className:'visually-hidden'},selectedIcon?'Change icon':'Choose icon')),
            h('input',{ref:inputRef,type:'file',accept:'image/*,.avif,.heic,.heif',hidden:true,onChange:e=>accept(e.target.files?.[0])}),
            h('button',{type:'button',className:'cms-image-overlay',disabled:busy,onClick:e=>{e.preventDefault();e.stopPropagation();inputRef.current?.click()}},icon(busy?'bi bi-arrow-repeat':'bi bi-crop'),h('span',null,busy?'Uploading...':dragging?'Drop image here':status||'Upload / cover / crop')),
            iconPath&&value&&h('button',{type:'button',className:'cms-image-icon-choice','aria-label':'Use an icon instead',title:'Use an icon instead',onClick:e=>{e.preventDefault();e.stopPropagation();try{sessionStorage.setItem('lpcms_icon_target',iconPath)}catch(x){}api.setAdminRequestedTab('icons');api.setAdminOpen(true)}},icon('bi bi-grid')),
            value&&h('button',{type:'button',className:'cms-image-view','aria-label':api.t('Preview image'),title:api.t('Preview image'),onClick:e=>{e.preventDefault();e.stopPropagation();const img=e.currentTarget.closest('.cms-image-editor')?.querySelector('img');if(img)window.dispatchEvent(new CustomEvent('lpcms:open-lightbox',{detail:{img}}))}},icon('bi bi-arrows-fullscreen')),
            h('button',{type:'button',className:'cms-image-free','aria-label':'Choose free image',title:'Choose free image',onClick:e=>{e.preventDefault();e.stopPropagation();setFreeOpen(true)}},icon('bi bi-search')),
            stored&&h('button',{type:'button',className:'cms-image-remove','aria-label':'Remove image',title:'Remove image',onClick:e=>{e.preventDefault();e.stopPropagation();api.update(path,'');setStatus('Image removed')}},icon('bi bi-trash'))
          )
        ),
        pendingFile&&h(ImageCropDialog,{file:pendingFile,onCancel:()=>{setPendingFile(null);setStatus('')},onOriginal:()=>performUpload(pendingFile),onCrop:performUpload}),
        freeOpen&&h(FreeImageDialog,{api,path,target,onClose:()=>setFreeOpen(false)})
      );
    }

    const InlineEditContext = React.createContext(null);

    function AiInlineDialog({cms,path,current,html=false,onClose}){
      const [mode,setMode]=useState('improve'); const [instruction,setInstruction]=useState(''); const [language,setLanguage]=useState(''); const [output,setOutput]=useState(''); const [busy,setBusy]=useState(false);
      const modes={write:'Write new content for this field',improve:'Improve this content while keeping its meaning',shorten:'Make this shorter and clearer',clearer:'Rewrite this in simple, clear language',professional:'Rewrite in a professional business tone',friendly:'Rewrite in a friendly approachable tone',translate:'Translate this content',seo:'Rewrite this to be SEO-friendly and useful to humans'};
      function promptText(){ return `${modes[mode]||modes.improve}.${language?` Target language: ${language}.`:''}${instruction?` Extra instruction: ${instruction}.`:''}${html?' Return clean HTML suitable for a website content field.':' Return only the replacement text.'}`; }
      async function generate(){setBusy(true);setOutput('');try{const r=await fetch('?api=ai-generate',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':cms.csrf},body:JSON.stringify({prompt:promptText(),context:String(current||''),tone:mode,target:path})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'AI generation failed.');setOutput(j.text||'')}catch(e){cms.setNotice?.(e.message||'AI generation failed.')}finally{setBusy(false)}}
      async function copyPrompt(){const text=`${promptText()}\n\nCurrent content:\n${String(current||'')}`;try{await navigator.clipboard.writeText(text);cms.setNotice?.('AI prompt copied.')}catch(e){cms.setNotice?.('Copy failed.')}}
      function apply(){if(!output.trim())return;cms.update(path,output.trim());cms.setNotice?.('AI content applied. Save JSON when ready.');onClose();}
      return ReactDOM.createPortal(h('div',{className:'cms-modal-backdrop',onClick:onClose},h('div',{className:'cms-modal small ai-inline-modal',onClick:e=>e.stopPropagation()},
        h('div',{className:'d-flex justify-content-between align-items-start gap-2 mb-3'},h('div',null,h('h3',{className:'h5 mb-1'},'AI content tools'),h('div',{className:'small muted'},path)),h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))),
        h(Field,{label:'Action'},h('select',{className:'form-select',value:mode,onChange:e=>setMode(e.target.value)},Object.entries(modes).map(([k,v])=>h('option',{key:k,value:k},v)))),
        mode==='translate'&&h(Field,{label:'Target language'},h('input',{className:'form-control',value:language,onChange:e=>setLanguage(e.target.value),placeholder:'e.g. Latvian, English, German'})),
        h(Field,{label:'Optional instruction'},h('input',{className:'form-control',value:instruction,onChange:e=>setInstruction(e.target.value),placeholder:'Keywords, audience, offer, length...'})),
        h('div',{className:'small muted mb-2'},'Current content: ',String(current||'').replace(/<[^>]+>/g,' ').slice(0,220)),
        h('div',{className:'d-flex gap-2 flex-wrap mb-3'},h('button',{className:'btn-cms',disabled:busy,onClick:generate},busy?'Generating...':'Generate'),h('button',{className:'btn-ghost',onClick:copyPrompt},'Copy prompt')),
        h(Field,{label:'AI result'},h('textarea',{className:'form-control',value:output,onChange:e=>setOutput(e.target.value),placeholder:'Generated content appears here. You can edit it before applying.'})),
        h('div',{className:'d-flex justify-content-end gap-2'},h('button',{className:'btn-ghost',onClick:onClose},'Cancel'),h('button',{className:'btn-cms',disabled:!output.trim(),onClick:apply},'Apply'))
      )),document.body);
    }

    function Editable({path,tag='span',className='',html=false,placeholder=''}){
      const cms=React.useContext(InlineEditContext);
      if(!cms) return null;
      const {data,admin,update,requestSavePrompt,csrf,setNotice,lang}=cms;
      const Tag=tag; const editPath=localizedDataPath(path,lang); const baseVal=get(data,path) ?? ''; const val=localizedDataValue(data,path,lang) ?? ''; const canEdit=admin; const elRef=useRef(null); const toolbarRef=useRef(null); const pendingInline=useRef(false); const [active,setActive]=useState(false); const [aiOpen,setAiOpen]=useState(false); const [pos,setPos]=useState({top:8,left:8});
      const parts=String(path).split('.'); const leaf=parts[parts.length-1]||''; const supportsStyle=!html && !/^\d+$/.test(leaf); const stylePath=supportsStyle?[...parts.slice(0,-1),leaf+'Style'].join('.'):''; const rawStyle=supportsStyle?get(data,stylePath):null; const savedStyle=(rawStyle&&typeof rawStyle==='object'&&!Array.isArray(rawStyle))?rawStyle:{};
      function editorStyle(){ const out={}; if(savedStyle.textAlign) out.textAlign=savedStyle.textAlign; if(savedStyle.color) out.color=savedStyle.color; if(savedStyle.fontWeight) out.fontWeight=savedStyle.fontWeight; if(savedStyle.fontSize) out.fontSize=savedStyle.fontSize; if(savedStyle.fontStyle) out.fontStyle=savedStyle.fontStyle; if(savedStyle.textDecoration) out.textDecoration=savedStyle.textDecoration; return out; }
      function positionToolbar(){ if(!elRef.current)return; const r=elRef.current.getBoundingClientRect(); const width=620; setPos({top:Math.max(8,r.top-46),left:Math.max(8,Math.min(window.innerWidth-width-8,r.left))}); }
      function markInlineChanged(){ pendingInline.current=true; }
      function askToSaveInline(){ if(!pendingInline.current)return; pendingInline.current=false; requestSavePrompt?.('You changed inline content or formatting. Save these changes to data.json now?'); }
      function commitCurrentText(){ if(!elRef.current)return false; const next=html?elRef.current.innerHTML:elRef.current.textContent; const changed=String(next??'')!==String(val??''); if(changed){ update(editPath,next); markInlineChanged(); } return changed; }
      function changeStyle(key,value){ commitCurrentText(); const current=get(data,stylePath); const next=(current&&typeof current==='object'&&!Array.isArray(current))?{...current}:{}; if(value===''||value==null) delete next[key]; else next[key]=value; update(stylePath,next); markInlineChanged(); }
      function clearStyle(){ commitCurrentText(); update(stylePath,{}); markInlineChanged(); }
      useEffect(()=>{ if(!active)return; positionToolbar(); const move=()=>positionToolbar(); const outside=e=>{ if(elRef.current?.contains(e.target)||toolbarRef.current?.contains(e.target))return; setActive(false); askToSaveInline(); }; window.addEventListener('resize',move); window.addEventListener('scroll',move,true); document.addEventListener('pointerdown',outside,true); return()=>{window.removeEventListener('resize',move);window.removeEventListener('scroll',move,true);document.removeEventListener('pointerdown',outside,true)}; },[active]);
      const props={ref:elRef,className:cls('cms-editable',canEdit&&'admin',className),suppressContentEditableWarning:true,style:editorStyle()};
      if(canEdit){ props.contentEditable=true; props.onFocus=()=>{setActive(supportsStyle);setTimeout(positionToolbar,0)}; props.onClick=()=>{if(supportsStyle){setActive(true);setTimeout(positionToolbar,0)}}; props.onBlur=e=>{ const next=html?e.currentTarget.innerHTML:e.currentTarget.textContent; const changed=String(next??'')!==String(val??''); if(changed){ update(editPath,next); markInlineChanged(); } const movedToToolbar=toolbarRef.current?.contains(e.relatedTarget); if(!movedToToolbar) askToSaveInline(); }; props.onKeyDown=e=>{if(e.key==='Escape'){setActive(false);askToSaveInline();}}; props.title=supportsStyle?'Click to edit - alignment, colour and weight tools available':'Click to edit'; }
      const node=html?h(Tag,{...props,dangerouslySetInnerHTML:{__html:val||placeholder}}):h(Tag,props,val||placeholder);
      if(!canEdit||!supportsStyle||!active) return node;
      const toolbar=h('div',{ref:toolbarRef,className:'cms-inline-toolbar',style:{top:pos.top,left:pos.left},onPointerDown:e=>{if(e.target.closest?.('button'))e.preventDefault()}},
        h('select',{title:'Text size','aria-label':'Text size',value:String(savedStyle.fontSize||''),onChange:e=>changeStyle('fontSize',e.target.value)},h('option',{value:''},'Size'),h('option',{value:'.875rem'},'Small'),h('option',{value:'1rem'},'Normal'),h('option',{value:'1.25rem'},'Large'),h('option',{value:'1.6rem'},'XL'),h('option',{value:'2.2rem'},'2XL')),
        h('select',{title:'Font weight','aria-label':'Font weight',value:String(savedStyle.fontWeight||''),onChange:e=>changeStyle('fontWeight',e.target.value)},h('option',{value:''},'Weight'),h('option',{value:'400'},'Regular'),h('option',{value:'500'},'Medium'),h('option',{value:'600'},'Semi'),h('option',{value:'700'},'Bold'),h('option',{value:'800'},'Extra')),
        h('button',{type:'button',className:savedStyle.fontStyle==='italic'?'active':'',title:'Italic',onClick:()=>changeStyle('fontStyle',savedStyle.fontStyle==='italic'?'':'italic')},icon('bi bi-type-italic')),
        h('button',{type:'button',className:savedStyle.textDecoration==='underline'?'active':'',title:'Underline',onClick:()=>changeStyle('textDecoration',savedStyle.textDecoration==='underline'?'':'underline')},icon('bi bi-type-underline')),
        h('span',{className:'toolbar-divider'}),
        ['left','center','right'].map(a=>h('button',{type:'button',key:a,className:savedStyle.textAlign===a?'active':'',title:'Align '+a,'aria-label':'Align '+a,onClick:()=>changeStyle('textAlign',savedStyle.textAlign===a?'':a)},icon('bi bi-text-'+a))),
        h('span',{className:'toolbar-divider'}),
        h('label',{className:'cms-inline-color',title:'Text colour'},h('span',{className:'visually-hidden'},'Text colour'),h('input',{type:'color','aria-label':'Text colour',value:savedStyle.color||'#1f2937',onInput:e=>changeStyle('color',e.target.value)})),
        h('button',{type:'button',title:'Clear text styling','aria-label':'Clear text styling',onClick:clearStyle},icon('bi bi-eraser')),
        data.settings?.ai?.enabled&&h('button',{type:'button',className:'ai-inline-btn',title:'AI write / improve / translate',onClick:()=>{commitCurrentText();setAiOpen(true)}},icon('bi bi-stars'))
      );
      return h(React.Fragment,null,node,ReactDOM.createPortal(toolbar,document.body),aiOpen&&h(AiInlineDialog,{cms:{data,update,csrf,setNotice},path:editPath,current:localizedDataValue(data,path,lang)||'',html,onClose:()=>setAiOpen(false)}));
    }

    function GlobalImageLightbox(){
      const [state,setState]=useState(null);
      function toUrl(value){const raw=mediaUrl(String(value||'').trim());if(!raw)return '';try{return new URL(raw,location.origin+(CMS_BASE_PATH||'/')).href}catch(e){try{return new URL(raw,document.baseURI).href}catch(x){return raw}}}
      function looksLikeImageUrl(value){return /\.(?:avif|webp|png|jpe?g|gif|svg)(?:[?#].*)?$/i.test(String(value||''))||/^https?:\/\/(?:images\.|images\.unsplash\.com|.*\.(?:cloudfront|wikimedia)\.)/i.test(String(value||''));}
      function imageItem(img){if(!img)return null;const attr=img.getAttribute('data-lightbox-src')||img.getAttribute('data-full-src')||'';const rendered=img.currentSrc||img.getAttribute('src')||img.src||'';const anchor=img.closest('a');const href=anchor?.getAttribute('href')||'';const source=attr||(looksLikeImageUrl(href)?href:'')||rendered;const src=toUrl(source),fallback=toUrl(rendered);if(!src)return null;return {src,fallback:fallback&&fallback!==src?fallback:'',alt:img.alt||anchor?.getAttribute('aria-label')||'',node:img};}
      function eligible(img,allowAdmin=false){if(!img||img.closest('.admin-panel,.site-header,.site-footer,.no-lightbox,.cms-crop-preview,.free-media-grid,.cms-upload-result'))return false;if(!allowAdmin&&img.closest('.cms-image-editor.is-admin'))return false;return img.hasAttribute('data-lightbox')||!!img.closest('.admin-super-image,.admin-shot-card');}
      function openFrom(img,allowAdmin=false){if(!eligible(img,allowAdmin))return;const group=img.getAttribute('data-lightbox')||'site';const scope=img.closest('section')||img.closest('.cms-modal')||document.body;let candidates=[...scope.querySelectorAll('img[data-lightbox]')].filter(x=>eligible(x,allowAdmin));if(group&&group!=='site')candidates=candidates.filter(x=>(x.getAttribute('data-lightbox')||'site')===group);const rawItems=candidates.map(imageItem).filter(Boolean),seen=new Set(),items=[];for(const it of rawItems){if(seen.has(it.src))continue;seen.add(it.src);items.push(it)}const clicked=imageItem(img);if(!clicked)return;let index=items.findIndex(it=>it.src===clicked.src);if(index<0){items.unshift(clicked);index=0}setState({items,index,error:''});}
      useEffect(()=>{function click(e){const img=e.target?.closest?.('img');if(!eligible(img,false)||img.closest('.product-gallery'))return;const anchor=img.closest('a'),href=anchor?.getAttribute('href')||'';if(anchor&&!looksLikeImageUrl(href)&&!anchor.classList.contains('admin-super-image')&&!anchor.closest('.admin-shot-card'))return;e.preventDefault();e.stopPropagation();openFrom(img,false)}function custom(e){const img=e?.detail?.img;if(img)openFrom(img,true)}document.addEventListener('click',click,true);window.addEventListener('lpcms:open-lightbox',custom);return()=>{document.removeEventListener('click',click,true);window.removeEventListener('lpcms:open-lightbox',custom)}},[]);
      useEffect(()=>{if(!state)return;const key=e=>{if(e.key==='Escape')setState(null);if(e.key==='ArrowLeft')setState(v=>v?{...v,index:(v.index-1+v.items.length)%v.items.length,error:''}:v);if(e.key==='ArrowRight')setState(v=>v?{...v,index:(v.index+1)%v.items.length,error:''}:v)};document.addEventListener('keydown',key);const old=document.body.style.overflow;document.body.style.overflow='hidden';return()=>{document.removeEventListener('keydown',key);document.body.style.overflow=old}},[!!state]);
      if(!state)return null;const item=state.items[state.index]||{},many=state.items.length>1;function imageError(){if(item.fallback&&item.fallback!==item.src){setState(v=>{if(!v)return v;const items=v.items.slice();items[v.index]={...items[v.index],src:item.fallback,fallback:''};return {...v,items,error:''}})}else setState(v=>v?{...v,error:'Image preview could not be loaded.'}:v)}
      return ReactDOM.createPortal(h('div',{className:'cms-global-lightbox',role:'dialog','aria-modal':'true','aria-label':'Image preview',onClick:()=>setState(null)},h('div',{className:'cms-global-lightbox-frame',onClick:e=>e.stopPropagation()},h('button',{type:'button',className:'cms-global-lightbox-close','aria-label':'Close',title:'Close',onClick:()=>setState(null)},icon('bi bi-x-lg')),many&&h('button',{type:'button',className:'cms-global-lightbox-nav cms-global-lightbox-prev','aria-label':'Previous image',onClick:()=>setState(v=>({...v,index:(v.index-1+v.items.length)%v.items.length,error:''}))},icon('bi bi-chevron-left')),state.error?h('div',{className:'cms-global-lightbox-status',role:'status'},state.error):h('img',{key:item.src,className:'cms-global-lightbox-image',src:item.src,alt:item.alt||'',onError:imageError,decoding:'async'}),many&&h('button',{type:'button',className:'cms-global-lightbox-nav cms-global-lightbox-next','aria-label':'Next image',onClick:()=>setState(v=>({...v,index:(v.index+1)%v.items.length,error:''}))},icon('bi bi-chevron-right')),item.alt&&h('div',{className:'cms-global-lightbox-caption'},item.alt))),document.body);
    }

    function App(){
      const [boot,setBoot] = useState(initialBoot);
      const [data,setData] = useState(seed);
      const [lang,setLangState] = useState(SERVER_LANG || DEFAULT_LANG);
      const [adminOpen,setAdminOpen] = useState(false);
      const [adminRequestedTab,setAdminRequestedTab] = useState('');
      const [modal,setModal] = useState(null);
      const [quoteItems,setQuoteItems] = useState(()=>{ try{return JSON.parse(localStorage.getItem('cms_quote_items')||'[]')}catch(e){return[]} });
      const [shopCartItems,setShopCartItems] = useState(()=>{ try{return JSON.parse(localStorage.getItem('cms_shop_cart')||'[]')}catch(e){return[]} });
      const [notice,setNotice] = useState('');
      const [dirty,setDirty] = useState(false);
      const [savePrompt,setSavePrompt] = useState(null);
      const admin = boot.admin;
      const csrf = boot.csrf;
      const theme = data.settings.theme || {};
      window.__LPCMS_MEDIA_SETTINGS = data.settings.media || {};
      window.__lpcmsLang = lang;
      function setLang(code){
        code=String(code||DEFAULT_LANG).toLowerCase(); if(!LANGUAGE_CODES.includes(code))code=DEFAULT_LANG;
        try{localStorage.setItem('lpcms_lang',code);localStorage.setItem('lpcms_lang_manual','1')}catch(e){}
        const next=languageUrl(code); if(next!==location.pathname+location.search+location.hash){location.assign(next);return;} setLangState(code);
      }
      function t(text){ return translatedText(lang,text); }
      function href(v){ return internalHref(lang,v); }

      useEffect(()=>{
        document.documentElement.lang=lang;
        const explicit=explicitLangFromPath();
        const cfg=data.settings?.localization||{};
        if(explicit){try{localStorage.setItem('lpcms_lang',explicit)}catch(e){};return;}
        let desired=''; try{desired=cfg.persistChoice!==false?localStorage.getItem('lpcms_lang')||'':''}catch(e){}
        if(!desired&&cfg.autoDetect!==false)desired=browserLanguage();
        if(desired&&LANGUAGE_CODES.includes(desired)&&desired!==DEFAULT_LANG){ const next=languageUrl(desired); if(next!==location.pathname+location.search+location.hash)location.replace(next); }
      },[]);
      useEffect(()=>{ fetch('?api=site',{cache:'no-store'}).then(r=>cmsResponseJson(r)).then(j=>{ if(j.ok){ setBoot(j); setData(j.data); if(j.lang&&LANGUAGE_CODES.includes(String(j.lang).toLowerCase()))setLangState(String(j.lang).toLowerCase()); } }).catch(err=>{ console.error('LPCMS API boot failed:',err); setNotice('Server API error: '+(err?.message||'Could not load site data.')); }); }, []);
      useEffect(()=>{
        function scrollHash(behavior='auto'){const raw=String(location.hash||'').replace(/^#/,'');if(!raw)return false;let id=raw;try{id=decodeURIComponent(raw)}catch(e){}const target=document.getElementById(id);if(!target)return false;const header=document.querySelector('.site-header');const offset=(header?.getBoundingClientRect().height||0)+10;const top=Math.max(0,target.getBoundingClientRect().top+window.scrollY-offset);window.scrollTo({top,behavior});return true;}
        const run=()=>{if(location.hash)scrollHash('auto')};requestAnimationFrame(()=>{run();setTimeout(run,80);setTimeout(run,350)});const onHash=()=>requestAnimationFrame(()=>scrollHash('smooth'));window.addEventListener('hashchange',onHash);return()=>window.removeEventListener('hashchange',onHash);
      },[lang,data.version]);
      useEffect(()=>{ document.documentElement.style.setProperty('--cms-brand', theme.brand || '#0d6efd'); document.documentElement.style.setProperty('--cms-brand2', theme.brand2 || '#6610f2'); document.documentElement.style.setProperty('--cms-accent', theme.accent || '#20c997'); document.documentElement.style.setProperty('--cms-text', theme.text || '#1f2937'); document.documentElement.style.setProperty('--cms-muted', theme.muted || '#6b7280'); document.documentElement.style.setProperty('--cms-bg', theme.bg || '#fff'); document.documentElement.style.setProperty('--cms-soft', theme.softBg || '#f6f8fb'); document.documentElement.style.setProperty('--cms-radius', theme.radius || '22px'); }, [theme]);
      useEffect(()=>{ if(window.AOS) window.AOS.init({once:true,duration:700}); }, []);
      useEffect(()=>{ const nodes=[...document.querySelectorAll('.reveal-section')]; if(!nodes.length)return; if(!('IntersectionObserver' in window)){nodes.forEach(el=>el.classList.add('is-visible'));return;} const io=new IntersectionObserver(entries=>entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('is-visible');io.unobserve(entry.target);}}),{threshold:.08,rootMargin:'0px 0px -7% 0px'}); nodes.forEach(el=>io.observe(el)); return()=>io.disconnect(); }, [lang,data.settings?.sectionOrder,data.settings?.sectionPatterns]);
      useEffect(()=>{ const styleId='cms-custom-live'; let st=document.getElementById(styleId); if(!st){ st=document.createElement('style'); st.id=styleId; document.head.appendChild(st);} st.textContent=data.settings.theme.customCss||''; }, [data.settings.theme.customCss]);
      useEffect(()=>{ maybeLoadGA(data); }, [data.settings.analytics, data.settings.cookies]);
      useEffect(()=>{ localStorage.setItem('cms_quote_items', JSON.stringify(quoteItems)); }, [quoteItems]);
      useEffect(()=>{ localStorage.setItem('cms_shop_cart', JSON.stringify(shopCartItems)); }, [shopCartItems]);
      useEffect(()=>{ if(data.settings?.app?.enabled && 'serviceWorker' in navigator && (location.protocol==='https:'||location.hostname==='localhost')) navigator.serviceWorker.register((CMS_BASE_PATH||'')+'/service-worker.js').catch(()=>{}); }, [data.settings?.app?.enabled]);
      useEffect(()=>{ const token=new URLSearchParams(location.search).get('recover-cart'); if(!token)return; fetch('?api=abandoned-cart-recover&token='+encodeURIComponent(token)).then(r=>cmsResponseJson(r)).then(j=>{if(j.ok&&Array.isArray(j.cart?.lines)){setShopCartItems(j.cart.lines);setModal({type:'cart',email:j.email||''});setNotice('Your saved cart has been restored.');const u=new URL(location.href);u.searchParams.delete('recover-cart');history.replaceState({},'',u.pathname+u.search+u.hash);}}).catch(()=>{}); },[]);

      function numberValue(v){ if(typeof v==='number') return v; const m=String(v||'').replace(',','.').match(/[0-9]+(?:\.[0-9]+)?/); return m ? Number(m[0]) : 0; }
      function productVariationList(item){ return Array.isArray(item?.variations) ? item.variations.filter(v=>v&&typeof v==='object') : []; }
      function variationLabel(v){ const attrs=v?.attributes||{}; const bits=Object.keys(attrs).map(k=>`${k}: ${attrs[k]}`).filter(Boolean); return bits.length?bits.join(', '):(v?.sku||v?.id||'Variation'); }
      function variationKey(v){ return v?.id||v?.sku||variationLabel(v)||''; }
      function productPriceValue(item,variation=null){ if(variation) return numberValue(variation.price || item?.price); const vars=productVariationList(item).map(v=>numberValue(v.price)).filter(n=>n>0); if((item?.type==='variable'||vars.length) && vars.length) return Math.min(...vars); return numberValue(item?.price); }
      function productOldPriceValue(item,variation=null){ if(variation) return numberValue(variation.oldPrice || item?.oldPrice); const vars=productVariationList(item).map(v=>numberValue(v.oldPrice||v.price)).filter(n=>n>0); if((item?.type==='variable'||vars.length) && vars.length) return Math.min(...vars); return numberValue(item?.oldPrice); }
      function formatMoney(value,currency){ return new Intl.NumberFormat(undefined,{style:'currency',currency:currency||data.products?.currency||'EUR'}).format(Number(value||0)); }
      function formatProductPrice(item,variation=null){ const currency=variation?.currency || item?.currency || data.products?.currency || 'EUR'; const price=productPriceValue(item,variation); const prefix=(!variation && productVariationList(item).length>0) ? t('From')+' ' : ''; return price>0 ? prefix + formatMoney(price,currency) : t('Price on request'); }
      function basketLine(item){ const qty=item.qty||1; const sku=item.sku ? ` [${item.sku}]` : ''; const vars=item.variationLabel ? ` (${item.variationLabel})` : ''; return `${qty} × ${t(item.title||'Product')}${sku}${vars} - ${formatProductPrice(item)}`; }
      function normaliseProductLine(item,variation=null,qty=1){ return {title:item.title||'Product',slug:item.slug||'',id:item.id||'',sku:variation?.sku||item.sku||'',variationId:variationKey(variation),variationLabel:variation?variationLabel(variation):'',attributes:variation?.attributes||item.attributes||{},qty:Math.max(1,Number(qty)||1),price:productPriceValue(item,variation),currency:variation?.currency||item.currency||data.products?.currency||'EUR'}; }
      function addToQuote(item,variation=null,qty=1){ const line=normaliseProductLine(item,variation,qty); setQuoteItems(items=>{ const key=(line.slug||line.title)+'|'+line.variationId; if(items.some(x=>((x.slug||x.title)+'|'+(x.variationId||''))===key)) return items; return [...items,line]; }); setNotice('Added to quote basket'); }
      function addToCart(item,variation=null,qty=1){ const line=normaliseProductLine(item,variation,qty); if(line.price<=0){ addToQuote(item,variation,qty); return; } setShopCartItems(items=>{ const key=(line.slug||line.title)+'|'+line.variationId; const found=items.findIndex(x=>((x.slug||x.title)+'|'+(x.variationId||''))===key); if(found>=0){ const next=[...items]; next[found]={...next[found],qty:(next[found].qty||1)+line.qty}; return next; } return [...items,line]; }); setNotice('Added to shopping cart'); }
      function removeQuote(i){ setQuoteItems(items=>items.filter((_,x)=>x!==i)); }
      function clearQuote(){ setQuoteItems([]); }
      function removeCart(i){ setShopCartItems(items=>items.filter((_,x)=>x!==i)); }
      function clearCart(){ setShopCartItems([]); }
      function cartTotal(){ return shopCartItems.reduce((sum,it)=>sum+(Number(it.price||0)*Number(it.qty||1)),0); }
      function quoteMessage(){ return quoteItems.length ? t('Quote request products:')+'\n' + quoteItems.map((it,i)=>`${i+1}. ${basketLine(it)}`).join('\n') + '\n\n'+t('Message:') : ''; }
      function orderMessage(serverCart=null){ const cfg=data.products||{}; const pay=cfg.payment?.directDebit||{}; const cart=serverCart||null; const items=cart?.lines||shopCartItems; const total=cart?.total??cartTotal(); const lines=items.map((it,i)=>`${i+1}. ${(it.qty||1)} × ${t(it.title||'Product')}${it.sku?' ['+it.sku+']':''}${it.variationLabel?' ('+it.variationLabel+')':''} - ${formatMoney(it.price||0,it.currency||cfg.currency||'EUR')}`).join('\n'); const details=cart?`\n${t('Subtotal')}: ${formatMoney(cart.subtotal||0,cfg.currency||'EUR')}${Number(cart.discount||0)>0?'\n'+t('Discount')+': -'+formatMoney(cart.discount,cfg.currency||'EUR'):''}${cart.shippingLabel?'\n'+t('Shipping')+': '+t(cart.shippingLabel)+' '+formatMoney(cart.shipping||0,cfg.currency||'EUR'):''}`:''; const payment=pay.enabled ? `\n\n${t('Payment method:')} ${t(pay.title||'Direct bank transfer')}\n${t(pay.instructions||'')}${pay.accountName?'\n'+t('Account:')+' '+pay.accountName:''}${pay.iban?'\nIBAN: '+pay.iban:''}${pay.swift?'\nSWIFT/BIC: '+pay.swift:''}${pay.bankName?'\n'+t('Bank:')+' '+pay.bankName:''}` : ''; return items.length ? t('Order products:')+'\n' + lines + details + `\n\n${t('Total')}: ${formatMoney(total,cfg.currency||'EUR')}` + payment + '\n\n'+t('Customer message:') : ''; }

      function update(path,val){ setData(d=>set(d,path,val)); setDirty(true); }
      function updateLocalized(path,val){ update(localizedDataPath(path,lang),val); }
      function localizedValue(path){ return localizedDataValue(data,path,lang); }
      function replace(next){ setData(next); setDirty(true); }
      function requestSavePrompt(message){ setSavePrompt(current=>current||{message:message||'You have unsaved inline changes. Save them now?'}); }
      async function save(options={}){ const silent=!!options?.silent; try{ const r=await fetch('?api=save',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':csrf},body:JSON.stringify(data)}); const j=await cmsResponseJson(r); if(j.ok){setData(j.data);setDirty(false);if(!silent)setNotice('Saved to data.json');return {ok:true,data:j.data};} const error=j.error||'Save failed'; if(!silent)setNotice(error); return {ok:false,error}; }catch(err){ const error=err?.message||'Save failed'; if(!silent)setNotice(error); return {ok:false,error}; } }
      async function logout(){ await fetch('?api=logout',{method:'POST',headers:{'X-CMS-CSRF':csrf}}); location.reload(); }
      const api = {data,setData,update,updateLocalized,localizedPath:(path)=>localizedDataPath(path,lang),localizedValue,replace,Editable,admin,t,href,email:boot.email,role:boot.role||'',isSuper:!!boot.isSuper,setModal,lang,setLang,save,logout,dirty,notice,setNotice,adminOpen,setAdminOpen,adminRequestedTab,setAdminRequestedTab,csrf,boot,setBoot,quoteItems,shopCartItems,addToQuote,addToCart,removeQuote,clearQuote,removeCart,clearCart,cartTotal,quoteMessage,orderMessage,formatProductPrice,productPriceValue,productOldPriceValue,variationLabel,productVariationList};
      const sectionRegistry={hero:Hero,about:About,stats:Stats,features:FeatureBlocks,services:Services,process:Process,gallery:Gallery,testimonials:Testimonials,cards:ImageCards,contentList:ContentList,team:TeamLead,growth:GrowthMarketing,industries:Industries,logos:LogoStrip,products:Products,booking:Booking,adminShowcase:AdminShowcase,pricing:Pricing,demoIntake:DemoIntake,blog:Blog,faq:FAQ,contact:Contact,cta:CTA};
      const defaultSectionOrder=Object.keys(sectionRegistry); const savedOrder=Array.isArray(data.settings?.sectionOrder)?data.settings.sectionOrder:defaultSectionOrder; const sectionOrder=[...new Set([...savedOrder.filter(x=>sectionRegistry[x]),...defaultSectionOrder.filter(x=>!savedOrder.includes(x))])];
      return h(InlineEditContext.Provider,{value:{data,admin,update,requestSavePrompt,csrf,setNotice,lang}},
        h(Header,{api}),
        boot.route?.item && h(RoutePage,{api,route:boot.route}),
        ...sectionOrder.map((key,sectionIndex)=>{const Comp=sectionRegistry[key];if(!Comp||!data[key]?.enabled)return null;const pattern=String(data.settings?.sectionPatterns?.[key]||'1');const reveal=key==='hero'?'':(sectionIndex%2===0?' reveal-section reveal-left':' reveal-section reveal-right');return h('div',{key,className:'section-pattern-'+pattern+reveal},h(Comp,{api}));}),
        h(Footer,{api}),
        h('button',{className:'btn-cms scroll-up',onClick:()=>scrollTo({top:0,behavior:'smooth'})},'↑'),
        admin && h('button',{className:'btn-cms admin-login',onClick:()=>setAdminOpen(true)}, icon('bi bi-sliders'), ' Admin'),
        !admin && h(LoginButton,{api}),
        data.products?.basketEnabled && quoteItems.length>0 && h('button',{className:'btn-cms basket-btn quote-floating',onClick:()=>setModal({type:'basket'})}, icon('bi bi-bag'), ' Quote ', h('span',{className:'basket-count'},quoteItems.length)),
        data.products?.shopEnabled && data.products?.cartEnabled && shopCartItems.length>0 && h('button',{className:'btn-cms basket-btn cart-floating',onClick:()=>setModal({type:'cart'})}, icon('bi bi-cart3'), ' Cart ', h('span',{className:'basket-count'},shopCartItems.length)),
        h(AdminPanel,{api,open:adminOpen,onClose:()=>setAdminOpen(false)}),
        h(GlobalImageLightbox),
        h(CookieBanner,{api}),
        data.chatbot?.enabled && !admin && h(ChatbotWidget,{api}),
        modal && h(Modal,{modal,setModal,api}),
        savePrompt && h(SaveChangesDialog,{api,prompt:savePrompt,onClose:()=>setSavePrompt(null)}),
        notice && h('div',{className:'cms-modal-backdrop',onClick:()=>setNotice('')}, h('div',{className:'cms-modal small'}, h('p',{className:'mb-3'},notice), h('button',{className:'btn-cms',onClick:()=>setNotice('')},'OK')))
      );
    }

    function maybeLoadGA(data){
      const id=data.settings?.analytics?.ga4Id; if(!id || window.__cmsGaLoaded) return;
      const consentNeeded=data.settings?.analytics?.loadAfterCookieConsent && data.settings?.cookies?.enabled;
      if(consentNeeded && localStorage.getItem('cms_cookie_ok')!=='yes') return;
      window.__cmsGaLoaded=true;
      const s=document.createElement('script'); s.async=true; s.src='https://www.googletagmanager.com/gtag/js?id='+encodeURIComponent(id); document.head.appendChild(s);
      window.dataLayer=window.dataLayer||[]; function gtag(){dataLayer.push(arguments)} window.gtag=gtag; gtag('js',new Date()); gtag('config',id);
    }

    function translate(api,obj,key){ const raw=obj?.i18n?.[api.lang]?.[key] ?? obj?.[key] ?? ''; return api.t(raw); }
    function RoutePage({api,route}){ const item=route.item||{}; const group=route.group||'pages'; const path=`${group}.items.${route.index}`; const E=api.Editable; const isProduct=group==='products'; const price=api.productPriceValue(item); const mode=price<=0?'quote':String(item.purchaseMode||api.data.products?.defaultPurchaseMode||'quote'); const showBuy=isProduct&&mode==='checkout'&&api.data.products?.showBuyButton&&item.buyButton!==false&&price>0; const showQuote=isProduct&&mode==='quote'&&api.data.products?.showQuoteButton&&item.quoteButton!==false; return h('section',{className:'section cms-soft'}, h(Container,{api}, h('article',{className:'cms-card mx-auto',style:{maxWidth:980}}, h('div',{className:'eyebrow text-primary fw-bold mb-2'}, isProduct?'Product':group==='blog'?'Blog':'Page'), h(EditableImage,{api,path:path+'.image',alt:item.title||'',className:'admin-image-field mb-4',hideWhenEmpty:true,eager:true}), h(E,{path:path+'.title',tag:'h1',className:'display-5 fw-bold'}), item.date&&h('p',{className:'muted'},item.date), isProduct&&h(ProductPrice,{api,item,path}), h(E,{path:path+'.body',tag:'div',html:true,className:'fs-5'}), isProduct&&Array.isArray(item.features)&&item.features.length>0&&h('ul',{className:'mt-3'},item.features.map((f,i)=>h('li',{key:i}, h(E,{path:path+'.features.'+i})))), isProduct&&h('div',{className:'product-actions'}, showBuy&&(item.checkoutUrl?h('a',{className:'btn-cms',href:item.checkoutUrl,target:'_blank',rel:'noopener noreferrer'},item.checkoutButtonText||api.data.products?.buyButtonText||'Checkout now'):h('button',{className:'btn-cms',onClick:()=>api.addToCart(item)},item.checkoutButtonText||api.data.products?.buyButtonText||'Checkout now')), showQuote&&h('button',{className:'btn-cms',onClick:()=>api.addToQuote(item)},api.data.products?.quoteButtonText||'Add to quote')), h('div',{className:'share-row'}, shareLinks(location.href,item.title).map(s=>h('a',{key:s.label,href:s.url,target:'_blank',rel:'noopener'},s.label)))))); }
    function Container({api,children,className=''}){ const grid=api.data.settings.theme.grid || 'container'; return h('div',{className:cls(grid,className)},children); }
    function SectionTitle({api,base}){ const E=api.Editable; const defaults={stats:'Highlights',features:'Features',services:'Services',process:'How it works',gallery:'Gallery',testimonials:'Testimonials',cards:'Highlights',contentList:'Details',team:'Team lead',growth:'Marketing, SEO & AI content',industries:'Industries',logos:'Trusted by',products:'Products',booking:'Booking',adminShowcase:'Admin & editing',pricing:'Pricing',demoIntake:'Free demo',blog:'Blog',faq:'FAQ',contact:'Contact'}; const fallback=api.t(defaults[base]||String(base||'').replace(/([a-z])([A-Z])/g,'$1 $2').replace(/^./,m=>m.toUpperCase())); return h('div',{className:'section-title'}, h(E,{path:base+'.preheader',tag:'div',className:'eyebrow',placeholder:fallback}), h(E,{path:base+'.title',tag:'h2'}), h(E,{path:base+'.subtitle',tag:'p'})); }

    function Header({api}){ const {data,setModal,lang,setLang,admin,update}=api; const [open,setOpen]=useState(false); const [langOpen,setLangOpen]=useState(false); const langRef=useRef(null); const E=api.Editable; const menu=data.header.menu||[]; const flagMap={en:'/assets/img/flags/gb.svg',lv:'/assets/img/flags/lv.svg',ru:'/assets/img/flags/ru.svg',fr:'/assets/img/flags/fr.svg',es:'/assets/img/flags/es.svg',de:'/assets/img/flags/de.svg',pl:'/assets/img/flags/pl.svg',lt:'/assets/img/flags/lt.svg',et:'/assets/img/flags/ee.svg',da:'/assets/img/flags/dk.svg',no:'/assets/img/flags/no.svg',sv:'/assets/img/flags/se.svg',fi:'/assets/img/flags/fi.svg',is:'/assets/img/flags/is.svg'}; const languageNames={en:'English',lv:'Latviešu',ru:'Русский',fr:'Français',es:'Español',de:'Deutsch',pl:'Polski',lt:'Lietuvių',et:'Eesti',da:'Dansk',no:'Norsk',sv:'Svenska',fi:'Suomi',is:'Íslenska'}; const languages=data.settings.languages||I18N.languages||[]; const activeLang=languages.find(l=>String(l.code).toLowerCase()===String(lang).toLowerCase())||{code:lang,label:String(lang||'EN').toUpperCase()}; useEffect(()=>{ if(!langOpen)return; const close=e=>{if(langRef.current&&!langRef.current.contains(e.target))setLangOpen(false)}; const esc=e=>{if(e.key==='Escape')setLangOpen(false)}; document.addEventListener('pointerdown',close);document.addEventListener('keydown',esc);return()=>{document.removeEventListener('pointerdown',close);document.removeEventListener('keydown',esc)}; },[langOpen]); useEffect(()=>{ if(!open)return; const prevBodyOverflow=document.body.style.overflow; const prevHtmlOverflow=document.documentElement.style.overflow; document.body.style.overflow='hidden'; document.documentElement.style.overflow='hidden'; const esc=e=>{if(e.key==='Escape')setOpen(false)}; const resize=()=>{if(window.innerWidth>=1200)setOpen(false)}; document.addEventListener('keydown',esc); window.addEventListener('resize',resize,{passive:true}); return()=>{document.removeEventListener('keydown',esc);window.removeEventListener('resize',resize);document.body.style.overflow=prevBodyOverflow;document.documentElement.style.overflow=prevHtmlOverflow}; },[open]); function chooseLanguage(code){setLangOpen(false);setLang(code)} function linkProps(it,mobile=false){const blank=it?.target==='_blank';return {className:mobile?'':'nav-linkx',href:api.href(it?.href||'#'),target:blank?'_blank':undefined,rel:blank?'noopener noreferrer':undefined,onClick:()=>setOpen(false)}} function renderMenu(items,mobile=false){ return h('ul',null,items.map((it,i)=>h('li',{key:i,className:!mobile&&it.children?.length?'has-sub':''}, h('a',linkProps(it,mobile), translate(api,it,'label'), it.children?.length? h('span',{className:'ms-1'},'▾'):null), it.children?.length?h('ul',{className:mobile?'mobile-submenu':'submenu'},it.children.map((c,j)=>h('li',{key:j},h('a',linkProps(c,mobile),translate(api,c,'label'))))):null))); }
      const languagePicker=h('div',{className:'lang-picker',ref:langRef}, h('button',{className:'icon-btn lang-flag-btn',type:'button','aria-label':'Language: '+(languageNames[lang]||activeLang.name||activeLang.label||lang),'aria-haspopup':'menu','aria-expanded':langOpen?'true':'false',title:languageNames[lang]||activeLang.name||activeLang.label||lang,onClick:()=>setLangOpen(v=>!v)},h('img',{className:'flag-img',src:mediaUrl(flagMap[lang]||flagMap.en),alt:'',width:22,height:16,'aria-hidden':'true'})), langOpen&&h('div',{className:'lang-menu',role:'menu'},languages.map(l=>{const code=String(l.code||'').toLowerCase();return h('button',{key:code,type:'button',role:'menuitem',className:cls('lang-option',code===lang&&'active'),onClick:()=>chooseLanguage(code)},h('img',{className:'flag-img',src:mediaUrl(flagMap[code]||flagMap.en),alt:'',width:24,height:18,'aria-hidden':'true'}),h('span',null,l.name||languageNames[code]||l.label||code),h('span',{className:'lang-code'},code.toLowerCase()))}))); 
      const mobileMenu=open&&ReactDOM.createPortal(h(React.Fragment,null,
        h('button',{type:'button',className:'mobile-menu-backdrop','aria-label':api.t('Close menu')||'Close menu',onClick:()=>setOpen(false)}),
        h('aside',{className:'mobile-panel open',role:'dialog','aria-modal':'true','aria-label':api.t('Mobile navigation')||'Mobile navigation'}, h('div',{className:'mobile-panel-head d-flex justify-content-between align-items-center'}, h('a',{href:api.href('#home'),className:'mobile-panel-brand',onClick:()=>setOpen(false)}, data.header.logoImage ? h('img',{src:mediaUrl(data.header.logoImage),alt:data.settings.siteName,loading:'eager',decoding:'async'}) : h('strong',null,data.settings.siteName)), h('button',{className:'icon-btn',type:'button','aria-label':api.t('Close menu')||'Close menu',onClick:()=>setOpen(false)},icon('bi bi-x-lg'))), h('nav',{className:'mobile-nav','aria-label':api.t('Mobile navigation')||'Mobile navigation'},renderMenu(menu,true)), admin && h('button',{className:'btn-ghost w-100 mt-3',onClick:()=>{api.setAdminOpen(true);setOpen(false)}},'Admin settings'))
      ),document.body);
      return h(React.Fragment,null,
        h('header',{className:'site-header'}, h(Container,{api,className:'header-inner py-3 d-flex align-items-center justify-content-between'},
          h('a',{href:api.href('#home'),className:'brand-mark'}, data.header.logoImage ? h('img',{src:mediaUrl(data.header.logoImage),alt:data.settings.siteName,loading:'eager',decoding:'async',fetchPriority:'high'}) : h('span',{className:'brand-dot'}, data.settings?.branding?.placeholderMark||'LP'), h(E,{path:'header.logoText'})),
          h('nav',{className:'main-nav'}, renderMenu(menu)),
          h('div',{className:'nav-actions'}, h('button',{className:'icon-btn',onClick:()=>setModal({type:'search'}),'aria-label':'Search'},icon('bi bi-search')), languagePicker, h('button',{className:'icon-btn hamburger',onClick:()=>setOpen(true), 'aria-label':'Open menu'},icon('bi bi-list')))
        )),
        mobileMenu
      );
    }

    function HeroParticles(){
      const ref=useRef(null);
      useEffect(()=>{
        const canvas=ref.current;if(!canvas)return;
        const ctx=canvas.getContext('2d',{alpha:true});if(!ctx)return;
        const host=canvas.closest('.hero')||canvas;
        const reduce=window.matchMedia&&window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let raf=0,w=0,hgt=0,dpr=1,nodes=[],brand='#0d6efd',brand2='#6610f2',gridStep=84;
        const pointer={x:0,y:0,active:false};
        const rand=(a,b)=>a+Math.random()*(b-a);
        const readTheme=()=>{const cs=getComputedStyle(canvas);brand=cs.getPropertyValue('--cms-brand').trim()||'#0d6efd';brand2=cs.getPropertyValue('--cms-brand2').trim()||'#6610f2';};
        const reset=()=>{
          const r=canvas.getBoundingClientRect();w=Math.max(1,r.width);hgt=Math.max(1,r.height);dpr=Math.min(window.devicePixelRatio||1,1.5);
          canvas.width=Math.round(w*dpr);canvas.height=Math.round(hgt*dpr);ctx.setTransform(dpr,0,0,dpr,0,0);readTheme();
          gridStep=Math.max(72,Math.min(96,w/14));
          const count=Math.max(34,Math.min(82,Math.round(w/22)));
          nodes=Array.from({length:count},(_,i)=>({
            x:rand(0,w),y:rand(0,hgt),vx:rand(-.11,.11),vy:rand(-.085,.085),
            r:i%10===0?rand(2.8,3.8):rand(1.15,2.15),phase:rand(0,Math.PI*2),hub:i%10===0
          }));
        };
        const stroke=(colour,alpha,width=1)=>{ctx.strokeStyle=colour;ctx.globalAlpha=alpha;ctx.lineWidth=width;};
        const fill=(colour,alpha)=>{ctx.fillStyle=colour;ctx.globalAlpha=alpha;};
        const drawGrid=()=>{
          ctx.save();stroke(brand,.027,.7);ctx.beginPath();
          for(let x=gridStep*.5;x<w;x+=gridStep){ctx.moveTo(x,0);ctx.lineTo(x,hgt)}
          for(let y=gridStep*.5;y<hgt;y+=gridStep){ctx.moveTo(0,y);ctx.lineTo(w,y)}
          ctx.stroke();ctx.restore();
        };
        const draw=(t=0)=>{
          raf=0;ctx.clearRect(0,0,w,hgt);drawGrid();
          const maxDist=Math.min(176,Math.max(118,w*.125));
          const mouseDist=Math.min(250,Math.max(190,w*.18));
          for(let i=0;i<nodes.length;i++){
            const a=nodes[i];
            if(!reduce){
              a.x+=a.vx+Math.sin(t*.00035+a.phase)*.012;a.y+=a.vy+Math.cos(t*.00031+a.phase)*.01;
              if(a.x<-24)a.x=w+24;if(a.x>w+24)a.x=-24;if(a.y<-24)a.y=hgt+24;if(a.y>hgt+24)a.y=-24;
            }
            for(let j=i+1;j<nodes.length;j++){
              const b=nodes[j],dx=a.x-b.x,dy=a.y-b.y,dist=Math.hypot(dx,dy);
              if(dist>=maxDist)continue;
              const proximity=1-dist/maxDist;
              stroke((a.hub||b.hub)?brand2:brand,.035+proximity*.16,.65+proximity*.42);
              ctx.beginPath();ctx.moveTo(a.x,a.y);ctx.lineTo(b.x,b.y);ctx.stroke();
              if(!reduce&&dist<maxDist*.78&&((i*11+j*7)%29===0)){
                const progress=(t*.000075+(i+j)*.071)%1;
                const px=a.x+(b.x-a.x)*progress,py=a.y+(b.y-a.y)*progress;
                fill(brand2,.24+proximity*.28);ctx.beginPath();ctx.arc(px,py,1.45,0,Math.PI*2);ctx.fill();
              }
            }
          }
          if(pointer.active){
            nodes.forEach(n=>{
              const dx=n.x-pointer.x,dy=n.y-pointer.y,dist=Math.hypot(dx,dy);if(dist>=mouseDist)return;
              const proximity=1-dist/mouseDist;
              stroke(n.hub?brand2:brand,.035+proximity*.24,.72+proximity*.55);ctx.beginPath();ctx.moveTo(n.x,n.y);ctx.lineTo(pointer.x,pointer.y);ctx.stroke();
            });
            stroke(brand,.13,1);ctx.beginPath();ctx.arc(pointer.x,pointer.y,9+Math.sin(t*.002)*1.5,0,Math.PI*2);ctx.stroke();
            fill(brand2,.22);ctx.beginPath();ctx.arc(pointer.x,pointer.y,1.8,0,Math.PI*2);ctx.fill();
          }
          nodes.forEach((n,i)=>{
            const pulse=reduce?0:(Math.sin(t*.0011+n.phase)+1)*.5;
            if(n.hub){
              stroke(brand2,.07+pulse*.075,1);ctx.beginPath();ctx.arc(n.x,n.y,n.r+5+pulse*4.5,0,Math.PI*2);ctx.stroke();
              stroke(brand,.055+pulse*.04,.8);ctx.beginPath();ctx.moveTo(n.x-8-pulse*2,n.y);ctx.lineTo(n.x-4,n.y);ctx.moveTo(n.x+4,n.y);ctx.lineTo(n.x+8+pulse*2,n.y);ctx.moveTo(n.x,n.y-8-pulse*2);ctx.lineTo(n.x,n.y-4);ctx.moveTo(n.x,n.y+4);ctx.lineTo(n.x,n.y+8+pulse*2);ctx.stroke();
            }
            fill(i%5===0?brand2:brand,n.hub?.58:.34);ctx.beginPath();ctx.arc(n.x,n.y,n.r+(n.hub?pulse*.28:0),0,Math.PI*2);ctx.fill();
          });
          ctx.globalAlpha=1;
          if(!reduce&&!document.hidden)raf=requestAnimationFrame(draw);
        };
        const redraw=()=>{cancelAnimationFrame(raf);reset();draw(0)};
        const onPointer=e=>{const r=canvas.getBoundingClientRect();pointer.x=e.clientX-r.left;pointer.y=e.clientY-r.top;pointer.active=pointer.x>=0&&pointer.y>=0&&pointer.x<=r.width&&pointer.y<=r.height};
        const onLeave=()=>{pointer.active=false};
        const onVisibility=()=>{cancelAnimationFrame(raf);raf=0;if(!document.hidden)draw(performance.now())};
        reset();draw(0);
        const ro=typeof ResizeObserver!=='undefined'?new ResizeObserver(redraw):null;if(ro)ro.observe(canvas);
        const onResize=()=>{if(!ro)redraw()};
        window.addEventListener('resize',onResize,{passive:true});host.addEventListener('pointermove',onPointer,{passive:true});host.addEventListener('pointerleave',onLeave,{passive:true});document.addEventListener('visibilitychange',onVisibility);
        return()=>{cancelAnimationFrame(raf);if(ro)ro.disconnect();window.removeEventListener('resize',onResize);host.removeEventListener('pointermove',onPointer);host.removeEventListener('pointerleave',onLeave);document.removeEventListener('visibilitychange',onVisibility)};
      },[]);
      return h('canvas',{ref,className:'hero-particles','aria-hidden':'true'});
    }

    function Hero({api}){
      const {data}=api; const slides=data.hero?.slides||[]; const offers=data.hero?.offers||[]; const promo=data.hero?.lpcmsPromo||{}; const E=api.Editable; const swiperEl=useRef(null);
      useEffect(()=>{
        const el=document.getElementById('home'); if(!el)return; let raf=0;
        const update=()=>{raf=0;const rect=el.getBoundingClientRect();const vh=window.innerHeight||800;const progress=Math.max(-1,Math.min(1,(vh/2-(rect.top+rect.height/2))/Math.max(vh,rect.height)));el.style.setProperty('--hero-parallax-y',(progress*34).toFixed(1)+'px');};
        const onScroll=()=>{if(!raf)raf=requestAnimationFrame(update)}; update(); window.addEventListener('scroll',onScroll,{passive:true}); window.addEventListener('resize',onScroll,{passive:true});
        return()=>{if(raf)cancelAnimationFrame(raf);window.removeEventListener('scroll',onScroll);window.removeEventListener('resize',onScroll)};
      },[]);
      useEffect(()=>{
        if(!swiperEl.current||!window.Swiper||!slides.length)return;
        const inst=new window.Swiper(swiperEl.current,{
          loop:slides.length>1,
          speed:760,
          grabCursor:slides.length>1,
          keyboard:{enabled:true},
          autoplay:slides.length>1?{delay:5600,disableOnInteraction:false,pauseOnMouseEnter:true}:false,
          pagination:{el:swiperEl.current.querySelector('.hero-swiper-pagination'),clickable:true},
          navigation:{nextEl:swiperEl.current.querySelector('.hero-swiper-next'),prevEl:swiperEl.current.querySelector('.hero-swiper-prev')},
          a11y:{enabled:true}
        });
        return()=>{try{inst.destroy(true,true)}catch(e){}};
      },[slides.length,api.lang]);
      function slideMedia(slide,i){
        const mediaType=String(slide.mediaType||((slide.video||'')?'video':'image')).toLowerCase(); const videoSrc=mediaUrl(slide.video||''); const poster=mediaUrl(slide.videoPoster||slide.image||'');
        if(mediaType==='video'&&videoSrc){
          return h('div',{className:'hero-card p-2 hero-media-card'},h('div',{className:'hero-video-wrap'},h('video',{className:'hero-video',src:videoSrc,poster,autoPlay:slide.videoAutoplay!==false,loop:slide.videoLoop!==false,muted:true,playsInline:true,preload:'metadata','aria-label':api.t('Website development workflow demo')})),h('div',{className:'hero-video-steps'},h('div',{className:'hero-video-step'},icon('bi bi-pencil-square'),h('span',null,api.t('Edit content'))),h('div',{className:'hero-video-step'},icon('bi bi-image'),h('span',null,api.t('Add images'))),h('div',{className:'hero-video-step'},icon('bi bi-grid'),h('span',null,api.t('Change icons'))),h('div',{className:'hero-video-step'},icon('bi bi-check2-circle'),h('span',null,api.t('Save & publish')))));
        }
        return h('div',{className:'hero-card p-2'},h(EditableImage,{api,path:`hero.slides.${i}.image`,alt:slide.title||'',className:'hero-img',eager:i===0}));
      }
      const offerGrid=offers.length?h('div',{className:'hero-offer-grid'},offers.map((o,i)=>{const external=/^https?:\/\//i.test(String(o.href||''));return h('a',{key:i,className:'hero-offer-card',href:o.href||'#services',target:external?'_blank':undefined,rel:external?'noopener noreferrer':undefined},h('span',{className:'hero-offer-icon'},icon(o.icon||'bi bi-arrow-up-right')),h('span',null,h('strong',null,api.t(o.title||'')),h('span',null,api.t(o.text||''))))})):null;
      const lpcmsPromo=promo.enabled!==false&&promo.title?h('aside',{className:'lpcms-hero-promo'},
        h('div',{className:'lpcms-promo-icon'},icon(promo.icon||'bi bi-lightning-charge-fill')),
        h('div',{className:'lpcms-promo-copy'},h(E,{path:'hero.lpcmsPromo.eyebrow',tag:'span',className:'lpcms-promo-eyebrow'}),h(E,{path:'hero.lpcmsPromo.title',tag:'strong'}),h(E,{path:'hero.lpcmsPromo.text',tag:'p'})),
        h('a',{className:'btn-cms lpcms-promo-btn',href:promo.href||'https://lpcms.dev4.click/',target:'_blank',rel:'noopener noreferrer'},h(E,{path:'hero.lpcmsPromo.buttonText'}),icon('bi bi-arrow-up-right'))
      ):null;
      return h('section',{id:'home',className:'hero section'},h('div',{className:'hero-parallax-bg','aria-hidden':'true'}),h(HeroParticles),h(Container,{api},
        h('div',{className:'swiper hero-swiper',ref:swiperEl},
          h('div',{className:'swiper-wrapper'},slides.map((slide,i)=>h('div',{className:'swiper-slide',key:i},
            h('div',{className:'hero-slide-grid'},
              h('div',{className:'hero-copy-col'},h('div',{className:'hero-slide-copy'},
                h('div',{className:'eyebrow text-primary fw-bold mb-3'},h(E,{path:`hero.slides.${i}.eyebrow`})),
                h(E,{path:`hero.slides.${i}.title`,tag:i===0?'h1':'h2',className:'hero-slide-title'}),
                h(E,{path:`hero.slides.${i}.text`,tag:'p',className:'my-4'}),
                h('a',{className:'btn-cms',href:slide.buttonHref||'#contact'},h(E,{path:`hero.slides.${i}.buttonText`}))
              )),
              h('div',{className:'hero-media-col'},slideMedia(slide,i))
            )
          ))),
          slides.length>1&&h('div',{className:'hero-swiper-controls'},
            h('button',{className:'hero-swiper-prev',type:'button','aria-label':api.t('Previous')||'Previous'},icon('bi bi-arrow-left')),
            h('div',{className:'hero-swiper-pagination'}),
            h('button',{className:'hero-swiper-next',type:'button','aria-label':api.t('Next')||'Next'},icon('bi bi-arrow-right'))
          )
        ),
        offerGrid,
        lpcmsPromo
      ));
    }

    function About({api}){ const d=api.data.about, E=api.Editable; return h('section',{id:'about',className:'section'}, h(Container,{api}, h('div',{className:'row align-items-center g-5'}, h('div',{className:'col-lg-6'}, h('div',{className:'about-team-visual-wrap'}, h(EditableImage,{api,path:'about.image',alt:d.title||'',className:'feature-panel about-team-visual'}), h('span',{className:'about-float-badge badge-wp'},icon('bi bi-wordpress'),' WordPress'), h('span',{className:'about-float-badge badge-react'},icon('bi bi-filetype-jsx'),' React'), h('span',{className:'about-float-badge badge-ai'},icon('bi bi-stars'),' AI workflows'))), h('div',{className:'col-lg-6'}, h(E,{path:'about.preheader',tag:'div',className:'eyebrow mb-2',placeholder:api.t('About')}), h(E,{path:'about.title',tag:'h2',className:'display-5'}), h(E,{path:'about.subtitle',tag:'p',className:'lead muted'}), h(E,{path:'about.body',tag:'div',html:true,className:'fs-5'}))))); }

    function Stats({api}){ const E=api.Editable; const items=api.data.stats?.items||[]; return h('section',{id:'stats',className:'section pt-0'}, h(Container,{api}, h(SectionTitle,{api,base:'stats'}), h('div',{className:'row g-4'}, items.map((it,i)=>h('div',{className:'col-6 col-lg-3',key:i}, h('div',{className:'cms-card stat-card'}, h(E,{path:`stats.items.${i}.number`,tag:'div',className:'stat-number'}), h(E,{path:`stats.items.${i}.label`,tag:'p',className:'muted mb-0'}))))))); }

    function FeatureBlocks({api}){ const E=api.Editable; const d=api.data.features||{}; return h('section',{id:'features',className:'section cms-soft'}, h(Container,{api}, h('div',{className:'row g-4 g-xl-5 align-items-center features-top-row'}, h('div',{className:'col-lg-5'}, h('div',{className:'eyebrow mb-2'},'Features'), h(E,{path:'features.title',tag:'h2',className:'display-5'}), h(E,{path:'features.subtitle',tag:'p',className:'lead muted mb-0'})), h('div',{className:'col-lg-7'}, h(EditableImage,{api,path:'features.image',alt:d.title||'',className:'feature-panel feature-panel-compact'}))), h('div',{className:'row g-3 g-xl-4 mt-2 feature-grid-row'}, (d.items||[]).map((it,i)=>h('div',{className:'col-md-6 col-xl-4',key:i}, h('article',{className:'cms-card feature-card-compact d-flex gap-3 h-100'}, h(EditableImage,{api,path:`features.items.${i}.image`,iconPath:`features.items.${i}.icon`,alt:it.title||'',className:'cms-icon flex-shrink-0 cms-image-icon',fallback:icon(it.icon,'bi bi-check2-circle')}), h('div',null, h(E,{path:`features.items.${i}.title`,tag:'h3',className:'h5'}), h(E,{path:`features.items.${i}.text`,tag:'p',className:'muted mb-0'})))))))); }

    function LoadMoreGrid({api,base,render,columns}){ const section=api.data[base]||{}; const startCount=Math.max(1,Number(section.homeLimit||section.batch||3)); const addCount=Math.max(1,Number(section.batch||startCount)); const items=section.items||[]; const [count,setCount]=useState(startCount); useEffect(()=>setCount(startCount),[base,startCount]); return h(React.Fragment,null, h('div',{className:'row g-4'}, items.slice(0,count).map((item,i)=>render(item,i,columns))), section.showButton && count<items.length && h('div',{className:'load-more-wrap'}, h('button',{className:'btn-cms',onClick:()=>setCount(c=>Math.min(items.length,c+addCount))}, api.localizedValue(base+'.buttonText')||'Load more'))); }

    function Services({api}){ const E=api.Editable; const serviceItems=api.data.services?.items||[]; return h('section',{id:'services',className:'section cms-soft'}, h(Container,{api}, h(SectionTitle,{api,base:'services'}), h(LoadMoreGrid,{api,base:'services',render:(it,i)=>{const external=/^https?:\/\//i.test(String(it.buttonHref||''));const wide=i===serviceItems.length-1;return h('div',{className:wide?'col-12 service-wide-card':'col-md-6 col-lg-4',key:i,'data-aos':'fade-up'}, h('article',{className:'cms-card'}, h(EditableImage,{api,path:`services.items.${i}.image`,iconPath:`services.items.${i}.icon`,alt:it.title||'',className:'cms-icon cms-image-icon',fallback:icon(it.icon,'bi bi-stars')}), h(E,{path:`services.items.${i}.title`,tag:'h3',className:'h4 fw-bold'}), h(E,{path:`services.items.${i}.text`,tag:'p',className:'muted'}), it.buttonText && h('a',{className:'btn-ghost mt-2',href:it.buttonHref||'#contact',target:external?'_blank':undefined,rel:external?'noopener noreferrer':undefined}, h(E,{path:`services.items.${i}.buttonText`})) ))}}))); }

    function Gallery({api}){
      const cfg=api.data.gallery||{}, E=api.Editable, items=cfg.items||[], categories=cfg.categories||[];
      const [cat,setCat]=useState('all'); const startCount=Math.max(1,Number(cfg.homeLimit||9)); const batch=Math.max(1,Number(cfg.batch||3)); const [count,setCount]=useState(startCount);
      useEffect(()=>setCount(startCount),[cat,startCount,items.length]);
      const filtered=cat==='all'?items:items.filter(it=>it.category===cat);
      const categoryTitle=slug=>{const c=categories.find(x=>x.slug===slug);return c?api.t(c.title||slug):api.t(slug||'');};
      return h('section',{id:'gallery',className:'section'}, h(Container,{api},
        h(SectionTitle,{api,base:'gallery'}),
        h('div',{className:'portfolio-toolbar','aria-label':api.t('Portfolio filters')},categories.map((c,i)=>h('button',{key:c.slug||i,type:'button',className:cls('portfolio-filter',(c.slug||'all')===cat&&'active'),onClick:()=>setCat(c.slug||'all')},api.t(c.title||c.slug||'All work')))),
        h('div',{className:'portfolio-count'},filtered.length,' ',api.t(filtered.length===1?'project':'projects')),
        h('div',{className:'row g-4'},filtered.slice(0,count).map(it=>{const i=items.indexOf(it);return h('div',{className:'col-md-6 col-lg-4',key:it.slug||i},h('article',{className:'cms-card portfolio-card'},h('div',{className:'portfolio-image-wrap'},h(EditableImage,{api,path:`gallery.items.${i}.image`,alt:api.localizedValue(`gallery.items.${i}.alt`)||it.alt||it.title||'',className:'portfolio-image',target:'gallery'})),h('div',{className:'portfolio-card-body'},h('div',{className:'portfolio-meta'},h('span',{className:'portfolio-kind'},categoryTitle(it.category)||api.t(it.kind||'')),h('span',{className:cls('portfolio-status',String(it.status||'').toLowerCase().includes('development')&&'is-development')},api.t(it.status||'Live project'))),h(E,{path:`gallery.items.${i}.title`,tag:'h3',className:'h4 fw-bold'}),h(E,{path:`gallery.items.${i}.text`,tag:'p',className:'muted mb-0'}),Array.isArray(it.tech)&&h('div',{className:'tech-chips'},it.tech.map((t,j)=>h('span',{key:j,className:'tech-chip'},t))),h('div',{className:'portfolio-actions'},h('button',{type:'button',className:'btn-ghost',onClick:()=>api.setModal({type:'item',group:'gallery',index:i})},api.t('View project'))))))})),
        cfg.showButton&&count<filtered.length&&h('div',{className:'load-more-wrap'},h('button',{className:'btn-cms',onClick:()=>setCount(c=>Math.min(filtered.length,c+batch))},api.localizedValue('gallery.buttonText')||api.t('Load more projects')))
      ));
    }

    function Testimonials({api}){ const E=api.Editable; return h('section',{id:'testimonials',className:'section cms-soft'}, h(Container,{api}, h(SectionTitle,{api,base:'testimonials'}), h(LoadMoreGrid,{api,base:'testimonials',render:(it,i)=>h('div',{className:'col-md-6 col-lg-4',key:i}, h('article',{className:'cms-card'}, h('div',{className:'d-flex align-items-center gap-3 mb-3'}, h(EditableImage,{api,path:`testimonials.items.${i}.photo`,alt:it.name||'',className:'avatar cms-image-avatar',fallback:icon('bi bi-person')}), h('div',null,h(E,{path:`testimonials.items.${i}.name`,tag:'strong'}), h(E,{path:`testimonials.items.${i}.role`,tag:'div',className:'small muted'}))), h(E,{path:`testimonials.items.${i}.text`,tag:'p',className:'mb-0'}))) }))); }

    function FAQ({api}){ const E=api.Editable; const [open,setOpen]=useState(0); return h('section',{id:'faq',className:'section'}, h(Container,{api}, h(SectionTitle,{api,base:'faq'}), h('div',{className:'mx-auto',style:{maxWidth:860}}, (api.data.faq?.items||[]).map((it,i)=>h('div',{className:'faq-item',key:i}, h('button',{className:'faq-q',onClick:()=>setOpen(open===i?null:i)}, h(E,{path:`faq.items.${i}.question`}), h('span',null,open===i?'−':'+')), open===i&&h('div',{className:'faq-a'}, h(E,{path:`faq.items.${i}.answer`,tag:'div',html:true}))))))); }

    function Process({api}){ const E=api.Editable; return h('section',{id:'process',className:'section'}, h(Container,{api}, h(SectionTitle,{api,base:'process'}), h('div',{className:'row justify-content-center'}, h('div',{className:'col-lg-8'}, (api.data.process?.items||[]).map((it,i)=>h('article',{className:'timeline-step d-flex gap-3 mb-4',key:i}, h(EditableImage,{api,path:`process.items.${i}.image`,iconPath:`process.items.${i}.icon`,alt:it.title||'',className:'cms-icon flex-shrink-0 cms-image-icon',fallback:icon(it.icon,'bi bi-check')}), h('div',{className:'cms-card flex-fill'}, h('div',{className:'small muted mb-1'},'0'+(i+1)), h(E,{path:`process.items.${i}.title`,tag:'h3',className:'h4'}), h(E,{path:`process.items.${i}.text`,tag:'p',className:'muted mb-0'})))))))); }

    function ImageCards({api}){
      const d=api.data.cards||{}, E=api.Editable;
      const cols=Math.max(2,Math.min(4,Number(d.columns||3)));
      return h('section',{id:'cards',className:'section'},
        h(Container,{api},
          h(SectionTitle,{api,base:'cards'}),
          h('div',{className:'row g-4'},(d.items||[]).map((it,i)=>{
            const external=/^https?:\/\//i.test(String(it.buttonHref||''));
            return h('div',{className:`col-md-6 col-lg-${12/cols}`,key:i},
              h('article',{className:'cms-card'},
                h(EditableImage,{api,path:`cards.items.${i}.image`,iconPath:`cards.items.${i}.icon`,alt:it.title||'',className:it.image?'image-card-media mb-3':'cms-icon cms-image-icon mb-3',fallback:icon(it.icon||'lucide:layout-template')}),
                h(E,{path:`cards.items.${i}.title`,tag:'h3',className:'h5'}),
                h(E,{path:`cards.items.${i}.text`,tag:'p',className:'muted'}),
                it.buttonText&&h('a',{className:'btn-ghost',href:it.buttonHref||'#contact',target:external?'_blank':undefined,rel:external?'noopener noreferrer':undefined},h(E,{path:`cards.items.${i}.buttonText`}))
              )
            );
          }))
        )
      );
    }

    function ContentList({api}){
      const d=api.data.contentList||{}, E=api.Editable; const items=d.items||[]; const [open,setOpen]=useState(false);
      function openEncoded(encoded){ if(!encoded)return; try{const url=atob(String(encoded));if(!/^https?:\/\//i.test(url))return;const win=window.open(url,'_blank','noopener,noreferrer');if(win)win.opener=null;}catch(e){} }
      return h('section',{id:'content-list',className:'section cms-soft','data-nosnippet':''},h(Container,{api},
        h(SectionTitle,{api,base:'contentList'}),
        h('div',{className:'agency-exp-accordion','data-nosnippet':''},
          h('button',{type:'button',className:'agency-exp-toggle','aria-expanded':open,onClick:()=>setOpen(v=>!v)},
            h('span',null,api.t(d.accordionLabel||'View selected UK agency projects'),h('span',{className:'agency-exp-count'},items.length-1,'+')),
            icon(open?'bi bi-chevron-up':'bi bi-chevron-down')
          ),
          open&&h('div',{className:'agency-exp-panel','data-nosnippet':''},
            h('p',{className:'agency-exp-note'},api.t(d.privacyNote||'')),
            h('div',{className:'agency-exp-grid'},items.map((it,i)=>h('button',{type:'button',key:i,className:cls('agency-exp-item',it.urlEncoded&&'can-open'),onClick:it.urlEncoded?()=>openEncoded(it.urlEncoded):undefined,'aria-label':it.urlEncoded?(api.t('Open')+' '+(it.title||'')):undefined},
              h('span',null,h(E,{path:`contentList.items.${i}.title`,tag:'strong'}),h(E,{path:`contentList.items.${i}.text`,tag:'small'})),
              icon(it.urlEncoded?'bi bi-box-arrow-up-right':'bi bi-plus-circle')
            )))
          )
        )
      ));
    }

    function GrowthMarketing({api}){
      const d=api.data.growth||{}, E=api.Editable; const items=d.items||[];
      return h('section',{id:'growth',className:'section growth-section'},h(Container,{api},
        h(SectionTitle,{api,base:'growth'}),
        h('div',{className:'row g-4 growth-grid'},items.map((it,i)=>h('div',{className:'col-md-6 col-xl-4',key:i},
          h('article',{className:'cms-card growth-card h-100'},
            h('span',{className:'growth-icon'},icon(it.icon||'bi bi-graph-up-arrow')),
            h(E,{path:`growth.items.${i}.title`,tag:'h3',className:'h5 fw-bold'}),
            h(E,{path:`growth.items.${i}.text`,tag:'p',className:'muted mb-0'}),
            Array.isArray(it.points)&&it.points.length?h('ul',{className:'growth-points'},it.points.map((point,j)=>h('li',{key:j},icon('bi bi-check2'),h(E,{path:`growth.items.${i}.points.${j}`})))):null
          )
        ))),
        h('div',{className:'growth-note cms-card'},h('div',null,h(E,{path:'growth.noteTitle',tag:'strong'}),h(E,{path:'growth.noteText',tag:'p',className:'muted mb-0'})),h('a',{className:'btn-cms',href:'#contact'},h(E,{path:'growth.buttonText'})))
      ));
    }

    function Industries({api}){
      const d=api.data.industries||{}, E=api.Editable; const items=d.items||[];
      return h('section',{id:'industries',className:'section cms-soft industries-section'},h(Container,{api},
        h(SectionTitle,{api,base:'industries'}),
        h('div',{className:'industry-grid'},items.map((it,i)=>h('article',{className:'cms-card industry-card',key:i},
          h('div',{className:'industry-card-top'},h('span',{className:'industry-icon'},icon(it.icon||'bi bi-buildings')),h(E,{path:`industries.items.${i}.title`,tag:'h3',className:'h5 fw-bold mb-0'})),
          h(E,{path:`industries.items.${i}.text`,tag:'p',className:'muted'}),
          Array.isArray(it.solutions)&&it.solutions.length?h('div',{className:'industry-solutions'},it.solutions.map((x,j)=>h('span',{key:j},x))):null
        ))),
        h('div',{className:'industries-footer'},h(E,{path:'industries.footerText',tag:'p',className:'mb-0'}),h('a',{className:'btn-ghost',href:'#contact'},h(E,{path:'industries.buttonText'})))
      ));
    }

    function TeamLead({api}){
      const d=api.data.team||{}, E=api.Editable; const [open,setOpen]=useState(0); const gallery=api.data.gallery?.items||[];
      const projectsFor=it=>(it.portfolioIndexes||[]).map(idx=>({it:gallery[idx],idx})).filter(x=>x.it);
      return h('section',{id:'team',className:'section'},h(Container,{api},
        h(SectionTitle,{api,base:'team'}),
        h('div',{className:'team-lead-shell'},
          h('aside',{className:'team-lead-profile cms-card'},
            h('div',{className:'team-photo-wrap'},h(EditableImage,{api,path:'team.photo',alt:d.name||'Team lead',className:'team-photo'})),
            h(E,{path:'team.name',tag:'h3',className:'h3 mb-1'}),
            h(E,{path:'team.role',tag:'div',className:'team-role'}),
            h(E,{path:'team.summary',tag:'p',className:'muted team-summary'}),
            h('div',{className:'team-tech-chips'},(d.technologies||[]).map((tech,i)=>h('span',{key:i},tech))),
            d.linkedin&&h('a',{className:'btn-cms team-linkedin',href:d.linkedin,target:'_blank',rel:'noopener noreferrer'},icon('bi bi-linkedin'),h(E,{path:'team.buttonText'}))
          ),
          h('div',{className:'team-accordion'},(d.items||[]).map((it,i)=>{
            const isOpen=open===i; const projects=it.type==='portfolio'?projectsFor(it):[]; const agencies=it.type==='agency-portfolio'?(it.agencyItems||[]):[];
            return h('article',{className:cls('team-accordion-item',isOpen&&'open'),key:i},
              h('button',{type:'button',className:'team-accordion-toggle','aria-expanded':isOpen,onClick:()=>setOpen(isOpen?null:i)},
                h('span',{className:'team-accordion-icon'},icon(it.icon||'bi bi-plus-circle')),
                h(E,{path:`team.items.${i}.title`,tag:'strong'}),
                icon(isOpen?'bi bi-dash-lg':'bi bi-plus-lg')
              ),
              isOpen&&h('div',{className:'team-accordion-body',...(agencies.length?{'data-nosnippet':''}:{})},
                h(E,{path:`team.items.${i}.text`,tag:'div',html:true,className:agencies.length?'team-agency-note':'muted'}),
                agencies.length>0&&h('div',{className:'team-agency-grid'},agencies.map((agency,j)=>{const canOpen=!!agency.urlEncoded;return h('button',{type:'button',key:j,className:cls('team-agency-btn',canOpen&&'can-open'),disabled:!canOpen,onClick:()=>{if(!canOpen)return;try{const url=window.atob(agency.urlEncoded);window.open(url,'_blank','noopener,noreferrer')}catch(e){}}},h('span',null,agency.title),icon(canOpen?'bi bi-box-arrow-up-right':'bi bi-plus-circle'));})),
                projects.length>0&&h('div',{className:'team-mini-portfolio'},projects.map(({it:p,idx})=>h('button',{type:'button',className:'team-mini-project',key:idx,onClick:()=>api.setModal({type:'item',group:'gallery',index:idx})},
                  h('img',{src:mediaUrl(p.image),alt:api.localizedValue(`gallery.items.${idx}.alt`)||p.title||'',loading:'lazy',decoding:'async'}),
                  h('span',null,h('strong',null,api.localizedValue(`gallery.items.${idx}.title`)||p.title),h('small',null,api.localizedValue(`gallery.items.${idx}.kind`)||p.kind||p.category||''))
                )))
              )
            );
          }))
        )
      ));
    }

    function LogoStrip({api}){
      const d=api.data.logos||{}, E=api.Editable, items=d.items||[];
      const group=(copyKey)=>h('div',{className:'tech-marquee-group','aria-hidden':copyKey==='copy'?true:undefined,key:copyKey},items.map((it,i)=>h('a',{className:'tech-pill',href:it.href||'#features',key:copyKey+'-'+i,tabIndex:copyKey==='copy'?-1:undefined},it.icon?icon(it.icon):icon('bi bi-code-slash'),h(E,{path:`logos.items.${i}.label`,tag:'span'}))));
      return h('section',{id:'logos',className:'section tech-stack-strip'},h(Container,{api},
        h('div',{className:'tech-stack-head'},
          h('div',null,h(E,{path:'logos.preheader',tag:'div',className:'eyebrow'}),h(E,{path:'logos.title',tag:'h2'})),
          h(E,{path:'logos.subtitle',tag:'p'})
        ),
        h('div',{className:'tech-swiper tech-marquee','aria-label':api.localizedValue('logos.title')||'Technology stack'},
          h('div',{className:'tech-marquee-track'},group('main'),group('copy'))
        )
      ));
    }
    function ProductPrice({api,item,path,variation=null}){ const price=api.productPriceValue(item,variation); const old=api.productOldPriceValue(item,variation); return h('div',{className:'d-flex align-items-center gap-2 flex-wrap mb-3'}, h('span',{className:'product-price'}, api.formatProductPrice(item,variation)), old>price&&price>0&&h('span',{className:'old-price'}, new Intl.NumberFormat(undefined,{style:'currency',currency:variation?.currency||item.currency||api.data.products?.currency||'EUR'}).format(old)), item.discountLabel&&h('span',{className:'discount-badge'},item.discountLabel)); }
    function ProductButtons({api,item,variation=null,qty=1}){ const price=api.productPriceValue(item,variation); const cfg=api.data.products||{}; const vars=api.productVariationList(item); const needsChoice=vars.length>0 && !variation; const mode=price<=0?'quote':String(item.purchaseMode||cfg.defaultPurchaseMode||'quote'); const showBuy=mode==='checkout'&&cfg.showBuyButton&&item.buyButton!==false&&price>0; const showQuote=mode==='quote'&&cfg.showQuoteButton&&item.quoteButton!==false; const direct=String(item.checkoutUrl||'').trim(); const idx=(cfg.items||[]).indexOf(item); const itemButton=idx>=0?api.localizedValue(`products.items.${idx}.checkoutButtonText`):''; const buyLabel=itemButton||api.localizedValue('products.buyButtonText')||'Checkout now'; const quoteLabel=api.localizedValue('products.quoteButtonText')||'Add to quote'; return h('div',{className:'product-actions'}, showBuy&&(direct&&!needsChoice?h('a',{className:'btn-cms',href:direct,target:'_blank',rel:'noopener noreferrer'},buyLabel):h('button',{className:'btn-cms',onClick:()=>needsChoice?api.setModal({type:'item',group:'products',index:idx}):api.addToCart(item,variation,qty)},buyLabel)), showQuote&&h('button',{className:'btn-cms',onClick:()=>api.addToQuote(item,variation,qty)},quoteLabel)); }
    function isColourKey(key){ return ['colour','color','colours','colors','tone','shade'].includes(String(key||'').toLowerCase()); }
    function colourValueCss(value){ const raw=String(value||'').trim(); const key=raw.toLowerCase().replace(/[^a-z0-9#]/g,''); if(/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i.test(raw)) return raw; const map={black:'#111827',white:'#ffffff',blue:'#2563eb',navy:'#1e3a8a',sky:'#38bdf8',red:'#ef4444',green:'#22c55e',lime:'#84cc16',yellow:'#facc15',orange:'#f97316',purple:'#8b5cf6',violet:'#7c3aed',pink:'#ec4899',rose:'#f43f5e',brown:'#92400e',beige:'#d6b98c',cream:'#fff7ed',gray:'#9ca3af',grey:'#9ca3af',silver:'#cbd5e1',gold:'#f59e0b'}; if(map[key]) return map[key]; const parts=key.match(/black|white|blue|navy|sky|red|green|lime|yellow|orange|purple|violet|pink|rose|brown|beige|cream|gray|grey|silver|gold/g); if(parts&&parts.length){ const colors=parts.map(x=>map[x]||x); return colors.length>1 ? `linear-gradient(135deg,${colors.join(',')})` : colors[0]; } let hash=0; for(let i=0;i<raw.length;i++) hash=(hash*31+raw.charCodeAt(i))>>>0; return `hsl(${hash%360} 72% 52%)`; }
    function ColourSwatch({value,selected=false,className='',title=''}){ const v=String(value||'').trim(); const low=v.toLowerCase(); return h('span',{className:cls('colour-swatch',(low==='white'||low==='cream')&&'is-white',low.includes('light')&&'is-light',selected&&'active',className),style:{background:colourValueCss(v)},title:title||v,'aria-label':title||v}, h('span',{className:'visually-hidden'},title||v)); }
    function ProductFilterDropdown({label,active,children,onClear}){ const [open,setOpen]=useState(false); const ref=useRef(null); useEffect(()=>{ if(!open) return; const close=e=>{ if(ref.current&&!ref.current.contains(e.target)) setOpen(false); }; document.addEventListener('mousedown',close); return()=>document.removeEventListener('mousedown',close); },[open]); return h('div',{className:'filter-dropdown',ref}, h('button',{type:'button',className:cls('filter-trigger',open&&'open',active&&'active'),onClick:()=>setOpen(!open),'aria-expanded':open}, h('span',null,label), active&&h('strong',null,active), icon('bi bi-chevron-down')), open&&h('div',{className:'filter-panel'}, h('div',{className:'d-flex align-items-center justify-content-between mb-2'}, h('div',{className:'filter-panel-title'},label), onClear&&h('button',{type:'button',className:'filter-clear',onClick:()=>{onClear();setOpen(false);}},'Clear')), children)); }
    function CategoryFilter({categories,value,onChange}){ const active=value!=='all' ? ((categories.find(c=>c.slug===value)||{}).title||value) : ''; return h(ProductFilterDropdown,{label:'Category',active,onClear:()=>onChange('all')}, h('div',{className:'filter-option-list'}, h('button',{type:'button',className:cls('filter-option',value==='all'&&'active'),onClick:()=>onChange('all')}, h('span',null,'All categories'), value==='all'&&icon('bi bi-check2')), categories.map(c=>h('button',{type:'button',key:c.slug,className:cls('filter-option',value===c.slug&&'active'),onClick:()=>onChange(c.slug)}, h('span',null,c.title||c.slug), value===c.slug&&icon('bi bi-check2'))))); }
    function AttributeFilter({def,value,onChange}){ const opts=def.options||[]; if(isColourKey(def.key)) return h(ProductFilterDropdown,{label:def.label||'Colour',active:value?h('span',{className:'filter-colour-inline'},h(ColourSwatch,{value}),value):'',onClear:()=>onChange('')}, h('div',{className:'swatch-grid'}, h('button',{type:'button',className:cls('swatch-option is-all',!value&&'active'),onClick:()=>onChange('')}, 'All'), opts.map(o=>h('button',{type:'button',key:o,className:cls('swatch-option',value===o&&'active'),title:o,'aria-label':o,onClick:()=>onChange(o)}, h(ColourSwatch,{value:o,selected:value===o}), h('span',null,o))))); return h(ProductFilterDropdown,{label:def.label||def.key,active:value||'',onClear:()=>onChange('')}, h('div',{className:'filter-option-list'}, h('button',{type:'button',className:cls('filter-option',!value&&'active'),onClick:()=>onChange('')}, h('span',null,'All'), !value&&icon('bi bi-check2')), opts.map(o=>h('button',{type:'button',key:o,className:cls('filter-option',value===o&&'active'),onClick:()=>onChange(o)}, h('span',null,o), value===o&&icon('bi bi-check2'))))); }
    function PriceRangeFilter({api,items,value,onChange}){ const prices=useMemo(()=>{ const vals=[]; (items||[]).forEach(p=>{ const base=api.productPriceValue(p); if(base>0) vals.push(base); (api.productVariationList(p)||[]).forEach(v=>{ const n=api.productPriceValue(p,v); if(n>0) vals.push(n); }); }); return vals; },[items]); if(!prices.length) return null; const cur=api.data.products?.currency||'EUR'; const minLimit=Math.floor(Math.min(...prices)); const maxLimit=Math.ceil(Math.max(...prices)); const minVal=value.min===''?minLimit:Number(value.min); const maxVal=value.max===''?maxLimit:Number(value.max); const money=n=>new Intl.NumberFormat(undefined,{style:'currency',currency:cur,maximumFractionDigits:0}).format(Number(n)||0); const active=(value.min!==''||value.max!=='') ? `${money(minVal)} – ${money(maxVal)}` : ''; function setMin(v){ const n=Math.min(Number(v), maxVal); onChange({min:n,max:value.max===''?'':maxVal}); } function setMax(v){ const n=Math.max(Number(v), minVal); onChange({min:value.min===''?'':minVal,max:n}); } return h(ProductFilterDropdown,{label:'Price',active,onClear:()=>onChange({min:'',max:''})}, h('div',{className:'price-filter'}, h('div',{className:'price-filter-fields'}, h('label',null,'Min', h('input',{className:'form-control',type:'number',min:minLimit,max:maxLimit,value:minVal,onChange:e=>setMin(e.target.value)})), h('label',null,'Max', h('input',{className:'form-control',type:'number',min:minLimit,max:maxLimit,value:maxVal,onChange:e=>setMax(e.target.value)}))), h('div',{className:'range-stack'}, h('input',{type:'range',min:minLimit,max:maxLimit,value:minVal,onChange:e=>setMin(e.target.value)}), h('input',{type:'range',min:minLimit,max:maxLimit,value:maxVal,onChange:e=>setMax(e.target.value)})), h('div',{className:'price-label-row'}, h('span',null,money(minLimit)), h('span',null,money(maxLimit))))); }
    function ActiveFilterChips({q,cat,categories,attrs,price,setQ,setCat,setAttrs,setPrice}){ const chips=[]; if(q) chips.push(['Search',q,()=>setQ('')]); if(cat!=='all') chips.push(['Category',(categories.find(c=>c.slug===cat)||{}).title||cat,()=>setCat('all')]); Object.entries(attrs).forEach(([k,v])=>{ if(v) chips.push([k,v,()=>setAttrs({...attrs,[k]:''})]); }); if(price.min!==''||price.max!=='') chips.push(['Price',`${price.min||'min'} – ${price.max||'max'}`,()=>setPrice({min:'',max:''})]); if(!chips.length) return null; return h('div',{className:'active-filter-chips'}, chips.map(([k,v,clear],i)=>h('span',{className:'active-filter-chip',key:i}, h('strong',null,k+':'), String(v), h('button',{type:'button',onClick:clear,'aria-label':'Remove filter'},'×')))); }
    function VariationChoice({api,item,variation,active,onClick}){ const attrs=variation?.attributes||{}; const colourKey=Object.keys(attrs).find(isColourKey); const other=Object.entries(attrs).filter(([k])=>k!==colourKey).map(([k,v])=>v).join(' / '); return h('button',{type:'button',className:cls('variation-choice',active&&'active'),onClick,title:api.variationLabel(variation)}, colourKey&&h(ColourSwatch,{value:attrs[colourKey],selected:active,title:attrs[colourKey]}), h('small',null, other || variation.sku || variation.id || 'Option'), h('small',null,api.formatProductPrice(item,variation))); }
    function ProductBadges({item}){ const attrs=item.attributes||{}; return h('div',{className:'product-badges'}, item.id&&h('span',null,'ID ',item.id), item.sku&&h('span',null,item.sku), item.category&&h('span',null,item.category), Object.keys(attrs).slice(0,3).map(k=>isColourKey(k)?h('span',{key:k,className:'colour-badge',title:`${k}: ${attrs[k]}`},h(ColourSwatch,{value:attrs[k],title:`${k}: ${attrs[k]}`})):h('span',{key:k},k,': ',attrs[k]))); }
    function ProductCard({api,it,i}){ const E=api.Editable; return h('div',{className:'col-12 col-md-6 col-xl-3',key:i,'data-aos':'fade-up'}, h('article',{className:'cms-card product-card'}, h(EditableImage,{api,path:`products.items.${i}.image`,fallbackSrc:it.gallery?.[0]||'',alt:it.title||'',className:'product-card-img mb-3'}), it.discountLabel&&h('span',{className:'discount-badge mb-2'},h(E,{path:`products.items.${i}.discountLabel`})), h(E,{path:`products.items.${i}.title`,tag:'h3',className:'h5 fw-bold'}), h(ProductBadges,{item:it}), h(ProductPrice,{api,item:it,path:`products.items.${i}`}), h(E,{path:`products.items.${i}.text`,tag:'p',className:'muted'}), h('div',{className:'product-actions'}, h('a',{className:'btn-ghost',href:pretty('products',it),onClick:e=>{ if(!e.metaKey&&!e.ctrlKey){ e.preventDefault(); api.setModal({type:'item',group:'products',index:i}); }}},'Details')), h(ProductButtons,{api,item:it}))); }
    function Products({api}){ const cfg=api.data.products||{}; const items=cfg.items||[]; const [q,setQ]=useState(''); const [cat,setCat]=useState('all'); const [sort,setSort]=useState('default'); const [attrs,setAttrs]=useState({}); const [price,setPrice]=useState({min:'',max:''}); const start=Number(cfg.homeLimit||8); const batch=Number(cfg.batch||8); const [count,setCount]=useState(start); useEffect(()=>setCount(start),[start,items.length]); const attrDefs=useMemo(()=>{ const fromCfg=Array.isArray(cfg.attributeFilters)?cfg.attributeFilters:[]; const map={}; fromCfg.forEach(a=>{ if(a?.key) map[a.key]={key:a.key,label:a.label||a.key,options:[...(a.options||[])]}; }); items.forEach(p=>{ Object.entries(p.attributes||{}).forEach(([k,v])=>{ if(!map[k]) map[k]={key:k,label:k,options:[]}; if(v&&!map[k].options.includes(v)) map[k].options.push(v); }); (p.variations||[]).forEach(v=>Object.entries(v.attributes||{}).forEach(([k,val])=>{ if(!map[k]) map[k]={key:k,label:k,options:[]}; if(val&&!map[k].options.includes(val)) map[k].options.push(val); })); }); return Object.values(map); },[cfg.attributeFilters,items]); const categories=useMemo(()=>{ const c=(cfg.categories||[]).map(x=>typeof x==='string'?{slug:x,title:x}:x); items.forEach(p=>{ if(p.category&&!c.some(x=>x.slug===p.category)) c.push({slug:p.category,title:p.category}); }); return c; },[cfg.categories,items]); function itemText(p){ return JSON.stringify([p.id,p.sku,p.title,p.category,p.brand,p.tags,p.text,p.body,p.attributes,p.variations]).toLowerCase(); } function attrMatch(p,key,val){ if(!val) return true; if(String((p.attributes||{})[key]||'').toLowerCase()===val.toLowerCase()) return true; return (p.variations||[]).some(v=>String((v.attributes||{})[key]||'').toLowerCase()===val.toLowerCase()); } function priceMatch(p){ if(price.min===''&&price.max==='') return true; const val=api.productPriceValue(p); if(val<=0) return false; const min=price.min===''?null:Number(price.min); const max=price.max===''?null:Number(price.max); return (min===null||val>=min)&&(max===null||val<=max); } function resetAll(){ setQ(''); setCat('all'); setSort('default'); setAttrs({}); setPrice({min:'',max:''}); setCount(start); } const filtered=useMemo(()=>{ const low=q.trim().toLowerCase(); let out=[...items].reverse().filter(p=>(cat==='all'||p.category===cat) && (!low||itemText(p).includes(low)) && Object.entries(attrs).every(([k,v])=>attrMatch(p,k,v)) && priceMatch(p)); if(sort==='price-asc') out=[...out].sort((a,b)=>api.productPriceValue(a)-api.productPriceValue(b)); if(sort==='price-desc') out=[...out].sort((a,b)=>api.productPriceValue(b)-api.productPriceValue(a)); if(sort==='title') out=[...out].sort((a,b)=>String(a.title||'').localeCompare(String(b.title||''))); return out; },[items,q,cat,attrs,price.min,price.max,sort]); return h('section',{id:'products',className:'section'}, h(Container,{api}, h(SectionTitle,{api,base:'products'}), cfg.shopEnabled&&h(React.Fragment,null,
        h('div',{className:'catalog-toolbar'},
          cfg.searchEnabled!==false&&h('div',{className:'catalog-search-wrap'}, icon('bi bi-search'), h('input',{className:'form-control catalog-search',placeholder:'Search by title, ID, SKU, category, tag, colour, size...',value:q,onChange:e=>{setQ(e.target.value);setCount(start)}})),
          h('div',{className:'catalog-filter-row'},
            cfg.filterEnabled!==false&&h(React.Fragment,null,
              h(CategoryFilter,{categories,value:cat,onChange:v=>{setCat(v);setCount(start)}}),
              h(PriceRangeFilter,{api,items,value:price,onChange:v=>{setPrice(v);setCount(start)}}),
              attrDefs.map(a=>h(AttributeFilter,{key:a.key,def:a,value:attrs[a.key]||'',onChange:v=>{setAttrs({...attrs,[a.key]:v});setCount(start)}}))
            ),
            h('select',{className:'form-select catalog-select',value:sort,onChange:e=>setSort(e.target.value)}, h('option',{value:'default'},'Newest'), h('option',{value:'title'},'Title A-Z'), h('option',{value:'price-asc'},'Price low-high'), h('option',{value:'price-desc'},'Price high-low')),
            h('button',{type:'button',className:'btn-ghost filter-reset',onClick:resetAll},'Reset')
          )
        ),
        h(ActiveFilterChips,{q,cat,categories,attrs,price,setQ,setCat,setAttrs,setPrice})
      ), h('div',{className:'small muted mb-3'}, filtered.length,' product',filtered.length===1?'':'s',' found'), h('div',{className:'row g-4'}, filtered.slice(0,count).map((item)=>h(ProductCard,{api,it:item,i:items.indexOf(item),key:item.slug||item.sku||item.title}))), cfg.showButton && count<filtered.length && h('div',{className:'load-more-wrap'}, h('button',{className:'btn-cms',onClick:()=>setCount(c=>c+batch)}, cfg.buttonText||'Load more')))); }

    function HCaptchaWidget({api,onToken,className='',resetKey=0}){
      const hc=api.data.settings?.hcaptcha||{}; const host=useRef(null); const widgetId=useRef(null);
      useEffect(()=>{
        onToken?.('');
        if(!hc.enabled||!hc.siteKey||!host.current)return;
        let dead=false;
        const render=()=>{
          if(dead||widgetId.current!==null||!host.current||!window.hcaptcha)return;
          try{widgetId.current=window.hcaptcha.render(host.current,{sitekey:hc.siteKey,callback:(token)=>onToken?.(token||''),'expired-callback':()=>onToken?.(''),'error-callback':()=>onToken?.('')});}catch(e){}
        };
        render(); const timer=setInterval(render,300); const stop=setTimeout(()=>clearInterval(timer),6500);
        return()=>{dead=true;clearInterval(timer);clearTimeout(stop);if(window.hcaptcha&&widgetId.current!==null){try{window.hcaptcha.remove(widgetId.current)}catch(e){}}widgetId.current=null;};
      },[hc.enabled,hc.siteKey,api.lang]);
      useEffect(()=>{if(resetKey&&window.hcaptcha&&widgetId.current!==null){try{window.hcaptcha.reset(widgetId.current)}catch(e){}onToken?.('');}},[resetKey]);
      if(!hc.enabled||!hc.siteKey)return null;
      return h('div',{className:className||'hcaptcha-wrap',ref:host});
    }

    function Booking({api}){
      const d=api.data.booking||{};
      const services=(d.services||[]).filter(x=>x.enabled!==false);
      const [service,setService]=useState(services[0]?.id||'');
      const [date,setDate]=useState('');
      const [slots,setSlots]=useState([]);
      const [time,setTime]=useState('');
      const [form,setForm]=useState({name:'',email:'',phone:''});
      const [busy,setBusy]=useState(false);
      const [msg,setMsg]=useState('');
      const [captcha,setCaptcha]=useState(''); const [captchaReset,setCaptchaReset]=useState(0);
      const hc=api.data.settings?.hcaptcha||{};
      async function loadSlots(v,sv=service){
        setDate(v); setTime(''); setSlots([]); if(!v)return;
        try{const r=await fetch('?api=booking-slots&date='+encodeURIComponent(v)+'&serviceId='+encodeURIComponent(sv));const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Could not load times.');setSlots(j.slots||[])}catch(e){setMsg(e.message)}
      }
      async function book(e){
        e.preventDefault(); setBusy(true); setMsg('');
        try{const r=await fetch('?api=booking-create',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({...form,serviceId:service,date,time,hcaptcha:captcha})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Booking failed.');setMsg(j.message||'Booking confirmed.');setTime('');setCaptcha('');setCaptchaReset(v=>v+1);await loadSlots(date,service)}catch(e){setMsg(e.message||'Booking failed.')}finally{setBusy(false)}
      }
      const serviceCards=h('div',{className:'booking-service-grid mb-4'},services.map(sv=>{
        const meta=`${sv.durationMinutes||sv.duration||60} min${Number(sv.price||0)>0?' · '+new Intl.NumberFormat(undefined,{style:'currency',currency:d.currency||api.data.products?.currency||'EUR'}).format(Number(sv.price)):''}`;
        return h('button',{type:'button',className:cls('booking-service',service===sv.id&&'active'),key:sv.id,onClick:()=>{setService(sv.id);if(date)loadSlots(date,sv.id)}},h('strong',null,translate(api,sv,'title')||sv.name||'Service'),h('div',{className:'small muted'},meta));
      }));
      const slotList=h('div',{className:'booking-slots'},slots.length?slots.map(t=>h('button',{type:'button',className:cls('booking-slot',time===t&&'active'),key:t,onClick:()=>setTime(t)},t)):h('span',{className:'small muted'},date?'No available times for this date.':'Choose a date first.'));
      const bookingForm=h('form',{className:'cms-card',onSubmit:book},
        h('div',{className:'row g-3'},
          h('div',{className:'col-md-6'},h(Field,{label:'Choose date'},h('input',{type:'date',className:'form-control',min:new Date().toISOString().slice(0,10),value:date,onChange:e=>loadSlots(e.target.value),required:true}))),
          h('div',{className:'col-md-6'},h('div',{className:'small fw-bold mb-2'},'Available time'),slotList),
          h('div',{className:'col-md-4'},h('input',{className:'form-control',placeholder:'Name',value:form.name,onChange:e=>setForm({...form,name:e.target.value}),required:true})),
          h('div',{className:'col-md-4'},h('input',{className:'form-control',type:'email',placeholder:'Email',value:form.email,onChange:e=>setForm({...form,email:e.target.value}),required:true})),
          h('div',{className:'col-md-4'},h('input',{className:'form-control',placeholder:'Phone',value:form.phone,onChange:e=>setForm({...form,phone:e.target.value})}))
        ),
        h(HCaptchaWidget,{api,onToken:setCaptcha,resetKey:captchaReset,className:'hcaptcha-wrap booking-hcaptcha'}),
        h('button',{className:'btn-cms mt-3',disabled:busy||!time||(hc.enabled&&!captcha)},busy?'Confirming...':'Book → confirmation in real time'),
        msg&&h('div',{className:'booking-confirm mt-3'},msg)
      );
      return h('section',{id:'booking',className:'section cms-soft'},h(Container,{api},h('div',{className:'booking-shell'},h(SectionTitle,{api,base:'booking'}),serviceCards,bookingForm)));
    }


    function AdminShowcase({api}){
      const d=api.data.adminShowcase||{}, E=api.Editable;
      const options=Array.isArray(d.domainOptions)?d.domainOptions:[];
      const shots=Array.isArray(d.items)?d.items:[];
      const rows=Array.isArray(d.rows)?d.rows:[];
      const permission=v=>v
        ?h('span',{className:'permission-yes'},icon('bi bi-check-circle-fill'),api.t('Yes'))
        :h('span',{className:'permission-no'},icon('bi bi-lock-fill'),api.t('No'));
      const domainCards=options.map((opt,i)=>h('div',{className:'admin-domain-option',key:i},
        h('span',{className:'cms-icon'},icon(opt.icon||'bi bi-globe2')),
        h('div',null,
          h(E,{path:`adminShowcase.domainOptions.${i}.title`,tag:'h4',className:'h6 mb-1'}),
          h(E,{path:`adminShowcase.domainOptions.${i}.text`,tag:'p',className:'small muted mb-0'}),
          opt.example&&h('span',{className:'domain-example'},opt.example)
        )
      ));
      const superItems=Array.isArray(d.superAdminItems)?d.superAdminItems:[];
      const superPanel=d.superAdminImage?h('div',{className:'admin-super-showcase'},
        h('div',{className:'admin-super-copy'},h(E,{path:'adminShowcase.superAdminTitle',tag:'h3',className:'h4'}),h(E,{path:'adminShowcase.superAdminText',tag:'p',className:'muted'}),h('div',{className:'admin-super-feature-grid'},superItems.map((it,i)=>h('div',{className:'admin-super-feature',key:i},h('span',{className:'admin-super-feature-icon'},icon(it.icon||'bi bi-check2-circle')),h('div',null,h(E,{path:`adminShowcase.superAdminItems.${i}.title`,tag:'strong'}),h(E,{path:`adminShowcase.superAdminItems.${i}.text`,tag:'span',className:'small muted'})))))),
        h('a',{className:'admin-super-image',href:mediaUrl(d.superAdminImage),'aria-label':api.localizedValue('adminShowcase.superAdminTitle')||'Super Admin'},h('img',{src:mediaUrl(d.superAdminImage),alt:api.localizedValue('adminShowcase.superAdminTitle')||'Super Admin',loading:'lazy',decoding:'async',fetchPriority:'low','data-lightbox':'admin-showcase','data-lightbox-src':mediaUrl(d.superAdminImage)}))
      ):null;
      const shotCards=shots.map((it,i)=>h('article',{className:'admin-shot-card',key:i},
        h('div',{className:'admin-shot-icon','aria-hidden':'true'},icon(it.icon||['bi bi-window-stack','bi bi-images','bi bi-bag-check'][i]||'bi bi-grid')),
        h('div',{className:'admin-shot-copy'},
          h(E,{path:`adminShowcase.items.${i}.title`,tag:'h3',className:'h6'}),
          h(E,{path:`adminShowcase.items.${i}.text`,tag:'p',className:'small muted mb-0'})
        )
      ));
      const capabilityRows=rows.map((row,i)=>h('tr',{key:i},
        h('td',null,h(E,{path:`adminShowcase.rows.${i}.title`,tag:'strong'})),
        h('td',null,permission(!!row.editor)),
        h('td',null,permission(!!row.super)),
        h('td',{className:'admin-ease'},h(E,{path:`adminShowcase.rows.${i}.ease`}))
      ));
      const capabilityTable=h('div',{className:'table-responsive admin-cap-table-wrap'},
        h('table',{className:'admin-cap-table'},
          h('thead',null,h('tr',null,
            h('th',null,api.t('Task')),
            h('th',null,api.t('Editor')),
            h('th',null,api.t('Super Admin')),
            h('th',null,api.t('How easy?'))
          )),
          h('tbody',null,...capabilityRows)
        )
      );
      return h('section',{id:'admin-tools',className:'section admin-showcase'},
        h(Container,{api},
          h(SectionTitle,{api,base:'adminShowcase'}),
          h('div',{className:'admin-showcase-shell'},
            h('div',{className:'admin-domain-card'},
              h(E,{path:'adminShowcase.domainTitle',tag:'h3',className:'h4 mb-2'}),
              h(E,{path:'adminShowcase.domainText',tag:'p',className:'muted mb-0'}),
              h('div',{className:'admin-domain-options'},...domainCards)
            ),
            superPanel,h('div',{className:'admin-shot-grid'},...shotCards),
            h('div',null,
              h(E,{path:'adminShowcase.tableTitle',tag:'h3',className:'h4 mb-1'}),
              h(E,{path:'adminShowcase.tableSubtitle',tag:'p',className:'muted mb-3'}),
              capabilityTable
            )
          )
        )
      );
    }

    function Pricing({api}){
      const E=api.Editable; const items=api.data.pricing?.items||[]; const swiperEl=useRef(null); const instRef=useRef(null);
      useEffect(()=>{
        const setup=()=>{
          if(!swiperEl.current||!window.Swiper)return;
          if(window.innerWidth<768){
            if(instRef.current){ try{instRef.current.update()}catch(e){} return; }
            const paginationEl=swiperEl.current.querySelector('.pricing-swiper-pagination');
            instRef.current=new window.Swiper(swiperEl.current,{
              slidesPerView:1,
              spaceBetween:18,
              speed:550,
              loop:false,
              rewind:items.length>1,
              watchOverflow:true,
              observer:true,
              observeParents:true,
              autoplay:{delay:4200,disableOnInteraction:true,pauseOnMouseEnter:true},
              pagination:{el:paginationEl,clickable:true,hideOnClick:false,dynamicBullets:false},
              a11y:{enabled:true}
            });
          }else if(instRef.current){ try{instRef.current.destroy(true,true)}catch(e){} instRef.current=null; }
        };
        const onPaginationClick=(event)=>{
          const paginationEl=swiperEl.current?.querySelector('.pricing-swiper-pagination');
          const bullet=event.target?.closest?.('.swiper-pagination-bullet');
          if(!paginationEl||!bullet||!paginationEl.contains(bullet)||!instRef.current)return;
          const bullets=Array.from(paginationEl.querySelectorAll('.swiper-pagination-bullet'));
          const index=bullets.indexOf(bullet);
          if(index<0)return;
          try{instRef.current.autoplay?.stop()}catch(e){}
          try{instRef.current.slideTo(index,550)}catch(e){}
        };
        setup();
        const paginationEl=swiperEl.current?.querySelector('.pricing-swiper-pagination');
        paginationEl?.addEventListener('click',onPaginationClick);
        window.addEventListener('resize',setup);
        return()=>{
          paginationEl?.removeEventListener('click',onPaginationClick);
          window.removeEventListener('resize',setup);
          if(instRef.current){try{instRef.current.destroy(true,true)}catch(e){}instRef.current=null;}
        };
      },[items.length,api.lang]);
      return h('section',{id:'pricing',className:'section cms-soft'},h(Container,{api},
        h(SectionTitle,{api,base:'pricing'}),
        h('div',{className:'swiper pricing-swiper',ref:swiperEl},
          h('div',{className:'swiper-wrapper row g-4 pricing-grid-clean'},items.map((it,i)=>
            h('div',{className:'swiper-slide col-12 col-md-6 col-xl-3 pricing-slide',key:i},
              h('article',{className:cls('cms-card pricing-card pricing-card-clean',i===1&&'featured')},
                h('div',{className:'pricing-card-head'},
                  it.badge&&h('span',{className:'plan-badge'},h(E,{path:`pricing.items.${i}.badge`})),
                  h(E,{path:`pricing.items.${i}.title`,tag:'h3',className:'pricing-title'}),
                  h('div',{className:'pricing-dual-price'},
                    h(E,{path:`pricing.items.${i}.price`,tag:'div',className:'pricing-price'}),
                    (it.secondaryPrice||it.priceEur)&&h(E,{path:`pricing.items.${i}.secondaryPrice`,tag:'div',className:'secondary'}),
                    h(E,{path:`pricing.items.${i}.period`,tag:'div',className:'pricing-period muted'})
                  ),
                  h(E,{path:`pricing.items.${i}.text`,tag:'p',className:'pricing-intro muted'})
                ),
                Array.isArray(it.features)&&h('div',{className:'pricing-features'},it.features.map((f,j)=>
                  h('div',{key:j,className:'plan-feature'},h('span',{className:'plan-feature-icon'},icon('bi bi-check2')),h(E,{path:`pricing.items.${i}.features.${j}`}))
                )),
                h('a',{className:'btn-cms pricing-action',href:it.buttonHref||'#contact'},h(E,{path:`pricing.items.${i}.buttonText`}))
              )
            )
          )),
          h('div',{className:'pricing-swiper-pagination'})
        )
      ));
    }

    function DemoIntake({api}){
      const cfg=api.data.demoIntake||{};
      const plans=Array.isArray(cfg.plans)?cfg.plans:[];
      const [step,setStep]=useState(0);
      const [logo,setLogo]=useState(null);
      const [busy,setBusy]=useState(false);
      const [result,setResult]=useState(null);
      const [error,setError]=useState('');
      const captchaRef=useRef(null), captchaId=useRef(null);
      const [form,setForm]=useState({plan:'premium',currency:'GBP',name:'',email:'',phone:'',company:'',industry:'',tagline:'',about:'',services:'',products:'',address:'',existingWebsite:'',preferredDomain:'',language:'English',goals:'',primaryColor:'#0d6efd',style:'clean',chatChoice:'chatbot',whatsapp:'',bookingNeeded:false,socials:'',consent:false,_hp:''});
      const hc=api.data.settings?.hcaptcha||{};
      const steps=Array.isArray(cfg.steps)&&cfg.steps.length===4?cfg.steps:['Your business','Website content','Branding & tools','Review'];
      function setf(k,v){setForm(f=>({...f,[k]:v}));setError('');}
      function validCurrent(){
        if(step===0 && (!form.company.trim()||!form.name.trim()||!/^\S+@\S+\.\S+$/.test(form.email))) return 'Add your business name, contact name and a valid email.';
        if(step===1 && (!form.about.trim()||!form.services.trim())) return 'Add a short business description and at least one service or product.';
        if(step===2 && form.chatChoice==='whatsapp' && !form.whatsapp.trim()) return 'Add the WhatsApp number or choose another chat option.';
        return '';
      }
      function next(){const e=validCurrent();if(e){setError(e);return;}setStep(v=>Math.min(3,v+1));}
      useEffect(()=>{
        if(step!==3||!hc.enabled||!hc.siteKey||!captchaRef.current||captchaId.current!==null)return;
        const tryRender=()=>{ if(window.hcaptcha&&captchaRef.current&&captchaId.current===null){ try{captchaId.current=window.hcaptcha.render(captchaRef.current,{sitekey:hc.siteKey});}catch(e){} } };
        tryRender(); const t=setInterval(tryRender,400); const stop=setTimeout(()=>clearInterval(t),6000); return()=>{clearInterval(t);clearTimeout(stop)};
      },[step,hc.enabled,hc.siteKey]);
      async function submit(e){
        e.preventDefault(); setError('');
        const v=validCurrent(); if(v){setError(v);return;} if(!form.consent){setError('Please confirm the demo preparation consent.');return;}
        setBusy(true);
        try{
          const fd=new FormData(); Object.entries(form).forEach(([k,v])=>fd.append(k,typeof v==='boolean'?(v?'1':'0'):String(v??''))); if(logo)fd.append('logo',logo,logo.name);
          if(hc.enabled&&window.hcaptcha&&captchaId.current!==null)fd.append('hcaptcha',window.hcaptcha.getResponse(captchaId.current)||'');
          const r=await fetch('?api=demo-intake',{method:'POST',body:fd}); const j=await cmsResponseJson(r); if(!r.ok||!j.ok)throw new Error(j.error||'Could not submit the demo request.'); setResult(j); setStep(0);
        }catch(err){setError(err.message||'Could not submit the demo request.');}
        finally{setBusy(false);}
      }
      if(result)return h('section',{id:'demo-request',className:'section'},h(Container,{api},h('div',{className:'demo-intake-shell demo-success'},icon('bi bi-check2-circle'),h('h2',null,'Demo request received'),h('p',{className:'lead'},api.localizedValue('demoIntake.thankYou')||result.message||'Your draft was created for review.'),result.requestId&&h('div',{className:'plan-pill'},'Reference ',result.requestId))));
      const stepDots=h('div',{className:'demo-steps'},steps.map((label,i)=>h('div',{key:i,className:cls('demo-step',i===step&&'active',i<step&&'done')},h('span',null,i<step?icon('bi bi-check-lg'):String(i+1)),h('small',null,label))));
      let content=null;
      if(step===0)content=h('div',{className:'row g-3'},
        h('div',{className:'col-12'},h('div',{className:'demo-plan-grid'},plans.map(p=>h('button',{type:'button',key:p.id,className:cls('demo-plan-choice',form.plan===p.id&&'active'),onClick:()=>setf('plan',p.id)},h('strong',null,p.title),h('span',null,p.description),h('small',null,p.price))))),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Business / brand name *'),h('input',{className:'form-control',value:form.company,onChange:e=>setf('company',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Industry'),h('input',{className:'form-control',value:form.industry,onChange:e=>setf('industry',e.target.value),placeholder:'e.g. beauty salon, construction, consulting'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Your name *'),h('input',{className:'form-control',value:form.name,onChange:e=>setf('name',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Email *'),h('input',{className:'form-control',type:'email',value:form.email,onChange:e=>setf('email',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Phone'),h('input',{className:'form-control',value:form.phone,onChange:e=>setf('phone',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Billing currency'),h('select',{className:'form-select',value:form.currency,onChange:e=>setf('currency',e.target.value)},h('option',{value:'GBP'},'GBP (£)'),h('option',{value:'EUR'},'EUR (€)')))
      );
      if(step===1)content=h('div',{className:'row g-3'},
        h('div',{className:'col-12'},h('label',{className:'form-label'},'Main headline / tagline'),h('input',{className:'form-control',value:form.tagline,onChange:e=>setf('tagline',e.target.value),placeholder:'What should visitors understand first?'})),
        h('div',{className:'col-12'},h('label',{className:'form-label'},'About the business *'),h('textarea',{className:'form-control',rows:4,value:form.about,onChange:e=>setf('about',e.target.value),placeholder:'Describe the business, customers and what makes it different.'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Services * — one per line'),h('textarea',{className:'form-control',rows:6,value:form.services,onChange:e=>setf('services',e.target.value),placeholder:'Consultation — Short description\nInstallation — Short description'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Products / prices — Premium Pro'),h('textarea',{className:'form-control',rows:6,value:form.products,onChange:e=>setf('products',e.target.value),placeholder:'Product name | 29.99 | Short description\nSecond product | 49.00'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Address'),h('input',{className:'form-control',value:form.address,onChange:e=>setf('address',e.target.value)})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Existing website'),h('input',{className:'form-control',value:form.existingWebsite,onChange:e=>setf('existingWebsite',e.target.value),placeholder:'https://...'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Preferred domain / subdomain'),h('input',{className:'form-control',value:form.preferredDomain,onChange:e=>setf('preferredDomain',e.target.value),placeholder:'yourbrand.com or yourbrand'})),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Main language'),h('input',{className:'form-control',value:form.language,onChange:e=>setf('language',e.target.value)})),
        h('div',{className:'col-12'},h('label',{className:'form-label'},'Website goals / extra content'),h('textarea',{className:'form-control',rows:3,value:form.goals,onChange:e=>setf('goals',e.target.value)}))
      );
      if(step===2)content=h('div',{className:'row g-3'},
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Logo image'),h('input',{className:'form-control',type:'file',accept:'image/jpeg,image/png,image/webp,image/avif,image/heic,image/heif',onChange:e=>setLogo(e.target.files?.[0]||null)}),h('div',{className:'small muted mt-1'},logo?logo.name:'PNG, JPG, WebP, HEIC or AVIF.')),
        h('div',{className:'col-md-3'},h('label',{className:'form-label'},'Brand colour'),h('input',{className:'form-control form-control-color w-100',type:'color',value:form.primaryColor,onChange:e=>setf('primaryColor',e.target.value)})),
        h('div',{className:'col-md-3'},h('label',{className:'form-label'},'Visual style'),h('select',{className:'form-select',value:form.style,onChange:e=>setf('style',e.target.value)},['clean','bold','minimal','friendly','luxury'].map(v=>h('option',{key:v,value:v},v.charAt(0).toUpperCase()+v.slice(1))))),
        h('div',{className:'col-md-6'},h('label',{className:'form-label'},'Website chat'),h('select',{className:'form-select',value:form.chatChoice,onChange:e=>setf('chatChoice',e.target.value)},h('option',{value:'chatbot'},'AI chatbot'),h('option',{value:'whatsapp'},'WhatsApp'),h('option',{value:'none'},'No chat button'))),
        form.chatChoice==='whatsapp'&&h('div',{className:'col-md-6'},h('label',{className:'form-label'},'WhatsApp number'),h('input',{className:'form-control',value:form.whatsapp,onChange:e=>setf('whatsapp',e.target.value),placeholder:'+44...'})),
        h('div',{className:'col-12'},h('label',{className:'form-label'},'Social links / usernames'),h('textarea',{className:'form-control',rows:3,value:form.socials,onChange:e=>setf('socials',e.target.value),placeholder:'Facebook, Instagram, LinkedIn, TikTok...'})),
        h('div',{className:'col-12'},h('label',{className:'form-check'},h('input',{className:'form-check-input',type:'checkbox',checked:form.bookingNeeded,onChange:e=>setf('bookingNeeded',e.target.checked)}),h('span',{className:'form-check-label ms-2'},'Include booking / appointment functionality in the demo')))
      );
      if(step===3)content=h('div',null,
        h('div',{className:'demo-review-grid'},
          h('div',null,h('small',null,'Plan'),h('strong',null,plans.find(p=>p.id===form.plan)?.title||form.plan,' · ',form.currency)),
          h('div',null,h('small',null,'Business'),h('strong',null,form.company)),
          h('div',null,h('small',null,'Contact'),h('strong',null,form.name,' · ',form.email)),
          h('div',null,h('small',null,'Chat'),h('strong',null,form.chatChoice)),
          h('div',null,h('small',null,'Booking'),h('strong',null,form.bookingNeeded?'Included':'Not requested')),
          h('div',null,h('small',null,'Logo'),h('strong',null,logo?.name||'No logo uploaded'))
        ),
        h('p',{className:'small muted mt-3'},'A private website draft will be generated from these answers. Super Admin must approve and load it before the public site changes.'),
        hc.enabled&&hc.siteKey?h('div',{className:'mb-3',ref:captchaRef}):null,
        h('label',{className:'form-check'},h('input',{className:'form-check-input',type:'checkbox',checked:form.consent,onChange:e=>setf('consent',e.target.checked)}),h('span',{className:'form-check-label ms-2'},cfg.privacyText||'I approve use of this information for my website demo.')),
        h('input',{type:'text',value:form._hp,onChange:e=>setf('_hp',e.target.value),tabIndex:-1,autoComplete:'off',style:{position:'absolute',left:'-9999px'},'aria-hidden':'true'})
      );
      return h('section',{id:'demo-request',className:'section'},h(Container,{api},h(SectionTitle,{api,base:'demoIntake'}),h('form',{className:'demo-intake-shell',onSubmit:submit},stepDots,h('div',{className:'demo-form-body'},content),error&&h('div',{className:'alert alert-danger mt-3 mb-0'},error),h('div',{className:'demo-form-actions'},step>0?h('button',{type:'button',className:'btn-ghost',disabled:busy,onClick:()=>{setError('');setStep(v=>v-1)}},'Back'):h('span'),step<3?h('button',{type:'button',className:'btn-cms',onClick:next},'Continue ',icon('bi bi-arrow-right')):h('button',{className:'btn-cms',disabled:busy},busy?'Creating draft…':(api.localizedValue('demoIntake.buttonText')||'Create my demo request'))))));
    }

    function Blog({api}){
      const E=api.Editable;
      return h('section',{id:'blog',className:'section cms-soft'},
        h(Container,{api},
          h(SectionTitle,{api,base:'blog'}),
          h(LoadMoreGrid,{api,base:'blog',render:(it,i)=>
            h('div',{className:'col-md-6 col-lg-4',key:i},
              h('article',{className:'cms-card'},
                h(EditableImage,{api,path:`blog.items.${i}.image`,alt:it.title||'',className:'product-card-img mb-3',hideWhenEmpty:true}),
                h('small',{className:'muted'},it.date),
                h(E,{path:`blog.items.${i}.title`,tag:'h3',className:'h4 fw-bold mt-2'}),
                h(E,{path:`blog.items.${i}.excerpt`,tag:'p',className:'muted'}),
                h('a',{className:'btn-ghost',href:pretty('blog',it),onClick:e=>{if(!e.metaKey&&!e.ctrlKey){e.preventDefault();api.setModal({type:'item',group:'blog',index:i});}}},api.t('Read article'))
              )
            )
          })
        )
      );
    }

    function shareLinks(url,title){ const u=encodeURIComponent(url), t=encodeURIComponent(title||document.title); return [ {label:'Facebook',url:`https://www.facebook.com/sharer/sharer.php?u=${u}`}, {label:'X',url:`https://twitter.com/intent/tweet?url=${u}&text=${t}`}, {label:'LinkedIn',url:`https://www.linkedin.com/sharing/share-offsite/?url=${u}`}, {label:'WhatsApp',url:`https://wa.me/?text=${t}%20${u}`} ]; }

    function DeliveryMap({locations=[]}){
      const mapRef=useRef(null);
      const leafletRef=useRef(null);
      const [fallback,setFallback]=useState(false);
      useEffect(()=>{
        if(!mapRef.current || !window.L){ setFallback(true); return; }
        if(leafletRef.current){ try{ leafletRef.current.remove(); }catch(e){} leafletRef.current=null; }
        const L=window.L;
        const map=L.map(mapRef.current,{zoomControl:false,scrollWheelZoom:false,dragging:window.innerWidth>991,tap:true});
        leafletRef.current=map;
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',{attribution:'&copy; OpenStreetMap contributors &copy; CARTO'}).addTo(map);
        const points=[
          {name:'Northampton, England',coords:[52.2405,-0.9027]},
          {name:'Riga, Latvia',coords:[56.9496,24.1052]}
        ];
        const icon=L.divIcon({className:'delivery-map-pin',html:'<span></span>',iconSize:[22,22],iconAnchor:[11,22]});
        points.forEach(point=>L.marker(point.coords,{icon}).addTo(map).bindPopup(point.name));
        const bounds=L.latLngBounds(points.map(point=>point.coords));
        map.fitBounds(bounds.pad(0.45),{animate:false,padding:[28,28]});
        const onResize=()=>map.invalidateSize();
        window.addEventListener('resize',onResize);
        setTimeout(onResize,60);
        return()=>{window.removeEventListener('resize',onResize); try{map.remove();}catch(e){}};
      },[]);
      if(fallback)return h('iframe',{className:'map-frame delivery-map-real delivery-map-fallback',loading:'lazy',title:'DigitalPulse coverage map for the UK and Europe',src:'https://www.openstreetmap.org/export/embed.html?bbox=-12%2C45%2C30%2C61&layer=mapnik'});
      return h('div',{className:'map-frame delivery-map-real',ref:mapRef,'aria-label':'DigitalPulse coverage map for the UK and Europe'});
    }

    function Contact({api}){
      const d=api.data.contact||{}, E=api.Editable;
      const info=[['phone','Phone','bi bi-telephone'],['email','Email','bi bi-envelope'],['hours','Hours','bi bi-clock']];
      const socials=(d.social||api.data.footer?.social||[]).filter(s=>s&&s.url); const locations=d.locations||[];
      const locationCards=locations.length?h('div',{className:'contact-locations'},locations.map((loc,i)=>h('div',{className:'contact-location-card',key:i},
        h('span',{'aria-hidden':'true'},icon(loc.icon||'bi bi-geo-alt-fill')),
        h('div',null,h(E,{path:`contact.locations.${i}.region`,tag:'strong'}),h(E,{path:`contact.locations.${i}.city`,tag:'b'}),h(E,{path:`contact.locations.${i}.text`,tag:'small'}))
      ))):null;
      const details=h('div',{className:'contact-detail-list'},...info.map(([k,label,ico])=>h('div',{key:k,className:'contact-detail-row'},h('span',{className:'contact-detail-icon','aria-hidden':'true'},icon(ico)),h('p',null,h('strong',null,api.t(label)+': '),h(E,{path:`contact.details.${k}`})))));
      const socialRow=socials.length?h('div',{className:'contact-socials','aria-label':api.t('Social media')},...socials.map((social,i)=>{const label=api.t(social.label||'Social media'),external=social.url&&social.url!=='#';return h('a',{key:i,href:social.url,className:'contact-social-link',target:external?'_blank':undefined,rel:external?'noopener noreferrer':undefined,'aria-label':label,title:label},icon(social.icon||'bi bi-link-45deg'));})):null;
      const actions=h('div',{className:'contact-actions'},
        h('button',{className:'btn-cms contact-form-btn',onClick:()=>api.setModal({type:'form'})},api.localizedValue('contact.formButtonText')||api.t('Open contact form')),
        h('button',{className:'btn-ghost business-card-btn',onClick:()=>api.setModal({type:'business-card'})},icon('bi bi-person-vcard'),api.t('Business Card'))
      );
      const box=h('div',{className:'contact-box'},locationCards,details,actions,socialRow);
      const map=h(DeliveryMap,{locations});
      return h('section',{id:'contact',className:'section'},h(Container,{api},h(SectionTitle,{api,base:'contact'}),h('div',{className:'row g-4 contact-layout'},h('div',{className:'col-lg-8'},h('div',{className:'delivery-map-shell'},map)),h('div',{className:'col-lg-4'},box))));
    }

    function CTA({api}){ const E=api.Editable; return h('section',{className:'section'}, h(Container,{api}, h('div',{className:'cta text-center'}, h(E,{path:'cta.title',tag:'h2',className:'display-6 fw-bold'}), h(E,{path:'cta.text',tag:'div',html:true,className:'lead'}), h('button',{className:'btn-ghost',onClick:()=>api.setModal({type:'form'})}, h(E,{path:'cta.buttonText'}))))); }

    function Footer({api}){
      const d=api.data.footer||{};
      const app=api.data.settings?.app||{};
      const year=new Date().getFullYear();
      const showApps=!!app.enabled&&app.footerBadgesEnabled!==false;
      const installBadge=(platform)=>{
        const apple=platform==='apple';
        const label=apple?'iPhone / iPad':'Android';
        const small=apple?api.t('Add to home screen'):api.t('Install app on');
        return h('button',{type:'button',className:'footer-app-badge',onClick:()=>api.setModal({type:'install',platform}),'aria-label':api.t('Install mobile app')+' - '+label,title:api.t('Install mobile app')+' - '+label},
          h('span',{className:'footer-app-icon'},icon(apple?'bi bi-apple':'bi bi-google-play')),
          h('span',{className:'footer-app-copy'},h('small',null,small),h('strong',null,label))
        );
      };
      const rawCopy=String(api.localizedValue('footer.copy')??d.copy??'');
      const copy=rawCopy.replace(/^Copyright\s+/i,'© ').replace('{year}',year);
      return h('footer',{className:'site-footer'},
        h(Container,{api},
          h('div',{className:'footer-layout'+(showApps?'':' no-apps')},
            h('div',{className:'footer-copyline'},copy),
            h('nav',{className:'footer-menu','aria-label':api.t('Footer links')},
              (d.menu||[]).map((m,i)=>h('a',{key:i,href:api.href(m.href)},translate(api,m,'label')))
            ),
            showApps&&h('div',{className:'footer-apps'},
              h('div',{className:'footer-apps-label'},api.t('Install our app')),
              h('div',{className:'footer-app-badges'},installBadge('android'),installBadge('apple'))
            ),
            h('div',{className:'footer-socials','aria-label':api.t('Social media')},
              (d.social||[]).map((s,i)=>{
                const label=translate(api,s,'label')||'Social media';
                return h('a',{key:i,href:s.url,target:'_blank',rel:'noopener',className:'footer-social-link','aria-label':label,title:label},icon(s.icon));
              })
            )
          )
        )
      );
    }

    function LoginButton({api}){
      const [open,setOpen]=useState(false); const [email,setEmail]=useState(''); const [msg,setMsg]=useState(''); const [captcha,setCaptcha]=useState(''); const [captchaReset,setCaptchaReset]=useState(0);
      const hc=api.data.settings?.hcaptcha||{};
      async function submit(e){ e.preventDefault(); setMsg('Sending...'); const r=await fetch('?api=login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email,hcaptcha:captcha})}); const j=await cmsResponseJson(r); setMsg(j.error||j.message||'Check your email.'); if(j.ok){setCaptcha('');setCaptchaReset(v=>v+1);} }
      return h(React.Fragment,null,
        h('button',{className:'btn-cms admin-login',onClick:()=>setOpen(true),'aria-label':'Admin login'}, icon('bi bi-person-lock')),
        open&&h('div',{className:'cms-modal-backdrop',onClick:()=>setOpen(false)},
          h('form',{className:'cms-modal small',onClick:e=>e.stopPropagation(),onSubmit:submit},
            h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,'Admin login'), h('button',{type:'button',className:'icon-btn',onClick:()=>setOpen(false)},icon('bi bi-x-lg'))),
            h('p',{className:'muted'},'Enter an administrator email. A secure login URL will be sent to that address.'),
            h('input',{className:'form-control mb-3',type:'email',value:email,onChange:e=>setEmail(e.target.value),placeholder:'admin@example.com',required:true}),
            h(HCaptchaWidget,{api,onToken:setCaptcha,resetKey:captchaReset,className:'hcaptcha-wrap mb-3'}),
            h('button',{className:'btn-cms w-100',disabled:!!hc.enabled&&!captcha},'Send login link'),
            msg&&h('p',{className:'mt-3 mb-0'},msg)
          )
        )
      );
    }

    function SaveChangesDialog({api,prompt,onClose}){ const [busy,setBusy]=useState(false); const [error,setError]=useState(''); async function confirm(){ if(busy)return; setBusy(true); setError(''); const result=await api.save({silent:true}); if(result?.ok){ onClose(); } else { setError(result?.error||'Could not save changes.'); setBusy(false); } } return h('div',{className:'cms-modal-backdrop cms-save-backdrop',onClick:busy?undefined:onClose}, h('div',{className:'cms-modal small',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-start gap-3 mb-3'}, h('div',null,h('h3',{className:'h5 mb-1'},'Save changes?'),h('div',{className:'small muted'},'Your changes are currently only previewed in the editor.')), h('button',{type:'button',className:'icon-btn',disabled:busy,onClick:onClose,'aria-label':'Keep editing'},icon('bi bi-x-lg'))), h('p',{className:'mb-3'},prompt?.message||'Save your inline changes to data.json now?'), error&&h('div',{className:'alert alert-danger py-2'},error), h('div',{className:'d-flex justify-content-end gap-2'}, h('button',{type:'button',className:'btn-ghost',disabled:busy,onClick:onClose},'Keep editing'), h('button',{type:'button',className:'btn-cms',disabled:busy,onClick:confirm},icon('bi bi-save'),busy?' Saving...':' Save changes')))); }

    function ChatbotWidget({api}){
      const cfg=api.data.chatbot||{};
      const mode=cfg.mode||'chatbot';
      const position=cfg.position==='right'?'right':'left';
      const wa=cfg.whatsapp||{};
      const [open,setOpen]=useState(false);
      const [messages,setMessages]=useState(()=>[{role:'bot',text:api.localizedValue('chatbot.welcome')||api.localizedValue('chatbot.welcomeMessage')||'Hi! How can I help?'}]);
      const [q,setQ]=useState('');
      const [busy,setBusy]=useState(false);
      const end=useRef(null);

      useEffect(()=>{ end.current?.scrollIntoView({behavior:'smooth'}); },[messages,open]);

      async function send(e,forced=''){
        e?.preventDefault();
        const text=String(forced||q).trim();
        if(!text||busy)return;
        const history=messages.slice(-8).map(m=>({role:m.role==='bot'?'assistant':'user',text:m.text}));
        setQ('');
        setMessages(m=>[...m,{role:'user',text}]);
        setBusy(true);
        try{
          const r=await fetch('?api=chatbot',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:text,history})});
          const j=await cmsResponseJson(r);
          if(!r.ok||!j.ok)throw new Error(j.error||api.localizedValue('chatbot.fallback')||'Please contact us.');
          setMessages(m=>[...m,{role:'bot',text:j.reply||api.localizedValue('chatbot.fallback')||'Please contact us.'}]);
        }catch(err){
          setMessages(m=>[...m,{role:'bot',text:err.message||api.localizedValue('chatbot.fallback')||'Please contact us.'}]);
        }finally{ setBusy(false); }
      }

      if(mode==='whatsapp'){
        const number=String(wa.number||'').replace(/[^0-9]/g,'');
        if(!number)return null;
        const waMessage=api.localizedValue('chatbot.whatsapp.message')||wa.message||''; const href='https://wa.me/'+number+(waMessage?'?text='+encodeURIComponent(waMessage):'');
        return h('a',{className:'chatbot-launcher contact-chat-launcher whatsapp '+position,href,target:'_blank',rel:'noopener noreferrer','aria-label':api.localizedValue('chatbot.whatsapp.buttonText')||wa.buttonText||'WhatsApp'},icon('bi bi-whatsapp'));
      }

      const localizedQuestions=api.localizedValue('chatbot.suggestedQuestions'); const questions=Array.isArray(localizedQuestions)?localizedQuestions.filter(Boolean).slice(0,6):[];
      const messageList=h('div',{className:'chatbot-messages'},
        ...messages.map((m,i)=>h('div',{className:'chat-msg '+m.role,key:i},m.text)),
        busy?h('div',{className:'chat-msg bot'},'Thinking…'):null,
        h('span',{ref:end})
      );
      const compose=h('form',{className:'chatbot-compose',onSubmit:send},
        h('input',{className:'form-control',value:q,onChange:e=>setQ(e.target.value),placeholder:api.localizedValue('chatbot.placeholder')||'Ask a question...'}),
        h('button',{className:'btn-cms',disabled:busy||!q.trim(),'aria-label':'Send'},icon('bi bi-arrow-up'))
      );
      const panel=open?h('div',{className:'chatbot-panel '+position},
        h('div',{className:'chatbot-head'},
          h('div',null,h('strong',null,api.localizedValue('chatbot.title')||'AI assistant'),h('div',{className:'small muted'},'Ask about this website')),
          h('button',{className:'icon-btn',onClick:()=>setOpen(false)},icon('bi bi-x-lg'))
        ),
        messageList,
        questions.length?h('div',{className:'chatbot-questions'},questions.map((text,i)=>h('button',{type:'button',key:i,disabled:busy,onClick:e=>send(e,text)},text))):null,
        compose
      ):null;
      return h(React.Fragment,null,
        h('button',{className:'chatbot-launcher contact-chat-launcher '+position,'aria-label':api.localizedValue('chatbot.buttonText')||'AI chat',onClick:()=>setOpen(v=>!v)},icon(open?'bi bi-x-lg':'bi bi-chat-dots-fill')),
        panel
      );
    }

    function BusinessCardModal({api,onClose}){
      const cfg=api.data.contact?.businessCard||{}; const preview=mediaUrl(cfg.preview||'/assets/img/digitalpulse-business-card-preview.png'); const download=mediaUrl(cfg.download||'/assets/docs/digitalpulse-business-card-print.pdf');
      return h('div',{className:'cms-modal-backdrop',onClick:onClose},
        h('div',{className:'cms-modal business-card-modal',onClick:e=>e.stopPropagation()},
          h('div',{className:'d-flex justify-content-between align-items-center mb-3'},
            h('div',null,h('div',{className:'eyebrow mb-1'},api.t('DigitalPulse contact')),h('h3',{className:'mb-0'},api.t('Business Card'))),
            h('button',{type:'button',className:'icon-btn',onClick:onClose,'aria-label':api.t('Close')},icon('bi bi-x-lg'))
          ),
          h('img',{src:preview,alt:api.t('DigitalPulse business card preview'),className:'business-card-preview',loading:'eager',decoding:'async'}),
          h('div',{className:'business-card-actions'},
            h('a',{className:'btn-ghost',href:preview,target:'_blank',rel:'noopener noreferrer'},icon('bi bi-arrows-fullscreen'),api.t('View full size')),
            h('a',{className:'btn-cms',href:download,download:'digitalpulse-business-card-print.pdf'},icon('bi bi-download'),api.t('Download business card'))
          )
        )
      );
    }

    function Modal({modal,setModal,api}){ if(modal.type==='search') return h(SearchModal,{api,onClose:()=>setModal(null)}); if(modal.type==='form') return h(FormModal,{api,modal,onClose:()=>setModal(null)}); if(modal.type==='basket') return h(BasketModal,{api,onClose:()=>setModal(null)}); if(modal.type==='cart') return h(CartModal,{api,onClose:()=>setModal(null)}); if(modal.type==='install') return h(InstallModal,{api,platform:modal.platform||'',onClose:()=>setModal(null)}); if(modal.type==='business-card') return h(BusinessCardModal,{api,onClose:()=>setModal(null)}); if(modal.type==='item') return h(ItemModal,{api,modal,onClose:()=>setModal(null)}); return null; }
    function SearchModal({api,onClose}){ const [q,setQ]=useState(''); const results=useMemo(()=>{ if(!q.trim()) return []; const low=q.toLowerCase(); const out=[]; ['services','features','process','pricing','faq','products','blog','gallery','pages'].forEach(group=>(api.data[group]?.items||[]).forEach((it,i)=>{ if(JSON.stringify(it).toLowerCase().includes(low)) out.push({group,i,title:it.title||it.alt||group,text:it.excerpt||it.text||it.alt||'',href:['blog','products','pages'].includes(group)?pretty(group,it):'#'+group}); })); return out.slice(0,20); },[q,api.data]); return h('div',{className:'cms-modal-backdrop',onClick:onClose}, h('div',{className:'cms-modal',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,'Ajax search'), h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))), h('input',{autoFocus:true,className:'form-control form-control-lg',placeholder:'Search this page...',value:q,onChange:e=>setQ(e.target.value)}), h('div',{className:'mt-3'}, results.map((r,i)=>h('a',{key:i,className:'search-result',href:r.href,onClick:onClose}, h('strong',null,r.title), h('span',{className:'badge text-bg-light ms-2'},r.group), h('p',{className:'muted mb-0'},r.text)))))); }

    function ItemModal({api,modal,onClose}){
      const item=api.data[modal.group]?.items?.[modal.index]||{};
      const path=`${modal.group}.items.${modal.index}`;
      const E=api.Editable;
      const url=absolute(pretty(modal.group,item));
      const isProduct=modal.group==='products';
      const isPortfolio=modal.group==='gallery';
      const imgs=[item.image,...(item.gallery||[])].filter((v,i,a)=>v&&a.indexOf(v)===i);
      const variations=api.productVariationList(item);
      const [active,setActive]=useState(imgs[0]||'');
      const [variationId,setVariationId]=useState(variations[0]?api.variationLabel(variations[0]):'');
      const [qty,setQty]=useState(1);
      useEffect(()=>{
        setActive(imgs[0]||'');
        setVariationId(variations[0]?api.variationLabel(variations[0]):'');
        setQty(1);
      },[modal.group,modal.index]);
      const selected=variations.find(v=>api.variationLabel(v)===variationId)||variations[0]||null;
      return h('div',{className:'cms-modal-backdrop',onClick:onClose},
        h('article',{className:'cms-modal',onClick:e=>e.stopPropagation()},
          h('button',{className:'icon-btn float-end',onClick:onClose},icon('bi bi-x-lg')),
          active&&h('img',{
            src:mediaUrl(active),
            alt:api.localizedValue(path+'.title')||item.title,
            className:'img-fluid rounded-4 mb-3 product-main-lightbox',
            loading:'lazy',decoding:'async',
            'data-lightbox':isPortfolio?'portfolio':'product',
            'data-lightbox-src':mediaUrl(active)
          }),
          imgs.length>1&&h('div',{className:'product-gallery'},
            imgs.map((src,i)=>h('img',{
              key:i,src:mediaUrl(src),alt:api.localizedValue(path+'.title')||item.title,
              loading:'lazy',decoding:'async',onClick:()=>setActive(src)
            }))
          ),
          h(E,{path:path+'.title',tag:'h2',className:'fw-bold'}),
          item.date&&h('p',{className:'muted'},item.date),
          isPortfolio&&h('div',{className:'portfolio-modal-meta'},
            item.kind&&h('span',{className:'demo-pill'},api.t(item.kind)),
            item.status&&h('span',{className:cls('portfolio-status',String(item.status||'').toLowerCase().includes('development')&&'is-development')},api.t(item.status)),
            item.client&&h('span',{className:'demo-pill'},api.t(item.client)),
            ...(item.tech||[]).map((t,i)=>h('span',{key:i,className:'tech-chip'},t))
          ),
          isProduct&&h(ProductBadges,{item}),
          isProduct?h(ProductPrice,{api,item,path,variation:selected}):(item.price&&h(E,{path:path+'.price',tag:'strong',className:'text-primary d-block mb-3'})),
          isProduct&&variations.length>0&&h('div',{className:'variation-box'},
            h('label',{className:'form-label small fw-bold'},'Choose variation'),
            h('div',{className:'variation-swatches'},
              variations.map((v,i)=>h(VariationChoice,{
                api,item,variation:v,key:i,
                active:api.variationLabel(v)===variationId,
                onClick:()=>{setVariationId(api.variationLabel(v));if(v.image)setActive(v.image);}
              }))
            )
          ),
          isProduct&&h('div',{className:'qty-box'},
            h('label',{className:'form-label small fw-bold'},'Quantity'),
            h('input',{className:'form-control',type:'number',min:1,value:qty,onChange:e=>setQty(Math.max(1,Number(e.target.value)||1))})
          ),
          item.sku&&h('p',{className:'small muted mb-2'},'SKU: ',selected?.sku||item.sku),
          h(E,{path:path+'.body',tag:'div',html:true,className:'fs-5'}),
          isProduct&&Array.isArray(item.features)&&item.features.length>0&&h('ul',{className:'mt-3'},
            item.features.map((f,i)=>h('li',{key:i},h(E,{path:path+'.features.'+i})))
          ),
          isProduct&&h(ProductButtons,{api,item,variation:selected,qty}),
          isPortfolio&&item.url&&h('a',{
            className:'btn-cms portfolio-external',href:item.url,
            target:/^https?:\/\//i.test(String(item.url))?'_blank':undefined,
            rel:/^https?:\/\//i.test(String(item.url))?'noopener noreferrer':undefined,
            onClick:/^#/.test(String(item.url))?onClose:undefined
          },api.t(item.linkText||'Build something similar')),
          h('div',{className:'share-row'},
            shareLinks(url,item.title).map(s=>h('a',{key:s.label,href:s.url,target:'_blank',rel:'noopener'},s.label))
          )
        )
      );
    }

    function BasketModal({api,onClose}){ const cfg=api.data.products||{}; return h('div',{className:'cms-modal-backdrop',onClick:onClose}, h('div',{className:'cms-modal small',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,cfg.basketTitle||'Quote basket'), h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))), api.quoteItems.length===0?h('p',{className:'muted'},'Quote basket is empty.') : h(React.Fragment,null, api.quoteItems.map((it,i)=>h('div',{className:'basket-line',key:i}, h('div',null,h('strong',null,it.title), h('div',{className:'small muted'}, (it.qty||1),' × ',api.formatProductPrice(it), it.sku?` · ${it.sku}`:'', it.variationLabel?` · ${it.variationLabel}`:'')), h('button',{className:'icon-btn',onClick:()=>api.removeQuote(i)},icon('bi bi-trash')))), h('div',{className:'d-flex gap-2 mt-3'}, h('button',{className:'btn-cms flex-fill',onClick:()=>api.setModal({type:'form',formType:'quote',message:api.quoteMessage()})},cfg.basketButtonText||'Send quote request'), h('button',{className:'btn-ghost',onClick:api.clearQuote},'Clear'))))); }

    function CartModal({api,onClose}){ const cfg=api.data.products||{}; const pay=cfg.payment?.directDebit||{}; const methods=(cfg.shippingMethods||[]).filter(x=>x.enabled!==false); const codes=(cfg.discountCodes||[]).filter(x=>x.enabled!==false); const [busy,setBusy]=useState(false); const [error,setError]=useState(''); const [email,setEmail]=useState(''); const [shippingId,setShippingId]=useState(methods[0]?.id||''); const [discountCode,setDiscountCode]=useState(''); const subtotal=api.cartTotal(); const ship=methods.find(x=>x.id===shippingId); const dc=codes.find(x=>String(x.code||'').toLowerCase()===discountCode.trim().toLowerCase()); const discount=dc?(dc.type==='fixed'?Math.min(subtotal,Number(dc.value||0)):subtotal*Math.max(0,Math.min(100,Number(dc.value||0)))/100):0; const shipping=Number(ship?.price||0); const total=Math.max(0,subtotal-discount+shipping); async function checkout(){setBusy(true);setError('');try{if(api.data.settings?.abandonedCart?.enabled&&!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))throw new Error('Enter your email so the cart can be saved and checkout can continue.');if(api.data.settings?.abandonedCart?.enabled){await fetch('?api=abandoned-cart',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'capture',email,items:api.shopCartItems,shippingId,discountCode})})}const r=await fetch('?api=checkout-start',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({items:api.shopCartItems,shippingId,discountCode,customer:{email}})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Checkout could not start.');if(j.mode==='order-form'){api.setModal({type:'form',formType:'order',message:api.orderMessage(j.cart),email});return;}if(j.url){location.href=j.url;return;}throw new Error('Checkout did not return a payment URL.')}catch(e){setError(e.message||'Checkout failed.')}finally{setBusy(false)}} return h('div',{className:'cms-modal-backdrop',onClick:onClose}, h('div',{className:'cms-modal small',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,cfg.cartTitle||'Shopping cart'), h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))), api.shopCartItems.length===0?h('p',{className:'muted'},'Cart is empty.') : h(React.Fragment,null, api.shopCartItems.map((it,i)=>h('div',{className:'basket-line',key:i}, h('div',null,h('strong',null,it.title), h('div',{className:'small muted'}, (it.qty||1),' × ',api.formatProductPrice(it), it.sku?` · ${it.sku}`:'', it.variationLabel?` · ${it.variationLabel}`:'')), h('button',{className:'icon-btn',onClick:()=>api.removeCart(i)},icon('bi bi-trash')))), h('div',{className:'cart-options mt-3'},api.data.settings?.abandonedCart?.enabled&&h(Field,{label:'Email for checkout & saved-cart reminder'},h('input',{className:'form-control',type:'email',value:email,onChange:e=>setEmail(e.target.value),placeholder:'you@example.com'})),methods.length>0&&h(Field,{label:'Delivery / fulfilment'},h('select',{className:'form-select',value:shippingId,onChange:e=>setShippingId(e.target.value)},methods.map(m=>h('option',{key:m.id,value:m.id},`${m.label} · ${api.formatMoney?api.formatMoney(m.price,cfg.currency):new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(Number(m.price||0))}`)))),codes.length>0&&h(Field,{label:'Discount code'},h('input',{className:'form-control',value:discountCode,onChange:e=>setDiscountCode(e.target.value),placeholder:'Code'})),h('div',{className:'cart-summary-row'},h('span',null,'Subtotal'),h('strong',null,new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(subtotal))),discount>0&&h('div',{className:'cart-summary-row'},h('span',null,'Discount'),h('strong',null,'−'+new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(discount))),h('div',{className:'cart-summary-row'},h('span',null,'Shipping'),h('strong',null,new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(shipping))),h('div',{className:'cart-summary-row total'},h('span',null,'Total'),h('span',null,new Intl.NumberFormat(undefined,{style:'currency',currency:cfg.currency||'EUR'}).format(total)))), pay.enabled&&h('div',{className:'payment-note'}, h('strong',null,pay.title||'Direct bank transfer'), h('p',null,pay.instructions||'Submit order and we will confirm payment details.')), error&&h('div',{className:'alert alert-danger py-2 mt-3'},error), h('div',{className:'d-flex gap-2 mt-3'}, h('button',{className:'btn-cms flex-fill',disabled:busy,onClick:checkout},busy?'Starting checkout...':(cfg.cartButtonText||'Checkout now')), h('button',{className:'btn-ghost',disabled:busy,onClick:api.clearCart},'Clear'))))); }

    function FormModal({api,modal,onClose}){
      const initialMessage=modal?.message || (modal?.formType==='quote'?api.quoteMessage():'');
      const [form,setForm]=useState({name:'',email:modal?.email||'',phone:'',message:initialMessage});
      const [msg,setMsg]=useState(''); const [captcha,setCaptcha]=useState(''); const [captchaReset,setCaptchaReset]=useState(0); const hc=api.data.settings.hcaptcha||{};
      async function submit(e){
        e.preventDefault(); setMsg('Sending...');
        const r=await fetch('?api=form',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({...form,hcaptcha:captcha,type:modal?.formType||'contact'})});
        const j=await cmsResponseJson(r); setMsg(j.error||j.message||'Sent');
        if(j.ok){ setForm({name:'',email:'',phone:'',message:''}); setCaptcha(''); setCaptchaReset(v=>v+1); if(modal?.formType==='quote') api.clearQuote(); if(modal?.formType==='order') api.clearCart(); }
      }
      const sendLabel=modal?.formType==='order'?(api.data.products?.checkoutButtonText||'Send order'):(modal?.formType==='quote'?'Send quote request':'Send');
      return h('div',{className:'cms-modal-backdrop',onClick:onClose},
        h('form',{className:'cms-modal small',onClick:e=>e.stopPropagation(),onSubmit:submit},
          h('div',{className:'d-flex justify-content-between align-items-center mb-3'},
            h('h3',null,modal?.formType==='order'?'Checkout order':(modal?.formType==='quote'?'Send quote request':'Send request')),
            h('button',{type:'button',className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))
          ),
          ['name','email','phone'].map(k=>h('input',{key:k,className:'form-control mb-3',placeholder:k.charAt(0).toUpperCase()+k.slice(1),type:k==='email'?'email':'text',value:form[k],onChange:e=>setForm({...form,[k]:e.target.value}),required:k!=='phone'})),
          h('textarea',{className:'form-control mb-3',rows:7,placeholder:'Message',value:form.message,onChange:e=>setForm({...form,message:e.target.value}),required:true}),
          h(HCaptchaWidget,{api,onToken:setCaptcha,resetKey:captchaReset,className:'hcaptcha-wrap mb-3'}),
          h('button',{className:'btn-cms w-100',disabled:hc.enabled&&!captcha},sendLabel),
          msg&&h('p',{className:'mt-3 mb-0'},msg)
        )
      );
    }

    function InstallModal({api,platform='',onClose}){ const app=api.data.settings.app||{}; const [promptEvent,setPromptEvent]=useState(window.__lpcmsInstallPrompt||null); useEffect(()=>{ const fn=()=>setPromptEvent(window.__lpcmsInstallPrompt||null); window.addEventListener('lpcms:install-ready',fn); return()=>window.removeEventListener('lpcms:install-ready',fn); },[]); const androidUrl=api.href(app.androidInstallUrl||'/?install=android'); const appleUrl=api.href(app.appleInstallUrl||'/?install=apple'); async function install(){ const target=platform||(/iphone|ipad|ipod/i.test(navigator.userAgent)?'apple':'android'); if(target!=='apple'&&promptEvent){ promptEvent.prompt(); await promptEvent.userChoice; window.__lpcmsInstallPrompt=null; setPromptEvent(null); } else { location.href=target==='apple'?appleUrl:androidUrl; } } const primaryLabel=platform==='apple'?api.t('View iPhone / iPad install steps'):promptEvent?api.t('Install on Android now'):api.t('Open install guide'); return h('div',{className:'cms-modal-backdrop',onClick:onClose}, h('div',{className:'cms-modal small',onClick:e=>e.stopPropagation()}, h('div',{className:'d-flex justify-content-between align-items-center mb-3'}, h('h3',null,api.t('Install mobile app')), h('button',{className:'icon-btn',onClick:onClose,'aria-label':'Close'},icon('bi bi-x-lg'))), h('p',{className:'muted'},api.localizedValue('settings.app.description')||api.t(app.description||'Install this website as a mobile app.')), h('div',{className:'d-grid gap-2'}, h('button',{className:'btn-cms',onClick:install}, icon(platform==='apple'?'bi bi-apple':'bi bi-download'), ' ', primaryLabel), h('a',{className:'btn-ghost text-center',href:androidUrl},icon('bi bi-google-play'),' ',api.t('Android guide')), h('a',{className:'btn-ghost text-center',href:appleUrl},icon('bi bi-apple'),' ',api.t('Apple guide'))))); }

    function CookieBanner({api}){ const data=api.data,cfg=data.settings.cookies||{}; const [show,setShow]=useState(()=>cfg.enabled && !localStorage.getItem('cms_cookie_ok')); if(!show) return null; return h('div',{className:'cookie-box'}, h('div',{className:'d-flex flex-column flex-lg-row align-items-lg-center gap-3 justify-content-between'}, h('p',{className:'mb-0'},api.localizedValue('settings.cookies.message')||cfg.message), h('div',{className:'d-flex gap-2'}, h('button',{className:'btn-ghost',onClick:()=>{localStorage.setItem('cms_cookie_ok','no');setShow(false)}},api.localizedValue('settings.cookies.declineText')||cfg.declineText||'Decline'), h('button',{className:'btn-cms',onClick:()=>{localStorage.setItem('cms_cookie_ok','yes');setShow(false);maybeLoadGA(data)}},api.localizedValue('settings.cookies.acceptText')||cfg.acceptText||'Accept')))); }

    function AdminPanel({api,open,onClose}){ const [tab,setTab]=useState('site'); useEffect(()=>{ if(open && api.adminRequestedTab){ setTab(api.adminRequestedTab); api.setAdminRequestedTab(''); } },[open,api.adminRequestedTab]); const allTabs=[['site','bi bi-house','Site','all'],['theme','bi bi-palette','Themes','all'],['menu','bi bi-list-ul','Menu','all'],['content','bi bi-layout-text-window','Content','all'],['products','bi bi-bag','Products','all'],['booking','bi bi-calendar-check','Booking','all'],['chatbot','bi bi-chat-dots','Chat / WhatsApp','all'],['blog','bi bi-journal-text','Blog','all'],['media','bi bi-images','Media','all'],['icons','bi bi-grid','Icons','all'],['seo','bi bi-search','SEO','all'],['ai','bi bi-stars','AI Writer','all'],['plan','bi bi-gem','Plans & demo','super'],['demo','bi bi-ui-checks-grid','Demo requests','super'],['abandoned','bi bi-cart-x','Cart recovery','super'],['forms','bi bi-envelope','Forms','super'],['mailer','bi bi-send-check','Mailer','super'],['headless','bi bi-diagram-3','Headless','super'],['app','bi bi-phone','App','super'],['cache','bi bi-speedometer2','Cache','super'],['users','bi bi-people','Users','super'],['libraries','bi bi-plugin','Libraries','super'],['redirects','bi bi-signpost','Redirects','super'],['server','bi bi-hdd-network','Server','super'],['json','bi bi-braces','JSON','super']]; const tabs=allTabs.filter(t=>t[3]==='all'||api.isSuper); useEffect(()=>{ if(!tabs.some(t=>t[0]===tab)) setTab('site'); },[api.isSuper,tab]); if(!api.admin) return null; return h('aside',{className:cls('admin-panel',open&&'open')}, h('header',null, h('div',{className:'d-flex justify-content-between align-items-center'}, h('div',null,h('div',{className:'admin-title'},'LPCMS Admin ', h('span',{className:'role-badge'},api.isSuper?'Super':'Editor')), h('div',{className:'admin-subtitle'},api.email || 'Inline editor', ' · ', 'Editing language', ': ', ((api.data.settings?.languages||I18N.languages||[]).find(l=>String(l.code||'').toLowerCase()===String(api.lang||DEFAULT_LANG).toLowerCase())?.name||String(api.lang||DEFAULT_LANG).toUpperCase()))), h('button',{className:'icon-btn',onClick:onClose},icon('bi bi-x-lg'))), h('div',{className:'admin-nav'}, tabs.map(([key,ic,label])=>h('button',{key,className:cls('admin-tab',tab===key&&'active'),onClick:()=>setTab(key)},icon(ic), h('span',null,label))))), h('div',{className:'body'}, h('div',{className:'admin-actions mb-3'}, api.dirty&&h('div',{className:'alert alert-warning py-2 mb-2'},'Unsaved changes'), h('div',{className:'d-flex gap-2'}, h('button',{className:'btn-cms flex-fill',onClick:api.save},icon('bi bi-save'),' Save JSON'), h('button',{className:'btn-ghost',onClick:api.logout},'Logout'))), tab==='site'&&h(SiteTab,{api}), tab==='theme'&&h(ThemeTab,{api}), tab==='menu'&&h(MenuTab,{api}), tab==='content'&&h(ContentTab,{api}), tab==='products'&&h(ProductsTab,{api}), tab==='booking'&&h(BookingTab,{api}), tab==='chatbot'&&h(ChatbotTab,{api}), tab==='blog'&&h(BlogTab,{api}), tab==='media'&&h(MediaTab,{api}), tab==='icons'&&h(IconsTab,{api}), tab==='seo'&&h(SeoTab,{api}), tab==='ai'&&h(AiTab,{api}), tab==='plan'&&h(PlanTab,{api}), tab==='demo'&&h(DemoRequestsTab,{api}), tab==='abandoned'&&h(AbandonedCartTab,{api}), tab==='forms'&&h(FormsTab,{api}), tab==='mailer'&&h(MailerTab,{api}), tab==='headless'&&h(HeadlessTab,{api}), tab==='app'&&h(AppTab,{api}), tab==='cache'&&h(CacheTab,{api}), tab==='users'&&h(UsersTab,{api}), tab==='libraries'&&h(LibrariesTab,{api}), tab==='redirects'&&h(RedirectsTab,{api}), tab==='server'&&h(ServerTab,{api}), tab==='json'&&h(JsonTab,{api}) )); }

    function Field({label,children}){ return h('label',{className:'form-label w-100 mb-3'}, h('span',{className:'small fw-bold d-block mb-1'},label), children); }
    function Txt({api,path,label,type='text',localize=true}){ const p=localize?api.localizedPath(path):path; const raw=localize?api.localizedValue(path):get(api.data,path); return h(Field,{label}, h('input',{className:'form-control',type,value:raw??'',onChange:e=>api.update(p,e.target.value)})); }
    function Bool({api,path,label}){ return h('div',{className:'form-check form-switch mb-2'}, h('input',{className:'form-check-input',type:'checkbox',checked:!!get(api.data,path),onChange:e=>api.update(path,e.target.checked),id:path}), h('label',{className:'form-check-label',htmlFor:path},label)); }
    function RichText({api,path,label,large=false}){
      const editPath=api.localizedPath(path);
      const start=api.localizedValue(path)||'';
      const [html,setHtml]=useState(start);
      const [source,setSource]=useState(false);
      const [aiOpen,setAiOpen]=useState(false);
      const ref=useRef(null);
      const currentValue=api.localizedValue(path)||'';

      useEffect(()=>{
        const v=api.localizedValue(path)||'';
        setHtml(v);
        if(ref.current&&ref.current.innerHTML!==v)ref.current.innerHTML=v;
      },[path,currentValue]);

      function save(){
        if(!ref.current)return;
        const v=ref.current.innerHTML;
        setHtml(v);
        api.update(editPath,v);
      }
      function cmd(name,arg){
        if(ref.current)ref.current.focus();
        document.execCommand(name,false,arg||null);
        save();
      }
      function link(){const url=prompt(api.t('Link URL'),'https://');if(url)cmd('createLink',url);}
      function image(){const url=prompt(api.t('Image URL'),'https://');if(url)cmd('insertImage',url);}
      function block(v){if(v)cmd('formatBlock','<'+v+'>');}
      function openAi(){
        if(!source)save();
        setAiOpen(true);
      }

      const toolbar=h('div',{className:'editor-toolbar'},
        h('select',{defaultValue:'',onChange:e=>{block(e.target.value);e.target.value='';}},
          h('option',{value:''},'Text type'),h('option',{value:'p'},'Paragraph'),h('option',{value:'h2'},'Heading 2'),h('option',{value:'h3'},'Heading 3'),h('option',{value:'blockquote'},'Quote')
        ),
        h('button',{type:'button',onClick:()=>cmd('bold')},icon('bi bi-type-bold')),
        h('button',{type:'button',onClick:()=>cmd('italic')},icon('bi bi-type-italic')),
        h('button',{type:'button',onClick:()=>cmd('underline')},icon('bi bi-type-underline')),
        h('span',{className:'editor-sep'}),
        h('button',{type:'button',onClick:()=>cmd('justifyLeft')},icon('bi bi-text-left')),
        h('button',{type:'button',onClick:()=>cmd('justifyCenter')},icon('bi bi-text-center')),
        h('button',{type:'button',onClick:()=>cmd('justifyRight')},icon('bi bi-text-right')),
        h('span',{className:'editor-sep'}),
        h('button',{type:'button',onClick:()=>cmd('insertUnorderedList')},icon('bi bi-list-ul')),
        h('button',{type:'button',onClick:()=>cmd('insertOrderedList')},icon('bi bi-list-ol')),
        h('button',{type:'button',onClick:link},icon('bi bi-link-45deg')),
        h('button',{type:'button',onClick:image},icon('bi bi-image')),
        h('button',{type:'button',onClick:()=>cmd('removeFormat')},icon('bi bi-eraser')),
        api.data.settings?.ai?.enabled?h('button',{type:'button',className:'ai-inline-btn',title:'AI write / improve / translate',onClick:openAi},icon('bi bi-stars')):null,
        h('button',{type:'button',className:source?'ms-auto active':'ms-auto',onClick:()=>{if(!source)save();setSource(v=>!v);}},source?'Visual':'HTML')
      );

      const editor=!source
        ?h('div',{ref,className:cls('rich-editor',large&&'large'),contentEditable:true,suppressContentEditableWarning:true,onBlur:save,dangerouslySetInnerHTML:{__html:html}})
        :h('textarea',{className:'form-control html-editor',rows:large?14:8,value:html,onChange:e=>{setHtml(e.target.value);api.update(editPath,e.target.value);}});

      return h(Field,{label},
        h('div',{className:'editor-card'},toolbar,editor),
        aiOpen?h(AiInlineDialog,{cms:api,path:editPath,current:html,html:true,onClose:()=>setAiOpen(false)}):null
      );
    }

    function SiteTab({api}){ const plan=get(api.data,'settings.sitePlan')||{}; const fallback=(plan.subdomain||'yourbusiness')+'.'+(plan.platformDomain||'dev4.click'); return h(React.Fragment,null,
      h('div',{className:'admin-card'},h('h4',{className:'h6'},'Site identity'),h(Txt,{api,path:'settings.siteName',label:'Site name'}),api.isSuper&&h(Txt,{api,path:'settings.baseUrl',label:'Base URL'}),h(Txt,{api,path:'header.logoText',label:'Logo text'}),h('div',{className:'small muted'},api.isSuper?`Deployment address: ${plan.customDomain||fallback}`:'Domain and deployment settings are managed by the super administrator.')),
      h('div',{className:'admin-card'},h('h4',{className:'h6'},'Logo'),h('div',{className:'brand-preview mb-2'},h(EditableImage,{api,path:'header.logoImage',alt:api.data.settings.siteName||'',className:'admin-image-field'})),h(Txt,{api,path:'header.logoImage',label:'Logo image URL (optional)'}),h('p',{className:'small muted mb-0'},'When no logo image is set, LPCMS shows the built-in LP mark next to your logo text.')),
      h('div',{className:'admin-card'},h('h4',{className:'h6'},'Favicon'),h('div',{className:'brand-preview mb-2'},h(EditableImage,{api,path:'settings.branding.favicon',alt:'Favicon',className:'favicon-admin-field',fallback:h('span',{className:'favicon-placeholder'},api.data.settings?.branding?.placeholderMark||'LP')}),h('div',null,h('strong',null,get(api.data,'settings.branding.favicon')?'Custom favicon':'LP placeholder favicon'),h('div',{className:'small muted'},'Used in browser tabs and bookmarks.'))),h(Txt,{api,path:'settings.branding.favicon',label:'Favicon image URL (optional)'}),h('p',{className:'small muted mb-0'},'If no favicon is uploaded, LPCMS automatically renders an LP placeholder using the active brand colour.'))
    ); }

    function ThemeTab({api}){
      const colors=['brand','brand2','accent','text','muted','bg','softBg'];
      const [catalog,setCatalog]=useState(null),[busy,setBusy]=useState(''),[zip,setZip]=useState(null),[title,setTitle]=useState(''),[license,setLicense]=useState('User-provided / verify licence'),[themeDialog,setThemeDialog]=useState('');
      async function load(){ if(!api.isSuper)return; try{const r=await fetch('?api=theme-catalog');const j=await cmsResponseJson(r);if(j.ok)setCatalog(j);else api.setNotice(j.error||'Could not load theme catalogue');}catch(e){api.setNotice(e.message)} }
      useEffect(()=>{load()},[api.isSuper]);
      async function activate(id){setBusy(id);try{const r=await fetch('?api=theme-activate',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},body:JSON.stringify({id})});const j=await cmsResponseJson(r);if(!j.ok)throw new Error(j.error||'Theme activation failed');api.setNotice('Theme activated. Reloading design…');location.reload();}catch(e){api.setNotice(e.message);setBusy('')}}
      async function importFree(id){setBusy(id);try{const fd=new FormData();fd.append('sourceType','free');fd.append('themeId',id);const r=await fetch('?api=theme-import',{method:'POST',headers:{'X-CMS-CSRF':api.csrf},body:fd});const j=await cmsResponseJson(r);if(!j.ok)throw new Error(j.error||'Theme import failed');api.setNotice(`Imported ${j.theme?.title||'theme'} · compatibility ${j.theme?.compatibilityScore||0}%`);location.reload();}catch(e){api.setNotice(e.message);setBusy('')}}
      async function importZip(){if(!zip){api.setNotice('Choose a Bootstrap HTML ZIP first.');return;}setBusy('upload');try{const fd=new FormData();fd.append('sourceType','upload');fd.append('themeZip',zip,zip.name);fd.append('title',title||zip.name.replace(/\.zip$/i,''));fd.append('license',license);const r=await fetch('?api=theme-import',{method:'POST',headers:{'X-CMS-CSRF':api.csrf},body:fd});const j=await cmsResponseJson(r);if(!j.ok)throw new Error(j.error||'Theme import failed');api.setNotice(`Imported ${j.theme?.title||'theme'} · Bootstrap ${j.theme?.bootstrap||'unknown'} · compatibility ${j.theme?.compatibilityScore||0}%`);location.reload();}catch(e){api.setNotice(e.message);setBusy('')}}
      async function removeTheme(id){if(!confirm(api.t('Delete this imported theme and its files?')))return;setBusy(id);try{const r=await fetch('?api=theme-delete',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},body:JSON.stringify({id})});const j=await cmsResponseJson(r);if(!j.ok)throw new Error(j.error||'Delete failed');await load();api.setNotice('Theme deleted.');setBusy('');}catch(e){api.setNotice(e.message);setBusy('')}}
      const active=get(api.data,'settings.themeManager.active')||catalog?.manager?.active||'lpcms-default';
      const presets=catalog?.presets||[]; const installed=catalog?.manager?.installed||get(api.data,'settings.themeManager.installed')||[];
      function card(t,kind){const palette=t.palette||{};const isActive=active===t.id;return h('div',{className:cls('theme-card',isActive&&'active'),key:t.id},h('div',{className:'d-flex justify-content-between align-items-start gap-2'},h('div',{className:'min-w-0'},h('strong',null,t.title),kind==='imported'&&h('div',{className:'small muted'},`Bootstrap ${t.bootstrap||'unknown'} · ${t.compatibilityScore||0}% compatible${t.archiveEngine?' · '+t.archiveEngine:''}`)),isActive&&h('span',{className:'theme-score'},'Active')),palette&&h('div',{className:'theme-swatch-row'},['brand','brand2','accent'].map(k=>h('span',{key:k,style:{background:palette[k]||'#eee'}}))),t.description&&h('p',{className:'small muted mb-2'},t.description),Array.isArray(t.warnings)&&t.warnings.length>0&&h('ul',{className:'theme-warnings'},t.warnings.slice(0,3).map((w,i)=>h('li',{key:i},w))),h('div',{className:'theme-card-actions'},h('button',{type:'button',className:isActive?'btn-ghost':'btn-cms',disabled:!!busy||isActive,onClick:()=>activate(t.id)},busy===t.id?'Working…':isActive?'Selected':'Switch theme'),kind==='imported'&&h('button',{type:'button',className:'btn-ghost',disabled:!!busy,onClick:()=>removeTheme(t.id)},'Delete')));}
      function closeDialog(){if(!busy)setThemeDialog('')}
      const freeDialog=themeDialog==='free'&&ReactDOM.createPortal(
        h('div',{className:'cms-modal-backdrop',onClick:closeDialog},
          h('div',{className:'cms-modal theme-library-modal',onClick:e=>e.stopPropagation()},
            h('div',{className:'d-flex justify-content-between align-items-start gap-3 mb-3'},
              h('div',null,
                h('h3',{className:'h4 mb-1'},'Free online Bootstrap themes'),
                h('p',{className:'muted mb-0'},'Choose a theme below. LPCMS downloads the allowlisted source ZIP itself, analyses Bootstrap compatibility, rebuilds an adapter, installs the theme, and activates it. No local ZIP selection is required.')
              ),
              h('button',{type:'button',className:'icon-btn',disabled:!!busy,onClick:closeDialog},icon('bi bi-x-lg'))
            ),
            !catalog&&h('div',{className:'alert alert-info'},'Loading theme catalogue…'),
            catalog&&!catalog.zipSupported&&h('div',{className:'alert alert-warning'},'No ZIP extraction engine is available. Enable PHP ZipArchive or the standard Phar/PharData extension.'),
            catalog?.zipSupported&&catalog?.archiveSupport?.preferred==='PharData'&&h('div',{className:'alert alert-info'},'PHP ZipArchive is not enabled, so LPCMS will use its PharData ZIP fallback for online theme imports.'),
            catalog&&catalog.downloadSupport&&!catalog.downloadSupport.supported&&h('div',{className:'alert alert-warning'},'This PHP container has no usable outbound downloader. Use Download ZIP manually, then import the downloaded file with the local ZIP importer.'),
            catalog?.downloadSupport?.supported&&h('div',{className:'small muted mb-3'},'Online downloader: ',(catalog.downloadSupport.methods||[]).join(' → ')),
            h('div',{className:'theme-grid'},
              (catalog?.free||[]).map(t=>h('div',{className:'theme-card',key:t.id},
                h('div',{className:'d-flex justify-content-between gap-2'},
                  h('strong',null,t.title),
                  h('span',{className:'theme-score'},t.license||'Free')
                ),
                h('div',{className:'small muted mb-2'},t.provider||'Start Bootstrap'),
                h('p',{className:'small muted'},t.description),
                h('div',{className:'d-grid gap-2'},
                  h('button',{type:'button',className:'btn-cms w-100',disabled:!!busy||!catalog?.zipSupported||!catalog?.downloadSupport?.supported,onClick:()=>importFree(t.id)},busy===t.id?'Importing & rebuilding…':h(React.Fragment,null,icon('bi bi-cloud-arrow-down'),' Import online')),
                  h('a',{className:'btn-ghost text-center',href:t.manualDownload||t.download,target:'_blank',rel:'noopener noreferrer'},icon('bi bi-download'),' Download ZIP manually'),
                  h('button',{type:'button',className:'btn-ghost w-100',disabled:!!busy||!catalog?.zipSupported,onClick:()=>{setTitle(t.title);setLicense(t.license||'MIT');setThemeDialog('zip')}},icon('bi bi-file-earmark-zip'),' Import downloaded ZIP')
                )
              ))
            ),
            h('div',{className:'theme-modal-note small muted mt-3'},'Online import first tries PHP cURL, then PHP HTTPS streams, then container curl/wget when available. If the server cannot reach GitHub because of DNS/firewall/SSL restrictions, download the ZIP in your browser and use the local ZIP importer.')
          )
        ),
        document.body
      );
      const zipDialog=themeDialog==='zip'&&ReactDOM.createPortal(h('div',{className:'cms-modal-backdrop',onClick:closeDialog},h('div',{className:'cms-modal small',onClick:e=>e.stopPropagation()},
        h('div',{className:'d-flex justify-content-between align-items-start gap-3 mb-3'},h('div',null,h('h3',{className:'h4 mb-1'},'Import Bootstrap HTML / ThemeForest ZIP'),h('p',{className:'muted mb-0'},'Upload a theme ZIP you are licensed to use. LPCMS will analyse it and build a switchable adapter.')),h('button',{type:'button',className:'icon-btn',disabled:!!busy,onClick:closeDialog},icon('bi bi-x-lg'))),
        catalog&&!catalog.zipSupported&&h('div',{className:'alert alert-warning'},'No ZIP extraction engine is available. Enable PHP ZipArchive or the standard Phar/PharData extension.'),catalog?.zipSupported&&catalog?.archiveSupport?.preferred==='PharData'&&h('div',{className:'alert alert-info'},'PHP ZipArchive is not enabled. LPCMS will import this ZIP with the PharData fallback.'),
        h('div',{className:'theme-import-zone'},h('label',{className:'small fw-bold d-block mb-1'},'Theme ZIP'),h('input',{className:'form-control mb-3',type:'file',accept:'.zip,application/zip',disabled:!!busy||!catalog?.zipSupported,onChange:e=>{const f=e.target.files?.[0]||null;setZip(f);if(f&&!title)setTitle(f.name.replace(/\.zip$/i,''))}}),h('label',{className:'small fw-bold d-block mb-1'},'Theme name'),h('input',{className:'form-control mb-3',value:title,onChange:e=>setTitle(e.target.value),placeholder:'Theme name'}),h('label',{className:'small fw-bold d-block mb-1'},'Licence / purchase reference'),h('input',{className:'form-control mb-3',value:license,onChange:e=>setLicense(e.target.value),placeholder:'Licence / purchase reference note'}),h('button',{type:'button',className:'btn-cms w-100',disabled:!!busy||!zip||!catalog?.zipSupported,onClick:importZip},busy==='upload'?'Importing & rebuilding…':h(React.Fragment,null,icon('bi bi-file-earmark-zip'),' Import & rebuild for LPCMS'))),
        h('p',{className:'small muted mt-3 mb-0'},'Imported JavaScript remains disabled by default. LPCMS keeps source HTML as non-executable reference material and adapts the theme assets to the CMS layout.')
      )),document.body);
      return h(React.Fragment,null,
        h('div',{className:'admin-card'},h('div',{className:'d-flex justify-content-between align-items-start gap-2'},h('div',null,h('h4',{className:'h5 mb-1'},'Theme switcher'),h('p',{className:'small muted mb-0'},'Switch built-in or imported themes. Theme activation saves immediately and reloads the public design.')),api.isSuper&&h('button',{className:'btn-ghost',onClick:load},'Refresh')),api.isSuper?h('div',{className:'theme-grid mt-3'},presets.map(t=>card(t,'preset')),installed.map(t=>card(t,'imported'))):h('p',{className:'small muted mt-3'},'Editors can adjust colours below. Theme installation and switching requires super administrator access.')),
        api.isSuper&&h('div',{className:'admin-card'},h('h4',{className:'h5'},'Import themes'),h('p',{className:'small muted'},'Use separate import flows for licensed ZIP files and the built-in free online Bootstrap catalogue.'),h('div',{className:'theme-import-choice'},h('button',{type:'button',className:'btn-ghost',onClick:()=>setThemeDialog('zip')},icon('bi bi-file-earmark-zip'),' Import Bootstrap / ThemeForest ZIP'),h('button',{type:'button',className:'btn-cms',onClick:()=>setThemeDialog('free')},icon('bi bi-cloud-arrow-down'),' Browse free Bootstrap themes'))),
        api.isSuper&&h('div',{className:'admin-card'},h('h4',{className:'h6'},'Imported asset policy'),h(Bool,{api,path:'settings.themeManager.loadImportedCss',label:'Load imported theme CSS'}),h(Bool,{api,path:'settings.themeManager.loadImportedJs',label:'Load imported theme JavaScript (review first)'}),h('p',{className:'small muted mb-0'},'For security and stability, JavaScript from uploaded/online themes is copied but not executed unless you explicitly enable it and save. ThemeForest downloads are not fetched automatically; upload a ZIP you are licensed to use.')),
        h('div',{className:'admin-card'},h('h4',{className:'h5 mb-1'},'LPCMS design tokens'),h('p',{className:'small muted mb-2'},'Adjust the active theme palette and layout tokens.'),h('div',{className:'design-token-grid'},colors.map(k=>h('label',{key:k,className:'design-token-field'},h('span',{className:'small fw-bold'},k),h('input',{className:'form-control form-control-color',type:'color',value:get(api.data,'settings.theme.'+k)||'#000000',onChange:e=>api.update('settings.theme.'+k,e.target.value)})))),h('div',{className:'design-token-text-grid'},h(Txt,{api,path:'settings.theme.radius',label:'Radius'}),h(Txt,{api,path:'settings.theme.grid',label:'Bootstrap grid class'})),h(Field,{label:'Custom CSS'},h('textarea',{className:'form-control',rows:5,value:get(api.data,'settings.theme.customCss')||'',onChange:e=>api.update('settings.theme.customCss',e.target.value)})),h(Field,{label:'Custom SCSS source (stored only unless Sass library is enabled)'},h('textarea',{className:'form-control',rows:5,value:get(api.data,'settings.theme.customScss')||'',onChange:e=>api.update('settings.theme.customScss',e.target.value)})),h('button',{className:'btn-ghost',onClick:()=>compileScss(api)},'Compile SCSS to CSS')),
        freeDialog,zipDialog
      );
    }
    function compileScss(api){ if(!window.Sass){ api.setNotice('Enable Sass Builder in Libraries, save, and reload.'); return; } Sass.compile(api.data.settings.theme.customScss||'', res=>{ if(res.status===0) api.update('settings.theme.customCss',res.text); else api.setNotice(res.formatted||'SCSS error'); }); }
    function MenuTab({api}){
      const menu=api.data.header.menu||[];
      function upd(i,k,v){if(k==='label'){api.updateLocalized(`header.menu.${i}.label`,v);return;}const next=[...menu];next[i]={...next[i],[k]:v};api.update('header.menu',next)}
      function updChild(i,j,k,v){if(k==='label'){api.updateLocalized(`header.menu.${i}.children.${j}.label`,v);return;}const next=[...menu];const children=[...(next[i]?.children||[])];children[j]={...children[j],[k]:v};next[i]={...next[i],children};api.update('header.menu',next)}
      function add(){api.update('header.menu',[...menu,{label:'New item',href:'#section',target:'_self',children:[]}])}
      function addChild(i){const next=[...menu];const children=[...(next[i]?.children||[]),{label:'Submenu item',href:'#section',target:'_self'}];next[i]={...next[i],children};api.update('header.menu',next)}
      function remove(i){api.update('header.menu',menu.filter((_,x)=>x!==i))}
      function removeChild(i,j){const next=[...menu];next[i]={...next[i],children:(next[i]?.children||[]).filter((_,x)=>x!==j)};api.update('header.menu',next)}
      const targetSelect=(value,onChange)=>h('select',{className:'form-select',value:value==='_blank'?'_blank':'_self',onChange},h('option',{value:'_self'},'Default'),h('option',{value:'_blank'},'New tab'));
      return h(React.Fragment,null,
        h('div',{className:'admin-card'},h('h4',{className:'h5'},'Navigation menu'),h('p',{className:'small muted'},'Build clean dropdown menus without editing JSON. Each menu and submenu link can open normally or in a new browser tab.'),
          menu.map((m,i)=>h('div',{key:i,className:'menu-editor-card'},
            h('div',{className:'menu-row'},h('input',{className:'form-control',value:api.localizedValue(`header.menu.${i}.label`)??'',onChange:e=>upd(i,'label',e.target.value),placeholder:'Menu label'}),h('input',{className:'form-control',value:m.href||'',onChange:e=>upd(i,'href',e.target.value),placeholder:'#section or https://...'}),targetSelect(m.target,e=>upd(i,'target',e.target.value))),
            h('div',{className:'d-flex gap-2 mt-2 flex-wrap'},h('button',{type:'button',className:'btn-ghost',onClick:()=>addChild(i)},icon('bi bi-plus-lg'),' Add submenu'),h('button',{type:'button',className:'btn-ghost',onClick:()=>remove(i)},icon('bi bi-trash'),' Remove menu item')),
            (m.children||[]).length>0&&h('div',{className:'submenu-editor'},h('div',{className:'small fw-bold mb-2'},'Submenu'),(m.children||[]).map((c,j)=>h('div',{key:j,className:'submenu-editor-row'},h('input',{className:'form-control',value:api.localizedValue(`header.menu.${i}.children.${j}.label`)??'',onChange:e=>updChild(i,j,'label',e.target.value),placeholder:'Submenu label'}),h('input',{className:'form-control',value:c.href||'',onChange:e=>updChild(i,j,'href',e.target.value),placeholder:'URL or #section'}),targetSelect(c.target,e=>updChild(i,j,'target',e.target.value)),h('button',{type:'button',className:'mini-remove',title:'Remove submenu',onClick:()=>removeChild(i,j)},icon('bi bi-x-lg')))))
          )),h('button',{className:'btn-cms',onClick:add},icon('bi bi-plus-lg'),' Add menu item'))
      );
    }

    function ContentTab({api}){
      const allSecs=['hero','about','stats','features','services','process','gallery','testimonials','cards','contentList','team','growth','industries','logos','products','booking','adminShowcase','pricing','blog','faq','contact','cta'];
      const configured=get(api.data,'settings.sectionOrder')||allSecs;
      const order=[...configured.filter(x=>allSecs.includes(x)),...allSecs.filter(x=>!configured.includes(x))];
      const [drag,setDrag]=useState('');
      const slides=api.data.hero?.slides||[];

      function move(from,to){
        if(!from||from===to)return;
        const next=[...order];
        const a=next.indexOf(from),b=next.indexOf(to);
        if(a<0||b<0)return;
        next.splice(b,0,next.splice(a,1)[0]);
        api.update('settings.sectionOrder',next);
      }

      function listEditor(base,label,defaults){
        const arr=get(api.data,base+'.items')||[];
        function upd(i,k,v){
          if(k==='icon'){ const next=[...arr]; next[i]={...next[i],icon:v,image:''}; api.update(base+'.items',next); return; }
          if(['title','label','text','buttonText'].includes(k)){ api.updateLocalized(`${base}.items.${i}.${k}`,v); return; }
          const next=[...arr];
          next[i]={...next[i],[k]:v};
          api.update(base+'.items',next);
        }
        const rows=arr.map((it,i)=>{
          const fields=[];
          if(Object.prototype.hasOwnProperty.call(defaults,'image')){
            fields.push(h(EditableImage,{api,path:`${base}.items.${i}.image`,alt:it.title||it.label||'',className:'admin-image-field mb-2',key:'image'}));
          }
          if(Object.prototype.hasOwnProperty.call(defaults,'icon')){
            const iconChoices=[['bi bi-stars','Bootstrap · Stars'],['bi bi-lightning-charge','Bootstrap · Lightning'],['lucide:wand-sparkles','Lucide · Magic'],['lucide:rocket','Lucide · Rocket'],['lucide:shield-check','Lucide · Shield'],['tabler:palette','Tabler · Palette'],['tabler:device-desktop-cog','Tabler · Admin'],['tabler:photo-scan','Tabler · Media'],['material-symbols:edit-outline','Material · Edit'],['material-symbols:calendar-chat-outline','Material · Booking/chat'],['mdi:shopping-outline','Material Design · Shop'],['fluent:globe-24-regular','Fluent · Globe']];
            fields.push(h(Field,{label:'Free icon library',key:'icon'},h('div',{className:'d-flex gap-2 align-items-center'},h('span',{className:'cms-icon flex-shrink-0'},icon(it.icon||defaults.icon||'bi bi-stars')),h('select',{className:'form-select',value:it.icon||defaults.icon||'',onChange:e=>upd(i,'icon',e.target.value)},iconChoices.map(([v,l])=>h('option',{value:v,key:v},l))),h('button',{type:'button',className:'btn-ghost flex-shrink-0',onClick:()=>{try{sessionStorage.setItem('lpcms_icon_target',`${base}.items.${i}.icon`)}catch(x){} if(Object.prototype.hasOwnProperty.call(it,'image')) api.update(`${base}.items.${i}.image`,''); api.setAdminRequestedTab('icons')}},'More icons'))));
          }
          if(Object.prototype.hasOwnProperty.call(defaults,'title')){
            fields.push(h('input',{className:'form-control mb-2',value:api.localizedValue(`${base}.items.${i}.title`)??'',onChange:e=>upd(i,'title',e.target.value),placeholder:'Title',key:'title'}));
          }
          if(Object.prototype.hasOwnProperty.call(defaults,'label')){
            fields.push(h('input',{className:'form-control mb-2',value:api.localizedValue(`${base}.items.${i}.label`)??'',onChange:e=>upd(i,'label',e.target.value),placeholder:'Label',key:'label'}));
          }
          if(Object.prototype.hasOwnProperty.call(defaults,'text')){
            fields.push(h('textarea',{className:'form-control mb-2',rows:2,value:api.localizedValue(`${base}.items.${i}.text`)??'',onChange:e=>upd(i,'text',e.target.value),placeholder:'Text',key:'text'}));
          }
          if(Object.prototype.hasOwnProperty.call(defaults,'buttonText')){
            fields.push(h('div',{className:'row g-2',key:'button'},
              h('div',{className:'col-6'},h('input',{className:'form-control',value:api.localizedValue(`${base}.items.${i}.buttonText`)??'',onChange:e=>upd(i,'buttonText',e.target.value),placeholder:'Button text'})),
              h('div',{className:'col-6'},h('input',{className:'form-control',value:it.buttonHref||'',onChange:e=>upd(i,'buttonHref',e.target.value),placeholder:'#contact'}))
            ));
          }
          return h('div',{className:'simple-list-row',key:i},
            h('div',{className:'d-flex justify-content-between gap-2 mb-2'},
              h('strong',null,api.localizedValue(`${base}.items.${i}.title`)||api.localizedValue(`${base}.items.${i}.label`)||('Item '+(i+1))),
              h('button',{className:'mini-remove',onClick:()=>api.update(base+'.items',arr.filter((_,x)=>x!==i))},icon('bi bi-x-lg'))
            ),
            ...fields
          );
        });
        return h('div',{className:'admin-card'},
          h('div',{className:'d-flex justify-content-between align-items-center mb-2'},
            h('h5',{className:'mb-0'},label),
            h('button',{className:'btn-ghost',onClick:()=>api.update(base+'.items',[...arr,{...defaults}])},'Add item')
          ),
          ...rows
        );
      }

      const libraryCards=order.map(key=>h('div',{className:'section-preview-card',key},
        h('div',{className:'section-preview-mini'},h('span'),h('span'),h('span')),
        h('div',{className:'meta'},
          h('strong',null,key==='contentList'?'content list':key==='team'?'team lead':key),
          h('div',{className:'form-check form-switch mb-0'},
            h('input',{className:'form-check-input',type:'checkbox',checked:!!get(api.data,key+'.enabled'),onChange:e=>api.update(key+'.enabled',e.target.checked),'aria-label':'Show '+key})
          )
        ),
        h('select',{className:'form-select form-select-sm',value:String(get(api.data,'settings.sectionPatterns.'+key)||'1'),onChange:e=>api.update('settings.sectionPatterns.'+key,e.target.value)},
          h('option',{value:'1'},'Pattern 1 · Standard'),
          h('option',{value:'2'},'Pattern 2 · Alternate')
        )
      ));

      const orderRows=order.map(key=>h('div',{
        key,
        className:cls('section-sort-row',drag===key&&'dragging'),
        draggable:true,
        onDragStart:()=>setDrag(key),
        onDragEnd:()=>setDrag(''),
        onDragOver:e=>e.preventDefault(),
        onDrop:e=>{e.preventDefault();move(drag,key);setDrag('');}
      },
        h('span',{className:'drag-handle'},icon('bi bi-grip-vertical')),
        h('strong',null,key==='contentList'?'Content list':key==='team'?'Team lead':key==='gallery'?'Portfolio':key.charAt(0).toUpperCase()+key.slice(1)),
        h('span',{className:'small muted'},'Pattern '+String(get(api.data,'settings.sectionPatterns.'+key)||'1')),
        h('span',{className:'small'},get(api.data,key+'.enabled')?'On':'Off')
      ));

      const imageRows=slides.map((slide,i)=>h('div',{key:i,className:'border rounded-4 p-3 mb-3'},
        h('div',{className:'small fw-bold mb-2'},api.t('Hero slide ') +(i+1)),
        h(Field,{label:'Hero media type'},h('select',{className:'form-select',value:String(slide.mediaType||((slide.video||'')?'video':'image')),onChange:e=>api.update(`hero.slides.${i}.mediaType`,e.target.value)},h('option',{value:'video'},api.t('Looping demo video')),h('option',{value:'image'},api.t('Image')))),
        String(slide.mediaType||((slide.video||'')?'video':'image'))==='video'&&h(React.Fragment,null,h(VideoUploadField,{api,path:`hero.slides.${i}.video`,label:'Hero video'}),h('div',{className:'small fw-bold mb-1'},api.t('Hero video poster')),h(EditableImage,{api,path:`hero.slides.${i}.videoPoster`,alt:slide.title||'',className:'admin-image-field'}),h(Bool,{api,path:`hero.slides.${i}.videoAutoplay`,label:'Autoplay muted video'}),h(Bool,{api,path:`hero.slides.${i}.videoLoop`,label:'Loop video'})),
        String(slide.mediaType||((slide.video||'')?'video':'image'))!=='video'&&h(React.Fragment,null,h('div',{className:'small fw-bold mb-1'},api.t('Hero image')),h(EditableImage,{api,path:`hero.slides.${i}.image`,alt:slide.title||'',className:'admin-image-field'}))
      ));

      const library=h('div',{className:'admin-card'},
        h('h5',null,'Visual section library'),
        h('p',{className:'small muted'},'Turn sections on/off from visual previews, then choose the alternate layout. Drag the order below.'),
        h('div',{className:'section-library-grid'},...libraryCards)
      );
      const sectionOrder=h('div',{className:'admin-card'},h('h5',null,'Section order'),...orderRows);
      const sectionImages=h('div',{className:'admin-card'},
        h('h5',null,'Section images'),
        ...imageRows,
        h('div',{className:'small fw-bold mb-1'},'About image'),
        h(EditableImage,{api,path:'about.image',alt:api.data.about?.title||'',className:'admin-image-field'}),
        h('div',{className:'small fw-bold mb-1'},'Features image'),
        h(EditableImage,{api,path:'features.image',alt:api.data.features?.title||'',className:'admin-image-field'})
      );
      const visualText=h('div',{className:'admin-card'},
        h('h5',null,'Visual text editor'),
        h(RichText,{api,path:'about.body',label:'About body',large:true}),
        h(RichText,{api,path:'cta.text',label:'CTA text / HTML',large:true})
      );


      const showcaseRows=get(api.data,'adminShowcase.rows')||[];
      const showcaseOptions=get(api.data,'adminShowcase.domainOptions')||[];
      const showcaseEditor=h('div',{className:'admin-card'},
        h('h5',null,'How the admin works'),
        h(Txt,{api,path:'adminShowcase.title',label:'Section title'}),
        h(Txt,{api,path:'adminShowcase.subtitle',label:'Section subtitle'}),h(Txt,{api,path:'adminShowcase.superAdminTitle',label:'Super Admin showcase title'}),h(Field,{label:'Super Admin showcase text'},h('textarea',{className:'form-control',rows:3,value:api.localizedValue('adminShowcase.superAdminText')??'',onChange:e=>api.updateLocalized('adminShowcase.superAdminText',e.target.value)})),h(Txt,{api,path:'adminShowcase.superAdminImage',label:'Super Admin screenshot URL'}),
        h(Txt,{api,path:'adminShowcase.domainTitle',label:'Domain choice title'}),
        h(Field,{label:'Domain choice text'},h('textarea',{className:'form-control',rows:3,value:api.localizedValue('adminShowcase.domainText')??'',onChange:e=>api.updateLocalized('adminShowcase.domainText',e.target.value)})),
        h('div',{className:'small fw-bold mb-2'},'Domain options'),
        ...showcaseOptions.map((opt,i)=>h('div',{className:'simple-list-row',key:'domain-'+i},
          h('input',{className:'form-control mb-2',value:api.localizedValue(`adminShowcase.domainOptions.${i}.title`)??'',onChange:e=>api.updateLocalized(`adminShowcase.domainOptions.${i}.title`,e.target.value),placeholder:'Title'}),
          h('textarea',{className:'form-control mb-2',rows:2,value:api.localizedValue(`adminShowcase.domainOptions.${i}.text`)??'',onChange:e=>api.updateLocalized(`adminShowcase.domainOptions.${i}.text`,e.target.value),placeholder:'Text'}),
          h('input',{className:'form-control',value:opt.example||'',onChange:e=>api.update(`adminShowcase.domainOptions.${i}.example`,e.target.value),placeholder:'Example domain'})
        )),
        h(Txt,{api,path:'adminShowcase.tableTitle',label:'Capability table title'}),
        h(Txt,{api,path:'adminShowcase.tableSubtitle',label:'Section subtitle'})
      );
      const showcaseSuperItems=get(api.data,'adminShowcase.superAdminItems')||[];
      const showcaseSuperEditor=h('div',{className:'admin-card'},
        h('div',{className:'d-flex justify-content-between align-items-center mb-2'},h('h5',{className:'mb-0'},'Super Admin capabilities'),h('span',{className:'small muted'},'Icons from free libraries')),
        ...showcaseSuperItems.map((it,i)=>h('div',{className:'simple-list-row',key:i},
          h('div',{className:'d-flex align-items-center gap-2 mb-2'},h('span',{className:'cms-icon'},icon(it.icon||'bi bi-check-circle')),h('strong',null,api.localizedValue(`adminShowcase.superAdminItems.${i}.title`)||('Item '+(i+1)))),
          h('input',{className:'form-control mb-2',value:api.localizedValue(`adminShowcase.superAdminItems.${i}.title`)??'',onChange:e=>api.updateLocalized(`adminShowcase.superAdminItems.${i}.title`,e.target.value),placeholder:'Title'}),
          h('textarea',{className:'form-control mb-2',rows:2,value:api.localizedValue(`adminShowcase.superAdminItems.${i}.text`)??'',onChange:e=>api.updateLocalized(`adminShowcase.superAdminItems.${i}.text`,e.target.value),placeholder:'Text'}),
          h('button',{type:'button',className:'btn-ghost',onClick:()=>{try{sessionStorage.setItem('lpcms_icon_target',`adminShowcase.superAdminItems.${i}.icon`)}catch(e){}api.setAdminRequestedTab('icons')}},'Choose free icon')
        ))
      );

      const showcaseTableEditor=h('div',{className:'admin-card'},
        h('div',{className:'d-flex justify-content-between align-items-center mb-2'},h('h5',{className:'mb-0'},'Capability rows'),h('button',{className:'btn-ghost',onClick:()=>api.update('adminShowcase.rows',[...showcaseRows,{title:'New task',editor:true,super:true,ease:'Simple fields'}])},'Add item')),
        ...showcaseRows.map((row,i)=>h('div',{className:'simple-list-row',key:i},
          h('div',{className:'d-flex justify-content-between gap-2 mb-2'},h('strong',null,api.localizedValue(`adminShowcase.rows.${i}.title`)||('Item '+(i+1))),h('button',{className:'mini-remove',onClick:()=>api.update('adminShowcase.rows',showcaseRows.filter((_,x)=>x!==i))},icon('bi bi-x-lg'))),
          h('input',{className:'form-control mb-2',value:api.localizedValue(`adminShowcase.rows.${i}.title`)??'',onChange:e=>api.updateLocalized(`adminShowcase.rows.${i}.title`,e.target.value),placeholder:'Task'}),
          h('input',{className:'form-control mb-2',value:api.localizedValue(`adminShowcase.rows.${i}.ease`)??'',onChange:e=>api.updateLocalized(`adminShowcase.rows.${i}.ease`,e.target.value),placeholder:'How easy?'}),
          h('div',{className:'row g-2'},h('div',{className:'col-6'},h(Bool,{api,path:`adminShowcase.rows.${i}.editor`,label:'Editor'})),h('div',{className:'col-6'},h(Bool,{api,path:`adminShowcase.rows.${i}.super`,label:'Super Admin'})))
        ))
      );

      return h(React.Fragment,null,
        library,
        sectionOrder,
        showcaseEditor,
        showcaseSuperEditor,
        listEditor('features','Features',{image:'',icon:'lucide:wand-sparkles',title:'New feature',text:'Feature description'}),
        listEditor('services','Services',{image:'',icon:'tabler:briefcase',title:'New service',text:'Service description',buttonText:'Read more',buttonHref:'#contact'}),
        listEditor('process','How it works',{image:'',icon:'lucide:circle-check-big',title:'New step',text:'Step description'}),
        listEditor('adminShowcase','Admin screenshots',{image:'',title:'Admin screenshot',text:'Description'}),
        showcaseTableEditor,
        sectionImages,
        listEditor('cards','Image cards',{image:'',icon:'lucide:layout-template',title:'New card',text:'Card text.',buttonText:'Learn more',buttonHref:'#contact'}),
        listEditor('contentList','Content list',{image:'',icon:'lucide:list-checks',title:'New item',text:'Content text.',buttonText:'Learn more',buttonHref:'#contact'}),
        listEditor('logos','Logo strip',{image:'',label:'Client / partner'}),
        visualText
      );
    }

    const COMMERCE_INTEGRATIONS = [
      ['payments','paypal','PayPal'],['payments','stripe','Stripe'],['payments','braintree','Braintree'],['payments','klarna','Klarna'],['payments','apple-pay','Apple Pay'],['payments','google-pay','Google Pay'],['payments','paysera','Paysera'],['payments','swedbank','Swedbank'],['payments','seb','SEB'],['payments','luminor','Luminor'],['payments','klix','Klix by Citadele'],['payments','urbo','Urbo'],['payments','siauliu','Siauliu bankas'],['payments','lhv','LHV'],['payments','coop-pank','Coop Pank'],['payments','mobilepay','MobilePay'],['payments','nordea','Nordea'],['payments','giropay','Giropay'],['payments','ideal','iDEAL'],['payments','revolut','Revolut'],['payments','n26','N26'],['payments','exactly','Exactly'],['payments','makecommerce','MakeCommerce'],['payments','esto','ESTO'],['payments','blueorange','BlueOrange'],['payments','decta','Decta'],['payments','payscz','pays.cz'],['payments','przelewy24','Przelewy24'],
      ['shipping','ups','UPS'],['shipping','dhl','DHL Express'],['shipping','fedex','FedEx'],['shipping','usps','USPS'],['shipping','lasership','LaserShip'],['shipping','ontrac','OnTrac'],['shipping','veho','Veho'],['shipping','axlehire','AxleHire'],['shipping','better-trucks','Better Trucks'],['shipping','swyft','Swyft'],['shipping','apc-postal','APC Postal'],['shipping','epost-global','ePost Global'],['shipping','airterra','Airterra'],['shipping','australia-post','Australia Post'],['shipping','sendle','Sendle'],['shipping','canada-post','Canada Post'],['shipping','purolator','Purolator'],['shipping','chronopost','Chronopost'],['shipping','colissimo','Colissimo'],['shipping','royal-mail','Royal Mail'],['shipping','evri','Evri'],['shipping','parcelforce','Parcelforce'],['shipping','deutsche-post','Deutsche Post'],['shipping','poste-italiane','Poste Italiane'],['shipping','correos','Correos'],['shipping','dpd','DPD'],['shipping','smartpost','Smartpost'],['shipping','omniva','Omniva'],['shipping','venipak','Venipak'],['shipping','shippo','Shippo'],['shipping','shipstation','ShipStation'],['shipping','swotzy','Swotzy'],['shipping','packeta','Packeta'],['shipping','mans-pasts','Mans Pasts'],['shipping','eksprespasts','Eksprespasts'],['shipping','lp-express','LP Express'],['shipping','unisend','Unisend'],['shipping','qwqer','QWQER'],
      ['other','ga','Google Analytics'],['other','meta','Meta'],['other','cloudflare','Cloudflare'],['other','eprel','EPREL Database'],['other','vies','EU VIES VAT validation'],['other','resico','IBSC Resico'],['other','paytraq','Paytraq'],['other','api','Custom API / webhook']
    ];
    function CommerceIntegrations({api}){
      const providers=Array.isArray(get(api.data,'settings.commerceIntegrations.providers'))?get(api.data,'settings.commerceIntegrations.providers'):[];
      function enabled(id){return !!providers.find(p=>p?.id===id)?.enabled}
      function toggle(id,value){const idx=providers.findIndex(p=>p?.id===id);const next=[...providers];if(idx>=0)next[idx]={...next[idx],enabled:value};else next.push({id,enabled:value});api.update('settings.commerceIntegrations.providers',next)}
      return h('div',{className:'admin-card'},
        h('h4',{className:'h5'},'E-commerce integrations'),
        h('p',{className:'small muted'},'A Mozello-style connection catalogue for payment, shipping and business services. Provider cards are configuration profiles; use the custom HTTPS webhook below to connect live order events to Make, Zapier, n8n, your ERP, or a provider-specific adapter.'),
        ['payments','shipping','other'].map(group=>h('div',{key:group,className:'mb-3'},h('div',{className:'small fw-bold text-uppercase mb-2'},group),h('div',{className:'commerce-integration-grid'},COMMERCE_INTEGRATIONS.filter(x=>x[0]===group).map(([_,id,label])=>h('div',{className:'integration-card',key:id},h('div',null,h('strong',null,label),h('span',{className:'small muted'},enabled(id)?'Enabled profile':'Available')),h('div',{className:'form-check form-switch'},h('input',{className:'form-check-input',type:'checkbox',checked:enabled(id),onChange:e=>toggle(id,e.target.checked),'aria-label':'Enable '+label}))))))),
        h('hr'),h('h5',{className:'h6'},'Checkout connection'),h('p',{className:'small muted'},'Order form keeps the current bank-transfer flow. Hosted payment link redirects the cart to a payment page. Checkout adapter POSTs an authoritative server-priced cart to your HTTPS integration and expects a JSON payment URL back.'),h(Field,{label:'Checkout mode'},h('select',{className:'form-select',value:get(api.data,'settings.commerceIntegrations.checkout.mode')||'order-form',onChange:e=>api.update('settings.commerceIntegrations.checkout.mode',e.target.value)},h('option',{value:'order-form'},'Order form / bank transfer'),h('option',{value:'payment-link'},'Hosted payment link'),h('option',{value:'adapter'},'HTTPS payment adapter'))),get(api.data,'settings.commerceIntegrations.checkout.mode')==='payment-link'&&h(Txt,{api,path:'settings.commerceIntegrations.checkout.paymentLink',label:'Hosted payment URL'}),get(api.data,'settings.commerceIntegrations.checkout.mode')==='adapter'&&h(React.Fragment,null,h(Txt,{api,path:'settings.commerceIntegrations.checkout.adapterUrl',label:'Checkout adapter HTTPS URL'}),h(Txt,{api,path:'settings.commerceIntegrations.checkout.bearerToken',label:(get(api.data,'settings.commerceIntegrations.checkout.bearerTokenSet')?'Adapter bearer token (saved - enter to replace)':'Adapter bearer token'),type:'password'})),
        h('hr'),h('h5',{className:'h6'},'Order webhook'),h(Bool,{api,path:'settings.commerceIntegrations.webhook.enabled',label:'Send order submissions to HTTPS webhook'}),h(Txt,{api,path:'settings.commerceIntegrations.webhook.url',label:'Webhook URL'}),h(Txt,{api,path:'settings.commerceIntegrations.webhook.bearerToken',label:(get(api.data,'settings.commerceIntegrations.webhook.bearerTokenSet')?'Bearer token (saved - enter to replace)':'Bearer token (optional)'),type:'password'}),h('p',{className:'small muted mb-0'},'Secrets are masked in the browser after saving. Order submissions are posted as JSON with event order.submitted.')
      );
    }

    function ProductsTab({api}){
      const arr=api.data.products?.items||[];
      const cfg=api.data.products||{};
      function upd(i,k,v){ if(['title','text','discountLabel'].includes(k)){api.updateLocalized(`products.items.${i}.${k}`,v);return;} const next=[...arr]; next[i]={...next[i],[k]:v}; api.update('products.items',next); }
      function replaceItem(i,v){ const next=[...arr]; next[i]=v; api.update('products.items',next); }
      function add(){ api.update('products.items',[...arr,{title:'New product',slug:'new-product',id:String(Date.now()).slice(-6),sku:'',type:'simple',category:'',brand:'',tags:[],price:0,oldPrice:0,discountLabel:'',currency:cfg.currency||'EUR',image:'',gallery:[],attributes:{},variations:[],stock:0,inStock:true,text:'Short product text.',body:'<p>Product description.</p>',features:[],purchaseMode:cfg.defaultPurchaseMode||'quote',checkoutUrl:'',buyUrl:'',buyButton:true,quoteButton:true,seo:{title:'New product',description:'',image:''}}]); }
      return h(React.Fragment,null,
        h('div',{className:'alert alert-info'},'Enable Shop mode to run products like a flat-file WooCommerce-style catalogue: product IDs/SKUs, categories, variation filters, discounts, gallery/modal view, cart, quote basket, and direct bank transfer checkout.'),
        api.isSuper&&h(CommerceIntegrations,{api}),
        h('div',{className:'admin-card'},
          h('div',{className:'d-flex align-items-center justify-content-between gap-2 mb-2'}, h('h4',{className:'mb-0'},'Shop / catalogue settings'), api.isSuper&&h('button',{type:'button',className:'btn-ghost',onClick:()=>{ if(confirm(api.t('Replace current products with 20 demo products that show all filters?'))) api.update('products.items',LPCMS_DEMO_PRODUCTS); }},'Import 20 demo products')),
          h(Bool,{api,path:'products.enabled',label:'Show products/catalogue section'}),
          h(Bool,{api,path:'products.shopEnabled',label:'Enable shop mode'}),
          h(Bool,{api,path:'products.searchEnabled',label:'Enable Ajax product search'}),
          h(Bool,{api,path:'products.filterEnabled',label:'Enable category/variation filters'}),
          h(Bool,{api,path:'products.cartEnabled',label:'Enable shopping cart'}),
          h(Bool,{api,path:'products.basketEnabled',label:'Enable quote basket'}),
          h(Txt,{api,path:'products.title',label:'Section title'}),
          h(Txt,{api,path:'products.subtitle',label:'Section subtitle'}),
          h('div',{className:'row g-2'},
            h('div',{className:'col-6'},h(Txt,{api,path:'products.homeLimit',label:'Initial product count',type:'number'})),
            h('div',{className:'col-6'},h(Txt,{api,path:'products.batch',label:'Load-more amount',type:'number'})),
            h('div',{className:'col-6'},h(Txt,{api,path:'products.currency',label:'Default currency'})),
            h('div',{className:'col-6'},h(Txt,{api,path:'products.buttonText',label:'Load more text'}))
          )
        ),
        h('div',{className:'admin-card'},
          h('h4',null,'Buttons and checkout'),
          h(Field,{label:'Default product action'},h('select',{className:'form-select',value:cfg.defaultPurchaseMode||'quote',onChange:e=>api.update('products.defaultPurchaseMode',e.target.value)},h('option',{value:'quote'},'Quote product'),h('option',{value:'checkout'},'Pay / Checkout now'))),
          h(Bool,{api,path:'products.showBuyButton',label:'Enable checkout buttons'}),
          h(Bool,{api,path:'products.showQuoteButton',label:'Enable quote buttons'}),
          h(Txt,{api,path:'products.buyButtonText',label:'Buy button text'}),
          h(Txt,{api,path:'products.quoteButtonText',label:'Quote button text'}),
          h(Txt,{api,path:'products.cartTitle',label:'Cart title'}),
          h(Txt,{api,path:'products.cartButtonText',label:'Cart checkout button'}),
          h(Txt,{api,path:'products.checkoutButtonText',label:'Order form submit button'})
        ),
        api.isSuper&&h('div',{className:'admin-card technical-box'},
          h('h4',null,'Super admin · categories, attributes and payment'),
          h(Field,{label:'Categories JSON'}, h('textarea',{className:'form-control',rows:4,value:JSON.stringify(cfg.categories||[],null,2),onChange:e=>{try{api.update('products.categories',JSON.parse(e.target.value))}catch(err){}}})),
          h(Field,{label:'Attribute filters JSON'}, h('textarea',{className:'form-control',rows:5,value:JSON.stringify(cfg.attributeFilters||[],null,2),onChange:e=>{try{api.update('products.attributeFilters',JSON.parse(e.target.value))}catch(err){}}})),
          h(Bool,{api,path:'products.payment.directDebit.enabled',label:'Enable direct debit / bank transfer instructions'}),
          h(Txt,{api,path:'products.payment.directDebit.title',label:'Payment title'}),
          h(Field,{label:'Payment instructions'}, h('textarea',{className:'form-control',rows:3,value:api.localizedValue('products.payment.directDebit.instructions')??'',onChange:e=>api.updateLocalized('products.payment.directDebit.instructions',e.target.value)})),
          h(Txt,{api,path:'products.payment.directDebit.accountName',label:'Account name'}),
          h(Txt,{api,path:'products.payment.directDebit.iban',label:'IBAN'}),
          h(Txt,{api,path:'products.payment.directDebit.swift',label:'SWIFT/BIC'}),
          h(Txt,{api,path:'products.payment.directDebit.bankName',label:'Bank name'}),
          h(Txt,{api,path:'products.payment.directDebit.referencePrefix',label:'Reference prefix'})
        ),
        arr.map((it,i)=>h('div',{key:i,className:'admin-card'},
          h('div',{className:'d-flex justify-content-between gap-2 mb-2'}, h('strong',null,api.localizedValue(`products.items.${i}.title`)||'Product'), h('button',{className:'btn-ghost',onClick:()=>api.update('products.items',arr.filter((_,x)=>x!==i))},'Remove')),
          h('input',{className:'form-control mb-2',value:api.localizedValue(`products.items.${i}.title`)??'',onChange:e=>upd(i,'title',e.target.value),placeholder:'Product title'}),
          h('div',{className:'row g-2'},
            h('div',{className:'col-6'},h('input',{className:'form-control mb-2',value:it.slug||'',onChange:e=>upd(i,'slug',e.target.value),placeholder:'slug'})),
            h('div',{className:'col-6'},h('input',{className:'form-control mb-2',value:it.id||'',onChange:e=>upd(i,'id',e.target.value),placeholder:'Product ID'})),
            h('div',{className:'col-6'},h('input',{className:'form-control mb-2',value:it.sku||'',onChange:e=>upd(i,'sku',e.target.value),placeholder:'SKU'})),
            h('div',{className:'col-6'},h('input',{className:'form-control mb-2',value:it.category||'',onChange:e=>upd(i,'category',e.target.value),placeholder:'Category slug'})),
            h('div',{className:'col-6'},h('input',{className:'form-control mb-2',type:'number',value:it.price||0,onChange:e=>upd(i,'price',Number(e.target.value||0)),placeholder:'Price'})),
            h('div',{className:'col-6'},h('input',{className:'form-control mb-2',type:'number',value:it.oldPrice||0,onChange:e=>upd(i,'oldPrice',Number(e.target.value||0)),placeholder:'Old price'}))
          ),
          h('div',{className:'row g-2 mb-2'},h('div',{className:api.isSuper?'col-5':'col-12'},h(Field,{label:'Product action'},h('select',{className:'form-select',value:it.purchaseMode||cfg.defaultPurchaseMode||'quote',onChange:e=>upd(i,'purchaseMode',e.target.value)},h('option',{value:'quote'},'Quote'),h('option',{value:'checkout'},'Checkout now')))),api.isSuper&&h('div',{className:'col-7'},h(Txt,{api,path:`products.items.${i}.checkoutUrl`,label:'Direct checkout URL (optional)'}))),
          h(EditableImage,{api,path:`products.items.${i}.image`,alt:it.title||'',className:'admin-image-field'}),
          h('input',{className:'form-control mb-2',value:it.image||'',onChange:e=>upd(i,'image',e.target.value),placeholder:'Main image URL'}),
          h('textarea',{className:'form-control mb-2',rows:2,value:api.localizedValue(`products.items.${i}.text`)??'',onChange:e=>upd(i,'text',e.target.value),placeholder:'Card text'}),
          h(RichText,{api,path:`products.items.${i}.body`,label:'Visual product description',large:true}),
          api.isSuper&&h(Field,{label:'Super admin · full product JSON (variations, attributes, gallery, stock, SEO)'}, h('textarea',{className:'form-control',rows:9,value:JSON.stringify(it,null,2),onChange:e=>{try{replaceItem(i,JSON.parse(e.target.value))}catch(err){}}})),
          h('p',{className:'small muted'},api.isSuper?'Variable product settings and direct checkout URLs are available here for super admin. Editors keep the simple product fields and action selector.':'Use the simple fields above to edit product content, price, image and action. Technical integration settings are managed by the super administrator.')
        )),
        h('button',{className:'btn-cms',onClick:add},'Add product')
      );
    }

    function BlogTab({api}){ const arr=api.data.blog?.items||[];
      function value(i,k){return api.localizedValue(`blog.items.${i}.${k}`)??'';}
      function upd(i,k,v){ if(['title','excerpt'].includes(k)){api.updateLocalized(`blog.items.${i}.${k}`,v);return;} const next=[...arr]; next[i]={...next[i],[k]:v}; api.update('blog.items',next); }
      function add(){ api.update('blog.items',[...arr,{title:'New post',slug:'new-post',date:new Date().toISOString().slice(0,10),image:'',excerpt:'Short post excerpt.',body:'<p>Write the post content here.</p>',seo:{title:'New post',description:'',image:''}}]); }
      return h(React.Fragment,null,
        h('div',{className:'admin-card'}, h(Bool,{api,path:'blog.enabled',label:'Enable blog section'}), h(Txt,{api,path:'blog.homeLimit',label:'Posts shown initially',type:'number'}), h(Txt,{api,path:'blog.batch',label:'Posts added per click',type:'number'}), h(Bool,{api,path:'blog.showButton',label:'Show load more button'})),
        arr.map((it,i)=>h('div',{key:i,className:'admin-card'},
          h('div',{className:'d-flex justify-content-between gap-2 mb-2'}, h('strong',null,value(i,'title')||'Post'), h('button',{className:'btn-ghost',onClick:()=>api.update('blog.items',arr.filter((_,x)=>x!==i))},'Remove')),
          h('input',{className:'form-control mb-2',value:value(i,'title'),onChange:e=>upd(i,'title',e.target.value),placeholder:'Post title'}),
          h('input',{className:'form-control mb-2',value:it.slug||'',onChange:e=>upd(i,'slug',e.target.value),placeholder:'slug'}),
          h('input',{className:'form-control mb-2',value:it.date||'',onChange:e=>upd(i,'date',e.target.value),placeholder:'YYYY-MM-DD'}),
          h(EditableImage,{api,path:`blog.items.${i}.image`,alt:value(i,'title'),className:'admin-image-field'}),
          h('input',{className:'form-control mb-2',value:it.image||'',onChange:e=>upd(i,'image',e.target.value),placeholder:'Image URL (optional)'}),
          h('textarea',{className:'form-control mb-2',rows:2,value:value(i,'excerpt'),onChange:e=>upd(i,'excerpt',e.target.value),placeholder:'Excerpt'}),
          h(RichText,{api,path:`blog.items.${i}.body`,label:'Visual post body',large:true}),
          h('div',{className:'row g-2'},
            h('div',{className:'col-12'},h('input',{className:'form-control mb-2',value:api.localizedValue(`blog.items.${i}.seo.title`)??'',onChange:e=>api.updateLocalized(`blog.items.${i}.seo.title`,e.target.value),placeholder:'SEO title'})),
            h('div',{className:'col-12'},h('textarea',{className:'form-control',rows:2,value:api.localizedValue(`blog.items.${i}.seo.description`)??'',onChange:e=>api.updateLocalized(`blog.items.${i}.seo.description`,e.target.value),placeholder:'SEO description'}))
          )
        )), h('button',{className:'btn-cms',onClick:add},'Add blog post'));
    }

    function MediaTab({api}){
      const [target,setTarget]=useState('uploads'); const [out,setOut]=useState(null); const [dragging,setDragging]=useState(false); const [pendingFile,setPendingFile]=useState(null); const inputRef=useRef(null);
      const sources=get(api.data,'settings.mediaSources')||{}; const [provider,setProvider]=useState(sources.provider||'free'); const [q,setQ]=useState('business'); const [results,setResults]=useState([]); const [searching,setSearching]=useState(false); const [page,setPage]=useState(1); const [importing,setImporting]=useState(''); const [imagePrompt,setImagePrompt]=useState('Create a clean, modern website hero image for my business, no text, high quality.');
      const topics=['business','technology','nature','food','travel','people','office','architecture','fitness','fashion','education','health','abstract'];
      async function uploadImageFile(file){if(!file)return;setOut({message:'Preparing upload...'});try{const j=await uploadCmsImage(api,file,target);setOut({message:j.message||((j.format||'').toUpperCase()+' uploaded'),url:j.url,format:j.format,width:j.width,height:j.height,bytes:j.bytes});if(target==='gallery'){const items=api.data.gallery.items||[];api.update('gallery.items',[...items,{src:j.url,alt:String(file.name||'Image').replace(/\.[^.]+$/,'')}]);}setPendingFile(null)}catch(err){setOut({error:err?.message||'Upload failed.'})}finally{setDragging(false);if(inputRef.current)inputRef.current.value=''}}
      async function uploadFile(file){if(!file)return;if(target!=='docs'){setDragging(false);setPendingFile(file);setOut({message:'Choose cover / crop options'});return}setOut({message:'Preparing upload...'});try{const fd=new FormData();fd.append('file',file,file.name||'document.pdf');fd.append('target',target);const r=await fetch('?api=upload',{method:'POST',headers:{'X-CMS-CSRF':api.csrf},body:fd});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Upload failed.');setOut({message:j.message||'PDF uploaded',url:j.url,format:j.format,width:j.width,height:j.height,bytes:j.bytes})}catch(err){setOut({error:err?.message||'Upload failed.'})}finally{setDragging(false);if(inputRef.current)inputRef.current.value=''}}
      async function search(nextPage=1,term=q){const text=String(term||'').trim();if(!text)return;setSearching(true);setOut(null);try{const r=await fetch('?api=media-search&provider='+encodeURIComponent(provider)+'&q='+encodeURIComponent(text)+'&page='+nextPage);const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Media search failed.');setResults(j.results||[]);setPage(nextPage);setQ(text)}catch(e){setOut({error:e.message});setResults([])}finally{setSearching(false)}}
      async function importFree(item){if(target==='docs'){setOut({error:'Choose uploads, img, or gallery before importing an image.'});return}setImporting(item.id||item.url);try{const r=await fetch('?api=media-import',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},body:JSON.stringify({url:item.url,thumb:item.thumb,target,title:item.title,creator:item.creator,source:item.source,license:item.license,licenseUrl:item.licenseUrl,landing:item.landing,...(window.__LPCMS_MEDIA_SETTINGS||{})})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Import failed.');setOut({message:j.message,url:j.url,format:j.format,width:j.width,height:j.height,bytes:j.bytes});if(target==='gallery'){const items=api.data.gallery.items||[];api.update('gallery.items',[...items,{src:j.url,alt:item.title||'Imported image',credit:item.creator||'',license:item.license||'',sourceUrl:item.landing||''}])}}catch(e){setOut({error:e.message})}finally{setImporting('')}}
      const targetField=h(Field,{label:'Upload / import target'},h('select',{className:'form-select',value:target,onChange:e=>{setTarget(e.target.value);setOut(null);setPendingFile(null)}},h('option',{value:'uploads'},'assets/uploads'),h('option',{value:'img'},'assets/img'),h('option',{value:'gallery'},'assets/gallery'),h('option',{value:'docs'},'assets/docs')));
      const mediaCfg=get(api.data,'settings.media')||{}; const boxPresets=[['large','Large · 1920×1920'],['hero','Hero · 1600×900'],['content','Content · 1200×900'],['card','Card · 900×675'],['square','Square · 800×800'],['logo','Logo · 800×400'],['custom','Custom']]; const presetDims={large:[1920,1920],hero:[1600,900],content:[1200,900],card:[900,675],square:[800,800],logo:[800,400]}; function setBoxPreset(v){api.update('settings.media.boxPreset',v);if(presetDims[v]){api.update('settings.media.boxWidth',presetDims[v][0]);api.update('settings.media.boxHeight',presetDims[v][1]);}}
      const mediaBoxControls=h('div',{className:'media-box-controls mb-3'},h('div',{className:'small fw-bold mb-2'},'AVIF scale box'),h('div',{className:'row g-2'},h('div',{className:'col-md-6'},h('select',{className:'form-select',value:mediaCfg.boxPreset||'large',onChange:e=>setBoxPreset(e.target.value)},boxPresets.map(([v,l])=>h('option',{value:v,key:v},l)))),h('div',{className:'col-6 col-md-3'},h('input',{className:'form-control',type:'number',min:64,max:4096,value:Number(mediaCfg.boxWidth||1920),onChange:e=>api.update('settings.media.boxWidth',Number(e.target.value)||1920),'aria-label':'Box width'})),h('div',{className:'col-6 col-md-3'},h('input',{className:'form-control',type:'number',min:64,max:4096,value:Number(mediaCfg.boxHeight||1920),onChange:e=>api.update('settings.media.boxHeight',Number(e.target.value)||1920),'aria-label':'Box height'}))),h('div',{className:'small muted mt-2'},'Every uploaded or imported image is fitted inside this box without stretching, metadata is removed, and the saved file is AVIF.'));
      const dropzone=h('div',{className:cls('cms-upload-dropzone',dragging&&'dragging'),onClick:()=>inputRef.current?.click(),onDragEnter:e=>{e.preventDefault();setDragging(true)},onDragOver:e=>{e.preventDefault();e.dataTransfer.dropEffect='copy';setDragging(true)},onDragLeave:e=>{e.preventDefault();if(!e.currentTarget.contains(e.relatedTarget))setDragging(false)},onDrop:e=>{e.preventDefault();setDragging(false);uploadFile(e.dataTransfer.files?.[0])}},icon(target==='docs'?'bi bi-file-earmark-pdf':'bi bi-crop'),h('strong',null,dragging?'Drop file here':target==='docs'?'Click or drag & drop PDF':'Click or drag & drop image'),h('div',{className:'small muted'},target==='docs'?'PDF documents up to 20MB':'Choose crop if needed; LPCMS scales to the configured box and saves AVIF only'),h('input',{ref:inputRef,type:'file',accept:target==='docs'?'application/pdf':'image/*,.avif,.heic,.heif',onChange:e=>uploadFile(e.target.files?.[0])}));
      const result=out&&h('div',{className:cls('alert mt-3',out.error?'alert-danger':'alert-info')},out.error||out.message,out.url&&h(React.Fragment,null,h('div',{className:'mt-2'},'URL: ',h('code',null,out.url)),out.format&&h('div',{className:'small mt-1'},[out.width&&out.height?`${out.width}×${out.height}`:'',out.bytes?`${Math.round(out.bytes/1024)} KB`:'' ].filter(Boolean).join(' · ')),out.format&&out.format!=='pdf'&&h('div',{className:'cms-upload-result'},h('img',{src:out.url,alt:'',loading:'lazy',decoding:'async'}))));
      return h(React.Fragment,null,
        h('div',{className:'admin-card'},h('h4',{className:'h5'},'Media library'),targetField,mediaBoxControls,dropzone,result,pendingFile&&h(ImageCropDialog,{file:pendingFile,onCancel:()=>{setPendingFile(null);setOut(null)},onOriginal:()=>uploadImageFile(pendingFile),onCrop:uploadImageFile})),
        h('div',{className:'admin-card'},h('div',{className:'d-flex align-items-center justify-content-between gap-2 mb-2'},h('div',null,h('h4',{className:'h5 mb-1'},'Free image search'),h('div',{className:'small muted'},'Search free/open media, preview it, then import a local optimized copy with one click.')),h('span',{className:'plan-pill'},provider)),h('div',{className:'media-source-toolbar'},h('select',{className:'form-select',value:provider,onChange:e=>{setProvider(e.target.value);setResults([]);setPage(1)}},h('option',{value:'free'},'Free images · automatic'),h('option',{value:'openverse'},'Openverse · no key'),h('option',{value:'commons'},'Wikimedia Commons · no key'),h('option',{value:'pexels'},'Pexels · no key'),h('option',{value:'pixabay'},'Pixabay · no key'),h('option',{value:'unsplash'},'Unsplash · no key')),h('input',{className:'form-control',value:q,onChange:e=>setQ(e.target.value),onKeyDown:e=>{if(e.key==='Enter')search(1)},placeholder:'Search free images...'}),h('button',{className:'btn-cms',disabled:searching,onClick:()=>search(1)},searching?'Searching...':'Search')),h('div',{className:'topic-chips'},topics.map(t=>h('button',{type:'button',className:'topic-chip',key:t,onClick:()=>{setQ(t);search(1,t)}},t))),results.length>0&&h('div',{className:'free-media-grid'},results.map(item=>h('article',{className:'free-media-card',key:item.id||item.url},h('img',{src:item.thumb,alt:item.title||'',loading:'lazy',decoding:'async',fetchPriority:'low'}),h('div',{className:'meta'},h('div',{className:'title'},item.title||'Free image'),h('div',{className:'credit'},[item.creator,item.source,item.license].filter(Boolean).join(' · ')),item.landing&&h('a',{className:'small',href:item.landing,target:'_blank',rel:'noopener noreferrer'},'Source / licence'),h('button',{className:'btn-ghost',disabled:!!importing||target==='docs',onClick:()=>importFree(item)},importing===(item.id||item.url)?'Importing...':'Import image'))))),results.length>0&&h('div',{className:'d-flex justify-content-between mt-3'},h('button',{className:'btn-ghost',disabled:searching||page<=1,onClick:()=>search(page-1)},'Previous'),h('span',{className:'small muted align-self-center'},'Page ',page),h('button',{className:'btn-ghost',disabled:searching,onClick:()=>search(page+1)},'Next'))),
        h('div',{className:'admin-card'},h('h4',{className:'h6'},'AI image tools'),h('p',{className:'small muted'},'Prepare an image prompt and open a consumer AI image tool in a new tab. Download the generated image there, then drag it into Media above.'),h(Field,{label:'Image prompt'},h('textarea',{className:'form-control',rows:4,value:imagePrompt,onChange:e=>setImagePrompt(e.target.value)})),h('div',{className:'d-flex flex-wrap gap-2'},h('button',{className:'btn-ghost',type:'button',onClick:async()=>{try{await navigator.clipboard.writeText(imagePrompt);api.setNotice('Image prompt copied.')}catch(e){api.setNotice('Copy failed.')}}},'Copy prompt'),h('a',{className:'btn-ghost text-decoration-none',href:'https://chatgpt.com/',target:'_blank',rel:'noopener noreferrer'},'Open ChatGPT'),h('a',{className:'btn-ghost text-decoration-none',href:'https://copilot.microsoft.com/',target:'_blank',rel:'noopener noreferrer'},'Open Copilot'),h('a',{className:'btn-ghost text-decoration-none',href:'https://gemini.google.com/',target:'_blank',rel:'noopener noreferrer'},'Open Gemini'))),
        api.isSuper&&h('div',{className:'admin-card'},h('h4',{className:'h6'},'Media source connections'),h('p',{className:'small muted'},'Pexels, Pixabay and Unsplash use a no-key stock image proxy by default. Add your own API key only if you want direct provider access.'),h(Bool,{api,path:'settings.mediaSources.proxy.enabled',label:'Use no-key stock image proxy'}),h(Txt,{api,path:'settings.mediaSources.proxy.baseUrl',label:'Stock image proxy URL'}),h('details',{className:'mt-3'},h('summary',{className:'fw-semibold'},'Advanced: direct provider API keys'),h('p',{className:'small muted mt-2'},'Direct API keys are optional. Saved keys are masked in the browser.'),['pexels','pixabay','unsplash'].map(name=>h('div',{className:'border rounded-4 p-3 mb-2',key:name},h('div',{className:'small fw-bold text-uppercase mb-2'},name),h(Txt,{api,path:'settings.mediaSources.'+name+'.apiKey',label:(get(api.data,'settings.mediaSources.'+name+'.apiKeySet')?'Optional direct API key (saved - enter to replace)':'Optional direct API key'),type:'password'})))))
      );
    }

    function IconsTab({api}){
      const groups=[['','All libraries'],['bi','Bootstrap Icons'],['material-symbols','Material Symbols'],['mdi','Material Design'],['tabler','Tabler'],['lucide','Lucide'],['fluent','Fluent UI']]; const [prefix,setPrefix]=useState(''); const [q,setQ]=useState('home'); const [icons,setIcons]=useState([]); const [busy,setBusy]=useState(false);
      const targets=[]; [['features','Features'],['services','Services'],['process','Process'],['cards','Image cards'],['contentList','Content list']].forEach(([base,label])=>(api.data[base]?.items||[]).forEach((it,i)=>targets.push({path:`${base}.items.${i}.icon`,group:label,title:it.title||('Item '+(i+1))}))); (api.data.adminShowcase?.domainOptions||[]).forEach((it,i)=>targets.push({path:`adminShowcase.domainOptions.${i}.icon`,group:'Admin domain option',title:it.title||('Item '+(i+1))})); (api.data.adminShowcase?.items||[]).forEach((it,i)=>targets.push({path:`adminShowcase.items.${i}.icon`,group:'Admin overview',title:it.title||('Item '+(i+1))})); (api.data.adminShowcase?.superAdminItems||[]).forEach((it,i)=>targets.push({path:`adminShowcase.superAdminItems.${i}.icon`,group:'Super Admin',title:it.title||('Item '+(i+1))})); (api.data.footer?.social||[]).forEach((it,i)=>targets.push({path:`footer.social.${i}.icon`,group:'Footer social',title:it.label||('Item '+(i+1))}));
      let storedTarget=''; try{storedTarget=sessionStorage.getItem('lpcms_icon_target')||''}catch(e){} const [target,setTarget]=useState(()=>targets.some(x=>x.path===storedTarget)?storedTarget:''); const currentTarget=targets.find(x=>x.path===target)||null;
      async function search(){if(!q.trim())return;setBusy(true);try{const r=await fetch('?api=icon-search&q='+encodeURIComponent(q.trim())+'&prefix='+encodeURIComponent(prefix));const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Icon search failed.');setIcons(j.icons||[])}catch(e){api.setNotice(e.message)}finally{setBusy(false)}}
      function useIcon(ref){ if(!target){api.setNotice('Open an item and choose its icon first.');return} api.update(target,ref); if(/\.icon$/.test(target)){ const imagePath=target.replace(/\.icon$/,'.image'); if(get(api.data,imagePath)!==undefined) api.update(imagePath,''); } try{sessionStorage.removeItem('lpcms_icon_target')}catch(e){} api.setNotice('Icon selected and applied.'); }
      const targetPanel=currentTarget
        ?h('div',{className:'icon-target-context mb-3'},h('div',{className:'icon-target-context-glyph'},icon(get(api.data,target)||'bi bi-circle')),h('div',{className:'min-w-0'},h('div',{className:'small muted'},'Editing icon for'),h('strong',{className:'d-block text-truncate'},currentTarget.group,': ',currentTarget.title),h('div',{className:'small muted mt-1'},'Click any icon below to apply it directly.')))
        :h('div',{className:'icon-target-empty mb-3'},h('span',{className:'icon-target-empty-glyph'},icon('bi bi-hand-index-thumb')),h('div',null,h('strong',{className:'d-block'},'No item selected'),h('div',{className:'small muted mt-1'},'Open the item you want to edit and click its icon tile. The icon picker will apply directly to that item.')));
      const advanced=h('details',{className:'icon-target-advanced mb-3'},h('summary',{className:'small fw-semibold'},'Advanced: choose another target'),h('div',{className:'mt-2'},h('select',{className:'form-select',value:target,onChange:e=>setTarget(e.target.value)},h('option',{value:''},'Select target'),targets.map(x=>h('option',{value:x.path,key:x.path},x.group,': ',x.title)))));
      return h(React.Fragment,null,h('div',{className:'admin-card'},h('h4',{className:'h5'},'Live icon libraries'),h('p',{className:'small muted'},'Search Iconify live collections and assign any open-source icon to feature, service, or process items. Existing Bootstrap icon classes continue to work.'),targetPanel,advanced,h('div',{className:'row g-2'},h('div',{className:'col-4'},h('select',{className:'form-select',value:prefix,onChange:e=>setPrefix(e.target.value)},groups.map(([v,l])=>h('option',{value:v,key:v||'all'},l)))),h('div',{className:'col-8'},h('div',{className:'d-flex gap-2'},h('input',{className:'form-control',value:q,onChange:e=>setQ(e.target.value),onKeyDown:e=>{if(e.key==='Enter')search()},placeholder:'Search icons...'}),h('button',{className:'btn-cms',disabled:busy,onClick:search},busy?'...':'Search')))),icons.length>0?h('div',{className:'icon-picker-grid mt-3'},icons.map(ref=>h('button',{type:'button',disabled:!target,className:cls('icon-pick',target&&get(api.data,target)===ref&&'is-selected'),key:ref,title:target?'Use '+ref:'Open an item and choose its icon first.',onClick:()=>useIcon(ref)},h('span',{className:'icon-pick-glyph'},icon(ref)),h('small',null,ref.split(':').pop()),target&&get(api.data,target)===ref&&h('span',{className:'icon-selected-check'},icon('bi bi-check-lg'))))):h('div',{className:'small muted mt-3'},'Search to load icons.')));
    }

    function SeoTab({api}){ return h(React.Fragment,null, h(Txt,{api,path:'settings.seo.title',label:'Default meta title'}), h(Field,{label:'Default meta description'}, h('textarea',{className:'form-control',rows:3,value:get(api.data,'settings.seo.description')||'',onChange:e=>api.update('settings.seo.description',e.target.value)})), h(Txt,{api,path:'settings.seo.keywords',label:'Keywords'}), h(Txt,{api,path:'settings.seo.image',label:'OG image'}), h(Txt,{api,path:'settings.seo.canonical',label:'Canonical override'}), h(Txt,{api,path:'settings.seo.robots',label:'Robots'}), h('p',{className:'small muted'},'Blog and product SEO fields live inside each item in JSON. Pretty routes: /blog/post-slug, /product/product-slug, and /page/page-slug. Fallback query route: ?slug=slug.')) }
    function FormsTab({api}){
      return h(React.Fragment,null,
        h('div',{className:'admin-card'},
          h('h4',{className:'h5'},'Forms'),
          h(Txt,{api,path:'settings.forms.emailTo',label:'Send form submissions to email'}),
          h(Bool,{api,path:'settings.forms.storeSubmissions',label:'Store submissions in data.json'})
        ),
        h('div',{className:'admin-card'},
          h('h4',{className:'h6'},'Spam protection'),
          h(Bool,{api,path:'settings.hcaptcha.enabled',label:'Enable hCaptcha on forms'}),
          get(api.data,'settings.hcaptcha.enabled')&&h(React.Fragment,null,
            h(Txt,{api,path:'settings.hcaptcha.siteKey',label:'hCaptcha site key'}),
            h(Txt,{api,path:'settings.hcaptcha.secret',label:'hCaptcha secret'})
          ),
          h('p',{className:'small muted mb-0'},'hCaptcha is configured only here in Forms; it is no longer a general frontend Library toggle. The secret is never exposed publicly.')
        )
      );
    }
    function AiTab({api}){
      const cfg=get(api.data,'settings.ai')||{};
      const [prompt,setPrompt]=useState('Improve this content while keeping it concise and useful.');
      const [tone,setTone]=useState('professional');
      const [target,setTarget]=useState('hero.subtitle');
      const [output,setOutput]=useState('');
      const [busy,setBusy]=useState(false);
      const targets=[
        ['hero.title','Hero title'],['hero.subtitle','Hero subtitle'],['about.title','About title'],['about.text','About text'],['about.body','About body'],
        ['services.title','Services title'],['features.title','Features title'],['products.title','Products title'],['booking.title','Booking title'],['pricing.title','Pricing title'],
        ['contact.title','Contact title'],['contact.text','Contact text'],['settings.seo.title','SEO title'],['settings.seo.description','SEO description']
      ];
      const tier=cfg.accessTier||'free';

      async function generate(){
        setBusy(true);
        setOutput('');
        try{
          const context=String(get(api.data,target)||'');
          const r=await fetch('?api=ai-generate',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},
            body:JSON.stringify({prompt,context,tone,target})
          });
          const j=await cmsResponseJson(r);
          if(!r.ok||!j.ok)throw new Error(j.error||'AI generation failed.');
          setOutput(j.text||'');
        }catch(err){
          api.setNotice(err.message);
        }finally{
          setBusy(false);
        }
      }

      async function copyPrompt(){
        const text=`Task: ${prompt}\nTone: ${tone}\nTarget: ${target}\nCurrent content: ${String(get(api.data,target)||'')}`;
        try{
          await navigator.clipboard.writeText(text);
          api.setNotice('AI prompt copied.');
        }catch(err){
          api.setNotice('Copy failed.');
        }
      }

      function apply(){
        if(!output.trim())return;
        api.update(target,output.trim());
        api.setNotice('AI content applied. Save JSON when ready.');
      }

      const intro=h('div',{className:'admin-card'},
        h('div',{className:'d-flex align-items-start justify-content-between gap-2'},
          h('div',null,
            h('h4',{className:'h5 mb-1'},'AI writing tools'),
            h('p',{className:'small muted mb-0'},'Write new copy, improve or shorten text, translate it, or prepare SEO-friendly content. The sparkle button also appears beside inline text when connected AI is enabled.')
          ),
          h('span',{className:'plan-pill'},cfg.enabled?(tier==='pro'?'Connected AI':'AI enabled'):'AI off')
        ),
        !api.isSuper?h('div',{className:'alert alert-light border mt-3 mb-0'},'Provider, API key and access mode are managed by the super administrator.'):null
      );

      let providerCard=null;
      if(api.isSuper){
        const connectedFields=tier==='pro'?h(React.Fragment,null,
          h(Field,{label:'Provider'},h('select',{className:'form-select',value:cfg.provider||'openai',onChange:e=>api.update('settings.ai.provider',e.target.value)},
            h('option',{value:'openai'},'OpenAI API'),
            h('option',{value:'kimi'},'Kimi / Moonshot API'),
            h('option',{value:'gemini'},'Google Gemini API'),
            h('option',{value:'mistral'},'Mistral API'),
            h('option',{value:'custom'},'Custom OpenAI-compatible API')
          )),
          h(Txt,{api,path:'settings.ai.model',label:'Model'}),
          (cfg.provider||'openai')==='custom'?h(Txt,{api,path:'settings.ai.endpoint',label:'Endpoint'}):null,
          h(Txt,{api,path:'settings.ai.apiKey',label:cfg.apiKeySet?'API key (saved - enter to replace)':'API key',type:'password'}),
          h(Field,{label:'System instruction'},h('textarea',{className:'form-control',rows:4,value:cfg.systemPrompt||'',onChange:e=>api.update('settings.ai.systemPrompt',e.target.value)}))
        ):null;
        providerCard=h('div',{className:'admin-card technical-box'},
          h('h4',{className:'h6'},'Super admin · AI provider'),
          h(Bool,{api,path:'settings.ai.enabled',label:'Enable AI tools'}),
          h(Field,{label:'Access mode'},h('select',{className:'form-select',value:tier,onChange:e=>api.update('settings.ai.accessTier',e.target.value)},
            h('option',{value:'free'},'Free · copy prompt'),
            h('option',{value:'pro'},'Pro · connected API')
          )),
          connectedFields
        );
      }

      const generator=h('div',{className:'admin-card'},
        h('h4',{className:'h6'},'Content generator'),
        h(Field,{label:'Write into'},h('select',{className:'form-select',value:target,onChange:e=>setTarget(e.target.value)},...targets.map(([v,l])=>h('option',{key:v,value:v},l)))),
        h(Field,{label:'Tone'},h('select',{className:'form-select',value:tone,onChange:e=>setTone(e.target.value)},...['professional','friendly','concise','premium','playful','technical','sales'].map(v=>h('option',{key:v,value:v},v)))),
        h(Field,{label:'Instruction'},h('textarea',{className:'form-control',rows:4,value:prompt,onChange:e=>setPrompt(e.target.value)})),
        h('div',{className:'d-flex gap-2 flex-wrap mb-3'},
          h('button',{className:'btn-cms',disabled:busy||!cfg.enabled||tier!=='pro',onClick:generate},busy?'Generating...':'Generate with connected AI'),
          h('button',{className:'btn-ghost',onClick:copyPrompt},'Copy prompt')
        ),
        h(Field,{label:'Generated content'},h('textarea',{className:'form-control ai-output',value:output,onChange:e=>setOutput(e.target.value)})),
        h('button',{className:'btn-ghost',disabled:!output.trim(),onClick:apply},'Apply to selected content')
      );

      return h(React.Fragment,null,intro,providerCard,generator);
    }

    function PlanTab({api}){
      const p=get(api.data,'settings.sitePlan')||{};
      const selected=p.selected||'premium';
      const plans=[
        {id:'premium',title:'Premium',type:'Business website',gbp:p.premiumGbp||'3.50',eur:p.premiumEur||'4.00'},
        {id:'premium-pro',title:'Premium Pro',type:'E-commerce website',gbp:p.premiumProGbp||'6.50',eur:p.premiumProEur||'7.00'}
      ];
      const fallback=(p.subdomain||'yourbusiness')+'.'+(p.platformDomain||'dev4.click');

      function loadDemo(id){
        if(!confirm(api.t('Load the ')+(id==='premium'?api.t('Premium business'):api.t('Premium Pro e-commerce'))+api.t(' demo settings? Existing content stays, but commerce mode may change.')))return;
        api.update('settings.sitePlan.selected',id);
        if(id==='premium'){
          api.update('products.enabled',false);
          api.update('products.shopEnabled',false);
          api.update('products.cartEnabled',false);
          api.update('products.basketEnabled',false);
        }else{
          api.update('products.enabled',true);
          api.update('products.shopEnabled',true);
          api.update('products.cartEnabled',true);
          api.update('products.basketEnabled',true);
          api.update('products.defaultPurchaseMode','checkout');
          if(!(api.data.products?.items||[]).length)api.update('products.items',LPCMS_DEMO_PRODUCTS.slice(0,8));
        }
        api.setNotice('Demo mode loaded. Review it, then Save JSON.');
      }

      const planCards=plans.map(x=>h('div',{className:cls('plan-admin-card',selected===x.id&&'active'),key:x.id},
        h('span',{className:'plan-badge'},x.type),
        h('h5',{className:'mt-2'},x.title),
        h('div',{className:'h4'},'£'+x.gbp,' / €'+x.eur,h('small',{className:'muted'},' / month')),
        h('div',{className:'d-flex gap-2 flex-wrap'},
          h('button',{className:selected===x.id?'btn-cms':'btn-ghost',onClick:()=>api.update('settings.sitePlan.selected',x.id)},selected===x.id?'Selected':'Select plan'),
          h('button',{className:'btn-ghost',onClick:()=>loadDemo(x.id)},'Load demo')
        )
      ));

      return h(React.Fragment,null,
        h('div',{className:'admin-card'},
          h('h4',{className:'h5'},'Plans & demo mode'),
          h('p',{className:'small muted'},'Only super admin can choose the client plan or load a demo. Premium is the business-site mode; Premium Pro enables the e-commerce demo.'),
          h('div',{className:'plan-admin-grid'},...planCards)
        ),
        h('div',{className:'admin-card'},
          h('h4',{className:'h6'},'Domain'),
          h(Txt,{api,path:'settings.sitePlan.subdomain',label:'dev4.click subdomain'}),
          h(Txt,{api,path:'settings.sitePlan.customDomain',label:'Custom domain (optional)'}),
          h(Txt,{api,path:'settings.sitePlan.platformDomain',label:'Platform domain'}),
          h('div',{className:'alert alert-light border mb-0'},
            'Client address: ',h('strong',null,p.customDomain||fallback),h('br'),
            h('span',{className:'small muted'},'If the client does not buy a domain, their chosen subdomain is used on dev4.click.')
          )
        )
      );
    }

    function BookingTab({api}){
      const d=api.data.booking||{};
      const arr=d.services||[];
      const days=['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];

      function upd(i,k,v){
        if(k==='title'){api.updateLocalized(`booking.services.${i}.title`,v);return;}
        const next=[...arr];
        next[i]={...next[i],[k]:v};
        api.update('booking.services',next);
      }
      function add(){
        api.update('booking.services',[...arr,{id:'service-'+Date.now(),title:'New service',durationMinutes:60,price:0,enabled:true}]);
      }

      const serviceRows=arr.map((it,i)=>h('div',{className:'booking-admin-row',key:it.id||i},
        h('div',{className:'row g-2'},
          h('div',{className:'col-md-5'},h('input',{className:'form-control',value:api.localizedValue(`booking.services.${i}.title`)??'',onChange:e=>upd(i,'title',e.target.value),placeholder:'Service'})),
          h('div',{className:'col-md-3'},h('input',{className:'form-control',type:'number',value:it.durationMinutes||60,onChange:e=>upd(i,'durationMinutes',Number(e.target.value)),placeholder:'Minutes'})),
          h('div',{className:'col-md-3'},h('input',{className:'form-control',type:'number',step:'0.01',value:it.price||0,onChange:e=>upd(i,'price',Number(e.target.value)),placeholder:'Price'})),
          h('div',{className:'col-md-1'},h('button',{className:'mini-remove',onClick:()=>api.update('booking.services',arr.filter((_,x)=>x!==i))},icon('bi bi-x-lg')))
        )
      ));

      const dayRows=days.map(day=>h(Field,{key:day,label:day.charAt(0).toUpperCase()+day.slice(1)+' times (comma separated)'},
        h('input',{className:'form-control',value:(d.weeklySlots?.[day]||[]).join(', '),onChange:e=>api.update('booking.weeklySlots.'+day,e.target.value.split(',').map(x=>x.trim()).filter(Boolean))})
      ));

      const bookingRows=(d.bookings||[]).slice(-20).reverse().map(b=>h('div',{className:'booking-admin-row',key:b.id||b.code},
        h('strong',null,b.date,' ',b.time,' · ',b.serviceName||b.serviceTitle||b.serviceId),
        h('div',{className:'muted'},b.name,' · ',b.email,' · ',b.code)
      ));

      return h(React.Fragment,null,
        h('div',{className:'admin-card'},
          h('h4',{className:'h5'},'Booking → real-time confirmation'),
          h(Bool,{api,path:'booking.enabled',label:'Enable booking section'}),
          h(Txt,{api,path:'booking.title',label:'Title'}),
          h(Txt,{api,path:'booking.subtitle',label:'Subtitle'}),
          h(Field,{label:'Confirmation text'},h('textarea',{className:'form-control',rows:2,value:api.localizedValue('booking.confirmationText')??'',onChange:e=>api.updateLocalized('booking.confirmationText',e.target.value)}))
        ),
        h('div',{className:'admin-card'},
          h('div',{className:'d-flex justify-content-between mb-2'},h('h5',{className:'mb-0'},'Services'),h('button',{className:'btn-ghost',onClick:add},'Add service')),
          ...serviceRows
        ),
        h('div',{className:'admin-card'},h('h5',null,'Weekly availability'),...dayRows),
        api.isSuper?h('div',{className:'admin-card technical-box'},
          h('h5',null,'Super admin · booking integrations'),
          h(Txt,{api,path:'booking.technical.notifyEmail',label:'Notification email'}),
          h(Txt,{api,path:'booking.technical.webhookUrl',label:'HTTPS webhook URL'})
        ):null,
        Array.isArray(d.bookings)?h('div',{className:'admin-card'},
          h('h5',null,'Recent bookings'),
          d.bookings.length?h('div',{className:'small'},...bookingRows):h('p',{className:'small muted mb-0'},'No bookings yet.')
        ):null
      );
    }

    function ChatbotTab({api}){
      const d=api.data.chatbot||{};
      const mode=d.mode||'chatbot';
      const questions=Array.isArray(api.localizedValue('chatbot.suggestedQuestions'))?api.localizedValue('chatbot.suggestedQuestions'):[];
      function setQuestions(text){api.updateLocalized('chatbot.suggestedQuestions',text.split('\n').map(x=>x.trim()).filter(Boolean).slice(0,8));}
      const main=h('div',{className:'admin-card'},
        h('h4',{className:'h5'},'Website chat button'),
        h('p',{className:'small muted'},'Choose one visitor chat option. The launcher stays in a bottom corner and does not overlap the cart or scroll-up controls.'),
        h(Bool,{api,path:'chatbot.enabled',label:'Enable website chat button'}),
        h(Field,{label:'Chat type'},h('select',{className:'form-select',value:mode,onChange:e=>api.update('chatbot.mode',e.target.value)},h('option',{value:'chatbot'},'AI chatbot'),h('option',{value:'whatsapp'},'WhatsApp'))),
        h(Field,{label:'Button position'},h('select',{className:'form-select',value:d.position||'left',onChange:e=>api.update('chatbot.position',e.target.value)},h('option',{value:'left'},'Bottom left'),h('option',{value:'right'},'Bottom right'))),
        mode==='chatbot'&&h(React.Fragment,null,
          h(Txt,{api,path:'chatbot.buttonText',label:'Chatbot button label'}),
          h(Txt,{api,path:'chatbot.title',label:'Chat title'}),
          h(Field,{label:'Welcome message'},h('textarea',{className:'form-control',rows:2,value:api.localizedValue('chatbot.welcome')??api.localizedValue('chatbot.welcomeMessage')??'',onChange:e=>api.updateLocalized('chatbot.welcome',e.target.value)})),
          h(Txt,{api,path:'chatbot.placeholder',label:'Input placeholder'}),
          h(Field,{label:'Suggested visitor questions — one per line'},h('textarea',{className:'form-control',rows:5,value:questions.join('\n'),onChange:e=>setQuestions(e.target.value)})),
          h(Field,{label:'Fallback reply'},h('textarea',{className:'form-control',rows:3,value:api.localizedValue('chatbot.fallback')??'',onChange:e=>api.updateLocalized('chatbot.fallback',e.target.value)}))
        ),
        mode==='whatsapp'&&h(React.Fragment,null,
          h(Txt,{api,path:'chatbot.whatsapp.number',label:'WhatsApp number with country code'}),
          h(Txt,{api,path:'chatbot.whatsapp.buttonText',label:'Button label'}),
          h(Field,{label:'Pre-filled WhatsApp message'},h('textarea',{className:'form-control',rows:3,value:api.localizedValue('chatbot.whatsapp.message')??'',onChange:e=>api.updateLocalized('chatbot.whatsapp.message',e.target.value)}))
        )
      );
      const technical=api.isSuper&&mode==='chatbot'?h('div',{className:'admin-card technical-box'},
        h('h5',null,'Super admin · chatbot AI'),
        h(Bool,{api,path:'chatbot.technical.aiEnabled',label:'Use connected AI when available'}),
        h(Txt,{api,path:'chatbot.technical.maxContextChars',label:'Maximum site context characters',type:'number'}),
        h(Field,{label:'Chatbot system instruction'},h('textarea',{className:'form-control',rows:5,value:get(api.data,'chatbot.technical.systemPrompt')||'',onChange:e=>api.update('chatbot.technical.systemPrompt',e.target.value)}))
      ):null;
      return h(React.Fragment,null,main,technical);
    }

    function DemoRequestsTab({api}){
      const cfg=get(api.data,'settings.demoIntakeAdmin')||{};
      const [requests,setRequests]=useState(Array.isArray(cfg.requests)?cfg.requests:[]);
      const [busy,setBusy]=useState('');
      useEffect(()=>{fetch('?api=demo-requests',{headers:{'X-CMS-CSRF':api.csrf}}).then(r=>cmsResponseJson(r)).then(j=>{if(j.ok)setRequests(j.requests||[])}).catch(()=>{});},[]);
      async function act(action,id){
        if((action==='approve'||action==='delete'||action==='reject')&&!confirm(api.t(action==='approve'?'Approve this request and load its generated draft into the current website?':action==='delete'?'Delete this demo request?':'Mark this request as rejected?')))return;
        setBusy(action+id);
        try{const r=await fetch('?api=demo-requests',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},body:JSON.stringify({action,id})});const j=await cmsResponseJson(r);if(!r.ok||!j.ok)throw new Error(j.error||'Action failed.');setRequests(j.requests||[]);if(j.data)api.setData(j.data);api.setNotice(j.message||'Done.');}
        catch(err){api.setNotice(err.message)}finally{setBusy('')}
      }
      const cards=requests.slice().reverse().map(r=>h('div',{className:'admin-card',key:r.id},
        h('div',{className:'d-flex justify-content-between align-items-start gap-3 mb-2'},h('div',null,h('strong',null,r.company||r.contact?.company||'Demo request'),h('div',{className:'small muted'},r.id,' · ',r.createdAt||'')),h('span',{className:'plan-pill'},r.status||'pending')),
        r.logo&&h('img',{src:mediaUrl(r.logo),alt:'Client logo',loading:'lazy',decoding:'async',style:{maxWidth:160,maxHeight:70,objectFit:'contain'},className:'mb-3'}),
        h('div',{className:'row g-2 small mb-3'},
          h('div',{className:'col-md-6'},h('strong',null,'Client: '),r.name,' · ',r.email,r.phone?' · '+r.phone:''),
          h('div',{className:'col-md-6'},h('strong',null,'Plan: '),r.planTitle||r.plan,' · ',r.currency||'GBP'),
          h('div',{className:'col-md-6'},h('strong',null,'Chat: '),r.chatChoice||'none',r.whatsapp?' · '+r.whatsapp:''),
          h('div',{className:'col-md-6'},h('strong',null,'Booking: '),r.bookingNeeded?'Yes':'No'),
          h('div',{className:'col-12'},h('strong',null,'About: '),r.about||''),
          h('div',{className:'col-12'},h('strong',null,'Services: '),String(r.services||'').replace(/\n/g,' · ')),
          r.products&&h('div',{className:'col-12'},h('strong',null,'Products: '),String(r.products).replace(/\n/g,' · ')),
          r.websiteUrl&&h('div',{className:'col-12'},h('strong',null,'Generated website: '),r.websiteUrl),
          r.invoiceNumber&&h('div',{className:'col-12'},h('strong',null,'Billing: '),r.invoiceNumber,r.billSentAt?' · sent '+r.billSentAt:'')
        ),
        h('div',{className:'d-flex flex-wrap gap-2'},
          r.status!=='approved'&&h('button',{type:'button',className:'btn-cms',disabled:!!busy,onClick:()=>act('approve',r.id)},busy==='approve'+r.id?'Building…':'Approve & build site'),
          r.status==='approved'&&h('button',{type:'button',className:'btn-cms',disabled:!!busy,onClick:()=>act('bill',r.id)},busy==='bill'+r.id?'Sending…':(r.billSentAt?'Send bill again':'Send billing email')),
          r.status!=='rejected'&&r.status!=='approved'&&h('button',{type:'button',className:'btn-ghost',disabled:!!busy,onClick:()=>act('reject',r.id)},'Reject'),
          h('button',{type:'button',className:'btn-ghost',disabled:!!busy,onClick:()=>act('delete',r.id)},'Delete')
        )
      ));
      return h(React.Fragment,null,
        h('div',{className:'admin-card'},h('h4',{className:'h5'},'Demo intake & billing'),h(Bool,{api,path:'demoIntake.enabled',label:'Enable public multi-step demo form'}),h(Txt,{api,path:'settings.demoIntakeAdmin.notifyEmail',label:'New request notification email'}),h(Txt,{api,path:'settings.demoIntakeAdmin.billingSubject',label:'Billing email subject'}),h(Field,{label:'Billing email introduction'},h('textarea',{className:'form-control',rows:3,value:cfg.billingMessage||'',onChange:e=>api.update('settings.demoIntakeAdmin.billingMessage',e.target.value)})),h(Field,{label:'Payment instructions / payment link'},h('textarea',{className:'form-control',rows:4,value:cfg.paymentInstructions||'',onChange:e=>api.update('settings.demoIntakeAdmin.paymentInstructions',e.target.value)})),h('div',{className:'row g-2'},h('div',{className:'col-md-6'},h(Txt,{api,path:'settings.demoIntakeAdmin.invoicePrefix',label:'Invoice prefix'})),h('div',{className:'col-md-6'},h(Txt,{api,path:'settings.demoIntakeAdmin.dueDays',label:'Payment due days',type:'number'}))),h('p',{className:'small muted mb-0'},'Save JSON after changing these settings. Requests themselves are private and visible only to Super Admin.')),
        h('div',{className:'admin-card'},h('div',{className:'d-flex justify-content-between align-items-center'},h('h4',{className:'h5 mb-0'},'Client demo requests'),h('span',{className:'plan-pill'},requests.length,' total')),requests.length?null:h('p',{className:'small muted mt-3 mb-0'},'No demo requests yet.')),
        ...cards
      );
    }

    function AbandonedCartTab({api}){
      const cfg=get(api.data,'settings.abandonedCart')||{};
      const [tools,setTools]=useState(null);
      const [busy,setBusy]=useState(false);

      useEffect(()=>{
        fetch('?api=abandoned-cart-tools',{headers:{'X-CMS-CSRF':api.csrf}})
          .then(r=>cmsResponseJson(r))
          .then(j=>{if(j.ok)setTools(j);})
          .catch(()=>{});
      },[]);

      async function run(action){
        setBusy(true);
        try{
          const r=await fetch('?api=abandoned-cart-tools',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},
            body:JSON.stringify({action})
          });
          const j=await cmsResponseJson(r);
          if(!r.ok||!j.ok)throw new Error(j.error||'Action failed.');
          setTools(prev=>({...prev,...j,carts:j.carts||prev?.carts||[]}));
          api.setNotice(action==='process'?`Reminder run complete: ${j.result?.sent||0} email(s) sent.`:(j.message||'Done'));
        }catch(err){
          api.setNotice(err.message);
        }finally{
          setBusy(false);
        }
      }

      function gen(){
        const a=new Uint8Array(24);
        crypto.getRandomValues(a);
        api.update('settings.abandonedCart.cronKey',Array.from(a,b=>b.toString(16).padStart(2,'0')).join(''));
      }

      const carts=(tools?.carts||[]).slice(0,30).map((c,i)=>h('div',{className:'booking-admin-row',key:c.token||i},
        h('strong',null,c.email||'Unknown'),
        h('div',{className:'small muted'},c.updatedAt||c.createdAt||'',c.completedAt?' · completed':'',c.firstSentAt?' · reminder 1 sent':'',c.secondSentAt?' · reminder 2 sent':'')
      ));

      return h(React.Fragment,null,
        h('div',{className:'admin-card'},
          h('h4',{className:'h5'},'Abandoned cart recovery'),
          h(Bool,{api,path:'settings.abandonedCart.enabled',label:'Enable saved-cart reminder emails'}),
          h('div',{className:'row g-2'},
            h('div',{className:'col-6'},h(Txt,{api,path:'settings.abandonedCart.firstDelayMinutes',label:'First reminder (minutes)',type:'number'})),
            h('div',{className:'col-6'},h(Txt,{api,path:'settings.abandonedCart.secondDelayMinutes',label:'Second reminder (minutes)',type:'number'}))
          ),
          h(Txt,{api,path:'settings.abandonedCart.firstSubject',label:'First email subject'}),
          h(Field,{label:'First email message'},h('textarea',{className:'form-control',rows:3,value:cfg.firstMessage||'',onChange:e=>api.update('settings.abandonedCart.firstMessage',e.target.value)})),
          h(Txt,{api,path:'settings.abandonedCart.secondSubject',label:'Second email subject'}),
          h(Field,{label:'Second email message'},h('textarea',{className:'form-control',rows:3,value:cfg.secondMessage||'',onChange:e=>api.update('settings.abandonedCart.secondMessage',e.target.value)}))
        ),
        h('div',{className:'admin-card technical-box'},
          h('h5',null,'Super admin · scheduler'),
          h('p',{className:'small muted'},'For automatic reminders, call the scheduler URL from server cron. LPCMS also lets you run processing manually.'),
          h(Txt,{api,path:'settings.abandonedCart.cronKey',label:cfg.cronKeySet?'Scheduler key (saved - enter to replace)':'Scheduler key'}),
          h('div',{className:'d-flex gap-2 flex-wrap'},
            h('button',{className:'btn-ghost',onClick:gen},'Generate key'),
            h('button',{className:'btn-cms',disabled:busy,onClick:()=>run('process')},busy?'Processing...':'Process reminders now'),
            h('button',{className:'btn-ghost',disabled:busy,onClick:()=>run('clear-completed')},'Clear completed')
          ),
          h('div',{className:'small muted mt-2'},'Endpoint: ?api=abandoned-cart-cron&key=YOUR_SCHEDULER_KEY')
        ),
        h('div',{className:'admin-card'},
          h('h5',null,'Captured carts'),
          h('p',{className:'small muted'},`${tools?.count??(tools?.carts||[]).length} cart(s) recorded.`),
          ...carts
        )
      );
    }

    function MailerTab({api}){
      const [diag,setDiag]=useState(null); const [logs,setLogs]=useState([]); const [to,setTo]=useState(api.email||''); const [busy,setBusy]=useState('');
      async function load(){try{const r=await fetch('?api=mailer-tools');const j=await cmsResponseJson(r);if(j.ok){setDiag(j.status);setLogs(j.logs||[])}}catch(e){api.setNotice(e.message)}}
      useEffect(()=>{if(api.isSuper)load()},[api.isSuper]);
      async function tool(action){setBusy(action);try{const r=await fetch('?api=mailer-tools',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},body:JSON.stringify({action,to})});const j=await cmsResponseJson(r);if(j.logs)setLogs(j.logs);api.setNotice(j.message||j.error||(j.ok?'Mailer test completed.':'Mailer test failed.'));await load();}catch(e){api.setNotice(e.message)}finally{setBusy('')}}
      const mode=get(api.data,'settings.mailer.mode')||'mail';
      return h(React.Fragment,null,
        h('div',{className:'admin-card'},h('h4',{className:'h5'},'Mailer'),h('p',{className:'small muted'},'Choose PHP mail() or direct SMTP. SMTP credentials stay server-side and the saved password is masked after save.'),h(Field,{label:'Delivery method'},h('select',{className:'form-select',value:mode,onChange:e=>api.update('settings.mailer.mode',e.target.value)},h('option',{value:'mail'},'PHP mail()'),h('option',{value:'smtp'},'SMTP'))),h(Txt,{api,path:'settings.mailer.fromName',label:'From name'}),h(Txt,{api,path:'settings.mailer.fromEmail',label:'From email'}),h(Txt,{api,path:'settings.mailer.replyTo',label:'Reply-To email'})),
        mode==='smtp'&&h('div',{className:'admin-card'},h('h4',{className:'h6'},'SMTP settings'),h(Txt,{api,path:'settings.mailer.smtp.host',label:'SMTP host'}),h('div',{className:'row g-2'},h('div',{className:'col-5'},h(Txt,{api,path:'settings.mailer.smtp.port',label:'Port',type:'number'})),h('div',{className:'col-7'},h(Field,{label:'Encryption'},h('select',{className:'form-select',value:get(api.data,'settings.mailer.smtp.encryption')||'tls',onChange:e=>api.update('settings.mailer.smtp.encryption',e.target.value)},h('option',{value:'tls'},'STARTTLS'),h('option',{value:'ssl'},'Implicit TLS / SSL'),h('option',{value:'none'},'None'))))),h(Txt,{api,path:'settings.mailer.smtp.username',label:'Username'}),h(Txt,{api,path:'settings.mailer.smtp.password',label:(get(api.data,'settings.mailer.smtp.passwordSet')?'Password (saved - enter to replace)':'Password'),type:'password'}),h(Txt,{api,path:'settings.mailer.smtp.timeout',label:'Timeout seconds',type:'number'}),h(Bool,{api,path:'settings.mailer.smtp.verifyPeer',label:'Verify TLS certificate'})),
        h('div',{className:'admin-card'},h('h4',{className:'h6'},'Tools & diagnostics'),diag&&h('div',{className:'small muted mb-3'},`Mode: ${diag.mode||mode} · mail(): ${diag.mailFunction?'yes':'no'} · OpenSSL: ${diag.openssl?'yes':'no'} · sockets: ${diag.sockets?'yes':'no'} · log writable: ${diag.logWritable?'yes':'no'}`),h('div',{className:'d-flex gap-2 flex-wrap mb-3'},h('button',{className:'btn-ghost',disabled:!!busy,onClick:()=>tool('connection')},busy==='connection'?'Testing...':'Test connection'),h('button',{className:'btn-ghost',disabled:!!busy,onClick:load},'Refresh logs'),h('button',{className:'btn-ghost',disabled:!!busy,onClick:()=>tool('clear-logs')},'Clear logs')),h(Field,{label:'Send test email to'},h('div',{className:'d-flex gap-2'},h('input',{className:'form-control',type:'email',value:to,onChange:e=>setTo(e.target.value),placeholder:'you@example.com'}),h('button',{className:'btn-cms flex-shrink-0',disabled:!!busy||!to,onClick:()=>tool('send-test')},busy==='send-test'?'Sending...':'Send test')))),
        h('div',{className:'admin-card'},h('h4',{className:'h6'},'Mailer logs'),logs.length?h('div',{className:'mailer-log'},logs.map((row,i)=>h('div',{key:i},`${row.time||''}  ${row.event||''}${row.mode?'  '+row.mode:''}${row.to?'  '+row.to:''}${row.message?'  '+row.message:''}`))):h('p',{className:'small muted mb-0'},'No mailer log entries yet.'))
      );
    }

    function HeadlessTab({api}){
      const [test,setTest]=useState('{ site { settings { siteName defaultLang } products { title items } } }'),[out,setOut]=useState('');
      const origins=get(api.data,'settings.headless.corsOrigins')||[];
      async function run(){setOut('Loading…');try{const r=await fetch('?api=graphql',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({query:test})});const j=await cmsResponseJson(r);setOut(JSON.stringify(j,null,2));}catch(e){setOut(e.message)}}
      return h(React.Fragment,null,
        h('div',{className:'admin-card'},h('h4',{className:'h5'},'Headless API'),h(Bool,{api,path:'settings.headless.enabled',label:'Enable headless APIs'}),h(Bool,{api,path:'settings.headless.restEnabled',label:'Enable REST-style content API'}),h(Bool,{api,path:'settings.headless.graphqlEnabled',label:'Enable GraphQL read API'}),h(Txt,{api,path:'settings.headless.cacheSeconds',label:'Public API cache seconds',type:'number'}),h(Field,{label:'Allowed CORS origins (one per line; use * only for fully public use)'},h('textarea',{className:'form-control',rows:4,value:origins.join('\n'),onChange:e=>api.update('settings.headless.corsOrigins',e.target.value.split(/\r?\n/).map(x=>x.trim()).filter(Boolean))})),h('p',{className:'small muted mb-0'},'All headless responses use cms_public_data(), so users, form submissions and hCaptcha secrets stay private. APIs are read-only.')),
        h('div',{className:'admin-card'},h('h4',{className:'h6'},'REST examples'),h('div',{className:'headless-code'},'?api=headless&resource=site\n?api=headless&resource=products\n?api=headless&resource=products&slug=starter-website-package\n?api=headless&resource=settings.seo')),
        h('div',{className:'admin-card'},h('h4',{className:'h6'},'GraphQL playground'),h('p',{className:'small muted'},'Endpoint: ?api=graphql · Schema: ?api=graphql-schema'),h('textarea',{className:'form-control mb-2',rows:7,value:test,onChange:e=>setTest(e.target.value),spellCheck:false}),h('button',{className:'btn-cms mb-2',onClick:run},'Run query'),out&&h('pre',{className:'headless-code mb-0'},out))
      );
    }

    function AppTab({api}){ return h(React.Fragment,null, h('div',{className:'admin-card'}, h(Bool,{api,path:'settings.app.enabled',label:'Enable mobile app install option'}), h(Bool,{api,path:'settings.app.footerBadgesEnabled',label:'Show Android / Apple install badges in footer'}), h(Txt,{api,path:'settings.app.name',label:'App name'}), h(Txt,{api,path:'settings.app.shortName',label:'Short name'}), h(Txt,{api,path:'settings.app.icon',label:'Fallback app icon URL'}), h(Txt,{api,path:'settings.app.icon192',label:'192×192 PNG icon URL'}), h(Txt,{api,path:'settings.app.icon512',label:'512×512 PNG icon URL'}), h(Txt,{api,path:'settings.app.appleIcon',label:'Apple 180×180 PNG icon URL'}), h(Txt,{api,path:'settings.app.themeColor',label:'Theme color'}), h(Txt,{api,path:'settings.app.backgroundColor',label:'Background color'}), h(Txt,{api,path:'settings.app.androidInstallUrl',label:'Android install URL'}), h(Txt,{api,path:'settings.app.appleInstallUrl',label:'Apple install URL'}), h(Field,{label:'Offline message'}, h('textarea',{className:'form-control',rows:2,value:api.localizedValue('settings.app.offlineMessage')||'',onChange:e=>api.updateLocalized('settings.app.offlineMessage',e.target.value)})), h('p',{className:'small muted'},'Public guides: /?install=android and /?install=apple. PWA files are generated by index.php as /manifest.webmanifest and /service-worker.js.'))); }

    function CacheTab({api}){ const [stats,setStats]=useState(null); const [msg,setMsg]=useState(''); async function run(action){ setMsg('Working...'); const r=await fetch('?api=maintenance',{method:'POST',headers:{'Content-Type':'application/json','X-CMS-CSRF':api.csrf},body:JSON.stringify({action})}); const j=await cmsResponseJson(r); setStats(j); setMsg(j.message||j.error||'Done'); } useEffect(()=>{ if(api.isSuper) run('stats'); },[]); return h(React.Fragment,null, h('div',{className:'admin-card'}, h(Bool,{api,path:'settings.cache.enabled',label:'Enable anonymous page cache'}), h(Txt,{api,path:'settings.cache.htmlTtl',label:'HTML cache TTL seconds',type:'number'}), h(Bool,{api,path:'settings.cache.autoClearOnSave',label:'Clear cache after Save JSON'}), h(Txt,{api,path:'settings.cache.maxAgeDays',label:'Old cache cleanup days',type:'number'}), h('div',{className:'d-flex flex-wrap gap-2 mt-2'}, h('button',{className:'btn-cms',onClick:()=>run('stats')},'Refresh stats'), h('button',{className:'btn-ghost',onClick:()=>run('clear-cache')},'Clear cache'), h('button',{className:'btn-ghost',onClick:()=>run('cleanup-old-cache')},'Clean old cache')), msg&&h('p',{className:'small mt-3'},msg)), h('div',{className:'admin-card'}, h('h4',{className:'h6'},'Media optimization settings'), h(Field,{label:'Default image scale box'},h('select',{className:'form-select',value:get(api.data,'settings.media.boxPreset')||'large',onChange:e=>{const presets={large:[1920,1920],hero:[1600,900],content:[1200,900],card:[900,675],square:[800,800],logo:[800,400],custom:[get(api.data,'settings.media.boxWidth')||1920,get(api.data,'settings.media.boxHeight')||1920]};const v=e.target.value;api.update('settings.media.boxPreset',v);if(v!=='custom'){api.update('settings.media.boxWidth',presets[v][0]);api.update('settings.media.boxHeight',presets[v][1]);}}},h('option',{value:'large'},'Large · 1920×1920'),h('option',{value:'hero'},'Hero · 1600×900'),h('option',{value:'content'},'Content · 1200×900'),h('option',{value:'card'},'Card · 900×675'),h('option',{value:'square'},'Square · 800×800'),h('option',{value:'logo'},'Logo · 800×400'),h('option',{value:'custom'},'Custom box'))), h('div',{className:'row g-2'},h('div',{className:'col-6'},h(Txt,{api,path:'settings.media.boxWidth',label:'Box width',type:'number'})),h('div',{className:'col-6'},h(Txt,{api,path:'settings.media.boxHeight',label:'Box height',type:'number'}))), h(Txt,{api,path:'settings.media.avifQuality',label:'AVIF quality 35-95',type:'number'}), h(Bool,{api,path:'settings.media.browserAvif',label:'Optimize to AVIF in browser when supported'}), h('p',{className:'small muted'},'All image uploads and free-image imports are scaled inside the selected width × height box without stretching, metadata is stripped, and the saved website asset must be AVIF. If the server cannot create AVIF, the upload stops with an error instead of keeping a heavier original file.')), stats&&h('div',{className:'admin-card'}, h('h4',{className:'h6'},'Stats'), h('pre',{className:'small mb-0',style:{whiteSpace:'pre-wrap'}}, JSON.stringify(stats.cache||stats.media?stats:null,null,2)))); }

    function UsersTab({api}){ const users=get(api.data,'settings.users.items')||[]; function upd(i,k,v){ const next=[...users]; next[i]={...next[i],[k]:v}; api.update('settings.users.items',next); } function add(){ api.update('settings.users.items',[...users,{email:'editor@example.com',name:'Editor',role:'editor',active:true}]); } return h(React.Fragment,null, h('div',{className:'alert alert-info'},'Super admin login: open /?admin, enter password LPCMS@admin, then confirm the 5-character code sent to raivis@63.lv. Editors use the footer email login link and can edit basic content only.'), users.map((u,i)=>h('div',{key:i,className:'admin-card'}, h('div',{className:'d-flex justify-content-between gap-2 mb-2'}, h('strong',null,u.email||'User'), (String(u.email||'').toLowerCase()!=='raivis@63.lv')&&h('button',{className:'btn-ghost',onClick:()=>api.update('settings.users.items',users.filter((_,x)=>x!==i))},'Remove')), h('input',{className:'form-control mb-2',value:u.name||'',onChange:e=>upd(i,'name',e.target.value),placeholder:'Name'}), h('input',{className:'form-control mb-2',value:u.email||'',onChange:e=>upd(i,'email',e.target.value),placeholder:'email@example.com',disabled:String(u.email||'').toLowerCase()==='raivis@63.lv'}), h('select',{className:'form-select mb-2',value:u.role||'editor',onChange:e=>upd(i,'role',e.target.value),disabled:String(u.email||'').toLowerCase()==='raivis@63.lv'}, h('option',{value:'editor'},'Editor'), h('option',{value:'super'},'Super')), h(Bool,{api,path:`settings.users.items.${i}.active`,label:'Active'}))), h('button',{className:'btn-cms',onClick:add},'Add editor')); }

    function LibrariesTab({api}){
      return h(React.Fragment,null,
        h('div',{className:'admin-card'},
          h('h4',{className:'h5'},'Frontend libraries'),
          Object.keys(api.data.settings.libraries||{}).filter(k=>k!=='hcaptcha').map(k=>h(Bool,{key:k,api,path:'settings.libraries.'+k,label:k})),
          h('p',{className:'small muted mb-0'},'hCaptcha is intentionally managed under Forms only.')
        ),
        h('div',{className:'admin-card'},
          h(Txt,{api,path:'settings.analytics.ga4Id',label:'Google Analytics GA4 ID'}),
          h(Bool,{api,path:'settings.analytics.loadAfterCookieConsent',label:'Load GA only after cookie consent'}),
          h(Bool,{api,path:'settings.cookies.enabled',label:'Enable cookie banner'})
        )
      );
    }
    function RedirectsTab({api}){ const arr=api.data.redirects||[]; function upd(i,k,v){ const next=[...arr]; next[i]={...next[i],[k]:v}; api.update('redirects',next); } return h(React.Fragment,null, arr.map((r,i)=>h('div',{key:i,className:'border rounded-4 p-3 mb-3'}, h(Bool,{api,path:`redirects.${i}.enabled`,label:'Enabled'}), h('input',{className:'form-control mb-2',value:r.from||'',onChange:e=>upd(i,'from',e.target.value),placeholder:'/old'}), h('input',{className:'form-control mb-2',value:r.to||'',onChange:e=>upd(i,'to',e.target.value),placeholder:'/new'}), h('input',{className:'form-control mb-2',value:r.status||301,onChange:e=>upd(i,'status',parseInt(e.target.value||301,10)),placeholder:'301'}), h('button',{className:'btn-ghost',onClick:()=>api.update('redirects',arr.filter((_,x)=>x!==i))},'Remove'))), h('button',{className:'btn-cms',onClick:()=>api.update('redirects',[...arr,{from:'/old-page',to:'/',status:301,enabled:true}])},'Add redirect')); }
    function ServerTab({api}){ const status=api.boot.server||{}; async function sync(){ const r=await fetch('?api=sync-htaccess',{method:'POST',headers:{'X-CMS-CSRF':api.csrf}}); const j=await cmsResponseJson(r); if(j.server) api.setBoot({...api.boot,server:j.server}); api.setNotice(j.message||'Server config checked.'); } return h(React.Fragment,null, h('div',{className:'alert alert-info'},'index.php auto-generates root .htaccess and assets/.htaccess. Custom rules outside the FlatCMS marker block are preserved.'), h(Bool,{api,path:'settings.htaccess.autoSync',label:'Auto-sync .htaccess files'}), h(Bool,{api,path:'settings.htaccess.prettyUrls',label:'Pretty SEO URLs enabled'}), h(Bool,{api,path:'settings.htaccess.forceHttps',label:'Force HTTPS redirect'}), h(Bool,{api,path:'settings.htaccess.removeWww',label:'Redirect www to non-www'}), h(Txt,{api,path:'settings.htaccess.assetCacheDays',label:'Asset cache days',type:'number'}), h(Bool,{api,path:'settings.htaccess.protectJson',label:'Block direct access to data.json'}), h('button',{className:'btn-cms mt-2',onClick:sync},'Sync .htaccess now'), h('div',{className:'small muted mt-3'}, 'Root .htaccess: ', String(!!status.rootExists), ' / Assets .htaccess: ', String(!!status.assetsExists), ' / Root writable: ', String(!!status.rootWritable), ' / Assets writable: ', String(!!status.assetsWritable))); }
    function JsonTab({api}){ const [txt,setTxt]=useState(()=>JSON.stringify(api.data,null,2)); useEffect(()=>setTxt(JSON.stringify(api.data,null,2)),[api.data.version]); function apply(){ try{ api.replace(JSON.parse(txt)); }catch(e){ api.setNotice('JSON error: '+e.message); } } return h(React.Fragment,null, h(Field,{label:'Full data.json editor'}, h('textarea',{className:'form-control',rows:18,value:txt,onChange:e=>setTxt(e.target.value),spellCheck:false})), h('button',{className:'btn-cms me-2',onClick:apply},'Apply JSON'), h('button',{className:'btn-ghost',onClick:()=>{const a=document.createElement('a');a.href=URL.createObjectURL(new Blob([JSON.stringify(api.data,null,2)],{type:'application/json'}));a.download='data.json';a.click();}},'Download JSON')); }

    ReactDOM.createRoot(rootEl).render(h(App));
  })();
  </script>
</body>
</html>
