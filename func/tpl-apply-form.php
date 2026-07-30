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
 * Apply Form Template — 表单模板（可复用）
 * ====================================
 * 用于 apply.php 和 dash.php 的弹窗加载
 */

// 判断调用环境
$is_dash_mode = $is_dash_mode ?? false;

// 类型选项
$typeOptions = [
    0 => '本星域',
    1 => '联通百界',
    3 => '泛星域'
];

// 状态选项（仅 Dashboard 模式）
$statusOptions = [
    0 => '审核中',
    2 => '界域连通',
    3 => '通信失联',
    4 => '注销星域',
    10 => '已隐藏'
];
?>

<div class="mtl-apply-container">
    <!-- 表单（左侧，最大 540px） -->
    <form class="mtl-apply-form" method="POST" id="applyForm" novalidate>

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
                <?php if ($is_dash_mode): ?>
                    <?php foreach ($typeOptions as $val => $label): ?>
                        <option value="<?php echo $val; ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="1" selected>联通百界</option>
                <?php endif; ?>
            </select>
        </div>

        <?php if ($is_dash_mode): ?>
        <div class="mtl-form-group">
            <label class="mtl-form-label" for="status">站点状态 *</label>
            <select class="mtl-form-select" id="status" name="status" required>
                <?php foreach ($statusOptions as $val => $label): ?>
                    <option value="<?php echo $val; ?>"><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

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

        <button type="submit" class="mtl-form-submit" id="submitBtn" name="mtl_apply_submit">
            <?php echo $is_dash_mode ? '添加链接' : '提交申请'; ?>
        </button>
    </form>

    <!-- 预览卡片（右侧，min-width 320px） -->
    <div class="mtl-apply-preview">
        <a href="https://apple.com.cn"
           class="mtl-card"
           id="previewCard"
           target="_blank"
           rel="noopener noreferrer"
           style="--mtl-color-args: 255 128 64 / 0.8; --mtl-icon-url: url(https://www.apple.com.cn/ac/structured-data/images/knowledge_graph_logo.png?202410141441);">

            <div class="mtl-card-body">
                <div class="mtl-card-info">
                    <h3 class="mtl-card-name" id="previewName">Apple</h3>
                    <p class="mtl-card-des" id="previewDes">这是一个链接卡片的预览示例</p>
                </div>
            </div>

            <div class="mtl-card-divider"></div>

            <div class="mtl-card-footer">
                <div class="mtl-card-status">
                    <span class="mtl-status-text status-ok" id="previewStatusText"><iconify-icon icon="mingcute:check-circle-line"></iconify-icon> 准备连接</span>
                </div>
                <div class="mtl-card-timestamp" id="previewTimestamp">刚刚</div>
            </div>
        </a>
    </div>
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
            document.getElementById('name').value || 'Apple';

        document.getElementById('previewDes').textContent =
            document.getElementById('des').value || '这是一个链接卡片的预览示例';

        card.href = document.getElementById('link').value || 'https://apple.com.cn';

        var r = document.getElementById('colorR').value || 0;
        var g = document.getElementById('colorG').value || 199;
        var b = document.getElementById('colorB').value || 190;
        var a = config.showAlpha
            ? (document.getElementById('colorA') ? (document.getElementById('colorA').value || 1) : 1)
            : 1;

        // 合并颜色字符串
        var colorString = r + ' ' + g + ' ' + b + ' / ' + a;
        // 赋予 CSS 变量
        card.style.setProperty('--mtl-color-args', colorString);
        // 赋予隐藏的表单 Input
        document.getElementById('colorFinal').value = colorString;

        var iconUrl = document.getElementById('icon').value
            || 'https://www.apple.com.cn/ac/structured-data/images/knowledge_graph_logo.png?202410141441';
        card.style.setProperty('--mtl-icon-url', 'url(' + iconUrl + ')');
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
        icon:  { fn: 'validateIcon',  extra: 'config' }
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

            // 【新增】：针对 HTTP 不安全协议的专属警告注入
            if (id === 'link') {
                var urlVal = el.value.trim().toLowerCase();
                if (urlVal.startsWith('http://')) {
                    // 添加黄色边框警告状态
                    el.classList.add('warning');

                    // 安全获取紧邻的 hint 容器并支持 HTML 标签（由于需要加粗样式）
                    var rowDiv = el.closest('.mtl-input-row');
                    var hintEl = rowDiv ? rowDiv.nextElementSibling : null;
                    if (hintEl && hintEl.classList.contains('mtl-form-hint')) {
                        hintEl.className = 'mtl-form-hint warning';
                        hintEl.innerHTML = '按照网络安全规范，HTTPS 是现在更安全的一种链接方式。请在配置为 TLS 1.2 或更高的受信任证书之后，按照要求填写 HTTPS 链接。<strong>伪造的 HTTPS 链接可能会被屏蔽处理。</strong>';
                    }
                }
            }
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
                hintEl.textContent = result.msg;
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

            var statusIcon = 'mingcute:check-circle-line';
            var statusColor = 'var(--adt-colorBoard-Success)';
            if (ms > 4000) {
                statusColor = 'var(--adt-colorBoard-Caution)';
            } else if (ms > 800) {
                statusColor = 'var(--adt-colorBoard-Warning)';
            }
            document.getElementById('previewStatusText').innerHTML = '<iconify-icon icon="' + statusIcon + '"></iconify-icon> ' + ms + 'ms 连接成功';
            document.getElementById('previewStatusText').style.color = 'rgb(' + statusColor + ')';
        } catch (e) {
            var ms = Math.round(performance.now() - t0);
            resultDiv.className = 'mtl-ping-result error';
            resultDiv.innerHTML = '<iconify-icon icon="mingcute:close-circle-line"></iconify-icon> 连接失败 (' + ms + 'ms)';
            document.getElementById('previewStatusText').innerHTML = '<iconify-icon icon="mingcute:close-circle-line"></iconify-icon> 连接失败 (' + ms + 'ms)';
            document.getElementById('previewStatusText').style.color = 'rgb(var(--adt-colorBoard-Middle))';
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
