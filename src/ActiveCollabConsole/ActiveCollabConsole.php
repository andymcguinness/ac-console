<?php

/**
 * @file
 *   Provides command line options for interacting with activeCollab API.
 */

namespace ActiveCollabConsole;
use ActiveCollabApi\ActiveCollabApi;

/**
 * Provides methods for interacting with the ActiveCollabApi.
 */
class ActiveCollabConsole extends ActiveCollabApi
{

  const VERSION = '0.1';

  /**
   * Constructor
   */
  public function __construct()
  {
    if (!$this->checkRequirements()) {
      return false;
    }
    $currentUser = get_current_user();
    $config = parse_ini_file('/Users/' . $currentUser . '/.active_collab');
    $this->projects = serialize($config['projects']);
    parent::setKey($config['ac_token']);
    parent::setAPIUrl($config['ac_url']);

  }

  /**
   * Check to see if config file is present and for other requirements.
   *
   * @return true if all requirements pass, false otherwise.
   */
  public function checkRequirements()
  {
    $currentUser = get_current_user();

    if (!file_exists('/Users/' . $currentUser . '/.active_collab')) {
      print "Please create a ~/.active_collab file.\n";

      return false;
    }
    $file = parse_ini_file('/Users/' . $currentUser . '/.active_collab');
    if (!is_array($file)) {
      print "Could not parse config file.";

      return false;
    }
    if (!isset($file['ac_url']) || !$file['ac_url']) {
      print "Please specify a value for ac_url in your config file!\n";
    }
    if (!isset($file['ac_token']) || !$file['ac_token']) {
      print "Please specify a value for ac_token in your config file!\n";
    }
    if (!isset($file['ac_url']) || !isset($file['ac_token']) || !$file['ac_url'] || !$file['ac_token']) {
      return false;
    }

    return true;
  }

}
