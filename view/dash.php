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
 * MT-Linker Dashboard — 仪表板（需管理员权限）
 * ====================================
 * 短代码：[mtlinker-dash]
 */

// ---- 二次权限验证（防直接访问）------------------------------------------
if (!function_exists('mtlinker_verify_auth')) {
    die('Access Denied');
}

if (!mtlinker_verify_auth()) {
    die('Access Denied: Admin privileges required.');
}

// ---- 加载核心库 ---------------------------------------------------------
require_once __DIR__ . '/../func/core.php';

// ---- 获取数据库连接 -----------------------------------------------------
$db = mtlinker_get_db();
if (!$db) {
    echo '<div class="mtl-error">数据库连接失败</div>';
    return;
}

// ---- 处理操作 -----------------------------------------------------------
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mtl_action'])) {
        switch ($_POST['mtl_action']) {
            case 'delete':
                $id = intval($_POST['id']);
                $db->exec("DELETE FROM Linker WHERE NO = $id");
                $message = '删除成功';
                break;

            case 'update':
                $id = intval($_POST['id']);
                $stmt = $db->prepare("UPDATE Linker SET Name=?, Des=?, type=?, Link=?, icon=?, color=?, status=? WHERE NO=?");
                $stmt->execute([
                    trim($_POST['name']),
                    trim($_POST['des']),
                    intval($_POST['type']),
                    trim($_POST['link']),
                    trim($_POST['icon']),
                    trim($_POST['color']),
                    intval($_POST['status']),
                    $id
                ]);
                $message = '更新成功';
                break;

            case 'add':
                $stmt = $db->prepare("INSERT INTO Linker (Name, Des, type, Link, icon, color, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    trim($_POST['name']),
                    trim($_POST['des']),
                    intval($_POST['type']),
                    trim($_POST['link']),
                    trim($_POST['icon']),
                    trim($_POST['color']),
                    intval($_POST['status'])
                ]);
                $message = '添加成功';
                break;
        }
    }
}

// ---- 获取数据 -----------------------------------------------------------
// 分页参数
$page = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
$limit = 13;
$offset = ($page - 1) * $limit;

$pending = $db->query("SELECT * FROM Linker WHERE status = 0 ORDER BY NO DESC")->fetchAll();

// 获取总数用于分页
$totalCount = $db->query("SELECT COUNT(*) FROM Linker WHERE status != 0")->fetchColumn();
$totalPages = ceil($totalCount / $limit);

