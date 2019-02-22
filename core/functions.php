<?php



function get_config_data($loc){
  // Open FB Config File
  $fb_config_file = @fopen($loc, 'r');

  // Read FB Config Contents
  $contents = @fread($fb_config_file, filesize($loc));

  // Close Config File
  @fclose($fb_config_file);

  // Return Config
  return $contents;
}



function get_domain(){
  return (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'];
}



function fb_get_login_url($loc){
  // Get FB config file
  $fb_config = json_decode(get_config_data($loc));

  if(empty($fb_config)) return false;

  $fb_obj = new \Facebook\Facebook([
    'app_id' => $fb_config->app_id,
    'app_secret' => $fb_config->app_secret,
    'default_graph_version' => 'v2.2',
  ]);

  $helper = $fb_obj->getRedirectLoginHelper();

  // Permission
  $permissions = ['email'];

  // Callback URL
  $callback_url = get_domain().$fb_config->call_back;

  // The Login URL
  return $helper->getLoginUrl($callback_url, $permissions);
}



function get_fb_user_data($loc){
  // Get FB config file
  $fb_config = json_decode(get_config_data($loc));

  if(empty($fb_config)) return array(
    'status'      => false,
    'description' => 'Config file not found / unreadable / empty at: "'.$loc.'"'
  );

  $fb_obj = new \Facebook\Facebook([
    'app_id' => $fb_config->app_id,
    'app_secret' => $fb_config->app_secret,
    'default_graph_version' => 'v2.2',
  ]);

  $helper = $fb_obj->getRedirectLoginHelper();

  try{ $accessToken = $helper->getAccessToken(); }
  catch(Exception $e){ return array(
    'status'      => false,
    'description' => 'Unable to get access token!'
  ); }

  if(empty($accessToken)) return array(
    'status'      => false,
    'description' => 'Access token empty!'
  );

  // The OAuth 2.0 client handler helps us manage access tokens
  $oAuth2Client = $fb_obj->getOAuth2Client();

  // Get the access token metadata from /debug_token
  $tokenMetadata = $oAuth2Client->debugToken($accessToken);

  // Validation Of APP ID
  try{ $tokenMetadata->validateAppId($fb_config->app_id); }
  catch(Exception $e){ return array(
    'status'      => false,
    'description' => 'App ID not validated!'
  ); }

  $tokenMetadata->validateExpiration();

  // Exchanges a short-lived access token for a long-lived one
  if(!$accessToken->isLongLived()){
    try{
      $accessToken = (string)$oAuth2Client->getLongLivedAccessToken($accessToken);
    } catch(Exception $e){ return array(
      'status'      => false,
      'description' => 'Access token exchange failed!'
    ); }
  }

  // Set in session
  $_SESSION['fb_access_token'] = $accessToken;

  // Facebook Query Object
  try{ $response = $fb_obj->get('/me?fields=id,name,email', $accessToken); }
  catch(Exception $e){ return array(
    'status'      => false,
    'description' => 'Facebook query user data failed!'
  ); }

  // Get user
  $user = $response->getGraphUser();

  return array(
    'status'        => true,
    'description'   => 'Success',
    'first_name'    => $user['first_name'],
    'last_name'     => $user['last_name'],
    'email'         => $user['email'],
    'gender'        => $user['gender'],
    'picture'       => $user['picture']
  );
}
