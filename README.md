About opKdtPlugin
=================

はじめに
--------

これは
Kawahara用OpenPNE3開発支援ツール
ということで Kawahara Develop Tool
略してKDTです。

'絶対に実環境で導入しないでください。'
やばいです。

このプラグインの主な機能は
テスト用メンバーを増やしたり、コミュニティを増やすといった動作をするものです。

確実な再現性を求めたり一定の手順を求める場合にはあまり有効とはいえないでしょうが、
「ちょっと」１００人ほどダミーデータがほしいなんていうときに便利かもしれません。

OpenPNE3.2.x-3.6.x用です。

テストデータ挿入用タスク
------------------------

## メンバー

**opKdt:generate-member**

ダミーメンバーを作成する。メールアドレスはPC/携帯ともに sns{id}@example.com がセットされ、パスワードは password がセットされる。

また、デフォルトではメンバー名は「dummy{id}」となる。

例
---

    $ php symfony opKdt:generate-member

ダミーメンバーを10作成する。

    $ php symfony opKdt:generate-member --number=100

ダミーメンバーを100作成する。

    $ php symfony opKdt:generate-member --link=1

メンバーIDが1のメンバーとフレンドリンクしたダミーメンバーを10作成する。

    $ php symfony opKdt:generate-member --name-format="hoge%d"

メンバー名が hoge{id} のダミーメンバーを10作成する。

## コミュニティ

    $ php symfony opKdt:generate-community
    $ php symfony opKdt:generate-community-topic

## 日記

    $ php symfony opKdt:generate-diary
    $ php symfony opKdt:generate-diary-comment

## メッセージ

    $ php symfony opKdt:send-message

## あしあと

    $ php symfony opKdt:put-footprint

## フレンドにする

    $ php symfony opKdt:make-friend

## コミュニティに参加する

    $ php symfony opKdt:join-community

## 上記すべてを実行

    $ php symfony opKdt:generate-all

テストデータ挿入用管理画面
=========================

pc\_backend.php/opKdtPlugin/ にアクセスすると上記タスクを実行するページヘのリンクがあり，
それぞれのタスク実行のページではのオプション指定をフォームに表示してあるため，タスクよりも簡単にメンバが追加できます．
詳しい使い方についてはタスクをご覧ください．
