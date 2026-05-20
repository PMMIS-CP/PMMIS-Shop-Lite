<div align="center">
  <img src="https://img.shields.io/badge/Laravel-13-ff2d20.svg?logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3-777bb4.svg?logo=php&logoColor=white" alt="PHP 8.4">
</div>
<div align="center">
  <a href="README.md"><img src="https://img.shields.io/badge/lang-English-red.svg" alt="English"></a>
  <a href="README.fa.md"><img src="https://img.shields.io/badge/lang-فارسی-green.svg" alt="فارسی"></a>
</div>

---

# PMMIS-Shop-Lite

> ⚠️ **برای مشاهده مستندات به زبان فارسی، به [README.fa.md](README.fa.md) مراجعه کنید.**

A high-performance, budget-friendly, and SEO-native e-commerce platform carefully tailored to run blazingly fast on standard PHP shared hosting environments while retaining absolute core enterprise features.
---

## 🚀 Core Philosophy & Architecture

Most enterprise e-commerce platforms suffer from bloated architectures that demand expensive dedicated servers or cloud instances. **PMMIS-Shop-Lite** breaks this paradigm. It is engineered to deliver sub-50ms page loads, immaculate Google Core Web Vitals, and robust concurrency handling—all while operating within the tight resource boundaries of a standard, low-cost shared hosting account (e.g., 2-core CPU, limited RAM, and a 5GB disk cap under CloudLinux environments).

By substituting heavy third-party packages with hyper-optimized native mechanisms, optimizing state synchronization frequencies, and utilizing aggressive caching layers, this platform achieves elite enterprise efficiency on consumer-grade infrastructure.

---

## 🛠 Tech Stack & Core Optimization Strategies

### 1. Infrastructure & Security Edge
*   **Cloudflare Free Tier Proxy:** Serves as the primary line of defense and acceleration. Configured with aggressive edge page rules (`Cache Everything`) for guest users and Brotli compression to offload near-100% of static asset bandwidth and HTML distribution away from the origin server's CPU.
*   **S3-Compatible Object Storage:** Powered by the lightweight Flysystem-S3 driver. Images and media are processed and streamed directly to remote storage via ephemeral memory streams, completely bypassing the local disk to preserve the strict 5GB hosting storage cap.
*   **Native Secure Headers & Anti-Bot Middleware:** A zero-dependency, lightweight global middleware layer injecting strict security headers (CSP, HSTS). Includes session-based form honeypots that intercept and abort spam registrations or brute-force requests at the controller phase before any database or heavy framework footprints are instantiated.

### 2. Backend & Frontend Core
*   **Laravel Framework (Tuned Lifecycle):** Operates under permanent production source caching (`config:cache`, `route:cache`, `view:cache`, `event:cache`). Unused service providers are stripped from the bootstrap sequence, driving the initial framework boot time down below 30ms.
*   **MySQL / MariaDB Optimization:** Relational tables handle standard core business logic, while dynamic, flexible product attributes are maintained inside native `JSON` columns. Speed is guaranteed through Virtual Generated Columns mapped to appropriate B-tree indexes, alongside pessimistic database locking (`lockForUpdate`) on critical cart/checkout transactions to completely prevent multi-user race conditions.
*   **Filament v5 (Shared-Hosting Resource Capped):** A premium administrative panel configured specifically to prevent server RAM exhaustion. Includes a disabled global search shortcut (eliminating accidental heavy database lookups), forced lazy-loaded dashboard widgets, and strict low-threshold pagination caps (10-15 records max per table query).
*   **Low-Frequency Livewire v3:** All automated state polls (`wire:poll`) are strictly banned. Form elements utilize the `.blur` modifier instead of `.live`, ensuring that state synchronization requests only fire when a user moves out of an input field, keeping simultaneous CPU-bound PHP processes at near-zero.
*   **Server-Side Blade Engine & Tailwind CSS v4:** 100% server-side rendered (SSR) flat Blade layouts ensure pristine search engine indexing. Assets are entirely pre-compiled via Vite in the local development environment and uploaded as static files; the production shared host never runs Node.js or resource-intensive building utilities.

