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
 * MT-Linker Show — 链接展示页面
 * ====================================
 * 短代码：[mtklinker-show]
 */

// ---- 加载核心库 ---------------------------------------------------------
if (file_exists(__DIR__ . '/../func/core.php')) {
    require_once __DIR__ . '/../func/core.php';
}
if (file_exists(__DIR__ . '/../func/render.php')) {
    require_once __DIR__ . '/../func/render.php';
}

// ---- 获取数据库连接 -----------------------------------------------------
$db = mtlinker_get_db();
if (!$db) {
    echo '<div class="mtl-error">数据库连接失败</div>';
    return;
}

// ---- 获取统计信息 -------------------------------------------------------
$stats = [
    'connected' => $db->query("SELECT COUNT(*) FROM Linker WHERE status = 2")->fetchColumn(),
    'smooth' => $db->query("SELECT COUNT(*) FROM Linker WHERE status = 2 AND type IN (0, 1)")->fetchColumn(),
    'oneway' => $db->query("SELECT COUNT(*) FROM Linker WHERE status = 2 AND type = 3")->fetchColumn(),
];

// ---- 按 type 分组获取链接 ----------------------------------------------
$linkersByType = [];
$types = [0, 1, 3];

foreach ($types as $type) {
    $stmt = $db->prepare("SELECT * FROM Linker WHERE type = ? AND status IN (2, 3, 4) ORDER BY status ASC, NO ASC");
    $stmt->execute([$type]);
    $linkersByType[$type] = $stmt->fetchAll();
}

// ---- 获取域名 -----------------------------------------------------------
$domain = $_SERVER['HTTP_HOST'] ?? 'MT-Linker';

// ---- 类型和状态映射 -----------------------------------------------------
$typeNames = [
    0 => ['title' => '本星域', 'desc' => '本星域各网站'],
    1 => ['title' => '联通百界', 'desc' => "以下空间已与 $domain 建立门径"],
    3 => ['title' => '泛星域', 'desc' => "以下空间由 $domain 建立单向门径"],
];

$statusNames = [
    2 => '界域连通',
    3 => '通信失联',
    4 => '注销星域',
];

?>

<div class="mt-linker-page-wrapper">
    <!-- Hero Section -->
    <div class="mtl-hero">
        <h1 class="mtl-title">百界门径</h1>

        <div class="mtl-stats-container">
            <div class="mtl-stat-card">
                <div class="mtl-stat-number"><?php echo $stats['connected']; ?></div>
                <div class="mtl-stat-label">已连通门径</div>
            </div>
            <div class="mtl-stat-card">
                <div class="mtl-stat-number"><?php echo $stats['smooth']; ?></div>
                <div class="mtl-stat-label">银轨通畅</div>
            </div>
            <div class="mtl-stat-card">
                <div class="mtl-stat-number"><?php echo $stats['oneway']; ?></div>
                <div class="mtl-stat-label">单向门径</div>
            </div>
            <div class="mtl-stat-card mtl-check-card">
                <button class="mtl-check-button" id="checkLinksBtn" onclick="checkAllLinks()">
                    <iconify-icon icon="mingcute:refresh-2-line"></iconify-icon>
                    检查连接
                </button>
                <span class="mtl-check-cooldown" id="cooldownText"></span>
            </div>
        </div>
    </div>

    <!-- Links by Type -->
    <?php foreach ($types as $type): ?>
        <?php if (!empty($linkersByType[$type])): ?>
            <div class="mtl-section">
                <h2 class="mtl-section-title"><?php echo esc_html($typeNames[$type]['title']); ?></h2>
                <p class="mtl-section-desc"><?php echo esc_html($typeNames[$type]['desc']); ?></p>

                <?php
                // 按 status 分组
                $byStatus = [];
                foreach ($linkersByType[$type] as $item) {
                    $byStatus[$item['status']][] = $item;
                }
                ?>

                <?php foreach ([2, 3, 4] as $status): ?>
                    <?php if (!empty($byStatus[$status])): ?>
                        <h3 class="mtl-section-subtitle"><?php echo esc_html($statusNames[$status]); ?></h3>
                        <div class="mtl-cards-grid">
                            <?php foreach ($byStatus[$status] as $linker):
                                ?>
                                <a href="<?php echo esc_url($linker['Link']); ?>"
                                   class="mtl-card"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   style="--mtl-color-args: <?php echo esc_html($linker['color'] ?? '128 128 128 / 0.4'); ?>; --mtl-icon-url: url(<?php echo esc_url($linker['icon']); ?>);">

                                    <div class="mtl-card-body">
                                        <div class="mtl-card-info">
                                            <h3 class="mtl-card-name"><?php echo esc_html($linker['Name']); ?></h3>
                                            <p class="mtl-card-des"><?php echo esc_html($linker['Des']); ?></p>
                                        </div>
                                    </div>

                                    <div class="mtl-card-divider"></div>

                                    <div class="mtl-card-footer">
                                        <div class="mtl-card-status">
                                            <span class="mtl-status-text status-ok"><iconify-icon icon="mingcute:check-circle-line"></iconify-icon> --ms 连接成功</span>
                                        </div>
                                        <div class="mtl-card-timestamp">--</div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<script>
// =========================================================================
// Cookie 工具
// =========================================================================
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

function setCookie(name, value, maxAge) {
    document.cookie = `${name}=${value}; path=/; max-age=${maxAge}`;
}

function deleteCookie(name) {
    document.cookie = `${name}=; path=/; max-age=0`;
}

// =========================================================================
// 计时器常量
// =========================================================================
const AUTO_CACHE_TTL  = 1140; // 19 分钟（秒）
const MANUAL_COOLDOWN = 540;  // 9 分钟（秒）

