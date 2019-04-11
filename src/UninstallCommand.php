<?php
/**
 * Created by PhpStorm.
 * User: hiho
 * Date: 19-4-11
 * Time: 下午7:11
 */

namespace CV\Installer\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UninstallCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('uninstall')
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

        //卸载扩展
        //cd /opt/opencv/opencv/build
        //sudo make uninstall
        //sudo find . -name "*opencv*" | xargs sudo rm -rf

    }
}