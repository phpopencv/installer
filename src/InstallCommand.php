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
            ->addArgument('opencv_build_path', InputArgument::OPTIONAL, 'Automatically install the opencv directory', '/opt/opencv')
//            ->addOption('dev', null, InputOption::VALUE_NONE, 'Installs the latest "development" release')
//            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Forces install even if the directory already exists')
        ;
    }


    protected function buildEnvDetection()
    {
        $process = new Process(['./opencv-install-environment-detection.sh']);//给予当前用户
        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }

    /**
     * @param string $directory
     */
    protected function cloneOpenCV(string $directory)
    {
        $opencvUrl = 'https://github.com/opencv/opencv.git';
        $command = "sudo git clone {$opencvUrl} --depth 1";
        $process = new Process($command, $directory, null, null, null);//给予当前用户
        $process->setTty(Process::isTtySupported());//检查TTY支持
        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }


    protected function cloneOpenCVContrib(string $directory)
    {
        $opencvContribUrl = 'https://github.com/opencv/opencv_contrib.git';
        $command = "sudo git clone {$opencvContribUrl} --depth 1";
        $process = new Process($command, $directory, null, null, null);//给予当前用户
        $process->setTty(Process::isTtySupported());//检查TTY支持
        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException($process->getErrorOutput());
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
        //创建目录
        $directory = $input->getArgument('opencv_build_path');
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
        //编译安装
        $output->writeln('<comment>Application ready! Build something amazing.</comment>');
    }
}