<?php
/* 这个脚本不允许外部直接访问 */
if (!defined('_EXTERNAL')) die(header("Location: /"));

/**
 * 提供数据库操作的类
 * 这个类依赖mysqli库
 * 
 * 常用方法：
 * 
 * $obj->insert(
 * 	string $table, 
 * 	array $array
 * );
 * 将新数据插入数据库，数据内容由array定义
 * 
 * $obj->update(
 * 	string $table, 
 * 	array $array, 
 * 	array $where, 
 * 	int|string $limit=null, 
 * 	bool $quoteValue=true
 * );
 * 更新数据库中符合where定义的记录，以array定义要更新的具体内容
 * 
 * $obj->delete(
 * 	string $table, 
 * 	array $where, 
 * 	int|string $limit=null
 * );
 * 从数据库中删除符合where定义的记录
 * 
 * $obj->getCount(
 * 	string $table, 
 * 	array $where, 
 * 	int|string $limit=null, 
 * 	string $groupBy=null
 * ); 
 * 查询数据库中的表，并返回符合where条件的记录的统计计数。可以进行分组
 * 
 * $obj->getSum(
 * 	string $table, 
 * 	string $column, 
 * 	array $where, 
 * 	int|string 
 * 	$limit=null
 * );
 * 查询数据库中的表，并将column指定的列的值计算总和并返回
 * 
 * $obj->getArr(
 * 	string $table, 
 * 	array $where, 
 * 	string|array $select=null, 
 * 	int|string $limit=null, 
 * 	constant $order=null, 
 * 	string|array $sqlOrderBy=null, 
 * 	string $groupBy=null, 
 * 	string $distinct=null
 * );
 * 查询数据库中的表，将符合条件的所有结果以数组形式返回。这个方法中参数比较多，可以进行复杂的组合查询和排序。
 * 
 * $obj->getColumn(
 * 	string $table, 
 * 	string $select='*', 
 * 	array $where, 
 * 	string $limit=null, 
 * 	constant $order=null, 
 * 	string|array $sqlOrderBy=null, 
 * 	string $sqlOrderMethod=null, 
 * 	string $groupBy=null
 * );
 * 查询数据库中的表，将符合条件的记录中的指定列以数组形式返回。
 * 
 * $obj->getTables();
 * 查询数据库中与本系统关联的所有数据表，并返回一个数组。
 * 
 * $obj->query(
 * 	string $sql, 
 * 	string|array $table
 * );
 * 在指定的数据表执行SQL查询
 * 
 * $obj->execute(string $sql);
 * 执行一段给定的SQL语句，只对语句中的参数变量（@pre.替换）
 * 
 * $obj->clearStack();
 * 清除$this->stackQuery中的查询记录。
 */
class xDatabase
{
	#这个数组用来存储直至对象销毁前，每次查询和查询的结果。
	public $stackQuery = array();

	#这个变量用来记录最后一次操作的状态，以便其他代码调用
	public $lastStat = FALSE;

	function __construct(
	) {
		if($GLOBALS['deploy']['db']['socket'] == NULL) {
			$this->db = new mysqli(
				$GLOBALS['deploy']['db']['host'],
				$GLOBALS['deploy']['db']['username'],
				$GLOBALS['deploy']['db']['password'],
				$GLOBALS['deploy']['db']['dbname'],
				$GLOBALS['deploy']['db']['port']
			);
		} else {
			$this->db = new mysqli(
				$GLOBALS['deploy']['db']['host'],
				$GLOBALS['deploy']['db']['username'],
				$GLOBALS['deploy']['db']['password'],
				$GLOBALS['deploy']['db']['dbname'],
				$GLOBALS['deploy']['db']['port'],
				$GLOBALS['deploy']['db']['socket']
			);
		}

        // 如果有报错，把错误计入log中
		if($this->db->connect_error) {
			\fLog('error caught: '.$this->db->connect_errno.$this->db->connect_error, 1, true);
		} else {
			$this->db->query("SET NAMES utf8;");
		}

	}

