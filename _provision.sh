#!/bin/bash

#Provisioner for non-vagrant machines

cd /
git clone https://github.com/shaycohen/ovd.git
ln -s /ovd /vagrant
sudo bash /vagrant/provision.sh
