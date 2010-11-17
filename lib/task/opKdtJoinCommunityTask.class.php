<?php

class opKdtJoinCommunityTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'join-community';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('min', null, sfCommandOption::PARAMETER_REQUIRED, 'MemberID min', null),
        new sfCommandOption('max', null, sfCommandOption::PARAMETER_REQUIRED, 'MemberID max', null),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of joined communities', 10),
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

    $communities = Doctrine::getTable('Community')->findAll(Doctrine::HYDRATE_ARRAY);
    if (count($communities) < $options['number'])
    {
      throw new Exception('Too few communities. Please run "opKdt:generate-community" first.');
    }
    $communityIds = array_map(create_function('$c', 'return (int)$c[\'id\'];'), $communities);

    foreach ($memberIds as $memberid)
    {
      $joinCommunities = Doctrine::getTable('Community')->retrievesByMemberId($memberid, null);
      $joinCommunityIds = array();
      if ($joinCommunities)
      {
        foreach ($joinCommunities as $c)
        {
          $joinCommunityIds[] = $c->getId();
        }
      }

      $candidate = array_diff($communityIds, $joinCommunityIds);
      shuffle($candidate);
      $candidateSlices = array_slice($candidate, 0, $options['number']);

      foreach ($candidateSlices as $communityId)
      {
        $cm = new CommunityMember();
        $cm->setCommunityId($communityId);
        $cm->setMemberId($memberid);
        $cm->save();
        $cm->free();
        $this->logSection('added a community member', sprintf("%s - %s", $memberid, $communityId));
      }
    }
  }
}
