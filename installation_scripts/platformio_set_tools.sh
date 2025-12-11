#        #there is some error when compile code for ESP ending "Segmentation fault" no internet there is written this is error of tool-esptool@1.413 so here is downgrade done
#        #  - seems this is not case of wrong esptool but because I used wrong board
#        #
#        #pio pkg uninstall -t tool-esptool
#       #pio pkg install -t "platformio/tool-esptool@1.409"
#
#        # Error: Missing version mkfatfs for armv6l
#        #
#        #pio pkg install -t "platformio/tool-mkfatfs"
#
#        cd ~/Downloads
#        git clone https://github.com/labplus-cn/mkfatfs.git
#        cd mkfatfs
#        git submodule update --init --recursive
#        cmake .
#        make
#
#        mkdir ~/.platformio/packages/tool-mkfatfs
#        cp mkfatfs ~/.platformio/packages/tool-mkfatfs/mkfatfs
#        cd  ~/.platformio/packages/tool-mkfatfs
#        echo '{"type": "tool", "name": "tool-mkfatfs", "version": "2.0.1", "spec": {"owner": "platformio", "id": 8175, "name": "tool-mkfatfs", "requirements": null, "uri": null}}' > .piopm
#cat >> package.json<< EOF
#{
#  "name": "tool-mkfatfs",
#  "version": "1.200.0",
#  "description": "Tool to build and unpack FAT to ESP flash",
#  "keywords": [
#    "tools",
#    "build tools",
#    "filesystem"
#  ],
#  "license": "MIT",
#  "system": [
#    "linux_armv6l",
#    "linux_armv7l",
#    "linux_armv8l",
#    "linux_aarch64"
#  ],
#  "repository": {
#    "type": "git",
#    "url": "https://github.com/labplus-cn/mkfatfs"
#  }
#}
#EOF
#
#        echo "export PATH=$PATH:$HOME/.platformio/packages/toolchain-xtensa-esp32/bin" >> ~/.bashrc
#

