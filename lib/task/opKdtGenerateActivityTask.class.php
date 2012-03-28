<?php

class opKdtGenerateAvtivityTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-activity';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of Activities', 5),
        new sfCommandOption('min', null, sfCommandOption::PARAMETER_REQUIRED, 'Sender Member Id Minimum', null),
        new sfCommandOption('max', null, sfCommandOption::PARAMETER_REQUIRED, 'Sender Member Id Maximum', null),
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

    foreach ($memberIds as $memberid)
    {
      for ($i=0; $i<$options['number']; ++$i)
      {
        $ac = new ActivityData();
        $ac->setMemberId($memberid);
        $ac->setBody(md5($i));
        $ac->setPublicFlag(1);
        $ac->save();
        $ac->free();
        $this->logSection('posted a activity', sprintf('%s', $memberid));
      }
    }
  }
}
