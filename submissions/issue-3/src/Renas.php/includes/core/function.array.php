<?php
################################################
# 本脚本不允许外部直接访问
################################################
if (!defined('_EXTERNAL')) die(header("Location: /"));

################################################
#提供对数组做处理的方法
################################################

/**
 * 这个函数用来在数组的指定位置（从0开始计算位置）插入元素，可选择覆盖式插入。
 * 注意：这个函数会重建数组的索引。
 * 
 * @param  array   &$target
 * 这是操作目标数组，对它的操作是引用操作。
 * 
 * @param  integer  $position
 * 插入位置。如果输入的值<=0，那么就插入在数组的最前面。
 * 
 * @param  mixed  $insertion 
 * 要插入的元素。如果这个参数不是一个数组，那么函数会将其包装成一个数组。
 * 
 * @param  boolean $override
 * 是否采用覆盖方式。默认为FALSE，即不采用。
 * 如果设为TRUE，那么原始数组中的同名键会被覆盖。
 * 
 * @return array
 * 返回修改后的数组。由于本函数是对目标数组做引用修改，因此返回值是非必须的。
 */
function fArrayInsert(
    array &$target, 
    $position, 
    $insertion, 
    $override = FALSE
) {
    if(!is_numeric($position)) {
        \fLog('error: $position expected to be numeric.', 1, true);
        return $target;
    }

    #如果位置小于0，设为插入在最前面
    if($position <= 0) $position = 0;

    #如果$insertion不是一个数组，那么将它包装成数组
    if(!is_array($insertion)) $insertion = array($insertion);

    #$head 从数组中截取前半段
    $head = array_splice($target, 0, $position);

    #拼接数组，决定是否覆盖
    if($override === TRUE) {
        $target = $head + $insertion + $target;    
    } else {
        $target = array_merge($head, $insertion, $target);
    }
    return $target;
}


/**
 * 这个函数用来在数组的指定位置（从0开始计算位置）删除元素。
 * 注意：这个函数会重建数组的索引。
 * 
 * @param  array   &$target 
 * 这是操作目标数组，对它的操作是引用操作。
 * 
 * @param  [type]  $position
 * 插入位置。如果输入的值<=0，那么从数组的最前面开始删除。
 * 
 * @param  integer $elements 
 * 删除多少个元素，默认为1个
 * 
 * @return array
 * 返回修改后的数组。由于本函数是对目标数组做引用修改，因此返回值是非必须的。
 */
function fArrayRemove(
    array &$target, 
    $position,
    $elements=1
) {
    if(!is_numeric($position)) {
        \fLog('error: $position expected numeric: ', 1, true);
        return $target;
    }

    #如果位置小于0，设为删除最前面
    if($position <= 0) $position = 0;

    #$head 从数组开始到截取位置的前半段
    $head = array_slice($target, 0, $position);

    #$tail 从截取截止处到数组最末的后半段
    $tail = array_splice($target, $position+$elements);

    #拼接数组
    $target = array_merge($head, $tail);

    return $target;
}

/**
 * 对一个2维数组排序，排序根据每个1级数组元素的子元素的值排序。
 *
 * @param array $array 
 * 被排序的数组
 *
 * @param string|integer $key 
 * 作为排序依据的2级数组的键名或索引编号
 *
 * @param boolean $ascend
 * 排序方式
 * TRUE = 升序
 * FALSE = 降序
 *
 * @return array
 * 返回排序后的数组
 */
function fArraySort(
    $array,
    $key,
    $ascend = TRUE
) {
	if(
        !is_array($array) 
        || (!is_string($key) && !is_numeric($key))
    ) {
		return FALSE;
	}

	$arr_nums=$return=array();

	foreach($array as $k=>$v) {
		$arr_nums[$k]=$v[$key];
	}

	if($ascend==TRUE) {
		asort($arr_nums);
	} else {
		arsort($arr_nums);
	}

	foreach($arr_nums as $k=>$v) {
		$return[$k]=$array[$k];
	}

	return $return;
}

