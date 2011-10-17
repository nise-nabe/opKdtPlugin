<?php

class opKdtPluginConfigurationForm extends BaseForm
{

  public function configure()
  {
    $snsConfigTable = Doctrine::getTable('SnsConfig');

    $generateMember = 'GenerateMember';
    $this->setWidget($generateMember, new sfWidgetFormInput());
    $this->setValidator($generateMember, new sfValidatorInteger(array('min' => 0), array('min' => 'Please input 0 or greater.')));

    $this->widgetSchema->setNameFormat('op_kdt_plugin[%s]');
  }

  public function executeTasks($dispatcher)
  {
    chdir(sfConfig::get('sf_root_dir'));
    $configNames = array('GenerateMember');

    foreach ($configNames as $name)
    {
      if (!is_null($this->getValue($name)))
      {
        $taskName = sprintf('opKdt%sTask', $name);
        $t = new $taskName($dispatcher, new sfFormatter());
        $t->run($arguments = array(),$options = array('number' => $this->getValue($name)));
        unset($t);
      }
    }
  }
}
