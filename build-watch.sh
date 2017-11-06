#!/bin/sh
## Run the build script whenever there is a change in src folder


echo 'Watching changes on src/'

while inotifywait -qqr src -e create,move,modify,delete; do
    echo
    echo 'Change detected, running build'
    ./build.sh
done