#!/bin/bash

COMMIT_MSG=$(git log -1 --pretty=%B)
COMMIT_STRUCTURE=$(echo "$COMMIT_MSG" | cat -A)
LINE_COUNT=$(echo "$COMMIT_MSG" | wc -l)
LAST_LINE=$(echo "$COMMIT_MSG" | tail -n 1)
REGEX="^((Merge[ a-z-]* branch.*)|(Revert*)|((build|chore|ci|docs|feat|fix|perf|refactor|revert|style|test)(\(.*\))?!?: .*))"
MERGE_REGEX="^Merge pull request .*"

if [[ $(echo "$COMMIT_MSG" | head -n 1) =~ $MERGE_REGEX ]]; then
    echo "Обнаружен merge-коммит"
    exit 0
fi

if ! [[ $COMMIT_MSG =~ $REGEX ]]; then
    echo "ОШИБКА: Commit не соответствует стандарту Conventional Commits"
    echo "Допустимые типы: build|chore|ci|docs|feat|fix|perf|refactor|revert|style|test"
    exit 1
fi

echo "Сообщение коммита корректно"
exit 0
