<?php
/*
 * This file is part of the PHP_CodeCoverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Driver for Phpdbg's code coverage functionality.
 *
 * @since Class available since Release 2.2.0
 * @codeCoverageIgnore
 */
class PHP_CodeCoverage_Driver_Phpdbg implements PHP_CodeCoverage_Driver
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        if (PHP_SAPI !== 'phpdbg') {
            throw new PHP_CodeCoverage_Exception('This driver requires the phpdbg sapi');
        }

        if (version_compare(phpversion(), '7.0', '<')) {
            // actually we require the phpdbg version shipped with php7, not php7 itself
            throw new PHP_CodeCoverage_Exception(
                'phpdbg based code coverage requires at least php7'
            );
        }
    }

    /**
     * Start collection of code coverage information.
     */
    public function start()
    {
       phpdbg_start_oplog();
    }

    /**
     * Stop collection of code coverage information.
     *
     * @return array
     */
    public function stop()
    {
        $dbgData = phpdbg_end_oplog();

        $sourceLines = phpdbg_get_executable();
        foreach ($sourceLines as &$lines) {
            foreach ($lines as &$line) {
                $line = self::LINE_NOT_EXECUTED;
            }
        }

        $data = $this->detectExecutedLines($sourceLines, $dbgData);

        return $data;
    }

    /**
     * Convert phpdbg based data into the format CodeCoverage expects
     *
     * @param  array $sourceLines
     * @param  array $dbgData
     * @return array
     */
    private function detectExecutedLines(array $sourceLines, array $dbgData)
    {
        foreach ($dbgData as $file => $coveredLines) {
            foreach ($coveredLines as $lineNo => $numExecuted) {
                $sourceLines[$file][$lineNo] = self::LINE_EXECUTED;
            }
        }

        return $sourceLines;
    }
}
