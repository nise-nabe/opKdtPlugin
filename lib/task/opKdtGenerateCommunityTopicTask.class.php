<?php

class opKdtGenerateCommunityTopicTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-community-topic';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of community topics', 10),
        new sfCommandOption('cmin', null, sfCommandOption::PARAMETER_REQUIRED, 'Community Id min', null),
        new sfCommandOption('cmax', null, sfCommandOption::PARAMETER_REQUIRED, 'Community Id max', null),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->conn = $databaseManager->getDatabase('doctrine')->getDoctrineConnection();

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
      for ($i=0; $i < $options['number']; ++$i)
      {
        $ct = new CommunityTopic();
        $ct->setCommunityId($cid);
        $ct->setMemberId(self::fetchRandomMemberId($cid));
        $ct->setName('name');
        $ct->setBody('body');
        $ct->save();
        $ct->free();
        $this->logSection('created a community topic', sprintf("%s", $cid));
      }
    }
  }

  protected static function fetchRandomMemberId($communityId)
  {
    $communityMembers = Doctrine::getTable('CommunityMember')->getCommunityMembers($communityId);

    if (!$communityMembers)
    {
      return;
    }

    $communityMemberIds = array();
    foreach ($communityMembers as $m)
    {
      $communityMemberIds[] = $m->getMemberId();
    }
    shuffle($communityMemberIds);

    return array_pop($communityMemberIds);
  }
}
