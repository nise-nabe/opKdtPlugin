<?php

class opKdtGenerateDiaryCommentTask extends sfBaseTask
{
  protected $memberIds = array();

  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-diary-comment';

    require sfConfig::get('sf_data_dir').'/version.php';

    $this->addOptions(
      array(
        new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', null),
        new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        new sfCommandOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of diary comments', 5),
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

    $sql = 'SELECT max(id) FROM diary';
    $maxdiaryId = $this->conn->fetchOne($sql);

    foreach ($memberIds as $id)
    {
      for ($i=0; $i<$options['number']; ++$i)
      {
        $diaryid = rand(1,$maxdiaryId);
        $comment = new DiaryComment();
        $comment->setDiaryId($diaryid);
        $comment->setMemberId($id);
        $comment->setBody('body');
        $comment->save();
        $comment->free();
        $this->logSection('added a diary comment', sprintf('%s - %s', $diaryid, $id));
      }
    }
  }
}