/**
 * 从二维数组中，将每个子元素数组中指定键名的键值合并成一个数组。
 * 比如：$arr = array(
 *  0 => array('id' => 1, 'name' => 'one'),
 *  1 => array('id' => 2, 'name' => 'two'),
 *  2 => array('id' => 3, 'name' => 'three')
 * )
 * 经过nzArrayColumn()处理后，比如nzArrayColumn($arr, 'name')，将返回：
 * array(
 *  0 => 'one',
 *  1 => 'two',
 *  2 => 'three'
 * )
 * 
 * @param array $array
 * 被处理的二维数组
 * 
 * @param string|integer $column
 * 每个子数组中的特定键名。如果这个元素没有键名则可用序号（integer），如果这个元素有键名则用键名（string）。
 * 
 * @return array
 * 被合并后的数组。
 */
function fArrayColumn(
    $array,
    $column
) {
    if(!is_array($array)) return array(); //如果不是数组，直接返回空数组

    $columnData = array();
    foreach ($array as $k => $v) {
        $columnData[$k] = $v[$column];
    }

    return $columnData;
}

/**
 * 在一个二维数组中，查找每个子数组中的键值，并返回查找结果。
 * 
 * @param array $array
 * 被查找的二维数组
 * 
 * @param mixed $needle 
 * 要查找的值。注意本函数使用的是全等，因此要注意数据类型。
 * 
 * @param string|integer $column
 * 在每个子数组的哪个元素下进行查找。如果这个元素没有键名则用序号（integer），如果这个元素有键名则用键名（string）。
 * 
 * @return boolean
 * 如果没有结果，返回FALSE，反之返回TRUE。
 */
function fArraySearch(
    $array,
    $needle,
    $column
) {
    if(!is_array($array)) return FALSE; //如果不是数组，直接返回空数组

    $columnData = fArrayColumn($array, $column);
    $columnData = array_flip($columnData);

    if(isset($columnData[$needle])) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * 在一个二维数组中，查找每个子数组中的键值，并返回一个包含每个子数组键名的解集。
 * 
 * @param array $array
 * 被查找的二维数组
 * 
 * @param mixed $needle 
 * 要查找的值。注意本函数使用的是全等，因此要注意数据类型。
 * 
 * @param string|integer $column
 * 在每个子数组的哪个元素下进行查找。如果这个元素没有键名则用序号（integer），如果这个元素有键名则用键名（string）。
 * 
 * @return array
 * 如果没有结果，会返回一个空数组。
 * 如果有结果，那么结果中每个元素的键值对应的就是二维数组中的子数组键名。
 */
function fArrayMatch(
    $array,
    $needle,
    $column
) {
    $columnData = fArrayColumn($array, $column);

    $result = array();
    foreach ($columnData as $k => $v) {
        if($v === $needle) {
            $result[] = $k;
        }
    }

    return $result;
}


/**
 * 保留键值关系的数组随机重排
 * 这里是做的引用，因此不需要返回。
 * 
 * @param array &$array
 * 被随机重排的数组。
 */
function fShuffle(&$array) {
    $keys = array_keys($array);

    shuffle($keys);

    foreach($keys as $key) {
        $new[$key] = $array[$key];
    }

    $array = $new;
}

/**
 * 根据给定的数组（'result' => wt）随机权重，得到一个结果并返回
 * 
 * @param array $array
 * 传递的数组，这个数组的格式必须是：array(
 *  (string) option => (int) weight,
 * )
 * 
 * @param int $times = 1
 * 随机多少个结果出来
 * 
 * @param bool $nonReset = true
 * 是否使用非重置随机算法。
 * 所谓非重置随机，就是当一个结果被随机到之后，就会从随机库中移除
 * 
 * @return array
 * 返回一组随机结果的数组 array(
 *  'result1',
 *  'result2',
 *  ...
 * )
 */
function fArrayRandWt(
    array $array,
    int $times = 1,
    bool $nonReset = true
) {
    $return = array();
    if(empty($array)) return $return;

    for ($i=0; $i < $times; $i++) { 
        $total = array_sum($array);
        \fShuffle($array);
        \fLog(\fDump($array));

        $rand = mt_rand(0, $total);

        \fLog("Random number = {$rand}");

        foreach ($array as $name => $wt) {
            if($rand > $wt) {
                $rand -= $wt;
            } else {
                $return[] = $name;
                if($nonReset == true) unset($array[$name]);
                break;
            }
        }
    }

    return $return;
}

?>