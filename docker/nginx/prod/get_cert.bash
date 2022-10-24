#!/usr/bin/env bash

set -Eeuo pipefail;
#set -x; # uncomment for debug

echo 'Obtaining TLS cert...';

ADMIN_EMAIL="${1}";
SERVER_NAME="${2}";
certbot \
        --nginx \
        --non-interactive \
        --agree-tos \
        --keep-until-expiring \
        --expand \
        --email "${ADMIN_EMAIL}" \
        --domain "${SERVER_NAME}" \
        --domain www."${SERVER_NAME}";

echo 'TLS cert obtained!';
