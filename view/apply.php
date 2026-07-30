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
            <a href="?page=linker" class="mtl-success-button">查看百界门径</a>
        </div>
    <?php else: ?>
        <?php if ($submitError): ?>
            <div class="mtl-error-banner">
                <strong>提交失败：</strong><?php echo esc_html($submitError); ?>
            </div>
        <?php endif; ?>

        <div class="mtl-apply-container">
            <!-- 表单（左侧，最大 540px，垂直居中） -->
            <form class="mtl-apply-form" method="POST" id="applyForm" novalidate>
                <h2 style="margin-bottom: 24px;">申请友链</h2>

                <div class="mtl-form-group">
                    <label class="mtl-form-label" for="name">站点名称 *</label>
                    <input type="text"
                           class="mtl-form-input"
                           id="name"
                           name="name"
                           placeholder="请输入站点名称"
                           value="<?php echo esc_html($_POST['name'] ?? ''); ?>"
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
                              required><?php echo esc_html($_POST['des'] ?? ''); ?></textarea>
                    <div class="mtl-form-hint"></div>
                </div>

                <div class="mtl-form-group">
                    <label class="mtl-form-label" for="link">站点链接 *</label>
                    <div class="mtl-input-row">
                        <input type="url"
                               class="mtl-form-input"
                               id="link"
                               name="link"
                               placeholder="https://example.com"
                               value="<?php echo esc_html($_POST['link'] ?? ''); ?>"
                               required>
                        <button type="button" class="mtl-ping-button" id="pingLinkBtn" onclick="pingLink()">
                            <iconify-icon icon="mingcute:signal-line"></iconify-icon>
                            检测
                        </button>
                    </div>
                    <div class="mtl-form-hint"></div>
                    <div class="mtl-ping-result" id="pingResult" style="display: none;"></div>
                </div>

                <div class="mtl-form-group">
                    <label class="mtl-form-label" for="icon">站点图标 *</label>
                    <input type="url"
                           class="mtl-form-input"
                           id="icon"
                           name="icon"
                           placeholder="https://example.com/icon.png"
                           value="<?php echo esc_html($_POST['icon'] ?? ''); ?>"
                           required>
                    <div class="mtl-form-hint"></div>
                </div>

                <div class="mtl-form-group">
                    <label class="mtl-form-label" for="type">站点类别 *</label>
                    <select class="mtl-form-select" id="type" name="type" required>
                        <option value="1" selected>联通百界</option>
                    </select>
                </div>

                <div class="mtl-form-group">
                    <label class="mtl-form-label">卡片颜色 (RGB<?php echo ($config['incheck_colorAlpha'] ?? '1') === '1' ? 'A' : ''; ?>) *</label>
                    <div class="mtl-color-row">
                        <input type="number" class="mtl-form-input" id="colorR" name="colorR" placeholder="R" min="0" max="255" value="0" required>
                        <input type="number" class="mtl-form-input" id="colorG" name="colorG" placeholder="G" min="0" max="255" value="199" required>
                        <input type="number" class="mtl-form-input" id="colorB" name="colorB" placeholder="B" min="0" max="255" value="190" required>
                        <?php if (($config['incheck_colorAlpha'] ?? '1') === '1'): ?>
                        <span class="mtl-color-sep">/</span>
                        <input type="number" class="mtl-form-input" id="colorA" name="colorA" placeholder="A" min="0" max="1" step="0.01" value="1" required>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="color" id="colorFinal" value="0 199 190 / 1">
                    <div class="mtl-form-hint" id="colorHint"></div>
                </div>

                <button type="submit" class="mtl-form-submit" id="submitBtn" name="mtl_apply_submit">提交申请</button>
            </form>

            <!-- 预览卡片（右侧，min-width 320px） -->
            <div class="mtl-apply-preview">
                <a href="https://apple.com.cn"
                   class="mtl-card"
                   id="previewCard"
                   target="_blank"
                   rel="noopener noreferrer"
                   style="--mtl-r: 255; --mtl-g: 128; --mtl-b: 64; --mtl-a: 0.8; --mtl-icon-url: url(https://www.apple.com.cn/ac/structured-data/images/knowledge_graph_logo.png?202410141441);">

                    <div class="mtl-card-body">
                        <div class="mtl-card-info">
                            <h3 class="mtl-card-name" id="previewName">示例站点</h3>
                            <p class="mtl-card-des" id="previewDes">这是一个链接卡片的预览示例</p>
                        </div>

                        <div class="mtl-card-ping">
                            <span class="mtl-ping-dot status-ok"></span>
                            <span id="previewPingTime">42ms</span>
                        </div>
                    </div>

                    <div class="mtl-card-divider"></div>

                    <div class="mtl-card-footer">
                        <div class="mtl-card-status">
                            <span class="mtl-status-text status-ok" id="previewStatusText">· 连通正常</span>
                        </div>
                        <div class="mtl-card-timestamp" id="previewTimestamp">刚刚</div>
                    </div>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="<?php echo defined('MT_LINKER_BASE_URL') ? MT_LINKER_BASE_URL : ''; ?>/js/inCheck.js"></script>
