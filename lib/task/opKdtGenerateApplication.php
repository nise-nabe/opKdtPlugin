<?php

class opKdtGenerateApplicationTask extends sfBaseTask
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
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of Application', null),
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

    // profileを増やす
    if ($options['profile'])
    {
      $profiles = explode(',',$options['profile']);
      $preProfiles = Doctrine::getTable('Profile')->createQuery()
        ->select('id')
        ->execute(array(), Doctrine::HYDRATE_NONE);
      foreach ($preProfiles as $key => $value)
      {
        $profileid = $value[0];
        if (in_array($profileid, $profiles))
        {
//          opApplicationConfiguration::registerZend();
          foreach ($memberIds as $memberid)
          {
            $sql = 'SELECT id FROM member_profile WHERE member_id = ? AND application_id = ?';
            $where = array(intval($memberid), intval($profileid));
            $mp = $this->conn->fetchOne($sql, $where);
            if (!$mp)
            {
              $memberProfile = new MemberProfile();
              $memberProfile->setMemberId($memberid);
              $memberProfile->setProfileId($profileid);
              $memberProfile->setValue('dummy');
              $memberProfile->save();
              $memberProfile->free();
              $this->logSection('member_profile', sprintf("%s - %s", $memberid, $profileid));
            } 
          }
        }
      }
    }

  }

}
