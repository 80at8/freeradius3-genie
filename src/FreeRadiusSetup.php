<?php

namespace SonarSoftware\FreeRadius;

use League\CLImate\CLImate;
use RuntimeException;

class FreeRadiusSetup
{
    private $climate;
    public function __construct()
    {
        $this->climate = new CLImate;
    }

    /**
     * Configure the FreeRADIUS configuration files
     * for the networkradius.com binary packages
     */
    public function configureFreeRadiusToUseSql()
    {
        $mysqlPassword = getenv("MYSQL_PASSWORD");

        $v = `dpkg -s freeradius | grep Version`;
        $v = explode(" ",$v);
        $c = "/../conf/" . substr($v[1],0,1) . ".0/";
        $r = `apt-cache policy freeradius | grep networkradius`;
        switch ($c) {

        case "/../conf/3.0/":

            $this->climate->info("Detected FreeRADIUS version 3.x.x");
            $this->climate->lightBlue()->inline("Configuring FreeRADIUS to use the SQL database... ");
            try {
                CommandExecutor::executeCommand("/bin/cp " . __DIR__ . $c . "radiusd.conf /etc/freeradius//");
                CommandExecutor::executeCommand("/bin/cp " . __DIR__ . $c . "sql /etc/freeradius/mods-available");
                CommandExecutor::executeCommand("/bin/ln -fs /etc/freeradius/mods-available/sql -t /etc/freeradius/mods-enabled/");
                CommandExecutor::executeCommand("/bin/cp " . __DIR__ . $c . "detail_coa /etc/freeradius/mods-available");
                CommandExecutor::executeCommand("/bin/ln -fs /etc/freeradius/mods-available/detail_coa -t /etc/freeradius/mods-enabled/");
                CommandExecutor::executeCommand("/bin/cp " . __DIR__ . $c . "default /etc/freeradius/sites-available/");
                CommandExecutor::executeCommand("/bin/ln -fs /etc/freeradius/sites-available/default -t /etc/freeradius/sites-enabled/");
                CommandExecutor::executeCommand("/bin/cp " . __DIR__ . $c . "coa_relay /etc/freeradius/sites-available/");
                CommandExecutor::executeCommand("/bin/ln -fs /etc/freeradius/sites-available/coa_relay -t /etc/freeradius/sites-enabled/");
                CommandExecutor::executeCommand("/bin/sed -i 's/password = \"radpass\"/password = \"$mysqlPassword\"/g' /etc/freeradius/mods-enabled/sql");
                CommandExecutor::executeCommand("/usr/sbin/service freeradius restart");
            }
            catch (RuntimeException $e)
            {
                $this->climate->shout("FAILED!");
                $this->climate->shout($e->getMessage());
                $this->climate->shout("See /tmp/_genie_output for failure details.");
                return;
            }

            $this->climate->info("SUCCESS!");
            break;


        case "/../conf/2.0/":

            $this->climate->info("Detected FreeRADIUS version 2.x.x");

            $this->climate->lightBlue()->inline("Configuring FreeRADIUS to use the SQL database... ");
            try {
                CommandExecutor::executeCommand("/bin/cp " . __DIR__ . $c . "radiusd.conf /etc/freeradius/");
                CommandExecutor::executeCommand("/bin/cp " . __DIR__ . $c . "sql.conf/ /etc/freeradius/");
                CommandExecutor::executeCommand("/bin/cp " . __DIR__ . $c . "default /etc/freeradius/sites-available/");
                CommandExecutor::executeCommand("/bin/sed -i 's/password = \"radpass\"/password = \"$mysqlPassword\"/g' /etc/freeradius/sql.conf");
                CommandExecutor::executeCommand("/usr/sbin/service freeradius restart");
            }
            catch (RuntimeException $e)
            {
                $this->climate->shout("FAILED!");
                $this->climate->shout($e->getMessage());
                $this->climate->shout("See /tmp/_genie_output for failure details.");
                return;
            }
            break;

       }
    }
}
