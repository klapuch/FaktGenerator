#!/bin/sh
set -eu

# BRANCH_NAME--2019-01-01.sql
MIGRATION_NAME=$(git rev-parse --abbrev-ref HEAD)--$(date +"%m-%d").sql

# db/postgres/migrations/2019/BRANCH_NAME--2019-01-01.sql
MIGRATION_FILENAME=db/postgres/migrations/$(date +"%Y")/$MIGRATION_NAME

mkdir -p db/postgres/migrations/"$(date +"%Y")"
cp -i db/postgres/migrations/template.sql $MIGRATION_FILENAME

sed -i -e "s~VAR__MIGRATION_NAME~$MIGRATION_FILENAME~g" $MIGRATION_FILENAME

echo '[OK] Migration file is created.'
