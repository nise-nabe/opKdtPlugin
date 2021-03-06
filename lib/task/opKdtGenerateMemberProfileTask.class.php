<?php

class opKdtGenerateMemberProfileTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-member-profile';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('min', null, sfCommandOption::PARAMETER_REQUIRED, 'Set Member Id Minimum', null),
        new sfCommandOption('max', null, sfCommandOption::PARAMETER_REQUIRED, 'Set Member Id Maximum', null),
        new sfCommandOption('config', null, sfCommandOption::PARAMETER_REQUIRED, 'Set member config name(separated comma)', null),
        new sfCommandOption('profile', null, sfCommandOption::PARAMETER_REQUIRED, 'Set member profile id(separated comma)', null),
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

    // configを増やす
    if ($options['config'])
    {
      $configs = explode(',',$options['config']);
      foreach ($memberIds as $memberid)
      {
        $member = Doctrine::getTable('Member')->find($memberid);
        foreach ($configs as $config)
        {
          $member->setConfig($config, 'dummy');
          $this->logSection('member_config', sprintf("%s - %s", $config, $memberid));
        }
        $member->free();
      }
    }

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
          foreach ($memberIds as $memberid)
          {
            $sql = 'SELECT id FROM member_profile WHERE member_id = ? AND profile_id = ?';
            $where = array(intval($memberid), intval($profileid));
            $mp = $this->conn->fetchOne($sql, $where);
print "$mp";
            if (!$mp)
            {
              opApplicationConfiguration::registerZend();
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