	/**
	 * 执行一个INSERT命令，将数据作为一条新记录插入到数据库中。
	 *
	 * @param  string $table
	 * 指定插入的表名称，忽略它的前缀，$this->query()方法会自动加上。
	 * 
	 * @param  array  $array 
	 * 插入的数据，每个元素的键名作为数据库中的列名，键值作为记录的值。比如：
	 * array(
	 * 		'fieldName1' => 'fieldValue1',
	 * 		'fieldName2' => 'fieldValue2'
	 * )
	 * 注意，这个方法中不支持键值中填写NULL。
	 * 
	 * @return mixed
	 * 如果插入失败，会返回FALSE。
	 * 如果插入成功，会返回这条插入记录的id，用于后续的调用索引。
	 */
	function insert(
		$table,
		array $array
	) {
		#组装插入的列名
		$fieldName = implode(',', array_keys($array));

		#组装插入的值
		// $fieldValue = "'".implode("','", array_values($array))."'";
		$values = array();
		foreach ($array as $k => $v) {
			switch (true) {
				case is_null($v):
					$values[] = 'NULL';
					break;
				
				default:
					$values[] = "'{$v}'";
					break;
			}
		}
		$fieldValue = implode(',', $values);

		$sql = "INSERT @tab ($fieldName) VALUE ($fieldValue);";

		$stat = $this->query($sql, $table);
		if($stat === FALSE) {
			return FALSE;
		} else {
			return $this->db->insert_id;
		}

		return $this->db->insert_id;
	}


	/**
	 * 更新数据库中符合条件的记录，支持批量更新
	 * 
	 * @param  string $table
	 * 指定查询的表名称，忽略它的前缀，$this->query()方法会自动加上。
	 * 
	 * @param  array  $array 
	 * 更新的数据，每个元素的键名作为数据库中的列名，键值作为记录的值。比如：
	 * array(
	 * 		'fieldName1' => 'fieldValue1',
	 * 		'fieldName2' => 'fieldValue2'
	 * )
	 * 
	 * @param  array  $where
	 * 条件数组，每个元素的值包含一个条件，比如：
	 * array(
	 * 		1 => "`fieldName` = 'fieldValue'",
	 * 		2 =>  "`auth` <> 1",
	 * 		3 => "`widgets` LIKE 'ABC'",
	 * )
	 *
	 * @param  string|integer $limit
	 * SQL命令中的LIMIT查询限制，比如："0"、"0,30"
	 * NULL 表示没有限制
	 * 
	 * @param boolean $quoteValue
	 * 是否在组装语句时，将值用单引号 ' 包装起来。如果要在写入时进行计算，那么应当将这个参数设为FALSE。
	 * 
	 * @return mixed
	 * 如果更新不成功，返回FALSE。
	 * 如果更新成功，返回更新的行数。
	 * 如果没有更新，返回0。
	 */
	function update(
		$table, 
		array $array, 
		array $where,
		$limit = NULL,
		$quoteValue = TRUE
	) {
		if(empty($where)) {
			\fLog('error: $where is empty: ',1, true);
		}

		#组装赋值部分的语句
		$expression = '';
		foreach ($array as $key => $value) {
			if(is_null($value)) { //对为null的值做特殊处理
				$expression .= "`".$key."` = NULL,";
			} else {
				if($quoteValue === TRUE) {
					$expression .= "`".$key."` = '".$value."',";
				} else {
					$expression .= "`".$key."` = ".$value.",";
				}
			}
		}
		#这行代码用于去除$expression末尾的逗号
		$expression = substr($expression, 0, -1);

		#组装WHERE部分的语句
		$condition = implode(" AND ", array_values($where));

		#组装LIMIT限制
		$lmt = $limit == NULL ? NULL : " LIMIT ".$limit;

		$sql = "UPDATE @tab SET $expression WHERE ".$condition.$lmt.";";

		$stat = $this->query($sql, $table);
		if($stat === FALSE) {
			return FALSE;
		} else {
			return $this->db->affected_rows;
		}
	}


