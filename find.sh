#!/bin/bash

if [ $# -eq 0 ];
then

        echo "missing argument - string"

else

        echo $1
	#fgrep -irn --exclude-dir=node_modules "$1"
	#fgrep -irn --exclude-dir={node_modules,components,git} "$1" | less
       	fgrep -irn --exclude-dir={node_modules,components,git,dist} --exclude={*.json,*.sql,private.js,public.js,webpack.config.js} "$1" | less


fi

exit 0


