<?php
/*!
@file node.php
@brief 
@copyright Copyright (c) 2024 Yamanoi Yasushi.
*/

////////////////////////////////////

//--------------------------------------------------------------------------------------
///	ノードクラス
//--------------------------------------------------------------------------------------
class cnode{
	public $child_nodes;
	//--------------------------------------------------------------------------------------
	/*!
	@brief  コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct(){
		$this->child_nodes = array();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	パラメータのチェック
	@return	なし（エラーの場合はエラーフラグを立てる）
	*/
	//--------------------------------------------------------------------------------------
	public function paramchk(){
		foreach($this->child_nodes as $key => $val){
			$val->paramchk();
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	構築時の処理(継承して使用)
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function create(){
		foreach($this->child_nodes as $key => $val){
			$val->create();
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  表示
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public function display(){
		foreach($this->child_nodes as $key => $val){
			$val->display();
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  子nodeの追加
	@return 追加したオブジェクト（呼び出し側で使いやすくする）
	*/
	//--------------------------------------------------------------------------------------
	public function add_child($nede_obj){
		$this->child_nodes[] = $nede_obj;
		return $nede_obj;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __destruct() {
		$this->child_nodes = array();
	}
}

