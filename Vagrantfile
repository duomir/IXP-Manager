# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
  config.vm.box = "ubuntu/bionic64"

  config.vm.network "forwarded_port", guest: 80, host: 8088

  config.vm.synced_folder ".", "/vagrant/", id: "vagrant-root0",
    owner: "ubuntu"

  config.vm.synced_folder "./storage", "/vagrant/storage", id: "vagrant-root1",
      owner: "ubuntu",
      group: "www-data",
      mount_options: ["dmode=775,fmode=664"]

  config.vm.synced_folder "./database/Proxies", "/vagrant/database/Proxies", id: "vagrant-root3",
      owner: "ubuntu",
      group: "www-data",
      mount_options: ["dmode=775,fmode=664"]

  config.vm.synced_folder "./bootstrap/cache", "/vagrant/bootstrap/cache", id: "vagrant-root4",
      owner: "ubuntu",
      group: "www-data",
      mount_options: ["dmode=775,fmode=664"]


  config.vm.provider "virtualbox" do |vb|
    vb.memory = "1536"
  end

  config.vm.provision :shell, path: "bootstrap.sh"
end
