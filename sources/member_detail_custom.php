<?php
/*!
@file member_detail_custom.php
@brief メンバー詳細（カスタムノード）
@copyright Copyright (c) 2024 Yamanoi Yasushi.
*/

//ライブラリをインクルード
require_once("common/libs.php");

$err_array = array();
$err_flag = 0;
$page_obj = null;
//プライマリキー
$member_id = 0;


//--------------------------------------------------------------------------------------
///	本体ノード
//--------------------------------------------------------------------------------------
class cmain_node extends cnode {
	public $member_address;
	public $par_address;
	//--------------------------------------------------------------------------------------
	/*!
	@brief	コンストラクタ
	*/
	//--------------------------------------------------------------------------------------
	public function __construct() {
		//親クラスのコンストラクタを呼ぶ
		parent::__construct();
		//プライマリキー
		global $member_id;
		if(isset($_GET['mid']) 
		//cutilクラスのメンバ関数をスタティック呼出
			&& cutil::is_number($_GET['mid'])
			&& $_GET['mid'] > 0){
			$member_id = $_GET['mid'];
		}
		//$_POST優先
		if(isset($_POST['member_id']) 
		//cutilクラスのメンバ関数をスタティック呼出
			&& cutil::is_number($_POST['member_id'])
			&& $_POST['member_id'] > 0){
			$member_id = $_POST['member_id'];
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  POST変数のデフォルト値をセット
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public function post_default(){
		cutil::post_default("member_minor",0);
		if(!isset($_POST['fruits']))$_POST['fruits'] = array();
		cutil::post_default("member_comment",'');
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	構築時の処理(継承して使用)
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public function create(){
		$param_arr = array(
			'cntl_header_name' => 'member',
			'head' => 'メンバー'
		);
		$this->member_address = cutil::create('caddress',$param_arr);
		$this->add_child($this->member_address);
		$param_arr = array(
			'cntl_header_name' => 'par',
			'head' => '保護者'
		);
		$this->par_address = cutil::create('caddress',$param_arr);
		$this->add_child($this->par_address);
		parent::create();
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
		global $member_id;
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
			if($member_id > 0){
				$member_obj = new cmember();
				//$_POSTにデータを読み込む
				$_POST = $member_obj->get_tgt(false,$member_id);
				if(cutil::array_chk($_POST)){
					//好きな果物を読み込む
					$_POST['fruits'] = $member_obj->get_all_fruits_match(false,$member_id);
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
		parent::paramchk();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	フルーツデータの追加／更新
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	function regist_fruits($member_id){
		$where = 'member_id = :member_id';
		$wherearr[':member_id'] = (int)$member_id;
		$change_obj = new crecord();
		$change_obj->delete_core(false,'fruits_match',$where,$wherearr,false);
		foreach($_POST['fruits'] as $key => $val){
			$dataarr = array();
			$dataarr['member_id'] = (int)$member_id;
			$dataarr['fruits_id'] = (int)$val;
			$change_obj->insert_core(false,'fruits_match',$dataarr,false);
		}
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief	メンバーの追加／更新。保存後自分自身を再読み込みする。
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	function regist(){
		global $member_id;
		$change_obj = new crecord();
		$dataarr = array();
		$dataarr['member_name'] = (string)$_POST['member_name'];
		$dataarr['member_prefecture_id'] = (int)$_POST['member_prefecture_id'];
		$dataarr['member_address'] = (string)$_POST['member_address'];
		$dataarr['member_minor'] = (int)$_POST['member_minor'];
		$dataarr['par_name'] = (string)$_POST['par_name'];
		$dataarr['par_prefecture_id'] = (int)$_POST['par_prefecture_id'];
		$dataarr['par_address'] = (string)$_POST['par_address'];
		$dataarr['member_comment'] = (string)$_POST['member_comment'];
		if($member_id > 0){
			$where = 'member_id = :member_id';
			$wherearr[':member_id'] = (int)$member_id;
			$change_obj->update_core(false,'member',$dataarr,$where,$wherearr,false);
			$this->regist_fruits($member_id);
			cutil::redirect_exit($_SERVER['PHP_SELF'] . '?mid=' . $member_id);
		}
		else{
			$mid = $change_obj->insert_core(false,'member',$dataarr,false);
			$this->regist_fruits($mid);
			cutil::redirect_exit($_SERVER['PHP_SELF'] . '?mid=' . $mid);
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
	@brief	メンバーIDの取得(新規の場合は「新規」)
	@return	メンバーID
	*/
	//--------------------------------------------------------------------------------------
	function get_member_id_txt(){
		global $member_id;
		if($member_id <= 0){
			return '新規';
		}
		else{
			return $member_id;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	メンバー未成年ラジオボタンの取得
	@return	メンバー未成年ラジオボタン文字列
	*/
	//--------------------------------------------------------------------------------------
	function get_member_minor_radio(){
		global $err_array;
		//メンバー性別のラジオボタンを作成
		$tgt = new cradio('member_minor');
		$tgt->add(0,'成人',$_POST['member_minor'] == 0);
		$tgt->add(1,'未成年',$_POST['member_minor'] == 1);
		$ret_str = $tgt->get($_POST['func'] == 'conf','&nbsp;');
		if(isset($err_array['member_minor'])){
			$ret_str .=  '<br /><span class="text-danger">' 
			. cutil::ret2br($err_array['member_minor']) 
			. '</span>';
		}
		return $ret_str;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	好きな果物チェックボックスの取得
	@return	好きな果物チェックボックス文字列
	*/
	//--------------------------------------------------------------------------------------
	function get_fruits_match_check(){
		global $err_array;
		global $member_id;
		//フルーツの一覧を取得
		$fruits_obj = new cfruits();
		$fruits_rows = $fruits_obj->get_all(false);
		//果物のチェックボックスを作成
		$tgt = new cchkbox('fruits[]');
		if(!isset($_POST['fruits']))$_POST['fruits'] = array();
		foreach($fruits_rows as $key => $val){
			$check = false;
			if(array_search($val['fruits_id'],$_POST['fruits']) !== false){
				$check = true;
			}
			$tgt->add($val['fruits_id'],$val['fruits_name'],$check);
		}
		$ret_str = $tgt->get($_POST['func'] == 'conf','&nbsp;');
		return $ret_str;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	メンバーコメントの取得
	@return	メンバーコメント文字列
	*/
	//--------------------------------------------------------------------------------------
	function get_member_comment(){
		global $err_array;
		$tgt = new ctextarea('member_comment',$_POST['member_comment'],'cols="70" rows="5"');
		$ret_str = $tgt->get($_POST['func'] == 'conf');
		return $ret_str;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	操作ボタンの取得
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	function get_switch(){
		global $member_id;
		$ret_str = '';
		if($_POST['func'] == 'conf'){
			$button = '更新';
			if($member_id <= 0){
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
		global $member_id;
//PHPブロック終了
?>
<!-- コンテンツ　-->
<div class="contents">
<?= $this->get_err_flag(); ?>
<h5><strong>メンバー詳細（カスタムノード）</strong></h5>
<form name="form1" action="<?= $_SERVER['PHP_SELF']; ?>" method="post" >
<a href="member_list_custom.php">一覧に戻る</a><br>
<table class="table table-bordered">
<tr>
<th class="text-center">ID</th>
<td width="70%"><?= $this->get_member_id_txt(); ?></td>
</tr>
<?php $this->member_address->display(); ?>
<tr>
<tr>
<th class="text-center">好きな果物</th>
<td width="70%"><?= $this->get_fruits_match_check(); ?></td>
</tr>
<th class="text-center">未成年かどうか</th>
<td width="70%"><?= $this->get_member_minor_radio(); ?></td>
</tr>
<?php $this->par_address->display(); ?>
<tr>
<th class="text-center">コメント</th>
<td width="70%"><?= $this->get_member_comment(); ?></td>
</tr>
</table>
<input type="hidden" name="func" value="" />
<input type="hidden" name="param" value="" />
<input type="hidden" name="member_id" value="<?= $member_id; ?>" />
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
$page_obj->add_child($cmain_obj = cutil::create('cmain_node'));
//フッタ追加
$page_obj->add_child(cutil::create('cfooter'));
//構築時処理
$page_obj->create();
//本体実行（表示前処理）
$cmain_obj->execute();
//ページ全体を表示
$page_obj->display();

?>
