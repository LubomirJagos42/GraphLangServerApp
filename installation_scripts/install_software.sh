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
if command -v php >&2; then
	echo "----> FTP server installed - OK"
else
	echo "----> FTP server installed - FAIL"
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
