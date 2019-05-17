<?php

/**
 * デバッグユーティリティ.
 */
class DebugUtil
{

    const DEFAULT_LOGFILE_NAME = 'debug';

    private $logFile;
    private $env;

    /**
     * コンストラクタ.
     */
    public function __construct($logFileName = null)
    {
        $this->env     = new Env();
        $this->isDebug = $this->env->debug->is_debug;
        $logFileName   = is_null($logFileName) ? self::DEFAULT_LOGFILE_NAME : $logFileName;
        $this->logFile = sprintf("%s/%s_%s.log", $this->env->debug->log_dir, $logFileName, date("Ymd"));
    }

    /**
     * ログ出力.
     *
     * @param string $data データ
     */
    public function logOut($data)
    {
        if (!$this->isDebug == 1) {
            return;
        }

        if (!file_exists($this->logFile)) {
            touch($this->logFile);
            chmod($this->logFile, 0777);
        }
        
        if (is_array($data) || is_object($data)) {
            $varDumpString = $this->getVarDumpString($data);
            $logMessage    = sprintf("[%s] \n%s\n", date("Y-m-d H:i:s"), $varDumpString);
            error_log($logMessage, 3, $this->logFile);
            return;
        }
        $logMessage = sprintf("[%s] %s\n", date("Y-m-d H:i:s"), $data);
        error_log($logMessage, 3, $this->logFile);
    }

    /**
     * var_dumpの出力内容を文字列として取得する.
     *
     * @param mixed $data データ
     * @return string 出力文字列
     */
    public function getVarDumpString($data)
    {
        ob_start();
        var_dump($data);
        $dumpStr = ob_get_contents();
        ob_end_clean();
        return $dumpStr;
    }

}
