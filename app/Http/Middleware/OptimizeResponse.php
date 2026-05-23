<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class OptimizeResponse
{
    /**
     * لیست هدرهای امنیتی
     *
     * @var array<string, string>
     */
    protected array $securityHeaders = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ];

    /**
     * تگ‌هایی که نباید فشرده‌سازی روی محتوای داخل آنها انجام شود
     *
     * @var array<string>
     */
    protected array $preserveTags = ['pre', 'code', 'textarea', 'script', 'style'];

    /**
     * هندل درخواست
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Log::info('OptimizeResponse middleware executed', [
        //     'path' => $request->path(),
        //     'debug' => config('app.debug'),
        //     'is_html' => $this->isHtmlResponse($response)
        // ]);

        if ($response instanceof StreamedResponse) {
            return $this->addSecurityHeaders($response);
        }

        if ($this->shouldOptimize($request, $response)) {
            $response = $this->optimizeHtml($response);
        }

        $this->addSecurityHeaders($response);
        $this->addCacheHeaders($request, $response);

        return $response;
    }

    /**
     * بررسی نیاز به بهینه‌سازی
     */
    protected function shouldOptimize(Request $request, Response $response): bool
    {
        if (config('app.debug')) {
            return false;
        }

        if (!$this->isHtmlResponse($response)) {
            return false;
        }

        if ($request->hasHeader('X-Livewire')) {
            return false;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        return true;
    }

    /**
     * بررسی HTML بودن پاسخ
     */
    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type');
        
        if (!$contentType || !is_string($contentType)) {
            return false;
        }

        return str_contains($contentType, 'text/html') || 
               str_contains($contentType, 'application/xhtml+xml');
    }

    /**
     * بهینه‌سازی HTML
     */
    protected function optimizeHtml(Response $response): Response
    {
        $content = $response->getContent();
        
        if (!is_string($content) || $content === '') {
            return $response;
        }

        $compressed = $this->compressHtmlSmart($content);
        
        if (is_string($compressed) && $compressed !== '') {
            $response->setContent($compressed);
            $response->headers->set('X-HTML-Optimized', '1.0');
        }

        return $response;
    }

    /**
     * فشرده‌سازی هوشمند HTML (حفظ محتوای تگ‌های خاص)
     */
    protected function compressHtmlSmart(string $html): string
    {
        // مرحله 1: حفاظت از محتوای تگ‌های خاص
        $placeholders = [];
        $protectedHtml = $this->protectPreserveTags($html, $placeholders);
        
        // مرحله 2: فشرده‌سازی قسمت‌های محافظت شده
        $compressed = $this->compressHtml($protectedHtml);
        
        // مرحله 3: بازگرداندن محتوای محافظت شده
        $compressed = $this->restorePreserveTags($compressed, $placeholders);
        
        return $compressed;
    }

    /**
     * حفاظت از تگ‌های خاص در برابر فشرده‌سازی
     */
    protected function protectPreserveTags(string $html, array &$placeholders): string
    {
        foreach ($this->preserveTags as $tag) {
            $pattern = '/<' . $tag . '([^>]*)>(.*?)<\/' . $tag . '>/is';
            $html = preg_replace_callback($pattern, function ($matches) use ($tag, &$placeholders) {
                $placeholder = '%%' . $tag . '_' . md5($matches[0]) . '%%';
                $placeholders[$placeholder] = $matches[0];
                return $placeholder;
            }, $html);
        }
        
        return $html;
    }

    /**
     * بازگرداندن تگ‌های حفاظت شده
     */
    protected function restorePreserveTags(string $html, array $placeholders): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $html);
    }

    /**
     * فشرده‌سازی HTML پایه (نسخه اصلاح شده با قابلیت حذف کامنت)
     */
    protected function compressHtml(string $html): string
    {
        $result = $html;

        // ========== حذف کامنت‌های HTML (نسخه اصلاح شده) ==========
        if (config('app.remove_html_comments', false)) {
            // حذف همه کامنت‌های HTML (به جز کامنت‌های شرطی IE)
            $result = preg_replace('/<!--(?!\[if\s).*?-->/s', '', $result);
            if ($result === null) {
                $result = $html;
            }
            
            // حذف خطوط خالی ناشی از حذف کامنت‌ها
            $result = preg_replace('/^\s*[\r\n]+/m', '', $result);
            if ($result === null) {
                $result = $html;
            }
        }

        // حذف فاصله بعد از تگ (بهینه شده)
        $result = preg_replace('/\>[^\S ]+/s', '>', $result);
        if ($result === null) {
            $result = $html;
        }

        // حذف فاصله قبل از تگ (بهینه شده)
        $result = preg_replace('/[^\S ]+\</s', '<', $result);
        if ($result === null) {
            $result = $html;
        }

        // حذف فاصله‌های اضافی بین تگ‌ها
        $result = preg_replace('/>\s+</', '><', $result);
        if ($result === null) {
            $result = $html;
        }

        // تبدیل فاصله‌های تکراری به یک فاصله
        $result = preg_replace('/(\s)+/s', '\\1', $result);
        if ($result === null) {
            $result = $html;
        }

        // حذف فاصله ابتدا و انتهای خطوط
        $result = preg_replace('/^[\s]+|[\s]+$/m', '', $result);
        if ($result === null) {
            $result = $html;
        }

        // حذف خطوط کاملاً خالی
        $result = preg_replace('/^\s*[\r\n]+/m', '', $result);
        if ($result === null) {
            $result = $html;
        }

        // حذف BOM UTF-8
        $result = $this->removeUtf8Bom($result);
        
        return trim($result);
    }

    /**
     * حذف BOM UTF-8
     */
    protected function removeUtf8Bom(string $text): string
    {
        $bom = pack('H*', 'EFBBBF');
        $cleaned = preg_replace("/^{$bom}/", '', $text);
        return $cleaned !== null ? $cleaned : $text;
    }

    /**
     * افزودن هدرهای امنیتی
     */
    protected function addSecurityHeaders(Response $response): Response
    {
        foreach ($this->securityHeaders as $key => $value) {
            if (!$response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        // افزودن هدر X-Powered-By برای نمایش نسخه PHP (اختیاری)
        if (config('app.show_powered_by', false)) {
            $response->headers->set('X-Powered-By', 'Laravel/' . app()->version());
        }

        if (config('app.enable_csp', false)) {
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;"
            );
        }

        if (config('app.enable_hsts', false) && config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }

    /**
     * افزودن هدرهای کش (نسخه اصلاح شده)
     */
    protected function addCacheHeaders(Request $request, Response $response): void
    {
        // برای کاربران لاگین شده کش نکن
        if (Auth::check()) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
            return;
        }

        $path = $request->path();
        
        // کش assets برای یک سال
        if ($this->isStaticAsset($path)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
            $response->headers->set('Pragma', 'public');
        } 
        // کش صفحات عمومی با در نظر گرفتن زبان
        elseif ($response->getStatusCode() === 200 && !str_starts_with($path, 'admin')) {
            // زمان کش بر اساس تنظیمات (پیش‌فرض 5 دقیقه)
            $cacheTime = config('app.cache_ttl', 300);
            $response->headers->set('Cache-Control', "public, max-age={$cacheTime}, must-revalidate");
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + $cacheTime) . ' GMT');
            // مهم: کش بر اساس زبان و رمزگذاری
            $response->headers->set('Vary', 'Accept-Language, Accept-Encoding');
        }
        
        // افزودن هدر Last-Modified بر اساس زمان پاسخ (اختیاری)
        if ($response->getStatusCode() === 200 && !$this->isStaticAsset($path)) {
            $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        }
    }

    /**
     * بررسی فایل استاتیک (نسخه توسعه یافته)
     */
    protected function isStaticAsset(string $path): bool
    {
        $extensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'otf', 'pdf', 'xml', 'json'];
        
        foreach ($extensions as $ext) {
            if (Str::endsWith($path, '.' . $ext)) {
                return true;
            }
        }
        
        return false;
    }
}