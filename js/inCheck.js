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
 * Input Validation — 统一校验核心库
 * ====================================
 */

(function() {
    'use strict';

    /**
     * 获取字符串实际字节长度（双字节逻辑：1 汉字 = 2 英符）
     * @param {string} val
     * @returns {number}
     */
    function getByteLength(val) {
        if (!val) return 0;
        return val.replace(/[^\x00-\xff]/g, "01").length;
    }

    /**
     * 校验 Name 字段
     * @param {string} val
     * @returns {object} { valid: boolean, msg: string }
     */
    function validateName(val) {
        if (!val || val.trim() === '') {
            return { valid: false, msg: '站点名称不能为空' };
        }

        const len = getByteLength(val);
        if (len > 50) {
            return { valid: false, msg: `站点名称过长（当前 ${len}/50 字节），请缩短` };
        }

        return { valid: true, msg: '' };
    }

    /**
     * 校验 Des 字段
     * @param {string} val
     * @returns {object} { valid: boolean, msg: string }
     */
    function validateDes(val) {
        if (!val || val.trim() === '') {
            return { valid: false, msg: '站点描述不能为空' };
        }

        const len = getByteLength(val);
        if (len > 170) {
            return { valid: false, msg: `站点描述过长（当前 ${len}/170 字节），请缩短` };
        }

        return { valid: true, msg: '' };
    }

    /**
     * 校验 Link 字段
     * @param {string} val
     * @param {object} config - { focusHTTPS: boolean, checkDomain: boolean }
     * @returns {object} { valid: boolean, msg: string }
     */
    function validateLink(val, config = {}) {
        if (!val || val.trim() === '') {
            return { valid: false, msg: '站点链接不能为空' };
        }

        val = val.trim();

        // 规则 1：不能以 / 结尾
        if (val.endsWith('/')) {
            return { valid: false, msg: '链接尾部不能包含斜杠 /\n示例：https://icerya.com' };
        }

        // 规则 2：HTTPS 检查
        if (config.focusHTTPS && !val.startsWith('https://')) {
            return { valid: false, msg: '链接必须以 https:// 开头\n示例：https://icerya.com' };
        }

        // 规则 3：屏蔽托管平台域名
        if (config.checkDomain) {
            const blockedDomains = [
                'github.io',
                'vercel.app',
                'gitee.io',
                'netlify.app',
                'onrender.com',
                'firebaseapp.com'
            ];

            for (const domain of blockedDomains) {
                if (val.includes(domain)) {
                    return { valid: false, msg: `不允许使用托管平台域名（${domain}）\n请使用自有域名` };
                }
            }
        }

        return { valid: true, msg: '' };
    }

    /**
     * 校验 Icon 字段
     * @param {string} val
     * @param {object} config - { focusHTTPS: boolean }
     * @returns {object} { valid: boolean, msg: string }
     */
    function validateIcon(val, config = {}) {
        if (!val || val.trim() === '') {
            return { valid: false, msg: '站点图标不能为空' };
        }

        val = val.trim();

        // 规则 1：HTTPS 检查
        if (config.focusHTTPS && !val.startsWith('https://')) {
            return { valid: false, msg: '图标链接必须以 https:// 开头\n示例：https://example.com/icon.png' };
        }

        // 规则 2：文件扩展名检查
        const validExts = ['.jpg', '.jpeg', '.JPG', '.JPEG', '.png', '.PNG', '.svg', '.SVG', '.webp', '.WEBP'];
        const hasValidExt = validExts.some(ext => val.toLowerCase().endsWith(ext));

        if (!hasValidExt) {
            return { valid: false, msg: '图标必须是 .jpg / .png / .svg 格式\n示例：https://example.com/icon.png' };
        }

        return { valid: true, msg: '' };
    }

    /**
     * 校验 Color 字段
     * @param {string|number} r - 0-255
     * @param {string|number} g - 0-255
     * @param {string|number} b - 0-255
     * @param {string|number} a - 0-1 (可选)
     * @param {object} config - { showAlpha: boolean }
     * @returns {object} { valid: boolean, msg: string }
     */
    function validateColor(r, g, b, a = '1', config = {}) {
        const errors = [];

        // 验证 RGB
        const rgbValues = [
            { val: r, name: 'R（红色）' },
            { val: g, name: 'G（绿色）' },
            { val: b, name: 'B（蓝色）' }
        ];

        rgbValues.forEach(item => {
            if (item.val === '' || item.val === null || item.val === undefined) {
                errors.push(`${item.name} 分量不能为空`);
            } else {
                const num = parseInt(item.val);
                if (isNaN(num) || num < 0 || num > 255) {
                    errors.push(`${item.name} 必须在 0-255 之间`);
                }
            }
        });

        // 验证 Alpha（如果启用）
        if (config.showAlpha !== false) {
            if (a === '' || a === null || a === undefined) {
                errors.push('Alpha（透明度）不能为空');
            } else {
                const num = parseFloat(a);
                if (isNaN(num) || num < 0 || num > 1) {
                    errors.push('Alpha 必须在 0-1 之间');
                }
            }
        }

        if (errors.length > 0) {
            return {
                valid: false,
                msg: errors.join('\n') + '\n示例：0 199 190 / 1'
            };
        }

        return { valid: true, msg: '' };
    }

    /**
     * 应用校验结果到 UI（通用方法）
     * @param {HTMLElement} input - 输入框元素
     * @param {object} result - 校验结果 { valid, msg }
     */
    function applyValidationUI(input, result) {
        if (!input) return;

        // 清除之前的状态
        input.classList.remove('error', 'warning');

        // 尝试查找提示容器
        const hintElement = input.parentElement.querySelector('.mtl-form-hint');
        if (!hintElement) return;

        // 保存默认提示文本（首次调用）
        if (!hintElement.dataset.defaultHint) {
            hintElement.dataset.defaultHint = hintElement.textContent;
        }

        if (!result.valid) {
            // 根据消息严重性决定样式
            const isError = result.msg.includes('不能为空');
            input.classList.add(isError ? 'error' : 'warning');

            hintElement.textContent = result.msg;
            hintElement.className = 'mtl-form-hint ' + (isError ? 'error' : 'warning');
        } else {
            // 恢复默认提示文本
            hintElement.textContent = hintElement.dataset.defaultHint;
            hintElement.className = 'mtl-form-hint';
        }
    }

    // 挂载到全局
    window.MTLinkerValidation = {
        getByteLength,
        validateName,
        validateDes,
        validateLink,
        validateIcon,
        validateColor,
        applyValidationUI
    };
})();
