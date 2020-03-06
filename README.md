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
| db         | mariadb      | 10.4       | 開発用DB                 |
| db_testing | mariadb      | 10.4       | 自動テスト用DB           |

### 構築手順

1. リポジトリクローン

    ```bash
    $ git clone https://github.com/auto-ororo/vinyl-bucket-api.git
    ```

2. 設定ファイル(.env)をサンプルから作成

    ```bash
    $ cp .env.docker-compose.example .env # docker-compose
    $ cp src/.env.laravel.example src/.env # Laravel
    ```

    ※開発環境に応じて.env内の値を変更する（変更しなくても動く）

3. クローンしたリポジトリをVisual Studio Codeで開く

4. Visual Studio Codeのコマンドパレット(Cmd/Ctrl + P)より下記を入力してDockerコンテナ起動

    ```bash
    Remote rebuild and Reopen in Container...
    ```

5. (コンテナ)Composerモジュールインストール

    ```bash
    $ composer install
    ```

6. (コンテナ)DBのマイグレーション

    ```bash
    $ php artisan migrate
    ```

7. (コンテナ)Laravelのアプリケーションキー生成

    ```bash
    $ php artisan key:generate
    ```

8. 下記URLでAPIにアクセス

    ```bash
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
