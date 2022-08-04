<p align="center"><a href="https://microgg.coding.net/public/microgg/GenshinUID/git/files"><img src="https://img.genshin.minigg.cn/minigg.png" width="256" height="256" alt="GenshinUID"></a></p>
<h1 align="center">GenshinUID-PHP</h1>
<h3 align="center">♾️基于<a href="https://bot.q.qq.com/wiki/develop/api/" target="_blank">QQ官方频道WebSocket API</a>的原神多功能Bot♾️</h3>
<h4 align="center">同时兼容GO-CQHttp/微信可爱猫/Noknok</h4>
<p align="center">
<a href="#" target="_blank">安装文档</a> &nbsp; · &nbsp;
<a href="#" target="_blank">指令列表</a> &nbsp; · &nbsp;
<a href="#">常见问题</a>
</p>


# | 特性

* 支持多框架，一套代码多个平台
* 插件热更新，无需重启进行更新

# | 框架

## 环境
> PHP ≥ 7.3
### 已测试环境
| PHP版本   | 系统                                                  |
|---------|-----------------------------------------------------|
| PHP 8.0 | CentOS 7 / CentOS 8 / Ubuntu 20.04 / WinServer 2022 |
| PHP 7.4 | Windows 10 / Windows 11 / WinServer 2019            |


## 协议

> 回调地址:http://your.domain/app.php?frameId=50000&frameIp=127.0.0.1&frameGc=123456 ，frameId 不填默认 50000

| frameId | 框架                                                                        | 平台           | 鉴权     | HTTP | WS |
|---------|---------------------------------------------------------------------------|--------------|----------|------|----|
| 10000   | [MyPCQQ](https://www.mypcqq.cc)                                           | 电脑QQ         | 白名单IP | ✓    | ✗  |
| 20000   | [可爱猫](http://www.keaimao.com.cn/forum.php)                                | 微信           | 密钥     | ✓    | ✗  |
| 50000   | [NOKNOK 机器人](https://www.noknok.cn)                                       | NOKNOK       | 密钥     | ✓    | ✗  |
| 60000   | [go-cqhttp](https://github.com/Mrs4s/go-cqhttp/blob/master/docs/guild.md) | QQ频道 (GO-CQ) | 密钥     | ✓    | ✓  |
| 70000   | [QQ 机器人](https://bot.q.qq.com/)                                           | QQ频道 (官方)    | 密钥     | ✗    | ✓  |

## 数据

> **-** 表示不确定，且很大概率不行。

| msgType      | MyPCQQ | 可爱猫 | NOKNOK 机器人 | QQ频道 (GO-CQ) | QQ频道 (官方) |
|--------------|--------|--------|---------------|-----------|-----------|
| 文本         | ✓      | ✓      | ✓             | ✓         | ✓         |
| 图片         | ✓      | 本地   | ✓             | ✓         | ✓         |
| at_msg       | ✓      | ✓      | ✓             | ✓         | ✓         |
| reply_msg    | ✗      | ✗      | ✓             | ✓         | 需申请       |
| markdown_msg | ✗      | ✗      | ✓             | ✗         | 需申请       |
| json_msg     | -      | -      | -             | -         | ✓         |
| xml_msg      | ✓      | -      | -             | ✓         | -         |

# 配置

## 数据库

### Redis

> Bot数据缓存，关键词触发、统计都需要用到Redis。

如Redis设置了访问密钥，按 **app/example.config/app.database.php** 内说明修改

## 设置

> ~~**app/example.config** 目录下的文件需要进行配置~~
>
> 通常情况下，除 **app.config.php** 需要把里面的密钥换成自己的，其余配置文件无需额外编辑
>
> 配置完成后，复制 **app/example.config** 目录下所有文件到 **app/config** 目录下即可。

## frameIp

> **保持默认即可**
>
> **HTTP/HTTPS** 通信的IP或域名，可在 **app/config/app.config.php** 中修改`FRAME_IP`

如需外网访问，建议 服务器端防火墙、安全策略组 放行Web通信端口。~~额外放行 **8000-8100** 端口。~~

## frameGc

> **保持默认即可**
>
> `NOKNOK` 和 `QQ频道` 专用参数，填入 **子频道ID** 时，仅响应特定子频道，为空时默认全部子频道可用。

# 使用

## MyPCQQ (相对feng控几率比GO-CQ更低)

> 在MyPCQQ目录下 **Set.ini** 的底下加入以下信息，按照 log 填入白名单 IP，每个空格分开。
>
> 接口说明见 `app.config.php` 注释

```
[tran]
enable=1
target=http://your.domain/app.php?frameId=10000
whitelist=127.0.0.1 119.29.29.29
```

## 可爱猫

> 可爱猫(自动更新到最新版本) [下载地址](https://storage.minigg.cn/可爱猫.zip)。
>
> 接口说明见 `app.config.php` 注释

## NOKNOK

> 可以加入 [KK官方事务所](https://link.noknok.cn/n/7AeQ5y0o) 向管理员申请，由于 NOKNOK 的回调地址不允许带参数。所以 `frameId` 默认值为 NOKNOK 所使用的 `50000`

## QQ频道 (GO-CQHttp)

> 在GO-CQHttp的配置文件 **config.yml** 中HTTP通信部分的 post 的下方加入以下信息：

```
- url: 'http(s)://your.domain/app.php?frameId=60000'
  secret: '' #密钥
```

PS：由于QQ消息&QQ群消息&QQ频道消息的接口不相同+MyPCQQ对于QQ非频道消息更稳定，所以暂时不会考虑添加QQ与QQ群的支持。(应该是不会加了)

## QQ频道 (官方API)

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

## 丨感谢

- [YuanShen_User_Info](https://github.com/Womsxd/YuanShen_User_Info) - 米游社API
- [@KimigaiiWuyi](https://github.com/KimigaiiWuyi) - Wuyi哥哥好优siu
- [@erinilis](https://github.com/yuyumoko) - UID查询卡片设计
- [@Wansn-w](https://github.com/Wansn-w) - IGS图片生成器
- [@猫冬](https://bbs.mihoyo.com/ys/accountCenter/postList?id=74019947) - 原神攻略的**授权**使用
- [@Enka.Network](https://enka.network/) - 展柜面板的数据来源
- [@逍遥](https://github.com/ctrlcvs/xiaoyao_plus) - 新版图鉴图片来自这位大佬

# 总会有地上的生灵，敢于直面雷霆的威光！