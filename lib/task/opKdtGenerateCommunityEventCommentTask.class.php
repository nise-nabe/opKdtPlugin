<?php

class opKdtGenerateCommunityEventCommentTask extends sfBaseTask
{
  protected $conn;

  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-community-event-comment';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of community event comment', 10),
        new sfCommandOption('member', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of community event add menber', 10),
        new sfCommandOption('eventnumber', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of community events', 10),
        new sfCommandOption('communitynumber', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of community', 10),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->conn = $databaseManager->getDatabase('doctrine')->getDoctrineConnection();

    $communityIds = $this->getCommunityIds();
    for ($i=0; $i < $options['communitynumber']; ++$i)
    {
      $communityId = $this->fetchRandomCommunityId($communityIds);
      $eventIds = $this->getEventIds($communityId);
      $memberIds = $this->getMemberIds($communityId);
      for ($j=0; $j < $options['eventnumber']; ++$j)
      {
        // イベントコメント
        for ($k=0; $k < $options['number']; ++$k)
        {
          $ctc = new CommunityEventComment();
          $ctc->setMemberId(self::fetchRandomMemberId($memberIds));
          $ctc->setCommunityEventId(self::fetchRandomEventId($eventIds));
          $ctc->setBody('body');
          $ctc->save();
          $ctc->free();
          $this->logSection('created a community event comment', sprintf("%s", $communityId));
        }
        // イベント参加メンバー
        for ($k=0; $k < $options['member']; ++$k)
        {
          $ctm = new CommunityEventMember();
          $ctm->setMemberId(self::fetchRandomMemberId($memberIds));
          $ctm->setCommunityEventId(self::fetchRandomEventId($eventIds));
          $ctm->save();
          $ctm->free();
          $this->logSection('created a community event member', sprintf("%s", $communityId));
        }
      }
    }
  }

  protected function getCommunityIds()
  {
    $communities = Doctrine::getTable('Community')->findAll(Doctrine::HYDRATE_ARRAY);
    foreach ($communities as $community)
    {
      $communityIds[] = $community['id'];
    }
    return $communityIds;
  }

  protected static function fetchRandomCommunityId($communityIds)
  {
    shuffle($communityIds);
    return array_pop($communityIds);
  }

  protected function getMemberIds($communityId)
  {
    $communityMembers = Doctrine::getTable('CommunityMember')->getCommunityMembers($communityId);
    if (!$communityMembers)
    {
      return array();
    }
    $communityMemberIds = array();
    foreach ($communityMembers as $m)
    {
      $communityMemberIds[] = $m->getMemberId();
    }
    return $communityMemberIds;
  }

  protected static function fetchRandomMemberId($communityMemberIds)
  {
    shuffle($communityMemberIds);
    return array_pop($communityMemberIds);
  }

  protected function getEventIds($communityId)
  {
    $sql = 'SELECT id FROM community_event WHERE community_id = ?';
    $where = array($communityId);
    return $this->conn->fetchColumn($sql, $where);
  }

  protected static function fetchRandomEventId($communityEventIds)
  {
    shuffle($communityEventIds);
    return array_pop($communityEventIds);
  }

}