// 分页查询
$stmt = $db->prepare("SELECT * FROM Linker WHERE status != 0 ORDER BY status ASC, NO DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$others = $stmt->fetchAll();

// 加载配置（供模板使用）
$config = mtlinker_load_config();

$statusOptions = [
    0 => '审核中',
    2 => '界域连通',
    3 => '通信失联',
    4 => '注销星域',
    10 => '已隐藏'
];

$typeOptions = [
    0 => '本星域',
    1 => '联通百界',
    3 => '泛星域'
];
?>

<div class="mt-linker-page-wrapper">
    <div class="mtl-dashboard">
        <div class="mtl-dashboard-header">
            <h1 class="mtl-title">管理面板</h1>
            <button class="mtl-add-button" onclick="showAddModal()">
                <iconify-icon icon="mingcute:add-line"></iconify-icon>
                添加链接
            </button>
        </div>

        <?php if ($message): ?>
            <div class="mtl-message"><?php echo esc_html($message); ?></div>
        <?php endif; ?>

        <!-- 待审核 -->
        <div class="mtl-table-container">
            <h2 class="mtl-table-title">待审核 (<?php echo count($pending); ?>)</h2>
            <table class="mtl-table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>名称</th>
                        <th>描述</th>
                        <th>类型</th>
                        <th>链接</th>
                        <th>图标</th>
                        <th>颜色</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending as $item): ?>
                        <tr data-id="<?php echo $item['NO']; ?>">
                            <td class="mtl-readonly"><?php echo $item['NO']; ?></td>
                            <td class="mtl-editable" data-field="name" data-val="<?php echo esc_html($item['Name']); ?>"><?php echo esc_html($item['Name']); ?></td>
                            <td class="mtl-editable" data-field="des" data-val="<?php echo esc_html($item['Des']); ?>"><?php echo esc_html($item['Des']); ?></td>
                            <td class="mtl-editable" data-field="type" data-val="<?php echo esc_html($item['type']); ?>"><?php echo $typeOptions[$item['type']]; ?></td>
                            <td class="mtl-editable" data-field="link" data-val="<?php echo esc_html($item['Link']); ?>"><a href="<?php echo esc_url($item['Link']); ?>" target="_blank"><?php echo esc_html($item['Link']); ?></a></td>
                            <td class="mtl-editable" data-field="icon" data-val="<?php echo esc_html($item['icon']); ?>"><img src="<?php echo esc_url($item['icon']); ?>" width="24" height="24"></td>
                            <td class="mtl-editable" data-field="color" data-val="<?php echo esc_html($item['color']); ?>"><div class="mtl-color-preview" style="background:rgb(<?php echo esc_html($item['color']); ?>);"></div></td>
                            <td class="mtl-editable" data-field="status" data-val="<?php echo esc_html($item['status']); ?>"><?php echo $statusOptions[$item['status']]; ?></td>
                            <td class="mtl-actions">
                                <button class="mtl-btn mtl-btn-edit" onclick="editRow(this)">编辑</button>
                                <button class="mtl-btn mtl-btn-delete" onclick="deleteRow(<?php echo $item['NO']; ?>, '<?php echo esc_js($item['Name']); ?>')">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 其他状态 -->
        <div class="mtl-table-container">
            <h2 class="mtl-table-title">已处理项</h2>
            <table class="mtl-table">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>名称</th>
                        <th>描述</th>
                        <th>类型</th>
                        <th>链接</th>
                        <th>图标</th>
                        <th>颜色</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($others as $item): ?>
                        <tr data-id="<?php echo $item['NO']; ?>">
                            <td class="mtl-readonly"><?php echo $item['NO']; ?></td>
                            <td class="mtl-editable" data-field="name" data-val="<?php echo esc_html($item['Name']); ?>"><?php echo esc_html($item['Name']); ?></td>
                            <td class="mtl-editable" data-field="des" data-val="<?php echo esc_html($item['Des']); ?>"><?php echo esc_html($item['Des']); ?></td>
                            <td class="mtl-editable" data-field="type" data-val="<?php echo esc_html($item['type']); ?>"><?php echo $typeOptions[$item['type']]; ?></td>
                            <td class="mtl-editable" data-field="link" data-val="<?php echo esc_html($item['Link']); ?>"><a href="<?php echo esc_url($item['Link']); ?>" target="_blank"><?php echo esc_html($item['Link']); ?></a></td>
                            <td class="mtl-editable" data-field="icon" data-val="<?php echo esc_html($item['icon']); ?>"><img src="<?php echo esc_url($item['icon']); ?>" width="24" height="24"></td>
                            <td class="mtl-editable" data-field="color" data-val="<?php echo esc_html($item['color']); ?>"><div class="mtl-color-preview" style="background:rgb(<?php echo esc_html($item['color']); ?>);"></div></td>
                            <td class="mtl-editable" data-field="status" data-val="<?php echo esc_html($item['status']); ?>"><?php echo $statusOptions[$item['status']]; ?></td>
                            <td class="mtl-actions">
                                <button class="mtl-btn mtl-btn-edit" onclick="editRow(this)">编辑</button>
                                <button class="mtl-btn mtl-btn-delete" onclick="deleteRow(<?php echo $item['NO']; ?>, '<?php echo esc_js($item['Name']); ?>')">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- 分页器 -->
            <?php if ($totalPages > 1): ?>
                <div class="mtl-pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo defined('ABSPATH') ? esc_url(add_query_arg('p', $page - 1)) : '?page=dash&p=' . ($page - 1); ?>" class="mtl-btn">上一页</a>
                    <?php endif; ?>

                    <span class="mtl-page-info">第 <?php echo $page; ?> / <?php echo $totalPages; ?> 页</span>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo defined('ABSPATH') ? esc_url(add_query_arg('p', $page + 1)) : '?page=dash&p=' . ($page + 1); ?>" class="mtl-btn">下一页</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 添加弹窗 -->
