#author: Lubomir Jagos
#description: Check if software to run graphlang is installed
#

echo "--> Check if MaraDB installed"
if command -v mariadb >&2; then
	echo "----> MariaDB installed - OK"
else
	echo "----> MariaDB installed - FAIL"
	sudo apt install mariadb-server
fi

echo "--> Check if PHP installed"
if command -v php >&2; then
	echo "----> PHP installed - OK"
else
	echo "----> PHP installed - FAIL"
	sudo apt install php
fi

echo "--> Installing phpmyadmin"
sudo apt install phpmyadmin
