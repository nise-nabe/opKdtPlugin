<?php

/**
 * number の数のコミュニティメンバを持つコミュニティを number 個新しく生成する
 * また，cluster-number を与えると cluster-number を生成する．
 */
class opKdtGenerateCommunityClusterTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name = 'generate-community-cluster';

    $this->addOption('number', null, sfCommandOption::PARAMETER_OPTIONAL, 'num of member', 5);
    $this->addOption('offset', null, sfCommandOption::PARAMETER_OPTIONAL, 'num of member', 0);
    $this->addOption('cluster-number', null, sfCommandOption::PARAMETER_OPTIONAL, 'num of member', 1);
    $this->addOption('forked', null, sfCommandOption::PARAMETER_OPTIONAL, 'bool of fork', false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);
    if ($options['forked'])
    {
      $this->createCluster($options['number'], $options['offset']);
    }
    else
    {
      if (Doctrine::getTable('Member')->where('is_active != 0')->count() < $options['cluster-number'] * $options['number'])
      {
        echo 'there enough member'."\n";
        exit;
      }
      $php = $this->findPhpBinary();
      for ($i = 0; $i < $options['cluster-number']; ++$i)
      {
        echo 'make cluster '.$i.' :';
        exec($php.' '.sfConfig::get('sf_root_dir').'/symfony opKdt:generate-community-cluster --offset='.$i.' --forked=true --number='.$options['number']);
        echo 'end'."\n";
      }
    }
  }

  private function createCluster($memberNum, $offset)
  {
    $memberIds = array();  
    $members = Doctrine::getTable('Member')->createQuery()->select('id')->where('is_active != 0')->limit($memberNum)->offset($memberNum * $offset)->execute();

    for ($i = 0; $i < $memberNum; ++$i)
    {
      $community = new Community();
      $community->setName('dummy');
      $community->save();

      $community->setName(sprintf('dummy%d community', $community->getId()));
      $community->save();

      $configData = array(
        array('description', $community->getName()),
        array('topic_authority', 'public'),
        array('public_flag', 'public'),
        array('register_policy', 'open')
      );

      foreach ($configData as $config)
      {
        $communityConfig = new CommunityConfig();
        $communityConfig->setCommunity($community);
        $communityConfig->setName($config[0]);
        $communityConfig->setValue($config[1]);
        $communityConfig->save();
        $communityConfig->free();
      }

      for ($j = 0; $j < $memberNum; ++$j)
      {
        $communityMember = new CommunityMember();
        $communityMember->setCommunity($community);
        $communityMember->setMember($members[$j]);
        if ($j === 0)
        {
          $communityMember->addPosition('admin');
        }
        $communityMember->save();
      }
    }
  }

  private function findPhpBinary()
  {
    if (defined('PHP_BINARY') && PHP_BINARY)
    {
      return PHP_BINARY;
    }

    if (false !== strpos(basename($php = $_SERVER['_']), 'php'))
    {
      return $php;
    }

    // from https://github.com/symfony/Process/blob/379b35a41a2749cf7361dda0f03e04410daaca4c/PhpExecutableFinder.php
    $suffixes = DIRECTORY_SEPARATOR == '\\' ? (getenv('PATHEXT') ? explode(PATH_SEPARATOR, getenv('PATHEXT')) : array('.exe', '.bat', '.cmd', '.com')) : array('');
    foreach ($suffixes as $suffix)
    {
      if (is_executable($php = PHP_BINDIR.DIRECTORY_SEPARATOR.'php'.$suffix))
      {
        return $php;
      }
    }

    if ($php = getenv('PHP_PEAR_PHP_BIN'))
    {
      if (is_executable($php))
      {
        return $php;
      }
    }

    return sfToolkit::getPhpCli();
  }
}

