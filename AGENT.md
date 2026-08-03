# MT-Linker Agent Documentation

## 项目概览

MT-Linker 是一个友情链接管理组件，支持 WordPress 短代码集成与独立 PHP Demo 双模式运行。

---

## 架构设计

### 目录结构

```
MT-Linker/
├── api/                      # API 连接器
│   ├── connector.php         # 独立 PHP 连接器（定义 MT_LINKER_BASE_URL）
│   └── wp_connector.php      # WordPress 连接器 + 短代码 + admin-post 路由
├── css/                      # 样式文件
│   ├── color-board.css       # 调色盘变量（亮/暗双模式）
│   ├── mt-linker.css         # 主 UI 样式
│   └── wp-fix.css            # WP 主题样式强制覆盖（!important 锁定）
├── data/                     # 数据存储（git 忽略）
│   ├── mtlinker.db           # SQLite 数据库（自动生成）
│   └── mt-linker.json        # JSON 配置（遗留）
├── func/                     # 核心功能库
│   ├── core.php              # 核心库（配置、数据库、鉴权、原子写入）
│   ├── render.php            # 共享渲染函数 + esc_* polyfill
│   └── tpl-apply-form.php    # 可复用表单模板（apply.php + dash 弹窗共享）
├── js/                       # JavaScript
│   └── inCheck.js            # 前端校验引擎（MTLinkerValidation 命名空间）
├── view/                     # 页面视图
│   ├── linker.php            # 链接展示页 [mtklinker-show]
│   ├── apply.php             # 申请页面 [mtlinker-apply]
│   └── dash.php              # 管理面板 [mtlinker-dash]
├── img/                      # 图片资源
├── index.php                 # Demo 入口（默认管理员权限）
├── mtklink-config.yml        # 环境配置（git 忽略）
└── mtklink-config.yml.example # 配置模板
```

---

## 核心功能

### 1. 多环境认证支持

| Type | 说明 | 验证方式 |
|------|------|----------|
| `wp` | WordPress | `is_user_logged_in()` + `current_user_can('manage_options')` |
| `none` | 自定义 API | HTTP Header `X-MTLINKER-KEY` 或 Cookie `mtlinker_key` |
| `mtk` | MT-Kit 互联 | 预留接口 |
| `adt` | AirDesign Tools | 预留接口 |

### 2. SQLite 数据库

#### Linker 表结构

| 字段 | 类型 | 说明 |
|------|------|------|
| NO | INTEGER PRIMARY KEY | 自增序列 |
| Name | TEXT | 站点名称 |
| Des | TEXT | 站点描述 |
| type | INTEGER | 类型（0=本星域, 1=联通百界, 3=泛星域） |
| Link | TEXT | 站点链接 |
| icon | TEXT | 图标 URL |
| color | TEXT | 卡片颜色（`R G B / A` 空格分隔格式） |
| status | INTEGER | 状态（0=审核中, 2=连通, 3=失联, 4=注销, 10=隐藏） |

#### 安全特性

- **应用层鉴权**: 自动生成 64 字符十六进制 `sqlite-user` / `sqlite-key`
- **文件权限**: 数据库文件 `chmod 0600`（仅所有者读写）
- **原子写入**: 配置更新使用 `flock(LOCK_EX)` 防并发

### 3. 输入校验（inCheck.js）

| 字段 | 验证规则 | 配置项 |
|------|----------|--------|
| Name | ≤50 字节（中文 ×2，英文 ×1） | - |
| Des | ≤170 字节 | - |
| Link | HTTPS 开头，无尾部 `/`，不含托管域名 | `incheck_focusHTTPS`, `incheck_DOMAIN` |
| Icon | HTTPS 开头，`.jpg/.jpeg/.png/.svg/.webp` 结尾（大小写不敏感） | `incheck_focusHTTPS` |
| Color | RGB 0-255，Alpha 0-1 | `incheck_colorAlpha` |

**屏蔽域名列表**: `github.io`, `vercel.app`, `gitee.io`, `netlify.app`, `onrender.com`, `firebaseapp.com`

---

## WordPress 集成

### 安装步骤

1. 将 `MT-Linker` 目录放入子主题 `/AirDesign/` 目录
2. 在 `functions.php` 中添加：
```php
require_once get_stylesheet_directory() . '/AirDesign/MT-Linker/api/wp_connector.php';
```
3. 配置 `mtklink-config.yml` 设置 `type: "wp"`

### 短代码

| 短代码 | 功能 | 权限要求 |
|--------|------|----------|
| `[mtklinker-show]` | 链接展示页 | 无 |
| `[mtlinker-apply]` | 申请友链 | 无 |
| `[mtlinker-dash]` | 管理面板 | 需管理员 |

### WordPress 表单路由

