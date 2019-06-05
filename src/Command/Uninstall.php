<?php
/**
 * Created by PhpStorm.
 * User: hiho
 * Date: 19-4-11
 * Time: 下午7:11
 */

namespace CV\Installer\Console\Command;


use CV\Installer\Console\CommonTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Uninstall extends Command
{

    use CommonTrait;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('uninstall')
            ->setDescription('Uninstall PHPOpenCV extension and OpenCV');
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

        $this->checkIsRoot();
        // todo 通过日志文件获取
        //卸载扩展
        $commands = [
            'cd /opt/phpopencv/opencv/build',
            ($this->isRoot ? '' : 'sudo ') . 'make uninstall',
            ($this->isRoot ? '' : 'sudo ') . 'find . -name "*opencv*" | xargs ' . ($this->isRoot ? '' : 'sudo ') . 'rm -rf'
        ];
        $output->writeln('通过php-config --extension-dir查找扩展存放的目录');
        $process = new Process('php-config --extension-dir');
        $process->mustRun();
        $extDir = str_replace(PHP_EOL, '', $process->getOutput());
        $openCVSoPath = $extDir . '/opencv.so';
        if (file_exists($openCVSoPath)) {
            $commands[] = ($this->isRoot ? '' : 'sudo ') . 'rm ' . $openCVSoPath;
        }
        try {
            $process = new Process(implode(' && ', $commands));
            $process->setTty(Process::isTtySupported());//检查TTY支持
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException('Aborting.');
        }
        //cd /opt/opencv/opencv/build
        //sudo make uninstall
        //sudo find . -name "*opencv*" | xargs sudo rm -rf\
        //通过pkg-config php-config --extension-dir 删除

    }
}