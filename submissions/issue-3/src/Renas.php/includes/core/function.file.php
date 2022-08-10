<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
# 提供文件操作的方法
################################################

/**
 * Read file and return it's contents
 *
 * @param string $filename 
 *        filename and path, includes full path
 *
 * @param integer $offset 
 *        get string from specific position(0-index)
 *
 * @param integer $maxlen 
 *        get string length
 *
 * @return string 
 *         returns content as a string
 */
function fLoadFile (
	$filename, 
	$offset = 0, 
	$maxlen = NULL
) {
	if (file_exists($filename) && is_readable($filename)) {
		$return = isset($maxlen)?file_get_contents($filename, false, NULL, $offset, $maxlen):file_get_contents($filename, false, NULL, $offset);
        fLog("Loading file: {$filename}");
        return $return;
	} else {
		fLog("File doesn't exist or readable: ".$filename);
		$return = FALSE;
	}
}


/**
 * 将字符串存入文件中。
 *
 * @param string $filename 
 * 存入文件的完整路径
 *
 * @param string $contents 
 * 需要存入文件的字符串
 *
 * @param boolean $append 
 * 追加开关。默认为TRUE。
 * 设为TRUE将字符串追加在文件的末尾。
 * 设为FALSE会覆盖整个文件，请谨慎使用！
 *
 * @param boolean $createfile 
 * 创建开关。默认为TRUE。
 * 设为TRUE，将自动创建这个文件（如果它不存在的话）。
 *
 * @param boolean $lock 
 * 锁定开关。默认为TRUE。
 * 设为TRUE将在本函数写入时，锁定该文件，以免发生写入冲突。
 *
 * @return boolean
 * 返回写入的结果。如果写入成功，返回TRUE，否则返回FALSE。
 */
function fSaveFile (
    $filename, 
    $content, 
    $append = TRUE, 
    $createfile = TRUE, 
    $lock = TRUE
) {

	# create file if $createfile==TRUE and file not exists
	if (!file_exists($filename) || !is_readable($filename)) {
		if($createfile !== TRUE) {
            fLog("File doesn't exist or writeable: ".$filename);
			return FALSE;
		} elseif (!is_dir(dirname($filename))) {
			# create directory
			mkdir(dirname($filename).'/', 0777, TRUE);
		}
	}

	#controls file_put_contents flags
	$flag = array(
		'append' => $append == TRUE ? FILE_APPEND : NULL,
		'lock' => $lock == TRUE ? LOCK_EX : NULL
	);

	$stat = file_put_contents($filename, $content, $flag['append']|$flag['lock']);

    if($stat === FALSE) {
        fLog("Failed on writing file: ".$filename);
        return FALSE;
    } else {
        return TRUE;
    }
}

/**
 * 以流式方式输出文件
 * 
 * @param string $sourceFile
 * 提供下载的源文件完整路径和文件名
 * 
 * @param string $downloadName
 * 下载后保存的文件名，为空时与$sourceFile同名
 * 
 * @param string $downloadRate
 * 限速下载的每秒KB数
 */
function fStreamFile (
	$sourceFile,
	$downloadName = NULL,
	$streamRate = NULL
) {
	$saveFileName = $downloadName === NULL ? $sourceFile : $downloadName;

	if(
		file_exists($sourceFile)
		&& is_file($sourceFile)
	) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($saveFileName).'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($sourceFile));
		ob_clean();
		flush();

		fLog("Sending file {$sourceFile} to browser.");

		if($streamRate === NULL) {
			$downloadRate = $GLOBALS['deploy']['fileDownloadRate'] === NULL ? $streamRate : $GLOBALS['deploy']['fileDownloadRate'];
		} else {
			$downloadRate = $streamRate;
		}

		# 输入了限速参数，则每秒只发送一部分
		if(
			is_numeric($downloadRate)
			&& $downloadRate > 0
		) {
			$file = fopen($sourceFile, "r");
			while(!feof($file))
			{
				# 每秒发送文件部分
				print fread($file, round($downloadRate * 1024));
				flush();
				sleep(1);
			}
			fclose($file);
		}
		
		# 不设置限速，那么直接发送
		else {
			readfile($sourceFile);
		}

		return TRUE;
	} else {
		fLog("Source file doesn't exist: {$sourceFile}");
		return FALSE;
	}
}
?>