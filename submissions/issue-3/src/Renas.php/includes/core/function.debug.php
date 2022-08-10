<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 提供常用的debug方法
################################################

/**
 * 追踪debug信息的执行步骤，返回详细的步骤内容
 * 
 * @param integer $indent
 * 追踪记录的缩进级别。
 * 默认为0.
 * 
 * @return string
 * 返回追踪到的步骤
 */
function fDebugTrace(
    $indent = 0
) {
    if($GLOBALS['debug']['debugMode'] === false) return;

    $errorMsg = new Exception();
    $trace = explode("\n", $errorMsg->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();
    
    for ($i = 0; $i < $length; $i++)
    {
        $result[] = '[' . ($i + 1) . ']' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
    }

    return str_repeat("\t", $indent).implode("\n".str_repeat("\t", $indent), $result);
}

/**
 * 计算从整个脚本开始执行直至调用此函数时用去的秒数
 * @param boolean $unitSec 
 * 是否使用秒作为时间单位
 * 默认TRUE时，使用秒作为单位；设为FALSE时，使用微秒作为单位
 * 
 * @return string
 * 返回耗时的结果。
 */
function fExecTime(
    $unitSec = TRUE
) {
    #以秒作为单位输出
    if($unitSec === TRUE) {
        $return = round((microtime(TRUE) - $GLOBALS['debug']['debugTimeStart']), 3).' s';
    }
    #以毫秒作为单位输出
    else {
        $return = microtime(TRUE) - $GLOBALS['debug']['debugTimeStart'].' ms';
    }

    return $return;
}

/**
 * 获取调用此函数时的内存开销
 */
function fExecMem(
    $unitByte = FALSE
) {
    #以字节bytes作为单位输出
    if($unitByte === FALSE) {
        return fFormatByte(memory_get_usage());
    }
    #以kb/mb/gb/...作为单位输出
    else {
        return memory_get_usage(). ' bytes';    
    }
}


/**
 * Returns all details of given variable
 *
 * @param mixed $varInput
 *  Variable that will be outputted
 *
 * @param string $encode
 * 如果是空，那么取$GLOBALS['debug']['dumpEncode']
 * 如果是不支持的编码，那么直接返回原始值，不做编码处理
 * 
 * @param bool $displayVarName
 * 是否显示变量名，为True显示，为False不显示
 * 默认为True
 * 
 * @return string
 *  Returns the output buff of var_dump($varInput)
 */
function fDump(
    &$varInput,
    string $encode = NULL,
    bool $displayVarName = true
) {
    // if($GLOBALS['debug']['debugMode'] === false) return;

    # 获取变量名
    if($displayVarName === true) {
        $backtrace = debug_backtrace();
        $backtrace = $backtrace[0];
        $fh = fopen($backtrace['file'], 'r');
        $line = 0;
        while (++$line <= $backtrace['line']) {
            $code = fgets($fh);
        }
        fclose($fh);
        preg_match('/' . __FUNCTION__ . '\s*\((.*)\s*[\)\,]/uU', $code, $varName);
        $return = trim($varName[1])." = ";
    }

    # 按照给定格式打印变量
    if($encode === NULL) $encode = $GLOBALS['debug']['dumpEncode'];
    switch ($encode) {
        case 'json':
            $return .= json_encode($varInput);
            break;

        case 'print_r':
            ob_start();
            print_r($varInput);
            $return .= "\n".ob_get_contents();
            ob_end_clean();
            break;

        case 'var_dump':
            ob_start();
            var_dump($varInput);
            $return .= "\n".ob_get_contents();
            ob_end_clean();
            break;

        case 'var_export':
            ob_start();
            var_export($varInput);
            $return .= "\n".ob_get_contents();
            ob_end_clean();
            break;
        
        default:
            $return .= $varInput;
            break;
    }
    return $return;
}


/**
 * 将log加入到$GLOBALS['cache']['debugLog']中
 * **此为旧方法**
 * 
 * @param string $string
 * 要插入的log内容
 * 
 * @param integer $indent
 * log的缩进级别，默认为0
 * 
 * @param boolean $trace
 * 是否记录追踪记录，设为TRUE将记录
 */
function fLogCache(
    $string,
    $indent = 0,
    $trace = TRUE
) {
    if($GLOBALS['debug']['debugMode'] === false) return;
    if($GLOBALS['debug']['log'] !== TRUE || defined('_NOLOG')) return FALSE;
    $GLOBALS['cache']['logs'][] = str_repeat("\t", $indent)." [Exec:".fExecTime()."] [Mem:".fExecMem()."] ".$string;
    if($trace === TRUE) $GLOBALS['cache']['logs'][] = fDebugTrace($indent+1);
}

/**
 * 将log加入到临时日志文件中
 * 
 * @param string $string
 * 要插入的log内容
 * 
 * @param integer $indent
 * log的缩进级别，默认为0
 * 
 * @param boolean $trace
 * 是否记录追踪记录，设为TRUE将记录
 */
function fLog(
    $string,
    $indent = 0,
    $trace = TRUE
) {
    if($GLOBALS['debug']['debugMode'] === false) return;
    if($GLOBALS['debug']['log'] !== TRUE || defined('_NOLOG')) return FALSE;

    fputs(
        $GLOBALS['debug']['logFile'],
        "\n".($indent > 0 ? str_repeat("\t", $indent) : "\n")." [Exec:".fExecTime()."] [Mem:".fExecMem()."] ".$string."\n"
    );

    if($trace === TRUE) {
        fputs(
            $GLOBALS['debug']['logFile'],
            fDebugTrace($indent+1)
        );
    }
}

/**
 * 结束脚本的执行并将log存入到数据库中。这个函数通常用在脚本退出时（替代php本身的die()）。
 */
function fDie(
    $dieMessage = NULL
) {

    // \fSaveLogToDB();
    \fSaveLogToFile();
    exit($dieMessage);
}

/**
 * 将日志文件保存至对应日期、请求用户uid的目录下
 */
function fSaveLogToFile() {
    if($GLOBALS['debug']['debugMode'] === false) return;
    if($GLOBALS['debug']['log'] !== TRUE || defined('_NOLOG')) return FALSE;

    if(
        $GLOBALS['debug']['log'] === TRUE
        && !defined('_NOLOG')
    ) {
        $logPath = _ROOT.DIR_LOG.\fFormatTime(time(), 'date-').'/'.(is_null($GLOBALS['cache']['logUser']) ? '_guest' : $GLOBALS['cache']['logUser']);
        if(!is_dir($logPath)) { //如果日志目录不存在，创建它
            mkdir($logPath, 0777, true);
        }

        $logFile = $logPath
            .'/'
            .\fFormatTime(time(), 'time-')
            .str_replace('/', ' » ', $_SERVER['SCRIPT_FILENAME'])
            .'.log'
        ;

        fclose($GLOBALS['debug']['logFile']);
        rename(
            _ROOT.DIR_LOG.'temp/'.$GLOBALS['debug']['logFileName'].'.log',
            $logFile
        );
    }
}

/**
 * 将日志保存至数据库中
 * **此为旧方法**
 */
function fSaveLogToDB() {
    if($GLOBALS['debug']['debugMode'] === false) return;
    if($GLOBALS['debug']['log'] !== TRUE || defined('_NOLOG')) return FALSE;

    if(
        $GLOBALS['debug']['log'] === TRUE
        && !defined('_NOLOG')
    ) {
        global $db;

        $log = \fEncode(implode("\n", $GLOBALS['cache']['logs']));
        $db->insert(
            'logs',
            array(
                'uid' => $GLOBALS['cache']['logUser'],
                'timestamp' => time(),
                'content' => $log
            )
        );
    }
}

function fPrint(
    &$varInput
) {
    # 获取变量名
    $backtrace = debug_backtrace();
    $backtrace = $backtrace[0];
    $fh = fopen($backtrace['file'], 'r');
    $line = 0;
    while (++$line <= $backtrace['line']) {
        $code = fgets($fh);
    }
    fclose($fh);
    preg_match('/' . __FUNCTION__ . '\s*\((.*)\s*[\)\,]/uU', $code, $varName);
    $varName = trim($varName[1])." = ";


    echo('<hr><pre>');
    echo($varName);
    echo(\fDump($varInput, null, false));
    echo('</pre>');
}
?>