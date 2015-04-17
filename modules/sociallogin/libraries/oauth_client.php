<?php
/**
* oauth_client.php
*
* @version   $Id: oauth_client.php, v1.46 2013/01/10 10:11:33 mlemos Exp $ 
* @author    Manuel Lemos
* @copyright Copyright © (C) Manuel Lemos 2012
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
* @package   sociallogin
*/

class oauth_client_class
{
	/**
	* Store the message that is returned when an error
	* occurs.
	* @var string
	*/
	public $error = '';

	/**
	* Control whether debug output is enabled
	* @var boolean
	*/
	public $debug = false;

	/**
	* Control whether the dialog with the remote Web server
	* should also be logged.
	* @var boolean
	*/
	public $debug_http = false;

	/**
	* Determine if the current script should be exited after proccess.
	* @var boolean
	*/
	public $exit = false;

	/**
	* Inspect this variable if you need to see what happened during
	* the class function calls.
	* @var string
	*/
	public $debug_output = '';

	/**
	* Mark the lines of the debug output to identify actions
	* performed by this class.
	* @var string
	*/
	private $debug_prefix = '[OAuth client]: ';

	/**
	* Identify the type of OAuth server to access.
	* @var string
	*/
	public $server = '';

	/**
	* URL of the OAuth server to request the initial token for
	* OAuth 1.0 and 1.0a servers.
	* @var string
	*/
	public $request_token_url = '';

	/**
	* URL of the OAuth server to redirect the browser so the user
	* can grant access to your application for Oauth 2.0 servers.
	* @var string
	*/
	public $dialog_url = '';

	/**
	* Pass the OAuth session state in a variable with a different
	* name to work around implementation bugs of certain OAuth
	* servers
	* @var string
	*/
	public $append_state_to_redirect_uri = '';

	/**
	* OAuth server URL that will return the access token
	* URL.
	* @var string
	*/
	public $access_token_url = '';

	/**
	* Determine if the OAuth parameters should be passed via HTTP
	* Authorization request header.
	* @var boolean
	*/
	public $authorization_header = true;

	/**
	* Determine if the API call parameters should be moved to the
	* call URL.
	* @var boolean
	*/
	public $url_parameters = false;

	/**
	* Version of the protocol version supported by the OAuth
	* server.
	* @var string
	*/
	public $oauth_version = '2.0';

	/**
	* URL of the current script page that is calling this
	* class
	* @var string
	*/
	public $redirect_uri = '';

	/**
	* Set this variable to the application identifier that is
	* provided by the OAuth server when you register the
	* application.
	* @var string
	*/
	public $client_id = '';

	/**
	* Set this variable to the application secret that is provided
	* by the OAuth server when you register the application.
	* @var string
	*/
	public $client_secret = '';

	/**
	* Permissions that your application needs to call the OAuth
	* server APIs
	* @var string
	*/
	public $scope = '';

	/**
	* Access token obtained from the OAuth server
	* Check this variable to get the obtained access token upon successful OAuth authorization.
	* @var string
	*/
	public $access_token = '';

	/**
	* Access token secret obtained from the OAuth server
	* If the OAuth protocol version is 1.0 or 1.0a, check this
	* variable to get the obtained access token secret upon successful
	* OAuth authorization.
	* @var string
	*/
	private $access_token_secret = '';

	/**
	* Timestamp of the expiry of the access token obtained from
	* the OAuth server.
	* @var string
	*/
	private $access_token_expiry = '';

	/**
	* Check this variable to get the obtained access token type
	* upon successful OAuth authorization.
	* @var string
	*/
	private $access_token_type = '';

	/**
	* Error message returned when a call to the API fails.
	* @var string
	*/
	private $access_token_error = '';

	/**
	* Error message returned when it was not possible to obtain
	* an OAuth access token
	* @var string
	*/
	public $authorization_error = '';

	/**
	* HTTP response status returned by the server when calling an
	* API
	* 200 means no errors
	* 0 means the server response was not retrieved.
	* @var intval
	*/
	private $response_status = 0;

	/**
	* @var string
	*/
	private $oauth_user_agent = 'PHP-OAuth-API (http://www.phpclasses.org/oauth-api $Revision: 1.46 $)';

	public function __construct()
	{
		if (!isset($this->context))
			$this->context = Context::getContext();

		$this->module = new SocialLogin();
	}

