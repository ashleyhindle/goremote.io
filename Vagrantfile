# -*- mode: ruby -*-
# vi: set ft=ruby :
# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
require 'rbconfig'
VAGRANTFILE_API_VERSION = "2"

module OS
    def OS.windows?
        (/cygwin|mswin|mingw|bccwin|wince|emx/ =~ RUBY_PLATFORM) != nil
    end

    def OS.mac?
        (/darwin/ =~ RUBY_PLATFORM) != nil
    end

    def OS.unix?
        !OS.windows?
    end

    def OS.linux?
        OS.unix? and not OS.mac?
    end
end

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
    config.vm.box = "ubuntu/trusty64"
    config.vm.box_url = "http://cloud-images.ubuntu.com/vagrant/trusty/current/trusty-server-cloudimg-amd64-vagrant-disk1.box"
    config.vm.synced_folder ".", "/var/smelly-skeleton/", :owner => "www-data", :group => "www-data"

    config.vm.network "forwarded_port", guest: 80, host: 8888
    # Share SSH locally by default
    config.vm.network "forwarded_port", guest: 22, host: 2222, id: "ssh", auto_correct: true
	
    config.vm.provider "virtualbox" do |v|
        v.memory = 1024
    end

    if OS.windows?
        config.vm.provision "shell" do |sh|
            sh.path = "provisioning/windows.sh"
            sh.args = "provisioning/playbook.yml"
        end
    else

        config.vm.provision "ansible" do |ansible|
            ansible.playbook = "provisioning/playbook.yml"
            ansible.sudo = true
            ansible.host_key_checking = false
                 ansible.extra_vars = {
                ansible_ssh_user: 'vagrant',
                ansible_connection: 'ssh',
                ansible_ssh_args: '-o ForwardAgent=yes',
            }
            ansible.groups = {
              "vagrant" => ["default"]
            }
        end
    end
end
