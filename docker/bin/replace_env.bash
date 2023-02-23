#!/usr/bin/env bash

set -Eeuo pipefail
#set -x # uncomment for debug

os-depend-sed-in-place() {
    if [ "$(uname -s)" == 'Darwin' ]; then
        sed -i '' "$@"
    else
        sed --in-place "$@"
    fi
}

ENV_FILE_PATH="${1}"
ENV_NAME="${2}"
NEW_VALUE="${3}"

if [ "$(grep ${ENV_NAME} ${ENV_FILE_PATH})" != '' ]; then
    os-depend-sed-in-place -E "s|^.*${ENV_NAME}=.*$|${ENV_NAME}='${NEW_VALUE}'|" ${ENV_FILE_PATH}
else
    echo "${ENV_NAME}='${NEW_VALUE}'" >>${ENV_FILE_PATH}
fi
