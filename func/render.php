<?php
/**
 * Copyright (c) 2026 iCerya (icerya.com). All Rights Reserved.
 *
 * Released under the MIT License.
 * See LICENSE file in the project root for full license text.
 *
 * @package   MT-Linker
 * @author    iCerya
 * @link      https://icerya.com
 *
 * 共享渲染函数 — 同时被 connector.php 和 wp_connector.php 加载
 * ====================================
 */

// ---------------------------------------------------------------------------
// WordPress 兼容层：独立 PHP 环境下提供 esc_html / esc_url polyfill
// ---------------------------------------------------------------------------
if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_url')) {
    function esc_url($url, $protocols = null, $_context = 'display') {
        $url = (string) $url;
        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return '';
        }
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

// ---------------------------------------------------------------------------
// HTML 渲染函数
// ---------------------------------------------------------------------------
if (!function_exists('mt_linker_render_html')) {
    function mt_linker_render_html($data, $themeMode, $baseUrl) {
        ob_start();
        ?>
        <div class="mt-linker-page-wrapper">
            <!-- MT-Linker content will be rendered here -->
        </div>
        <?php
        return ob_get_clean();
    }
}
