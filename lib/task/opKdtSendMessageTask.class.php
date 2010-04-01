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
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $members = Doctrine::getTable('Member')->findAll(Doctrine::HYDRATE_ARRAY);
    $memberIds = array_map(create_function('$m', 'return (int)$m[\'id\'];'), $members);

    foreach ($memberIds as $id)
    {
      for ($i=0; $i<$options['number']; ++$i)
      {
        $sendTo = self::fetchRandomMemberId($id, $memberIds);
        $mes = new SendMessageData();
        $mes->setMemberId($id);
        $mes->setSubject('subject');
        $mes->setBody('body');
        $mes->setIsSend(true);
        $mes->setMessageTypeId(1);
        $mes->save();
        $mes->free();

        $messageSendList = new MessageSendList();
        $messageSendList->setMemberId($sendTo);
        $messageSendList->setSendMessageData($mes);
        $messageSendList->save();
        $messageSendList->free();

        $this->logSection('send message', sprintf("%s - %s", $id, $sendTo));
      }
    }
  }

  protected static function fetchRandomMemberId($memberId, $memberIds = array())
  {
    $candidate = array_diff($memberIds, array($memberId));
    shuffle($candidate);

    return array_pop($candidate);
  }
}
