<?php

class opKdtPluginBackendMemberForm extends BaseForm
{
  private $generateMember = 'GenerateMember';

  public function configure()
  {
    $snsConfigTable = Doctrine::getTable('SnsConfig');

    $this->setWidget('number', new sfWidgetFormInput());
    $this->setValidator('number',  new sfValidatorInteger(array('min' => 0), array('min' => 'Please input 0 or greater.')));

    $this->widgetSchema->setNameFormat('generate_member[%s]');
  }

  public function executeTask($dispatcher)
  {
    chdir(sfConfig::get('sf_root_dir'));

    if (!is_null($this->getValue('number')))
    {
      $taskName = sprintf('opKdt%sTask', $this->generateMember);
      $task = new $taskName($dispatcher, new sfFormatter());
      $task->run($arguments = array(), $options = array('number' => $this->getValue('number')));
    }
  }
}
