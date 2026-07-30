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

// ---- 加载配置 -----------------------------------------------------------
$config = mtlinker_load_config();

// ---- 处理表单提交 -------------------------------------------------------
$submitted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mtl_apply_submit'])) {
    $db = mtlinker_get_db();

    if ($db) {
        $stmt = $db->prepare("INSERT INTO Linker (Name, Des, type, Link, icon, color, status) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([
            $_POST['name'],
            $_POST['des'],
            intval($_POST['type']),
            $_POST['link'],
            $_POST['icon'],
            $_POST['color'],
        ]);

        $submitted = true;
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
            <a href="javascript:history.back()" class="mtl-success-button">返回上一页</a>
        </div>
    <?php else: ?>
        <div class="mtl-apply-container">
            <!-- Preview Card -->
            <div class="mtl-apply-preview">
                <a href="https://apple.com.cn"
                   class="mtl-card"
                   id="previewCard"
                   target="_blank"
                   rel="noopener noreferrer"
                   style="background: linear-gradient(135deg, rgba(255, 128, 64, 0.8) 0%, var(--card-bg) 50%); max-width: 380px;">

                    <div class="mtl-card-header">
                        <img src="https://www.apple.com.cn/ac/structured-data/images/knowledge_graph_logo.png?202410141441"
                             alt="Preview"
                             class="mtl-card-icon"
                             id="previewIcon">

                        <div class="mtl-card-info">
                            <h3 class="mtl-card-name" id="previewName">示例站点</h3>
                            <p class="mtl-card-des" id="previewDes">这是一个链接卡片的预览示例</p>
                        </div>

                        <div class="mtl-card-ping">
                            <span class="mtl-ping-dot status-ok"></span>
                            <span>42ms</span>
                        </div>
                    </div>

                    <div class="mtl-card-divider"></div>

                    <div class="mtl-card-footer">
                        <div class="mtl-card-status">
                            <span class="mtl-ping-dot status-ok"></span>
                            <span>连通正常</span>
                        </div>
                        <div class="mtl-card-timestamp">刚刚</div>
                    </div>
                </a>
            </div>

            <!-- Form -->
            <form class="mtl-apply-form" method="POST" id="applyForm">
                <h2 style="margin-bottom: 24px;">申请友链</h2>

                <div class="mtl-form-group">
                    <label class="mtl-form-label" for="name">站点名称 *</label>
                    <input type="text"
                           class="mtl-form-input"
                           id="name"
                           name="name"
                           placeholder="请输入站点名称"
                           required>
                    <div class="mtl-form-hint"></div>
                </div>

                <div class="mtl-form-group">
                    <label class="mtl-form-label" for="des">站点描述 *</label>
                    <textarea class="mtl-form-textarea"
                              id="des"
                              name="des"
                              rows="3"
                              placeholder="请简要描述您的站点"
                              required></textarea>
                    <div class="mtl-form-hint"></div>
                </div>

                <div class="mtl-form-group">
                    <label class="mtl-form-label" for="link">站点链接 *</label>
                    <input type="url"
                           class="mtl-form-input"
                           id="link"
                           name="link"
                           placeholder="https://example.com"
                           required>
                    <div class="mtl-form-hint"></div>
                </div>

                <div class="mtl-form-group">
                    <label class="mtl-form-label" for="icon">站点图标 *</label>
                    <input type="url"
                           class="mtl-form-input"
                           id="icon"
                           name="icon"
                           placeholder="https://example.com/icon.png"
                           required>
                    <div class="mtl-form-hint"></div>
                </div>

                <div class="mtl-form-group">
                    <label class="mtl-form-label" for="type">站点类别 *</label>
                    <select class="mtl-form-select" id="type" name="type" required>
                        <option value="0">本星域</option>
                        <option value="1" selected>联通百界</option>
                        <option value="3">泛星域</option>
                    </select>
                </div>

                <div class="mtl-form-group">
                    <label class="mtl-form-label">卡片颜色 (RGB<?php echo ($config['incheck_colorAlpha'] ?? '1') === '1' ? 'A' : ''; ?>) *</label>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="number" class="mtl-form-input" id="colorR" name="colorR" placeholder="R" min="0" max="255" value="255" required style="flex: 1;">
                        <input type="number" class="mtl-form-input" id="colorG" name="colorG" placeholder="G" min="0" max="255" value="128" required style="flex: 1;">
                        <input type="number" class="mtl-form-input" id="colorB" name="colorB" placeholder="B" min="0" max="255" value="64" required style="flex: 1;">
                        <?php if (($config['incheck_colorAlpha'] ?? '1') === '1'): ?>
                        <span>/</span>
                        <input type="number" class="mtl-form-input" id="colorA" name="colorA" placeholder="A" min="0" max="1" step="0.1" value="0.8" required style="flex: 1;">
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="color" id="colorFinal">
                    <div class="mtl-form-hint"></div>
                </div>

                <button type="submit" class="mtl-form-submit" id="submitBtn" name="mtl_apply_submit" disabled>提交申请</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<script src="<?php echo defined('MT_LINKER_BASE_URL') ? MT_LINKER_BASE_URL : ''; ?>/js/inCheck.js"></script>
