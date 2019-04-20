## PHPOpenCV Installer


First, download the PHPOpenCV installer using Composer:

```bash
composer global require "phpopencv/installer"
```

Make sure to place composer's system-wide vendor bin directory in your `$PATH` so the laravel executable can be located by your system. This directory exists in different locations based on your operating system; however, some common locations include:

- macOS: `$HOME/.composer/vendor/bin`
- GNU / Linux Distributions: `$HOME/.config/composer/vendor/bin`
- Windows: `%USERPROFILE%\AppData\Roaming\Composer\vendor\bin`


修改`/etc/profile`文件

```bash
vim /etc/profile
```

修改`$PATH`变量

```bash
export PATH=$HOME/.config/composer/vendor/bin:$PATH
```

更新缓存

```bash
source /etc/profile
```



Use:

```bash
phpopencv install
```


## 操作系统支持



| <img src="/images/ubuntu.png" width="48px" height="48px" alt="Chrome logo"> | <img src="/images/centos.jpg" width="48px" height="48px" alt="Edge logo"> |
|:---:|:---:|
| 16+ ✔ | 7+ ✔ |
