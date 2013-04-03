#!/bin/bash
#feed=eminaksehirli
source credentials.sh
feed=ffebookpaylasimalan
step=150
for start in {0..5600..150}
do
	url="https://friendfeed-api.com/v2/feed/$feed?pretty=1&num=$step&start=$start&raw=1&maxcomments=500&maxlikes=500"
	file=/tmp/ff_file_${feed}_$start.json
	wget --http-user=$user --http-password=$pwd ${url} -O $file
done