WP 模式下所有 POST 表单通过 `admin-post.php` + PRG 模式处理：

- 表单 `action` 动态指向 `admin_url('admin-post.php')`
- 内部 action 参数重命名为 `mtl_action`（避免与 WP 核心 `action` 冲突）
- `wp_connector.php` 注册两个 admin-post 钩子：
  - `admin_post_nopriv_mtl_submit_apply` / `admin_post_mtl_submit_apply` → 申请表单（公开）
  - `admin_post_mtl_submit_dash` → 仪表板操作（需登录 + 权限验证）
- 处理后 `wp_safe_redirect(wp_get_referer())` 回到原页面

### WordPress 主题样式隔离

`css/wp-fix.css` 以 `!important` 将全部 UI 锁定在 `.mt-linker-page-wrapper` 作用域，覆盖范围：

- 表单元素：input / textarea / select / button（border、background、radius、font、padding、appearance）
- Apply 布局：容器 grid、sticky 预览卡片、form-group、label、hint
- Dashboard 表格：容器背景、表头/单元格/hover/编辑行、固定列、分页器
- 链接卡片：mtl-card 全链路（容器、`::after` 伪元素、hover/离线态、暗色模式）
- Hero / Section 标题、统计卡片

---

## Demo 模式

`index.php` 默认开启 Demo 模式（`MTLINKER_DEMO_MODE = true`），具有管理员权限。

启动方式：
```bash
php -S localhost:8080
```

访问：
- `?page=linker` → 链接展示
- `?page=apply` → 申请友链
- `?page=dash` → 管理面板

---

## 样式系统

### 深色模式

所有颜色变量引用 `css/color-board.css`：

```css
--adt-colorBoard-Label         # 文本颜色（自动适配深色）
--adt-colorBoard-Blue          # 主题色
--adt-colorBoard-Green/Red/Yellow # 状态色
--adt-colorBoard-Error/Warning/Caution/Middle # 语义色
--adt-colorAlpha-100/85/65/50/13 # 透明度阶梯
```

### 链接卡片设计

- **容器**：`min-width: 320px`，毛玻璃背景（`backdrop-filter: blur(40px)`），24px 圆角
- **图标**：`::after` 伪元素占右侧 50%，`mask-image` 渐变淡化遮罩
  - 默认：opacity 25%（暗色主题 19%）+ `filter: saturate(0.6)`
  - Hover：opacity 100% + saturate 1，卡片 lift 4px + box-shadow
- **离线卡片**（status=3 通信失联 / status=4 注销星域）：
  - 添加 `.status-offline` class，`pointer-events: none` 不可点击
  - `::after` saturate 恒为 0，hover 不恢复
- **网格**：`auto-fill, minmax(280px, 1fr)` → 768px+ 升至 320px → 1200px+ 三列固定
- **边框颜色**：基于 `--mtl-color-args` CSS 变量，仅在 hover 时显示

### WP 样式隔离

`wp-fix.css` 作为 `wp_enqueue_style` 依赖链最后一环（`adt-wp-fix`），依赖 `['adt-color-board', 'adt-mt-linker-style']`，版本号 `1.1.0`。仅 WP 模式加载，独立 Demo 不受影响。

---

## 开发注意事项

### 文件规范

- 页面放入 `/view`，表单模板放入 `/func`
- 配置示例放入项目根目录（`mtklink-config.yml.example`），实际配置 git 忽略
- 不在根目录放置除 `index.php` / `README.md` / `LICENSE` 之外的非配置文件

### Git 提交规范

- 禁止添加 `Co-Authored-By`、`Signed-off-by` 等作者元信息
- 全局 `commit-msg` hook：`~/.git-hooks/commit-msg` 拦截含 Co-Authored-By 的提交
- 使用 SSH ED25519 密钥签名，`git config commit.gpgsign true`
- 提交信息遵循 Conventional Commits

### 安全检查清单

- [ ] `mtklink-config.yml` 已在 `.gitignore`
- [ ] 数据库文件权限为 `0600`
- [ ] 输入校验已启用（`incheck_focusHTTPS` / `incheck_DOMAIN`）
- [ ] XSS 防护（`esc_html()` / `esc_url()` / `esc_js()`）
- [ ] 管理员权限验证（`mtlinker_verify_auth()`）
- [ ] WordPress 模式 admin-post 钩子已注册

---

## 已知问题 & TODO

### 待实现功能

- [ ] 批量操作（批量审核、批量删除）
- [ ] 多条件搜索与排序
- [ ] API 导出（JSON/RSS）

### 性能优化

- [ ] 数据库查询结果缓存
- [ ] CSS/JS 压缩与合并
- [ ] 图标懒加载

---

## License

MIT License - Copyright (c) 2026 iCerya
