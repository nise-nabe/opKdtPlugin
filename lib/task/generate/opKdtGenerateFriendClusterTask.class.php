<?php

/**
 * number の数のフレンドを持つメンバの組を新しく生成する
 * また，cluster-number を与えると cluster-number を生成する．
 */
class opKdtGenerateFriendClusterTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name = 'generate-cluster';

    $this->addOption('number', null, sfCommandOption::PARAMETER_OPTIONAL, 'num of member', 40);
    $this->addOption('cluster-number', null, sfCommandOption::PARAMETER_OPTIONAL, 'num of member', 1);
    $this->addOption('forked', null, sfCommandOption::PARAMETER_OPTIONAL, 'bool of fork', false);
  }

  protected function execute($arguments = array(), $options = array())
  {
    if ($options['forked'])
    {
      $databaseManager = new sfDatabaseManager($this->configuration);
      $this->createCluster($options['number']);
    }
    else
    {
      $php = $this->findPhpBinary();
      for ($i = 0; $i < $options['cluster-number']; ++$i)
      {
        echo 'make cluster '.$i.' :';
        exec($php.' '.sfConfig::get('sf_root_dir').'/symfony opKdt:generate-cluster --forked=true --number='.$options['number']);
        echo 'end'."\n";
      }
    }
  }

  private function createCluster($memberNum)
  {
    $memberIds = array();  
    for($i = 0; $i < $memberNum; ++$i)
    {
      $member = new Member();
      $member->setName('dummy');
      $member->setIsActive(true);
      $member->save();       
  
      $memberIds[] = $member->getId();
   
      $member->setName(sprintf('dummy%d', $member->getId()));
      $member->save();       
    
      $address = sprintf('sns%d@example.com', $member->getId());

      $this->setMemberConfig($member->getId(), 'pc_address', $address);
      $this->setMemberConfig($member->getId(), 'mobile_address', $address);

      $password = 'password';
      $this->setMemberConfig($member->getId(), 'password', $password);
      $member->free(true);
    }
    for($i = 0; $i < $memberNum; ++$i)
    {
      for($j = $i + 1; $j < $memberNum; ++$j) 
      {
        if ($i === $j) continue;
        $relation = new MemberRelationship();
        $relation->setMemberIdFrom($memberIds[$i]);
        $relation->setMemberIdTo($memberIds[$j]);
        $relation->setFriend(true);
        $relation->save();     
        $relation->free(true); 
      }
    }
  }

  private function setMemberConfig($memberId, $name, $value)
  {
      $config = new MemberConfig();
      $config->setMemberId($memberId);
      $config->setName($name);
      $config->setValue($value);
      $config->save();       
      $config->free(true);
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

