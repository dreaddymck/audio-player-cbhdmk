#!/bin/bash

if [ $# -eq 0 ];
then

        echo "missing argument - string"

else

        echo $1
	fgrep -irn --exclude-dir=node_modules $1

fi

exit 0


