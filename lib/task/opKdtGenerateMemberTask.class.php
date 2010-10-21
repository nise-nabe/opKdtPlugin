<?php

class opKdtGenerateMemberTask extends sfBaseTask
{
  protected function configure()
  {
    $this->namespace = 'opKdt';
    $this->name      = 'generate-member';

    $this->addOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application', true);
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev');
    $this->addOption('link', 'l', sfCommandOption::PARAMETER_REQUIRED, 'Who links?', null);
    $this->addOption('name-format', null, sfCommandOption::PARAMETER_REQUIRED, "Member's Name format", 'dummy%d');
    $this->addOption('number', null, sfCommandOption::PARAMETER_REQUIRED, 'Number of added members', 10);
    $this->addOption('mail-address-format', null, sfCommandOption::PARAMETER_REQUIRED, 'Mail-Address format', 'sns%d@example.com');
    $this->addOption('password-format', null, sfCommandOption::PARAMETER_REQUIRED, 'Password format', 'password');
    $this->addOption('notactivemember-rate', null, sfCommandOption::PARAMETER_REQUIRED, 'Is active=0 member rate', 0);
  }

  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = new sfDatabaseManager($this->configuration);

    $n = (int)$options['number'];
    $link = $options['link'];
    if (null !== $link)
    {
      $linkMember = Doctrine::getTable('Member')->find($link);
      if (!$linkMember)
      {
        throw new Exception("not found member: ".$link);
      }
    }

    for ($i = 0; $i < $n; $i++)
    {
      $member = new Member();
      $member->setName('dummy');
      $member->setIsActive(self::fetchRandomNotActive($options['notactivemember-rate'], $n));
      $member->save();

      $member->setName(sprintf($options['name-format'], $member->getId()));
      $member->save();

      $address = sprintf($options['mail-address-format'], $member->getId());
      $member->setConfig('pc_address', $address);
      $member->setConfig('mobile_address', $address);

      $password = preg_replace("/%d/", $member->getId(), $options['password-format'], 1);
      $member->setConfig('password', md5($password));

      $this->logSection('member+', $member->getName());
      if (isset($linkMember))
      {
        $memberRelationship1 = new MemberRelationship();
        $memberRelationship1->setMember($member);
        $memberRelationship1->setMemberRelatedByMemberIdFrom($linkMember);
        $memberRelationship1->setIsFriend(true);
        $memberRelationship1->save();

        $memberRelationship2 = new MemberRelationship();
        $memberRelationship2->setMember($linkMember);
        $memberRelationship2->setMemberRelatedByMemberIdFrom($member);
        $memberRelationship2->setIsFriend(true);
        $memberRelationship2->save();
        $this->logSection('friend link', sprintf("%s - %s", $linkMember->getId(), $member->getId()));
      }
    }
  }

  protected static function fetchRandomNotActive($rate, $max)
  {
    if ($rate == 0) return true;
    // 仮登録メンバーを一定割合で追加する
    if ( rand(1,100) <= $rate)
    {
        return false;
    } else {
        return true;
    }
     
  }

}
