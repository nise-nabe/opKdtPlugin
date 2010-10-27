<?php

class opKdtMakeFriendTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'make-friend';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of added friends', 10),
        new sfCommandOption('prerate', null, sfCommandOption::PARAMETER_REQUIRED, 'Rate of request friends', 0),
        new sfCommandOption('min', null, sfCommandOption::PARAMETER_REQUIRED, 'Member Id Minimum', null),
        new sfCommandOption('max', null, sfCommandOption::PARAMETER_REQUIRED, 'Member Id Maximum', null),
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

    if (count($memberIds) < $options['number'])
    {
      throw new Exception('Too few members. Please run "opKdt:generate-member" first.');
    }

    foreach ($memberIds as $id)
    {
      $ds = Doctrine::getTable('MemberRelationship')->createQuery()
        ->select('member_id_to')
        ->where('member_id_from = ?', $id)
        ->execute(array(), Doctrine::HYDRATE_NONE);
      $id1 = array_map(create_function('$id', 'return (int)$id[0];'), $ds);
      $ds = Doctrine::getTable('MemberRelationship')->createQuery()
        ->select('member_id_from')
        ->where('member_id_to = ?', $id)
        ->execute(array(), Doctrine::HYDRATE_NONE);
      $id2 = array_map(create_function('$id', 'return (int)$id[0];'), $ds);
      $friendIds = array_merge($id1,$id2);
      $friendIds[] = (int)$id;
      $candidate = array_diff($memberIds, $friendIds);
      shuffle($candidate);
      $candidateSlices = array_slice($candidate, 0, $options['number']);
      foreach ($candidateSlices as $memberIdTo)
      {
        $mr1 = new MemberRelationship();
        $mr1->setMemberIdFrom($id);
        $mr1->setMemberIdTo($memberIdTo);
        $rate = $options['prerate'];
        if ($rate != 0 && rand(1,100) <= $rate )
        {
          // 一定割合で申請のみ
          $mr1->setIsFriend(false);
          $mr1->setIsFriendPre(true);
          $mr1->save();
          $mr1->free();
          $this->logSection('request friends', sprintf("%s - %s", $id, $memberIdTo));
        } else {
          $mr1->setIsFriend(true);
          $mr1->save();
          $mr1->free();
          $mr2= new MemberRelationship();
          $mr2->setMemberIdFrom($memberIdTo);
          $mr2->setMemberIdTo($id);
          $mr2->setIsFriend(true);
          $mr2->setIsFriendPre(false);
          $mr2->save();
          $mr2->free();
          $this->logSection('make friends', sprintf("%s - %s", $id, $memberIdTo));
        }

      }
    }
  }
}
