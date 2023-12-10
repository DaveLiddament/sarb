
Vagrant.configure("2") do |config|
    config.vm.provider :virtualbox do |v|
        v.name = "sarb"
        v.customize [
            "modifyvm", :id,
            "--name", v.name,
            "--memory", 2048,
            "--natdnshostresolver1", "on",
            "--cpus", 2,
        ]
    end

    config.vbguest.installer_options = { running_kernel_modules: ["vboxguest"] }

    config.vm.hostname = "sarb"

    config.vm.box = "debian/buster64"
    config.vm.box_version = "~> 10.20220718.1"

    config.vm.network :private_network, ip: "192.168.56.99"
    config.ssh.forward_agent = true

    config.vm.synced_folder ".", "/vagrant", type: "nfs", nfs_udp: false, mount_options: ["actimeo=2", "nolock"]


    # Provision box with php and composer
    config.vm.provision "shell", inline: <<-SHELL
      apt-get update
      apt-get install -y ca-certificates apt-transport-https git zip vim curl
      wget -q https://packages.sury.org/php/apt.gpg -O- | apt-key add -
      echo "deb https://packages.sury.org/php/ buster main" | tee /etc/apt/sources.list.d/php.list
      apt-get update
      apt-get install -y php8.0-cli php8.0-xml php8.0-mbstring  php8.0-dev php8.0-curl
      apt-get install -y php8.1-cli php8.1-xml php8.1-mbstring  php8.1-dev php8.1-curl
      apt-get install -y php8.2-cli php8.2-xml php8.2-mbstring  php8.2-dev php8.2-curl
      apt-get install -y php8.3-cli php8.3-xml php8.3-mbstring  php8.3-dev php8.3-curl
      curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
      sudo -iu vagrant /usr/local/bin/composer --working-dir=/vagrant install
    SHELL
end

