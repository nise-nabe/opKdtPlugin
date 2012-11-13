<?php

/**
 * opKdtPlugin actions.
 *
 * @package    OpenPNE
 * @subpackage opKdtPlugin
 * @author     Yuya Watanabe <watanabe@openpne.jp>
 */
class opKdtPluginActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->task = $request->getParameter('task', null);
    if (!is_null($this->task))
    {
      $this->form = new opKdtPluginBackendForm(array('task' => $this->task));

      if ($request->isMethod(sfRequest::POST))
      {
        $this->form->bind($request->getParameter($this->form->getName()));
        if ($this->form->isValid())
        {
          $this->form->executeTask();
          $this->getUser()->setFlash('notice', 'Execute the tasks.');

          $this->redirect('opKdtPlugin/index');
        }
      }

      return sfView::INPUT;
    }
    $finder = sfFinder::type('file')->maxdepth(0)->ignore_version_control(false)->follow_link()->name('opKdt*Task.class.php');

    $dir = array(sfConfig::get('sf_root_dir').DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.'opKdtPlugin'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'task');
    $this->tasks = array();
    foreach ($finder->in($dir) as $path)
    {
      preg_match('/^opKdt(.*)Task.class.php$/', basename($path), $matches);
      $this->tasks[] = $matches[1];
    }
  }
}
