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
 * MT-Linker Dashboard — 仪表板（需管理员权限）
 * ====================================
 * 短代码：[mtlinker-dash]
 */

// ---- 二次权限验证（防直接访问）------------------------------------------
if (!function_exists('mtlinker_verify_auth')) {
    die('Access Denied');
}

if (!mtlinker_verify_auth()) {
    die('Access Denied: Admin privileges required.');
}

// ---- 加载核心库 ---------------------------------------------------------
require_once __DIR__ . '/func/core.php';

?>

<div class="mtlinker-dashboard">
    <h1>MT-Linker Dashboard</h1>
    <p>仪表板内容将在下一轮指令中实现。</p>
</div>
