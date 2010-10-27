<?php

class opKdtGenerateDiaryTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-diary';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of diaries', 5),
        new sfCommandOption('date', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of diary date', null),
        new sfCommandOption('min', null, sfCommandOption::PARAMETER_REQUIRED, 'Member Id Minimum', null),
        new sfCommandOption('max', null, sfCommandOption::PARAMETER_REQUIRED, 'Member Id Maximum', null),
        new sfCommandOption('titlecount', null, sfCommandOption::PARAMETER_REQUIRED, 'count Title strings', 10),
        new sfCommandOption('bodycount', null, sfCommandOption::PARAMETER_REQUIRED, 'count Body strings', 200),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->conn = $databaseManager->getDatabase('doctrine')->getDoctrineConnection();

    $sql = 'SELECT id FROM member WHERE is_active != 0';
    $where = array();
    if ( $options['min'] && $options['max']  && $options['min'] <= $options['max'])
    {
        $sql .= ' AND id BETWEEN ? AND ?';
        $where = array(intval($options['min']),intval($options['max']));
    }
    $memberIds = $this->conn->fetchColumn($sql, $where);

    $this->setContents();
    foreach ($memberIds as $memberid)
    {
      $randomtime = $this->makeRandomTime($options['date'],$options['number']);
      for ($i=0; $i<$options['number']; ++$i)
      {
        $title=$this->makeRandomContents('title',$options['titlecount']);
        $body=$this->makeRandomContents('body',$options['bodycount']);
        $diary = new Diary();
        $diary->setMemberId($memberid);
        $diary->setTitle($title);
        $diary->setBody($body);
        $diary->setPublicFlag(1);
        $diary->setCreatedAt($randomtime[$i]);
        $diary->setUpdatedAt($randomtime[$i]);
        $diary->save();
        $diary->free();
        $this->logSection('posted a diary', sprintf('%s %s', $memberid, $randomtime[$i]));
      }
    }
  }

  protected static function makeRandomTime($date, $number)
  {
    $end   = time();
    $start = time() - $date * 24 * 60 * 60;
    for ($i=0; $i<$number; ++$i)
    {
      $array[] = date("Y/m/d H:i:s",rand($start,$end));
    }
    sort($array);
    return $array;
  }

  protected function makeRandomContents($type,$number)
  {
    // 引数の文字数の半分以上引数以下の文字数で文字列を返す
    $arr = $this->contents->$type;
    $str = $arr[array_rand($arr)];
    $rtn = mb_substr($str,rand(0,(int)(mb_strlen($str,'utf-8')/2)),rand((int)($number/2),$number),'utf-8');
    if (mb_strlen(trim($rtn)) == 0) $rtn = 'hogehoge';
    return $rtn;
  }

  protected function setContents()
  {
    $this->contents->title = array('OpenPNE 3.6.0 に向けての課題とリリーススケジュールについて','今週のOpenPNE3デモサイト委員会 #4','OpenPNE3プラグインの作り方#4 ');
    $this->contents->body = array('
OpenPNE 開発チームの海老原です。
https://twitter.com/#!/openpne_irc @openpne_irc で一部お届けしましたが、 10/12 (火) に OpenPNE 3.6.0 のリリース検討会議をおこない、リリースまでの課題の洗い出しや対応スケジュールの決定などをおこないました。
新しいリリーススケジュール
以前告知したとおり、 OpenPNE 3.6.0 のリリース予定日を 10/15 (金) とする前提で進めていましたが、検討の結果、 11 月末まで延期することになりました。
OpenPNE 3.6beta7
    10 月末
OpenPNE 3.6beta8
    11 月第 1 週 から 第 2 週
OpenPNE 3.6beta9
    11 月第 2 週 から 第 3 週
OpenPNE 3.6 RC1
    11 月第 3 週
OpenPNE 3.6.0
    11 月末
まだまだお待たせしてしまいますが……、ご理解と、開発へのご協力をどうぞよろしくお願いします！
',
'OpenPNE3デモサイト委員会、広報担当の今村です。
「今週のOpenPNE3デモサイト委員会」第4回です。

まずはデモサイトの現在のメンバー数からご報告します。
なんと今週は約100人増えて現在610人になりました！
これだけ多くの方々にOpenPNE3が注目されてとてもうれしいです。

さて、今日はこれからOpenPNE3デモサイトはどのように進化していくかについてブログを書こうと思います。
まずはバージョンアップ！

現状OpenPNE3.1.1なのでなるべく早く3.1.3へバージョンアップいたします。
ただOpenPNE3.1.4のリリースも近づいてきている（今日かも？？）ので、もしかしたら3.1.4へバージョンアップするかもしれません。

バージョンアップすることによって多くの機能を追加できるようになりますのでそちらも楽しみにしててください。バージョンアップするに伴いとりあえずは以下の機能を追加しようかと考えています。

    * ・opCCCCPlugin：OpenPNE3で4コマ漫画が描けるプラグイン
    * ・opGpsPlugin：OpenPNE3でGPSを利用できるようにするプラグイン
    * ・日記の文字装飾機能

デザインを変えたい

現在のデモサイトはデフォルトのデザインのままなので、こちらもどんどん変更していく予定です。デザインの方針としては、ユニバーサルデザインや視覚言語の要素を含むような誰でもわかりやすいようなデザインにしていこうかと思っています。

これからデモサイトが大きく変わっていくので、続けてチェックよろしくお願いします。
そしてたくさんのフィードバックお待ちしております！

■———————————————————■
http://demo3.openpne.jp/
“Master site”OpenPNE3デモサイト

http://redmine.openpne.jp/
OpenPNE3プロジェクトRedmine

http://sns.openpne.jp/
バグ・要望報告は→OpenPNE公式SNSでもOK

http://twitter.com/pnetan/
Pnetanつぶやきなう
■———————————————————■

',
'この記事は OpenPNE3.0.x のものです。現在の最新安定版では動作しない箇所が存在します。

開発チームの川原です。

プラグインの作り方の記事もとうとう４回目になりました。

OpenPNE3プラグインの作り方#1
OpenPNE3プラグインの作り方#2
OpenPNE3プラグインの作り方#3

今回は、OpenPNE3のテンプレート拡張について解説します。

OpenPNE2のカスタマイズは、特定ページ（例えばpage_h_home）に新しい機能を加えるとき、
そのページのアクションを編集して、さらにテンプレートを編集して…。
といった作業が必要でした。

OpenPNE3は、プラグインを追加するだけで
特定ページに、新たな部品を追加することができます。

この仕組みをテンプレート拡張と呼んでいます。

OpenPNE3のテンプレートでは、複数のテンプレート部品（パーツ）によって
構成される仕組みになっています。
パーツにはIDを持っていて、その前後に別のパーツを挿入することが可能です。
'
);


  }
}
