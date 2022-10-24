#!/usr/bin/env bash

set -Eeuo pipefail;
#set -x; # uncomment for debug

echo 'Creating Nginx conf...';

SERVER_NAME="${1}";
cat /root/app.conf.template | \
    SERVER_NAME=${SERVER_NAME} \
    SSL_CONFIG='' \
        envsubst \
            >  /etc/nginx/conf.d/app.conf;

echo 'Nginx conf created!';
