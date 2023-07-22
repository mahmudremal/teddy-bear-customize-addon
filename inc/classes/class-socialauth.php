<?php
/**
 * Blocks
 *
 * @package TeddyBearCustomizeAddon
 */
namespace TEDDYBEAR_CUSTOMIZE_ADDON\inc;
use TEDDYBEAR_CUSTOMIZE_ADDON\inc\Traits\Singleton;
class SocialAuth {
	use Singleton;
	private $facebookAppID;
	private $facebookAppSecret;
	private $facebookAppRedirect;
	private $googleClientID;
	private $googleClientSecret;
	private $googleAppRedirect;

	private $instagramAppID;
	private $instagramAppSecret;
	private $instagramAppRedirect;
	protected function __construct() {
		$this->setup_hooks();
	}
	protected function setup_hooks() {
		$this->facebookAppID					= apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'auth-googleclientid', 'facebook-app-id' );
		$this->facebookAppSecret			= apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/getoption', 'auth-googleclientid', 'facebook-app-secret' );
		$this->facebookAppRedirect		= $this->socialAuthRedirect( false, 'facebook' ); // site_url( 'custompermalink/fb-callback.php?&scope=email' );
		$this->googleClientID					= 'google-client-id';
		$this->googleClientSecret			= 'google-client-secret';
		$this->googleAppRedirect			= $this->socialAuthRedirect( false, 'google' );
		$this->instagramAppID					= 'instagram-app-id';
		$this->instagramAppSecret			= 'instagram-app-secret';
		$this->instagramAppRedirect		= $this->socialAuthRedirect( false, 'instagram' );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/socialauth/redirect', [ $this, 'socialAuthRedirect' ], 10, 2 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/socialauth/link', [ $this, 'socialAuthLink' ], 0, 2 );
		add_filter( 'futurewordpress/project/teddybearpopupaddon/rewrite/rules', [ $this, 'rewriteRules' ], 10, 1 );
		add_filter( 'query_vars', [ $this, 'query_vars' ], 10, 1 );
		add_filter( 'template_include', [ $this, 'template_include' ], 10, 1 );

		
		if( ! apply_filters( 'futurewordpress/project/teddybearpopupaddon/system/isactive', 'auth-enable' ) ) {return;}
		/**
		 * Actions.
		 */
		// add_filter( 'block_categories_all', [ $this, 'add_block_categories' ] );
	}
	/**
	 * Facebook Social Authentication process.
	 * 
	 * Create a Facebook App:
	 * Go to the Facebook Developer website and create a new app. This will give you a unique App ID and App Secret.
	 * Create a PHP file for your Facebook Login button:
	 * Create a PHP file where you want the Login button to appear. This could be a login page, or a section of your website dedicated to social logins.
	 * Define a Login URL:
	 * Use the App ID from step 1 to generate a Login URL. This URL will be used to redirect the user to Facebook to log in.
	 */
	/**
	 * Get Facebook login button.
	 * @return string
	 */
	private function getFBLoginBtn() {
		$loginBtn = '<a href="' . htmlspecialchars($this->getFBLoginUrl) . '">Log in with Facebook!</a>';
		return $loginBtn;
	}
	/**
	 * Get Facebook login Url for login button.
	 * @return string
	 */
	public function getFBLoginUrl() {
		$loginUrl = "https://www.facebook.com/v7.0/dialog/oauth?client_id={$this->facebookAppID}&redirect_uri={$this->facebookAppRedirect}";
		return $loginUrl;
	}
	/**
	 * Handle the callback from Facebook: Create a new PHP file, fb-callback.php, to handle the callback from Facebook. This file will receive the code parameter, and use it to request an access token.
	 * Get an Access Token: Use the $_GET['code'] parameter to get an access token. Replace the {app-id} and {app-secret} placeholders with your actual App ID and App Secret from step 1.
	 */
	private function getFBAccessToken() {
		$tokenUrl = 'https://graph.facebook.com/v7.0/oauth/access_token?' .
    'client_id={$this->facebookAppID}&redirect_uri={$this->facebookAppRedirect}' .
    '&client_secret={$this->facebookAppSecret}&code=' . $_GET['code'];
		$response = file_get_contents($tokenUrl);
		$params = json_decode($response);
		$accessToken = $params->access_token;
		return $accessToken;
	}
	/**
	 * Use the Access Token: With an access token in hand, you can now use the Facebook Graph API to request information about the user who logged in. For example:
	 * Store the user information: Use the $user object to store the user's information in your database, or use it to log them in to your website.
	 */
	private function getFBInfofromAccessToken() {
		$graphUrl = 'https://graph.facebook.com/v7.0/me?access_token=' . $this->getFBAccessToken() . '&fields=id,name,email';
		$response = file_get_contents($graphUrl);
		$user = json_decode($response);
		// Should store information.
	}


	/**
	 * Google social login process.
	 * 
	 * Create a Google API Console project and configure the Google Login API:
	 * Go to the Google API Console and create a new project. Then, enable the Google Login API. This will give you a unique Client ID and Client Secret.
	 * Create a PHP file for your Google Login button:
	 * Create a PHP file where you want the Login button to appear. This could be a login page, or a section of your website dedicated to social logins.
	 * Define a Login URL:
	 * Use the Client ID from step 1 to generate a Login URL. This URL will be used to redirect the user to Google to log in.
	 */
	private function getGooglelLoginBtn() {
		$loginBtn = '<a href="' . htmlspecialchars( $this->getGooglelLoginUrl() ) . '">Log in with Google!</a>';
		return $loginBtn;
	}
	private function getGooglelLoginUrl() {
		$loginUrl = "https://accounts.google.com/o/oauth2/v2/auth?client_id={$this->googleClientID}&redirect_uri={$this->googleAppRedirect}";
		return $loginUrl;
	}
	/**
	 * Handle the callback from Google:
	 * Create a new PHP file, google-callback.php, to handle the callback from Google. This file will receive the code parameter, and use it to request an access token.
	 * Get an Access Token:
	 * Use the $_GET['code'] parameter to get an access token. Replace the {client-id} and {client-secret} placeholders with your actual Client ID and Client Secret from step 1.
	 */
	private function getGooglelAccessToken() {
		$token_request = "https://oauth2.googleapis.com/token";
		$data = array(
			"code"					=> $_GET["code"],
			"client_id"			=> $this->googleClientID,
			"client_secret" => $this->googleClientSecret,
			"redirect_uri"	=> $this->googleAppRedirect,
			"grant_type"		=> "authorization_code"
		);
		$curl = curl_init($token_request);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);
		curl_close($curl);
		$response = json_decode($response);
		$access_token = $response->access_token;
		return $access_token;	
	}
	private function getGoogleInfofromAccessToken() {
		// Use the access token to retrieve the user's email address and name
		$info_request				= "https://www.googleapis.com/oauth2/v1/userinfo?access_token=" . $this->getGooglelAccessToken();
		$curl								= curl_init($info_request);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response						= curl_exec($curl);
		curl_close($curl);
		$person							= json_decode($response);
		$email							= $person->email;
		$name								= $person->name;
	
		// Store the email and name in the session or a database for later use
		// $_SESSION['google_email'] = $email;$_SESSION['google_name'] = $name;
		// Should be stored this info on database $person
	
		// Redirect the user to a protected page or show a welcome message
		header('Location: /welcome.php');
	}

	/**
	 * Instagram Authentication
	 * First, you need to register your application on the Instagram Developer platform and obtain a client ID and secret.
	 * Once you have your client ID and secret, you can redirect users to the Instagram authorization page using the following code:
	 */
	private function getInstagramlLoginBtn() {
		$loginBtn = '<a href="' . htmlspecialchars( $this->getInstagramlLoginUrl() ) . '">Log in with Instagram!</a>';
		return $loginBtn;
	}
	private function getInstagramlLoginUrl() {
		$loginUrl = "https://api.instagram.com/oauth/authorize/?client_id={$this->instagramAppID}&redirect_uri={$this->instagramAppRedirect}&response_type=code";
		return $loginUrl;
	}


	public function socialAuthRedirect( $default, $provider ) {
		if( $default !== false ) {
			return site_url( '/auth/' . $provider . '/capture' );
		}
		switch( $provider ) {
			case 'google' :
				return $this->googleAppRedirect;
				break;
			case 'facebook' :
				return $this->facebookAppRedirect;
				break;
			case 'instagram' :
				return $this->instagramAppRedirect;
				break;
			default :
				return $default;
				break;
		}
	}
	public function socialAuthLink( $default, $provider ) {
		if( $default !== false ) {
			return site_url( '/auth/' . $provider . '/redirect' );
		}
		switch( $provider ) {
			case 'google' :
				return $this->getGooglelLoginUrl();
				break;
			case 'facebook' :
				return $this->getFBLoginUrl();
				break;
			case 'instagram' :
				return $this->getInstagramlLoginUrl();
				break;
			default :
				return $default;
		}
	}
	public function query_vars( $query_vars ) {
		$query_vars[] = 'auth_provider';
		$query_vars[] = 'behaveing';
    return $query_vars;
	}
	public function rewriteRules( $rules ) {
		$rules[] = [ 'auth/([^/]*)/([^/]*)/?', 'index.php?auth_provider=$matches[1]&behaveing=$matches[2]', 'top' ];
		return $rules;
	}
	public function template_include( $template ) {
    $auth_provider = get_query_var( 'auth_provider' );
		if ( $auth_provider == false || $auth_provider == '' ) {
      return $template;
    } else {
			$file = TEDDY_BEAR_CUSTOMIZE_ADDON_DIR_PATH . '/templates/auth/index.php';
			if( file_exists( $file ) && ! is_dir( $file ) ) {
          return $file;
        } else {
          return $template;
        }
		}
	}

}
