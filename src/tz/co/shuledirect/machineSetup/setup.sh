# set-up the AMI machine with the right programs
sudo apt-get update
sudo apt-get install apache2
sudo apt-get install mysql-server libapache2-mod-auth-mysql php5-mysql
sudo apt-get install php5
sudo apt-get install emacs
sudo chown -R ubuntu /var/www
sudo ln -s /home/ubuntu/shule-internal/src/tz/co/shuledirect/api/ /var/www