Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/bionic64"

  unless Vagrant.has_plugin?("vagrant_hostupdater")
    config.vm.hostname = "mental.www"
  end

  config.vm.network "private_network", ip: "192.168.179.2"
  config.vm.network "forwarded_port", guest: 80, host: 8888

  config.vm.synced_folder ".", "/srv/www", :nfs => true

  config.vm.provision "shell", inline: "which python || sudo apt -y install python"

  config.vm.provision "ansible" do |ansible|
      ansible.playbook = "ansible/playbook.yml"
  end

end
