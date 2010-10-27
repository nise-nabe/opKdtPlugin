<?php

class opKdtSendMessageTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'send-message';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of send messages', 10),
        new sfCommandOption('draftrate', null, sfCommandOption::PARAMETER_REQUIRED, 'Rate of draft messages', 5),
        new sfCommandOption('dustrate', null, sfCommandOption::PARAMETER_REQUIRED, 'Rate of dust messages', 10),
        new sfCommandOption('min', null, sfCommandOption::PARAMETER_REQUIRED, 'Sender Member Id Minimum', null),
        new sfCommandOption('max', null, sfCommandOption::PARAMETER_REQUIRED, 'Sender Member Id Maximum', null),
        new sfCommandOption('footprint', null, sfCommandOption::PARAMETER_REQUIRED, 'Foot Print Number', null),
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

    $sql = 'SELECT max(id) FROM member WHERE is_active != 0';
    $maxmemberId = $this->conn->fetchOne($sql);

    foreach ($memberIds as $id)
    {
      for ($i=0; $i<$options['number']; ++$i)
      {
        // 送り先は、id1～存在する最大のidからランダムに選出
        $sendTo = rand(1,$maxmemberId);
        $mes = new SendMessageData();
        $mes->setMemberId($id);
        $mes->setSubject('subject');
        $mes->setBody('body');

        // 一定割合で下書き
        $rate = $options['draftrate'];
        if ($rate != 0 && rand(1,100) <= $rate )
        {
          $mes->setIsSend(false);
        } else {
          $mes->setIsSend(true);
        }
        $mes->setMessageTypeId(1);
        $mes->save();

        $messageSendList = new MessageSendList();
        $messageSendList->setMemberId($sendTo);
        $messageSendList->setSendMessageData($mes);
        $messageSendList->save();

        $mesid = $mes->getId();
        $messageSendListid = $messageSendList->getId();

        $mes->free();
        $messageSendList->free();

        $this->logSection('send message', sprintf("%s - %s", $id, $sendTo));

        // 同時にあしあとをつける
        if ($options['footprint'] > 0)
        {
          $r_date = date('Y-m-d');
          for ($j=0 ; $j < $options['footprint'] ; ++$j) { 
              $ashi = new Ashiato();
              $ashi->setMemberIdFrom($id);
              $ashi->setMemberIdTo($sendTo);
              $ashi->setRDate($r_date);
              $ashi->save();
          }
          $ashi->free();
        }

        // 一定割合でゴミ箱へ
        $rate = $options['dustrate'];
        if ($rate != 0 && rand(1,100) <= $rate )
        {
            // 送信者がゴミ箱
            $deleted_message = new DeletedMessage();
            $deleted_message->setMemberId($id);
            $deleted_message->setMessageSendListId($messageSendListid);
            $deleted_message->save();

            // 受信者がゴミ箱
            $rec_deleted_message = new DeletedMessage();
            $rec_deleted_message->setMemberId($sendTo);
            $rec_deleted_message->setMessageId($mesid);
            $rec_deleted_message->save();

            $deleted_message->free();
            $rec_deleted_message->free();

            $this->logSection('delete message', sprintf("%s - %s", $id, $sendTo));
        }

      }
    }
  }

}
