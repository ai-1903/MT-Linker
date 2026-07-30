<!--
Copyright (c) 2026 iCerya (icerya.com). All Rights Reserved.

Released under the MIT License.
See LICENSE file in the project root for full license text.

@package   MT-Linker
@author    iCerya
@link      https://icerya.com
-->

<!DOCTYPE html>
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
     */
    include __DIR__ . '/api/connector.php';
    ?>
    </div>

    <footer class="demo-footer">
        <p>MT-Linker · <a href="https://icerya.com">iCerya</a> · MIT License</p>
    </footer>
</body>
</html>
