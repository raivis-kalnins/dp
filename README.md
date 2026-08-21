# DigitalPulse - Creative Web Solutions

Multilingual DigitalPulse agency website built on the supplied LPCMS system.

## Included

- DigitalPulse branding, logo, favicon and responsive agency UI.
- Swiper hero with LPCMS / WordPress / WooCommerce positioning and anchor offers.
- Autoplay technology Swiper directly below the hero.
- Technology list includes the supplied professional stack: WordPress, WooCommerce, custom themes/plugins, API integrations, Figma, Adobe XD, ChatGPT Codex, GitHub Copilot, Kimi and Grok, plus PHP, MySQL, REST API, JSON, ReactJS, JavaScript, jQuery, SCSS, Bootstrap and Visual Studio Code.
- Services for LPCMS, WordPress, WooCommerce, web/mobile applications, AI solutions, hosting and maintenance.
- Marketing, SEO and AI-content options covering technical/on-page SEO, local visibility, campaign landing pages, analytics/conversion tracking, AI-assisted text generation and marketing integrations.
- Industries/use-cases grid covering construction, property, automotive, travel, hotels, hospitality, e-commerce, manufacturing, professional services, wellness, education, non-profits, events/booking, trades, creative businesses and SaaS/portals.
- Pricing, highlights, six-step process, 12-item filterable real-project portfolio, project brief, photo-led blog, WP BBuilder showcase, team lead profile, FAQ, contact, CTA and footer.
- Contact coverage for the whole UK and Europe, with Northampton shown first and Riga second on a large Europe map with red location pins.
- 14-language LPCMS language switcher retained: EN, LV, RU, FR, ES, DE, PL, LT, ET, DA, NO, SV, FI and IS.
- New DigitalPulse copy is translated through the existing LPCMS translation pack; technical product/tool names stay unchanged where appropriate.
- Existing LPCMS users/authentication/password behaviour preserved from the supplied project.

## Contact configured on the website

- hello@digitalpulse.click
- +44 7952 641616
- Northampton, England
- Riga, Latvia

## Run locally

PHP 8+ is recommended.

```bash
php -S 127.0.0.1:8080
```

Open `http://127.0.0.1:8080/`.

## Main files

- `index.php` - LPCMS application, public website and administration UI.
- `data.json` - DigitalPulse content and site configuration.
- `assets/i18n.json` - multilingual translations.
- `assets/img/digitalpulse-logo.svg` - main brand logo.
- Contact map uses an integrated OpenStreetMap/CARTO map with UK and Latvia delivery locations.
- `assets/img/portfolio/` - cached screenshots used by the real-project portfolio.
- `README-LPCMS.md` - original LPCMS documentation.

## Notes

Portfolio entries use real project URLs/screenshots supplied for this DigitalPulse site, with in-development projects labelled accordingly. UK agency experience references are kept in a collapsed, click-only section rather than rendered as crawlable project links. Before public deployment, review production mail/API settings, CAPTCHA, analytics consent, write permissions and administrator access policy.

## Brand assets and business card

Print-ready vector logo variants are in `assets/brand/`, including primary colour plus black/white monochrome files suitable for garments, signage, vehicle graphics, A4 documents and business stationery. The downloadable business-card PDF is stored at `assets/docs/digitalpulse-business-card-print.pdf` and is previewed from the Contact section.

## hCaptcha

Public enquiry, project-intake, booking and administrator login forms use hCaptcha. The public site key is stored in `data.json`. The verification secret is loaded server-side from `.secrets.php` (or the `HCAPTCHA_SECRET` environment variable) and `.secrets.php` is excluded by `.gitignore`; do not commit that file to a public repository.
