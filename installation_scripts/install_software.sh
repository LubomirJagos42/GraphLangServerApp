#!/bin/bash
#author: Lubomir Jagos
#description: Check if software to run graphlang is installed
#

#echo "--> Check g++ version (because of debugging using JSON format)"
#TODO
#

#echo "--> Check python version installed"
#TODO
#

#update repository sw list
sudo apt update

#install vim, it seems to me that vi editor has some problem using over ssh in msys2 on windows or what, vim is ok
echo "--> Check if vim editor installed"
sudo apt install -y vim

#echo "--> Check if MaraDB installed"
echo "--> Check if MariaDB installed"
if command -v mariadb >/dev/null 2>&1; then
	echo "----> MariaDB installed - OK"
else
	echo "----> MariaDB installed - FAIL"
	sudo apt install -y mariadb-server

        # Configure MariaDB root authentication for web app access (fix mysqli auth error)
        echo "----> Configuring MariaDB root user authentication..."
        sudo mariadb -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password; SET PASSWORD FOR 'root'@'localhost' = PASSWORD('root'); FLUSH PRIVILEGES;"
fi

echo "--> Check Apache server installed"
if command -v apache2 >/dev/null 2>&1; then
	echo "----> Apache server installed - OK"
else
	echo "----> Apache server installed - FAIL"
	sudo apt install -y apache2
fi
sudo systemctl enable apache2
sudo systemctl start apache2

echo "--> Check if PHP installed"
if command -v php >/dev/null 2>&1; then
	echo "----> PHP installed - OK"
else
	echo "----> PHP installed - FAIL"
	sudo apt install -y php
fi

echo "--> Check if PHP MySQL extension installed (required for DB connectivity)"
php -m 2>/dev/null | grep -q mysqli || sudo apt install -y php-mysql

echo "--> Check if PHP mbstring extension installed (required for phpMyAdmin)"
php -m 2>/dev/null | grep -q mbstring || sudo apt install -y php-mbstring

echo "--> Installing phpmyadmin"
sudo apt install -y phpmyadmin

#enable phpmyadmin making symbolic link in www/hhtml foledr
sudo ln -s /etc/phpmyadmin/apache.conf /etc/apache2/conf-available/phpmyadmin.conf
sudo a2enconf phpmyadmin
#sudo systemctl restart apache2


echo "--> Check if FTP server installed"
if systemctl is-active --quiet vsftpd; then
	echo "----> FTP server (vsftpd) installed - OK"
else
	echo "----> FTP server (vsftpd) not installed - FAIL"
	sudo apt install -y vsftpd
fi
#
#Now need to configure vsftpd server accroding to url: https://phoenixnap.com/kb/raspberry-pi-ftp-server
#Now need to configure vsftpd server according to url: https://phoenixnap.com/kb/raspberry-pi-ftp-server
#  sudo nano /etc/vsftpd.conf
#  uncomment:
#    write_enable=YES
#    local_umask=022
#    chroot_local_user=YES
#  change:
#    anonymous_enable=YES -> anonymous_enable=NO
#  add following lines to the end:
#    user_sub_token=$USER
#    local_root=/home/$USER/FTP
#
#Create FTP directory:
#  mkdir -p /home/[user]/FTP/[subdirectory_name]
#modify permissions:
#  chmod a-w /home/[user]/FTP
#
#Restart vsftpd daemon:
#  sudo service vsftpd restart
#

echo "--> Check if ZeroMQ dev C++ libraries  installed"
sudo apt install -y libzmq3-dev
sudo apt install -y python3-zmq

#echo "--> Check if Flatbuffers dev C++ libraries  installed"
sudo apt install -y flatbuffers-compiler flatbuffers-compiler-dev libflatbuffers-dev
#
## This install flatbuffer from git
##   - it takes ages t compile on RaspiZero appr. 2hrs
##   - it takes ages to compile on RaspiZero appr. 2hrs
#
#sudo apt install cmake
#mkdir ~/Downloads
#cd ~/Downloads
#git clone https://github.com/google/flatbuffers.git
#cd flatbuffers
#cmake -G "Unix Makefiles"
#make -j

echo "--> Check if Arduino IDE  installed"
#this will install IDE version but RaspiZero has no X11
#sudo apt install arduino
curl -fsSL https://raw.githubusercontent.com/arduino/arduino-cli/master/install.sh | BINDIR=~/Arduino sh

