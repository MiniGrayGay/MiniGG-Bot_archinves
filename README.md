# 特性

* 支持多框架，一套代码多个平台
* 插件热更新，无需重新卸载安装

# 框架

## 协议
> 回调地址:http://your.domain/app.php?frameId=50000&frameIp=127.0.0.1&frameGc=123456 ，frameId 默认值 50000 ，支持HTTP和HTTPS协议。

| frameId | 框架                                                                                                   | 平台    | 鉴权     | HTTP | WS |
|---------|--------------------------------------------------------------------------------------------------------|---------|--------|------|----|
| 10000   | [MyPCQQ](https://www.mypcqq.cc)                                                                        | 电脑 QQ | 白名单IP | ✓    | ✗  |
| 20000   | [可爱猫](http://www.keaimao.com.cn/forum.php)                                                          | 微信    | 密钥     | ✓    | ✗  |
| 50000   | [NOKNOK 机器人](https://www.noknok.cn)                                                                 | NOKNOK  | 密钥     | ✓    | ✗  |
| 60000   | [go-cqhttp](https://github.com/Mrs4s/go-cqhttp/blob/master/docs/guild.md)                              | 手机 QQ | 密钥     | ✓    | ✓  |
| 70000   | [QQ 机器人](https://qun.qq.com/qqweb/qunpro/share?_wv=3&_wwv=128&inviteCode=1d9lY8&from=181074&biz=ka) | QQ 频道 | 密钥     | ✗    | ✓  |

## 数据
> **-** 表示不确定，且很大概率不行

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

## redis

数据缓存，关键词触发、统计都需要他 [点击这里下载](https://redis.io/download)，或自行安装。

## 设置

**example** 文件夹内的配置文件需要自行配置。里面的密钥换成自己的，然后复制到 **config** 即可。

```
app/example.config/app.config.php 內的文件修改完以后复制一份到 app/config

以下4个文件一般情况下无需修改，直接复制到 app/config 即可
app/example.config/app.config.json
app/example.config/msg.blockList.txt
app/example.config/msg.whiteList.txt
app/example.config/user.blockList.txt
```

app/database/example.app.sql.php 为数据库配置
通常情况下直接复制并重命名为 app/database/app.sql.php 即可

## frameIp
> **HTTP** 转发回去的IP，默认为 **app/config/app.config.php** 中配置的IP

如需外网访问，建议 服务器端防火墙、安全策略组 放通 **8000-8100** 端口。

## frameGc

NOKNOK 和 QQ 频道 请填 **子频道ID** ，不填默认全部处理。

# 使用

## MyPCQQ

根目录下 **Set.ini** 的底下加入以下信息，按照 log 填入白名单 IP，每个空格分开。

```
[tran]
enable=1
target=http://your.domain/app.php?frameId=10000
whitelist=127.0.0.1 1.1.1.1
```

## 可爱猫

百度网盘 [链接挂了，稍后补链](#)。

## NOKNOK

找管理员申请，需要注意的是 NOKNOK 的回调地址不允许带参数。

```
无需填写配置文件，提交 http://your.domain/app.php 给管理员
```

## QQ 频道 - 第三方

在go-cqhttp的 **config.yml** post 的下方加入以下信息:

```
- url: 'http://your.domain/app.php?frameId=60000'
  secret: '' #密钥
```

## QQ 频道 - 官方

修改 app/ws/example.qq_ws.js 文件，复制并重命名为 app/ws/qq_ws.js

执行以下命令安装依赖并运行:

```
yarn

yarn start:qq
```

# 写在最后

环境密钥配置好以后需要 **管理员** 向机器人发送 **功能** 初始化插件，之后每次增删插件也需要。

只有初始化过的命令才会调用相关插件，避免都轮询所有插件影响速度。

## 注意

路径需要有写入、读取权限，否则【缓存】、【发图】相关功能受到影响。

如果有两个相似的命令 (比如 **一言状态** 和 **一言** )，建议长的放短的前面，否则调用时会先匹配到短的关键词上。