<script>
(function() {
    var config = {
        focusHTTPS: <?php echo json_encode(($config['incheck_focusHTTPS'] ?? '1') === '1'); ?>,
        checkDomain: <?php echo json_encode(($config['incheck_DOMAIN'] ?? '1') === '1'); ?>,
        showAlpha: <?php echo json_encode(($config['incheck_colorAlpha'] ?? '1') === '1'); ?>
    };

    // ---- 预览实时更新 ----

    function updatePreview() {
        var card = document.getElementById('previewCard');

        document.getElementById('previewName').textContent =
            document.getElementById('name').value || '示例站点';

        document.getElementById('previewDes').textContent =
            document.getElementById('des').value || '这是一个链接卡片的预览示例';

        card.href = document.getElementById('link').value || 'https://apple.com.cn';

        var r = document.getElementById('colorR').value || 0;
        var g = document.getElementById('colorG').value || 199;
        var b = document.getElementById('colorB').value || 190;
        var a = config.showAlpha
            ? (document.getElementById('colorA') ? (document.getElementById('colorA').value || 1) : 1)
            : 1;

        card.style.setProperty('--mtl-r', r);
        card.style.setProperty('--mtl-g', g);
        card.style.setProperty('--mtl-b', b);
        card.style.setProperty('--mtl-a', a);

        var iconUrl = document.getElementById('icon').value
            || 'https://www.apple.com.cn/ac/structured-data/images/knowledge_graph_logo.png?202410141441';
        card.style.setProperty('--mtl-icon-url', 'url(' + iconUrl + ')');

        document.getElementById('colorFinal').value = r + ' ' + g + ' ' + b + ' / ' + a;
    }

    // ---- 前端校验（纯视觉提示，不阻塞提交）----

    function applyHint(input, result) {
        if (typeof window.MTLinkerValidation !== 'undefined') {
            window.MTLinkerValidation.applyValidationUI(input, result);
        }
    }

    // name / des / link / icon 实时校验
    var fieldRules = {
        name:  { fn: 'validateName',  extra: null },
        des:   { fn: 'validateDes',   extra: null },
        link:  { fn: 'validateLink',  extra: 'config' },
        icon:  { fn: 'validateIcon',  extra: null }
    };

    Object.keys(fieldRules).forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;

        function runValidation() {
            if (typeof window.MTLinkerValidation === 'undefined') return;
            var rule = fieldRules[id];
            var extra = rule.extra === 'config' ? config : undefined;
            var result = extra !== undefined
                ? window.MTLinkerValidation[rule.fn](el.value, extra)
                : window.MTLinkerValidation[rule.fn](el.value);
            applyHint(el, result);
        }

        el.addEventListener('blur', runValidation);
        el.addEventListener('input', function() {
            clearTimeout(el._vTimer);
            el._vTimer = setTimeout(runValidation, 400);
            updatePreview();
        });
    });

    // 颜色校验
    ['colorR','colorG','colorB','colorA'].forEach(function(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function() {
            if (typeof window.MTLinkerValidation === 'undefined') { updatePreview(); return; }
            var r = document.getElementById('colorR').value;
            var g = document.getElementById('colorG').value;
            var b = document.getElementById('colorB').value;
            var a = config.showAlpha
                ? (document.getElementById('colorA') ? document.getElementById('colorA').value : '1')
                : '1';
            var result = window.MTLinkerValidation.validateColor(r, g, b, a, config);
            ['colorR','colorG','colorB','colorA'].forEach(function(cid) {
                var cel = document.getElementById(cid);
                if (cel) cel.classList.toggle('warning', !result.valid);
            });

            var hintEl = document.getElementById('colorHint');
            if (!result.valid) {
                hintEl.className = 'mtl-form-hint error';
                hintEl.textContent = 'RGB 值应当属于 [0,255]，Alpha 通道应当属于 [0,1]，例如 0 199 190 / 1 是合法的，0 0 0 / 0.6 是合法的。';
            } else {
                hintEl.className = 'mtl-form-hint';
                hintEl.textContent = '';
            }

            updatePreview();
        });
    });

    // ---- Ping 检测 ----

    window.pingLink = async function() {
        var linkInput = document.getElementById('link');
        var url = linkInput.value.trim();
        var resultDiv = document.getElementById('pingResult');
        var btn = document.getElementById('pingLinkBtn');

        if (!url) {
            resultDiv.style.display = 'block';
            resultDiv.className = 'mtl-ping-result error';
            resultDiv.innerHTML = '<iconify-icon icon="mingcute:close-circle-line"></iconify-icon> 请先输入链接';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<iconify-icon icon="mingcute:loading-3-line" class="rotating"></iconify-icon> 检测中';

        resultDiv.style.display = 'block';
        resultDiv.className = 'mtl-ping-result checking';
        resultDiv.innerHTML = '<iconify-icon icon="mingcute:loading-3-line" class="rotating"></iconify-icon> 正在检测...';

        var t0 = performance.now();
        try {
            await fetch(url, { method: 'HEAD', mode: 'no-cors', cache: 'no-cache' });
            var ms = Math.round(performance.now() - t0);
            resultDiv.className = 'mtl-ping-result success';
            resultDiv.innerHTML = '<iconify-icon icon="mingcute:check-circle-line"></iconify-icon> 连接成功 (' + ms + 'ms)';
            document.getElementById('previewPingTime').textContent = ms + 'ms';
            document.getElementById('previewStatusText').className = 'mtl-status-text status-ok';
            document.getElementById('previewStatusText').textContent = '\u00B7 连通正常';
        } catch (e) {
            var ms = Math.round(performance.now() - t0);
            resultDiv.className = 'mtl-ping-result error';
            resultDiv.innerHTML = '<iconify-icon icon="mingcute:close-circle-line"></iconify-icon> 连接失败 (' + ms + 'ms)';
            document.getElementById('previewPingTime').textContent = '超时';
            document.getElementById('previewStatusText').className = 'mtl-status-text status-error';
            document.getElementById('previewStatusText').textContent = '\u00B7 连接失败';
        }

        document.getElementById('previewTimestamp').textContent =
            new Date().toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' });

        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="mingcute:signal-line"></iconify-icon> 检测';
    };

    // ---- 初始渲染 ----
    updatePreview();
})();
</script>
