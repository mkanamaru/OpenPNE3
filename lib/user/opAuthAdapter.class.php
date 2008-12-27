<?php

/**
 * opAuthAdapter will handle authentication for OpenPNE.
 *
 * @package    OpenPNE
 * @subpackage user
 * @author     Kousuke Ebihara <ebihara@tejimaya.com>
 */
abstract class opAuthAdapter
{
  protected
    $authModuleName = '',
    $authModeName = '',
    $authForm = null;

  public function __construct($name)
  {
    $this->setAuthModeName($name);
    $formClass = self::getAuthFormClassName($this->authModeName);
    $this->authForm = new $formClass($this);

    $this->configure();
  }

  public function configure()
  {
  }

  public function getAuthParameters()
  {
    $params = $this->getRequest()->getParameter('auth'.$this->authModeName);
    return $params;
  }

  public function getAuthForm()
  {
    $form = $this->getAuthLoginForm();
    if ($form)
    {
      return $form;
    }

    return $this->authForm;
  }

  public function getAuthLoginForm()
  {
    $form = null;

    $formClass = self::getAuthLoginFormClassName($this->authModeName);
    if (class_exists($formClass))
    {
      $form = new $formClass($this);
    }

    return $form;
  }

  public function getAuthRegisterForm()
  {
    $form = null;

    $formClass = self::getAuthRegisterFormClassName($this->authModeName);
    $member = sfContext::getInstance()->getUser()->getMember();

    if (class_exists($formClass))
    {
      $form = new $formClass(array(), array('member' => $member));
    }
    // deprecated
    else
    {
      $form = $this->getAuthForm();
      $form->setForRegisterWidgets($member);
      sfContext::getInstance()->getConfiguration()->getEventDispatcher()->notify(new sfEvent(null, 'application.log', array('The '.self::getAuthFormClassName($this->authModeName).' is deprecated. Please create the class is named '.self::getAuthRegisterFormClassName($this->authModeName), 'priority' => sfLogger::ERR)));
    }

    return $form;
  }

  public function authenticate()
  {
    $authForm = $this->getAuthForm();
    $authForm->bind($this->getAuthParameters());
    if ($authForm->isValid())
    {
      if ($member = $authForm->getMember())
      {
        return $member->getId();
      }
    }

    return false;
  }

  public static function getAuthRegisterFormClassName($authMode)
  {
    return 'opAuthRegisterForm'.ucfirst($authMode);
  }

  public static function getAuthLoginFormClassName($authMode)
  {
    return 'opAuthLoginForm'.ucfirst($authMode);
  }

 /**
  * @deprecated
  */
  public static function getAuthFormClassName($authMode)
  {
    return 'sfOpenPNEAuthForm_'.$authMode;
  }

 /**
  * Gets name of this authentication method
  */
  public function getAuthModeName()
  {
    return $this->authModeName;
  }

 /**
  * Names this authentication method.
  *
  * @param string $name
  */
 public function setAuthModeName($name)
 {
   $this->authModeName = $name;
 }

 /**
  * Gets sfRequest instance.
  *
  * @return sfRequest
  */
  protected function getRequest()
  {
    return sfContext::getInstance()->getRequest();
  }

  /**
   * Registers data to storage container.
   *
   * @param  int    $memberId
   * @param  sfForm $form
   * @return bool   true if the data has already been saved, false otherwise
   */
  abstract public function registerData($memberId, $form);

 /**
  * Registers the current user with OpenPNE
  *
  * @param  sfForm $form
  * @return bool   returns true if the current user is authenticated, false otherwise
  */
  public function register($form)
  {
    $member = true;
    $profile = true;

    if ($form->memberForm) {
      $member = $form->memberForm->save();
      $memberId = $member->getId();
    }

    if ($form->profileForm) {
      $profile = $form->profileForm->save($memberId);
    }

    if ($form->configForm) {
      $config = $form->configForm->save($memberId);
    }

    $auth = $this->registerData($memberId, $form);

    if ($member && $profile && $auth && $config) {
      return $memberId;
    }

    return false;
  }

  /**
   * Returns true if the current state is a beginning of register.
   *
   * @return bool returns true if the current state is a beginning of register, false otherwise
   */
  abstract public function isRegisterBegin($member_id = null);

  /**
   * Returns true if the current state is a end of register.
   *
   * @return bool returns true if the current state is a end of register, false otherwise
   */
  abstract public function isRegisterFinish($member_id = null);

  /**
   * Gets an action path to register
   *
   * @return string
   */
  public function getRegisterEndAction()
  {
    return $this->authModuleName.'/registerEnd';
  }
}
