<?php
/**
 * pay_jp.php payment module class for PAY.JP payment method.
 *
 * @package paymentMethod
 * @copyright Copyright 2015 ARK-Web co., ltd.
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: $
 */

class pay_jp extends base {
	var $code, $title, $description, $enabled;

	function __construct() {
		global $order;
		
		$this->code = 'pay_jp';
		$this->title = MODULE_PAYMENT_PAYJP_TEXT_TITLE;
		$this->description = MODULE_PAYMENT_PAYJP_TEXT_DESCRIPTION;
		$this->sort_order = MODULE_PAYMENT_PAYJP_SORT_ORDER;
		$this->enabled = ((MODULE_PAYMENT_PAYJP_STATUS == 'True') ? true : false);
		$this->order_status = DEFAULT_ORDERS_STATUS_ID;
		if ((int)MODULE_PAYMENT_PAYJP_ORDER_STATUS_ID > 0) {
			$this->order_status = MODULE_PAYMENT_PAYJP_ORDER_STATUS_ID;
		}
		if (is_object($order)) $this->update_status();
	}

	function update_status() {
		global $order, $db;
		
		// 適用地域のチェック
		if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYJP_ZONE > 0) ) {
			$check_flag = false;
			$check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYJP_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
			while (!$check->EOF) {
				if ($check->fields['zone_id'] < 1) {
					$check_flag = true;
					break;
				} elseif ($check->fields['zone_id'] == $order->delivery['zone_id']) {
					$check_flag = true;
					break;
				}
				$check->MoveNext();
			}
			if ($check_flag == false) {
				$this->enabled = false;
			}
		}
	}

	function javascript_validation() {
		return false;
	}

	function selection() {
		return array('id' => $this->code,
					 'module' => $this->title,
					 'fields' => $this->_create_input_fields());
	}
	function _create_input_fields() {
		return array(
			array('title' => MODULE_PAYMENT_PAYJP_TEXT_INPUT_CARD_REMARK,
				  'field' => '<script src="https://checkout.pay.jp/" class="payjp-button" data-key="'. MODULE_PAYMENT_PAYJP_API_PUBLICKEY .'" data-lang="ja" data-partial="true"></script>'
			)
		);
	}

	function pre_confirmation_check() {
		$token = $_POST['payjp-token'];
		if (! $token) {
			global $messageStack;
			$messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYJP_TEXT_ERROR_TOKEN, 'error');
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
		}
		$_SESSION['payjp_token'] = $token;
	}

	function confirmation() {
		return false;
	}

	function process_button() {
		return false;
	}

	function before_process() {
		global $messageStack, $order, $currencies;
		
		try {
			if (is_readable(DIR_WS_MODULES .'payment/vendor/autoload.php')) {
				require_once DIR_WS_MODULES .'payment/vendor/autoload.php';
			} else if (is_readable(DIR_WS_MODULES .'payment/payjp-php/init.php')) {
				require_once DIR_WS_MODULES .'payment/payjp-php/init.php';
			} else {
				// PayJPのPHPライブラリがない
				$messageStack->add_session('checkout_payment', MODULE_PAYMENT_WEBPAYJP_ERROR_LIBRARY_NOT_FOUND, 'error');
				zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
			}
			
			\Payjp\Payjp::setApiKey(MODULE_PAYMENT_PAYJP_API_SECRETKEY);
			// トークンを使って顧客オブジェクトを作っておく
			$customer = \Payjp\Customer::create(array(
				'card'  => $_SESSION['payjp_token'],
				"email" => $order->customer['email_address'],
			));
			
			// トークン決済
			$charge_opt = array(
				'amount'   => $currencies->value($order->info['total']),
				'currency' => 'jpy',
				'customer' => $customer->id,
				"description" => $order->products[0]['model'] 
				                 . (count($order->products) >= 2 ? MODULE_PAYMENT_PAYJP_TEXT_CHARGE_MULTI_PRODUCT_ETC : ''),
			);
			if (defined('MODULE_PAYMENT_PAYJP_CAPTURE') and MODULE_PAYMENT_PAYJP_CAPTURE == 'False') {
				$charge_opt['capture'] = MODULE_PAYMENT_PAYJP_CAPTURE;
			}
			if (defined('MODULE_PAYMENT_PAYJP_CAPTURE') and MODULE_PAYMENT_PAYJP_CAPTURE == 'False'
			and defined('MODULE_PAYMENT_PAYJP_EXPIRY_DAYS') and MODULE_PAYMENT_PAYJP_EXPIRY_DAYS != '') {
				$charge_opt['expiry_days'] = MODULE_PAYMENT_PAYJP_EXPIRY_DAYS;
			}
			$charge = \Payjp\Charge::create($charge_opt);
			
			$_SESSION['payment_payjp'][] = array(
				'type'        => 'CHARGE',
				'customer_id' => $customer->id,
				'id'          => $charge->id,
				'fingerprint' => $charge->card->fingerprint,
			);
		} catch (\Payjp\ErrorResponse\ErrorResponseException $e) {
			$error = $e->data->error;
			switch ($error->type) {
				case 'card_error':
					// カードに関するエラー
					$messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYJP_API_ERROR_CARD, 'error');
					break;
				case 'client_error':
					// リクエストエラー
					$messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYJP_API_ERROR_CLIENT, 'error');
					break;
				case 'sever_error':
					// PAY.JPや決済ネットワーク側のエラー
					$messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYJP_API_ERROR_SERVER, 'error');
					break;
				default:
					// 未知のエラー
					$messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYJP_API_ERROR_UNKNOWN, 'error');
					break;
			}
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
		} catch (\Payjp\ApiException $e) {
			// APIからのレスポンスが受け取れない場合。接続エラーなど
			$messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYJP_API_ERROR_CONNECT_FAILD, 'error');
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
		} catch (\Exception $e) {
			// PAY.JP とは関係ない例外の場合
			$messageStack->add_session('checkout_payment', MODULE_PAYMENT_PAYJP_API_ERROR_INVALID, 'error');
			zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
		}
		return false;
	}

	function write_error_log($e) {
		$log_file = '/pay_jp_error.log';
		if (defined("DIR_FS_LOGS")) {
			$log_file = DIR_FS_LOGS . $log_file;
		} else {
			if (! is_writable(DIR_FS_CATALOG .'log')) return; // htodcs/log/ディレクトリが無ければ諦める
			$log_file = DIR_FS_CATALOG .'log'. $log_file;
		}
		$fh = fopen($log_file, "a");
		if (!$fh) return ;
		fwrite($fh, sprintf("%s message:%s\n", date("Y-m-d"), $e->getCode(), $e->getMessage()));
	}

	function after_process() {
		global $insert_id, $order, $db;
		
		if (! isset($_SESSION['payment_payjp'])
		or  count($_SESSION['payment_payjp']) == 0) return;
		
		$newline = (strlen($order->info['comments']) > 0 ? "\n\n" : "");
		foreach ($_SESSION['payment_payjp'] as $payment_payjp) {
			$order->info['comments'] .= $newline 
									 . MODULE_PAYMENT_PAYJP_TEXT_CHARGE_ID .": ". $payment_payjp['id'] . "\n"
									 . MODULE_PAYMENT_PAYJP_TEXT_CHARGE_FINGERPRINT .": ". $payment_payjp['fingerprint'];
		}
		$db->Execute("update " . TABLE_ORDERS_STATUS_HISTORY . " set comments='" . $order->info['comments'] . "' where orders_id='$insert_id'");
		
		// orders のステータスも更新
		$sql_data_array = array(
			'orders_status' => $this->order_status,     // ステータス
		);
		zen_db_perform(TABLE_ORDERS, $sql_data_array, "update", "orders_id=".$insert_id);
		// orders_status_history のステータスも更新
		$sql_data_array = array(
			'orders_status_id' => $this->order_status,     // ステータス
		);
		zen_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array, "update", "orders_id=".$insert_id);
		
		// delete SESSION
		unset($_SESSION['payjp_token']);
		unset($_SESSION['payment_payjp']);
		return false;
	}

	function get_error() {
		return false;
	}

	function check() {
		global $db;
		if (!isset($this->_check)) {
			$check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYJP_STATUS'");
			$this->_check = $check_query->RecordCount();
		}
		return $this->_check;
	}

	function install() {
		global $db, $messageStack;
		if (defined('MODULE_PAYMENT_PAYJP_STATUS')) {
			$messageStack->add_session('PAYJP module already installed.', 'error');
			zen_redirect(zen_href_link(FILENAME_MODULES, 'set=payment&module=cod', 'NONSSL'));
			return 'failed';
		}
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)               values ('Enable PAY.JP Module', 'MODULE_PAYMENT_PAYJP_STATUS', 'True', 'PAY.JP決済を有効にしますか?', '6', '1', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('適用地域', 'MODULE_PAYMENT_PAYJP_ZONE', '0', '適用地域を選択すると、選択した地域のみで利用可能となります。', '6', '2', 'zen_get_zone_class_title', 'zen_cfg_pull_down_zone_classes(', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)                             values ('表示の順番', 'MODULE_PAYMENT_PAYJP_SORT_ORDER', '0', '表示の順番を設定します。数値順に表示されます。', '6', '0', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('オーダーステータス', 'MODULE_PAYMENT_PAYJP_ORDER_STATUS_ID', '0', 'クレジットカード決済成功時のオーダーステータスを設定してください。', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");
		
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('公開キー', 'MODULE_PAYMENT_PAYJP_API_PUBLICKEY', '', 'PAY.JPにログインして「ユーザー設定」からAPI KEYに書いてある内容を設定してください。', '6', '3',  now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('秘密キー',   'MODULE_PAYMENT_PAYJP_API_SECRETKEY', '', 'PAY.JPにログインして「ユーザー設定」からAPI KEYに書いてある内容設定してください。',   '6', '3',  now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('支払を確定するか', 'MODULE_PAYMENT_PAYJP_CAPTURE',     'True',  'true:支払を確定します。false:カードの認証と支払い額確保のみ行います。',   '6', '4', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
		$db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('有効期限日数',     'MODULE_PAYMENT_PAYJP_EXPIRY_DAYS', '7',     '支払を確定しない場合(false）のみ、有効期限日数のチェックをします（デフォルト=7日, 1～60日で指定）。',   '6', '4', now())");
	}

	function remove() {
		global $db;
		$db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}

	function keys() {
		return array('MODULE_PAYMENT_PAYJP_STATUS', 'MODULE_PAYMENT_PAYJP_ZONE', 'MODULE_PAYMENT_PAYJP_ORDER_STATUS_ID', 'MODULE_PAYMENT_PAYJP_SORT_ORDER', 'MODULE_PAYMENT_PAYJP_API_PUBLICKEY', 'MODULE_PAYMENT_PAYJP_API_SECRETKEY', 'MODULE_PAYMENT_PAYJP_CAPTURE', 'MODULE_PAYMENT_PAYJP_EXPIRY_DAYS');
	}
}
