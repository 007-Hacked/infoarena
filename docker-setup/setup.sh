#!/bin/sh
set -e

chmod a+w,g+s attach

if [ ! -f config.php ]; then
    cp docker-setup/config.php config.php;
fi

if [ ! -f eval/config.php ]; then
    cp docker-setup/eval.config.php eval/config.php;
fi

docker-compose up -d

docker-compose exec --user "$(id -u):$(id -g)" infoarena docker-setup/setup.php
docker-compose exec --user "$(id -u):$(id -g)" infoarena scripts/recompute-task-users-solved