<div class="mtl-modal" id="addModal">
    <div class="mtl-modal-content">
        <div class="mtl-modal-header">
            <h2>添加新链接</h2>
            <button class="mtl-modal-close" onclick="hideAddModal()">
                <iconify-icon icon="fluent:dismiss-24-regular"></iconify-icon>
            </button>
        </div>
        <?php
            $is_dash_mode = true;
            require __DIR__ . '/../func/tpl-apply-form.php';
        ?>
    </div>
</div>

<script src="<?php echo defined('MT_LINKER_BASE_URL') ? MT_LINKER_BASE_URL : ''; ?>/js/inCheck.js"></script>
<script>
const statusOptions = <?php echo json_encode($statusOptions); ?>;
const typeOptions = <?php echo json_encode($typeOptions); ?>;
const config = {
    focusHTTPS: <?php echo json_encode(($config['incheck_focusHTTPS'] ?? '1') === '1'); ?>,
    checkDomain: <?php echo json_encode(($config['incheck_DOMAIN'] ?? '1') === '1'); ?>,
    showAlpha: <?php echo json_encode(($config['incheck_colorAlpha'] ?? '1') === '1'); ?>
};

const isWP = <?php echo defined('ABSPATH') ? 'true' : 'false'; ?>;
const wpAdminPost = "<?php echo defined('ABSPATH') ? esc_js(admin_url('admin-post.php')) : ''; ?>";

function showAddModal() {
    document.getElementById('addModal').classList.add('show');
}

function hideAddModal() {
    document.getElementById('addModal').classList.remove('show');
}

function deleteRow(id, name) {
    const confirmation = prompt(`确认删除？请输入「确认删除${name}」`);
    if (confirmation === `确认删除${name}`) {
        const form = document.createElement('form');
        form.method = 'POST';
        if (isWP) {
            form.action = wpAdminPost;
        }
        form.innerHTML = `<input type="hidden" name="mtl_action" value="delete"><input type="hidden" name="id" value="${id}">`;
        if (isWP) {
            const wpAction = document.createElement('input');
            wpAction.type = 'hidden';
            wpAction.name = 'action';
            wpAction.value = 'mtl_submit_dash';
            form.appendChild(wpAction);
        }
        document.body.appendChild(form);
        form.submit();
    }
}

function editRow(btn) {
    const row = btn.closest('tr');
    const isEditing = row.classList.contains('editing');

    if (isEditing) {
        // 保存模式：先校验再提交
        validateAndSaveRow(row, btn);
    } else {
        // 编辑模式
        enterEditMode(row, btn);
    }
}

