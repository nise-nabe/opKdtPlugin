<?php

/**
 * opKdtPlugin actions.
 *
 * @package    OpenPNE
 * @subpackage opKdtPlugin
 * @author     Your name here
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
  }

  public function executeMember(sfWebRequest $request)
  {
    $this->form = new opKdtPluginConfigurationForm();

    if ($request->isMethod(sfRequest::POST))
    {
      $this->form->bind($request->getParameter($this->form->getName()));
      if ($this->form->isValid())
      {
        $this->form->executeTasks($this->dispatcher);
        $this->getUser()->setFlash('notice', 'Execute the tasks.');

        $this->redirect('opKdtPlugin/member');
      }
    }
  }
}
