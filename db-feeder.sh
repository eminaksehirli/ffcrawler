#!/bin/bash

DIR=/tmp/ff-ebook/

for file in $DIR/*.json
do
	echo Feeding $file
	php db-populator.php $file
done
