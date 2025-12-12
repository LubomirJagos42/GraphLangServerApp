#author: Lubomir Jagos
#description: Check if software to run graphlang is installed
#

#echo "--> Check g++ version (because of debugging using JSON format)"
#TODO
#

#echo "--> Check python version installed"
#TODO
#

#install vim, it seems to me that vi editor has some problem using over ssh in msys2 on windows or what, vim is ok
echo "--> Check if vim editor installed"
sudo apt install -y vim

echo "--> Check if MaraDB installed"
if command -v mariadb >&2; then
	echo "----> MariaDB installed - OK"
else
	echo "----> MariaDB installed - FAIL"
	sudo apt install mariadb-server
fi

echo "--> Check Apache server installed"
if command -v apache2 >&2; then
	echo "----> Apache server installed - OK"
else
	echo "----> Apache server installed - FAIL"
	sudo apt install apache2
fi

echo "--> Check if PHP installed"
if command -v php >&2; then
	echo "----> PHP installed - OK"
else
	echo "----> PHP installed - FAIL"
	sudo apt install php
fi

echo "--> Installing phpmyadmin"
sudo apt install -y phpmyadmin

echo "--> Check if FTP server installed"
if systemctl is-active --quiet vsftpd; then
	echo "----> FTP server (vsftpd) installed - OK"
else
	echo "----> FTP server (vsftpd) not installed - FAIL"
	sudo apt install vsftpd
fi
#
#Now need to configure vsftpd server accroding to url: https://phoenixnap.com/kb/raspberry-pi-ftp-server
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
sudo apt install libusb-1.0-0-dev


# Install Platformio-CLI - for programming embedded HW
#     - src url: https://docs.platformio.org/en/latest/core/installation/methods/installer-script.html#super-quick-macos-linux
#
echo "--> Check if Platformio installed"
if command -v pio >&2; then
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
        mkdir example_3_raspi
        mkdir example_4_stm

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
if command -v avr-gdb >&2; then
	echo "----> avr-gdb installed - OK"
else
        sudo apt install gdb-avr
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
phpDir=(/etc/php/*)
phpDir=${phpDir[0]}
phpConfigFile=$phpDir/apache2/php.ini
sudo sed -ir "s/^memory_limit = .*/memory_limit = 300M/" $phpConfigFile
sudo sed -ir "s/^post_max_size = .*/post_max_size = 1500M/" $phpConfigFile
sudo sed -ir "s/^upload_max_filesize = .*/upload_max_filesize = 1500M/" $phpConfigFile

# TODO: Python wheels installation to virtual environment and running it from php
#
#
sudo apt install python3-venv
cd /var/www/html/GraphLangServerApp
python3 -m venv graphlang_env
./graphlang_env/bin/pip3 install ./GraphLang/0v1/_python_wheels/*.whl
./graphlang_env/bin/pip3 install websockets
./graphlang_env/bin/pip3 install psutils


if [[ $() == "aarch64" ]] then
   #
   # commands for raspi4
   #
   # For RaspberryPi4 increase swap file size - needed for compile code fore ESP32 too memory intensive
   #   - help forum: https://forums.raspberrypi.com/viewtopic.php?t=46472
   #
   sudo sed -ir "s/^CONF_SWAPSIZE.*/CONF_SWAPSIZE=1512/" "/etc/dphys-swapfile"

   # For Raspi4 to make phpmyadmin running according to: https://forums.raspberrypi.com/viewtopic.php?t=354480
   #
   # add: Include /etc/phpmyadmin/apache.conf
   # to:  /etc/apache2/apache2.conf
   echo -e "\n#Added to enable phpmyadmin in apache\n" >> /etc/apache2/apache2.conf
   echo -e "Include /etc/phpmyadmin/apache.conf\n" >> /etc/apache2/apache2.conf

   #
   # vsftpd needs to be configured on raspi4 to be able to write to directory using ftp from remote
   #
   echo -e "\n" >> /etc/vsftpd.conf
   echo -e "local_root=/var/www/html\n" >> /etc/vsftpd.conf
   echo -e "write_enable=true\n" >> /etc/vsftpd.conf

fi

if [[ $() == "armv6l" ]] then
   #
   # commands for raspi zero
   #
fi


