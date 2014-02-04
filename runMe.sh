#!/bin/bash
date
DIR="$( cd "$( dirname "$0" )" && pwd )"
pushd $DIR
php updater.php
php file_downloader.php
#php prepare_for_docs.php
popd
