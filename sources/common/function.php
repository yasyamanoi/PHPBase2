<?php
/*!
@file function.php
@brief ユーティリティクラス
@copyright Copyright (c) 2024 Yamanoi Yasushi.
*/


////////////////////////////////////
//クラスブロック

//--------------------------------------------------------------------------------------
///	ユーティリティ関数群(スタティック呼出をする)
//--------------------------------------------------------------------------------------
class cutil {
	//--------------------------------------------------------------------------------------
	/*!
	@brief	指定したURLにリダイレクトして終了
	@param[in]	$url	リダイレクトするURL
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public static function redirect_exit($url){
		$str = "Location: ".  $url;
		header($str);
		//リダイレクトしたのでexit
		exit();
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	すべて数字かどうかを判別
	@param[in]	$value 判別する文字列
	@return	true　数字　false数字以外が混じってる
	*/
	//--------------------------------------------------------------------------------------
	public static function is_number($value){
		if($value == '')return false;
		if(preg_match('/^[0-9]+$/',$value)){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	すべて数字とハイフンかを判別
	@param[in]	$value 判別する文字列
	@return	true　数字かハイフン　false数字以外が混じってる
	*/
	//--------------------------------------------------------------------------------------
	public static function is_num_hyphen($value){
		if($value == '')return false;
		if(preg_match('/^[-0-9]+$/',$value)){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	すべて半角かどうかを判別
	@param[in]	$value 判別する文字列
	@return	true　半角　false半角以外が混じってる
	*/
	//--------------------------------------------------------------------------------------
	public static function is_hankaku($value){
		$strlen = strlen($value);
		$mbstrlen = mb_strlen($value,PHP_CHARSET);
		if($strlen == $mbstrlen){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	すべて数字で、かつ範囲に入ってるか判別
	@param[in]	$value 判別する文字列
	@param[in]	$from 範囲の始まり
	@param[in]	$to 範囲の終わり
	@return	条件に合えばtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function number_range($value,$from,$to){
		if(cutil::is_number($value) &&
			$value >= $from && $value <= $to){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	文字列のサイズ（マルチバイト）が範囲に入ってるか判別
	@param[in]	$str 判別する文字列
	@param[in]	$from 範囲の始まり
	@param[in]	$to 範囲の終わり
	@return	条件に合えばtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function chk_mb_strlen_size($str,$from,$to){
		$len = mb_strlen($str,PHP_CHARSET);
		if($len >= $from && $len <= $to){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	文字列のサイズ（シングルバイト）が範囲に入ってるか判別
	@param[in]	$str 判別する文字列
	@param[in]	$from 範囲の始まり
	@param[in]	$to 範囲の終わり
	@return	条件に合えばtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function chk_strlen_size($str,$from,$to){
		$len = strlen($str);
		if($len >= $from && $len <= $to){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	特殊文字をhtmlエンティティに変換
	@param[in]	$value 変換するする文字列
	@return	変換後の文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function escape($value){
		return htmlspecialchars($value,ENT_QUOTES,PHP_CHARSET);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	改行を<br />に変換
	@param[in]	$str 変換するする文字列
	@return	変換後の文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function ret2br($str){
		$order = array("\r\n", "\n", "\r");
		$replace = '<br>';
		return str_replace($order, $replace, $str);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	改行を<br>に変換して出力
	@param[in]	$str 変換するする文字列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public static function echo_ret2br($str){
		echo cutil::ret2br($str);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	print_rを改行出力を有効にして出力
	@param[in]	$str 変換するする文字列
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public static function print_r2($str){
		echo '<pre>';
		print_r($str);
		echo  '</pre>';
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	パスワード暗号化(md5)
	@param[in]	$password 暗号化する文字列
	@return	暗号化した文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function pw_encode($password){
		$seed = null;
		for ($i = 1; $i <= 8; $i++){
			$seed .= substr('0123456789abcdef',rand(0,15),1);
		}
		return hash("md5",$seed . $password) . $seed;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	パスワードチェック(md5)
	@param[in]	$password 暗号化しないパスワード
	@param[in]	$stored_value 暗号化したパスワード
	@return	成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function pw_check($password,$stored_value){
		$stored_seed = substr($stored_value,32,8);
		if(hash("md5",$stored_seed . $password) . $stored_seed == $stored_value){
			return true;
		}
		else{
			return false;
		}
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief	opensslによる暗号化
	@param[in]	$plaintext プレーン文字列
	@param[out]	$iv Baase64エンコードされたivが入る参照
	@param[out]	$tag Baase64エンコードされたtagが入る参照
	@return	openssl暗号化した文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function encrypt($plaintext,&$iv,&$tag){
		$cipher = "aes-128-gcm";
		if (in_array($cipher, openssl_get_cipher_methods())){
			$ivlen = openssl_cipher_iv_length($cipher);
			$iv = openssl_random_pseudo_bytes($ivlen);
			$ciphertext = openssl_encrypt($plaintext, $cipher, MY_AES_KEY, $options=0, $iv,$tag);
			$iv = base64_encode($iv);
			$tag = base64_encode($tag);
			return $ciphertext;
		}
		else{
			return null;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	opensslによる複合化
	@param[in]	$ciphertext 暗号化文字列
	@param[in]	$iv Baase64エンコードされたiv
	@param[in]	$tag Baase64エンコードされたtag
	@return	複合化化した文字列
	*/
	//--------------------------------------------------------------------------------------
	public static function decrypt($ciphertext,$iv,$tag){
		$cipher = "aes-128-gcm";
		if (in_array($cipher, openssl_get_cipher_methods())){
			$original_plaintext = openssl_decrypt($ciphertext, $cipher, MY_AES_KEY, $options=0, 
				base64_decode($iv),
				base64_decode($tag));
			return $original_plaintext;
		}
		else{
			return null;
		}
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief	メールフォーマットチェック（フォーマットのみチェックする）
	@param[in]	$mail メールアドレス
	@return	成功すればtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function chek_mail_format($mail){
		if(preg_match('/^[-a-zA-Z0-9_\.]+@([-a-zA-Z0-9_\.]+\.[-a-zA-Z0-9_]+$)/',$mail)){
			return true;
		}
		else{
			return false;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	mb_send_mailをデバッグする
	@param[in]	$To 送信先
	@param[in]	$Subject タイトル
	@param[in]	$Message 本文
	@param[in]	$Headers ヘッダ
	@param[in]	$brflg 改行をbrにするかどうか
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public static function mb_send_mail_chk($To, $Subject, $Message, $Headers,$brflg = false){
		$retcode = '';
		if($brflg){
			$retcode = '<br />';
		}
		$str =<<< END_BLOCK
{$retcode}
--start-----mb_send_mail_chk--------------{$retcode}
To: {$To}{$retcode}
Subject: {$Subject}{$retcode}
Headers: {$Headers}{$retcode}
Massage:{$retcode}
{$Message}{$retcode}
--end-------mb_send_mail_chk--------------{$retcode}
END_BLOCK;
		echo $str;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	指定した年月のカレンダーを得る
	@param[in]	$year 年
	@param[in]	$month 月
	@return	カレンダーの配列
	*/
	//--------------------------------------------------------------------------------------
	public static function get_calender($year = '',$month = ''){
		if($year == '' || $month == ''){
			$year = date('Y');
			$month = date('n');
		}
		else{
			if(!cutil::is_number((string)$year) || !cutil::is_number((string)$month)){
				$year = date('Y');
				$month = date('n');
			}
		}
		$last_day = date('j', mktime(0, 0, 0, $month + 1, 0, $year));
		$cal_arr = array();
		$j = 0;

		for ($i = 1; $i <= $last_day; $i++) {
			$week_day = date('w', mktime(0, 0, 0, $month, $i, $year));
			if ($i == 1) {
			    for ($k = 1; $k <= $week_day; $k++) {
					$cal_arr[$j]['day'] = '';
					$j++;
				}
			}
			$cal_arr[$j]['day'] = $i;
			$j++;
			if ($i == $last_day) {
				for ($l = 1; $l <= 6 - $week_day; $l++) {
					$cal_arr[$j]['day'] = '';
					$j++;
				}
			}
		}
		return $cal_arr;
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief	$_POSTのデフォルト値の設定
	@param[in]	$name POST変数名
	@param[in]	$val デフォルトの値
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public static function post_default($name,$val){
		if(!isset($_POST[$name])){
			$_POST[$name] = $val;
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief	$_GETのデフォルト値の設定
	@param[in]	$name POST変数名
	@param[in]	$val デフォルトの値
	@return	なし
	*/
	//--------------------------------------------------------------------------------------
	public static function get_default($name,$val){
		if(!isset($_GET[$name])){
			$_GET[$name] = $val;
		}
	}

	//--------------------------------------------------------------------------------------
	/*!
	@brief  オブジェクト作成
	@param[in]	$class_name クラス名
	@param[in]	$param パラメータ
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public static function create($class_name,$param = null){
		$obj = null;
		if($param != null){
			$obj = new $class_name($param);
		}
		else{
			$obj = new $class_name();
		}
		return $obj;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  配列が有効かどうか
	@param[in]	$arr 配列
	@return 配列が有効で要素が1つ以上あればtrue
	*/
	//--------------------------------------------------------------------------------------
	public static function array_chk(&$arr){
		if(is_array($arr) && count($arr) > 0){
			return true;
		}
		return false;
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  配列を再帰的にエスケープする
	@param[in]	$arr 配列
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public static function array_escape(&$arr){
		foreach($arr as $key => &$val){
			if(is_array($val)){
				cutil::array_escape($val);
			}
			else{
				$val = cutil::escape($val);
			}
		}
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  変数をエスケープする
	@param[in]	$val 変数
	@return なし
	*/
	//--------------------------------------------------------------------------------------
	public static function val_escape(&$val){
		$val = cutil::escape($val);
	}
	//--------------------------------------------------------------------------------------
	/*!
	@brief  空白区切りの文字列を配列にセット
	@param[in]	$str 文字列
	@return セットされた配列
	*/
	//--------------------------------------------------------------------------------------
	public static function space_str2arr($str){
		$str = mb_convert_kana($str,"s",'UTF-8');
		$chk_arr = explode(' ',$str);
		$ret_arr = array();
		foreach($chk_arr as $key => $val){
			if($val != ''){
				$ret_arr[] = trim($val);
			}
		}
		return $ret_arr;
	}


}

