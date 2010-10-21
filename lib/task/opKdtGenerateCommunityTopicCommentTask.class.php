<?php

class opKdtGenerateCommunityTopicCommentTask extends sfBaseTask
{
  protected $conn;

  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-community-topic-comment';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of community topic comment', 10),
        new sfCommandOption('topicnumber', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of community topics', 10),
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
      $topicIds = $this->getTopicIds($communityId);
      $memberIds = $this->getMemberIds($communityId);
      for ($j=0; $j < $options['topicnumber']; ++$j)
      {
        for ($k=0; $k < $options['number']; ++$k)
        {
          $ctc = new CommunityTopicComment();
          $ctc->setMemberId(self::fetchRandomMemberId($memberIds));
          $ctc->setCommunityTopicId(self::fetchRandomTopicId($topicIds));
          $ctc->setBody('body');
          $ctc->save();
          $ctc->free();
          $this->logSection('created a community topic comment', sprintf("%s", $communityId));
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

  protected function getTopicIds($communityId)
  {
    $sql = 'SELECT id FROM community_topic WHERE community_id = ?';
    $where = array($communityId);
    return $this->conn->fetchColumn($sql, $where);
  }

  protected static function fetchRandomTopicId($communityTopicIds)
  {
    shuffle($communityTopicIds);
    return array_pop($communityTopicIds);
  }

}
