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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                $id = intval($_POST['id']);
                $db->exec("DELETE FROM Linker WHERE NO = $id");
                break;

            case 'update':
                $id = intval($_POST['id']);
                $stmt = $db->prepare("UPDATE Linker SET Name=?, Des=?, type=?, Link=?, icon=?, color=?, status=? WHERE NO=?");
                $stmt->execute([
                    $_POST['name'],
                    $_POST['des'],
                    intval($_POST['type']),
                    $_POST['link'],
                    $_POST['icon'],
                    $_POST['color'],
                    intval($_POST['status']),
                    $id
                ]);
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
        <h1 class="mtl-title">管理面板</h1>

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
                        <tr>
                            <td><?php echo $item['NO']; ?></td>
                            <td><?php echo esc_html($item['Name']); ?></td>
                            <td><?php echo esc_html($item['Des']); ?></td>
                            <td><?php echo $typeOptions[$item['type']]; ?></td>
                            <td><a href="<?php echo esc_url($item['Link']); ?>" target="_blank">查看</a></td>
                            <td><img src="<?php echo esc_url($item['icon']); ?>" width="24" height="24"></td>
                            <td><div style="width:24px;height:24px;background:rgba(<?php echo $item['color']; ?>);border-radius:4px;"></div></td>
                            <td><?php echo $statusOptions[$item['status']]; ?></td>
                            <td>
                                <button class="mtl-btn mtl-btn-primary" onclick="editRow(<?php echo $item['NO']; ?>)">编辑</button>
                                <button class="mtl-btn mtl-btn-danger" onclick="deleteRow(<?php echo $item['NO']; ?>, '<?php echo esc_html($item['Name']); ?>')">删除</button>
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
                        <tr>
                            <td><?php echo $item['NO']; ?></td>
                            <td><?php echo esc_html($item['Name']); ?></td>
                            <td><?php echo esc_html($item['Des']); ?></td>
                            <td><?php echo $typeOptions[$item['type']]; ?></td>
                            <td><a href="<?php echo esc_url($item['Link']); ?>" target="_blank">查看</a></td>
                            <td><img src="<?php echo esc_url($item['icon']); ?>" width="24" height="24"></td>
                            <td><div style="width:24px;height:24px;background:rgba(<?php echo $item['color']; ?>);border-radius:4px;"></div></td>
                            <td><?php echo $statusOptions[$item['status']]; ?></td>
                            <td>
                                <button class="mtl-btn mtl-btn-primary" onclick="editRow(<?php echo $item['NO']; ?>)">编辑</button>
                                <button class="mtl-btn mtl-btn-danger" onclick="deleteRow(<?php echo $item['NO']; ?>, '<?php echo esc_html($item['Name']); ?>')">删除</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
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

function editRow(id) {
    alert('编辑功能待实现：将支持行内编辑和实时校验');
}
</script>
