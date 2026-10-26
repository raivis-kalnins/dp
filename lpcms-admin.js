  (() => {
    const rh = React.createElement;
    const {useEffect, useMemo, useState, useRef} = React;
    // Let Chromium expose its native PWA install UI. We intentionally do not
    // call preventDefault() on beforeinstallprompt on normal website pages,
    // which avoids the misleading "Banner not shown" console diagnostic.
    window.__lpcmsInstallPrompt=null;
    window.addEventListener('appinstalled',()=>{ window.__lpcmsInstallPrompt=null; window.dispatchEvent(new CustomEvent('lpcms:install-ready')); });
    const rootEl = document.getElementById('root');
    const rootDataset = document.documentElement?.dataset || {};
    const configLanguages = String(rootDataset.lpcmsLangs||'en').split(',').map(x=>x.trim().toLowerCase()).filter(Boolean).map(code=>({code,label:code.toUpperCase(),name:code.toUpperCase()}));
    const CLIENT_CONFIG = {
      basePath:String(rootDataset.lpcmsBase||''),
      serverLang:String(rootDataset.lpcmsServerLang||document.documentElement?.lang||'en').toLowerCase(),
      defaultLang:String(rootDataset.lpcmsDefaultLang||'en').toLowerCase(),
      languages:configLanguages
    };
    const seed = {version:'',settings:{defaultLang:CLIENT_CONFIG.defaultLang,languages:CLIENT_CONFIG.languages,localization:{},theme:{grid:'container',customCss:''},analytics:{},cookies:{enabled:false},app:{enabled:false},hcaptcha:{enabled:false}},header:{logoText:'',logoImage:'',menu:[]},footer:{menu:[],social:[]},products:{basketEnabled:false,shopEnabled:false,cartEnabled:false},chatbot:{enabled:false}};
    const I18N = {languages:CLIENT_CONFIG.languages,strings:{},adminStrings:{},adminTerms:{}};
    async function cmsResponseJson(response){
      const body=await response.text();
      try{return JSON.parse(body)}catch(err){
        const preview=body.replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim().slice(0,220);
        throw new Error(preview ? `Server returned non-JSON (HTTP ${response.status}): ${preview}` : `Server returned invalid JSON (HTTP ${response.status}).`);
      }
    }
    const CMS_BASE_PATH = String(CLIENT_CONFIG.basePath||'');
    const SERVER_LANG = String(CLIENT_CONFIG.serverLang||seed.settings?.defaultLang||'en').toLowerCase();
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
    const LOCALIZED_CONTENT_ROOTS=new Set(['header','hero','about','stats','features','services','wpThemes','process','gallery','apps','testimonials','cards','contentList','team','growth','industries','logos','products','booking','adminShowcase','pricing','demoIntake','blog','faq','contact','cta','footer','chatbot','pages']);
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

    function EditableImage({api,path,alt='',className='',fallback=null,fallbackSrc='',errorSrc='',target='img',hideWhenEmpty=false,onView=null,eager=false,iconPath=''}){
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
          value?h('img',{src:value,alt,loading:eager?'eager':'lazy',decoding:'async',fetchPriority:eager?'high':'low','data-lightbox':'site','data-lightbox-src':value,onError:errorSrc?e=>{const src=mediaUrl(errorSrc);const img=e.currentTarget;if(!img.dataset.cmsImageFallback&&src){img.dataset.cmsImageFallback='1';img.src=src;img.dataset.lightboxSrc=src;img.alt=String(alt||'App')+' preview';}}:undefined}):h('div',{className:'cms-image-fallback'},iconPath?icon(selectedIcon||'bi bi-plus-lg'):(fallback||icon('bi bi-image'))),
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
      const [bootReady,setBootReady] = useState(false);
      const [bootError,setBootError] = useState('');
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
      useEffect(()=>{ fetch('?api=site',{cache:'no-store'}).then(r=>cmsResponseJson(r)).then(j=>{ if(!j.ok)throw new Error(j.error||'Could not load site data.'); if(j.i18n&&typeof j.i18n==='object'){ Object.keys(I18N).forEach(k=>delete I18N[k]); Object.assign(I18N,j.i18n); } setBoot(j); setData(j.data); if(j.lang&&LANGUAGE_CODES.includes(String(j.lang).toLowerCase()))setLangState(String(j.lang).toLowerCase()); setBootReady(true); }).catch(err=>{ const message=err?.message||'Could not load site data.'; console.error('LPCMS API boot failed:',err); setBootError(message); setBootReady(true); }); }, []);
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

      if(!bootReady) return h('div',{className:'lpcms-boot-screen','aria-hidden':'true'});
      if(bootError) return h('main',{className:'lpcms-boot-error',role:'alert'},h('strong',null,'Website could not be loaded.'),h('span',null,bootError));

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
      const sectionRegistry={hero:Hero,about:About,stats:Stats,features:FeatureBlocks,services:Services,wpThemes:WpThemes,process:Process,gallery:Gallery,apps:DigitalPulseApps,testimonials:Testimonials,cards:ImageCards,contentList:ContentList,team:TeamLead,growth:GrowthMarketing,industries:Industries,logos:LogoStrip,products:Products,booking:Booking,adminShowcase:AdminShowcase,pricing:Pricing,demoIntake:DemoIntake,blog:Blog,faq:FAQ,contact:Contact,cta:CTA};
      const defaultSectionOrder=Object.keys(sectionRegistry); const savedOrder=Array.isArray(data.settings?.sectionOrder)?data.settings.sectionOrder:defaultSectionOrder; const sectionOrder=[...new Set([...savedOrder.filter(x=>sectionRegistry[x]),...defaultSectionOrder.filter(x=>!savedOrder.includes(x))])];
      return h(InlineEditContext.Provider,{value:{data,admin,update,requestSavePrompt,csrf,setNotice,lang}},
        h(Header,{api}),
        boot.route?.item && h(RoutePage,{api,route:boot.route}),
        ...sectionOrder.map((key,sectionIndex)=>{const Comp=sectionRegistry[key];if(!Comp||!data[key]?.enabled)return null;const pattern=String(data.settings?.sectionPatterns?.[key]||'1');const reveal=key==='hero'?'':(sectionIndex%2===0?' reveal-section reveal-left':' reveal-section reveal-right');return h('div',{key,className:'section-pattern-'+pattern+reveal},h(Comp,{api}));}),
        h(Footer,{api}),
        h('button',{className:'btn-cms scroll-up',onClick:()=>scrollTo({top:0,behavior:'smooth'})},'↑'),
        data.products?.basketEnabled && quoteItems.length>0 && h('button',{className:'btn-cms basket-btn quote-floating',onClick:()=>setModal({type:'basket'})}, icon('bi bi-bag'), ' Quote ', h('span',{className:'basket-count'},quoteItems.length)),
        data.products?.shopEnabled && data.products?.cartEnabled && shopCartItems.length>0 && h('button',{className:'btn-cms basket-btn cart-floating',onClick:()=>setModal({type:'cart'})}, icon('bi bi-cart3'), ' Cart ', h('span',{className:'basket-count'},shopCartItems.length)),
        admin && h(AdminPanel,{api,open:adminOpen,onClose:()=>setAdminOpen(false)}),
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
    function SectionTitle({api,base}){ const E=api.Editable; const defaults={stats:'Highlights',features:'Features',services:'Services',wpThemes:'WordPress sector themes',process:'How it works',gallery:'Gallery',apps:'DigitalPulse apps',testimonials:'Testimonials',cards:'Highlights',contentList:'Details',team:'Team lead',growth:'Marketing, SEO & AI content',industries:'Industries',logos:'Trusted by',products:'Products',booking:'Booking',adminShowcase:'Admin & editing',pricing:'Pricing',demoIntake:'Free demo',blog:'Blog',faq:'FAQ',contact:'Contact'}; const fallback=api.t(defaults[base]||String(base||'').replace(/([a-z])([A-Z])/g,'$1 $2').replace(/^./,m=>m.toUpperCase())); return h('div',{className:'section-title'}, h(E,{path:base+'.preheader',tag:'div',className:'eyebrow',placeholder:fallback}), h(E,{path:base+'.title',tag:'h2'}), h(E,{path:base+'.subtitle',tag:'p'})); }

    function Header({api}){ const {data,setModal,lang,setLang,admin,update}=api; const [open,setOpen]=useState(false); const [langOpen,setLangOpen]=useState(false); const langRef=useRef(null); const E=api.Editable; const menu=data.header.menu||[]; const flagMap={en:'/assets/img/flags/gb.svg',lv:'/assets/img/flags/lv.svg',ru:'/assets/img/flags/ru.svg',fr:'/assets/img/flags/fr.svg',es:'/assets/img/flags/es.svg',de:'/assets/img/flags/de.svg',pl:'/assets/img/flags/pl.svg',lt:'/assets/img/flags/lt.svg',et:'/assets/img/flags/ee.svg',da:'/assets/img/flags/dk.svg',no:'/assets/img/flags/no.svg',sv:'/assets/img/flags/se.svg',fi:'/assets/img/flags/fi.svg',is:'/assets/img/flags/is.svg'}; const languageNames={en:'English',lv:'Latviešu',ru:'Русский',fr:'Français',es:'Español',de:'Deutsch',pl:'Polski',lt:'Lietuvių',et:'Eesti',da:'Dansk',no:'Norsk',sv:'Svenska',fi:'Suomi',is:'Íslenska'}; const languages=data.settings.languages||I18N.languages||[]; const activeLang=languages.find(l=>String(l.code).toLowerCase()===String(lang).toLowerCase())||{code:lang,label:String(lang||'EN').toUpperCase()}; useEffect(()=>{ if(!langOpen)return; const close=e=>{if(langRef.current&&!langRef.current.contains(e.target))setLangOpen(false)}; const esc=e=>{if(e.key==='Escape')setLangOpen(false)}; document.addEventListener('pointerdown',close);document.addEventListener('keydown',esc);return()=>{document.removeEventListener('pointerdown',close);document.removeEventListener('keydown',esc)}; },[langOpen]); useEffect(()=>{ if(!open)return; const prevBodyOverflow=document.body.style.overflow; const prevHtmlOverflow=document.documentElement.style.overflow; document.body.style.overflow='hidden'; document.documentElement.style.overflow='hidden'; const esc=e=>{if(e.key==='Escape')setOpen(false)}; const resize=()=>{if(window.innerWidth>=1200)setOpen(false)}; document.addEventListener('keydown',esc); window.addEventListener('resize',resize,{passive:true}); return()=>{document.removeEventListener('keydown',esc);window.removeEventListener('resize',resize);document.body.style.overflow=prevBodyOverflow;document.documentElement.style.overflow=prevHtmlOverflow}; },[open]); function chooseLanguage(code){setLangOpen(false);setLang(code)} function linkProps(it,mobile=false){const blank=it?.target==='_blank';return {className:mobile?'':'nav-linkx',href:api.href(it?.href||'#'),target:blank?'_blank':undefined,rel:blank?'noopener noreferrer':undefined,onClick:()=>setOpen(false)}} function renderMenu(items,mobile=false){ return h('ul',null,items.map((it,i)=>h('li',{key:i,className:!mobile&&it.children?.length?'has-sub':''}, h('a',linkProps(it,mobile), translate(api,it,'label'), it.children?.length? h('span',{className:'nav-chevron','aria-hidden':'true'}):null), it.children?.length?h('ul',{className:mobile?'mobile-submenu':'submenu'},it.children.map((c,j)=>h('li',{key:j},h('a',linkProps(c,mobile),translate(api,c,'label'))))):null))); }
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
          autoHeight:true,
          speed:760,
          grabCursor:slides.length>1,
          keyboard:{enabled:true},
          autoplay:slides.length>1?{delay:5600,disableOnInteraction:false,pauseOnMouseEnter:true}:false,
          pagination:{el:swiperEl.current.querySelector('.hero-swiper-pagination'),clickable:true},
          navigation:{nextEl:swiperEl.current.querySelector('.hero-swiper-next'),prevEl:swiperEl.current.querySelector('.hero-swiper-prev')},
          a11y:{enabled:true}
        });
        // Refresh the active slide height when an image finishes decoding
        // and after an orientation change (especially important on mobiles).
        const refreshHeight=()=>{
          if(inst.destroyed)return;
          window.requestAnimationFrame(()=>{if(!inst.destroyed)inst.updateAutoHeight(0);});
        };
        const images=Array.from(swiperEl.current.querySelectorAll('.swiper-slide img'));
        images.forEach(img=>img.addEventListener('load',refreshHeight,{passive:true}));
        window.addEventListener('orientationchange',refreshHeight,{passive:true});
        refreshHeight();
        return()=>{
          images.forEach(img=>img.removeEventListener('load',refreshHeight));
          window.removeEventListener('orientationchange',refreshHeight);
          try{inst.destroy(true,true)}catch(e){}
        };
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

    function WpThemes({api}){
      const d=api.data.wpThemes||{}, E=api.Editable, items=Array.isArray(d.items)?d.items:[];
      const tech=[['bi bi-wordpress','WordPress'],['bi bi-bootstrap','Bootstrap'],['bi bi-grid-3x3-gap','Gutenberg'],['bi bi-cart3','WooCommerce']];
      return h('section',{id:'wp-themes',className:'section wp-themes-section'},h(Container,{api},
        h('div',{className:'wp-themes-head'},
          h(SectionTitle,{api,base:'wpThemes'}),
          h('div',{className:'wp-themes-tech','aria-label':api.t('WordPress technology stack')},tech.map(([ic,label])=>h('span',{className:'wp-tech-pill',key:label},icon(ic),label)))
        ),
        h('div',{className:'wp-themes-layout'},
          h('div',{className:'wp-themes-media-card'},
            h(EditableImage,{api,path:'wpThemes.image',alt:api.localizedValue('wpThemes.alt')||api.localizedValue('wpThemes.title')||'WordPress themes',className:'wp-themes-image-frame',hideWhenEmpty:true}),
            d.badge&&h('div',{className:'wp-themes-media-badge'},icon('bi bi-wordpress'),h(E,{path:'wpThemes.badge'}))
          ),
          h('div',null,
            h('div',{className:'wp-themes-benefits'},items.map((it,i)=>h('article',{className:'wp-theme-benefit',key:i},
              h('div',{className:'wp-theme-benefit-icon'},icon(it.icon||'bi bi-check2-circle')),
              h(E,{path:`wpThemes.items.${i}.title`,tag:'h3'}),
              h(E,{path:`wpThemes.items.${i}.text`,tag:'p',className:'muted mb-0'})
            ))),
            h('div',{className:'wp-themes-note'},
              h(E,{path:'wpThemes.noteTitle',tag:'strong'}),
              h(E,{path:'wpThemes.noteText',tag:'p',className:'muted'})
            ),
            d.buttonText&&h('div',{className:'wp-themes-cta'},h('a',{className:'btn-cms',href:d.buttonHref||'#contact'},h(E,{path:'wpThemes.buttonText'})),h('span',{className:'small muted'},api.t('Professional WordPress delivery for business and commerce.')))
          )
        )
      ));
    }

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
        h('div',{className:'row g-4'},filtered.slice(0,count).map(it=>{const i=items.indexOf(it);return h('div',{className:'col-md-6 col-lg-4',key:it.slug||i},h('article',{className:'cms-card portfolio-card'},h('div',{className:'portfolio-image-wrap'},h(EditableImage,{api,path:`gallery.items.${i}.image`,alt:api.localizedValue(`gallery.items.${i}.alt`)||it.alt||it.title||'',className:'portfolio-image',target:'gallery',errorSrc:it.fallbackImage||''})),h('div',{className:'portfolio-card-body'},h('div',{className:'portfolio-meta'},h('span',{className:'portfolio-kind'},categoryTitle(it.category)||api.t(it.kind||'')),h('span',{className:cls('portfolio-status',String(it.status||'').toLowerCase().includes('development')&&'is-development')},api.t(it.status||'Live project'))),h(E,{path:`gallery.items.${i}.title`,tag:'h3',className:'h4 fw-bold'}),h(E,{path:`gallery.items.${i}.text`,tag:'p',className:'muted mb-0'}),Array.isArray(it.tech)&&h('div',{className:'tech-chips'},it.tech.map((t,j)=>h('span',{key:j,className:'tech-chip'},t))),h('div',{className:'portfolio-actions'},h('button',{type:'button',className:'btn-ghost',onClick:()=>api.setModal({type:'item',group:'gallery',index:i})},api.t('View project')),it.url&&/^https:\/\//i.test(String(it.url))&&h('a',{className:'btn-cms portfolio-visit-link',href:it.url,target:'_blank',rel:'noopener noreferrer',title:api.t('Open app in a new tab')+': '+(api.localizedValue(`gallery.items.${i}.title`)||it.title)},icon('bi bi-box-arrow-up-right'),api.t(it.linkText||'Open app'))))))})),
        cfg.showButton&&count<filtered.length&&h('div',{className:'load-more-wrap'},h('button',{className:'btn-cms',onClick:()=>setCount(c=>Math.min(filtered.length,c+batch))},api.localizedValue('gallery.buttonText')||api.t('Load more projects')))
      ));
    }

    function DigitalPulseApps({api}){
      const cfg=api.data.apps||{};
      const E=api.Editable;
      const items=Array.isArray(cfg.items)?cfg.items:[];
      const appCountLabel=({lv:'lietotnes',ru:'приложений',fr:'apps',es:'apps',de:'Apps',pl:'aplikacji',lt:'programėlių',et:'rakendust',da:'apps',no:'apper',sv:'appar',fi:'sovellusta',is:'öpp'})[api.lang]||'apps';
      return h('section',{id:'apps',className:'section digitalpulse-apps-section'},h(Container,{api},
        h('div',{className:'digitalpulse-apps-heading'},
          h(SectionTitle,{api,base:'apps'}),
          h('span',{className:'digitalpulse-apps-count'},icon('bi bi-grid-3x3-gap'),`${items.length} ${appCountLabel}`)
        ),
        h('div',{className:'digitalpulse-apps-grid'},items.map((app,i)=>{
          const rawUrl=String(app.url||'').trim();
          const url=/^(https?:\/\/[^\s]+|#[A-Za-z][\w-]*|\/[^\s]*)$/i.test(rawUrl)?rawUrl:'#apps';
          const external=/^https?:\/\//i.test(url);
          const name=api.localizedValue(`apps.items.${i}.title`)||app.title||'App';
          return h('a',{key:app.slug||i,className:'digitalpulse-app-tile',href:url,target:external?'_blank':undefined,rel:external?'noopener noreferrer':undefined,
            'aria-label':api.t('Open app in a new tab')+': '+name,
            onClick:api.admin?e=>{e.preventDefault()}:undefined},
            h('span',{className:'digitalpulse-app-icon','aria-hidden':'true'},icon(app.icon||'bi bi-app-indicator')),
            h('span',{className:'digitalpulse-app-content'},
              h(E,{path:`apps.items.${i}.title`,tag:'strong',className:'digitalpulse-app-name'}),
              h(E,{path:`apps.items.${i}.text`,tag:'span',className:'digitalpulse-app-description'})
            ),
            h('span',{className:'digitalpulse-app-arrow','aria-hidden':'true'},icon('bi bi-arrow-up-right'))
          );
        }))
      ));
    }

    function Testimonials({api}){ const E=api.Editable; return h('section',{id:'testimonials',className:'section cms-soft'}, h(Container,{api}, h(SectionTitle,{api,base:'testimonials'}), h(LoadMoreGrid,{api,base:'testimonials',render:(it,i)=>h('div',{className:'col-md-6 col-lg-4',key:i}, h('article',{className:'cms-card'}, h('div',{className:'d-flex align-items-center gap-3 mb-3'}, h(EditableImage,{api,path:`testimonials.items.${i}.photo`,alt:it.name||'',className:'avatar cms-image-avatar',fallback:icon('bi bi-person')}), h('div',null,h(E,{path:`testimonials.items.${i}.name`,tag:'strong'}), h(E,{path:`testimonials.items.${i}.role`,tag:'div',className:'small muted'}))), h(E,{path:`testimonials.items.${i}.text`,tag:'p',className:'mb-0'}))) }))); }

    function FAQ({api}){
      const E=api.Editable;
      const [open,setOpen]=useState(0);
      const items=api.data.faq?.items||[];
      return h('section',{id:'faq',className:'section'},
        h(Container,{api},
          h(SectionTitle,{api,base:'faq'}),
          h('div',{className:'mx-auto faq-list',style:{maxWidth:860}},
            ...items.map((it,i)=>{
              const isOpen=open===i;
              const answerId='faq-answer-'+i;
              return h('div',{className:cls('faq-item',isOpen&&'is-open'),key:i},
                h('button',{type:'button',className:'faq-q','aria-expanded':isOpen?'true':'false','aria-controls':answerId,onClick:()=>setOpen(isOpen?null:i)},
                  h('span',{className:'faq-q-text'},h(E,{path:`faq.items.${i}.question`})),
                  h('span',{className:'faq-toggle','aria-hidden':'true'},isOpen?'−':'+')
                ),
                isOpen?h('div',{id:answerId,className:'faq-a'},h(E,{path:`faq.items.${i}.answer`,tag:'div',html:true})):null
              );
            })
          )
        )
      );
    }

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
                  h('img',{src:mediaUrl(p.image),alt:api.localizedValue(`gallery.items.${idx}.alt`)||p.title||'',loading:'lazy',decoding:'async',onError:p.fallbackImage?e=>{const img=e.currentTarget;if(!img.dataset.cmsImageFallback){img.dataset.cmsImageFallback='1';img.src=mediaUrl(p.fallbackImage);}}:undefined}),
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
      const E=api.Editable;
      const pricing=api.data.pricing||{};
      const items=pricing.items||[];
      const [category,setCategory]=useState('all');
      const swiperEl=useRef(null);
      const instRef=useRef(null);
      const lang=String(api.lang||'en').toLowerCase();
      const langStrings=pricing.i18n?.[lang]||{};
      const detailsLabels={en:"What's included",lv:'Kas iekļauts',ru:'Что включено',fr:'Ce qui est inclus',es:'Qué incluye',de:'Leistungsumfang',pl:'Co zawiera pakiet',lt:'Kas įskaičiuota',et:'Mis sisaldub',da:'Dette er inkluderet',no:'Dette er inkludert',sv:'Detta ingår',fi:'Mitä sisältyy',is:'Hvað er innifalið'};
      const detailsLabel=detailsLabels[lang]||detailsLabels.en;
      const labels={
        filterAll:'All plans',filterLpcms:'LPCMS',filterWebsites:'Websites',filterCommerce:'E-commerce',
        filterCustom:'Custom development',filterCare:'Care',fromLabel:'From',ukLabel:'UK',euLabel:'Europe',lvLabel:'Latvia',
        quoteCaveat:'Indicative pricing, not a binding quote.',
        enterpriseTitle:pricing.enterpriseTitle||'Custom & Enterprise Development',
        enterpriseText:pricing.enterpriseText||'',enterpriseButton:pricing.enterpriseButton||'Request a scoped quote',note:pricing.note||''
      };
      const word=(key)=>langStrings[key]||labels[key]||'';
      const categories=[
        ['all','filterAll'],['lpcms','filterLpcms'],['websites','filterWebsites'],
        ['commerce','filterCommerce'],['custom','filterCustom'],['care','filterCare']
      ];
      const selectedItems=items.map((item,index)=>({item,index})).filter(({item})=>category==='all'||(item.group||'websites')===category);
      useEffect(()=>{
        const setup=()=>{
          if(!swiperEl.current||!window.Swiper)return;
          /* Pricing cards stack natively on phones. Avoid Swiper transforms here:
             they can calculate a zero-height wrapper and leave only pagination visible. */
          if(instRef.current){try{instRef.current.destroy(true,true)}catch(e){}instRef.current=null;}
        };
        setup();
        const paginationEl=swiperEl.current?.querySelector('.pricing-swiper-pagination');
        const onPaginationClick=(event)=>{
          const bullet=event.target?.closest?.('.swiper-pagination-bullet');
          if(!paginationEl||!bullet||!paginationEl.contains(bullet)||!instRef.current)return;
          const index=Array.from(paginationEl.querySelectorAll('.swiper-pagination-bullet')).indexOf(bullet);
          if(index>=0)try{instRef.current.slideTo(index,450)}catch(e){}
        };
        paginationEl?.addEventListener('click',onPaginationClick);
        window.addEventListener('resize',setup);
        return()=>{
          paginationEl?.removeEventListener('click',onPaginationClick);
          window.removeEventListener('resize',setup);
          if(instRef.current){try{instRef.current.destroy(true,true)}catch(e){}instRef.current=null;}
        };
      },[category,selectedItems.length,lang]);
      const currencyLocale={en:'en-GB',lv:'lv-LV',ru:'ru-RU',fr:'fr-FR',es:'es-ES',de:'de-DE',pl:'pl-PL',lt:'lt-LT',et:'et-EE',da:'da-DK',no:'nb-NO',sv:'sv-SE',fi:'fi-FI',is:'is-IS'};
      const formatPrice=(raw,currency)=>{
        const ungrouped=String(raw||'').replace(/,/g,'').replace(/[^\d.]/g,'');
        const amount=Number(ungrouped);
        if(!ungrouped||!Number.isFinite(amount))return String(raw||'');
        const decimal=!Number.isInteger(amount);
        return new Intl.NumberFormat(currencyLocale[lang]||'en-GB',{
          style:'currency',currency,minimumFractionDigits:decimal?2:0,maximumFractionDigits:decimal?2:0
        }).format(amount);
      };
      const offer=(item,price,label,currency,fieldPath,secondary=false)=>h('div',{className:secondary?'pricing-offer-secondary':'pricing-offer-primary'},
        h('span',{className:'pricing-price-label'},label),
        h('div',{className:secondary?'secondary':'pricing-price'},
          item.isFrom?word('fromLabel')+' ':null,
          api.admin&&fieldPath?h(E,{path:fieldPath,tag:'span',className:'dp-pricing-admin-raw'}):formatPrice(price,currency)
        )
      );
      return h('section',{id:'pricing',className:'section cms-soft'},h(Container,{api},
        h(SectionTitle,{api,base:'pricing'}),
        h('div',{className:'dp-pricing-filterbar',role:'group','aria-label':api.t('Pricing')},
          categories.map(([id,labelKey])=>h('button',{
            type:'button',key:id,className:'dp-pricing-filter',
            'aria-pressed':category===id,onClick:()=>setCategory(id)
          },word(labelKey)))
        ),
        h('div',{className:'swiper pricing-swiper',ref:swiperEl,key:category},
          h('div',{className:'swiper-wrapper row g-4 pricing-grid-clean'},selectedItems.map(({item:it,index:i})=>
            h('div',{className:'swiper-slide col-12 col-md-6 col-xl-3 pricing-slide',key:it.id||i},
              h('article',{className:cls('cms-card pricing-card pricing-card-clean',it.id==='wp-business'&&'featured')},
                h('div',{className:'pricing-card-head'},
                  it.badge&&h('span',{className:'plan-badge'},h(E,{path:`pricing.items.${i}.badge`})),
                  h(E,{path:`pricing.items.${i}.title`,tag:'h3',className:'pricing-title'}),
                  h('div',{className:'pricing-dual-price'},
                    lang==='en'?
                      h(React.Fragment,null,
                        offer(it,it.price,word('ukLabel')+' · GBP','GBP',`pricing.items.${i}.price`),
                        offer(it,it.secondaryPrice||it.priceEur,word('euLabel')+' · EUR','EUR',`pricing.items.${i}.secondaryPrice`,true)
                      ):
                      lang==='lv'?offer(it,it.latviaPrice||it.secondaryPrice||it.price,word('lvLabel')+' · EUR','EUR',`pricing.items.${i}.latviaPrice`):
                        offer(it,it.secondaryPrice||it.priceEur||it.price,word('euLabel')+' · EUR','EUR',`pricing.items.${i}.secondaryPrice`),
                    h(E,{path:`pricing.items.${i}.period`,tag:'div',className:'pricing-period muted'})
                  ),
                ),
                h('details',{className:'pricing-details',onToggle:()=>{
                    const swiper=instRef.current;
                    if(swiper)window.requestAnimationFrame(()=>{try{swiper.update();swiper.updateAutoHeight(220)}catch(e){}});
                  }},
                  h('summary',{className:'pricing-details-toggle'},h('span',null,detailsLabel),icon('bi bi-chevron-down pricing-details-chevron')),
                  h('div',{className:'pricing-details-body'},
                    h(E,{path:`pricing.items.${i}.text`,tag:'p',className:'pricing-intro muted'}),
                    Array.isArray(it.features)&&h('div',{className:'pricing-features'},it.features.map((f,j)=>
                      h('div',{key:j,className:'plan-feature'},h('span',{className:'plan-feature-icon'},icon('bi bi-check2')),h(E,{path:`pricing.items.${i}.features.${j}`}))
                    ))
                  )
                ),
                h('a',{className:'btn-cms pricing-action',href:it.buttonHref||'#contact'},h(E,{path:`pricing.items.${i}.buttonText`}))
              )
            )
          )),
          h('div',{className:'pricing-swiper-pagination'})
        ),
        h('p',{className:'dp-pricing-note'},h('strong',null,word('quoteCaveat')+' '),word('note')),
        h('div',{className:'dp-pricing-enterprise'},
          h('div',{className:'dp-pricing-enterprise-text'},
            h('h3',null,word('enterpriseTitle')),
            h('p',null,word('enterpriseText'))
          ),
          h('a',{className:'btn-cms',href:'#contact'},word('enterpriseButton'),icon('bi bi-arrow-up-right'))
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
      const [form,setForm]=useState({plan:'premium',currency:api.lang==='en'?'GBP':'EUR',name:'',email:'',phone:'',company:'',industry:'',tagline:'',about:'',services:'',products:'',address:'',existingWebsite:'',preferredDomain:'',language:'English',goals:'',primaryColor:'#0d6efd',style:'clean',chatChoice:'chatbot',whatsapp:'',bookingNeeded:false,socials:'',consent:false,_hp:''});
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
        h('div',{className:'col-12'},h('div',{className:'demo-plan-grid'},plans.map(p=>{
          const priceItem=(api.data.pricing?.items||[]).find(it=>it.id===({'premium':'lpcms-premium','premium-pro':'lpcms-pro','wordpress':'wp-business','woocommerce':'woo-standard'}[p.id]||''));
          const loc=priceItem?.i18n?.[api.lang]||{};
          const price=api.lang==='en'?p.price:api.lang==='lv'?(p.priceLatvia||p.priceEur||p.price):(p.priceEur||p.price);
          const fromLabel=api.data.pricing?.i18n?.[api.lang]?.fromLabel||'From';
          const localizedPrice=api.lang==='en'?price:String(price).replace(/^from /i,fromLabel+' ').replace(/\/ month$/i,api.t('/ month'));
          return h('button',{type:'button',key:p.id,className:cls('demo-plan-choice',form.plan===p.id&&'active'),onClick:()=>setf('plan',p.id)},
            h('strong',null,loc.title||p.title),h('span',null,loc.badge||p.description),h('small',null,localizedPrice));
        }))),
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
      useEffect(()=>{
        if(!mapRef.current||!window.L)return;
        const L=window.L;
        const defaults=[
          {key:'uk',label:'Northampton, England',short:'Northampton',lat:52.2405,lng:-0.9027},
          {key:'lv',label:'Riga, Latvia',short:'Riga',lat:56.9496,lng:24.1052}
        ];
        const points=defaults.map((fallback,i)=>{
          const loc=locations?.[i]||{};
          const lat=Number.isFinite(Number(loc.lat))?Number(loc.lat):fallback.lat;
          const lng=Number.isFinite(Number(loc.lng))?Number(loc.lng):fallback.lng;
          const label=String(loc.city||fallback.label);
          return {...fallback,label,short:(label.split(',')[0]||fallback.short).trim(),lat,lng};
        });
        const map=L.map(mapRef.current,{zoomControl:true,scrollWheelZoom:false,attributionControl:true,preferCanvas:true,minZoom:2,maxZoom:18});
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{
          minZoom:2,maxZoom:18,
          attribution:'&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors'
        }).addTo(map);
        const pinIcon=L.divIcon({className:'delivery-leaflet-pin-wrap',html:'<span class="delivery-leaflet-pin"></span>',iconSize:[38,46],iconAnchor:[19,43]});
        points.forEach((point,i)=>{
          const marker=L.marker([point.lat,point.lng],{icon:pinIcon,title:point.label,keyboard:true,riseOnHover:true}).addTo(map);
          const label=document.createElement('span'); label.textContent=point.short;
          marker.bindTooltip(label,{permanent:true,direction:i===0?'right':'left',offset:i===0?[13,-3]:[-13,-3],className:'delivery-leaflet-tooltip',opacity:1});
        });
        const bounds=L.latLngBounds(points.map(point=>[point.lat,point.lng]));
        const fit=()=>{map.invalidateSize(false);map.fitBounds(bounds,{paddingTopLeft:[68,58],paddingBottomRight:[68,58],maxZoom:5,animate:false});};
        requestAnimationFrame(fit);
        const timer=setTimeout(fit,140);
        const resize=()=>map.invalidateSize(false);
        window.addEventListener('resize',resize,{passive:true});
        return()=>{clearTimeout(timer);window.removeEventListener('resize',resize);try{map.remove()}catch(e){}};
      },[locations]);
      return h('div',{className:'delivery-map-embed'},
        h('div',{ref:mapRef,className:'map-frame delivery-map-real delivery-leaflet-map',role:'img','aria-label':'Map showing DigitalPulse locations in Northampton, England and Riga, Latvia'})
      );
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
              (d.menu||[]).map((m,i)=>h('a',{key:i,href:api.href(m.href)},translate(api,m,'label'))),
              api.admin
                ? h('button',{type:'button',className:'footer-login-text footer-login-menu-item footer-admin-entry',onClick:()=>api.setAdminOpen(true),'aria-label':api.t('Admin')||'Admin',title:api.t('Admin')||'Admin'},icon('bi bi-person-circle'),h('span',null,api.t('Admin')||'Admin'))
                : h('a',{className:'footer-login-text footer-login-menu-item',href:'/?admin',rel:'nofollow','aria-label':({en:'Login',lv:'Pieteikties',ru:'Войти',fr:'Connexion',es:'Acceder',de:'Anmelden',pl:'Zaloguj się',lt:'Prisijungti',et:'Logi sisse',da:'Log ind',no:'Logg inn',sv:'Logga in',fi:'Kirjaudu',is:'Skrá inn'})[api.lang]||'Login',title:'Administrator login'},icon('bi bi-person-circle'),h('span',null,({en:'Login',lv:'Pieteikties',ru:'Войти',fr:'Connexion',es:'Acceder',de:'Anmelden',pl:'Zaloguj się',lt:'Prisijungti',et:'Logi sisse',da:'Log ind',no:'Logg inn',sv:'Logga in',fi:'Kirjaudu',is:'Skrá inn'})[api.lang]||'Login'))
            ),
            showApps&&h('div',{className:'footer-apps'},
              h('div',{className:'footer-apps-label'},api.t('Install our app')),
              h('div',{className:'footer-app-badges'},installBadge('android'),installBadge('apple'))
            ),
            h('div',{className:'footer-socials','aria-label':api.t('Social media')},
              (d.social||[]).map((s,i)=>{
                const label=api.t(translate(api,s,'label')||'Social media');
                const safeUrl=/^https:\/\/[^\s]+$/i.test(String(s.url||''))?s.url:'#';
                return h('a',{key:i,href:safeUrl,target:safeUrl==='#'?undefined:'_blank',rel:safeUrl==='#'?undefined:'noopener noreferrer',className:cls('footer-social-link',s.iconImage&&'footer-social-image-link'),'aria-label':label,title:label},s.iconImage?h('img',{src:mediaUrl(s.iconImage),alt:'',loading:'lazy',decoding:'async',width:23,height:23}):icon(s.icon));
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
            'data-lightbox-src':mediaUrl(active),onError:isPortfolio&&item.fallbackImage?e=>{const img=e.currentTarget;if(!img.dataset.cmsImageFallback){img.dataset.cmsImageFallback='1';img.src=mediaUrl(item.fallbackImage);img.dataset.lightboxSrc=mediaUrl(item.fallbackImage);img.alt=(item.title||'App')+' preview';}}:undefined
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
          isPortfolio&&item.repositoryUrl&&/^https:\/\//i.test(String(item.repositoryUrl))&&h('a',{
            className:'btn-ghost portfolio-source-link',href:item.repositoryUrl,target:'_blank',rel:'noopener noreferrer'
          },icon('bi bi-github'),' ',api.t('View source code')),
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
      const allSecs=['hero','about','stats','features','services','wpThemes','process','gallery','apps','testimonials','cards','contentList','team','growth','industries','logos','products','booking','adminShowcase','pricing','blog','faq','contact','cta'];
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
          h('strong',null,key==='contentList'?'content list':key==='team'?'team lead':key==='wpThemes'?'WordPress themes':key),
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
        h('strong',null,key==='contentList'?'Content list':key==='team'?'Team lead':key==='gallery'?'Portfolio':key==='wpThemes'?'WordPress themes':key.charAt(0).toUpperCase()+key.slice(1)),
        h('span',{className:'small muted'},'Pattern '+String(get(api.data,'settings.sectionPatterns.'+key)||'1')),
        h('span',{className:'small'},get(api.data,key+'.enabled')?'On':'Off')
      ));

      const appList=api.data.apps?.items||[];
      const appsEditor=h('div',{className:'admin-card'},
        h('details',{className:'dp-admin-app-directory'},
          h('summary',null,'DigitalPulse app directory (',appList.length,') · links and translations'),
          h('div',{className:'pt-3'},
            h(Txt,{api,path:'apps.preheader',label:'Apps eyebrow'}),
            h(Txt,{api,path:'apps.title',label:'Apps section heading'}),
            h(Field,{label:'Apps section subtitle'},h('textarea',{className:'form-control',rows:2,value:api.localizedValue('apps.subtitle')??'',onChange:e=>api.updateLocalized('apps.subtitle',e.target.value)})),
            ...appList.map((app,i)=>h('div',{className:'simple-list-row',key:app.slug||i},
              h('div',{className:'d-flex align-items-center gap-2 mb-2'},
                h('span',{className:'cms-icon'},icon(app.icon||'bi bi-app-indicator')),
                h('strong',{className:'flex-grow-1'},app.title||('App '+(i+1))),
                h('button',{type:'button',className:'mini-remove','aria-label':'Remove app',onClick:()=>api.update('apps.items',appList.filter((_,x)=>x!==i))},icon('bi bi-x-lg'))
              ),
              h('input',{className:'form-control mb-2',value:api.localizedValue(`apps.items.${i}.title`)??'',placeholder:'App name',onChange:e=>api.updateLocalized(`apps.items.${i}.title`,e.target.value)}),
              h('textarea',{className:'form-control mb-2',rows:2,value:api.localizedValue(`apps.items.${i}.text`)??'',placeholder:'Short description',onChange:e=>api.updateLocalized(`apps.items.${i}.text`,e.target.value)}),
              h('input',{className:'form-control mb-2',type:'url',value:app.url||'',placeholder:'https://app.example.com/',onChange:e=>api.update(`apps.items.${i}.url`,e.target.value)}),
              h('div',{className:'input-group'},h('input',{className:'form-control',value:app.icon||'',placeholder:'bi bi-controller',onChange:e=>api.update(`apps.items.${i}.icon`,e.target.value)}),h('button',{type:'button',className:'btn btn-outline-secondary',onClick:()=>{try{sessionStorage.setItem('lpcms_icon_target',`apps.items.${i}.icon`)}catch(err){}api.setAdminRequestedTab('icons')}},'Choose icon'))
            )),
            h('button',{type:'button',className:'btn-ghost mt-2',onClick:()=>api.update('apps.items',[...appList,{slug:'new-app-'+(appList.length+1),title:'New app',text:'App description',url:'https://',icon:'bi bi-app-indicator'}])},icon('bi bi-plus-lg'),' Add app')
          )
        )
      );
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
        h(EditableImage,{api,path:'features.image',alt:api.data.features?.title||'',className:'admin-image-field'}),
        h('div',{className:'small fw-bold mb-1'},'WordPress sector theme image'),
        h(EditableImage,{api,path:'wpThemes.image',alt:api.localizedValue('wpThemes.alt')||api.data.wpThemes?.title||'',className:'admin-image-field'})
      );
      const visualText=h('div',{className:'admin-card'},
        h('h5',null,'Visual text editor'),
        h(RichText,{api,path:'about.body',label:'About body',large:true}),
        h(RichText,{api,path:'cta.text',label:'CTA text / HTML',large:true})
      );

      const wpThemesEditor=h('div',{className:'admin-card'},
        h('h5',null,'WordPress sector themes'),
        h(Txt,{api,path:'wpThemes.preheader',label:'Preheader'}),
        h(Txt,{api,path:'wpThemes.title',label:'Section title'}),
        h(Field,{label:'Section subtitle'},h('textarea',{className:'form-control',rows:3,value:api.localizedValue('wpThemes.subtitle')??'',onChange:e=>api.updateLocalized('wpThemes.subtitle',e.target.value)})),
        h(Txt,{api,path:'wpThemes.badge',label:'Image badge'}),
        h(Txt,{api,path:'wpThemes.noteTitle',label:'Note title'}),
        h(Field,{label:'Note text'},h('textarea',{className:'form-control',rows:3,value:api.localizedValue('wpThemes.noteText')??'',onChange:e=>api.updateLocalized('wpThemes.noteText',e.target.value)})),
        h(Txt,{api,path:'wpThemes.buttonText',label:'Button text'}),
        h(Txt,{api,path:'wpThemes.buttonHref',label:'Button link',localize:false})
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
        appsEditor,
        showcaseEditor,
        showcaseSuperEditor,
        listEditor('features','Features',{image:'',icon:'lucide:wand-sparkles',title:'New feature',text:'Feature description'}),
        listEditor('services','Services',{image:'',icon:'tabler:briefcase',title:'New service',text:'Service description',buttonText:'Read more',buttonHref:'#contact'}),
        wpThemesEditor,
        listEditor('wpThemes','WordPress theme benefits',{icon:'bi bi-check2-circle',title:'New WordPress benefit',text:'Benefit description'}),
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

    function UsersTab({api}){ const users=get(api.data,'settings.users.items')||[]; function upd(i,k,v){ const next=[...users]; next[i]={...next[i],[k]:v}; api.update('settings.users.items',next); } function add(){ api.update('settings.users.items',[...users,{email:'editor@example.com',name:'Editor',role:'editor',active:true}]); } return h(React.Fragment,null, h('div',{className:'alert alert-info'},'Super administrator access uses the protected /?admin login flow. Editors use the email login link and can edit basic content only.'), users.map((u,i)=>h('div',{key:i,className:'admin-card'}, h('div',{className:'d-flex justify-content-between gap-2 mb-2'}, h('strong',null,u.email||'User'), (String(u.email||'').toLowerCase()!=='raivis@63.lv')&&h('button',{className:'btn-ghost',onClick:()=>api.update('settings.users.items',users.filter((_,x)=>x!==i))},'Remove')), h('input',{className:'form-control mb-2',value:u.name||'',onChange:e=>upd(i,'name',e.target.value),placeholder:'Name'}), h('input',{className:'form-control mb-2',value:u.email||'',onChange:e=>upd(i,'email',e.target.value),placeholder:'email@example.com',disabled:String(u.email||'').toLowerCase()==='raivis@63.lv'}), h('select',{className:'form-select mb-2',value:u.role||'editor',onChange:e=>upd(i,'role',e.target.value),disabled:String(u.email||'').toLowerCase()==='raivis@63.lv'}, h('option',{value:'editor'},'Editor'), h('option',{value:'super'},'Super')), h(Bool,{api,path:`settings.users.items.${i}.active`,label:'Active'}))), h('button',{className:'btn-cms',onClick:add},'Add editor')); }

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