// =========================================================================
// Ping 结果缓存
// =========================================================================
function getPingCache() {
    const raw = getCookie('mtlinker_ping_cache');
    if (!raw) return null;
    try { return JSON.parse(decodeURIComponent(raw)); } catch (e) { return null; }
}

function setPingCache(results) {
    const data = { t: Date.now(), r: results };
    setCookie('mtlinker_ping_cache', encodeURIComponent(JSON.stringify(data)), AUTO_CACHE_TTL);
}

function isAutoCacheValid() {
    const cache = getPingCache();
    if (!cache || !cache.t || !cache.r) return false;
    // 需要上一次是自动检测
    if (getCookie('mtlinker_auto_cooldown')) {
        const cooldown = parseInt(getCookie('mtlinker_auto_cooldown'));
        if (Date.now() < cooldown && cache.r && Object.keys(cache.r).length > 0) {
            return true;
        }
    }
    return false;
}

// =========================================================================
// 渲染单个卡片的 Ping 结果
// =========================================================================
function renderCardStatus(statusText, result) {
    if (result.s) {
        const ms = result.t;
        let colorVar = 'var(--adt-colorBoard-Success)';
        if (ms > 4000) {
            colorVar = 'var(--adt-colorBoard-Caution)';
        } else if (ms > 800) {
            colorVar = 'var(--adt-colorBoard-Warning)';
        }
        statusText.innerHTML = '<iconify-icon icon="mingcute:check-circle-line"></iconify-icon> ' + ms + 'ms 连接成功';
        statusText.style.color = 'rgb(' + colorVar + ')';
    } else {
        statusText.innerHTML = '<iconify-icon icon="mingcute:close-circle-line"></iconify-icon> 连接失败 (' + result.t + 'ms)';
        statusText.style.color = 'rgb(var(--adt-colorBoard-Middle))';
    }
}

function renderCardChecking(statusText) {
    statusText.innerHTML = '<iconify-icon icon="mingcute:loading-3-line" class="rotating"></iconify-icon> 检测中...';
    statusText.style.color = '';
}

// =========================================================================
// 从缓存渲染全部卡片
// =========================================================================
function renderFromCache(cache) {
    const cards = document.querySelectorAll('.mtl-card');
    const cacheTime = new Date(cache.t);
    const timeStr = cacheTime.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' });
    cards.forEach(card => {
        const url = card.getAttribute('href');
        const statusText = card.querySelector('.mtl-status-text');
        if (!statusText) return;
        if (cache.r[url]) {
            renderCardStatus(statusText, cache.r[url]);
        }
    });
    document.querySelectorAll('.mtl-card-timestamp').forEach(el => { el.textContent = timeStr; });
}

// =========================================================================
// 核心：执行批量 Ping 并缓存
// =========================================================================
function runBatchPing(onComplete) {
    const cards = document.querySelectorAll('.mtl-card');
    const results = {};
    let pending = cards.length;

    cards.forEach(card => {
        const url = card.getAttribute('href');
        const statusText = card.querySelector('.mtl-status-text');
        if (!statusText) { pending--; return; }

        renderCardChecking(statusText);

        pingUrl(url).then(result => {
            results[url] = { s: result.success, t: result.time };
            renderCardStatus(statusText, { s: result.success, t: result.time });

            pending--;
            if (pending === 0) {
                setPingCache(results);
                const timeStr = new Date().toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' });
                document.querySelectorAll('.mtl-card-timestamp').forEach(el => { el.textContent = timeStr; });
                if (onComplete) onComplete();
            }
        });
    });
}

// =========================================================================
// 自动检测（页面加载时）
// =========================================================================
function autoPingAll() {
    runBatchPing(function() {
        setCookie('mtlinker_auto_cooldown', (Date.now() + AUTO_CACHE_TTL * 1000).toString(), AUTO_CACHE_TTL);
    });
}

// =========================================================================
// 手动检测（按钮点击）
// =========================================================================
function checkAllLinks() {
    setCookie('mtlinker_cooldown_end', (Date.now() + MANUAL_COOLDOWN * 1000).toString(), MANUAL_COOLDOWN);
    updateManualCooldown();
    runBatchPing();
}

// =========================================================================
// 冷却计时器 UI
// =========================================================================
function updateManualCooldown() {
    const now = Date.now();
    const cooldownEnd = getCookie('mtlinker_cooldown_end');
    const btn = document.getElementById('checkLinksBtn');
    const text = document.getElementById('cooldownText');

    if (cooldownEnd && now < parseInt(cooldownEnd)) {
        const remaining = Math.ceil((parseInt(cooldownEnd) - now) / 1000);
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        btn.disabled = true;
        text.textContent = `冷却中 ${minutes}:${seconds.toString().padStart(2, '0')}`;
        setTimeout(updateManualCooldown, 1000);
    } else {
        btn.disabled = false;
        text.textContent = '';
        deleteCookie('mtlinker_cooldown_end');
    }
}

// =========================================================================
// 单个 URL Ping
// =========================================================================
async function pingUrl(url) {
    const startTime = performance.now();
    try {
        await fetch(url, { method: 'HEAD', mode: 'no-cors', cache: 'no-cache' });
        return { success: true, time: Math.round(performance.now() - startTime) };
    } catch (error) {
        return { success: false, time: Math.round(performance.now() - startTime) };
    }
}

// =========================================================================
// 初始化
// =========================================================================
(function init() {
    updateManualCooldown();
    if (isAutoCacheValid()) {
        renderFromCache(getPingCache());
    } else {
        autoPingAll();
    }
})();
</script>
