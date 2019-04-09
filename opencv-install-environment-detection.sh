#!/bin/bash

# Detecting cmake on the system https://stackoverflow.com/questions/592620/how-to-check-if-a-program-exists-from-a-bash-script
command -v cmake >/dev/null 2>&1 || { echo >&2 "Compiling OpenCV requires cmake, but no cmake was detected for system installation. Aborting."; exit 1; }
command -v pkg-config >/dev/null 2>&1 || { echo >&2 "Compiling OpenCV requires pkg-config, but no cmake was detected for system installation. Aborting."; exit 1; }
