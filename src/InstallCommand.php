<?php

namespace CV\Installer\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{

    const EXTENSION_NAME = 'opencv';
    const OPENCV_VERSION = '4.0.0';

    protected $installInfo = [];

    protected $isRoot = false;
    protected $systemUsername;


    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('install')
            ->setDescription('Automatic installation of the php-opencv extension, including automatic installation of opencv (if no opencv is installed)')
//            ->addArgument('opencv_build_path', InputArgument::OPTIONAL, 'Automatically install the opencv directory', '/opt/opencv')
//            ->addArgument('version', InputArgument::OPTIONAL, 'Automatically install the opencv directory', '/opt/opencv')
//            ->addOption('dev', null, InputOption::VALUE_NONE, 'Installs the latest "development" release')
//            ->addOption('edition', 'e', InputOption::VALUE_REQUIRED, 'Automatically install the opencv directory', '/opt/opencv')//            ->addOption('php-opencv-version', 'pov', InputOption::VALUE_NONE, 'Specify the installed php-opencv version')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Automatically install the opencv directory', '/opt/phpopencv')//            ->addOption('php-opencv-version', 'pov', InputOption::VALUE_NONE, 'Specify the installed php-opencv version')
//            ->addOption('enable-contrib', null, InputOption::VALUE_REQUIRED, 'Automatically install the opencv directory', false)//            ->addOption('php-opencv-version', 'pov', InputOption::VALUE_NONE, 'Specify the installed php-opencv version')
        ;
    }


    protected function checkIsRoot()
    {
        $process = new Process(['whoami']);
        try {
            $process->mustRun();
            $username = str_replace(PHP_EOL, '', $process->getOutput());
            if ($username == 'root') {
                $this->isRoot = true;
            }
            $this->systemUsername = $username;
        } catch (\Exception $e) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }


    /**
     * 检测安装环境
     * @author hihozhou
     * @throws RuntimeException
     */
    protected function buildEnvDetection()
    {
        $shellPath = __DIR__ . '/../opencv-install-environment-detection.sh';
        $process = new Process([$shellPath]);//todo 给予当前用户
        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }

    /**
     * 克隆OpenCV项目
     * @author hihozhou
     *
     * @param string $directory
     */
    protected function cloneOpenCV(string $directory)
    {

        //检测OpenCV是否已经存在，如果已经存在则过滤
        if (file_exists($directory . '/opencv')) {
            //todo 切回到某个版本
            return;
        }
        $version = self::OPENCV_VERSION;
        $opencvUrl = 'https://github.com/opencv/opencv.git';
        $command = "git clone {$opencvUrl} --branch {$version} --depth 1";
        $process = new Process($command, $directory, null, null, null);//给予当前用户
        $process->setTty(Process::isTtySupported());//检查TTY支持
        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException('Aborting.');
        }
    }


    /**
     * 克隆opencv_contrib项目
     * @author hihozhou
     *
     * @param string $directory
     */
    protected function cloneOpenCVContrib(string $directory)
    {

        //检测OpenCV是否已经存在，如果已经存在则过滤
        if (file_exists($directory . '/opencv_contrib')) {
            //todo 切回到某个版本
            return;
        }
        $version = self::OPENCV_VERSION;
        $opencvContribUrl = 'https://github.com/opencv/opencv_contrib.git';
        $command = "git clone {$opencvContribUrl} --branch {$version} --depth 1";
        $process = new Process($command, $directory, null, null, null);//给予当前用户
        $process->setTty(Process::isTtySupported());//检查TTY支持
        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException('Aborting.');
        }
    }


    /**
     * 检测OpenCV是否已经安装
     * @author hihozhou
     *
     * @param OutputInterface $output
     */
    protected function findExistOpenCV(OutputInterface $output)
    {
        $output->writeln('Try to find the installed OpenCV on the system via pkg-config...');
        $process = new Process('pkg-config --modversion opencv4');//给予当前用户
        try {
            $process->mustRun();
            $existOpencvVersion = $process->getOutput();
            $output->writeln('Found, opencv version is ' . $existOpencvVersion);
            return true;

        } catch (\Exception $e) {
            //没有检测到opencv
            $output->writeln('Did not find opencv installed on the system.');
            return false;
        }
    }


    protected function checkExtensionIsInstall(OutputInterface $output)
    {
        //判断是否已经安装opencv扩展
        if (extension_loaded(self::EXTENSION_NAME)) {
            $process = new Process(['php', '--ri', self::EXTENSION_NAME]);
            $process->mustRun();
            $output->writeln($process->getOutput());
            throw new RuntimeException('The OpenCV PHP extension is installed.');
        }
    }

    protected function createBaseDir($directory, OutputInterface $output)
    {

        //提示安装目录
        $output->writeln("Compile the directory of opencv with {$directory}.");
        //
        if (!file_exists($directory)) {
            $output->writeln("Create {$directory} of the directory");
            if ($this->isRoot) {
                $process = new Process(['mkdir', $directory]);
            } else {
                $process = new Process(['sudo', 'mkdir', $directory]);
            }
            try {
                $process->mustRun();
            } catch (\Exception $e) {
                throw new RuntimeException($process->getErrorOutput());
            }

        }

        //如果不是root用户，则赋予目录当前用户
        if (!$this->isRoot) {
            try {
                $groupsCommand = 'groups ' . $this->systemUsername;
                $process = new Process($groupsCommand);
                $process->mustRun();
                $str = str_replace(PHP_EOL, '', $process->getOutput());
                if (substr_count($str, $this->systemUsername) >= 2) {
                    $chownCommand = 'sudo chown -R ' . $this->systemUsername . ':' . $this->systemUsername . ' ' . $directory;
                } else {
                    $chownCommand = 'sudo chown -R ' . $this->systemUsername . ' ' . $directory;
                }
                $process = new Process($chownCommand);
                $process->mustRun();
            } catch (\Exception $e) {
                throw new RuntimeException($process->getErrorOutput());
            }
            //给予当前用户
        }
    }


    /**
     * 编译安装opencv
     * @author hihozhou
     *
     * @param $directory
     */
    public function buildOpenCV($directory)
    {
        if (!file_exists($directory)) {
            $process = new Process(['mkdir', 'build'], $directory . '/opencv');
            try {
                $process->mustRun();
            } catch (\Exception $e) {
                throw new RuntimeException($process->getErrorOutput());
            }
        }

        $cmakeCommand = 'cmake -D CMAKE_BUILD_TYPE=RELEASE';
        $cmakeCommand .= ' -D CMAKE_INSTALL_PREFIX=/usr/local';
        $cmakeCommand .= ' -D WITH_TBB=ON';
        $cmakeCommand .= ' -D WITH_V4L=ON';
        $cmakeCommand .= ' -D INSTALL_C_EXAMPLES=OFF';
        $cmakeCommand .= ' -D INSTALL_PYTHON_EXAMPLES=OFF';
        $cmakeCommand .= ' -D BUILD_EXAMPLES=OFF';
        $cmakeCommand .= ' -D BUILD_JAVA=OFF';
        $cmakeCommand .= ' -D BUILD_TESTS=OFF';
        $cmakeCommand .= ' -D WITH_QT=ON';
        $cmakeCommand .= ' -D WITH_OPENGL=ON';
        $cmakeCommand .= ' -D OPENCV_PYTHON_SKIP_DETECTION=ON';
        $cmakeCommand .= ' -D OPENCV_GENERATE_PKGCONFIG=ON';
        $cmakeCommand .= ' -D OPENCV_EXTRA_MODULES_PATH=../../opencv_contrib/modules';
        $cmakeCommand .= ' ..';
        //编译安装
        $commands = [
            'cd opencv/build',
            $cmakeCommand,
            'make',
            'sudo make install',
            'sudo sh -c \'echo "/usr/local/lib" > /etc/ld.so.conf.d/opencv.conf\'',
            'sudo ldconfig'
        ];
        $process = new Process(implode(' && ', $commands), $directory, null, null, null);
        $process->setTty(Process::isTtySupported());//检查TTY支持
        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException('Aborting.');
        }
    }

    protected function buildPHPOpenCV($directory)
    {

        if (!file_exists($directory . '/php-opencv')) {
            $phpOpencvUrl = 'https://github.com/hihozhou/php-opencv.git';
            $command = "git clone {$phpOpencvUrl} --branch master --depth 1";
            $process = new Process($command, $directory, null, null, null);
            $process->setTty(Process::isTtySupported());//检查TTY支持
            try {
                $process->mustRun();
            } catch (\Exception $e) {
                throw new RuntimeException('Aborting.');
            }
        }

        try {
            $commands = [
                'cd php-opencv',
                'phpize',//todo
                './configure --with-php-config=/usr/bin/php-config',//todo
                'make',
                'sudo make install'
            ];
            $process = new Process(implode(' && ', $commands), $directory, null, null, null);
            $process->setTty(Process::isTtySupported());//检查TTY支持
            $process->mustRun();

        } catch (\Exception $e) {
            throw new RuntimeException('Aborting.');
        }
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        'enable sockets supports? [no] : ';
        //查找php配置
        try {
            $process = new Process('which phpize');
            $process->mustRun();
            $phpizePath = str_replace(PHP_EOL, '', $process->getOutput());
            $process = new Process('which php-config');
            $process->mustRun();
            $phpConfigPath = str_replace(PHP_EOL, '', $process->getOutput());

        } catch (\Exception $e) {
            throw new RuntimeException($process->getErrorOutput());
        }
        exit;
        $output->writeln('<comment>Application ready! Build something amazing.</comment>');
        $output->write('<comment>Application ready! Build something amazing.</comment>');
        $this->userInput();
        exit;
        $this->checkIsRoot();
        $this->checkExtensionIsInstall($output);
        $this->buildEnvDetection();
        //创建目录
        $directory = $input->getOption('path');
        $this->createBaseDir($directory, $output);

        if (!$this->findExistOpenCV($output)) {

            //克隆项目
            $this->cloneOpenCV($directory);
            $this->cloneOpenCVContrib($directory);

            //编译扩展
            $this->buildOpenCV($directory);
        }

        //编译phpopencv扩展
        $this->buildPHPOpenCV($directory);

        $output->writeln('<comment>Application ready! Build something amazing.</comment>');
    }


    /**
     * 用户输入
     * @author hihozhou
     * @return string
     */
    protected function userInput()
    {
        $input = trim(fgets(STDIN));
        return $input;
    }
}