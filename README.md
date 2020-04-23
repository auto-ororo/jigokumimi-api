# Jigokumimi API

## 説明

音楽収集アプリ「Jigokumimi」のバックエンドAPIです。

Jigokumimiアプリから連携される利用ユーザーの位置情報、及びSpotify利用データを元に

Jigokumimi利用ユーザーが聴いているSpotify上のトラック/アーティストを検索します｡

### 機能一覧

- JWTトークン認証機能
- ユーザー登録･変更機能
- トラック/アーティスト検索機能
- トラック/アーティスト検索履歴参照･削除機能

## 技術スタック

### 開発言語

PHP 7.4.2

### フレームワーク

Laravel 6.2

### 採用ライブラリ

※Laravelプロジェクト作成時に付随してインストールされるライブラリは除外

| ライブラリ   | バージョン | 概要                       |
| ------------ | ---------- | -------------------------- |
| JWT-Auth     | 1.0.0-rc.5 | JWTトークン認証            |
| Laravel-UUID | 3.0        | IDを自動採番からUUIDに変更 |

## 開発環境

Visual Studio Codeの拡張機能「Remote Container」を用いて、Docker上に開発環境を構築します。

### 必要要件(ローカルPC)

- (Mac)Docker for Mac 2.2.0.3 以上
- (Linux) Docker 19.03.7 以上
- docker-compose 1.25.4 以上
- 拡張機能「Remote Container」をインストールしたVisual Studio Code

### Docker構成

| コンテナ名 | ミドルウェア | バージョン | 備考                     |
| ---------- | ------------ | ---------- | ------------------------ |
| app        | php          | 7.4.2      | Laravel(APIプロジェクト) |
| web        | nginx        | 1.17       | API公開用Webサーバー     |
| db         | mariadb      | 10.4       | 開発用DB                 |
| db_testing | mariadb      | 10.4       | PHPUnitテスト用DB        |

### 開発環境構築手順

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

4. Visual Studio Codeのコマンドパレット(Cmd/Ctrl + P)より下記を入力してDockerコンテナ起動する

    ```bash
    Remote rebuild and Reopen in Container...
    ```

5. (コンテナ)Composerモジュールインストール

    ```bash
    $ composer install
    ```

6. (コンテナ)開発用DBのマイグレーション･初期データ投入

    ```bash
    $ php artisan migrate:fresh --seed
    ```

7. (コンテナ)自動テスト用DBのマイグレーション･初期データ投入

    ```bash
    $ php artisan migrate:fresh --seed --env=testing
    ```

8. (コンテナ)アプリケーションキー(APP_KEY)生成

    ```bash
    $ php artisan key:generate
    ```

9. (コンテナ)JWTキー(JWT_SECRET)生成

    ```bash
    $ php artisan jwt:secret
    ```

10. 下記URLでAPIにアクセス

    ```bash
    http://127.0.0.1:(port)
    # (port)は「.env」の「WEB_PORT」で指定したポート
    ```

    ｢Jigokumimi｣が画面上に表示されれば成功｡

## テスト

Dockerコンテナ内で以下を実行する｡
キャッシュがクリアされた後にテストが実行される｡

```bash
$ composer tf
```