function enterEditMode(row, btn) {
    row.classList.add('editing');
    btn.textContent = '保存';
    btn.classList.add('saving');

    const id = row.dataset.id;

    // 备份原始 HTML，为"取消"做准备
    row.querySelectorAll('.mtl-editable').forEach(cell => {
        if (cell.dataset.originalHtml === undefined) {
            cell.dataset.originalHtml = cell.innerHTML;
        }
    });

    // 把删除按钮改成取消
    const actionsCell = row.querySelector('.mtl-actions');
    const deleteBtn = actionsCell.querySelector('.mtl-btn-delete');
    if (deleteBtn) {
        deleteBtn.dataset.origText = deleteBtn.textContent;
        deleteBtn.dataset.origClick = deleteBtn.getAttribute('onclick');
        deleteBtn.textContent = '取消';
        deleteBtn.classList.replace('mtl-btn-delete', 'mtl-btn-secondary');
        deleteBtn.setAttribute('onclick', 'cancelEditMode(this)');
    }

    row.querySelectorAll('.mtl-editable').forEach(cell => {
        const field = cell.dataset.field;
        const currentValue = extractValue(cell, field);

        cell.dataset.original = currentValue;

        if (field === 'type') {
            const select = document.createElement('select');
            Object.entries(typeOptions).forEach(([val, label]) => {
                const option = document.createElement('option');
                option.value = val;
                option.textContent = label;
                if (label === currentValue) option.selected = true;
                select.appendChild(option);
            });
            cell.innerHTML = '';
            cell.appendChild(select);
        } else if (field === 'status') {
            const select = document.createElement('select');
            Object.entries(statusOptions).forEach(([val, label]) => {
                const option = document.createElement('option');
                option.value = val;
                option.textContent = label;
                if (label === currentValue) option.selected = true;
                select.appendChild(option);
            });
            cell.innerHTML = '';
            cell.appendChild(select);
        } else if (field === 'color') {
            const parts = currentValue.split('/');
            const rgb = parts[0].trim().split(/\s+/);
            const a = parts[1] ? parts[1].trim() : '1';

            const container = document.createElement('div');
            container.className = 'mtl-color-inputs-inline';
            container.innerHTML = `
                <input type="number" data-color="r" min="0" max="255" value="${rgb[0] || 0}" style="width:50px">
                <input type="number" data-color="g" min="0" max="255" value="${rgb[1] || 0}" style="width:50px">
                <input type="number" data-color="b" min="0" max="255" value="${rgb[2] || 0}" style="width:50px">
                <span>/</span>
                <input type="number" data-color="a" min="0" max="1" step="0.01" value="${a}" style="width:50px">
            `;
            cell.innerHTML = '';
            cell.appendChild(container);

            // 颜色输入实时校验
            container.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', () => {
                    const inputs = container.querySelectorAll('input');
                    const result = window.MTLinkerValidation.validateColor(
                        inputs[0].value, inputs[1].value, inputs[2].value, inputs[3].value, config
                    );
                    inputs.forEach(inp => inp.classList.toggle('error', !result.valid));
                });
            });
        } else if (field === 'icon') {
            const input = document.createElement('input');
            input.type = 'url';
            input.value = currentValue;
            cell.innerHTML = '';
            cell.appendChild(input);

            // Icon 实时校验
            input.addEventListener('input', () => {
                const result = window.MTLinkerValidation.validateIcon(input.value, config);
                input.classList.toggle('error', !result.valid);
                input.classList.toggle('warning', !result.valid);
            });
        } else if (field === 'link') {
            const input = document.createElement('input');
            input.type = 'url';
            input.value = currentValue;
            cell.innerHTML = '';
            cell.appendChild(input);

            // Link 实时校验
            input.addEventListener('input', () => {
                const result = window.MTLinkerValidation.validateLink(input.value, config);
                input.classList.toggle('error', !result.valid);
                input.classList.toggle('warning', !result.valid);
            });
        } else if (field === 'des') {
            const textarea = document.createElement('textarea');
            textarea.value = currentValue;
            textarea.rows = 2;
            cell.innerHTML = '';
            cell.appendChild(textarea);

            // Des 实时校验
            textarea.addEventListener('input', () => {
                const result = window.MTLinkerValidation.validateDes(textarea.value);
                textarea.classList.toggle('error', !result.valid);
                textarea.classList.toggle('warning', !result.valid);
            });
        } else if (field === 'name') {
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentValue;
            cell.innerHTML = '';
            cell.appendChild(input);

            // Name 实时校验
            input.addEventListener('input', () => {
                const result = window.MTLinkerValidation.validateName(input.value);
                input.classList.toggle('error', !result.valid);
                input.classList.toggle('warning', !result.valid);
            });
        }
    });
}

