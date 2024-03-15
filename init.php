<?php
/**
 * Bitnykk on 15/03/2024
 * Assumed code below from other modules logic
 */
 
/** 
 * Var initializer
 * Considering in order : get, post, cookie, global, default
 */
function request_var($varname, $default) {
	if(isset($_GET[$varname])) return $_GET[$varname];
	elseif(isset($_POST[$varname])) return $_POST[$varname];
	elseif(isset($_COOKIE[$varname])) return $_COOKIE[$varname];
	elseif(isset($GLOBALS[$varname])) return $GLOBALS[$varname];
	else return $default;	
}

/**
 * Simple SQL class
 * Based on https://codeshack.io/super-fast-php-mysql-database-class/
 */
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'aoc_items_api';
$mm_db = new db($dbhost, $dbuser, $dbpass, $dbname);
class db {
    protected $connection;
	protected $query;
    protected $show_errors = TRUE;
    protected $query_closed = TRUE;
	public $query_count = 0;
	public function __construct($dbhost = 'localhost', $dbuser = 'root', $dbpass = '', $dbname = '', $charset = 'utf8') {
		$this->connection = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
		if ($this->connection->connect_error) {
			$this->error('Failed to connect to MySQL - ' . $this->connection->connect_error);
		}
		$this->connection->set_charset($charset);
		$content = file_get_contents("MySQL_ItemDB_Tables.sql");
		$tables = explode(";", $content);
		foreach($tables as $table) {
			if(strlen($table)>0) {
				if ($this->query = $this->connection->prepare($table)) {
					$this->query->execute();
				} else {			
					$this->error('Failed to initialize Table - ' . $this->connection->connect_error);
				}
			}
		}
	}
	public function sql_query($sql) {
		return $this->query($sql);
	}
	public function sql_query_parms($sql,$parms) {
		return $this->query($sql,$parms);
	}	
    public function query($query) {
        if (!$this->query_closed) {
            $this->query->close();
        }
		if ($this->query = $this->connection->prepare($query)) {
            if (func_num_args() > 1) {
                $x = func_get_args();
                $args = array_slice($x, 1);
				$types = '';
                $args_ref = array();
                foreach ($args as $k => &$arg) {
					if (is_array($args[$k])) {
						foreach ($args[$k] as $j => &$a) {
							$types .= $this->_gettype($args[$k][$j]);
							$args_ref[] = &$a;
						}
					} else {
	                	$types .= $this->_gettype($args[$k]);
	                    $args_ref[] = &$arg;
					}
                }
				array_unshift($args_ref, $types);
                call_user_func_array(array($this->query, 'bind_param'), $args_ref);
            }
            $this->query->execute();
           	if ($this->query->errno) {
				$this->error('Unable to process MySQL query (check your params) - ' . $this->query->error);
           	}
            $this->query_closed = FALSE;
			$this->query_count++;
        } else {
            $this->error('Unable to prepare MySQL statement (check your syntax) - ' . $this->connection->error);
        }
		if(strtolower(substr($query,0,6))=="select") return $this->fetchAll();
		else return $this;
    }
	public function fetchAll($callback = null) {
	    $params = array();
        $row = array();
	    $meta = $this->query->result_metadata();
	    while ($field = $meta->fetch_field()) {
	        $params[] = &$row[$field->name];
	    }
	    call_user_func_array(array($this->query, 'bind_result'), $params);
        $result = array();
        while ($this->query->fetch()) {
            $r = array();
            foreach ($row as $key => $val) {
                $r[$key] = $val;
            }
			$o = (object) $r;
            if ($callback != null && is_callable($callback)) {
                $value = call_user_func($callback, $o);
                if ($value == 'break') break;
            } else {
                $result[] = $o;
            }
        }
        $this->query->close();
        $this->query_closed = TRUE;
		return $result;
	}
	public function close() {
		return $this->connection->close();
	}
    public function error($error) {
        if ($this->show_errors) {
            exit($error);
        }
    }
	private function _gettype($var) {
	    if (is_string($var)) return 's';
	    if (is_float($var)) return 'd';
	    if (is_int($var)) return 'i';
	    return 'b';
	}
}

?>