echo "--> Install libusb-1-0-0-dev for upload compiled .hex files to arduino devices"
sudo apt install -y libusb-1.0-0-dev


# Install Platformio-CLI - for programming embedded HW
#     - src url: https://docs.platformio.org/en/latest/core/installation/methods/installer-script.html#super-quick-macos-linux
#
echo "--> Check if Platformio installed"
if command -v pio >/dev/null 2>&1; then
	echo "----> Platformio installed - OK"
else
	echo "----> Platformio not installed - FAIL"
        curl -fsSL -o get-platformio.py https://raw.githubusercontent.com/platformio/platformio-core-installer/master/get-platformio.py
        python3 get-platformio.py
        export PATH=$PATH:$HOME/.platformio/penv/bin
        echo "\n\n" >> ~/.bashrc
        echo "#add Platformio bin to path" >> ~/.bashrc
        echo "export PATH=$PATH:$HOME/.platformio/penv/bin" >> ~/.bashrc

        echo "------> Platformio create project directory in home dir - ~/platformio_projects"
        mkdir ~/platformio_projects
        cd ~/platformio_projects

        echo "------> Platformio create example project"
        mkdir example_1_arduino
        mkdir example_2_esp
        mkdir example_2_esp8266
        mkdir example_3_raspi
        mkdir example_4_stm
        mkdir example_5_esp32

        # Create default project for Arduino Nano
        #   - board id identified from command "pio boards arduino" (parameter arduino doesn't seems to be working, boards id is in first column)
        #   - this will install toolchain to project dir??
        cd ~/platformio_projects/example_1_arduino
        pio project init --board nanoatmega328

        cd ~/platformio_projects/example_2_esp8266
        pio project init --board nodemcuv2

        cd ~/platformio_projects/example_3_raspi
        pio project init --board pico

        cd ~/platformio_projects/example_4_stm
        pio project init --board nucleo_g070rb

        cd ~/platformio_projects/example_5_esp32
        pio project init --board node32s
fi

echo "--> Check if avr-gdb installed"
if command -v avr-gdb >/dev/null 2>&1; then
	echo "----> avr-gdb installed - OK"
else
        sudo apt install -y gdb-avr
fi


# TODO: Mysql need to be properly set to use root user with root password!!!!
#
# This is how to purge mysql:
#   > sudo systemctl stop mariadb
#
#   > sudo apt purge mariadb-server mariadb-client mariadb-common
#   > sudo apt autoremove --purge
#
#   > sudo apt update
#   > sudo apt install mariadb-server mariadb-client # Ubuntu/Debian
#
#   > sudo systemctl start mariadb
#   > sudo mysql_secure_installation
#       - answer questions, root with password, no secure through unix_sockets
#

