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
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
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
$pending = $db->query("SELECT * FROM Linker WHERE status = 0 ORDER BY NO DESC")->fetchAll();
$others = $db->query("SELECT * FROM Linker WHERE status != 0 ORDER BY status ASC, NO DESC LIMIT 13")->fetchAll();

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
                            <td class="mtl-editable" data-field="name"><?php echo esc_html($item['Name']); ?></td>
                            <td class="mtl-editable" data-field="des"><?php echo esc_html($item['Des']); ?></td>
                            <td class="mtl-editable" data-field="type"><?php echo $typeOptions[$item['type']]; ?></td>
                            <td class="mtl-editable" data-field="link"><a href="<?php echo esc_url($item['Link']); ?>" target="_blank"><?php echo esc_html($item['Link']); ?></a></td>
                            <td class="mtl-editable" data-field="icon"><img src="<?php echo esc_url($item['icon']); ?>" width="24" height="24"></td>
                            <td class="mtl-editable" data-field="color"><div class="mtl-color-preview" style="background:rgba(<?php echo esc_html($item['color']); ?>);"></div></td>
                            <td class="mtl-editable" data-field="status"><?php echo $statusOptions[$item['status']]; ?></td>
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
                            <td class="mtl-editable" data-field="name"><?php echo esc_html($item['Name']); ?></td>
                            <td class="mtl-editable" data-field="des"><?php echo esc_html($item['Des']); ?></td>
                            <td class="mtl-editable" data-field="type"><?php echo $typeOptions[$item['type']]; ?></td>
                            <td class="mtl-editable" data-field="link"><a href="<?php echo esc_url($item['Link']); ?>" target="_blank"><?php echo esc_html($item['Link']); ?></a></td>
                            <td class="mtl-editable" data-field="icon"><img src="<?php echo esc_url($item['icon']); ?>" width="24" height="24"></td>
                            <td class="mtl-editable" data-field="color"><div class="mtl-color-preview" style="background:rgba(<?php echo esc_html($item['color']); ?>);"></div></td>
                            <td class="mtl-editable" data-field="status"><?php echo $statusOptions[$item['status']]; ?></td>
                            <td class="mtl-actions">
                                <button class="mtl-btn mtl-btn-edit" onclick="editRow(this)">编辑</button>
                                <button class="mtl-btn mtl-btn-delete" onclick="deleteRow(<?php echo $item['NO']; ?>, '<?php echo esc_js($item['Name']); ?>')">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 添加弹窗 -->
<div class="mtl-modal" id="addModal">
    <div class="mtl-modal-content">
        <h2>添加新链接</h2>
        <form method="POST" id="addForm">
            <input type="hidden" name="action" value="add">

            <div class="mtl-form-group">
                <label>名称</label>
                <input type="text" name="name" required>
            </div>

            <div class="mtl-form-group">
                <label>描述</label>
                <textarea name="des" rows="3" required></textarea>
            </div>

            <div class="mtl-form-group">
                <label>类型</label>
                <select name="type" required>
                    <?php foreach ($typeOptions as $val => $label): ?>
                        <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mtl-form-group">
                <label>链接</label>
                <input type="url" name="link" required>
            </div>

            <div class="mtl-form-group">
                <label>图标</label>
                <input type="url" name="icon" required>
            </div>

            <div class="mtl-form-group">
                <label>颜色 (RGBA)</label>
                <div class="mtl-color-inputs">
                    <input type="number" id="addColorR" min="0" max="255" value="0" required>
                    <input type="number" id="addColorG" min="0" max="255" value="199" required>
                    <input type="number" id="addColorB" min="0" max="255" value="190" required>
                    <span>/</span>
                    <input type="number" id="addColorA" min="0" max="1" step="0.01" value="1" required>
                </div>
                <input type="hidden" name="color" id="addColorFinal" value="0 199 190 / 1">
            </div>

            <div class="mtl-form-group">
                <label>状态</label>
                <select name="status" required>
                    <?php foreach ($statusOptions as $val => $label): ?>
                        <option value="<?php echo $val; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mtl-modal-actions">
                <button type="button" class="mtl-btn" onclick="hideAddModal()">取消</button>
                <button type="submit" class="mtl-btn mtl-btn-primary">添加</button>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo defined('MT_LINKER_BASE_URL') ? MT_LINKER_BASE_URL : ''; ?>/js/inCheck.js"></script>
<script>
const statusOptions = <?php echo json_encode($statusOptions); ?>;
const typeOptions = <?php echo json_encode($typeOptions); ?>;

function showAddModal() {
    document.getElementById('addModal').classList.add('show');
}

function hideAddModal() {
    document.getElementById('addModal').classList.remove('show');
}

// 添加表单颜色实时拼接
['addColorR', 'addColorG', 'addColorB', 'addColorA'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        const r = document.getElementById('addColorR').value;
        const g = document.getElementById('addColorG').value;
        const b = document.getElementById('addColorB').value;
        const a = document.getElementById('addColorA').value;
        document.getElementById('addColorFinal').value = `${r} ${g} ${b} / ${a}`;
    });
});

