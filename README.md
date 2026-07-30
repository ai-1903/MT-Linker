<div align="center">

# MT-Linker

**A friendship Linker for MT-Kit** — 优雅的友情链接管理组件

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-5.0+-green.svg)](https://wordpress.org/)
</div>

---

## ✨ 特性

- 🎨 **现代化 UI**：毛玻璃卡片、空间分隔颜色语法、深色模式
- 🌐 **连接检测**：并发 Ping、四级延迟状态机、19 分钟自动缓存
- 🔐 **多环境认证**：WordPress / 自定义 API / MT-Kit / AirDesign Tools
- 💾 **SQLite 存储**：无感初始化，自动生成鉴权密钥，原子写入
- ✅ **前端校验**：双字节长度计算，域名屏蔽，实时 visual feedback
- ✏️ **行内编辑**：Dashboard 一键编辑/保存/取消，实时校验反馈
- 📱 **响应式设计**：sticky 预览卡片，自适应网格布局
- 🚀 **零配置启动**：`php -S localhost:8080`，同时支持 WordPress 短代码

---

## 📦 安装

### 独立模式

1. 克隆或下载本仓库
2. 复制 `mtklink-config.yml.example` 为 `mtklink-config.yml`
3. 配置认证方式（见下文）
4. 启动内置服务器：

```bash
php -S localhost:8080
```

访问 `http://localhost:8080` 即可预览 Demo。

### WordPress 集成

1. 将 `MT-Linker` 目录放入子主题的 `/AirDesign/` 目录
2. 在子主题 `functions.php` 中添加：

```php
require_once get_stylesheet_directory() . '/AirDesign/MT-Linker/api/wp_connector.php';
```

3. 配置 `mtklink-config.yml` 设置 `type: "wp"`
4. 在页面/文章中使用短代码：

```
[mtklinker-show]     # 链接展示页
[mtlinker-apply]      # 申请友链
[mtlinker-dash]       # 管理面板（需管理员权限）
```

---

## ⚙️ 配置

### mtklink-config.yml

```yaml
# 认证类型：wp | mtk | adt | none
type: "wp"

# type=none 时：自定义 API 鉴权
auth: "https://your-auth-api.example.com/verify"
key: "your-secret-key"

# SQLite 凭据（自动生成，无需手动填写）
sqlite-user: ""
sqlite-key: ""

# 输入校验配置
incheck_focusHTTPS: "1"   # 1=强制 HTTPS
incheck_DOMAIN: "1"       # 1=禁止托管平台域名
incheck_colorAlpha: "1"   # 1=显示透明度控制器
```

### 认证方式

| Type | 说明 | 使用场景 |
|------|------|----------|
| `wp` | WordPress 管理员权限 | 集成到 WordPress 站点 |
| `none` | 自定义 API 鉴权 | 独立部署，使用自己的认证系统 |
| `mtk` | MT-Kit 互联（预留） | 未来与 MT-Kit 主项目互联 |
| `adt` | AirDesign Tools（预留） | 未来与 AirDesign Tools 互联 |

---

## 🎨 界面预览

### 链接展示页 (linker.php)

- **标题**：百界门径
- **统计**：已连通门径 / 银轨通畅 / 单向门径
- **分类**：本星域 / 联通百界 / 泛星域
- **状态**：界域连通 / 通信失联 / 注销星域
- **Ping 检测**：首次访问自动并发检测全部链接，结果缓存 19 分钟
- **延迟状态机**：≤800ms Success / ≤4000ms Warning / >4000ms Caution / Offline Middle

### 申请页面 (apply.php)

- **左侧**：表单输入（名称、描述、链接、图标、类别、颜色）
- **右侧**：sticky 实时预览卡片
- **校验**：失焦时实时检查，非法输入标红/黄并提示

### 管理面板 (dash.php)

- **待审核表**：status=0 的申请
- **已处理表**：分页展示（13 条/页）
- **行内编辑**：点击编辑 → 实时校验 → 保存 / 取消回滚
- **批量操作**：添加链接弹窗、删除确认

---

## 🔒 安全特性

### 数据库安全

- **应用层鉴权**：自动生成 64 字符十六进制密钥
- **文件权限**：数据库文件权限 `0600`（仅所有者读写）
- **原子写入**：配置更新使用 `flock(LOCK_EX)` 防并发

### 输入校验

| 字段 | 规则 |
|------|------|
| Name | 最多 50 字节（≈25 汉字或 50 英文字符） |
| Des | 最多 170 字节（≈85 汉字或 170 英文字符） |
| Link | HTTPS 开头，无尾部 `/`，不含托管域名 |
| Icon | HTTPS 开头，`.jpg/.png/.svg` 结尾 |
| Color | RGB 0-255 / Alpha 0-1 |

**屏蔽域名**：`github.io`, `vercel.app`, `gitee.io`, `netlify.app`, `onrender.com`, `firebaseapp.com`

### XSS 防护

所有输出使用 `esc_html()` / `esc_url()` 转义。

---

## 📊 数据库结构

### Linker 表

| 字段 | 类型 | 说明 |
|------|------|------|
| NO | INTEGER PRIMARY KEY | 自增序列 |
| Name | TEXT | 站点名称 |
| Des | TEXT | 站点描述 |
| type | INTEGER | 0=本星域, 1=联通百界, 3=泛星域 |
| Link | TEXT | 站点链接 |
| icon | TEXT | 图标 URL |
| color | TEXT | 卡片颜色（`R G B / A` 格式） |
| status | INTEGER | 0=审核中, 2=连通, 3=失联, 4=注销, 10=隐藏 |

---

## 🛠️ 开发

### 技术栈

- **后端**：PHP 7.4+, SQLite 3, PDO
- **前端**：原生 JavaScript, CSS Variables
- **样式**：参考 trailblazerUI 设计风格
- **图标**：Iconify

### 项目结构

```
MT-Linker/
├── api/                  # 连接器（独立 PHP / WordPress）
│   ├── connector.php     #   独立 PHP include 方式
│   └── wp_connector.php  #   WordPress 短代码注册
├── css/                  # 样式
│   ├── color-board.css   #   设计令牌 + 亮/暗模式
│   └── mt-linker.css     #   主 UI 样式（卡片/表格/弹窗）
├── func/                 # 核心库
│   ├── core.php          #   配置读写、数据库、鉴权
│   ├── render.php        #   共享渲染 + polyfill（esc_*）
│   └── tpl-apply-form.php#   可复用表单模板
├── js/
│   └── inCheck.js        #   前端校验引擎
├── view/                 # 页面视图
│   ├── linker.php        #   链接展示
│   ├── apply.php         #   申请友链
│   └── dash.php          #   管理面板
├── data/                 # SQLite + 配置（自动生成）
├── index.php             # Demo 入口
└── mtklink-config.yml    # 环境配置
```

### 开发指南

1. 页面文件放入 `/view`
2. 样式使用 `css/color-board.css` 中的变量
3. 输入校验调用 `js/inCheck.js` 中的函数
4. 数据库操作通过 `func/core.php` 中的函数

---

## 🚀 快速开始

### Demo 模式

```bash
git clone https://github.com/ai-1903/MT-Linker.git
cd MT-Linker
php -S localhost:8080
```

访问：
- `http://localhost:8080?page=linker` - 链接展示
- `http://localhost:8080?page=apply` - 申请友链
- `http://localhost:8080?page=dash` - 管理面板

Demo 模式默认具有管理员权限。

### WordPress 集成

```bash
# 1. 进入子主题目录
cd /path/to/wordpress/wp-content/themes/your-child-theme/

# 2. 创建 AirDesign 目录
mkdir -p AirDesign

# 3. 克隆项目
cd AirDesign
git clone https://github.com/ai-1903/MT-Linker.git

# 4. 配置
cd MT-Linker
cp mtklink-config.yml.example mtklink-config.yml
# 编辑 mtklink-config.yml，设置 type: "wp"

# 5. 在 functions.php 中添加
# require_once get_stylesheet_directory() . '/AirDesign/MT-Linker/api/wp_connector.php';
```

---

## 📝 License

MIT License - Copyright (c) 2026 iCerya

---

## 🙏 致谢

- 设计灵感：Apple Human Interface Guidelines
- 样式参考：[trailblazerUI](https://github.com/ai-1903/trailblazerUI)
- 图标库：[Iconify](https://iconify.design/)

---

## 🔗 相关项目

- **MT-Kit**: 多工具集成套件（开发中）
- **AirDesign Tools**: 设计工具集（开发中）
- **trailblazerUI**: 拓星旅迹 UI 组件

---

<p align="center">
  <sub>Made with ❤️ by <a href="https://icerya.com">iCerya</a></sub>
</p>