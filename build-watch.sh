#!/bin/bash
## Runs build script whenever there is a change in src folder

echo 'Watching changes on src/'

current_OS=`uname`

# For macOS (Darwin), you can install fsevents-tools via Homebrew: `brew install fsevents-tools`
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