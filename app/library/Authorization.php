<?php
namespace Molly;

use Molly\Models\User,
	Molly\Models\UserFailedLogin,
	Molly\Models\UserSuccessLogin,
	Molly\Models\UserRememberToken,
	Phalcon\Mvc\User\Component;

class Authorization extends Component
{
	private $current = null;
	
	public function __construct()
	{
		$this->getCurrentAuth();
	}
	
	private function getCurrentAuth()
	{
		# Auth by session
		try {
			$session = $this->session->get("user");
			
			if( !is_array($session) OR !isset($session["id"]) )
				throw new \Exception("Error Processing Request", 1);
			
			$user = User::findFirst($session["id"]);
			
			if( !$user )
				throw new \Exception("Error Processing Request", 1);
			
			return $this->authorize($user);
		} catch (\Exception $e) {}
		
		# Auth by cookies
		try {
			if( !$this->cookies->has("userToken") )
				throw new \Exception("Error Processing Request", 1);
			
			$userToken = $this->cookies->get("userToken")->getValue();
			
			if( trim($userToken) == "" )
				throw new \Exception("Error Processing Request", 1);
			
			$userAgent = $this->request->getUserAgent();
			
			$token = UserRememberToken::findFirst(array(
				"conditions" => "token = :token: AND userAgent = :userAgent:",
				"bind" => array(
					"token" => $userToken,
					"userAgent" => $userAgent,
				),
			));
			
			if( !$token )
				throw new \Exception("Error Processing Request", 1);
			
			return $this->authorize($token->user);
		} catch (\Exception $e) {}
		
		return false;
	}
	
	public function login( $credentials )
	{
		$user = User::findFirst(array(
			"conditions" => "email = :email:",
			"bind" => array(
				"email" => $credentials["email"],
			),
		));
		
		if( !$user )
		{
			$this->registerUserThrottling(0);
			return false;
		}
		
		if( !$this->security->checkHash($credentials["password"], $user->password) )
		{
			$this->registerUserThrottling($user->id);
			return false;
		}
		
		if( $user->active != "Y" )
			return false;
		
		$this->saveSuccessLogin($user);
		
		$remember = ((isset($credentials["remember"])AND$credentials["remember"])?true:false);
		
		$this->authorize($user, $remember);
		
		return true;
	}
	
	public function logout()
	{
		$this->session->destroy();
	}
	
	public function isAuthorized()
	{
		if( $this->current !== null )
			return true;
		
		return false;
	}
	
	public function getCurrentId()
	{
		if( $this->current !== null )
			return $this->current->id;
		
		return false;
	}

    public function getCurrentGroupId()
    {
        if( $this->current !== null )
            return $this->current->groupId;

        return false;
	}
	
	public function getCurrent()
	{
		if( $this->current !== null )
			return $this->current;
		
		return false;
	}
	
	private function registerUserThrottling( $userId )
	{
		$failedLogin = new UserFailedLogin();
		$failedLogin->assign(array(
			"userId" => $userId,
			"ipAddress" => $this->request->getClientAddress(),
			"attempted" => time(),
		));
		$failedLogin->save();
		
		$attempts = UserFailedLogin::count(array(
			"ipAddress = ?0 AND attempted >= ?1",
			"bind" => array(
				$this->request->getClientAddress(),
				time() - 3600 * 6
			)
		));

		switch( $attempts )
		{
			case 1:
			case 2:
				// no delay
				break;
			case 3:
			case 4:
				sleep(2);
				break;
			default:
				sleep(4);
				break;
		}
	}
	
	public function saveSuccessLogin( $user )
	{
		$successLogin = new UserSuccessLogin();
		$successLogin->assign(array(
			"createdAt" => (new \DateTime())->format("Y-m-d H:i:s"),
			"userId" => $user->id,
			"ipAddress" => $this->request->getClientAddress(),
			"userAgent" => $this->request->getUserAgent(),
		));
		$successLogin->save();
	}
	
	public function authorize( $user, $remember = false )
	{
		$this->session->set("user", array(
			"id" => $user->id,
		));
		
		$this->current = $user;
	}
}