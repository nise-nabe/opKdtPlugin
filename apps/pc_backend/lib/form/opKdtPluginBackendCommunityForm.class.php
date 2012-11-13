<?php

class opKdtPluginBackendCommunityForm extends BaseForm
{
  private $generateCommunity = 'GenerateCommunity';

  public function configure()
  {
    $snsConfigTable = Doctrine::getTable('SnsConfig');

    $this->setWidget('number', new sfWidgetFormInput());
    $this->setValidator('number',  new sfValidatorInteger(array('min' => 0), array('min' => 'Please input 0 or greater.')));

    $this->widgetSchema->setNameFormat('generate_community[%s]');
  }

  public function executeTask()
  {
    chdir(sfConfig::get('sf_root_dir'));

    if (!is_null($this->getValue('number')))
    {
      $taskName = sprintf('opKdt%sTask', $this->generateCommunity);
      $task = new $taskName(sfContext::getInstance()->getEventDispatcher(), new sfFormatter());
      $task->run($arguments = array(), $options = array('number' => $this->getValue('number')));
    }
  }
}
