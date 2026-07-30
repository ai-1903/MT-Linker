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
 * MT-Linker Apply — 申请页面（无需权限）
 * ====================================
 * 短代码：[mtlinker-apply]
 */

// ---- 加载核心库 ---------------------------------------------------------
if (file_exists(__DIR__ . '/../func/core.php')) {
    require_once __DIR__ . '/../func/core.php';
}
if (file_exists(__DIR__ . '/../func/render.php')) {
    require_once __DIR__ . '/../func/render.php';
}

// ---- 加载配置 -----------------------------------------------------------
$config = mtlinker_load_config();

// ---- 处理表单提交 -------------------------------------------------------
$submitted = false;
$submitError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mtl_apply_submit'])) {
    // 服务端校验
    $name = trim($_POST['name'] ?? '');
    $des  = trim($_POST['des']  ?? '');
    $link = trim($_POST['link'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $type = intval($_POST['type'] ?? 0);

    $errors = [];
    if ($name === '')  $errors[] = '站点名称不能为空';
    if ($des === '')   $errors[] = '站点描述不能为空';
    if ($link === '')  $errors[] = '站点链接不能为空';
    if ($icon === '')  $errors[] = '站点图标不能为空';

    if (empty($errors)) {
        $db = mtlinker_get_db();
        if ($db) {
            try {
                $stmt = $db->prepare(
                    "INSERT INTO Linker (Name, Des, type, Link, icon, color, status) VALUES (?, ?, ?, ?, ?, ?, 0)"
                );
                $stmt->execute([$name, $des, $type, $link, $icon, $color]);
                $submitted = true;
            } catch (PDOException $e) {
                $submitError = '写入数据库失败：' . $e->getMessage();
            }
        } else {
            $submitError = '数据库连接失败，请检查 data/ 目录是否可写';
        }
    } else {
        $submitError = implode('；', $errors);
    }
}

?>
<div class="mt-linker-page-wrapper">
    <?php if ($submitted): ?>
        <div class="mtl-success-message">
            <h1 class="mtl-success-title">✓ 提交成功</h1>
            <p style="margin-bottom: 24px; color: rgba(var(--adt-colorBoard-Label) / var(--adt-colorAlpha-50));">
                您的申请已提交，等待管理员审核
            </p>
            <a href="javascript:history.back();" class="mtl-success-button">返回</a>
        </div>
    <?php else: ?>
        <?php if ($submitError): ?>
            <div class="mtl-error-banner">
                <strong>提交失败：</strong><?php echo esc_html($submitError); ?>
            </div>
        <?php endif; ?>

        <h2 class="mtl-section-title" style="margin-bottom: 32px; text-align: center;">申请友链</h2>
        <?php require __DIR__ . '/../func/tpl-apply-form.php'; ?>
    <?php endif; ?>
</div>