	/**
	 * 执行一个DELETE操作，从数据库中删除符合条件的记录，支持批量删除。
	 * 
	 * @param  string $table
	 * 指定查询的表名称，忽略它的前缀，$this->query()方法会自动加上。
	 * 
	 * @param  array  $where
	 * 条件数组，每个元素的值包含一个条件，比如：
	 * array(
	 * 		1 => "`fieldName` = 'fieldValue'",
	 * 		2 =>  "`auth` <> 1",
	 * 		3 => "`widgets` LIKE 'ABC'",
	 * )
	 * @param  string|integer $limit
	 * SQL命令中的LIMIT查询限制，比如："0"、"0,30"
	 * 
	 * @return mixed
	 * 如果删除不成功，返回FALSE。
	 * 如果删除成功，返回删除的条数。
	 * 如果没有删除，返回0。
	 */
	function delete(
		$table,
		array $where,
		$limit = NULL
	) {
		if(empty($where)) {
			\fLog('error: $where is empty: ',1, true);
		}

		#组装条件
		$condition = implode(" AND ", array_values($where));

		#组装LIMIT限制
		$lmt = $limit == NULL ? NULL : " LIMIT ".$limit;

		$sql = "DELETE FROM @tab WHERE ".$condition.$lmt.";";

		$stat = $this->query($sql, $table);
		if($stat === FALSE) {
			return FALSE;
		} else {
			return $this->db->affected_rows;
		}
	}


	/**
	 * 查询数据库中的表，并返回符合条件的记录的统计计数。
	 *
	 * @param  string|array $table
	 * 指定查询的表名称，忽略它的前缀，$this->query()方法会自动加上。
	 * 数组的用法详见query()方法
	 * 
	 * @param  array  $where 
	 * 条件数组，每个元素的值包含一个条件，比如：
	 * array(
	 * 		1 => "`fieldName` = 'fieldValue'",
	 * 		2 =>  "`auth` <> 1",
	 * 		3 => "`widgets` LIKE 'ABC'",
	 * )
	 * 如果不填写，默认不做限定条件查询
	 * 
	 * @param null|string|array $select
	 * select的目标
	 * 如果为NULL，等同于 "SELECT *";
	 * 如果类型为string，则将之作为查询的字段名，比如 $select = 'data'，代表 "SELECT `data`";
	 * 如果类型为array，则查询多个字段，每个字段名作为一个数组成员，比如 $select = array('data', 'name', 'value')，代表"SELECT `data`, `name`, `value`"。
	 * 默认为NULL
	 * 
	 * @param  string|integer $limit
	 * SQL命令中的LIMIT查询限制，比如："0"、"0,30"
	 * 
	 * @param  string $groupBy
	 * 查询到的结果中，以某一列作为分组依据（该列不同的值会归在一组）。
	 * 
	 * @param string $distinct
	 * 是否对某个数据列做去重，须在$select参数非数组时生效。
	 * 
	 * @return mixed
	 * 如果没有设置$groupBy，那么会返回一个数字。
	 * 如果设置了$groupBy，那么会返回一个数组，这个数组的元素键名为一个组的值，元素键值为这一组的统计数。
	 */
	function getCount(
		$table, 
		array $where = array(),
		$select = NULL,
		$limit = NULL, 
		$groupBy = NULL,
		$distinct = FALSE
	) {
		if(is_null($where) && is_null($groupBy)) {
			\fLog('error: $where and $groupBy should not be both NULL: ',1, true);
			return FALSE;
		}

		#distinct去重
		if(
			$distinct === FALSE
			|| $select === array()
		) {
			$distinction = '';
		} else {
			$distinction = "DISTINCT";
		}

		#组装条件语句
		if(is_null($where)) {
			$condition = NULL;
		} else {
			$condition = implode(" AND ", array_values($where));
		}

		#select拼装
		if(is_null($select)) {
			$sel = '*';
		}
		elseif(is_array($select)) {
			$tmpSel = array();
			foreach ($select as $k => $elem) {
				#如果成员是数组，那么将键名作为表的alias，将键值作为列名
				if(is_array($elem)) {
					foreach($elem as $s => $c) {
						$tmpSel[] = "{$s}.`{$c}`";
					}
				}

				#否则直接将成员的键值作为列名
				else {
					$tmpSel[] = "`{$elem}`";
				}
			}
			$sel = implode(', ', $tmpSel);
		}
		elseif(is_string($select)) {
			$sel = "`{$select}`";
		}

		#组装LIMIT限制
		$lmt = $limit == NULL ? NULL : " LIMIT ".$limit;

		#组装WHERE语句
		$wh = $condition == NULL ? '' : ' WHERE '.$condition;

		#组装分组语句
		$group = is_null($groupBy) ? NULL : " GROUP BY $groupBy";

		$sql = "SELECT count({$distinction} {$sel}) AS `count` FROM @tab".$wh.$group.$lmt.";";

		#将查询结果返回
		$result = $this->resultToArr($this->query($sql, $table));

		if(is_null($groupBy)) {
			#如果没有分组汇总，那么返回数值结果。
			$return = $result[0]['count'];
		} else {
			#如果是分组汇总，那么返回数组，数组中每个元素对应1个分组结果
			$return = array();
			foreach ($result as $k => $v) {
				$return[$v[$groupBy]] = $v['count'];
			}
		}

		return $return;
	}

