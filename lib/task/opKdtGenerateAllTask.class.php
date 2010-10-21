<?php

class opKdtGenerateAllTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-all';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', true),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('size', null, sfCommandOption::PARAMETER_REQUIRED, 'Size of database Pattern', null),
      )
    );

    $this->briefDescription = 'Generate Data for Test';
    $this->detailedDescription = <<<EOF
The [opKdt:generate-all|INFO] task generates useless data for testing.
Call it with:

  [./symfony opKdt:generate-all --size=small|INFO]
  size=tiny,small,medium,big,large,huge(config/app.ymlを編集する)
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $tasks = array();
    if ($options['size'])
    {
      $tasks = $this->settask($options['size']);
    } else {
      $tasks = $this->setdefaulttask();
    }

    foreach ($tasks as $task)
    {
      $taskName = sprintf('opKdt%sTask', $task['task']);
      $t = new $taskName($this->dispatcher, $this->formatter);
      $t->run($arguments = array(),$options = $task['option']);
      unset($t);
    }
  }

  protected function setdefaulttask()
  {
    $tasks = array(
      'GenerateMember',
      'GenerateCommunity',
      'GenerateCommunityTopic',
      'GenerateDiary',
      'GenerateDiaryComment',
      'JoinCommunity',
      'MakeFriend',
      'PutFootprint',
      'SendMessage',
    );
    foreach ($tasks as $task)
    {
      $taskoptions[] = array('task' => $task, 'option' => array());
    }
    return $taskoptions;
  }

  protected function settask($size)
  {
    $config = sfConfig::get(sprintf('app_datasize_%s',$size));
    $type = sfConfig::get(sprintf('app_datatype_%s',$size));

    $taskoptions = array();
    if ($type =="scenario")
    {
      foreach ($config as $num => $task)
      {
        foreach ($task as $key => $value)
        {
          $taskoptions[] = array('task' => $key, 'option' => $value);
        }
      }
    } else {
      foreach ($config as $key => $value)
      {
        $taskoptions[] = array('task' => $key, 'option' => $value);
      }
    }
    return $taskoptions;
  }

}