function deleteRow(id, name) {
    const confirmation = prompt(`确认删除？请输入「确认删除${name}」`);
    if (confirmation === `确认删除${name}`) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

function editRow(btn) {
    const row = btn.closest('tr');
    const isEditing = row.classList.contains('editing');

    if (isEditing) {
        // 保存模式
        saveRow(row, btn);
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
            const rgb = parts[0].trim().split(' ');
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
        } else if (field === 'icon') {
            const input = document.createElement('input');
            input.type = 'url';
            input.value = currentValue;
            cell.innerHTML = '';
            cell.appendChild(input);
        } else if (field === 'link') {
            const input = document.createElement('input');
            input.type = 'url';
            input.value = currentValue;
            cell.innerHTML = '';
            cell.appendChild(input);
        } else if (field === 'des') {
            const textarea = document.createElement('textarea');
            textarea.value = currentValue;
            textarea.rows = 2;
            cell.innerHTML = '';
            cell.appendChild(textarea);
        } else {
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentValue;
            cell.innerHTML = '';
            cell.appendChild(input);
        }
    });
}

function saveRow(row, btn) {
    const id = row.dataset.id;
    const data = { action: 'update', id };

    // 提取所有字段值
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
            data[field] = `${r} ${g} ${b} / ${a}`;
        } else if (field === 'des') {
            data[field] = cell.querySelector('textarea').value;
        } else {
            data[field] = cell.querySelector('input').value;
        }
    });

    // 提交表单
    const form = document.createElement('form');
    form.method = 'POST';
    for (const [key, value] of Object.entries(data)) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
}

function extractValue(cell, field) {
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
</script>

<style>
.mtl-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.mtl-add-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: rgba(var(--adt-colorBoard-Blue) / var(--adt-colorAlpha-100));
    color: rgba(var(--adt-colorBoard-buttonLabel) / var(--adt-colorAlpha-100));
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.mtl-add-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.mtl-message {
    padding: 16px;
    margin-bottom: 24px;
    background: rgba(var(--adt-colorBoard-Green) / 0.1);
    border-left: 4px solid rgba(var(--adt-colorBoard-Green) / var(--adt-colorAlpha-100));
    border-radius: 8px;
    color: rgba(var(--adt-colorBoard-Label) / var(--adt-colorAlpha-100));
}

.mtl-table-container {
    margin-bottom: 40px;
}

.mtl-table-title {
    font-size: 21px;
    font-weight: 600;
    margin-bottom: 16px;
}

.mtl-table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(var(--adt-colorBoard-Background) / 0.6);
    backdrop-filter: blur(20px);
    border-radius: 16px;
    overflow: hidden;
}

.mtl-table th {
    background: rgba(var(--adt-colorBoard-Label) / 0.05);
    padding: 12px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: rgba(var(--adt-colorBoard-Label) / var(--adt-colorAlpha-50));
}

.mtl-table td {
    padding: 12px;
    border-top: 0.5px solid rgba(var(--adt-colorBoard-Label) / 0.13);
    font-size: 14px;
}

.mtl-table tr.editing td {
    background: rgba(var(--adt-colorBoard-Yellow) / 0.05);
}

.mtl-table input[type="text"],
.mtl-table input[type="url"],
.mtl-table input[type="number"],
.mtl-table textarea,
.mtl-table select {
    width: 100%;
    padding: 6px 8px;
    border: 0.5px solid rgba(var(--adt-colorBoard-Label) / 0.2);
    border-radius: 6px;
    background: transparent;
    color: inherit;
    font-size: 14px;
}

.mtl-color-preview {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: 0.5px solid rgba(var(--adt-colorBoard-Label) / 0.2);
}

.mtl-color-inputs-inline {
    display: flex;
    gap: 4px;
    align-items: center;
}

.mtl-actions {
    display: flex;
    gap: 8px;
}

.mtl-btn {
    padding: 6px 12px;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    background: rgba(var(--adt-colorBoard-Label) / 0.1);
    color: rgba(var(--adt-colorBoard-Label) / var(--adt-colorAlpha-100));
}

.mtl-btn:hover {
    background: rgba(var(--adt-colorBoard-Label) / 0.2);
}

.mtl-btn-edit {
    background: rgba(var(--adt-colorBoard-Blue) / var(--adt-colorAlpha-100));
    color: rgba(var(--adt-colorBoard-buttonLabel) / var(--adt-colorAlpha-100));
}

.mtl-btn-delete {
    background: rgba(var(--adt-colorBoard-Red) / var(--adt-colorAlpha-100));
    color: rgba(var(--adt-colorBoard-buttonLabel) / var(--adt-colorAlpha-100));
}

.mtl-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.mtl-modal.show {
    display: flex;
}

.mtl-modal-content {
    background: rgba(var(--adt-colorBoard-Background) / 1);
    padding: 32px;
    border-radius: 16px;
    max-width: 540px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.mtl-modal h2 {
    margin-bottom: 24px;
}

.mtl-form-group {
    margin-bottom: 20px;
}

.mtl-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 14px;
}

.mtl-form-group input,
.mtl-form-group textarea,
.mtl-form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 0.5px solid rgba(var(--adt-colorBoard-Label) / 0.2);
    border-radius: 8px;
    background: transparent;
    color: inherit;
    font-size: 14px;
}

.mtl-color-inputs {
    display: flex;
    gap: 8px;
    align-items: center;
}

.mtl-color-inputs input {
    flex: 1;
}

.mtl-modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}

.mtl-btn-primary {
    background: rgba(var(--adt-colorBoard-Blue) / var(--adt-colorAlpha-100));
    color: rgba(var(--adt-colorBoard-buttonLabel) / var(--adt-colorAlpha-100));
}
</style>
