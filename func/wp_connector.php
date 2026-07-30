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
 * MT-Linker — WordPress 连接器
 * ====================================
 * 在你的 WordPress 子主题 functions.php 中加入以下代码即可挂载短代码：
 *
 *   require_once get_stylesheet_directory() . '/AirDesign/MT-Linker/func/wp_connector.php';
 *
 * 之后在任意页面/文章中使用短代码：[mt_linker]
 */

// ---- 安全防范 -----------------------------------------------------------
if (!defined('ABSPATH')) {
    exit;
}

// ---- 加载共享渲染函数 ---------------------------------------------------
require_once __DIR__ . '/render.php';

// ---- 计算资源路径（相对于当前文件）--------------------------------------
$wp_connector_base_dir  = dirname(__DIR__);                                          // MT-Linker/
$wp_connector_base_url  = get_stylesheet_directory_uri() . '/AirDesign/MT-Linker';   // WordPress URI

// =========================================================================
// 1. 样式注册与引入
// =========================================================================
add_action('wp_enqueue_scripts', function () use ($wp_connector_base_url) {

    // ---- 调色盘变量（最先注册以保证优先级）------------------------------
    wp_enqueue_style(
        'adt-color-board',
        $wp_connector_base_url . '/css/color-board.css',
        [],
        '1.0.0'
    );

    // ---- MT-Linker UI 主样式 -------------------------------------------
    wp_enqueue_style(
        'adt-mt-linker-style',
        $wp_connector_base_url . '/css/mt-linker.css',
        ['adt-color-board'],
        '1.0.0'
    );

    // ---- 动态注入 Blocksy 容器宽度适配补丁 -----------------------------
    $blocksy_patch = "
    .mt-linker-page-wrapper {
        margin-top: 40px;
        margin-bottom: 60px;
        width: var(--theme-container-width, calc(100% - 40px));
        max-width: var(--theme-block-max-width, 1200px);
        margin-inline: auto;
        box-sizing: border-box;
    }
    ";
    wp_add_inline_style('adt-mt-linker-style', $blocksy_patch);

    // ---- Iconify 图标库（异步加载，不阻塞首屏）--------------------------
    wp_enqueue_script(
        'iconify-icon',
        'https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js',
        [],
        '2.1.0',
        true
    );
});

// =========================================================================
// 2. 注册短代码 [mt_linker]
// =========================================================================
add_shortcode('mt_linker', function () use ($wp_connector_base_dir, $wp_connector_base_url) {

    // ---- 加载数据配置 ---------------------------------------------------
    $configJsonPath = $wp_connector_base_dir . '/data/mt-linker.json';

    // 兜底：如果原始路径不存在，尝试子主题根目录
    if (!file_exists($configJsonPath)) {
        $configJsonPath = get_stylesheet_directory() . '/mt-linker.json';
    }

    $mtData = file_exists($configJsonPath)
        ? json_decode(file_get_contents($configJsonPath), true)
        : [];

    // ---- 主题检测 -------------------------------------------------------
    $themeMode = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

    // ---- 渲染 -----------------------------------------------------------
    return mt_linker_render_html($mtData, $themeMode, $wp_connector_base_url);
});
