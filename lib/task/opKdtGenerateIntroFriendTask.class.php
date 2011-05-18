<?php

class opKdtGenerateIntroFriendTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-intro-friend';

    require sfConfig::get('sf_data_dir').'/version.php';

  }

  // there is a bug that if you execute this method, the dumy members cannot quit.
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->conn = $databaseManager->getDatabase('doctrine')->getDoctrineConnection();

    $sql = 'SELECT id FROM member WHERE is_active != 0';
    $memberIds = $this->conn->fetchColumn($sql);

    foreach($memberIds as $memberid)
    {
      $max = mt_rand(1, count($memberIds));
      for($i = 0; $i < $max; ++$i)
      {
        $from = $memberIds[mt_rand(0, count($memberIds)-1)];
        if(!Doctrine::getTable('IntroFriend')->getByFromAndTo($from, $memberid))
        {
          $in = new IntroFriend();
          $in->setMemberIdTo($memberid);
          $in->setMemberIdFrom($from);
          $in->setContent('I introduced');
          $in->save();
          $this->logSection('write intro ', 'from ' . $from . ' to ' . $memberid);
        }
      }
    }
  }
}
