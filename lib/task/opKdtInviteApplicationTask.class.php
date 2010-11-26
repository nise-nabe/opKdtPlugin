<?php

class opKdtInviteApplicationTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'invite-application';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of invite application', 10),
        new sfCommandOption('min', null, sfCommandOption::PARAMETER_REQUIRED, 'Sender Member Id Minimum', null),
        new sfCommandOption('max', null, sfCommandOption::PARAMETER_REQUIRED, 'Sender Member Id Maximum', null),
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

    $sql = 'SELECT max(id) FROM member WHERE is_active != 0';
    $maxmemberId = $this->conn->fetchOne($sql);

    foreach ($memberIds as $id)
    {
      // アプリID取得
      $sql = 'SELECT application_id FROM member_application WHERE member_id = ?';
      $where = array(intval($id));
      $appIds = $this->conn->fetchColumn($sql, $where);
      for ($i=0; $i<$options['number']; ++$i)
      {
        // アプリID割り当て
        $key = array_rand($appIds);
        $appId = $appIds[$key];
        // 送り先は、id1～存在する最大のidからランダムに選出
        $sendTo = rand(1,$maxmemberId);
        $appiv = new ApplicationInvite();
        $appiv->setToMemberId($sendTo);
        $appiv->setFromMemberId($id);
        $appiv->setApplicationId($appId);
        $appiv->save();
        $appiv->free();

        $this->logSection('application invite', sprintf("%s: %s - %s", $appId, $id, $sendTo));
      }
    }
  }

}
