#!/bin/bash

# configure system to use networkradius.com binary packages these are newer than those supplied by ubuntu

# determine distribution and version more logic needed so we dont create multiple entries if the script is re run 

if lsb_release -d | awk -F"\t" '{print $2}' | grep -q Ubuntu
    then
            if lsb_release -d | awk -F"\t" '{print $2}' | grep -q 20.04
                then
                    echo "found ubuntu 20.04"
                    echo "adding (deb https://packages.networkradius.com/releases/ubuntu-focal focal main) to (/etc/apt/sources.list)"
                    echo 'deb https://packages.networkradius.com/releases/ubuntu-focal focal main' >> /etc/apt/sources.list
            elif lsb_release -d | awk -F"\t" '{print $2}' | grep -q 18.04
                then
                    echo "found ubuntu 18.04"
                    echo "adding ( https://packages.networkradius.com/releases/ubuntu-bionic bionic main) to (/etc/apt/sources.list)"
                    echo 'deb https://packages.networkradius.com/releases/ubuntu-bionic bionic main' >> /etc/apt/sources.list
            fi
elif lsb_release -d | awk -F"\t" '{print $2}' | grep -q Debian
    then
            if lsb_release -d | awk -F"\t" '{print $2}' | grep -q stretch
                then
                    echo "found Debian stretch"
                    echo "adding (deb https://packages.networkradius.com/releases/debian-stretch stretch main) to (/etc/apt/sources.list)"
                    echo 'deb https://packages.networkradius.com/releases/debian-stretch stretch main' >> /etc/apt/sources.list
            elif lsb_release -d | awk -F"\t" '{print $2}' | grep -q buster
                then
                    echo "found Debian buster"
                    echo "adding (deb https://packages.networkradius.com/releases/debian-buster buster main) to (/etc/apt/sources.list)"
                    echo 'deb https://packages.networkradius.com/releases/debian-buster buster main' >> /etc/apt/sources.list
            fi
fi

# import networkradius.com pgp key

sudo apt-key adv --keyserver keys.gnupg.net --recv-key 0x41382202

sudo apt-get update --yes
sudo apt-get upgrade --yes
sudo apt-get install --yes php-cli php-mbstring php-mysql unzip
sudo apt-get install --yes mariadb-server mariadb-client
sudo apt-get install --yes freeradius freeradius-common freeradius-utils freeradius-mysql

### test for an existing swap device or file before we create one  ###

if cat /etc/fstab | grep -q swap
        then
                echo "swap was found in fstab"
                if free | awk '/^Swap:/ {exit !$2}'
                        then
                                echo "swap is enabled"
                        else
                                echo "swap was not enabled we will enable it now"
                                swapon -a
                fi
        else
            echo "no swap found in fstab we will now create and enable a swapfile"
            /usr/bin/fallocate -l 4G /swapfile
            /bin/chmod 600 /swapfile
            /sbin/mkswap /swapfile
            /sbin/swapon /swapfile
            echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

/sbin/sysctl vm.swappiness=10
echo 'vm.swappiness=10' >> /etc/sysctl.conf
/sbin/sysctl vm.vfs_cache_pressure=50
echo 'vm.vfs_cache_pressure=50' >> /etc/sysctl.c
echo ""
echo "if this is your first run it is now time to secure the mysql installation this will allow you to create the root mysql passowrd needed later on "
echo ""
sudo /usr/bin/mysql_secure_installation
echo '# PUT YOUR MYSQL PASSWORD YOU JUST ENTERED BELOW, THEN PRESS CTRL+X and Y to SAVE CHANGES' >> ~/freeradius3-genie/.env
echo 'MYSQL_PASSWORD=changeme' >> ~/freeradius3-genie/.env

nano ~/freeradius3-genie/.env
# eof
