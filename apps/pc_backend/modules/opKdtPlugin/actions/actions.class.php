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
  }

 /**
  * Executes member generate action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeMember(sfWebRequest $request)
  {
    $this->form = new opKdtPluginBackendMemberForm();

    if ($request->isMethod(sfRequest::POST))
    {
      $this->form->bind($request->getParameter($this->form->getName()));
      if ($this->form->isValid())
      {
        $this->form->executeTask($this->dispatcher);
        $this->getUser()->setFlash('notice', 'Execute the tasks.');

        $this->redirect('opKdtPlugin/member');
      }
    }
  }

 /**
  * Executes member generate action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeCommunity(sfWebRequest $request)
  {
    $this->form = new opKdtPluginBackendCommunityForm();

    if ($request->isMethod(sfRequest::POST))
    {
      $this->form->bind($request->getParameter($this->form->getName()));
      if ($this->form->isValid())
      {
        $this->form->executeTask($this->dispatcher);
        $this->getUser()->setFlash('notice', 'Execute the tasks.');

        $this->redirect('opKdtPlugin/community');
      }
    }
  }
}