	/**
	* @return boolean true is load variables
	*/
	public function initialize()
	{
		if ($this->debug)
			$this->outputDebug('Class initialized with '.$this->server);
		switch ($this->server)
		{
			case '';
				break;

			case 'Bitbucket':
				$this->oauth_version = '1.0a';
				$this->request_token_url = 'https://bitbucket.org/!api/1.0/oauth/request_token';
				$this->dialog_url = 'https://bitbucket.org/!api/1.0/oauth/authenticate';
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://bitbucket.org/!api/1.0/oauth/access_token';
				$this->authorization_header = true;
				$this->url_parameters = true;
				break;

			case 'Dropbox':
				$this->oauth_version = '1.0';
				$this->request_token_url = 'https://api.dropbox.com/1/oauth/request_token';
				$this->dialog_url = 'https://www.dropbox.com/1/oauth/authorize';
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://api.dropbox.com/1/oauth/access_token';
				$this->authorization_header = false;
				$this->url_parameters = false;
				break;

			case 'Facebook':
				$this->oauth_version = '2.0';
				$this->request_token_url = '';
				$options_facebook = 'client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&scope={SCOPE}&state={STATE}';
				if (Configuration::get($this->module->name.'_POPUP'))
					$options_facebook .= '&display=popup';
				$this->dialog_url = 'https://www.facebook.com/v2.1/dialog/oauth?'.$options_facebook;
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://graph.facebook.com/v2.1/oauth/access_token';
				$this->authorization_header = true;
				$this->url_parameters = false;
				break;

			case 'Fitbit':
				$this->oauth_version = '1.0a';
				$this->request_token_url = 'http://api.fitbit.com/oauth/request_token';
				$this->dialog_url = 'http://api.fitbit.com/oauth/authorize';
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'http://api.fitbit.com/oauth/access_token';
				$this->authorization_header = true;
				$this->url_parameters = false;
				break;

			case 'Flickr':
				$this->oauth_version = '1.0a';
				$this->request_token_url = 'http://www.flickr.com/services/oauth/request_token';
				$this->dialog_url = 'http://www.flickr.com/services/oauth/authorize';
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'http://www.flickr.com/services/oauth/access_token';
				$this->authorization_header = false;
				$this->url_parameters = false;
				break;

			case 'Foursquare':
				$this->oauth_version = '2.0';
				$this->request_token_url = '';
				$options_foursquare = 'client_id={CLIENT_ID}&scope={SCOPE}&response_type=code&redirect_uri={REDIRECT_URI}&state={STATE}';
				$this->dialog_url = 'https://foursquare.com/oauth2/authorize?'.$options_foursquare;
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://foursquare.com/oauth2/access_token';
				$this->authorization_header = true;
				$this->url_parameters = false;
				break;

			case 'Github':
				$this->oauth_version = '2.0';
				$this->request_token_url = '';
				$this->dialog_url = 'https://github.com/login/oauth/authorize?client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&scope={SCOPE}&state={STATE}';
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://github.com/login/oauth/access_token';
				$this->authorization_header = true;
				$this->url_parameters = false;
				break;

			case 'Google':
				$this->oauth_version = '2.0';
				$this->request_token_url = '';
				$options_google = '&client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&scope={SCOPE}&state={STATE}';
				$this->dialog_url = 'https://accounts.google.com/o/oauth2/auth?response_type=code'.$options_google;
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://accounts.google.com/o/oauth2/token';
				$this->authorization_header = true;
				$this->url_parameters = false;
				break;

			case 'Instagram':
				$this->oauth_version = '2.0';
				$this->request_token_url = '';
				$options_instagram = 'client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&scope={SCOPE}&response_type=code&state={STATE}';
				$this->dialog_url = 'https://api.instagram.com/oauth/authorize/?'.$options_instagram;
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://api.instagram.com/oauth/access_token';
				$this->authorization_header = true;
				$this->url_parameters = false;
				break;

			case 'Linkedin':
				$this->oauth_version = '1.0a';
				$this->request_token_url = 'https://api.linkedin.com/uas/oauth/requestToken?scope={SCOPE}';
				$this->dialog_url = 'https://api.linkedin.com/uas/oauth/authenticate';
				$this->access_token_url = 'https://api.linkedin.com/uas/oauth/accessToken';
				$this->append_state_to_redirect_uri = '';
				$this->authorization_header = true;
				$this->url_parameters = true;
				break;

			case 'Microsoft':
				$this->oauth_version = '2.0';
				$this->request_token_url = '';
				$options_microsoft = 'client_id={CLIENT_ID}&scope={SCOPE}&response_type=code&redirect_uri={REDIRECT_URI}&state={STATE}';
				$this->dialog_url = 'https://login.live.com/oauth20_authorize.srf?'.$options_microsoft;
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://login.live.com/oauth20_token.srf';
				$this->authorization_header = true;
				$this->url_parameters = false;
				break;

			case 'Scoop.it':
				$this->oauth_version = '1.0a';
				$this->request_token_url = 'https://www.scoop.it/oauth/request';
				$this->dialog_url = 'https://www.scoop.it/oauth/authorize';
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://www.scoop.it/oauth/access';
				$this->authorization_header = false;
				$this->url_parameters = false;
				break;

			case 'Tumblr':
				$this->oauth_version = '1.0a';
				$this->request_token_url = 'http://www.tumblr.com/oauth/request_token';
				$this->dialog_url = 'http://www.tumblr.com/oauth/authorize';
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'http://www.tumblr.com/oauth/access_token';
				$this->authorization_header = true;
				$this->url_parameters = false;
				break;

			case 'Twitter':
				$this->oauth_version = '1.0a';
				$this->request_token_url = 'https://api.twitter.com/oauth/request_token';
				$this->dialog_url = 'https://api.twitter.com/oauth/authenticate';
				$this->append_state_to_redirect_uri = '';
				$this->access_token_url = 'https://api.twitter.com/oauth/access_token';
				$this->authorization_header = true;
				$this->url_parameters = true;
				break;

			case 'Yahoo':
				$this->oauth_version = '1.0a';
				$this->request_token_url = 'https://api.login.yahoo.com/oauth/v2/get_request_token';
				$this->dialog_url = 'https://api.login.yahoo.com/oauth/v2/request_auth';
				$this->access_token_url = 'https://api.login.yahoo.com/oauth/v2/get_token';
				$this->append_state_to_redirect_uri = '';
				$this->authorization_header = false;
				$this->url_parameters = false;
				break;

			default:
				return ($this->setError($this->server.' is not yet a supported type of OAuth server. 
					Please contact the author Manuel Lemos <mlemos@acm.org> to request adding built-in support to this type of OAuth server.'));
		}
		return (true);
	}

	/**
	 * @return array specific configuration api
	 */
	public function getApiUserUrl()
	{
		switch ($this->server)
		{
			case 'Facebook':
				$url = 'https://graph.connect.facebook.com/v2.1/me/';
				$method = 'GET';
				$parameters = array();
				$options = array('FailOnAccessError' => true);
				$scope = 'public_profile,email'; //,user_birthday';
				break;
			case 'Google':
				// $url = 'https://www.googleapis.com/oauth2/v1/userinfo';
				$url = 'https://www.googleapis.com/plus/v1/people/me';
				$method = 'GET';
				$parameters = array('fields', 'birthday,emails,gender,id,name(familyName,givenName)');
				$options = array('FailOnAccessError' => true);
				$scope = 'profile email';
				break;
			case 'Linkedin':
				$url = 'http://api.linkedin.com/v1/people/~:(id,first_name,last_name,date-of-birth,picture-url,email-address,public-profile-url)?format=json';
				$method = 'GET';
				$parameters = array();
				$options = array('FailOnAccessError' => true);
				$scope = 'r_fullprofile r_emailaddress';
				break;
			case 'Microsoft':
				$url = 'https://apis.live.net/v5.0/me';
				$method = 'GET';
				$parameters = array();
				$options = array('FailOnAccessError' => true);
				$scope = 'wl.basic wl.emails wl.birthday';
				break;
			case 'Twitter':
				$url = 'https://api.twitter.com/1.1/account/verify_credentials.json';
				$method = 'GET';
				$parameters = array();
				$options = array('FailOnAccessError' => true);
				$scope = '';
				break;
			case 'Yahoo':
				$url = 'https://social.yahooapis.com/v1/user/me/profile?format=json';
				$method = 'GET';
				$parameters = array();
				$options = array('FailOnAccessError' => true);
				$scope = '';
				break;
			default:
				return false;
		}

		return array(
			'url' => $url,
			'method' => $method,
			'parameters' => $parameters,
			'options' => $options,
			'scope' => $scope,
		);
	}

	/**
	 * @param array $user data from api
	 * @param string $action
	 * @return array $data_profile
	 */
	public function getUserData($user)
	{
		if ($this->debug)
			$this->outputDebug('Received user info: '.print_r($user, true));

		if (empty($user) || empty($this->server))
			return;

		$id = '';
		$first_name = '';
		$last_name = '';
		$gender = 0;
		$email = '';

		switch ($this->server)
		{
			case 'Facebook':
				if (isset($user->id) && Validate::isGenericName($user->id))
					$id = $user->id;
				if (isset($user->first_name) && Validate::isName($user->first_name))
					$first_name = $user->first_name;
				if (isset($user->last_name) && Validate::isName($user->last_name))
					$last_name = $user->last_name;
				if (isset($user->email) && Validate::isEmail($user->email))
					$email = $user->email;
				if (isset($user->gender))
				{
					if ($user->gender == 'male')
						$gender = 1;
					elseif ($user->gender == 'female')
						$gender = 2;
				}
				break;
			case 'Google':
				if (isset($user->id) && Validate::isGenericName($user->id))
					$id = $user->id;
				if (isset($user->name->givenName) && Validate::isName($user->name->givenName))
					$first_name = $user->name->givenName;
				if (isset($user->name->familyName) && Validate::isName($user->familyName))
					$last_name = $user->name->familyName;
				if (isset($user->emails[0]->value) && Validate::isEmail($user->emails[0]->value))
					$email = $user->emails[0]->value;
				if (isset($user->gender))
				{
					if ($user->gender == 'male')
						$gender = 1;
					elseif ($user->gender == 'female')
						$gender = 2;
				}
				break;
			case 'Linkedin':
				if (isset($user->id) && Validate::isGenericName($user->id))
					$id = $user->id;
				if (isset($user->firstName) && Validate::isName($user->firstName))
					$first_name = $user->firstName;
				if (isset($user->lastName) && Validate::isName($user->lastName))
					$last_name = $user->lastName;
				if (isset($user->emailAddress) && Validate::isEmail($user->emailAddress))
					$email = $user->emailAddress;
				break;
			case 'Microsoft':
				if (isset($user->id) && Validate::isGenericName($user->id))
					$id = $user->id;
				if (isset($user->first_name) && Validate::isName($user->first_name))
					$first_name = $user->first_name;
				if (isset($user->last_name) && Validate::isName($user->last_name))
					$last_name = $user->last_name;
				if (isset($user->emails->personal) && Validate::isEmail($user->emails->personal))
					$email = $user->emails->personal;
				elseif (isset($user->emails->preferred) && Validate::isEmail($user->emails->preferred))
					$email = $user->emails->preferred;
				elseif (isset($user->emails->account) && Validate::isEmail($user->emails->account))
					$email = $user->emails->account;
				elseif (isset($user->emails->business) && Validate::isEmail($user->emails->business))
					$email = $user->emails->business;
				if (isset($user->gender))
				{
					if ($user->gender == 'male')
						$gender = 1;
					elseif ($user->gender == 'female')
						$gender = 2;
				}
				break;
			case 'Yahoo':
				if (isset($user->profile->guid) && Validate::isGenericName($user->profile->guid))
					$id = $user->profile->guid;
				if (isset($user->profile->givenName) && Validate::isName($user->profile->givenName))
					$first_name = $user->profile->givenName;
				if (isset($user->profile->familyName) && Validate::isName($user->profile->familyName))
					$last_name = $user->profile->familyName;
				if (is_array($user->profile->emails) && Validate::isEmail($user->profile->emails[0]->handle))
					$email = $user->profile->emails[0]->handle;

				if (isset($user->profile->gender))
				{
					if ($user->profile->gender == 'M')
						$gender = 1;
					elseif ($user->profile->gender == 'F')
						$gender = 2;
				}
				break;
			case 'Twitter':
				if (isset($user->id_str) && Validate::isGenericName($user->id_str))
					$id = $user->id_str;
				if (isset($user->name) && Validate::isName($user->name))
					$name = explode(' ', $user->name, 2);

				if (isset($name) && count($name) == 2)
				{
					$first_name = $name[0];
					$last_name = $name[1];
				}
				elseif (isset($name))
					$first_name = $user->name;
				break;
			default:
				return;
		}

		$data_profile = array(
			'required' => array(
				'first_name' => Tools::ucwords($first_name),
				'last_name' => Tools::ucwords($last_name),
				'email' => pSQL($email),
			),
			'optional' => array(
				'gender' => (int)$gender,
			),
			'user' => array(
				'id_user' => pSQL($id),
				'network' => pSQL(Tools::strtolower($this->server)),
			)
		);

		return $data_profile;
	}

	/**
	* @return boolean true is successfully
	*/
	public function process()
	{
		switch ((int)$this->oauth_version)
		{
			case 1:
				return $this->processOauthOne();

			case 2:
				$this->processOauthTwo();
				break;

			default:
				return ($this->setError($this->oauth_version.' is not a supported version of the OAuth protocol'));
		}
		return (true);
	}

	private function processOauthOne()
	{
		$one_a = ($this->oauth_version === '1.0a');

		if ($this->debug)
			$this->outputDebug('Checking the OAuth token authorization state');

		$access_token = '';
		if (!$this->getAccessToken($access_token))
			return false;
		if (IsSet($access_token['authorized']) && IsSet($access_token['value']))
		{
			$expired = (IsSet($access_token['expiry']) && strcmp($access_token['expiry'], gmstrftime('%Y-%m-%d %H:%M:%S')) <= 0);
			if (!$access_token['authorized'] || $expired)
			{
				if ($this->debug)
				{
					if ($expired)
						$this->outputDebug('The OAuth token expired on '.$access_token['expiry'].'UTC');
					else
						$this->outputDebug('The OAuth token is not yet authorized');
					$this->outputDebug('Checking the OAuth token and verifier');
				}
				if (!$this->getRequestToken($token, $verifier))
					return false;
				if (!IsSet($token) || ($one_a && !IsSet($verifier)))
				{
					if (!$this->getRequestDenied($denied))
						return false;
					if (IsSet($denied) && $denied === $access_token['value'])
					{
						if ($this->debug)
							$this->outputDebug('The authorization request was denied');
						$this->authorization_error = 'the request was denied';
						return true;
					}
					else
					{
						if ($this->debug)
							$this->outputDebug('Reset the OAuth token state because token and verifier are not both set');
						$access_token = array();
					}
				}
				elseif ($token !== $access_token['value'])
				{
					if ($this->debug)
						$this->outputDebug('Reset the OAuth token state because token does not match what as previously retrieved');
					$access_token = array();
				}
				else
				{
					if (!$this->getAccessTokenURL($url))
						return false;
					$oauth = array(
						'oauth_token'=>$token,
					);
					if ($one_a)
						$oauth['oauth_verifier'] = $verifier;
					$this->access_token_secret = $access_token['secret'];
					if (!$this->sendAPIRequest($url, 'GET', array(), $oauth, array('Resource'=>'OAuth access token'), $response))
						return false;
					if (Tools::strlen($this->access_token_error))
					{
						$this->authorization_error = $this->access_token_error;
						return true;
					}
					if (!IsSet($response['oauth_token']) || !IsSet($response['oauth_token_secret']))
					{
						$this->authorization_error = 'it was not returned the access token and secret';
						return true;
					}
					$access_token = array(
						'value'=>$response['oauth_token'],
						'secret'=>$response['oauth_token_secret'],
						'authorized'=>true
					);
					if (IsSet($response['oauth_expires_in']))
					{
						$expires = $response['oauth_expires_in'];
						if ((string)$expires !== (string)(int)$expires || $expires <= 0)
							return ($this->setError('OAuth server did not return a supported type of access token expiry time'));
						$this->access_token_expiry = gmstrftime('%Y-%m-%d %H:%M:%S', time() + $expires);
						if ($this->debug)
							$this->outputDebug('Access token expiry: '.$this->access_token_expiry.' UTC');
						$access_token['expiry'] = $this->access_token_expiry;
					}
					else
						$this->access_token_expiry = '';

					if (!$this->storeAccessToken($access_token))
						return false;
					if ($this->debug)
						$this->outputDebug('The OAuth token was authorized');
				}
			}
			elseif ($this->debug)
				$this->outputDebug('The OAuth token was already authorized');
			if (IsSet($access_token['authorized']) && $access_token['authorized'])
			{
				$this->access_token = $access_token['value'];
				$this->access_token_secret = $access_token['secret'];
				return true;
			}
		}
		else
		{
			if ($this->debug)
				$this->outputDebug('The OAuth access token is not set');
			$access_token = array();
		}
		if (!IsSet($access_token['authorized']))
		{
			if ($this->debug)
				$this->outputDebug('Requesting the unauthorized OAuth token');
			if (!$this->getRequestTokenURL($url))
				return false;
			$url = str_replace('{SCOPE}', UrlEncode($this->scope), $url);
			if (!$this->getRedirectURI($redirect_uri))
				return false;
			$oauth = array(
				'oauth_callback' => $redirect_uri,
			);
			if (!$this->sendAPIRequest($url, 'GET', array(), $oauth, array('Resource'=>'OAuth request token'), $response))
				return false;
			if (Tools::strlen($this->access_token_error))
			{
				$this->authorization_error = $this->access_token_error;
				return true;
			}
			if (!IsSet($response['oauth_token']) || !IsSet($response['oauth_token_secret']))
			{
				$this->authorization_error = 'it was not returned the requested token';
				return true;
			}
			$access_token = array(
				'value'=>$response['oauth_token'],
				'secret'=>$response['oauth_token_secret'],
				'authorized'=>false
			);
			if (!$this->storeAccessToken($access_token))
				return false;
		}
		if (!$this->getDialogURL($url))
			return false;
		$url .= '?oauth_token='.$access_token['value'];
		if (!$one_a)
		{
			if (!$this->getRedirectURI($redirect_uri))
				return false;
			$url .= '&oauth_callback='.UrlEncode($redirect_uri);
		}
		if ($this->debug)
			$this->outputDebug('Redirecting to OAuth authorize page '.$url);

		Tools::redirect($url);
		$this->exit = true;
		return true;
	}

	private function processOauthTwo()
	{
		if ($this->debug)
			$this->outputDebug('Checking if OAuth access token was already retrieved from '.$this->access_token_url);

		$access_token = '';
		if (!$this->getAccessToken($access_token))
			return false;

		if (IsSet($access_token['value']))
		{
			if (IsSet($access_token['expiry']) && strcmp($this->access_token_expiry = $access_token['expiry'], gmstrftime('%Y-%m-%d %H:%M:%S')) < 0)
			{
				if ($this->debug)
					$this->outputDebug('The OAuth access token expired in '.$this->access_token_expiry);
			}
			else
			{
				$this->access_token = $access_token['value'];
				if (IsSet($access_token['type']))
					$this->access_token_type = $access_token['type'];
				if ($this->debug)
					$this->outputDebug('The OAuth access token '.$this->access_token.' is valid');
				if (Tools::strlen($this->access_token_type) && $this->debug)
					$this->outputDebug('The OAuth access token is of type '.$this->access_token_type);
				return true;
			}
		}

		if ($this->debug)
			$this->outputDebug('Checking the authentication state in URI '.$_SERVER['REQUEST_URI']);

		if (!$this->getStoredState($stored_state))
			return false;

		if (Tools::strlen($stored_state) == 0)
			return ($this->setError('it was not set the OAuth state'));

		if (!$this->getRequestState($state))
			return false;

		if ($state === $stored_state)
		{
			if ($this->debug)
				$this->outputDebug('Checking the authentication code');
			if (!$this->getRequestCode($code))
				return false;
			if (Tools::strlen($code) == 0)
			{
				if (!$this->getRequestError($this->authorization_error))
					return false;
				if (IsSet($this->authorization_error))
				{
					if ($this->debug)
						$this->outputDebug('Authorization failed with error code '.$this->authorization_error);
					switch ($this->authorization_error)
					{
						case 'invalid_request':
						case 'unauthorized_client':
						case 'access_denied':
						case 'unsupported_response_type':
						case 'invalid_scope':
						case 'server_error':
						case 'temporarily_unavailable':
						case 'user_denied':
							return true;
						default:
							return ($this->setError('it was returned an unknown OAuth error code'));
					}
				}
				return ($this->setError('it was not returned the OAuth dialog code'));
			}
			if (!$this->getAccessTokenURL($url))
				return false;
			if (!$this->getRedirectURI($redirect_uri))
				return false;
			$values = array(
				'code'=>$code,
				'client_id'=>$this->client_id,
				'client_secret'=>$this->client_secret,
				'redirect_uri'=>$redirect_uri,
				'grant_type'=>'authorization_code'
			);
			if (!$this->sendAPIRequest($url, 'POST', $values, null, array('Resource'=>'OAuth access token', 'ConvertObjects'=>true), $response))
				return false;
			if (Tools::strlen($this->access_token_error))
			{
				$this->authorization_error = $this->access_token_error;
				return true;
			}
			if (!IsSet($response['access_token']))
			{
				if (IsSet($response['error']))
				{
					$this->authorization_error = 'it was not possible to retrieve the access token: it was returned the error: '.$response['error'];
					return true;
				}
				return ($this->setError('OAuth server did not return the access token'));
			}
			$access_token = array(
				'value'=>$this->access_token = $response['access_token'],
				'authorized'=>true
			);
			if ($this->debug)
				$this->outputDebug('Access token: '.$this->access_token);
			if (IsSet($response['expires']) || IsSet($response['expires_in']))
			{
				$expires = (IsSet($response['expires']) ? $response['expires'] : $response['expires_in']);
				if ((string)$expires !== (string)(int)$expires || $expires <= 0)
					return ($this->setError('OAuth server did not return a supported type of access token expiry time'));
				$this->access_token_expiry = gmstrftime('%Y-%m-%d %H:%M:%S', time() + $expires);
				if ($this->debug)
					$this->outputDebug('Access token expiry: '.$this->access_token_expiry.' UTC');
				$access_token['expiry'] = $this->access_token_expiry;
			}
			else
				$this->access_token_expiry = '';
			if (IsSet($response['token_type']))
			{
				$this->access_token_type = $response['token_type'];
				if ($this->debug)
					$this->outputDebug('Access token type: '.$this->access_token_type);
				$access_token['type'] = $this->access_token_type;
			}
			else
				$this->access_token_type = '';
			if (!$this->storeAccessToken($access_token))
				return false;
		}
		else
		{
			if (!$this->getDialogURL($url))
				return false;

			if (Tools::strlen($url) == 0)
				return ($this->setError('it was not set the OAuth dialog URL'));

			if (!$this->getRedirectURI($redirect_uri))
				return false;

			if (Tools::strlen($this->append_state_to_redirect_uri))
				$redirect_uri .= (strpos($redirect_uri, '?') === false ? '?' : '&').$this->append_state_to_redirect_uri.'='.$stored_state;

			$url = str_replace(
				'{REDIRECT_URI}', UrlEncode($redirect_uri), str_replace(
				'{CLIENT_ID}', UrlEncode($this->client_id), str_replace(
				'{SCOPE}', UrlEncode($this->scope), str_replace(
				'{STATE}', UrlEncode($stored_state),
				$url))));

			if ($this->debug)
				$this->outputDebug('Redirecting to OAuth Dialog '.$url);

			Tools::redirect($url);
			$this->exit = true;
		}
	}

	/**
	* @var string $url
	* @var string $method
	* @var array $parameters
	* @var array $options
	* @return boolen true is succesffully
	* @var string $response with answer
	*/
	public function callAPI($url, $method, $parameters, $options, &$response)
	{
		if (!IsSet($options['Resource']))
			$options['Resource'] = 'API call';
		if (!IsSet($options['ConvertObjects']))
			$options['ConvertObjects'] = false;
		switch ((int)$this->oauth_version)
		{
			case 1:
				$oauth = array(
					'oauth_token'=>$this->access_token
				);
				break;

			case 2:
				$oauth = null;
				$url .= (strcspn($url, '?') < Tools::strlen($url) ? '&' : '?').'access_token='.UrlEncode($this->access_token);
				break;

			default:
				return ($this->setError($this->oauth_version.' is not a supported version of the OAuth protocol'));
		}
		return ($this->sendAPIRequest($url, $method, $parameters, $oauth, $options, $response));
	}

	/**
	* @var boolean $success
	* @return boolean
	*/
	public function finalize($success)
	{
		return ($success);
	}

	private function setError($error)
	{
		$this->error = $error;
		if ($this->debug)
			$this->outputDebug('Error: '.$error);
		return (false);
	}

	private function setPHPError($error, &$php_error_message)
	{
		if (IsSet($php_error_message) && Tools::strlen($php_error_message))
			$error .= ': '.$php_error_message;
		return ($this->setError($error));
	}

	private function outputDebug($message)
	{
		if ($this->debug)
		{
			$message = $this->debug_prefix.$message;
			$this->debug_output .= $message.'\n';
			Logger::AddLog($message);
		}
		return (true);
	}

	private function getRequestTokenURL(&$request_token_url)
	{
		$request_token_url = $this->request_token_url;
		return (true);
	}

	private function getDialogURL(&$redirect_url)
	{
		$redirect_url = $this->dialog_url;
		return (true);
	}

	private function getAccessTokenURL(&$access_token_url)
	{
		$access_token_url = $this->access_token_url;
		return (true);
	}

	/**
	* @var string $state
	* @return boolean true is successfully
	*/
	private function getStoredState(&$state)
	{
		if (!$this->context->cookie->__isset('OAUTH_STATE'))
			$this->context->cookie->__set('OAUTH_STATE', time().'-'.Tools::substr(md5(rand().time()), 0, 6));

		$state = $this->context->cookie->__get('OAUTH_STATE');

		return (true);
	}

	private function getRequestState(&$state)
	{
		$check = (Tools::strlen($this->append_state_to_redirect_uri) ? $this->append_state_to_redirect_uri : 'state');
		$state = (Tools::getIsset($check) ? Tools::getValue($check) : null);
		return (true);
	}

	private function getRequestCode(&$code)
	{
		$code = (Tools::getIsset('code') ? Tools::getValue('code') : null);
		return (true);
	}

	private function getRequestError(&$error)
	{
		$error = (Tools::getIsset('error') ? Tools::getValue('error') : null);
		return (true);
	}

	private function getRequestDenied(&$denied)
	{
		$denied = (Tools::getIsset('denied') ? Tools::getValue('denied') : null);
		return (true);
	}

	private function getRequestToken(&$token, &$verifier)
	{
		$token = (Tools::getIsset('oauth_token') ? Tools::getValue('oauth_token') : null);
		$verifier = (Tools::getIsset('oauth_verifier') ? Tools::getValue('oauth_verifier') : null);
		return (true);
	}

	private function getRedirectURI(&$redirect_uri)
	{
		if (Tools::strlen($this->redirect_uri))
			$redirect_uri = $this->redirect_uri;
		else
			$redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		return true;
	}

	/**
	* @var array $acces_token
	* @return boolean true is succesffully
	*/
	private function storeAccessToken($access_token)
	{
		if (is_array($access_token))
			foreach ($access_token as $key => $value)
				$this->context->cookie->__set($this->server.$key, $value);

		$this->context->cookie->__set($this->server.'OAUTH_ACCESS_TOKEN', $this->access_token_url);
		return true;
	}

	/**
	* @var array $acces_token array(
	* @var string 'authorized’
	* @var string 'value’
	* @var string 'expiry’
	* @var string 'secret’
	* @var string 'type' for oauth 2.0)
	* @return boolean true is succesffully
	*/
	private function getAccessToken(&$access_token)
	{
		if ($this->context->cookie->__isset($this->server.'OAUTH_ACCESS_TOKEN'))
		{
			if ($this->context->cookie->__isset($this->server.'authorized'))
				$access_token['authorized'] = $this->context->cookie->__get($this->server.'authorized');

			if ($this->context->cookie->__isset($this->server.'value'))
				$access_token['value'] = $this->context->cookie->__get($this->server.'value');

			if ($this->context->cookie->__isset($this->server.'expiry'))
				$access_token['expiry'] = $this->context->cookie->__get($this->server.'expiry');

			if ($this->context->cookie->__isset($this->server.'secret'))
				$access_token['secret'] = $this->context->cookie->__get($this->server.'secret');

			if ($this->context->cookie->__isset($this->server.'type'))
				$access_token['type'] = $this->context->cookie->__get($this->server.'type');
		}
		else
			$access_token = array();

		return true;
	}

	/**
	* @return boolean true is succesffully
	*/
	private function resetAccessToken()
	{
		if ($this->debug)
			$this->outputDebug('Resetting the access token status for OAuth server located at '.$this->access_token_url);

		if ($this->context->cookie->__isset($this->server.'OAUTH_ACCESS_TOKEN'))
		{
			$this->context->cookie->__unset($this->server.'OAUTH_ACCESS_TOKEN');

			if ($this->context->cookie->__isset($this->server.'authorized'))
				$this->context->cookie->__unset($this->server.'authorized');

			if ($this->context->cookie->__isset($this->server.'value'))
				$this->context->cookie->__unset($this->server.'value');

			if ($this->context->cookie->__isset($this->server.'expiry'))
				$this->context->cookie->__unset($this->server.'expiry');

			if ($this->context->cookie->__isset($this->server.'secret'))
				$this->context->cookie->__unset($this->server.'secret');

			if ($this->context->cookie->__isset($this->server.'type'))
				$this->context->cookie->__unset($this->server.'type');
		}

		return true;
	}

	/**
	* @var string $value
	* @return string or array
	*/
	private function encode($value)
	{
		return (is_array($value) ? $this->encodeArray($value) : str_replace('%7E', '~', str_replace('+', ' ', RawURLEncode($value))));
	}

	private function encodeArray($array)
	{
		foreach ($array as $key => $value)
			$array[$key] = $this->encode($value);
		return $array;
	}

	private function HMAC($function, $data, $key)
	{
		switch ($function)
		{
			case 'sha1':
				$pack = 'H40';
				break;
			default:
				if ($this->debug)
					$this->outputDebug($function.' is not a supported an HMAC hash type');
				return ('');
		}
		if (Tools::strlen($key) > 64)
			$key = pack($pack, $function($key));
		if (Tools::strlen($key) < 64)
			$key = str_pad($key, 64, "\0");
		return (pack($pack, $function((str_repeat("\x5c", 64) ^ $key).pack($pack, $function((str_repeat("\x36", 64) ^ $key).$data)))));
	}

	private function sendAPIRequest($url, $method, $parameters, $oauth, $options, &$response)
	{
		$this->response_status = 0;
		include_once ('http.php');
		$http = new http_class;
		$http->debug = ($this->debug && $this->debug_http);
		$http->log_debug = true;
		$http->user_agent = $this->oauth_user_agent;

		if ($this->debug)
			$this->outputDebug('Accessing the '.$options['Resource'].' at '.$url);

		$arguments = array();
		$method = Tools::strtoupper($method);
		$authorization = '';
		if (isset($options['RequestContentType']))
			$type = Tools::strtolower(trim(strtok($options['RequestContentType'], ';')));
		else
			$type = 'application/x-www-form-urlencoded';
		if (IsSet($oauth))
		{
			$values = array(
				'oauth_consumer_key' => $this->client_id,
				'oauth_nonce' => md5(uniqid(rand(), true)),
				'oauth_signature_method' => 'HMAC-SHA1',
				'oauth_timestamp' => time(),
				'oauth_version' => '1.0',
			);
			if ($this->url_parameters && $type === 'application/x-www-form-urlencoded' && count($parameters))
			{
				$first = (strpos($url, '?') === false);
				foreach ($parameters as $parameter => $value)
					$url .= ($first ? '?' : '&').UrlEncode($parameter).'='.UrlEncode($value);
				$parameters = array();
			}
			$value_parameters = ($type !== 'application/x-www-form-urlencoded' ? array() : $parameters);
			$values = array_merge($values, $oauth, $value_parameters);
			$uri = strtok($url, '?');
			$sign = $method.'&'.$this->encode($uri).'&';
			$first = true;
			$sign_values = $values;
			$u = parse_url($url);
			if (IsSet($u['query']))
			{
				parse_str($u['query'], $q);
				foreach ($q as $parameter => $value)
					$sign_values[$parameter] = $value;
			}
			KSort($sign_values);
			foreach ($sign_values as $parameter => $value)
			{
				$sign .= $this->encode(($first ? '' : '&').$parameter.'='.$this->encode($value));
				$first = false;
			}
			$key = $this->encode($this->client_secret).'&'.$this->encode($this->access_token_secret);
			$values['oauth_signature'] = base64_encode($this->HMAC('sha1', $sign, $key));
			if ($this->authorization_header)
			{
				$authorization = 'OAuth';
				$first = true;
				foreach ($values as $parameter => $value)
				{
					$authorization .= ($first ? ' ' : ',').$parameter.'="'.$this->encode($value).'"';
					$first = false;
				}
			}
			else
			{
				if ($method === 'GET')
				{
					$first = (strcspn($url, '?') == Tools::strlen($url));
					foreach ($values as $parameter => $value)
					{
						$url .= ($first ? '?' : '&').$parameter.'='.$this->encode($value);
						$first = false;
					}
					// $post_values = array();
				}
				// else
					// $post_values = $values;
			}
		}
		if (Tools::strlen($error = $http->getRequestArguments($url, $arguments)))
			return ($this->setError('it was not possible to open the '.$options['Resource'].' URL: '.$error));

		if (Tools::strlen($error = $http->open($arguments)))
			return ($this->setError('it was not possible to open the '.$options['Resource'].' URL: '.$error));

		$arguments['RequestMethod'] = $method;
		switch ($type)
		{
			case 'application/x-www-form-urlencoded':
				if (IsSet($options['RequestBody']))
					return ($this->setError('the request body is defined automatically from the parameters'));
				$arguments['PostValues'] = $parameters;
				break;
			case 'application/json':
				$arguments['Headers']['Content-Type'] = $options['RequestContentType'];
				if (!IsSet($options['RequestBody']))
				{
					$arguments['Body'] = Tools::jsonEncode($parameters);
					break;
				}
			default:
				if (!IsSet($options['RequestBody']))
					return ($this->setError('it was not specified the body value of the of the API call request'));
				$arguments['Headers']['Content-Type'] = $options['RequestContentType'];
				$arguments['Body'] = $options['RequestBody'];
				break;
		}
		$arguments['Headers']['Accept'] = (IsSet($options['Accept']) ? $options['Accept'] : '*/*');
		if (Tools::strlen($authorization))
			$arguments['Headers']['Authorization'] = $authorization;
		if (Tools::strlen($error = $http->sendRequest($arguments)) || Tools::strlen($error = $http->readReplyHeaders($headers)))
		{
			$http->close();
			return ($this->setError('it was not possible to retrieve the '.$options['Resource'].': '.$error));
		}
		$error = $http->readWholeReplyBody($data);
		$http->close();
		if (Tools::strlen($error))
			return ($this->setError('it was not possible to access the '.$options['Resource'].': '.$error));

		$this->response_status = (int)$http->response_status;
		$content_type = (IsSet($headers['content-type']) ? Tools::strtolower(trim(strtok($headers['content-type'], ';'))) : 'unspecified');
		switch ($content_type)
		{
			case 'text/javascript':
			case 'application/json':
				if (!function_exists('json_decode'))
					return ($this->setError('the JSON extension is not available in this PHP setup'));

				$object = Tools::jsonDecode($data);
				switch (GetType($object))
				{
					case 'object':
						if (!IsSet($options['ConvertObjects'])
						|| !$options['ConvertObjects'])
							$response = $object;
						else
						{
							$response = array();
							foreach ($object as $property => $value)
								$response[$property] = $value;
						}
						break;
					case 'array':
						$response = $object;
						break;
					default:
						if (!IsSet($object))
							return ($this->setError('it was not returned a valid JSON definition of the '.$options['Resource'].' values'));
						$response = $object;
						break;
				}
				break;
			case 'application/x-www-form-urlencoded':
			case 'text/plain':
			case 'text/html':
				parse_str($data, $response);
				break;
			default:
				$response = $data;
				break;
		}
		if ($this->response_status >= 200 && $this->response_status < 300)
			$this->access_token_error = '';
		else
		{
			$this->access_token_error = 'it was not possible to access the '.$options['Resource'].
				': it was returned an unexpected response status '.$http->response_status.' Response: '.$data;

			if ($this->debug)
				$this->outputDebug('Could not retrieve the OAuth access. Error: '.$this->access_token_error);

			if (IsSet($options['FailOnAccessError']) && $options['FailOnAccessError'])
			{
				$this->error = $this->access_token_error;
				return false;
			}
		}
		return true;
	}
	public function Output()
	{
		if (Tools::strlen($this->authorization_error) || Tools::strlen($this->access_token_error) || Tools::strlen($this->access_token))
		{
?>
			<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
			<html>
				<head>
					<title>OAuth client result</title>
				</head>
				<body>
					<h1>OAuth client result</h1>
					<?php if (Tools::strlen($this->authorization_error))
					{?>
						<p>It was not possible to authorize the application.
						<?php if ($this->debug)
						{?>
							<br>Authorization error: 
							<?php echo HtmlSpecialChars($this->authorization_error);
						}?>
						</p>
<?php
					}
					elseif (Tools::strlen($this->access_token_error))
					{?>
						<p>It was not possible to use the application access token.
						<?php if ($this->debug)
						{?>
							<br>Error: <?php echo HtmlSpecialChars($this->access_token_error);
						}?>
						</p>
<?php
					}
					elseif (Tools::strlen($this->access_token))
					{?>
						<p>The application authorization was obtained successfully.
						<?php if ($this->debug)
						{?>
							<br>Access token:
							<?php echo HtmlSpecialChars($this->access_token);
							if (isset($this->access_token_secret))?>
								<br>Access token secret: <?php echo HtmlSpecialChars($this->access_token_secret);
						}?>
						</p>
						<?php if (Tools::strlen($this->access_token_expiry)) ?>
							<p>Access token expiry: <?php echo $this->access_token_expiry; ?> UTC</p>
<?php
					}
					?>
				</body>
			</html>
<?php
		}
	}
}