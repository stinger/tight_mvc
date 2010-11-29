<?php

class SQLQueryPDO {
	protected $_dbHandle = null;
	protected $_result;
	protected $_query;
	protected $_table;

	protected $_describe = array();

	protected $_extraConditions = array();
	protected $_orderConditions = array();
	protected $_hO;
	protected $_hM;
	protected $_hMABTM;
	protected $_page;
	protected $_limit;

	/** Connects to database **/
	
	function connect($hostname, $username, $password, $dbname, $dbtype)
	{
		$dsn = "{$dbtype}:host={$hostname};dbname={$dbname}";
		try
		{
			$this->_dbHandle = new PDO($dsn, $username, $password);
			if ($dbtype == 'mysql')
			{
				$this->_dbHandle->exec("set names utf8");
			}
		}
		catch (PDOException $e)
		{
		//	trigger_error($e->getMessage(), E_USER_ERROR);
		//	error_log($e->getMessage(), 0);
			$this->_dbHandle = NULL;
			return 0;
		}
	}
 
	/** Disconnects from database **/

	function disconnect()
	{
		$this->_dbHandle = null;
	}

	/** Select Query **/

	function where($field, $value, $compare_sign='=')
	{
		if ($this->_dbHandle !== NULL)
		{
			
			array_push($this->_extraConditions, '`'.$this->_model.'`.`'.$field.'` '.$compare_sign.' '.$this->_dbHandle->quote($value));
		}
		return $this;
	}


	function where_in($field, $value)
	{
		if ($this->_dbHandle !== NULL)
		{
			if (!is_array($value))
			{
				$value = $this->_dbHandle->quote($value);
			}
			else
			{
				foreach ($value as $k=>$v)
				{
					$value[$k] = $this->_dbHandle->quote($value);
				}
				$value = implode(',',$value);
			}
			array_push($this->_extraConditions, '`'.$this->_model.'`.`'.$field.'` IN ( '.$value.')');
		}
		return $this;
	}

	function like($field, $value)
	{
		if ($this->_dbHandle !== NULL)
		{
			array_push($this->_extraConditions, '`'.$this->_model.'`.`'.$field.'` LIKE \'%'.$this->_dbHandle->quote($value).'%\'');
		}
		return $this;
	}

	function fetch_associations($associations)
	{
		if ((isset($associations['one'])) && ($associations['one'] === TRUE))
		{
			$this->_hO = 1;
		}
		if ((isset($associations['many'])) && ($associations['many'] === TRUE))
		{
			$this->_hM = 1;
		}
		if ((isset($associations['manytomany'])) && ($associations['manytomany'] === TRUE))
		{
			$this->_hMABTM = 1;
		}
		return $this;
	}


