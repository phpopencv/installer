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



Once installed, the PHPOpenCV new command will create a fresh PHPOpenCV installation in the directory you specify. For instance, laravel new blog will create a directory named blog containing a fresh Laravel installation with all of Laravel's dependencies already installed:

```bash
phpopencv install
```