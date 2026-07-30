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
// 冷却计时器（使用 Cookie）
const COOLDOWN_DURATION = 540000; // 9分钟 = 540000毫秒

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

function updateCooldown() {
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

        setTimeout(updateCooldown, 1000);
    } else {
        btn.disabled = false;
        text.textContent = '';
        deleteCookie('mtlinker_cooldown_end');
    }
}

async function pingUrl(url) {
    const startTime = performance.now();

    try {
        const response = await fetch(url, {
            method: 'HEAD',
            mode: 'no-cors',
            cache: 'no-cache',
        });
        const endTime = performance.now();
        return {
            success: true,
            time: Math.round(endTime - startTime),
        };
    } catch (error) {
        const endTime = performance.now();
        return {
            success: false,
            time: Math.round(endTime - startTime),
        };
    }
}

async function checkAllLinks() {
    const now = Date.now();
    setCookie('mtlinker_cooldown_end', (now + COOLDOWN_DURATION).toString(), 540);
    updateCooldown();

    const cards = document.querySelectorAll('.mtl-card');

    cards.forEach(card => {
        const url = card.getAttribute('href');
        const statusText = card.querySelector('.mtl-status-text');
        const timestamp = card.querySelector('.mtl-card-timestamp');

        // 重置为检查中状态
        statusText.innerHTML = '<iconify-icon icon="mingcute:loading-3-line" class="rotating"></iconify-icon> 检测中...';
        statusText.style.color = '';

        // 并发检测，谁测完谁更新
        pingUrl(url).then(result => {
            if (result.success) {
                const ms = result.time;
                let icon = 'mingcute:check-circle-line';
                let colorVar = 'var(--adt-colorBoard-Success)';
                if (ms > 4000) {
                    colorVar = 'var(--adt-colorBoard-Caution)';
                } else if (ms > 800) {
                    colorVar = 'var(--adt-colorBoard-Warning)';
                }
                statusText.innerHTML = '<iconify-icon icon="' + icon + '"></iconify-icon> ' + ms + 'ms 连接成功';
                statusText.style.color = 'rgb(' + colorVar + ')';
            } else {
                statusText.innerHTML = '<iconify-icon icon="mingcute:close-circle-line"></iconify-icon> 连接失败 (' + result.time + 'ms)';
                statusText.style.color = 'rgb(var(--adt-colorBoard-Middle))';
            }

            timestamp.textContent = new Date().toLocaleTimeString('zh-CN', {
                hour: '2-digit',
                minute: '2-digit'
            });
        });
    });
}

// 页面加载时检查冷却
updateCooldown();
</script>
