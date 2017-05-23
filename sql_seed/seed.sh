#!/bin/bash
NAME='ovd'
MYSQL_IMG="mysql:5.7.18"

sudo docker run --rm \
	-v /vagrant/sql_seed/:/tmp/sql_seed \
	--link $NAME-db:db $MYSQL_IMG \
	sh -c 'exec echo "DROP DATABASE '$NAME';" | mysql -h"$DB_PORT_3306_TCP_ADDR" -P"$DB_PORT_3306_TCP_PORT" -uroot -p"$DB_ENV_MYSQL_ROOT_PASSWORD" '
sudo docker run --rm \
	-v /vagrant/sql_seed/:/tmp/sql_seed \
	--link $NAME-db:db $MYSQL_IMG \
	sh -c 'exec echo "CREATE DATABASE '$NAME';" | mysql -h"$DB_PORT_3306_TCP_ADDR" -P"$DB_PORT_3306_TCP_PORT" -uroot -p"$DB_ENV_MYSQL_ROOT_PASSWORD" '
sudo docker run --rm \
	-v /vagrant/sql_seed/:/tmp/sql_seed \
	--link $NAME-db:db $MYSQL_IMG \
	sh -c 'exec mysql -h"$DB_PORT_3306_TCP_ADDR" -P"$DB_PORT_3306_TCP_PORT" -uroot -p"$DB_ENV_MYSQL_ROOT_PASSWORD" '$NAME' < /tmp/sql_seed/'$NAME'.seed.sql'
