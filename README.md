# 特性

* 支持多框架，一套代码多个平台
* 插件热更新，无需重新卸载安装
* 全局管理器，简单配置即可上手

# 框架

## 环境

已在`Windows11`、`Windows Server 2019`、`Centos 8.5.2111`上使用`PHP 7.4`与`PHP 8.0`进行测试。

理论上兼容`PHP 7.3`及以上的版本，推荐使用`PHP 8.0`进行部署

## 协议

> 回调地址:http://your.domain/app.php?frameId=50000&frameIp=127.0.0.1&frameGc=123456 ，frameId 不填默认 50000

| frameId | 框架                                                                        | 平台     | 鉴权     | HTTP | WS |
|---------|---------------------------------------------------------------------------|----------|----------|------|----|
| 5000    | [小米小爱开放平台](https://developers.xiaoai.mi.com)                              | 小爱音响 | -        | ✓    | ✗  |
| 10000   | [MyPCQQ](https://www.mypcqq.cc)                                           | 电脑 QQ  | 白名单IP | ✓    | ✗  |
| 20000   | [可爱猫](http://www.keaimao.com.cn/forum.php)                                | 微信     | 密钥     | ✓    | ✗  |
| 50000   | [NOKNOK 机器人](https://www.noknok.cn)                                       | NOKNOK   | 密钥     | ✓    | ✗  |
| 60000   | [go-cqhttp](https://github.com/Mrs4s/go-cqhttp/blob/master/docs/guild.md) | 手机 QQ  | 密钥     | ✓    | ✓  |
| 70000   | [QQ 机器人](https://bot.q.qq.com/)                                           | QQ 频道  | 密钥     | ✗    | ✓  |

## 数据

> **-** 表示不确定，且很大概率不行，

> **小爱音响** 只支持语音文本回复（文字识别率有点感人）

| msgType      | MyPCQQ | 可爱猫 | NOKNOK 机器人 | go-cqhttp | QQ 机器人 |
|--------------|--------|--------|---------------|-----------|-----------|
| 文本         | ✓      | ✓      | ✓             | ✓         | ✓         |
| 图片         | ✓      | 本地   | ✗             | ✓         | ✓         |
| at_msg       | ✓      | ✓      | ✓             | ✓         | ✓         |
| reply_msg    | ✗      | ✗      | ✓             | ✓         | ✗         |
| markdown_msg | ✗      | ✗      | ✓             | ✗         | ✗         |
| json_msg     | -      | -      | -             | -         | ✓         |
| xml_msg      | ✓      | -      | -             | ✓         | -         |

# 配置

## 数据库

### Redis

> Bot数据缓存，关键词触发、统计都需要用到 [下载地址](https://redis.io/download)。

如Redis设置了访问密钥，按 **app/example.config/app.database.php** 内说明修改

### ~~MySQL/MariaDB~~

> ~~暂未使用的预留配置文件，不需要进行配置。~~

## 项目

> https://github.com/MiniGrayGay/PaimonUID

## 密钥

> **app/example.config** 目录下的文件需要进行配置
>
>通常情况下，除 **app.config.php** 需要把里面的密钥换成自己的，其余配置文件无需额外编辑
>
>配置完成后，复制 **app/example.config** 目录下所有文件到 **app/config** 目录下即可。

## frameIp

> **HTTP/HTTPS** 通信的IP或域名，可在 **app/config/app.config.php** 中修改`FRAME_IP`

如需外网访问，建议 服务器端防火墙、安全策略组 放行Web通信端口外，额外放行 **8000-8100** 端口。

## frameGc

> `NOKNOK` 和 `QQ频道` 专用参数，填入 **子频道ID** 时，仅响应特定子频道，为空时默认全部子频道可用。

# 使用

## 小爱音响 `测试中`

> [创建技能](https://developers.xiaoai.mi.com/skills/create/list) -> 编辑技能 -> 配置服务 -> 配置信息，按提示配置即可

## MyPCQQ (相对feng控几率比GO-CQ更低)

> 在MyPCQQ目录下 **Set.ini** 的底下加入以下信息，按照 log 填入白名单 IP，每个空格分开。

```
[tran]
enable=1
target=http://your.domain/app.php?frameId=10000
whitelist=127.0.0.1 119.29.29.29
```

## 可爱猫

> 可爱猫5.1.7(自动更新到最新版本) [下载地址](https://storage.minigg.cn/可爱猫.zip)。

## NOKNOK

> 需要向管理员申请，由于 NOKNOK 的回调地址不允许带参数。所以 `frameId` 为 NOKNOK的 `50000`

## QQ 频道 (GO-CQHttp)

> 在GO-CQHttp的配置文件 **config.yml** 中HTTP通信部分的 post 的下方加入以下信息：

```
- url: 'http(s)://your.domain/app.php?frameId=60000'
  secret: '' #密钥
```

PS：由于QQ消息&QQ群消息&QQ频道消息的接口不相同+MyPCQQ对于QQ非频道消息更稳定，所以暂时不会考虑添加QQ与QQ群的支持。(应该是不会加了)

## QQ 频道 (官方API)

> 在插件目录下执行以下命令安装依赖并运行（需要`Nodejs 12+`，Linux下推荐使用`screen`保持运行）:

```
npm install -g yarn

yarn

yarn start:qq
```

# 写在最后

> Bot配置完成以后及每次增删插件时需要 **管理员** 向机器人发送 `功能` 初始化插件，修改插件不涉及触发的命令时可以不用重新初始化。

PS：只有初始化过的命令才能使用，避免全部命令都使用轮询，提升运行速度。

## 注意

路径需要有写入、读取权限，否则【缓存】、【发图】相关功能受到影响。

如果有两个相似的命令 (比如 **一言状态** 和 **一言** )，建议长的放短的前面，否则调用次数的统计可能会统计到先匹配到的关键词上。

# 总会有地上的生灵，敢于直面雷霆的威光！