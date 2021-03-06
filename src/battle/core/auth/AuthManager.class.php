<?php
require_once 'core/auth/db/UserDB.class.php';
require_once 'core/auth/datamodel/User.class.php';
require_once 'core/auth/datamodel/Identity.class.php';
require_once 'lib/phpass-0.3/PasswordHash.php';
//require_once 'core/auth/FacebookService.class.php';

/**
 * AuthManager
 * Responsible of the user authentication management.
 * Note : 
 * - requires an mysql db containing the user & role tables
 * - currently, all data validation happens before calling any of these methods (in the 'validate()'' methods of form models), 
 * so there is NO DATA VALIDATION HERE
 */
class AuthManager{

	const AuthTypePassword = 0;
	const AuthTypeUser = 1;
	const AuthTypeFB = 2;

    public static function authenticate($type, $identity){
        // if facebook authenticated, then lookfor corresponding user and keep its authentified state in session
        // ...
        // otherwise find dbuser corresponding to given identity login or mail
    	if($type === self::AuthTypeUser){
	        $login = $identity->login;
	        $password = $identity->password;
	        $userdb = UserDB::getInstance()->get_user_from_login($login,Request::get_application());
			if(is_null($userdb))
				$userdb = UserDB::getInstance()->get_user_from_mail($login,Request::get_application());
			if(is_null($userdb))
				return false;

			// compare identity's password with dbuser's hashedpassword
			$user = User::create_user_from_db($userdb);
			$hasher = new PasswordHash(8, false);
			if(!$hasher->CheckPassword($password, $user->hashed_password))
				return false;

			// if right password, store user_id in session for current application
			$_SESSION['applications_loggedin'] = array();
			$_SESSION['applications_loggedin'][Request::get_application()] = $user->id;
			return true;
		}
		// otherwise compare password with the one set in config
		elseif($type === self::AuthTypePassword){
			$password = $identity->password;
			$hasher = new PasswordHash(8, false);
			if(!$hasher->CheckPassword($password, Configuration::SIMPLE_AUTH_PASS))
				return false;
			
			$_SESSION['applications_loggedin'][Request::get_application()] = 1;
			return true;
		}
	}

	public static function direct_authenticate($user_id){
		$_SESSION['applications_loggedin'] = array();
		$_SESSION['applications_loggedin'][Request::get_application()] = $user_id;
	}
    
    // Checks if user is logged in current application
    public static function is_authenticated(){
        // check session-stored user authentification state
		return isset($_SESSION['applications_loggedin']) 
			&& isset($_SESSION['applications_loggedin'][Request::get_application()])
			&& ($_SESSION['applications_loggedin'][Request::get_application()] != 0);
	}
    
    // Unauthenticates from current application
    public static function unauthenticate(){
		// delete current user auth credentials from session
		if (isset($_SESSION['applications_loggedin'][Request::get_application()]))
			unset($_SESSION['applications_loggedin'][Request::get_application()]);
	}

	// Gets current user id if authenticated. Returns 0 otherwise
    public static function get_current_user_id(){
    	$application = Request::get_application();
    	if(isset($_SESSION['applications_loggedin'][$application]))
			return $_SESSION['applications_loggedin'][$application];
		else
			return 0;
    }
    
	public static function get_user_infos(){
		$user_id = self::get_current_user_id();
    	return ($user_id < 1)
    		? null
    		: User::create_user_from_db(UserDB::getInstance()->get_user($user_id));
	}

    public static function get_user_ip(){
    	return $_SERVER['REMOTE_ADDR'];
    }


    // ---- Role Helpers ----
    
    private static $roles = array(0 => "?", 1 => "user", 2 => "admin");

	public static function role_id_to_name($roleid){
		if($roleid > count(self::$roles) || $roleid < 0)
			$roleid = 0;
		return self::$roles[$roleid];
	}

	public static function role_name_to_id($rolename){
		$result = array_search($rolename, self::$roles); 
		if($result === FALSE) 
			$result = 0;
		return $result;
	}

    public static function is_confirmed(){
    	$user_id = self::get_current_user_id();
    	if($user_id < 1)
    		return false;

    	// TODO : don't get all user information for 1 information
    	$userdb = UserDB::getInstance()->get_user($user_id);
    	if(is_null($userdb))
    		return false;

		$user = User::create_user_from_db($userdb);
    	return $user->has_confirmed;
    }

    public static function has_role($rolename){
    	$user_id = self::get_current_user_id();
    	if($user_id < 1)
    		return false;

    	// TODO : don't get all user information for 1 information
    	$userdb = UserDB::getInstance()->get_user($user_id);
    	if(is_null($userdb))
    		return false;

    	$user = User::create_user_from_db($userdb);
    	return (self::role_id_to_name($user->role_id) == $rolename);
    }

    public static function is_current_user_admin(){
    	return self::has_role("admin");
    }
    
    public static function is_current_user_customer(){
    	return self::has_role("user");
    }
	

    // OLD AuthManager

	public static function login($login,$password){
		$identity = new Identity();
		$identity->login = $login;
		$identity->password = $password;
		return self::authenticate(self::AuthTypeUser,$identity);
	}
	
	public static function logout(){
		self::unauthenticate();
	}
	
	public static function register($mail,$login,$password,$application){
		$user = new User();
		$user->mail = $mail;
		$user->login = $login;
		$user->hashed_password = self::hash_password($password);
		$user->confirmation_token = sha1(uniqid($user->login, true));
		$user->role_id = self::role_name_to_id("user");
		$user->last_ip = self::get_user_ip();
		$user->application = $application;

		// send confirmation mail ? Currently in ActionAuth...
		return UserDB::getInstance()->add($user);
	}
	
	// TODO
	public static function confirmation($userid, $confirmationtoken){
		//confirm registration of user (from confirmation mail)
		// getuser from id
		// compare confirmation tokens
		// if(same) userdb->has_confirmed = true/1;
	}
	
	// TODO
	public static function change_password($old,$new){
		// update password
		// send mail?
	}
	
	// TODO
	public static function unregister(){
		// update user state to unregistered
	}
	
	// ---- FACEBOOK ----

	/**
	 * Note : to check if user is authenticated on facebook :
	 * isset(get_user_profile($fbm))
	 */
	public static function get_user_profile($facebook_manager){
		return $facebook_manager->get_user_profile();
	}	

	public static function get_loginout_url($facebook_manager,$user_profile){
		return $facebook_manager->get_url_loginout($user_profile);
	}
	
	// ---- Helpers ----
	
	public static function get_identity_from_post(){
		$identity = new Identity();
		$identity->login = Request::isset_or($_POST['login'],"");
		$identity->mail = Request::isset_or($_POST['mail'],"");
		$identity->password = Request::isset_or($_POST['password'],"");
		return $identity;
	}

	public static function hash_password($password){
		// Initialize the hasher without portable hashes (this is more secure)
		$hasher = new PasswordHash(8, false);
		return $hasher->HashPassword($password);
	}
}
?>