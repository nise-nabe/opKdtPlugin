<?php

class opKdtGenerateMemberApplicationTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-member-application';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('min', null, sfCommandOption::PARAMETER_REQUIRED, 'Set Member Id Minimum', null),
        new sfCommandOption('max', null, sfCommandOption::PARAMETER_REQUIRED, 'Set Member Id Maximum', null),
        new sfCommandOption('appmin', null, sfCommandOption::PARAMETER_REQUIRED, 'Minimum limit application', null),
        new sfCommandOption('appmax', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum limit application', null),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    if ( !$options['appmin'] || !$options['appmax'] || ($options['appmin'] > $options['appmax']))
    {
      throw new Exception("invalid option: ");
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->conn = $databaseManager->getDatabase('doctrine')->getDoctrineConnection();

    $sql = 'SELECT id FROM application';
    $appIds = $this->conn->fetchColumn($sql);

    $sql = 'SELECT id FROM member WHERE is_active != 0';
    $where = array();
    if ( $options['min'] && $options['max']  && $options['min'] <= $options['max'])
    {
        $sql .= ' AND id BETWEEN ? AND ?';
        $where = array(intval($options['min']),intval($options['max']));
    }
    $memberIds = $this->conn->fetchColumn($sql, $where);

    foreach ($memberIds as $memberid)
    {
      $appids = $this->setrandomappid($appIds, $options['appmin'], $options['appmax']);
      foreach ($appids as $application_id)
      {
        $sql = 'SELECT id FROM member_application WHERE member_id = ? AND application_id = ?';
        $where = array(intval($memberid), intval($application_id));
        $ma = $this->conn->fetchOne($sql, $where);
        if (!$ma)
        {
          $memberApplication = new MemberApplication();
          $memberApplication->setMemberId($memberid);
          $memberApplication->setApplicationId($application_id);
          $memberApplication->setPublicFlag('public');
          $memberApplication->save();
          $memberApplication->free();
          $this->logSection('member_application', sprintf("%s - %s", $memberid, $application_id));
        }
      }
    }
  }

  protected function setrandomappid($Ids,$min,$max)
  {
    $num_list = $Ids;
    if ($max > count($Ids))
    {
      $max = count($Ids);
    }
    $app_num  = rand($min,$max);
    if ($app_num == 1) {
      $key = array_rand($num_list);
      return array($num_list[$key]);
    } else {
      $keys = array_rand($num_list, $app_num);
      foreach($keys as $key) {
        $randappid[] = $num_list[$key];
      }
      shuffle($randappid);
      return $randappid;
    }

  }
}
