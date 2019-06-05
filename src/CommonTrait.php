<?php
/**
 * Created by PhpStorm.
 * User: hiho
 * Date: 19-6-4
 * Time: 下午9:16
 */

namespace CV\Installer\Console;


use Symfony\Component\Process\Process;

trait CommonTrait
{
    protected $isRoot = false;
    protected $systemUsername;


    /**
     * 判断是否是超级管理员
     * @author hihozhou
     */
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
            throw new \RuntimeException($process->getErrorOutput());
        }
    }
}