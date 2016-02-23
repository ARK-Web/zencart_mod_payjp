# zencart_mod_payjp

Zen Cart PayJP決済モジュール
====

ark-web/zencart_mod_payjp - Zen Cartで動作するPayJP決済モジュールです。

この決済モジュールでは、通常課金にのみ対応します。定期課金対応は info@ark-web.jp までご相談ください。


### 動作環境
* PHP 5.3.3 以上必須
* PayJP提供のPHPライブラリ（Install 参照）


### 動作確認済環境：
* Zen Cart 1.3.0.2 jp8 UTF版
* Zen Cart 1.5.1   日本語版


### はじめかた
PayJP公式はこちら→ https://pay.jp/

+ 1.PayJPへ登録 or ログインしてから
+ 2.右上の歯車アイコンから「ユーザー設定」を指定して「API KEY」タブを開いて、APIキーを控えておき
+ 3.Zen Cartの管理画面の「モジュール＞支払い」からPayJPモジュールを【インストール】してAPIキーの内容を設定して保存する。

（詳細は Install 参照）

![PayJPのAPIキーをZen CartのPayJPモジュールへ登録する](https://raw.github.com/wiki/ARK-Web/zencart_mod_payjp/images/setup.png)


## Install

**※ファイル・DBのバックアップを取っておくこと！**

+ 1.https://github.com/ark-web/zencart_mod_payjp/ にアクセスして【Download ZIP】でダウンロードします。
+ 2.zipを解凍して htdocs/includes/ 配下をZen Cartへアップロードします。
+ 3.composerを利用できる場合は3-1へ、利用できない場合は3-2に進みます。
+ 3-1.composerを利用してPayJPのPHPライブラリ(https://github.com/payjp/payjp-php )をインストールします。
  + htdocs/includes/modules/payment/ に composer.json が同梱されているのでそれを利用します。成功すると、vendorディレクトリが作られます。

  ```
	$ cd htdocs/includes/modules/payment/
	$ php composer.phar install
  ```

  ※PayJPのPHPライブラリについてのライセンス規約は vendor/payjp-php/LICENSE をご一読ください。
+ 3-2.PayJPのPHPライブラリ(https://github.com/payjp/payjp-php )からソースファイル群のzipファイルを落としてきて配置します。(VERSION が0.0.2 については動作確認済みです)
  + htdocs/includes/modules/payment/ にダウンロードした payjp-php-master.zip をアップロード後、解凍して payjp-php とリネームします。

  ```
	$ unzip master.zip
	$ mv payjp-php-master payjp-php
  ```

  ※PayJPのPHPライブラリについてのライセンス規約は payjp-php/LICENSE をご一読ください。
+ 4.Zen Cart管理画面にログインして「モジュール＞支払い＞PayJP クレジットカード決済」をインストールします。
+ 5.編集の「公開キー」と「秘密キー」を設定します。
  + PayJPにログインして「ユーザー設定」からAPIキーに書いてある内容を設定してください。
  その他、任意で「適用地域」「オーダーステータス」「表示の順番」を変更します。

![PayJPのAPIキーをZen CartのPayJPモジュールへ登録する](https://raw.github.com/wiki/ARK-Web/zencart_mod_payjp/images/setup.png)


## Licence

GPL v.2


## Author

[ark-web](https://github.com/ark-web)

