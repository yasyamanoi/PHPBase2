<?php
/*!
@file libs.php
@brief ライブラリをすべてインクルード。各ページはこのファイルをインクルードする
@copyright Copyright (c) 2024 Yamanoi Yasushi.
*/
//定数の読み込み
require_once("config.php");
//ユーティリティクラス他
require_once("function.php");
//コントロールクラス他
require_once("controls.php");
//コントロールクラスエクストラ（オプション）
require_once("controls_ex.php");
//PDO接続初期化
require_once("pdointerface.php");
//ブロッククラス
require_once("block.php");
//他ユーティリティ
require_once("contents_func.php");
//コンテンツに合わせ定義されるもの
require_once("contents_db.php");
require_once("contents_blocks.php");


