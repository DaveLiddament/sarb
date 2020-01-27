Vagrant.configure(2) do |config|

  config.vm.network :private_network, ip: "192.168.210.99"
  config.vm.synced_folder ".", "/vagrant", type: "nfs", nfs_udp: false, mount_options: ["actimeo=2", "nolock"]

  config.vm.hostname = "sarb"


  config.vm.box = "debian/stretch64"

  config.ssh.forward_agent = true

  config.vm.provider "virtualbox" do |vb|
     vb.memory = "2048"
  end

  # Provision box with php and composer
  config.vm.provision "shell", inline: <<-SHELL
    apt-get update
    apt-get install -y ca-certificates apt-transport-https git zip vim curl
    wget -q https://packages.sury.org/php/apt.gpg -O- | apt-key add -
    echo "deb https://packages.sury.org/php/ stretch main" | tee /etc/apt/sources.list.d/php.list
    apt-get update
    apt-get install -y php7.2-cli php7.2-xml php7.2-mbstring  php7.2-dev php-pear
    pecl install ast-1.0.1
    echo "extension=ast.so" > /etc/php/7.2/cli/conf.d/30-ast.ini
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    sudo -iu vagrant /usr/local/bin/composer --working-dir=/vagrant install
  SHELL
end

