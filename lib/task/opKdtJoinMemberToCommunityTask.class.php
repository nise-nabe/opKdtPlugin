<?php

class opKdtJoinMemberToCommunityTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'join-member-to-community';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', true);
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('cmin', null, sfCommandOption::PARAMETER_REQUIRED, "Community Id min", null);
    $this->addOption('cmax', null, sfCommandOption::PARAMETER_REQUIRED, "Community Id max", null);
    $this->addOption('number', null, sfCommandOption::PARAMETER_REQUIRED, "Community member number", 10);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->conn = $databaseManager->getDatabase('doctrine')->getDoctrineConnection();

    $sql = 'SELECT id FROM member WHERE is_active != 0';
    $memberIds = $this->conn->fetchColumn($sql);
    $sql = 'SELECT id FROM community';
    $where = array();
    if ( $options['cmin'] && $options['cmax']  && $options['cmin'] <= $options['cmax'])
    {
        $sql .= ' WHERE id BETWEEN ? AND ?';
        $where = array(intval($options['cmin']),intval($options['cmax']));
    }
    $commuIds = $this->conn->fetchColumn($sql, $where);
    foreach ($commuIds as $cid)
    {
      $community = Doctrine::getTable('Community')->find($cid);
      for ($i=0; $i < intval($options['number']); ++$i)
      {
        $id = array_rand($memberIds);
        $o = Doctrine::getTable('CommunityMember')->retrieveByMemberIdAndCommunityId($id, $cid);
        if (!$o)
        {
          Doctrine::getTable('CommunityMember')->join($id, $cid);
          $this->logSection('join community+', $id.' to '.$cid);
        }
      }
    }
  }
}