<script>
const config = {
    focusHTTPS: <?php echo json_encode(($config['incheck_focusHTTPS'] ?? '1') === '1'); ?>,
    checkDomain: <?php echo json_encode(($config['incheck_DOMAIN'] ?? '1') === '1'); ?>,
    showAlpha: <?php echo json_encode(($config['incheck_colorAlpha'] ?? '1') === '1'); ?>
};

let isValid = { name: false, des: false, link: false, icon: false, color: false };

function updateSubmitButton() {
    const allValid = Object.values(isValid).every(v => v);
    document.getElementById('submitBtn').disabled = !allValid;
}

function updatePreview() {
    document.getElementById('previewName').textContent = document.getElementById('name').value || '示例站点';
    document.getElementById('previewDes').textContent = document.getElementById('des').value || '这是一个链接卡片的预览示例';
    document.getElementById('previewIcon').src = document.getElementById('icon').value || 'https://www.apple.com.cn/ac/structured-data/images/knowledge_graph_logo.png?202410141441';

    const r = document.getElementById('colorR').value || 255;
    const g = document.getElementById('colorG').value || 128;
    const b = document.getElementById('colorB').value || 64;
    const a = config.showAlpha ? (document.getElementById('colorA')?.value || 0.8) : 1;

    document.getElementById('previewCard').style.background = `linear-gradient(135deg, rgba(${r}, ${g}, ${b}, ${a}) 0%, var(--card-bg) 50%)`;
    document.getElementById('colorFinal').value = `${r} ${g} ${b} / ${a}`;
}

document.getElementById('name').addEventListener('blur', function() {
    const result = window.MTLinkerValidation.validateName(this.value);
    window.MTLinkerValidation.applyValidationUI(this, result);
    isValid.name = result.valid;
    updateSubmitButton();
});

document.getElementById('des').addEventListener('blur', function() {
    const result = window.MTLinkerValidation.validateDes(this.value);
    window.MTLinkerValidation.applyValidationUI(this, result);
    isValid.des = result.valid;
    updateSubmitButton();
});

document.getElementById('link').addEventListener('blur', function() {
    const result = window.MTLinkerValidation.validateLink(this.value, config);
    window.MTLinkerValidation.applyValidationUI(this, result);
    isValid.link = result.valid;
    updateSubmitButton();
});

document.getElementById('icon').addEventListener('blur', function() {
    const result = window.MTLinkerValidation.validateIcon(this.value);
    window.MTLinkerValidation.applyValidationUI(this, result);
    isValid.icon = result.valid;
    updateSubmitButton();
});

['colorR', 'colorG', 'colorB', 'colorA'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('input', function() {
            const r = document.getElementById('colorR').value;
            const g = document.getElementById('colorG').value;
            const b = document.getElementById('colorB').value;
            const a = config.showAlpha ? (document.getElementById('colorA')?.value || '1') : '1';

            const result = window.MTLinkerValidation.validateColor(r, g, b, a, config);
            isValid.color = result.valid;

            if (!result.valid) {
                el.classList.add('warning');
            } else {
                ['colorR', 'colorG', 'colorB', 'colorA'].forEach(cid => {
                    const cel = document.getElementById(cid);
                    if (cel) cel.classList.remove('warning');
                });
            }

            updateSubmitButton();
            updatePreview();
        });
    }
});

['name', 'des', 'icon'].forEach(id => {
    document.getElementById(id).addEventListener('input', updatePreview);
});

// 初始验证
isValid.color = true;
updatePreview();
</script>