	function limit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}

	function page($page)
	{
		$this->_page = $page;
		return $this;
	}

	function order_by($orderBy, $order = 'ASC', $binary = FALSE, $model = NULL)
	{
		$orderBinary = '';
		if ($binary === TRUE)
		{
			$orderBinary = 'BINARY ';
		}
		if ($model === NULL)
		{
			$model = $this->_model;
			array_push($this->_orderConditions, $orderBinary.'`'.$model.'`.`'.$orderBy.'` '.$order);
		}
		return $this;
	}

	function find()
	{

		global $inflect;

		$from = '`'.$this->_table.'` as `'.$this->_model.'` ';
		$conditions = '\'1\'=\'1\'';
		$conditionsChild = '';
		$fromChild = '';

		if ($this->_hO == 1 && isset($this->hasOne)) {
			
			foreach ($this->hasOne as $alias => $model) {
				$table = strtolower($inflect->pluralize($model));
				$singularAlias = strtolower($alias);
				$from .= 'LEFT JOIN `'.$table.'` as `'.$alias.'` ';
				$from .= 'ON `'.$this->_model.'`.`'.$singularAlias.'_id` = `'.$alias.'`.`id`  ';
			}
		}
	
		if (($this->id) && ($this->_dbHandle !== NULL))
		{
			$conditions .= 'AND `'.$this->_model.'`.`id` = '.$this->_dbHandle->quote($this->id);
		}

		if (!empty($this->_extraConditions)) {
			$conditions .= ' AND '.implode(' AND', $this->_extraConditions);
		}
		
		if (!empty($this->_orderConditions)) {
			$conditions .= ' ORDER BY '.implode(',',$this->_orderConditions);
		}

		if (isset($this->_page)) {
			$offset = ($this->_page-1)*$this->_limit;
			$conditions .= ' LIMIT '.$this->_limit.' OFFSET '.$offset;
		}
		$this->_query = 'SELECT * FROM '.$from.' WHERE '.$conditions;
		
		if ($this->_dbHandle !== NULL)
		{
			$this->_result = $this->_dbHandle->prepare($this->_query);
			$this->_result->execute();
			$result = array();
			$table = array();
			$field = array();
			$tempResults = array();
			$numOfFields = $this->_result->columnCount();

			for ($i = 0; $i < $numOfFields; ++$i)
			{
				$columnData = $this->_result->getColumnMeta($i);
				array_push($table,$columnData['table']);
				array_push($field,$columnData['name']);
			}
			if ($this->_result->rowCount() > 0 ) {
				while ($row = $this->_result->fetch(PDO::FETCH_BOTH)) {
					for ($i = 0;$i < $numOfFields; ++$i) {
						$tempResults[$table[$i]][$field[$i]] = $row[$i];
					}

					if ($this->_hM == 1 && isset($this->hasMany)) {
						foreach ($this->hasMany as $aliasChild => $modelChild) {
							$queryChild = '';
							$conditionsChild = '';
							$fromChild = '';

							$tableChild = strtolower($inflect->pluralize($modelChild));
							$pluralAliasChild = strtolower($inflect->pluralize($aliasChild));
							$singularAliasChild = strtolower($aliasChild);

							$fromChild .= '`'.$tableChild.'` as `'.$aliasChild.'`';

							$conditionsChild .= '`'.$aliasChild.'`.`'.strtolower($this->_model).'_id` = \''.$tempResults[$this->_model]['id'].'\'';

							$queryChild =  'SELECT * FROM '.$fromChild.' WHERE '.$conditionsChild;
							#echo '<!--'.$queryChild.'-->';
							$resultChild = $this->_dbHandle->prepare($queryChild);
							$resultChild->execute();

							$tableChild = array();
							$fieldChild = array();
							$tempResultsChild = array();
							$resultsChild = array();

							if ($resultChild->rowCount() > 0) {
								$numOfFieldsChild = $resultChild->columnCount();
								for ($j = 0; $j < $numOfFieldsChild; ++$j) {
									$childColumnData = $resultChild->getColumnMeta($j);
									array_push($tableChild,$childColumnData['table']);
									array_push($fieldChild,$childColumnData['name']);
								}

								while ($rowChild = $resultChild->fetch(PDO::FETCH_BOTH)) {
									for ($j = 0;$j < $numOfFieldsChild; ++$j) {
										$tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
									}
									array_push($resultsChild,$tempResultsChild);
								}
							}

							$tempResults[$aliasChild] = $resultsChild;

							$resultChild->closeCursor();
						}
					}


					if ($this->_hMABTM == 1 && isset($this->hasManyAndBelongsToMany)) {
						foreach ($this->hasManyAndBelongsToMany as $aliasChild => $tableChild) {
							$queryChild = '';
							$conditionsChild = '';
							$fromChild = '';

							$tableChild = strtolower($inflect->pluralize($tableChild));
							$pluralAliasChild = strtolower($inflect->pluralize($aliasChild));
							$singularAliasChild = strtolower($aliasChild);

							$sortTables = array($this->_table,$pluralAliasChild);
							sort($sortTables);
							$joinTable = implode('_',$sortTables);

							$fromChild .= '`'.$tableChild.'` as `'.$aliasChild.'`,';
							$fromChild .= '`'.$joinTable.'`,';

							$conditionsChild .= '`'.$joinTable.'`.`'.$singularAliasChild.'_id` = `'.$aliasChild.'`.`id` AND ';
							$conditionsChild .= '`'.$joinTable.'`.`'.strtolower($this->_model).'_id` = \''.$tempResults[$this->_model]['id'].'\'';
							$fromChild = substr($fromChild,0,-1);

							$queryChild =  'SELECT * FROM '.$fromChild.' WHERE '.$conditionsChild;
							# echo '<!--'.$queryChild.'-->';
							$resultChild = $this->_dbHandle->prepare($queryChild);
							$resultChild->execute();

							$tableChild = array();
							$fieldChild = array();
							$tempResultsChild = array();
							$resultsChild = array();

							if ($resultChild->rowCount() > 0) {
								$numOfFieldsChild = $resultChild->columnCount();
								for ($j = 0; $j < $numOfFieldsChild; ++$j) {
									$childColumnData = $resultChild->getColumnMeta($j);
									array_push($tableChild,$childColumnData['table']);
									array_push($fieldChild,$childColumnData['name']);
								}

								while ($rowChild = $resultChild->fetch(PDO::FETCH_BOTH)) {
									for ($j = 0;$j < $numOfFieldsChild; ++$j) {
										$tempResultsChild[$tableChild[$j]][$fieldChild[$j]] = $rowChild[$j];
									}
									array_push($resultsChild,$tempResultsChild);
								}
							}
							$tempResults[$aliasChild] = $resultsChild;
							$resultChild->closeCursor();
						}
					}

					array_push($result,$tempResults);
				}

				if ($this->_result->rowCount() == 1 && $this->id != null) {
					$this->_result->closeCursor();
					$this->clear();
					return($result[0]);
				}
				else
				{
					$this->_result->closeCursor();
					$this->clear();
					return($result);
				}
			}
			else
			{
				$this->_result->closeCursor();
				$this->clear();
				return $result;
			}
		}
	}

    /** Custom SQL Query **/

	function custom($query) {

		global $inflect;
		if ($this->_dbHandle !== NULL)
		{
			$this->_result = $this->_dbHandle->prepare($query);
			$this->_result->execute();

			$result = array();
			$table = array();
			$field = array();
			$tempResults = array();

			if ($this->_result->rowCount() > 0)
			{
				if(substr_count(strtoupper($query),"SELECT") > 0)
				{
					$numOfFields =$this->_result->columnCount();
					for ($i = 0; $i < $numOfFields; ++$i)
					{
						$columnData = $this->_result->getColumnMeta($i);
						array_push($table,$columnData['table']);
						array_push($field,$columnData['name']);
					}
					while ($row = $this->_result->fetch(PDO::FETCH_BOTH))
					{
						for ($i = 0;$i < $numOfFields; ++$i)
						{
							$table[$i] = ucfirst($inflect->singularize($table[$i]));
							$tempResults[$table[$i]][$field[$i]] = $row[$i];
						}
						array_push($result,$tempResults);
					}
				}
				else
				{
					$numOfFields =$this->_result->columnCount();
					for ($i = 0; $i < $numOfFields; ++$i)
					{
						$columnData = $this->_result->getColumnMeta($i);
						array_push($field,$columnData['name']);
					}
					while ($row = $this->_result->fetch(PDO::FETCH_BOTH))
					{
						for ($i = 0;$i < $numOfFields; ++$i)
						{
							$tempResults[$field[$i]] = $row[$i];
						}
						array_push($result,$tempResults);
					}
				}
			}
			$this->_result->closeCursor();
			$this->clear();
			return($result);
		}
		else
		{
			return NULL;
		}
	}

    /** Describes a Table **/

	protected function _describe() {
		global $cache;

		$this->_describe = $cache->get('describe'.$this->_table);

		if (!$this->_describe) {
			$this->_describe = array();
			$query = 'DESCRIBE '.$this->_table;
			if ($this->_dbHandle !== NULL)
			{
				$this->_result = $this->_dbHandle->prepare($query);
				$this->_result->execute();
				while ($row = $this->_result->fetch(PDO::FETCH_BOTH)) {
					array_push($this->_describe,$row[0]);
				}

				$this->_result->closeCursor();
				$cache->set('describe'.$this->_table,$this->_describe);
			}
		}

		foreach ($this->_describe as $field) {
			$this->$field = null;
		}
	}

    /** Delete an Object **/

	function delete() {
		if (($this->id) && ($this->_dbHandle !== NULL)) {
			$query = 'DELETE FROM '.$this->_table.' WHERE `id`='.$this->_dbHandle->quote($this->id).'';
			$this->_result = $this->_dbHandle->prepare($query);
			$this->_result->execute();
			$this->clear();
			if (!$this->_result) {
			    /** Error Generation **/
				return -1;
		   }
		} else {
			/** Error Generation **/
			return -1;
		}
		
	}

    /** Saves an Object i.e. Updates/Inserts Query **/

	function save() {
		$query = '';
		if ($this->_dbHandle !== NULL)
		{
			if (isset($this->id)) {
				$updates = '';
				foreach ($this->_describe as $field) {
					if ($this->$field) {
						$updates .= '`'.$field.'` = '.$this->_dbHandle->quote($this->$field).',';
					}
				}

				$updates = substr($updates,0,-1);

				$query = 'UPDATE '.$this->_table.' SET '.$updates.' WHERE `id`='.$this->_dbHandle->quote($this->id);
			} else {
				$fields = '';
				$values = '';
				foreach ($this->_describe as $field) {
					if ($this->$field) {
						$fields .= '`'.$field.'`,';
						$values .= $this->_dbHandle->quote($this->$field).',';
					}
				}
				$values = substr($values,0,-1);
				$fields = substr($fields,0,-1);

				$query = 'INSERT INTO '.$this->_table.' ('.$fields.') VALUES ('.$values.')';
			}
			$this->_result = $this->_dbHandle->prepare($query);
			$this->_result->execute();
			$this->clear();
			if (!$this->_result)
			{
				/** Error Generation **/
				return -1;
			}
		}
		else
		{
			return -1;
		}
	}

	/** Clear All Variables **/

	function clear() {
		foreach($this->_describe as $field) {
			$this->$field = null;
		}

		$this->_extraConditions = array();
		$this->_orderConditions = array();
		$this->_hO = null;
		$this->_hM = null;
		$this->_hMABTM = null;
		$this->_page = null;
	}

	/** Pagination Count **/

	function total_pages() {
		global $inflect;

		$from = '`'.$this->_table.'` as `'.$this->_model.'` ';
		$conditions = '\'1\'=\'1\'';
		$conditionsChild = '';
		$fromChild = '';

		if ($this->_hO == 1 && isset($this->hasOne)) {

			foreach ($this->hasOne as $alias => $model) {
				$table = strtolower($inflect->pluralize($model));
				$singularAlias = strtolower($alias);
				$from .= 'LEFT JOIN `'.$table.'` as `'.$alias.'` ';
				$from .= 'ON `'.$this->_model.'`.`'.$singularAlias.'_id` = `'.$alias.'`.`id`  ';
			}
		}

		if (($this->id) && ($this->_dbHandle !== NULL))
		{
			$conditions .= 'AND `'.$this->_model.'`.`id` = '.$this->_dbHandle->quote($this->id);
		}

		if (!empty($this->_extraConditions)) {
			$conditions .= ' AND '.implode(' AND', $this->_extraConditions);
		}

		if (!empty($this->_orderConditions)) {
			$conditions .= ' ORDER BY '.implode(',',$this->_orderConditions);
		}

		if (isset($this->_page)) {
			$offset = ($this->_page-1)*$this->_limit;
			$conditions .= ' LIMIT '.$this->_limit.' OFFSET '.$offset;
		}
		$this->_query = 'SELECT * FROM '.$from.' WHERE '.$conditions;
		
		if ($this->_limit && ($this->_dbHandle !== NULL)) {
			$pattern = '/SELECT (.*?) FROM (.*)LIMIT(.*)/i';
			$replacement = 'SELECT COUNT(*) FROM $2';
			$countQuery = preg_replace($pattern, $replacement, $this->_query);
			$this->_result = $this->_dbHandle->prepare($countQuery);
			$this->_result->execute();
			$count = $this->_result->fetch(PDO::FETCH_BOTH);
			$totalPages = ceil($count[0]/$this->_limit);
			return $totalPages;
		} else {
			/* Error Generation Code Here */
			return -1;
		}
	}

    /** Get error string **/

    function getError() {
        return $this->_dbHandle->errorInfo();
    }
}