	/**
	 * 执行一个SELECT查询，查询符合条件的条目中，某一列的和并返回
	 * 
	 * @param  string|array $table
	 * 指定查询的表名称，忽略它的前缀，$this->query()方法会自动加上。
	 * 数组的用法详见query()方法
	 * 
	 * @param string $column
	 * 要求和的列名
	 * 根据必要用``来包裹列名
	 * 
	 * @param  array $where
	 * 条件数组，每个元素的值包含一个条件，比如：
	 * array(
	 * 		1 => "`fieldName` = 'fieldValue'",
	 * 		2 =>  "`auth` <> 1",
	 * 		3 => "`widgets` LIKE 'ABC'",
	 * )
	 * 
	 * @param  string|integer $limit
	 * SQL命令中的LIMIT查询限制，比如："0"、"0,30"
	 * 
	 * @param  string|array $sqlOrderBy
	 * 根据哪一列进行排序，这个参数填写列名。
	 * 如果是array，那么按照多列排序
	 * 
	 * @param string $sqlOrderMethod
	 * 排序方式，支持 ASC|DESC|RAND，默认为ASC
	 * 如果为RAND，则会随机排序
	 * 
	 * @return int
	 * 返回查询到的和，如果是不合法的结果，会返回0
	 */
	function getSum(
		$table,
		$column,
		array $where = array(),
		$limit = NULL,
		$sqlOrderBy = NULL,
		$sqlOrderMethod = NULL
	) {
		#查询条件
		$cond = (is_array($where) && !empty($where)) ? 'WHERE '. implode(" AND ", array_values($where)) : '';

		#查询限制
		$lmt = $limit == NULL ? NULL : " LIMIT ".$limit;

		#组装排序命令
		$orderCmd = '';

		if($sqlOrderMethod == 'RAND') {
			$orderCmd = " ORDER BY RAND()";
		}

		elseif(is_string($sqlOrderBy)) {
			$orderCmd = " ORDER BY ".$sqlOrderBy." ";
			$orderCmd .= $sqlOrderMethod == NULL ? "ASC" : $sqlOrderMethod;
		}

		elseif(is_array($sqlOrderBy)) {
			$orderArr = array();
			foreach ($sqlOrderBy as $k) {
				$orderArr[] = "`{$k}`";
			}

			$orderCmd = " ORDER BY ".implode(', ', $orderArr)." ";
			$orderCmd .= $sqlOrderMethod == NULL ? "ASC" : $sqlOrderMethod;
		}
		
		#组装查询语句并进行查询
		$sql = "SELECT SUM({$column}) FROM @tab $cond $orderCmd $lmt;";

		$result = $this->resultToArr($this->query($sql,$table));

		if(is_null($result[0]["SUM({$column})"])) return 0;
		return $result[0]["SUM({$column})"];
	}

