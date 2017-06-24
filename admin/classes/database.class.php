<?php
class database {

	var $_sql			= '';	
	/** @var Internal variable to hold the connector resource */
	var $_resource		= '';
	/** @var Internal variable to hold the query result*/
	var $_result        = ''; 
		/** @var Internal variable to hold the query result*/
	var $_insertId      = ''; 

	//$host = '';
	/**
	* Database object constructor
	* @param string Database host
	* @param string Database user name
	* @param string Database user password
	* @param string Database name
	* @param string Common prefix for all tables
	* @param boolean If true and there is an error, go offline
	*/
	function database() {
		global $glob;
		// _P($glob);exit;
		// $host        = "host=127.0.0.1";
	 //    $port        = "port=5432";
	 //    $dbname      = "dbname=testdb";
	 //    $credentials = "user=postgres password=pass123";
		$host = $glob['dbhost'];
		$user = $glob['dbusername'];
		$pass = $glob['dbpassword'];
		$db = $glob['dbdatabase'];
		$port = $glob['dbport'];
		// pg_connect("host=sheep port=5432 dbname=mary user=lamb password=foo");
		try {
			$this->_resource= pg_connect('host='.$host.' port='.$port.' dbname='.$db.' user='.$user.' password='.$pass.'');
			// $this->_resource = new PDO('mysql:host='.$host.';dbname='.$db.'', $user, $pass);
			
		} catch (Exception $e) {

			print "Error!: " . $e . "<br/>";
			die();
		}

				
	}
	
	/**
	* Execute the query
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query($sql) {
		
		$_sql = $sql;	
		
		// $stmt = $this->_resource->prepare($_sql);
		$stmt = pg_query($this->_resource, $_sql);
		// $stmt->execute();
       	//$_result = $stmt->fetchAll();
	   	
		return $stmt;				
	}
	
	/**
	* Execute the query for insert
	* @return auto increment id
	*/
	function insert($table, $dbFields) {
	
		$field = array();
		$value = array();
		
		foreach ( $dbFields as $k => $v) {
		  	$v = pg_escape_string($v);
				
			$field[] = $k;
			// $qmark[] =":".$k;
			$value[":".$k] = $v;			
		}

		$f = implode(',',$field);
		
		$val = implode("','",$value);
		// $q = implode(",",$qmark);
		$lastid="";
	 	$insertSql = "INSERT INTO $table ($f) VALUES ('$val') RETURNING id ";	
	    $ret = pg_query($this->_resource, $insertSql);
	    $_result=pg_fetch_row($ret);
		// $stmt = $this->_resource->prepare($insertSql);
		// $stmt->execute($value);
		// $_result=pg_last_oid($ret);
		// $this->_insertId=$_result;
		return $_result;
		
	}
	
	/**
	* Execute the query for update
	* @return true for success
	*/
	function update($table, $dbFields, $where) {
		$updateSql = "UPDATE $table SET ";
		$i=0;
		$values=array();
		foreach ( $dbFields as $k => $v) {
			$v = pg_escape_string($v);
			
			if ($i==0){
				$updateSql .= " $k = '".$v."' ";				
			}
			else{
				$updateSql .= ", $k = '".$v."' ";
			}	
			$values[]=$v;		
			$i++;
		}
		$updateSql .= " WHERE $where";
		 // echo $updateSql; exit;
		$stmt = pg_query($this->_resource, $updateSql);
		$_result=pg_affected_rows($stmt);
        //$result = $this->fetchResults($stmt,$resultType);
		// $stmt = $this->_resource->prepare($updateSql);
		// $_result=$stmt->execute($values);
		return $_result;
	}
	
	function updateJsonb($table, $dbFields, $where) {
		$updateSql = "UPDATE $table SET ";
		
		$elements = array();
		foreach( $dbFields as $k => $v ){
			$elements[] = ( $v );
		}
		$updateSql .= implode( ',', $elements );
		$updateSql .= " WHERE $where";
		//echo $updateSql; exit;
		$stmt = pg_query( $this->_resource, $updateSql );
		$_result = pg_affected_rows( $stmt );
        return $_result;
	}
	
	/**
	* Execute the query for sekect
	* @return array contains result
	*/
	
	function select($vars = "*", $table, $where = "", $orderBy = "", $groupBy = "", $resultType = '' ){

		// _P($table);

 		if ($vars != "*"){
			 if (is_array($vars)){
					$vars = implode(",",$vars);
			}
  		}
		$selectSql = "SELECT ".$vars." FROM ".$table." WHERE true ".$where." ".$groupBy." ".$orderBy;
		$stmt = pg_query($this->_resource, $selectSql);
		$resultSet = $this->fetchResults($stmt,$resultType);
		$result = array();
		if( is_array($resultSet) && count( $resultSet ) > 0 ){
			foreach( $resultSet as $k => $v ){
				$result[] = $v;
			}
		} 
   		return $result;
 	}
 	/**
	* Execute the SQl for update
	* @return true
	*/
	function update_sql($sql) {

	 // $deleteSql = "DELETE FROM $table WHERE $where ";
		
	// $stmt = $this->_resource->prepare($deleteSql);
	$stmt = pg_query($this->_resource, $sql);
	
	$_result=pg_affected_rows($stmt);
	
	 // $result = $stmt->execute();
	 
	 return $_result;
	 	
	}
	/**
	* Execute the query for delete
	* @return true
	*/
	function delete($table, $where) {

	 $deleteSql = "DELETE FROM $table WHERE $where ";
		
	// $stmt = $this->_resource->prepare($deleteSql);
	$stmt = pg_query($this->_resource, $deleteSql);
	$_result=pg_affected_rows($stmt);
	;
	 // $result = $stmt->execute();
	 
	 return $_result;
	 	
	}
	
	/**
	* Called for taking last insert id
	* @return last inserted id
	*/
	function getInsertId(){
		echo $this->_insertId;
	}
	
	/**
	* Execute the query for num of row count
	* @return number of rows for result
	*/
	function numRows($sql){
		$_sql = $sql;
		#Mysql
		// $stmt = $this->_resource->query($_sql);
		// $result = $stmt->rowCount();
		// return $result;
		
		#Postgres
		$stmt = pg_query($this->_resource, $_sql);
		$result = pg_num_rows($stmt);
		return $result;
	}
	
	/**
	* Clode db connection
	*/
	function dbClose(){
		$this->_resource = null;
	}
	
	function fetchArray($stmt, $return_type = ''){
		if( $return_type == 'both' ){
			return $stmt->fetch();
		}
		else if( $return_type == 'numeric' ){
			return $stmt->fetchAll(PDO::FETCH_NUM);
		}
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	function fetchResults($stmt, $return_type = ''){
		// if( $return_type == 'both' ){
		// 	$result = $stmt->fetchAll();
		// }
		// else if( $return_type == 'numeric' ){
		// 	$result = $stmt->fetchAll(PDO::FETCH_NUM);
		// }
		$result = pg_fetch_all($stmt);
		return $result;
	}
	
}		
?>