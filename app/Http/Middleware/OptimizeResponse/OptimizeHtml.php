<?php

namespace App\Http\Middleware\OptimizeResponse;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OptimizeHtml
{
    protected array $preserveTags = ['pre', 'code', 'textarea', 'script', 'style'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof StreamedResponse) {
            return $response;
        }

        if ($this->shouldOptimize($request, $response)) {
            $response = $this->optimizeHtml($response);
        }

        return $response;
    }

    protected function shouldOptimize(Request $request, Response $response): bool
    {
        if (config('app.debug')) {
            return false;
        }

        if (!$this->isHtmlResponse($response)) {
            return false;
        }

        if ($request->hasHeader('X-Livewire') || $request->ajax() || $request->wantsJson()) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        return true;
    }

    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type');
        
        if (!$contentType) {
            return false;
        }

        return str_contains($contentType, 'text/html') || 
               str_contains($contentType, 'application/xhtml+xml');
    }

    protected function optimizeHtml(Response $response): Response
    {
        $content = $response->getContent();
        
        if (empty($content)) {
            return $response;
        }

        $compressed = $this->compressHtmlSmart($content);
        
        if (!empty($compressed)) {
            $response->setContent($compressed);
            $response->headers->set('X-HTML-Optimized', '1.0');
        }

        return $response;
    }

    protected function compressHtmlSmart(string $html): string
    {
        $placeholders = [];
        $protectedHtml = $this->protectPreserveTags($html, $placeholders);
        $compressed = $this->compressHtml($protectedHtml);
        
        return $this->restorePreserveTags($compressed, $placeholders);
    }

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

    protected function restorePreserveTags(string $html, array $placeholders): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $html);
    }

    protected function compressHtml(string $html): string
    {
        $result = $html;

        // Remove HTML comments (if enabled)
        if (config('app.remove_html_comments', false)) {
            $result = preg_replace('/<!--(?!\[if\s).*?-->/s', '', $result) ?? $html;
            $result = preg_replace('/^\s*[\r\n]+/m', '', $result) ?? $html;
        }

        // Minify spaces between tags
        $result = preg_replace('/\>[^\S ]+/s', '>', $result) ?? $html;
        $result = preg_replace('/[^\S ]+\</s', '<', $result) ?? $html;
        $result = preg_replace('/>\s+</', '><', $result) ?? $html;
        $result = preg_replace('/(\s)+/s', '\\1', $result) ?? $html;
        $result = preg_replace('/^[\s]+|[\s]+$/m', '', $result) ?? $html;
        $result = preg_replace('/^\s*[\r\n]+/m', '', $result) ?? $html;

        // Remove UTF-8 BOM
        $result = $this->removeUtf8Bom($result);
        
        return trim($result);
    }

    protected function removeUtf8Bom(string $text): string
    {
        $bom = pack('H*', 'EFBBBF');
        return preg_replace("/^{$bom}/", '', $text) ?? $text;
    }
}