# Jigokumimi API

## 説明

音楽収集アプリ「Jigokumimi」のバックエンドAPIです。

Jigokumimiから連携される利用ユーザーの位置情報、及びSpotify利用データを元に

近くの利用ユーザーのSpotify利用データを提供します。

## 開発環境

Visual Studio Codeの拡張機能「Remote Container」を用いて、Dockerコンテナ上に開発環境を構築します。

### 必要要件(ローカルPC)

- Docker for Mac 2.2.0.3
- 拡張機能「Remote Container」をインストールしたVisual Studio Code

### Docker構成

| コンテナ名 | ミドルウェア | バージョン | 備考                     |
| ---------- | ------------ | ---------- | ------------------------ |
| app        | php          | 7.4.2      | Laravel(APIプロジェクト) |
| web        | nginx        | 1.17       | 公開用Webサーバー        |
| db         | mysql        | 8.0.15     | 開発用DB                 |
| db_testing | mysql        | 8.0.15     | 自動テスト用DB           |

### 構築手順

1. リポジトリクローン

    ```
    $ git clone https://github.com/auto-ororo/vinyl-bucket-api.git
    ```

2. クローンしたリポジトリをVisual Studio Codeで開く

3. コマンドパレット(Cmd + P)より下記を入力してDockerコンテナ起動

    ```
    Remote rebuild and Reopen in Container...
    ```

4. 下記でAPIにアクセス

    ```
    http://127.0.0.1:(port)
    # (port)は「.env」の「WEB_PORT」で指定したポート
    ```

## テスト

Todo

## デプロイ
 
Todo

## 作者

Todo

## ライセンス
 
Todo