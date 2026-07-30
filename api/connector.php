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
 * MT-Linker — 独立 PHP 连接器
 * ====================================
 * 将此文件包含在任意 PHP 页面中即可渲染 MT-Linker 界面。
 *
 * 用法：
 *   <?php include 'path/to/MT-Linker/func/connector.php'; ?>
 *
 * 高级（手动指定资源 URL）：
 *   <?php
 *   define('MT_LINKER_BASE_URL', '/my-assets/MT-Linker');
 *   include 'path/to/MT-Linker/func/connector.php';
 *   ?>
 *
 * 注意：
 *   - 本文件会内联输出 CSS 和必要的 <script> 标签
 *   - 如需最佳性能，建议在 <head> 中引入或在调用前定义 MT_LINKER_BASE_URL
 *   - 同一页面多次 include 仅会执行一次（通过 MT_LINKER_LOADED 守卫）
 */

// ---- 防重复加载 ---------------------------------------------------------
if (defined('MT_LINKER_LOADED')) {
    return;
}
define('MT_LINKER_LOADED', true);

// ---- 基础路径 -----------------------------------------------------------
define('MT_LINKER_BASE', dirname(__DIR__));

// ---- 加载核心库和共享渲染函数 -------------------------------------------
require_once MT_LINKER_BASE . '/func/core.php';
require_once MT_LINKER_BASE . '/func/render.php';

// ---- 加载数据配置 -------------------------------------------------------
$mt_json_path = MT_LINKER_BASE . '/data/mt-linker.json';
$mt_data = file_exists($mt_json_path)
    ? json_decode(file_get_contents($mt_json_path), true)
    : [];

// ---- 初始化数据库 -------------------------------------------------------
mtlinker_init_database();

// ---- 主题检测（Cookie 驱动，与 WordPress 主题兼容）----------------------
$themeMode = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// ---- 资源 URL 自动检测 -------------------------------------------------
if (!defined('MT_LINKER_BASE_URL')) {
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $mtPath  = str_replace('\\', '/', MT_LINKER_BASE);
    $baseUrl = str_replace($docRoot, '', $mtPath);
    define('MT_LINKER_BASE_URL', $baseUrl);
}

// ---- 内联 CSS（零配置，即插即用）----------------------------------------
$strip_css_comments = function($css) {
    return preg_replace('/\/\*.*?\*\//s', '', $css);
};

// 调色盘变量（必须最先加载）
$colorBoardCss = @file_get_contents(MT_LINKER_BASE . '/css/color-board.css');
if ($colorBoardCss) {
    echo '<style id="mt-linker-color-board">' . $strip_css_comments($colorBoardCss) . '</style>';
}

// 缺失的 WP/主题变量兜底（使独立模式不依赖外部主题）
echo '<style id="mt-linker-fallback">
:root {
    --card-bg: rgba(255,255,255,0.72);
    --text-secondary: rgba(var(--adt-colorBoard-Label), 0.5);
    --color-blue: #007AFF;
    --color-blue-rgb: 0 122 255;
    --nav-height: 0px;
}
[data-color-mode="dark"] {
    --card-bg: rgba(30,30,32,0.85);
    --text-secondary: rgba(var(--adt-colorBoard-Label), 0.55);
}
</style>';

// 主 UI 样式
$mtLinkerCss = @file_get_contents(MT_LINKER_BASE . '/css/mt-linker.css');
if ($mtLinkerCss) {
    echo '<style id="mt-linker-ui">' . $strip_css_comments($mtLinkerCss) . '</style>';
}

// ---- Iconify 图标库 ----------------------------------------------------
echo '<script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js" defer></script>';

// ---- 渲染界面 -----------------------------------------------------------
echo mt_linker_render_html($mt_data, $themeMode, MT_LINKER_BASE_URL);
