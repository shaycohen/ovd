#!/bin/bash
NAME='ovd'
PKGS="docker.io vim curl imagemagick"
for PKG in $PKGS
do
	dpkg -V $PKG || { 
		apt-get -y update
		apt-get -y install $PKGS
	}
done
systemctl restart docker
DBUSER="root"
DBPASS="PASS"


MYSQL_IMG="mysql:5.7.18"
PHP_IMG="php:5-apache"
MYADMIN_IMG="phpmyadmin/phpmyadmin:latest"
for IMG in $MYSQL_IMG $PHP_IMG $MYADMIN_IMG
do
	IMG_FN=$(echo $IMG | sed -e 's/:/IMG_FN_SEMI_COLON/')
	echo "Verifying / Loading / Pulling image $IMG, this might take a few minutes"
	[[ -e /vagrant/docker-images/${IMG_FN}.tar ]] && { 
		sudo docker images | sed -e 's/\s\s*/:/; s/\s.*//' | egrep -q "^$IMG" || sudo docker load -i /vagrant/docker-images/${IMG_FN}.tar || exit 1
	} || { 
		sudo docker images | sed -e 's/\s\s*/:/; s/\s.*//' | egrep -q "^$IMG" || sudo docker pull ${IMG}
		echo "Archiving docker image $IMG, this might take a few minutes"
		[[ -e /vagrant/docker-images/$(dirname $IMG_FN) ]] || mkdir -p /vagrant/docker-images/$(dirname $IMG_FN)
		TMPF=$(mktemp)
		docker save -o $TMPF $IMG
		mv $TMPF "/vagrant/docker-images/${IMG_FN}.tar"
	}
done


for CONTAINER in $NAME-httpd $NAME-db $NAME-myadmin
do
	docker ps -a --format='{{.Names}}' | egrep "^$CONTAINER$" && { 
		docker rm -f $CONTAINER
		echo "removing container $CONTAINER"
	}
done

docker run -d --name=$NAME-db -e MYSQL_ROOT_PASSWORD=$DBPASS $MYSQL_IMG

COUNT=0
while : 
do
	docker run --rm --link $NAME-db:db $MYSQL_IMG \
		sh -c 'exec echo "select 1" |  mysql -h"$DB_PORT_3306_TCP_ADDR" -P"$DB_PORT_3306_TCP_PORT" -uroot -p"$DB_ENV_MYSQL_ROOT_PASSWORD" mysql'
	[[ $? == '0' ]] && break
	echo 'waiting for mysqld to start'
	sleep 6
	(( COUNT++ ))
	[[ $COUNT -gt 10 ]] && { 
		echo "mysqld did not start after 60 seconds, exiting"
		exit 60
	}
done

docker run --name $NAME-myadmin -d --link $NAME-db:db -p 0.0.0.0:8080:80 $MYADMIN_IMG

	docker run --rm --link $NAME-db:db $MYSQL_IMG \
		sh -c 'exec echo "CREATE DATABASE '$NAME'" |  mysql -h"$DB_PORT_3306_TCP_ADDR" -P"$DB_PORT_3306_TCP_PORT" -uroot -p"$DB_ENV_MYSQL_ROOT_PASSWORD" mysql' || exit $?

docker run --rm \
	-v /vagrant/sql_seed/:/tmp/sql_seed \
	--link $NAME-db:db $MYSQL_IMG \
	sh -c 'exec mysql -h"$DB_PORT_3306_TCP_ADDR" -P"$DB_PORT_3306_TCP_PORT" -uroot -p"$DB_ENV_MYSQL_ROOT_PASSWORD" '$NAME' < /tmp/sql_seed/'$NAME'.seed.sql' || exit $?

docker run -d \
	--name=$NAME-httpd \
	--link=$NAME-db:db \
	-v /vagrant/php.ini:/usr/local/etc/php/php.ini \
	-e "DBHOST=db" \
	-e "DBUSER=$DBUSER" \
	-e "DBPASS=$DBPASS" \
	-v /vagrant/html:/var/www/html \
	-p 0.0.0.0:80:80 -p 443:443 \
	$PHP_IMG
docker exec $NAME-httpd sh -c 'exec docker-php-ext-install pdo pdo_mysql'
docker exec $NAME-httpd sh -c 'exec apache2ctl -k restart'
docker exec $NAME-httpd sh -c 'exec apt-get update'
docker exec $NAME-httpd sh -c 'exec apt-get install -y imagemagick'
true
