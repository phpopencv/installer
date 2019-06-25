<?php declare(strict_types=1);

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Exception\RuntimeException;


if (!function_exists('process')) {
    /**
     * @param $command
     */
    function process($command)
    {
        $process = new Process($command);
        try {
            $process->mustRun();
        } catch (\Exception $e) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }
}
