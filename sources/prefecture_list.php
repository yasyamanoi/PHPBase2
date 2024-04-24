<?php
/*!
@file prefecture_list.php
@brief 都道府県一覧
@copyright Copyright (c) 2024 Yamanoi Yasushi.
*/

//ライブラリをインクルード
require_once("common/libs.php");

$err_array = array();
$err_flag = 0;
$page_obj = null;

//ページの設定
//デフォルトは1
$page = 1;
//もしページが指定されていたら
if(isset($_GET['page']) 
    //なおかつ、数字だったら
    && cutil::is_number($_GET['page'])
    //なおかつ、0より大きかったら
    && $_GET['page'] > 0){
    //パラメータを設定
    $page = $_GET['page'];
}

//1ページのリミット
$limit = 20;
$prefecture_rows = array();


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
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	データ読み込み
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	function readdata(){
		global $limit;
		global $prefecture_rows;
		global $page;
		$obj = new cprefecture();
		$from = ($page - 1) * $limit;
		$prefecture_rows = $obj->get_all(false,$from,$limit);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	削除
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	function deljob(){
		if(isset($_POST['param']) && $_POST['param'] > 0){
			$where = 'prefecture_id = :prefecture_id';
			$wherearr[':prefecture_id'] = (int)$_POST['param'];
			$change_obj = new crecord();
			$change_obj->delete_core(false,'prefecture',$where,$wherearr,false);
		}
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
		if(is_null($page_obj)){
			return;
		}
		if(isset($_POST['func'])){
			switch($_POST['func']){
				case "del":
					//削除操作
					$this->deljob();
					//再読み込みのためにリダイレクト
					cutil::redirect_exit($_SERVER['PHP_SELF']);
				break;
				default:
					echo 'エラー';
					exit();
				break;
			}
		}
		//データの読み込み
		$this->readdata();
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
	@brief	都道府県のリストを得る
	@return	都道府県リストの文字列
	*/
	//--------------------------------------------------------------------------------------
	public function get_prefecture_rows(){
		global $prefecture_rows;
		global $page;
		$retstr = '';
		$urlparam = '&page=' . $page;
		$rowscount = 1;
		if(count($prefecture_rows) > 0){
			foreach($prefecture_rows as $key => $value){
				$javamsg =  '【' . $value['prefecture_name'] . '】';
				$str =<<<END_BLOCK

<tr>
<td width="20%" class="text-center">
{$value['prefecture_id']}
</td>
<td width="65%" class="text-center">
<a href="prefecture_detail.php?pid={$value['prefecture_id']}{$urlparam}">{$value['prefecture_name']}</a>
</td>
<td width="15%" class="text-center">
<input type="button" value="削除確認" onClick="del_func_form({$value['prefecture_id']},'{$javamsg}');" />
</td>
</tr>
END_BLOCK;
			$retstr .= $str;
			$rowscount++;
			}
		}
		else{
			$retstr =<<<END_BLOCK

<tr><td colspan="3" class="text-left">都道府県が見つかりません</td></tr>
END_BLOCK;
		}
		return $retstr;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	ページャーを得る
	@return	ページャー文字列
	*/
	//--------------------------------------------------------------------------------------
	function get_page_block(){
		global $limit;
		global $page;
		$obj = new cprefecture();
		$allcount = $obj->get_all_count(false);
		$ctl = new cpager($_SERVER['PHP_SELF'],$allcount,$limit);
		return $ctl->get('page',$page);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	POSTするURLを得る
	@return	POSTするURL
	*/
	//--------------------------------------------------------------------------------------
	function get_tgt_uri(){
		global $page;
		return $_SERVER['PHP_SELF'] 
		. '?page=' . $page;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  表示(継承して使用)
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public function display(){
//PHPブロック終了
?>
<!-- コンテンツ　-->
<div class="contents">
<h5><strong>都道府県一覧</strong></h5>
<form name="form1" action="<?= $this->get_tgt_uri(); ?>" method="post" >
<p><a href="prefecture_detail.php">新規</a></p>
<p><?= $this->get_page_block(); ?></p>
<table class="table table-bordered">
<tr>
<th class="text-center">都道府県ID</th>
<th class="text-center">都道府県名</th>
<th class="text-center">操作</th>
</tr>
<?= $this->get_prefecture_rows(); ?>
</table>
<input type="hidden" name="func" value="" >
<input type="hidden" name="param" value="" >
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
