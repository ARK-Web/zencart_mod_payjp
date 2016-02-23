<?php
/**
 * PAY.JP 用決済モジュールの言語ファイル.
 *
 * @package languageDefines
 * @copyright Copyright 2015 ARK-Web co., ltd.
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */
//

define('MODULE_PAYMENT_PAYJP_TEXT_TITLE', 'PAY.JP クレジットカード決済');
define('MODULE_PAYMENT_PAYJP_TEXT_DESCRIPTION', 'PAY.JP 用クレジットカード決済の機能を提供します。<br>※PHP 5.4以上');

// フォーム系
define('MODULE_PAYMENT_PAYJP_TEXT_INPUT_CARD_REMARK',   '下記のボタンからクレジットカード情報を入力してください。');


// エラー
define('MODULE_PAYMENT_PAYJP_TEXT_ERROR_TOKEN', 'クレジットカード情報の入力データが不正です。再度クレジットカード情報を入力してください。');

define('MODULE_PAYMENT_PAYJP_ERROR_LIBRARY_NOT_FOUND', 'PayJPのPHPライブラリが見つかりません。README.md からインストール方法を見直してください。');

define('MODULE_PAYMENT_PAYJP_API_ERROR_CARD',          '入力されたカード情報が間違っているか、カードの限度額に達してしまっている可能性があります。お手数ですがカード会社へお問い合わせください。');
define('MODULE_PAYMENT_PAYJP_API_ERROR_CLIENT',        '注文データが不正です。注文が完了できているかはお問い合わせください。');
define('MODULE_PAYMENT_PAYJP_API_ERROR_SERVER',        '現在システムが利用できません。オンライン決済ASPが一時的に利用不能になっているようです。時間をあけて再度ご注文ください。');
define('MODULE_PAYMENT_PAYJP_API_ERROR_UNKNOWN',       'レスポンスエラーが発生しました。他の決済方法を指定するかお問い合わせください。ご迷惑をお掛けしまして申し訳ございません。');
define('MODULE_PAYMENT_PAYJP_API_ERROR_CONNECT_FAILD', '接続エラーが発生しました。オンライン決済ASPが一時的に利用不能になっているようです。時間をあけて再度ご注文ください。');
define('MODULE_PAYMENT_PAYJP_API_ERROR_INVALID',       'システムエラーが発生しました。他の決済方法を指定するかお問い合わせください。ご迷惑をお掛けしまして申し訳ございません。');


// 最終確認画面
define('MODULE_PAYMENT_PAYJP_TEXT_CARD_INFO', '決済トークン');

// 注文履歴のコメント
define('MODULE_PAYMENT_PAYJP_TEXT_CHARGE_ID', 'PAY.JP 課金ID');
define('MODULE_PAYMENT_PAYJP_TEXT_CHARGE_FINGERPRINT', 'カード情報識別ID');
define('MODULE_PAYMENT_WEBPAYJP_TEXT_CHARGE_MULTI_PRODUCT_ETC', ' etc.');

define('MODULE_PAYMENT_PAYJP_TEXT_CUSTOMER_ID',  'PAY.JP 顧客ID');
define('MODULE_PAYMENT_PAYJP_TEXT_RECURSION_ID', '定期課金ID');
define('MODULE_PAYMENT_WEBPAYJP_TEXT_RECURSION_MODEL',  '定期課金 商品型番');
define('MODULE_PAYMENT_PAYJP_TEXT_RECURSION_PERIOD', '定期間隔');
define('MODULE_PAYMENT_PAYJP_TEXT_RECURSION_FIRST',  '初回課金日');

?>