	/**
	 * 执行一个SELECT查询，将符合条件的结果以数组形式返回。
	 * 
	 * @param  string|array $table
	 * 指定查询的表名称，忽略它的前缀，$this->query()方法会自动加上。
	 * 数组的用法详见query()方法
	 * 
	 * @param  array $where
	 * 条件数组，每个元素的值包含一个条件，比如：
	 * array(
	 * 		1 => "`fieldName` = 'fieldValue'",
	 * 		2 =>  "`auth` <> 1",
	 * 		3 => "`widgets` LIKE 'ABC'",
	 * )
	 * 
	 * @param null|string|array $select
	 * select的目标
	 * 如果为NULL，等同于 "SELECT *";
	 * 如果类型为string，则将之作为查询的字段名，比如 $select = 'data'，代表 "SELECT `data`";
	 * 如果类型为array，则查询多个字段，每个字段名作为一个数组成员，比如 $select = array('data', 'name', 'value')，代表"SELECT `data`, `name`, `value`"。
	 * 默认为NULL
	 * 
	 * @param  string|integer $limit
	 * SQL命令中的LIMIT查询限制，比如："0"、"0,30"
	 * 
	 * @param  constant $order
	 * 支持以下常量
	 * MYSQLI_ASSOC：返回关联数组
	 * 	MYSQLI_NUM：返回编号数组
	 * 	MYSQLI_BOTH：两者都返回
	 * 
	 * @param  string|array $sqlOrderBy
	 * 根据哪一列进行排序，这个参数填写列名。
	 * 如果是array，那么按照多列排序
	 * 
	 * @param string $sqlOrderMethod
	 * 排序方式，支持 ASC|DESC|RAND，默认为ASC
	 * 如果为RAND，则会随机排序
	 * 
	 * @param string $groupBy
	 * 分组查询方式
	 * 
	 * @param string $distinct
	 * 是否对某个数据列做去重，须在$select参数非数组时生效。
	 * 
	 * @return mixed
	 * 返回查询到的结果，以数组方式组装。
	 * return array(
	 * 	0 => array(
	 * 		列名 => 值,
	 * 		列名 => 值,
	 * 		...
	 * 	),
	 * 	1 => array(),
	 * 	...
	 * )
	 * 如果没有查询到结果，返回 FALSE。
	 */
	public function getArr(
		$table, 
		$where = array(),
		$select = NULL,
		$limit = NULL, 
		$order = NULL, 
		$sqlOrderBy = NULL, 
		$sqlOrderMethod = NULL,
		$groupBy = NULL,
		$distinct = FALSE
	) {
		#distinct去重
		if(
			$distinct === FALSE
			|| $select === array()
		) {
			$distinction = '';
		} else {
			$distinction = "DISTINCT";
		}

		#select拼装
		if(is_null($select)) {
			$sel = '*';
		}
		elseif(is_array($select)) {
			$tmpSel = array();
			foreach ($select as $k => $elem) {
				#如果成员是数组，那么将键名作为表的alias，将键值作为列名
				if(is_array($elem)) {
					foreach($elem as $s => $c) {
						$tmpSel[] = "{$s}.{$c}";
					}
				}

				#否则直接将成员的键值作为列名
				else {
					$tmpSel[] = $elem;
				}
			}
			$sel = implode(', ', $tmpSel);
		}
		elseif(is_string($select)) {
			// $sel = "`{$select}`";
			$sel = $select;
		}

		#查询条件
		$cond = (is_array($where) && !empty($where)) ? 'WHERE '. implode(" AND ", array_values($where)) : '';

		#查询限制
		$lmt = $limit == NULL ? NULL : " LIMIT ".$limit;

		#组装排序命令
		$orderCmd = '';

		if($sqlOrderMethod == 'RAND') {
			$orderCmd = " ORDER BY RAND()";
		}

		elseif(is_string($sqlOrderBy)) {
			$orderCmd = " ORDER BY ".$sqlOrderBy." ";
			$orderCmd .= $sqlOrderMethod == NULL ? "ASC" : $sqlOrderMethod;
		}

		elseif(is_array($sqlOrderBy)) {
			$orderArr = array();
			foreach ($sqlOrderBy as $k) {
				$orderArr[] = "`{$k}`";
			}

			$orderCmd = " ORDER BY ".implode(', ', $orderArr)." ";
			$orderCmd .= $sqlOrderMethod == NULL ? "ASC" : $sqlOrderMethod;
		}

		// if($sqlOrderBy !== NULL) {
		// 	$orderCmd = " ORDER BY @tab.`".$sqlOrderBy."` ";	
		// 	$orderCmd .= $sqlOrderMethod == NULL ? "ASC" : $sqlOrderMethod;
		// }

		if(is_null($groupBy)) {
			$grp = NULL;
		} else {
			$grp = 'GROUP BY '.$groupBy;
		}
		
		#组装查询语句并进行查询
		$sql = "SELECT {$distinction} {$sel} FROM @tab $cond $grp $orderCmd $lmt;";

		$result = $this->query($sql,$table);
		\fLog("Query result = ".\fDump($result), 1);

		return $this->resultToArr($result);
	}

