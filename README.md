# xiaohuanxiong

开源有态度的漫画 CMS

# 程序特色

- 完善的会员系统，带支付系统，带 VIP 功能，带推广功能
- 完善的 APP 接口
- 完善的火车头 API
- 阅读页可以选择一页几图，为网站增加 PV
- 章节预加载，在阅读下一章时不需要再等待载入

# 最新 5.0 版本介绍

- 三端并存：PC 端，移动端，MIP 端，MIP 端可以跳转到移动端的阅读页，解决 MIP 不能放广告的问题（MIP 做引流，移动端做真正变现）。
- 后台操作比 3.0 更流畅。
- 前台一些列表页面使用 vue 上拉加载，用户体验更好
- 5.0 不开源，需要的话请加 QQ 群咨询，群号在下面。

# 关于

- 官方论坛：http://xhxcms.com
- 文档地址：https://www.kancloud.cn/hiliqi/xwx_comic_cms
- 官方 QQ 群：780362399

# AI 解析项目资料，仅供参考

## 项目结构

```
.DS_Store
.gitattributes
.htaccess
.idea/
	encodings.xml
	hanman.iml
	inspectionProfiles/
	misc.xml
	modules.xml
	php.xml
	vcs.xml
	workspace.xml
	xiaohuanxiong.iml
使用说明.url
application/
	.DS_Store
	admin/
	api/
	app/
	command.php
	common.php
	index/
	install/
	model/
	pay/
	provider.php
	service/
	tags.php
	ucenter/
build.php
composer.json
composer.lock
config/
	app.php
	cache.php
	console.php
	...
extend/
	...
LICENSE.txt
nginx.htaccess
public/
README.md
route/
runtime/
think
thinkphp/
vendor/
```

## 使用方式

### 安装

使用 Composer 安装：

```sh
composer create-project topthink/think tp
```

启动服务：

```sh
cd tp
php think run
```

然后在浏览器中访问：

```sh
http://localhost:8000
```

更新框架：

```sh
composer update topthink/framework
```

## API 接口文档

### 获取书籍详情

**URL:** `/api/read/book`

**方法:** `GET`

**参数:**

- `api_key` (string): API 密钥
- `bid` (int): 书籍 ID

**响应:**

```json
{
  "err": 0,
  "book": {
    "id": 1,
    "title": "书名",
    "author": "作者",
    "description": "书籍描述",
    "cover": "封面图片链接"
  }
}
```

### 搜索书籍

**URL:** `/api/read/search`

**方法:** `GET`

**参数:**

- `api_key` (string): API 密钥
- `keyword` (string): 搜索关键词

**响应:**

```json
{
    "err": 0,
    "books": [
        {
            "id": 1,
            "title": "书名",
            "author": "作者"
        },
        ...
    ]
}
```

### 用户登录

**URL:** `/api/user/login`

**方法:** `POST`

**参数:**

- `username` (string): 用户名
- `password` (string): 密码

**响应:**

```json
{
  "err": 0,
  "token": "用户登录令牌"
}
```

### 用户注册

**URL:** `/api/user/register`

**方法:** `POST`

**参数:**

- `username` (string): 用户名
- `password` (string): 密码
- `email` (string): 邮箱

**响应:**

```json
{
  "err": 0,
  "msg": "注册成功"
}
```

### 获取用户信息

**URL:** `/api/user/info`

**方法:** `GET`

**参数:**

- `api_key` (string): API 密钥
- `token` (string): 用户登录令牌

**响应:**

```json
{
  "err": 0,
  "user": {
    "id": 1,
    "username": "用户名",
    "email": "邮箱"
  }
}
```

# 开源协议

本程序采用 MIT 协议开源

# 免责声明

小浣熊漫画 cms 是一款不以盈利为目的的开源漫画 cms 系统。程序的著作权均归作者所有，用户具有自由的使用权。
如果用户下载、安装、使用本系统，即表明用户信任该系统。那么，用户在使用本系统时造成对用户自己或他人任何形式的损失和伤害，作者不承担任何责任。
本系统只提供做漫画系统最基本的功能和程序，未提供任何可以让使用者违法使用、牟利（如侵权盗版、涉黄、非法采集他人数据等）的功能。用户使用本系统从事任何违法违规的事情，一切后果由用户自行承担，作者不承担任何责任。

# 许可声明

下载、安装和使用：本系统永久免费，不会盈利，用户可以无限制次数下载、安装本系统。
复制、分发和传播：用户可以无限制次数复制、分发和传播本系统。但必须保证复制、分发和传播的程序的完整性和真实性，需包括所有有关本系统的软件、电子文档, 版权和商标及本协议等。

# 使用声明

本系统不含有任何旨在破坏用户计算机数据和获取用户隐私信息的恶意代码；不含有任何跟踪、监视用户计算机功能的代码；不含有监控用户网上、网下行为的功能；不含有收集用户的其它软件、文档中包含的个人信息的功能；不会泄漏用户隐私。
本系统唯一官方下载途径就是 GitHub，对于用户从官方途径下载的系统以及从非作者发行的介质上获得的系统，作者无法保证其是否感染计算机病毒、是否隐藏有伪装的特洛伊木马程序或者黑客软件。用户使用此类软件，将可能导致不可预测的风险，建议用户不要轻易下载、安装、使用。作者不承担由此产生的一切法律责任。
用户不得利用本系统误导、欺骗他人;不得故意避开或者破坏作者为保护本系统著作权而采取的技术措施。
