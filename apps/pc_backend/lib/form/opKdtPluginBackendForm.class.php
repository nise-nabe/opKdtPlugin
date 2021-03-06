<?php

class opKdtPluginBackendForm extends BaseForm
{
  public function configure()
  {
    $taskName = sprintf('opKdt%sTask', $this->getDefault('task'));
    $task = new $taskName(sfContext::getInstance()->getEventDispatcher(), new sfFormatter());
    $options = $task->getOptions();

    foreach ($task->getOptions() as $option)
    {
      $optionName = $option->getName();
      $this->setWidget($optionName, new sfWidgetFormInput());
      $this->setValidator($optionName,  new sfValidatorString());
      $this->setDefault($optionName, $option->getDefault());
    }

    $this->widgetSchema->setNameFormat('generate_'.$this->taskName.'[%s]');
  }

  public function executeTask()
  {
    chdir(sfConfig::get('sf_root_dir'));

    $taskName = sprintf('opKdt%sTask', $this->getDefault('task'));
    $task = new $taskName(sfContext::getInstance()->getEventDispatcher(), new sfFormatter());

    $options = array();
    foreach ($task->getOptions() as $option)
    {
      $optionName =  $option->getName();
      $options[$optionName] = $this->getValue($optionName);
    }

    $task->run(array(), $options);
  }
}
