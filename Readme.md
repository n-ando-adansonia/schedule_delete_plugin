# 準備
wp-config.phpに以下を追加するとwp-contentにdebug.logが書き出しされる。
```
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false ); // エラーメッセージを画面に表示しない
@ini_set( 'display_errors', 0 ); // エラーメッセージを画面に表示しない
```

# 使い方
1. scheduled-media-deletion プラグインを入れて有効化
1. メディアライブラリを開いて画像を選択し「編集」
1. 画面右側のBOXに日付を設定する箇所があるので、削除したい日を設定して保存
1. 指定の日付になったらWP-CRONで画像は自動削除される

## URLパラメータをつけてアクセスするとテスト用関数が実行する
管理者ユーザーでログイン状態が必須。

```sh
# 例
http://your-domain.jp/wordpress/?smd_manual_trigger

# 手動トリガー用の関数（手動でメディア削除関数を実行する）
?smd_manual_trigger

# Cronイベントをログに記録する関数（WP-Cronに登録されているイベントをログに出す）
?smd_log_cron_events

# メタデータの確認関数（カスタムフィールド_smd_deletion_dateのデータを確認）
?smd_check_meta 

# クエリのテスト関数（削除対象となるメディアのレスポンスデータを確認）
?smd_test_meta_query 
```