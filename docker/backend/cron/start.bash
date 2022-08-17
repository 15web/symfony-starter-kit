#!/usr/bin/env bash

# хак для передачи переменных окружения в крон
declare -p | grep -Ev '^declare -[[:alpha:]]*r' > /docker.env

exec cron -f