# Change php upload max file size for apache: sudo nano /etc/php/8.2/apache2/php.ini
#   - edit max_upload... memory_limit....
#     memory_limit = 1500M
#     post_max_size = 1500M
#     upload_max_filesize = 1500M
#
#phpDir=(/etc/php/*)
#phpDir=${phpDir[0]}
#phpConfigFile=$phpDir/apache2/php.ini
#sudo sed -ir "s/^memory_limit = .*/memory_limit = 300M/" $phpConfigFile
#sudo sed -ir "s/^memory_limit = .*/memory_limit = 512M/" $phpConfigFile
#sudo sed -ir "s/^post_max_size = .*/post_max_size = 1500M/" $phpConfigFile
#sudo sed -ir "s/^upload_max_filesize = .*/upload_max_filesize = 1500M/" $phpConfigFile
# Find the PHP config directory dynamically
phpDir=(/etc/php/*)

# Check if the directory actually exists and isn't just the literal glob string
if [[ -d "${phpDir[0]}" ]]; then
    phpConfigFile="${phpDir[0]}/apache2/php.ini"

    if [[ -f "$phpConfigFile" ]]; then
        sudo sed -i "s/^memory_limit = .*/memory_limit = 300M/" "$phpConfigFile"
        sudo sed -i "s/^memory_limit = .*/memory_limit = 512M/" "$phpConfigFile" # (Note: This overwrites the 300M above)
        sudo sed -i "s/^post_max_size = .*/post_max_size = 1500M/" "$phpConfigFile"
        sudo sed -i "s/^upload_max_filesize = .*/upload_max_filesize = 1500M/" "$phpConfigFile"
        echo "PHP configuration updated successfully."
    else
        echo "Warning: apache2/php.ini not found at $phpConfigFile (is libapache2-mod-php installed?)"
    fi
else
    echo "Warning: No PHP installation found in /etc/php/"
fi

# TODO: Python wheels installation to virtual environment and running it from php
#
#
sudo apt install -y python3-venv
cd /var/www/html/GraphLangServerApp
python3 -m venv graphlang_env
./graphlang_env/bin/pip3 install ./GraphLang/0v1/_python_wheels/*.whl
./graphlang_env/bin/pip3 install websockets
./graphlang_env/bin/pip3 install psutils
./graphlang_env/bin/pip3 install psutil


if [[ $(uname -m) =~ ^aarch64$ ]]; then   #compare string using regular expression
   #
   # commands for raspi4
   #
   # For RaspberryPi4 increase swap file size - needed for compile code fore ESP32 too memory intensive
   # For RaspberryPi4 increase swap file size - needed for compile code for ESP32 too memory intensive
   #   - help forum: https://forums.raspberrypi.com/viewtopic.php?t=46472
   #
   sudo sed -ir "s/^CONF_SWAPSIZE.*/CONF_SWAPSIZE=1512/" "/etc/dphys-swapfile"

   # For Raspi4 to make phpmyadmin running according to: https://forums.raspberrypi.com/viewtopic.php?t=354480
   #
   # add: Include /etc/phpmyadmin/apache.conf
   # to:  /etc/apache2/apache2.conf
   echo -e "\n#Added to enable phpmyadmin in apache\n" >> /etc/apache2/apache2.conf
   echo -e "Include /etc/phpmyadmin/apache.conf\n" >> /etc/apache2/apache2.conf
   if ! grep -q "phpmyadmin" /etc/apache2/apache2.conf 2>/dev/null; then
       echo -e "\n#Added to enable phpmyadmin in apache\n" >> /etc/apache2/apache2.conf
       echo -e "Include /etc/phpmyadmin/apache.conf\n" >> /etc/apache2/apache2.conf
   fi

   # Enable mod_rewrite for URL routing
   sudo a2enmod rewrite

   # Set correct ownership and permissions for the web app
   sudo chown -R www-data:www-data /var/www/html/GraphLangServerApp
   sudo chmod -R 755 /var/www/html/GraphLangServerApp
   sudo chmod -R 777 /var/www/html/GraphLangServerApp/_temp

   # Restart apache to apply all config changes
   sudo systemctl restart apache2

   #
   # vsftpd needs to be configured on raspi4 to be able to write to directory using ftp from remote
   #
   echo -e "\n" >> /etc/vsftpd.conf
   echo -e "local_root=/var/www/html\n" >> /etc/vsftpd.conf
   echo -e "write_enable=true\n" >> /etc/vsftpd.conf
   echo "" >> /etc/vsftpd.conf
   echo "local_root=/var/www/html" >> /etc/vsftpd.conf
   echo "write_enable=YES" >> /etc/vsftpd.conf
   echo "allow_writeable_chroot=YES" >> /etc/vsftpd.conf

   # Platformio set udev rules to be able use stlink boards connected through usb
   #     source: https://docs.platformio.org/en/latest/core/installation/udev-rules.html#platformio-udev-rules
   #
   curl -fsSL https://raw.githubusercontent.com/platformio/platformio-core/develop/platformio/assets/system/99-platformio-udev.rules | sudo tee /etc/udev/rules.d/99-platformio-udev.rules

   #sudo service udev restart
   sudo udevadm control --reload-rules
   sudo udevadm trigger

   # Raspi4 seems to be disconnected from Wifi probably due power saving management, trying this to turn off it
   #
   #
   # Disable power save and set static IP
   sudo iw dev wlan0 set power_save off
   #add line to /etc/rc.local to disable power_save mode
   echo '/sbin/iw dev wlan0 set power_save off' | sudo tee -a /etc/rc.local

fi

if [[ $(uname -m) =~ ^(armv6l|armv7l)$ ]]; then
   #
   # commands for raspi zero
   #
   :
fi


sudo apt install cmake


cd ~
python3 -m venv graphlang_python_env
source graphlang_python_env/bin/activate
cd /var/www/html/GraphLangServerApp/GraphLang/0v1/_python_wheels
pip install pygdbmi-0.11.0.0-py3-none-any.whl
pip install remote_pdb-2.1.0-py2.py3-none-any.whl
pip install websockets
echo 'source ~/graphlang_python_env/bin/activate' >> ~/.bashrc


echo "--> INSTALLATION SW PART ENDS"
