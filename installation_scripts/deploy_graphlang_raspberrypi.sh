#!/bin/bash

#author: Lubomir Jagos
#description: This script will connect to raspberry pi over ssh, install needed SW to run MariaDB, php and deploy sql database.

#constants
SSH_DEVICE_IP=192.142.0.128
SSH_USER=pi
SSH_PASSWORD=raspberry
SSH_REMOTE_DIR=/tmp/__graphlang_experiment_2
SSH_REMOTE_DB_FILENAME=graphlang_local_develop.sql
SSH_REMOTE_DB_FILEPATH=$SSH_REMOTE_DIR/$SSH_REMOTE_DB_FILENAME
LOCAL_SQL_DB_FILE_PATH=../_sql_db_tables_definition/graphlang_local_develop.sql
MARIADB_USER=root
MARIADB_PASSWORD=root
GRAPHLANG_DB_NAME=graphlang_local_develop
SSH_REMOTE_GRAPHLANG_FOLDER=/var/www/html/GraphLangServerApp
SSH_REMOTE_GRAPHLANG_FOLDER_USER_PROJECTS_OUTPUT=$SSH_REMOTE_GRAPHLANG_FOLDER/_temp

#########################################################################################################################################
# Check script input arguments and set flags for different installation parts
#########################################################################################################################################
DEPLOY_DB=true
DO_COPY_FILES=true

# parse arguments
for arg in "$@"; do
  case $arg in
    --no-db-deploy)
      DEPLOY_DB=false
      shift # remove argument
      ;;
    --no-file-copy)
      DO_COPY_FILES=false
      shift # remove argument
      ;;
    --help | -h)
	  echo
	  echo "Available arguments:"
	  echo -e "\t--no-db-deploy ...skip deploy of SQL file to DB\n"
	  echo -e "\t--no-file-copy ...skip copying files to target (no .php, .html copied to target)"
      exit 0
      ;;
  esac
done

#installation
echo "--> Start installation"

#########################################################################################################################################
# Check if raspberrypi available using SSH
#########################################################################################################################################

echo "--> Checking if possible to connect to target over SSH"
status=$(sshpass -p $SSH_PASSWORD ssh -o ConnectTimeout=3 $SSH_USER@$SSH_DEVICE_IP echo ok 2>&1)
if [[ $status == ok ]]; then
	echo "--> SSH connection established, continue in installation"
else
	echo "Cannot connect to $HOST via SSH. Exiting..."
	exit 1
fi

#########################################################################################################################################
# Erase graphlang folder and create new one which is empty, copy sql dump to server and deploy it to MariaDB
#########################################################################################################################################

echo "--> Creating folder for db file on remote over ssh"
sshpass -p $SSH_PASSWORD ssh $SSH_USER@$SSH_DEVICE_IP << EOF
	rm -R $SSH_REMOTE_DIR
	mkdir $SSH_REMOTE_DIR

	echo "Removing graphlang web folder"
	echo "$SSH_PASSWORD" | sudo -S rm -R $SSH_REMOTE_GRAPHLANG_FOLDER

	echo "Creating graphlang web folder"
        sudo mkdir $SSH_REMOTE_GRAPHLANG_FOLDER
        sudo chmod 777 $SSH_REMOTE_GRAPHLANG_FOLDER
EOF

echo "--> Checking SW if installed on raspi"
cat install_software.sh | sshpass -p $SSH_PASSWORD ssh $SSH_USER@$SSH_DEVICE_IP

if [ "$DEPLOY_DB" = true ]; then
	echo "--> Copying SQL DB file to remote device"
	sshpass -p $SSH_PASSWORD scp $LOCAL_SQL_DB_FILE_PATH $SSH_USER@$SSH_DEVICE_IP:$SSH_REMOTE_DIR/$SSH_REMOTE_DB_FILENAME

	echo "--> Deploy .db file to MariaDB server"
	sshpass -p $SSH_PASSWORD ssh $SSH_USER@$SSH_DEVICE_IP << EOF
		echo $SSH_PASSWORD | sudo -S mysql -u$MARIADB_USER -p$MARIADB_PASSWORD -e "drop database if exists $GRAPHLANG_DB_NAME;create database $GRAPHLANG_DB_NAME;"
		echo $SSH_PASSWORD | sudo -S mysql -u$MARIADB_USER -p$MARIADB_PASSWORD $GRAPHLANG_DB_NAME < $SSH_REMOTE_DB_FILEPATH
		echo $SSH_PASSWORD | sudo -S mysql -u$MARIADB_USER -p$MARIADB_PASSWORD -e "show databases;use $GRAPHLANG_DB_NAME;show tables;select * from active_users;"
EOF

else
  echo "--> Skipping database deployment (--no-db-deploy used)."
fi

#########################################################################################################################################
# Copying graphlang files to server
#########################################################################################################################################

if [ "$DO_COPY_FILES" = true ]; then
	#scp -v ...show progress of Copying
	#    -r ...copy directory recursively
	echo "--> Copying GraphLang files to remote"
	# 1. way using scp
	#sshpass -p $SSH_PASSWORD scp -v -r ../../GraphLangServerApp $SSH_USER@$SSH_DEVICE_IP:$SSH_REMOTE_GRAPHLANG_FOLDER/..
	# 2. way using rsync
	sshpass -p $SSH_PASSWORD rsync -av --info=progress2 --stats -e 'ssh' ../../GraphLangServerApp $SSH_USER@$SSH_DEVICE_IP:$SSH_REMOTE_GRAPHLANG_FOLDER/..
else
  echo "--> Skipping copying files to server (--no-file-copy used)."
fi


#########################################################################################################################################
# Set folder for user projects for compilation to be accesible to write
#########################################################################################################################################

echo "--> Set user projects output dir to 777 to be able for php write into it"
sshpass -p $SSH_PASSWORD ssh $SSH_USER@$SSH_DEVICE_IP "sudo chmod -R 777 $SSH_REMOTE_GRAPHLANG_FOLDER_USER_PROJECTS_OUTPUT"



# For Raspi4 to make phpmyadmin running according to: https://forums.raspberrypi.com/viewtopic.php?t=354480
#
# add: Include /etc/phpmyadmin/apache.conf
# to:  /etc/apache2/apache2.conf


# Mysql need to be properly set to use root user with root password!!!!
#
#

# Change php upload max file size for apache: sudo nano /etc/php/8.2/apache2/php.ini
#   - edit max_upload... memory_limit....
#     memory_limit = 1500M
#     post_max_size = 1500M
#     upload_max_filesize = 1500M
#
#

# Python wheels installation to virtual environment and running it from php
#
#


echo "--> Installation finished"