	/**
	 * 执行一个SELECT查询，查询1个指定名称的列，将符合条件的结果以数组形式返回。
	 * 
	 * @param  string|array $table
	 * 指定查询的表名称，忽略它的前缀，$this->query()方法会自动加上。
	 * 数组的用法详见query()方法
	 * 
	 * @param string $select
	 * 指定的列名
	 * 
	 * @param  array $where
	 * 条件数组，每个元素的值包含一个条件，比如：
	 * array(
	 * 		1 => "`fieldName` = 'fieldValue'",
	 * 		2 =>  "`auth` <> 1",
	 * 		3 => "`widgets` LIKE 'ABC'",
	 * )
	 * 
	 * @param  string|integer $limit
	 * SQL命令中的LIMIT查询限制，比如："0"、"0,30"
	 * 
	 * @param  constant $order
	 * 支持以下常量
	 * MYSQLI_ASSOC：返回关联数组
	 * 	MYSQLI_NUM：返回编号数组
	 * 	MYSQLI_BOTH：两者都返回
	 * 
	 * @param  string|array $sqlOrderBy
	 * 根据哪一列进行排序，这个参数填写列名。
	 * 如果是array，那么按照多列排序
	 * 
	 * @param string $sqlOrderMethod
	 * 排序方式，支持 ASC 和 DESC，默认为ASC
	 * 
	 * @param string $groupBy
	 * 分组查询方式
	 * 
	 * @return mixed
	 * 返回查询到的结果，以数组方式组装。
	 * return array(
	 * 	0 => array(
	 * 		列名 => 值,
	 * 		列名 => 值,
	 * 		...
	 * 	),
	 * 	1 => array(),
	 * 	...
	 * )
	 * 如果没有查询到结果，返回 FALSE。
	 */
	public function getColumn(
		$table, 
		$select = '*',
		$where = array(), 
		$limit = NULL, 
		$order = NULL, 
		$sqlOrderBy = NULL, 
		$sqlOrderMethod = NULL,
		$groupBy = NULL
	) {
		#查询条件
		$cond = (is_array($where) && !empty($where)) ? 'WHERE '. implode(" AND ", array_values($where)) : '';

		#查询限制
		$lmt = $limit == NULL ? NULL : " LIMIT ".$limit;

		#组装排序命令
		$orderCmd = '';

		if(is_string($sqlOrderBy)) {
			$orderCmd = " ORDER BY `".$sqlOrderBy."` ";
			$orderCmd .= $sqlOrderMethod == NULL ? "ASC" : $sqlOrderMethod;
		}

		elseif(is_array($sqlOrderBy)) {
			$orderArr = array();
			foreach ($sqlOrderBy as $k) {
				$orderArr[] = "`{$k}`";
			}

			$orderCmd = " ORDER BY ".implode(', ', $orderArr)." ";
			$orderCmd .= $sqlOrderMethod == NULL ? "ASC" : $sqlOrderMethod;
		}

		// if($sqlOrderBy !== NULL) {
		// 	$orderCmd = " ORDER BY @tab.`".$sqlOrderBy."` ";	
		// 	$orderCmd .= $sqlOrderMethod == NULL ? "ASC" : $sqlOrderMethod;
		// }

		if(is_null($groupBy)) {
			$grp = NULL;
		} else {
			$grp = 'GROUP BY '.$groupBy;
		}
		
		#组装查询语句并进行查询
		$sql = "SELECT `{$select}` FROM @tab $cond $grp $orderCmd $lmt;";

		$result = $this->query($sql,$table);

		return $this->resultToArr($result);
	}


	/**
	 * 查询数据库中与本系统关联的所有数据表，并返回一个数组。
	 * @return array
	 * 返回数组中，每个元素的值都是一个表名。
	 */
	public function getTables()
	{
		$sql = "SHOW TABLES LIKE  '{$GLOBALS['deploy']['db']['prefix']}%'";
		$result = $this->query($sql);
		
		$tableList = array();
		while($cRow = mysqli_fetch_array($result))
		{
			$tableList[] = $cRow[0];
		}
		return $tableList;
	}


