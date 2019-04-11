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
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Automatically install the opencv directory', '/opt/opencv')//            ->addOption('php-opencv-version', 'pov', InputOption::VALUE_NONE, 'Specify the installed php-opencv version')
//            ->addOption('enable-contrib', null, InputOption::VALUE_REQUIRED, 'Automatically install the opencv directory', false)//            ->addOption('php-opencv-version', 'pov', InputOption::VALUE_NONE, 'Specify the installed php-opencv version')
        ;
    }


    /**
     * 检测安装环境
     * @author hihozhou
     * @throws RuntimeException
     */
    protected function buildEnvDetection()
    {
        $shellPath = __DIR__ . '/../opencv-install-environment-detection.sh';
        $process = new Process([$shellPath]);//给予当前用户
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
        $version = self::OPENCV_VERSION;
        $opencvUrl = 'https://github.com/opencv/opencv.git';
        $command = "sudo git clone {$opencvUrl} --branch {$version} --depth 1";
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
        $version = self::OPENCV_VERSION;
        $opencvContribUrl = 'https://github.com/opencv/opencv_contrib.git';
        $command = "sudo git clone {$opencvContribUrl} --branch {$version} --depth 1";
        $process = new Process($command, $directory, null, null, null);//给予当前用户
        $process->setTty(Process::isTtySupported());//检查TTY支持
        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException('Aborting.');
        }
    }


    protected function findExistOpenCV(OutputInterface $output)
    {
        $output->writeln('Try to find the installed OpenCV on the system via pkg-config...');
        $process = new Process('pkg-config --modversion opencv4');//给予当前用户
        try {
            $process->mustRun();
            $existOpencvVersion = $process->getOutput();
            $output->writeln('Found, opencv version is ' . $existOpencvVersion);

        } catch (\Exception $e) {
            //没有检测到opencv
            $output->writeln('Did not find opencv installed on the system.');
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


        //判断是否已经安装opencv扩展
        if (extension_loaded(self::EXTENSION_NAME)) {
            $process = new Process(['php', '--ri', self::EXTENSION_NAME]);
            $process->mustRun();
            $output->writeln($process->getOutput());
            throw new RuntimeException('The OpenCV PHP extension is installed.');
        }
        $this->buildEnvDetection();
        $this->findExistOpenCV($output);

        //创建目录
        $directory = $input->getOption('path');
        $output->writeln("Compile the directory of opencv with {$directory}.");
        if (!file_exists($directory)) {
            $output->writeln("Create {$directory} of the directory");
            $process = new Process(['sudo', 'mkdir', $directory]);//给予当前用户
            try {
                $process->mustRun();
            } catch (\Exception $e) {
                throw new RuntimeException($process->getErrorOutput());
            }

        }
        $this->cloneOpenCV($directory);
        $this->cloneOpenCVContrib($directory);
        //
        $commands = [
            'cd opencv'
        ];
        $cloneOpenCVDirectory = $directory . '/opencv';
        $commands = [
            'mkdir build'
        ];
        $command = 'mkidr && cd build';
        //编译安装
        $output->writeln('<comment>Application ready! Build something amazing.</comment>');
    }
}