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
 * 核心配置与工具库
 * ====================================
 */

// ---- 防重复加载 ---------------------------------------------------------
if (defined('MTLINKER_CORE_LOADED')) {
    return;
}
define('MTLINKER_CORE_LOADED', true);

// ---- 基础路径 -----------------------------------------------------------
define('MTLINKER_BASE', dirname(__DIR__));
define('MTLINKER_DATA', MTLINKER_BASE . '/data');
define('MTLINKER_CONFIG', MTLINKER_BASE . '/mtklink-config.yml');

// =========================================================================
// YAML 配置加载器（简单实现，仅支持基础键值对）
// =========================================================================
function mtlinker_load_config() {
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    if (!file_exists(MTLINKER_CONFIG)) {
        return [];
    }

    $content = file_get_contents(MTLINKER_CONFIG);
    $lines = explode("\n", $content);
    $config = [];

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line[0] === '#') {
            continue;
        }

        if (preg_match('/^([a-zA-Z0-9_-]+):\s*"?(.*?)"?\s*$/', $line, $matches)) {
            $config[$matches[1]] = $matches[2];
        }
    }

    return $config;
}

// =========================================================================
// YAML 配置写入器（原子写入，防并发）
// =========================================================================
function mtlinker_update_config($key, $value) {
    $lockFile = MTLINKER_CONFIG . '.lock';
    $fp = fopen($lockFile, 'c');

    if (!$fp || !flock($fp, LOCK_EX)) {
        return false;
    }

    try {
        $config = mtlinker_load_config();
        $config[$key] = $value;

        $lines = ["# MT-Linker Configuration", "# ========================", "# This file stores environment-specific configuration.", "# Do NOT commit this file with sensitive data to version control.", ""];
        $lines[] = "# Authentication type: \"wp\" | \"mtk\" | \"adt\" | \"none\"";
        $lines[] = "type: \"" . ($config['type'] ?? 'none') . "\"";
        $lines[] = "";
        $lines[] = "# For type=\"none\": Custom authentication endpoint and key";
        $lines[] = "auth: \"" . ($config['auth'] ?? '') . "\"";
        $lines[] = "key: \"" . ($config['key'] ?? '') . "\"";
        $lines[] = "";
        $lines[] = "# SQLite credentials (auto-generated, do not modify manually)";
        $lines[] = "sqlite-user: \"" . ($config['sqlite-user'] ?? '') . "\"";
        $lines[] = "sqlite-key: \"" . ($config['sqlite-key'] ?? '') . "\"";

        $result = file_put_contents(MTLINKER_CONFIG, implode("\n", $lines) . "\n", LOCK_EX);

        flock($fp, LOCK_UN);
        fclose($fp);
        @unlink($lockFile);

        return $result !== false;
    } catch (Exception $e) {
        flock($fp, LOCK_UN);
        fclose($fp);
        @unlink($lockFile);
        return false;
    }
}

// =========================================================================
// SQLite 数据库初始化（无感自动生成密钥）
// =========================================================================
function mtlinker_init_database() {
    $config = mtlinker_load_config();

    // 检查是否已初始化
    if (!empty($config['sqlite-user']) && !empty($config['sqlite-key'])) {
        return true;
    }

    // 生成 SQLite 应用层鉴权密钥
    $sqliteUser = bin2hex(random_bytes(16));
    $sqliteKey = bin2hex(random_bytes(32));

    // 写入配置
    mtlinker_update_config('sqlite-user', $sqliteUser);
    mtlinker_update_config('sqlite-key', $sqliteKey);

    // 创建数据库文件
    $dbPath = MTLINKER_DATA . '/mtlinker.db';

    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 创建 Linker 表
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS Linker (
                NO INTEGER PRIMARY KEY AUTOINCREMENT,
                Name TEXT NOT NULL,
                Des TEXT,
                type INTEGER NOT NULL DEFAULT 0,
                Link TEXT NOT NULL,
                icon TEXT,
                color TEXT,
                status INTEGER NOT NULL DEFAULT 1
            )
        ");

        // 设置数据库文件权限（仅所有者读写）
        chmod($dbPath, 0600);

        return true;
    } catch (PDOException $e) {
        error_log('MT-Linker DB Init Error: ' . $e->getMessage());
        return false;
    }
}

// =========================================================================
// 获取数据库连接（带应用层鉴权验证）
// =========================================================================
function mtlinker_get_db($providedUser = null, $providedKey = null) {
    $config = mtlinker_load_config();

    // 验证应用层鉴权（如果提供了凭据）
    if ($providedUser !== null && $providedKey !== null) {
        if ($providedUser !== $config['sqlite-user'] || $providedKey !== $config['sqlite-key']) {
            return null; // 鉴权失败
        }
    }

    $dbPath = MTLINKER_DATA . '/mtlinker.db';

    if (!file_exists($dbPath)) {
        mtlinker_init_database();
    }

    try {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log('MT-Linker DB Connection Error: ' . $e->getMessage());
        return null;
    }
}

// =========================================================================
// 鉴权验证函数
// =========================================================================
function mtlinker_verify_auth() {
    $config = mtlinker_load_config();
    $type = $config['type'] ?? 'none';

    switch ($type) {
        case 'wp':
            // WordPress 环境验证
            if (!defined('ABSPATH')) {
                return false;
            }
            return is_user_logged_in() && current_user_can('manage_options');

        case 'none':
            // 自定义 API 验证
            $authUrl = $config['auth'] ?? '';
            $authKey = $config['key'] ?? '';

            if (empty($authUrl) || empty($authKey)) {
                return false;
            }

            // 从请求头或 Cookie 获取提交的 key
            $providedKey = $_SERVER['HTTP_X_MTLINKER_KEY'] ?? $_COOKIE['mtlinker_key'] ?? '';

            if (empty($providedKey)) {
                return false;
            }

            // 简单对比（生产环境建议调用远程 API）
            return hash_equals($authKey, $providedKey);

        case 'mtk':
        case 'adt':
            // 预留接口，暂不处理
            return false;

        default:
            return false;
    }
}
