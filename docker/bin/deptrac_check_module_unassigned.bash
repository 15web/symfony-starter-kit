#!/usr/bin/env bash


unassigned="$(docker compose run --rm backend-cli \
    vendor/bin/deptrac debug:unassigned \
        --config-file=deptrac-module.yaml \
        --cache-file=var/cache/.deptrac.cache)"

unassignedWithoutInfra=$(echo "$unassigned" | grep --invert-match 'App\\Infrastructure')

if [ "$unassignedWithoutInfra" != '' ]; then
    echo 'В deptrac-module.yaml не описаны классы:'
    echo "$unassignedWithoutInfra"
    exit 1
fi
