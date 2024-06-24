<?php
/*!
@file prefecture_detail.php
@brief 都道府県詳細
@copyright Copyright (c) 2024 Yamanoi Yasushi.
*/

//ライブラリをインクルード
require_once("common/libs.php");

$err_array = array();
$err_flag = 0;
$page_obj = null;
//プライマリキー
$prefecture_id = 0;

//--------------------------------------------------------------------------------------
///	本体ノード
//--------------------------------------------------------------------------------------
class cmain_node extends cnode {
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct() {
		//親クラスのコンストラクタを呼ぶ
		parent::__construct();
		//プライマリキー
		global $prefecture_id;
		if(isset($_GET['pid']) 
		//cutilクラスのメンバ関数をスタティック呼出
			&& cutil::is_number($_GET['pid'])
			&& $_GET['pid'] > 0){
			$prefecture_id = $_GET['pid'];
		}
		//$_POST優先
		if(isset($_POST['prefecture_id']) 
		//cutilクラスのメンバ関数をスタティック呼出
			&& cutil::is_number($_POST['prefecture_id'])
			&& $_POST['prefecture_id'] > 0){
			$prefecture_id = $_POST['prefecture_id'];
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  POST変数のデフォルト値をセット
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public function post_default(){
		cutil::post_default("prefecture_name",'');
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	構築時の処理(継承して使用)
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function create(){
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  本体実行（表示前処理）
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public function execute(){
		global $err_array;
		global $err_flag;
		global $page_obj;
		//プライマリキー
		global $prefecture_id;
		if(is_null($page_obj)){
			echo 'ページが無効です';
			exit();
		}
		if(isset($_POST['func'])){
			switch($_POST['func']){
				case 'set':
					//パラメータのチェック
					$page_obj->paramchk();
					if($err_flag != 0){
						$_POST['func'] = 'edit';
					}
					else{
						$this->regist();
					}
				case 'conf':
					//パラメータのチェック
					$page_obj->paramchk();
					if($err_flag != 0){
						$_POST['func'] = 'edit';
					}
				break;
				case 'edit':
					//戻るボタン。
				break;
				default:
					//通常はありえない
					echo '原因不明のエラーです。';
					exit;
				break;
			}
		}
		else{
			if($prefecture_id > 0){
				$prefecture_obj = new cprefecture();
				//$_POSTにデータを読み込む
				$_POST = $prefecture_obj->get_tgt(false,$prefecture_id);
				if(cutil::array_chk($_POST)){
					//データ取得成功
					$_POST['func'] = 'edit';
				}
				else{
					//データの取得に失敗したので
					//新規ページにリダイレクト
					cutil::redirect_exit($_SERVER['PHP_SELF']);
				}
			}
			else{
				//新規の入力フォーム
				$_POST['func'] = 'new';
			}
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	パラメータのチェック
	@return	エラーの場合はfalseを返す
	*/
	//--------------------------------------------------------------------------------------
	function paramchk(){
		global $err_array;
		global $err_flag;
		/// 都道府県名の存在と空白チェック
		if(cutil_ex::chkset_err_field($err_array,'prefecture_name','都道府県名','isset_nl')){
			$err_flag = 1;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	都道府県の追加／更新。保存後自分自身を再読み込みする。
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	function regist(){
		global $prefecture_id;
		$change_obj = new crecord();
		$dataarr = array();
		$dataarr['prefecture_name'] = (string)$_POST['prefecture_name'];
		if($prefecture_id > 0){
			$where = 'prefecture_id = :prefecture_id';
			$wherearr[':prefecture_id'] = (int)$prefecture_id;
			$change_obj->update_core(false,'prefecture',$dataarr,$where,$wherearr,false);
			cutil::redirect_exit($_SERVER['PHP_SELF'] . '?pid=' . $prefecture_id);
		}
		else{
			$pid = $change_obj->insert_core(false,'prefecture',$dataarr,false);
			cutil::redirect_exit($_SERVER['PHP_SELF'] . '?pid=' . $pid);
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	エラー存在文字列の取得
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	function get_err_flag(){
		global $err_flag;
		switch($err_flag){
			case 1:
			$str =<<<END_BLOCK

<p class="text-danger">入力エラーがあります。各項目のエラーを確認してください。</p>
END_BLOCK;
			return $str;
			break;
			case 2:
			$str =<<<END_BLOCK

<p class="text-danger">更新に失敗しました。サポートを確認下さい。</p>
END_BLOCK;
			return $str;
			break;
		}
		return '';
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	都道府県IDの取得(新規の場合は「新規」)
	@return	都道府県ID
	*/
	//--------------------------------------------------------------------------------------
	function get_prefecture_id_txt(){
		global $prefecture_id;
		if($prefecture_id <= 0){
			return '新規';
		}
		else{
			return $prefecture_id;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	都道府県コントロールの取得
	@return	都道府県コントロール
	*/
	//--------------------------------------------------------------------------------------
	function get_prefecture_name(){
		global $err_array;
		$ret_str = '';
		$tgt = new ctextbox('prefecture_name',$_POST['prefecture_name'],'size="70"');
		$ret_str = $tgt->get($_POST['func'] == 'conf');
		if(isset($err_array['prefecture_name'])){
			$ret_str .=  '<br /><span class="text-danger">' 
			. cutil::ret2br($err_array['prefecture_name']) 
			. '</span>';
		}
		return $ret_str;
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief	操作ボタンの取得
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	function get_switch(){
		global $prefecture_id;
		$ret_str = '';
		if($_POST['func'] == 'conf'){
			$button = '更新';
			if($prefecture_id <= 0){
				$button = '追加';
			}
			$ret_str =<<<END_BLOCK

<input type="button"  value="戻る" onClick="set_func_form('edit','')"/>&nbsp;
<input type="button"  value="{$button}" onClick="set_func_form('set','')"/>
END_BLOCK;
		}
		else{
			$ret_str =<<<END_BLOCK

<input type="button"  value="確認" onClick="set_func_form('conf','')"/>
END_BLOCK;
		}
		return $ret_str;
	}


	//--------------------------------------------------------------------------------------
	/*!
	@brief  表示(継承して使用)
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public function display(){
		global $prefecture_id;
//PHPブロック終了
?>
<!-- コンテンツ　-->
<div class="contents">
<?= $this->get_err_flag(); ?>
<h5><strong>都道府県詳細</strong></h5>
<form name="form1" action="<?= $_SERVER['PHP_SELF']; ?>" method="post" >
<a href="prefecture_list.php">一覧に戻る</a>
<table class="table table-bordered">
<tr>
<th class="text-center">ID</th>
<td width="70%"><?= $this->get_prefecture_id_txt(); ?></td>
</tr>
<tr>
<th class="text-center">都道府県名</th>
<td width="70%"><?= $this->get_prefecture_name(); ?></td>
</tr>
</table>
<input type="hidden" name="func" value="" />
<input type="hidden" name="param" value="" />
<input type="hidden" name="prefecture_id" value="<?= $prefecture_id; ?>" />
<p class="text-center"><?= $this->get_switch(); ?></p>
</form>
</div>
<!-- /コンテンツ　-->
<?php 
//PHPブロック再開
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	デストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __destruct(){
		//親クラスのデストラクタを呼ぶ
		parent::__destruct();
	}
}

//ページを作成
$page_obj = new cnode();
//ヘッダ追加
$page_obj->add_child(cutil::create('cheader'));
//本体追加
$page_obj->add_child($main_obj = cutil::create('cmain_node'));
//フッタ追加
$page_obj->add_child(cutil::create('cfooter'));
//構築時処理
$page_obj->create();
//本体実行（表示前処理）
$main_obj->execute();
//ページ全体を表示
$page_obj->display();

?>
