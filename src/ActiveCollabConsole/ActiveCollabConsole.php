<?php

/**
 * @file
 *   Provides command line options for interacting with activeCollab API.
 */

namespace ActiveCollabConsole;
use ActiveCollabApi\ActiveCollabApi;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

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
  }

  /**
   * Check to see if config file is present and for other requirements.
   *
   * @return true if all requirements pass, false otherwise.
   */
  public function checkRequirements()
  {
    $currentUser = get_current_user();
    $fs = new Filesystem();
    $configFile = '/Users/' . $currentUser . '/.active_collab';

    if (!$fs->exists($configFile)) {
      print "Please create a ~/.active_collab file.\n";
      return false;
    }

    $yaml = new Parser();

    try {
        $file = $yaml->parse(file_get_contents($configFile));
        if (!isset($file['ac_url']) || !$file['ac_url']) {
          print "Please specify a value for ac_url in your config file!\n";
        } else {
          parent::setAPIUrl($file['ac_url']);
        }
        if (!isset($file['ac_token']) || !$file['ac_token']) {
          print "Please specify a value for ac_token in your config file!\n";
        } else {
          parent::setKey($file['ac_token']);
        }
        if (!isset($file['ac_url']) || !isset($file['ac_token']) || !$file['ac_url'] || !$file['ac_token']) {
          return false;
        }
    } catch (ParseException $e) {
        printf("Unable to parse the YAML string: %s", $e->getMessage());
        return false;
    }

    return true;
  }

}
