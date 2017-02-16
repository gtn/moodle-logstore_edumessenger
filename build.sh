#!/usr/bin/env sh
#
# Inspired from https://github.com/xAPI-vle/moodle-logstore_xapi/blob/master/build.sh

# Creates a folder to zip.
rm -f edumessenger.zip
php -r "readfile('https://getcomposer.org/installer');" | php
php composer.phar install --no-interaction --no-dev
cp -r . ../moodle_logstore_build

# Removes unused files and folders.
find ../moodle_logstore_build -type d -name 'tests' | xargs rm -rf
find ../moodle_logstore_build -type d -name 'docs' | xargs rm -rf
find ../moodle_logstore_build -type d -name '.git' | xargs rm -rf
find ../moodle_logstore_build -type d -name '.idea' | xargs rm -rf
find ../moodle_logstore_build -type f -name '.gitignore' | xargs rm -rf
find ../moodle_logstore_build -type f -name 'composer.*' | xargs rm -rf
find ../moodle_logstore_build -type f -name 'phpunit.*' | xargs rm -rf
find ../moodle_logstore_build -type f -name '*.sh' | xargs rm -rf

# Creates the zip file.
mv ../moodle_logstore_build edumessenger
zip -r edumessenger.zip edumessenger -x "edumessenger/.git/**/*"
rm -rf edumessenger

# Updates Github.
git add edumessenger.zip
git commit -m "Build zip file"
git push
