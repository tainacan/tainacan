#!/bin/bash
## Run the build script whenever there is a change in src folder


echo 'Watching changes on src/'

current_OS=`uname`

# For macOS (Darwin) is needed fsevents-tools installed (you can use homebrew install fsevents-tools)
if [ $current_OS == "Darwin" ]; then
    echo
    notifyloop src ./build.sh
else
    while inotifywait -qqr src -e create,move,modify,delete; do
        echo
        echo 'Change detected, running build'
        ./build.sh
    done
fi