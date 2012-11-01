<?php

namespace ActiveCollabConsole\Console;

use Symfony\Component\Console\Application as BaseApplication;
use ActiveCollabConsole\Console\Command\UserTasks;
use ActiveCollabConsole\ActiveCollabConsole;

/**
* @author Kosta Harlan <kostajh@gmail.com>
*/
class Application extends BaseApplication
{
    /**
* Constructor.
*/
    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('ActiveCollab Console', ActiveCollabConsole::VERSION);

        $this->add(new UserTaksCommand());
    }

    public function getLongVersion()
    {
        return parent::getLongVersion().' by <comment>Kosta Harlan</comment>';
    }
}