	/**
	 * 进行1次数据库查询
	 * @param  string $sql   
	 * 查询用的SQL语句。
	 * 查询语句中对表名可用“@tab”来替代，以便进行表名的替换
	 * 
	 * @param  string|array $table
	 * 为了在表中使用通用的SQL查询语句，$tab会替换查询语句中的“@tab”
	 * 如果是数组，则意味着这是一次连表查询。每个成员对应一张表名（不用加前缀）
	 * 为数组时，每个成员的键名为缩写，键值为表名。
	 *
	 * @return mixed
	 * 返回查询结果
	 */
	public function query(
		$sql, 
		$table = NULL
	) {
		// if(!is_null($table)) {
		// 	#将SQL语句中@tab替换成$table中的表名
		// 	$sql = str_replace('@tab', $GLOBALS['deploy']['db']['prefix'].$table, $sql);
		// }

		if(is_string($table)) {
			#将SQL语句中@tab替换成$table中的表名
			$sql = str_replace('@tab', $GLOBALS['deploy']['db']['prefix'].$table, $sql);

			#将SQL语句中@pre.替换成预设的表名前缀
			$sql = str_replace('@pre.', $GLOBALS['deploy']['db']['prefix'], $sql);
		}
		elseif(is_array($table)) {
			#将SQL语句中的@tab替换为多个可指定alias的表名
			$tabs = array();
			foreach($table as $k => $v) {
				if(is_string($k)) {
					$tabs[] = "{$GLOBALS['deploy']['db']['prefix']}{$v} AS {$k}";
				} else {
					$tabs[] = "{$GLOBALS['deploy']['db']['prefix']}{$v}";
				}
			}
			$sql = str_replace('@tab', implode(', ', $tabs), $sql);
			$sql = str_replace('@pre.', $GLOBALS['deploy']['db']['prefix'], $sql);
		}
		else {
			\fLog('Error while querying the database: '.\fDump($this->db->error_list));
			return FALSE;
		}
		\fLog("SQL query = {$sql}",1);


		#进行查询
		$return = $this->db->query($sql);

		#错误处理
		if($this->db->error){
			\fLog("Error caught on DB querying: ".$this->db->error, 1, true);
			$return = FALSE;
			$this->lastStat = FALSE;
		} else {
			$this->lastStat = TRUE;
		}

		#将这次查询记录在$this->stackQuery
		if(
			$GLOBALS['debug']['debugMode'] == true
			&& $GLOBALS['debug']['log'] == true
		) {
			$this->stackQuery[] = array(
				'query' => $sql,
				'result' => $return,
			);
		}
		
		return $return;
	}

    /**
     * 执行一段给定的SQL语句，只对语句中的参数变量（@pre.替换）
     */
    function execute(
        $sql
    ) {
        #将SQL语句中@pre.替换成预设的表名前缀
        $sql = str_replace('@pre.', $GLOBALS['deploy']['db']['prefix'], $sql);

        \fLog("SQL query = {$sql}",1);

		#进行查询
		$return = $this->db->query($sql);

		#错误处理
		if($this->db->error){
			\fLog("Error caught on DB querying: ".$this->db->error, 1, true);
			$return = FALSE;
			$this->lastStat = FALSE;
		} else {
			$this->lastStat = TRUE;
		}

		#将这次查询记录在$this->stackQuery
		if(
			$GLOBALS['debug']['debugMode'] == true
			&& $GLOBALS['debug']['log'] == true
		) {
			$this->stackQuery[] = array(
				'query' => $sql,
				'result' => $return,
			);
		}

		return $return;
    }

	/**
	 * 将数据库查询得到的结果转化成数组并返回
	 * 
	 * @param  object $query
	 * 查询的结果
	 * 
	 * @param  constant $order
	 * 支持以下常量
	 * MYSQLI_ASSOC：返回关联数组
	 * 	MYSQLI_NUM：返回编号数组
	 * 	MYSQLI_BOTH：两者都返回
	 * 	
	 * @return array
	 * 返回一个数组
	 */
	function resultToArr(
		$query = NULL, 
		$order= NULL
	) {
		if(is_null($query) || !is_object($query) || $query == FALSE) return FALSE;

		$return = array();

		switch ($order) {
			case 'NUM':
				while ($row = $query->fetch_array(MYSQLI_NUM)) {
					$return[] = $row;
				}
				break;
			
			case 'BOTH':
				while ($row = $query->fetch_array(MYSQLI_BOTH)) {
					$return[] = $row;
				}

			default:
				while ($row = $query->fetch_array(MYSQLI_ASSOC)) {
					$return[] = $row;
				}
				break;
		}

		if(empty($return)) return FALSE;
		return $return;
	}

	/**
	 * 清除$this->stackQuery中的查询记录。
	 */
	public function clearStack() {
		$this->stackQuery = array();
	}
}

?>