function validateAndSaveRow(row, btn) {
    const errors = [];
    const data = { mtl_action: 'update', id: row.dataset.id };

    // 提取并校验所有字段
    row.querySelectorAll('.mtl-editable').forEach(cell => {
        const field = cell.dataset.field;

        if (field === 'type' || field === 'status') {
            data[field] = cell.querySelector('select').value;
        } else if (field === 'color') {
            const inputs = cell.querySelectorAll('input');
            const r = inputs[0].value;
            const g = inputs[1].value;
            const b = inputs[2].value;
            const a = inputs[3].value;
            const result = window.MTLinkerValidation.validateColor(r, g, b, a, config);
            if (!result.valid) {
                errors.push(`颜色：${result.msg}`);
            }
            data[field] = `${r} ${g} ${b} / ${a}`;
        } else if (field === 'des') {
            const val = cell.querySelector('textarea').value;
            const result = window.MTLinkerValidation.validateDes(val);
            if (!result.valid) {
                errors.push(`描述：${result.msg}`);
            }
            data[field] = val;
        } else if (field === 'name') {
            const val = cell.querySelector('input').value;
            const result = window.MTLinkerValidation.validateName(val);
            if (!result.valid) {
                errors.push(`名称：${result.msg}`);
            }
            data[field] = val;
        } else if (field === 'link') {
            const val = cell.querySelector('input').value;
            const result = window.MTLinkerValidation.validateLink(val, config);
            if (!result.valid) {
                errors.push(`链接：${result.msg}`);
            }
            data[field] = val;
        } else if (field === 'icon') {
            const val = cell.querySelector('input').value;
            const result = window.MTLinkerValidation.validateIcon(val, config);
            if (!result.valid) {
                errors.push(`图标：${result.msg}`);
            }
            data[field] = val;
        }
    });

    // 如果有错误，阻止提交
    if (errors.length > 0) {
        alert('校验失败，请修正以下错误：\n\n' + errors.join('\n'));
        return;
    }

    // 提交表单
    const form = document.createElement('form');
    form.method = 'POST';
    if (isWP) {
        form.action = wpAdminPost;
    }
    for (const [key, value] of Object.entries(data)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    if (isWP) {
        const wpAction = document.createElement('input');
        wpAction.type = 'hidden';
        wpAction.name = 'action';
        wpAction.value = 'mtl_submit_dash';
        form.appendChild(wpAction);
    }
    document.body.appendChild(form);
    form.submit();
}

function extractValue(cell, field) {
    if (cell.hasAttribute('data-val')) return cell.getAttribute('data-val');
    if (field === 'link') {
        const a = cell.querySelector('a');
        return a ? a.getAttribute('href') : '';
    } else if (field === 'icon') {
        const img = cell.querySelector('img');
        return img ? img.getAttribute('src') : '';
    } else if (field === 'color') {
        const div = cell.querySelector('.mtl-color-preview');
        if (div) {
            const bg = div.style.background;
            const match = bg.match(/rgba?\(([^)]+)\)/);
            return match ? match[1] : '0 0 0 / 1';
        }
        return '0 0 0 / 1';
    } else {
        return cell.textContent.trim();
    }
}

function cancelEditMode(cancelBtn) {
    const row = cancelBtn.closest('tr');
    row.classList.remove('editing');

    // 恢复单元格原始样貌
    row.querySelectorAll('.mtl-editable').forEach(cell => {
        if (cell.dataset.originalHtml !== undefined) {
            cell.innerHTML = cell.dataset.originalHtml;
        }
    });

    // 恢复保存按钮为编辑
    const actionsCell = row.querySelector('.mtl-actions');
    const saveBtn = actionsCell.querySelector('.mtl-btn-edit');
    if (saveBtn) {
        saveBtn.textContent = '编辑';
        saveBtn.classList.remove('saving');
    }

    // 把取消按钮变回红色删除
    cancelBtn.textContent = cancelBtn.dataset.origText;
    cancelBtn.classList.replace('mtl-btn-secondary', 'mtl-btn-delete');
    cancelBtn.setAttribute('onclick', cancelBtn.dataset.origClick);
}
</script>
