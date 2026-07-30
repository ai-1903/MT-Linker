<?php
/**
 * MT-Linker Demo — 开箱即用
 *
 * PHP 内置服务器路由脚本：
 *   php -S localhost:8080 index.php
 *
 * Demo 模式：默认具有管理员权限
 */

// ---- 静态资源直出（PHP 内置服务器路由）------------------------------------
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$ext = strtolower(pathinfo($requestPath, PATHINFO_EXTENSION));
$staticExts = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'webp', 'json', 'xml'];
if (in_array($ext, $staticExts) && file_exists(__DIR__ . $requestPath)) {
    return false; // PHP 内置服务器将直接返回该文件
}

?><!DOCTYPE html>
<html lang="zh-CN" data-color-mode="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MT-Linker</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text",
                         "Helvetica Neue", "PingFang SC", sans-serif;
            background: #f5f5f7;
            color: #1d1d1f;
            min-height: 100vh;
        }

        body.dark {
            background: #0d0d0e;
            color: #f5f5f7;
        }

        .demo-header {
            display: flex;
            justify-content: flex-end;
            padding: 20px 30px;
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: 1px solid rgba(128,128,128,0.2);
            border-radius: 24px;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            color: inherit;
            transition: all 0.2s;
        }
        .theme-toggle:hover {
            background: rgba(128,128,128,0.1);
        }

        body.dark .theme-toggle {
            background: rgba(255,255,255,0.06);
        }

        .demo-footer {
            text-align: center;
            padding: 40px 20px 60px;
            font-size: 13px;
            color: rgba(128,128,128,0.6);
        }
        .demo-footer a {
            color: inherit;
            text-decoration: none;
        }
        .demo-footer a:hover {
            text-decoration: underline;
        }

        .demo-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
    </style>
    <script>
        (function() {
            var m = document.cookie.match(/(?:^|;\s*)theme=([^;]*)/);
            var t = m ? m[1] : 'light';
            if (t === 'dark') document.documentElement.setAttribute('data-color-mode', 'dark'), document.body.classList.add('dark');
        })();
    </script>
</head>
<body>
    <div class="demo-header">
        <button class="theme-toggle" onclick="(function(){
            var b = document.body;
            var m = b.classList.contains('dark') ? 'light' : 'dark';
            b.classList.toggle('dark');
            document.documentElement.setAttribute('data-color-mode', m);
            document.cookie = 'theme=' + m + ';path=/;max-age=31536000';
        })()">
            <span class="icon">☀️</span>
            <span class="label">Toggle Theme</span>
        </button>
    </div>

    <div class="demo-content">
    <?php
    /**
     * MT-Linker Demo — 开箱即用
     *
     * 将此文件放在 MT-Linker 根目录下，
     * 启动 PHP 内置服务器即可预览：
     *   php -S localhost:8080
     *
     * Demo 模式：默认具有管理员权限
     */

    // Demo 模式：模拟管理员权限
    define('MTLINKER_DEMO_MODE', true);

    include __DIR__ . '/api/connector.php';

    // 显示示例页面链接
    echo '<div style="text-align: center; margin: 40px 0; padding: 30px; background: var(--card-bg); border-radius: 16px;">';
    echo '<h2 style="margin-bottom: 20px;">MT-Linker Demo</h2>';
    echo '<p style="margin-bottom: 20px; color: var(--text-secondary);">选择要预览的页面：</p>';
    echo '<div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">';
    echo '<a href="?page=linker" style="padding: 12px 24px; background: rgba(var(--adt-colorBoard-Label)/ 1); color: rgba(var(--adt-colorBoard-buttonLabel)/ 1); border-radius: 12px; text-decoration: none; font-weight: 600;">链接展示</a>';
    echo '<a href="?page=apply" style="padding: 12px 24px; background: rgba(var(--adt-colorBoard-Label)/ 1); color: rgba(var(--adt-colorBoard-buttonLabel)/ 1); border-radius: 12px; text-decoration: none; font-weight: 600;">申请友链</a>';
    echo '<a href="?page=dash" style="padding: 12px 24px; background: rgba(var(--adt-colorBoard-Label)/ 1); color: rgba(var(--adt-colorBoard-buttonLabel)/ 1); border-radius: 12px; text-decoration: none; font-weight: 600;">管理面板</a>';
    echo '</div>';
    echo '</div>';

    // 根据参数加载页面
    if (isset($_GET['page'])) {
        echo '<hr style="margin: 40px 0; border: none; height: 1px; background: rgba(var(--adt-colorBoard-Label), 0.13);">';

        switch ($_GET['page']) {
            case 'linker':
                include __DIR__ . '/view/linker.php';
                break;
            case 'apply':
                include __DIR__ . '/view/apply.php';
                break;
            case 'dash':
                include __DIR__ . '/view/dash.php';
                break;
        }
    }
    ?>
    </div>

    <footer class="demo-footer">
        <p>MT-Linker · <a href="https://icerya.com">iCerya</a> · MIT License</p>
    </footer>
</body>
</html>