### 3. Hyper-Optimized System Inclusions
*   **Native Multi-Lingual Slugs:** Replaced bloated slug packages with a clean, dedicated Model Observer utilizing `Str::slug()`. Uses a lightweight, customized character mapping routine for perfect Persian/English SEO slugs during the model's `saving` lifecycle.
*   **Spatie Translatable JSON Hydration:** Eliminates heavy, relational `translation` tables and resource-expensive `JOIN` queries by storing multi-lingual localized values directly inside the main model's `JSON` column. Eloquent queries selectively fetch only mandatory columns to keep serialized array RAM footprints minimal.
*   **Multi-Currency Engine with Dynamic Mutators:** Base pricing is stored strictly in USD. A localized Eloquent Accessor performs microsecond-level CPU mathematical multiplication on-the-fly to output values in Tomans/Rials based on a cached conversion rate. Exchange rates update every 6 hours via an infinite cache key, using targeted Cache Tags to flush only pricing-dependent frontend fragments instead of full-page cold boots.
*   **Constrained Image Ingestion:** Filament file uploads are strictly limited to a maximum of 2MB. Runtime image encoding is processed via Intervention Image using native WebP targets capped at 75% quality, ensuring image manipulation memory buffers never clip the hosting provider's 128MB/256MB PHP memory limits.
*   **Laravel Scout Database Full-Text Search:** Utilizes the lightweight `database` driver paired with native MySQL `FULLTEXT` indexing on core product text columns, delivering instant, precise search results without the heavy RAM overhead of standalone engines like Elasticsearch.
*   **Laravel Breeze & Lightweight SMS Authenticator:** Secure authentication uses traditional session-based rate-limiting (`ThrottleLogins`) to mitigate brute-force vectors. OTP verification completely bypasses heavy notification drivers, invoking local SMS Webhook REST APIs directly through Laravel’s cURL-wrapped HTTP Client inside a lightweight mini-service class.
*   **Full-Page Caching with Target Flushing:** Fully rendered guest pages are written to disk as static HTML via `spatie/laravel-responsecache` (<50ms delivery). Dedicated Eloquent Model Observers hook into data modifications to instantly and selectively flush only the exact invalid pages, maximizing high-traffic survival.
*   **Incremental Episodic Queues:** Employs the `database` queue driver controlled via a specialized system Cron Job executing `queue:work --once --max-jobs=10 --time-limit=30` every minute. It processes a small batch of queued tasks (e.g., OTP codes, receipts) and gracefully terminates before CloudLinux's watchdog flags the process for excessive resource consumption.
*   **Dynamic XML Sitemap Streamer:** Prevents memory allocation crashes by leveraging Eloquent's `cursor()` generator method to stream thousands of dynamic category and product sitemap entries line-by-line (Lazy Streaming), wrapped in an aggressive ResponseCache loop to shield the application from web crawlers.
*   **Output Minification & Log Rotation:** A post-render global middleware uses optimized regex to strip inline spaces and comments from the raw rendered Blade views on-the-fly, reducing payload sizes up to 30%. System log rotation enforces a strict daily limit (`max_files => 5`), guaranteeing that bloated logs never trigger disk-full file I/O locks.

### 4. Client-Side Mechanics
*   **SPA Mode via Livewire `wire:navigate`:** Provides an instantaneous Single-Page Application feel for the public storefront by transforming link clicks into fetch operations. Includes a custom global JavaScript interceptor to gracefully handle session or token timeouts (e.g., 419 Page Expired errors) by falling back to a soft full-page reload when needed.
*   **Alpine.js Micro-Interactions:** Manages mobile navigation drawers, dropdowns, and micro-transitions entirely client-side with zero additional asset footprint, as it loads implicitly with the Livewire 3 core.
*   **Zero-Shift (CLS) Asset Rendering:** Completely eliminates icon-font files by utilizing raw, reusable inline SVG Blade components. Preloaded local, high-compression WOFF2 fonts coupled with `font-display: swap` CSS directives guarantee perfect visual stability and maximum Google Lighthouse performance scores.

---

## 📅 Automated Task Management (Cron Schedule)

The platform consolidates all automation into a single system cron configuration (`* * * * * php artisan schedule:run`). The core scheduler manages:
*   **Every Minute:** Incremental database queue processing with strict resource limits.
*   **Hourly:** The `livewire:clean-temporary-uploads` command to instantly wipe abandoned multi-part file chunks, ensuring temporary admin uploads never fill up the local disk.
*   **Every 6 Hours:** Fetching and caching current exchange rates.
*   **Nightly:** Lightweight, database-only backups (`spatie/laravel-backup` with asset exclusions) zipped and pushed directly to remote S3 storage, keeping the host disk untouched.

## License

This project is licensed under the terms of the **PMMIS Source-Available License**. 
It is provided strictly as a portfolio piece for performance evaluation and local testing. 
Commercial use or deployment to public production servers is strictly prohibited. 
See the [LICENSE](LICENSE) file for full details.