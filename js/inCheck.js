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
 * Input Validation — 输入校验逻辑
 * ====================================
 */

// 屏蔽的托管平台域名
const BLOCKED_DOMAINS = [
    'github.io',
    'vercel.app',
    'netlify.app',
    'herokuapp.com',
    'azurewebsites.net',
    'cloudfront.net',
    'pages.dev',
    'web.app',
    'firebaseapp.com',
    'gitlab.io',
    'surge.sh',
    'now.sh'
];

/**
 * 获取字符串实际长度（中文算2，英文算1）
 */
function getStringLength(str) {
    let len = 0;
    for (let i = 0; i < str.length; i++) {
        const code = str.charCodeAt(i);
        if (code >= 0x4e00 && code <= 0x9fa5) {
            len += 2; // 中文字符
        } else {
            len += 1; // 英文字符
        }
    }
    return len;
}

/**
 * 校验 Name 字段
 * @param {string} value
 * @returns {object} { valid: boolean, message: string }
 */
function validateName(value) {
    if (!value || value.trim() === '') {
        return { valid: false, message: '该值不得为空', level: 'error' };
    }

    const len = getStringLength(value);
    if (len > 50) {
        return {
            valid: false,
            message: `名称过长（当前 ${len}/50），请缩短名称`,
            level: 'warning'
        };
    }

    return { valid: true, message: '', level: 'ok' };
}

/**
 * 校验 Des 字段
 * @param {string} value
 * @returns {object} { valid: boolean, message: string }
 */
function validateDes(value) {
    if (!value || value.trim() === '') {
        return { valid: false, message: '该值不得为空', level: 'error' };
    }

    const len = getStringLength(value);
    if (len > 170) {
        return {
            valid: false,
            message: `描述过长（当前 ${len}/170），请缩短描述`,
            level: 'warning'
        };
    }

    return { valid: true, message: '', level: 'ok' };
}

/**
 * 校验 Link 字段
 * @param {string} value
 * @param {object} config - { focusHTTPS: boolean, checkDomain: boolean }
 * @param {array} existingLinks - 已存在的链接列表
 * @returns {object} { valid: boolean, message: string }
 */
function validateLink(value, config = {}, existingLinks = []) {
    if (!value || value.trim() === '') {
        return { valid: false, message: '该值不得为空', level: 'error' };
    }

    // 检查 HTTPS
    if (config.focusHTTPS && !value.startsWith('https://')) {
        return {
            valid: false,
            message: '链接必须以 https:// 开头\n示例：https://icerya.com\n示例：https://example.com',
            level: 'warning'
        };
    }

    // 检查尾部斜杠
    if (value.endsWith('/')) {
        return {
            valid: false,
            message: '链接尾部不能包含 /\n示例：https://icerya.com\n示例：https://example.com/page',
            level: 'warning'
        };
    }

    // 检查托管平台域名
    if (config.checkDomain) {
        for (const domain of BLOCKED_DOMAINS) {
            if (value.includes(domain)) {
                return {
                    valid: false,
                    message: `不允许使用托管平台域名（${domain}）\n请使用自有域名`,
                    level: 'warning'
                };
            }
        }
    }

    // 检查重复
    if (existingLinks.includes(value)) {
        return {
            valid: false,
            message: '该链接已存在',
            level: 'warning'
        };
    }

    return { valid: true, message: '', level: 'ok' };
}

/**
 * 校验 Icon 字段
 * @param {string} value
 * @param {object} config - { focusHTTPS: boolean }
 * @returns {object} { valid: boolean, message: string }
 */
function validateIcon(value) {
    if (!value || value.trim() === '') {
        return { valid: false, message: '该值不得为空', level: 'error' };
    }

    // 检查 HTTPS
    if (!value.startsWith('https://')) {
        return {
            valid: false,
            message: '图标链接必须以 https:// 开头\n示例：https://example.com/icon.png\n示例：https://cdn.example.com/logo.svg',
            level: 'warning'
        };
    }

    // 检查文件扩展名
    const validExts = ['.jpg', '.jpeg', '.png', '.svg'];
    const hasValidExt = validExts.some(ext => value.toLowerCase().endsWith(ext));

    if (!hasValidExt) {
        return {
            valid: false,
            message: '图标必须是 .jpg / .png / .svg 格式\n示例：https://example.com/icon.png\n示例：https://cdn.example.com/logo.svg',
            level: 'warning'
        };
    }

    return { valid: true, message: '', level: 'ok' };
}

/**
 * 校验 Color 字段
 * @param {string} r - 0-255
 * @param {string} g - 0-255
 * @param {string} b - 0-255
 * @param {string} a - 0-1 (可选)
 * @param {object} config - { showAlpha: boolean }
 * @returns {object} { valid: boolean, message: string }
 */
function validateColor(r, g, b, a = '1', config = {}) {
    const errors = [];

    // 验证 RGB
    [r, g, b].forEach((val, idx) => {
        const names = ['红色', '绿色', '蓝色'];
        if (val === '' || val === null || val === undefined) {
            errors.push(`${names[idx]}分量不得为空`);
        } else {
            const num = parseInt(val);
            if (isNaN(num) || num < 0 || num > 255) {
                errors.push(`${names[idx]}分量必须在 0-255 之间`);
            }
        }
    });

    // 验证 Alpha（如果启用）
    if (config.showAlpha) {
        if (a === '' || a === null || a === undefined) {
            errors.push('透明度不得为空');
        } else {
            const num = parseFloat(a);
            if (isNaN(num) || num < 0 || num > 1) {
                errors.push('透明度必须在 0-1 之间');
            }
        }
    }

    if (errors.length > 0) {
        return {
            valid: false,
            message: errors.join('\n') + '\n示例：255 128 64 / 0.8\n示例：52 199 89 / 1',
            level: 'warning'
        };
    }

    return { valid: true, message: '', level: 'ok' };
}

/**
 * 应用校验结果到 UI
 * @param {HTMLElement} input - 输入框元素
 * @param {object} result - 校验结果
 */
function applyValidationUI(input, result) {
    const hintElement = input.parentElement.querySelector('.mtl-form-hint');

    // 清除之前的状态
    input.classList.remove('error', 'warning');
    if (hintElement) {
        hintElement.textContent = '';
        hintElement.classList.remove('error', 'warning');
    }

    if (!result.valid) {
        if (result.level === 'error') {
            input.classList.add('error');
            if (hintElement) {
                hintElement.textContent = result.message;
                hintElement.classList.add('error');
            }
        } else if (result.level === 'warning') {
            input.classList.add('warning');
            if (hintElement) {
                hintElement.textContent = result.message;
                hintElement.classList.add('warning');
            }
        }
    }
}

// 导出函数供全局使用
if (typeof window !== 'undefined') {
    window.MTLinkerValidation = {
        validateName,
        validateDes,
        validateLink,
        validateIcon,
        validateColor,
        applyValidationUI,
        getStringLength
    };
}
