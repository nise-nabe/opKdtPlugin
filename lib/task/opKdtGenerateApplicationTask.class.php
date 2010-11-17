<?php

class opKdtGenerateApplicationTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-application';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('appmin', null, sfCommandOption::PARAMETER_REQUIRED, 'Minimum ID of Application', null),
        new sfCommandOption('appmax', null, sfCommandOption::PARAMETER_REQUIRED, 'Maximum ID of Application', null),
      )
    );
  }

  protected function execute($arguments = array(), $options = array())
  {
    if ( !$options['appmin'] || !$options['appmax'] || ($options['appmin'] > $options['appmax']))
    {
      throw new Exception("invalid option: ");
    }

    $databaseManager = new sfDatabaseManager($this->configuration);
    $this->conn = $databaseManager->getDatabase('doctrine')->getDoctrineConnection();

    $applist = range($options['appmin'], $options['appmax']);
    foreach ($applist as $application_id)
    {
      $sql = 'SELECT id FROM application WHERE id = ?';
      $where = array(intval($application_id));
      $app = $this->conn->fetchOne($sql, $where);
      if (!$app)
      {
        $application = new Application();
        $application->setUrl("http://");
        $application->setIsMobile('1');
        $application->setIsPc('0');
        $application->save();
        $application->free();
        $this->logSection('application', sprintf("%s", $application_id));
      }
    }

  }

}
