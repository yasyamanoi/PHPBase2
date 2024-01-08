<?php
/*!
@file pdointerface.php
@brief PDOのインターフェイス
@copyright Copyright (c) 2024 Yamanoi Yasushi.
*/

////////////////////////////////////
//実行ブロック
//DB接続
$DB_PDO = new cpdo();

////////////////////////////////////
//クラスブロック

//--------------------------------------------------------------------------------------
/// PDOクラス
//--------------------------------------------------------------------------------------
class cpdo extends PDO{
	private $m_display_errors = true;
	//--------------------------------------------------------------------------------------
	/*!
	@brief  コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct(){
		try {
			$engine = DB_RDBMS;
			$host = DB_HOST;
			$database = DB_NAME;
			$charset = DB_CHARSET;
			$user = DB_USER;
			$pass = DB_PASS;
			//接続
			$dsn = "{$engine}:host={$host};dbname={$database};charset={$charset}";
			parent::__construct($dsn,$user,$pass);
			$this->setAttribute (PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if(DB_MYSQL_SET_NAMES == 1){
				$this->beginTransaction ();
				$this->exec("SET NAMES {$charset}");
				$this->commit();
			}
			if(!ini_get('display_errors')){
				$this->m_display_errors = false;
			}
			$this->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		} catch (PDOException $e){
			if($this->m_display_errors){
				echo '接続できません: ' . $e->getMessage();
			}
			exit();
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  display_errorsかどうかをチェックする
	@return display_errorsならtrue
	*/
	//--------------------------------------------------------------------------------------
	public function is_display_errors(){
		return $this->m_display_errors;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __destruct(){
	}
}

//--------------------------------------------------------------------------------------
/// sqlのコアクラス
//--------------------------------------------------------------------------------------
class csqlcore {
	//エラーメッセージ等の情報配列
	public $retarr;
	//結果リソースの保持
	public $res = null;
	//--------------------------------------------------------------------------------------
	/*!
	@brief  コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct(){
		$this->retarr = array();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief プリペア度ステートメントを使ってSQLを実行する
	@param[in]  $sql  prepare文
	@param[in]  $values  値の配列
	@param[in]  $is_insert  インサートされたPKを設定するかどうか
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	protected function execute_core($sql,$values,$is_insert = false){
		global $DB_PDO;
		try{
			$this->retarr['sql'] = $sql;
			$this->retarr['values'] = $values;
			$DB_PDO->beginTransaction ();
			//プリペアドステートメントの登録
			$this->res = $DB_PDO->prepare($sql);
			if(is_array($values) && count($values) > 0){
				//データの登録
				foreach($values as $key => $val){
					if(is_int($val)){
						$param = PDO::PARAM_INT;
					}
					elseif(is_bool($val)){
						$param = PDO::PARAM_BOOL;
					}
					elseif(is_null($val)){
						$param = PDO::PARAM_NULL;
					}
					elseif(is_string($val)){
						$param = PDO::PARAM_STR;
					}
					else{
						$param = FALSE;
					}
					if($param !== false){
						$this->res->bindValue($key, $val, $param);
					}
				}
			}
			$this->res->execute();
			if($is_insert){
				$this->retarr['lastinsert'] = $DB_PDO->lastInsertId();
			}
			$DB_PDO->commit();
			return true;
		}
		catch (PDOException $e){
			$DB_PDO->rollback();
			if($DB_PDO->is_display_errors()){
				echo 'SQL文が実行できません: ' . $e->getMessage();
				echo '<br>' . $sql;
			}
			exit();
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  1行分の取り出し
	@return 1行分の配列。空や失敗場合はfalse。リソースが無効の場合は例外
	*/
	//--------------------------------------------------------------------------------------
	public function fetch_assoc(){
		global $DB_PDO;
		if($this->res){
			return $this->res->fetch(PDO::FETCH_ASSOC);
		}
		else{
			if($DB_PDO->is_display_errors()){
				echo 'リソースがありません';
			}
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  リソースと情報配列の解放
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public function free_res(){
		//空の配列を代入
		$this->retarr = array();
		if($this->res){
			$this->res->closeCursor();
			$this->res = null;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	protected function __destruct() {
		$this->free_res();
	}
}

//--------------------------------------------------------------------------------------
/// レコードクラス
//--------------------------------------------------------------------------------------
class crecord extends csqlcore {
	//--------------------------------------------------------------------------------------
	/*!
	@brief  コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct(){
		$this->res = null;
		parent::__construct();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  select文のクエリ実行
	@param[in]  $debug クエリを出力するかどうか
	@param[in]  $sql SQL文
	@param[in]  $values SQLにはバインド用の値が入っている前提
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function select_query($debug,$sql,$values,$chk = false){
		//chk処理
		if($chk){
			echo 'SQL: ' . $sql . '<br>';
			echo 'DATA: <br>';
			cutil::print_r2($values);
			exit();
		}
		$this->free_res();
		//親クラスのexec_core関数を呼ぶ
		$ret =  $this->execute_core($sql,$values);
		if($debug){
			echo '<br>[sql debug]<br>';
			$this->res->debugDumpParams();
			echo '<br>[/sql debug]<br>';
		}
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  change系のクエリ実行
	@param[in]  $debug クエリを出力するかどうか
	@param[in]  $sql SQL文
	@param[in]  $values SQLにはバインド用の値が入っている前提
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public function change_query($debug,$sql,$values,$chk = false){
		//chk処理
		if($chk){
			echo 'SQL: ' . $sql . '<br>';
			echo 'DATA: <br>';
			cutil::print_r2($values);
			exit();
		}
		//親クラスのexec_core関数を呼ぶ
		$ret =  $this->execute_core($sql,$values,false);
		if($debug){
			echo '<br>[sql debug]<br>';
			$this->res->debugDumpParams();
			echo '<br>[/sql debug]<br>';
		}
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  インサートコア
	@param[in]  $debug デバッグ出力
	@param[in]  $table テーブル名
	@param[in]  $dataarr 追加する項目の配列
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 成功すれば追加されたID
	*/
	//--------------------------------------------------------------------------------------
	public function insert_core($debug,$table,&$dataarr,$chk = false){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if($table == '' || !is_array($dataarr) || count($dataarr) < 1){
			if($DB_PDO->is_display_errors()){
				echo '追加すべきデータがありません。';
			}
			exit();
		}
		//追加するsql文を動的につくり出す
		$sqlstr = "(";
		$size = count($dataarr);
		$count = 1;
		foreach($dataarr as $i => $value){
			$sqlstr .=  $i;
			if($size > $count){
				$sqlstr .= ",";
			}
			$count++;
		}
		$sqlstr .= ") values (";
		$count = 1;
		//プレイスフォルダの作成
		foreach($dataarr as $key => $value){
			$sqlstr .= ":" . $key;
			if($size > $count){
				$sqlstr .= ",";
			}
			$count++;
		}
		$sqlstr .= ")";
		$sql =<<< END_BLOCK
insert into
{$table}
{$sqlstr}
END_BLOCK;
		//データの作成
		$values = array();
		foreach($dataarr as $key => $val){
			$values[":" . $key] = $val;
		}
		if($chk){
			echo 'SQL: ' . $sql . '<br>';
			echo 'DATA: <br>';
			cutil::print_r2($values);
			exit();
		}
		//親クラスのexcute_core関数を呼ぶ
		$ret = 0;
		if($this->execute_core($sql,$values,true)){
			$ret = $this->retarr['lastinsert'];
		}
		if($debug){
			echo '<br>[sql debug]<br>';
			$this->res->debugDumpParams();
			echo '<br>[/sql debug]<br>';
		}
		//追加したID
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  アップデートコア
	@param[in]  $debug デバッグ出力
	@param[in]  $table テーブル名
	@param[in]  $dataarr 更新する項目の配列
	@param[in]  $where where文
	@param[in]  $wherearr where文のための項目の配列
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 影響を受けた行数
	*/
	//--------------------------------------------------------------------------------------
	public function update_core($debug,$table,&$dataarr,$where,&$wherearr,$chk=false){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		if(!is_array($dataarr) || count($dataarr) < 1){
			if($DB_PDO->is_display_errors()){
				echo '更新すべきデータがありません。';
			}
			exit();
		}
		$size = count($dataarr);
		$count = 1;
		$sqlarr = '';
		//プレイスフォルダの作成
		foreach($dataarr as $key => $value){
			$sqlarr .=  $key . " = " . ":" . $key;
			if($size > $count){
				$sqlarr .= ",";
			}
			$count++;
		}
		$sql = <<< END_BLOCK
update
{$table}
set
{$sqlarr}
where
{$where}
END_BLOCK;
		//データの作成
		$values = array();
		foreach($dataarr as $key => $value){
			$values[":" . $key] = $value;
		}
		//$wherearrの追加
		foreach($wherearr as $key => $value){
			$values[$key] = $value;
		}
		//親クラスのchange_query関数を呼ぶ
		$ret = 0;
		if($this->change_query($debug,$sql,$values,$chk)){
			$ret = $this->res->rowCount();
		}
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  削除コア
	@param[in]  $debug デバッグ出力
	@param[in]  $table テーブル名
	@param[in]  $where where文
	@param[in]  $wherearr where文のための項目の配列
	@param[in]  $chk 実行せずにSQLを出力して止める場合true
	@return 影響を受けた行数
	*/
	//--------------------------------------------------------------------------------------
	public function delete_core($debug,$table,$where,&$wherearr,$chk=false){
		global $DB_PDO;
		//空の配列を代入
		$this->retarr = array();
		$sqlarr = '';
		$sql = <<< END_BLOCK
delete
from
{$table}
where
{$where}
END_BLOCK;
		//データの作成
		$values = array();
		//$wherearrの追加
		foreach($wherearr as $key => $value){
			$values[$key] = $value;
		}
		//親クラスのchange_query関数を呼ぶ
		$ret = 0;
		if($this->change_query($debug,$sql,$values,$chk)){
			$ret = $this->res->rowCount();
		}
		return $ret;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __destruct(){
		//親クラスのデストラクタを呼ぶ
		parent::__destruct();
	}
}

//▲▲▲▲▲▲クラスブロック▲▲▲▲▲▲

