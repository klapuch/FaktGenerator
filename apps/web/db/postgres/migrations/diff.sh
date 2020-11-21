#!/bin/sh
set -eu

if [ -f "${0%/*}/.env.local" ]; then
	. ${0%/*}/.env.local
else
	. ${0%/*}/.env
fi

BASE_DIR=/var/www/faktgenerator
TMP_DATABASE_NAME=tmp_faktgenerator
ACTUAL_SCHEMA_FILENAME=/tmp/migrations/actual.sql
MIGRATED_SCHEMA_FILENAME=/tmp/migrations/migrated.sql
MASTER_SCHEMA_FILENAME=/tmp/migrations/master.sql

mkdir -pv "$(dirname $ACTUAL_SCHEMA_FILENAME)"
mkdir -pv "$(dirname $MIGRATED_SCHEMA_FILENAME)"
mkdir -pv "$(dirname $MASTER_SCHEMA_FILENAME)"

psql -U $POSTGRES_USER -h $POSTGRES_HOST -d postgres -c "DROP DATABASE IF EXISTS $TMP_DATABASE_NAME";
psql -U $POSTGRES_USER -h $POSTGRES_HOST -d postgres -c "CREATE DATABASE $TMP_DATABASE_NAME";
psql -U $POSTGRES_USER -h $POSTGRES_HOST $TMP_DATABASE_NAME < $BASE_DIR/web/db/postgres/schema.sql;
pg_dump --host=$POSTGRES_HOST --port=$POSTGRES_PORT --username=$POSTGRES_USER --no-owner --no-privileges --schema-only --format=plain --file=$ACTUAL_SCHEMA_FILENAME $TMP_DATABASE_NAME

psql -U $POSTGRES_USER -h $POSTGRES_HOST -d postgres -c "DROP DATABASE $TMP_DATABASE_NAME";
psql -U $POSTGRES_USER -h $POSTGRES_HOST -d postgres -c "CREATE DATABASE $TMP_DATABASE_NAME";
git --no-pager show "$(git merge-base master HEAD):web/db/postgres/schema.sql" > $MASTER_SCHEMA_FILENAME;
psql -U $POSTGRES_USER -h $POSTGRES_HOST $TMP_DATABASE_NAME < $MASTER_SCHEMA_FILENAME;
git --no-pager diff --diff-filter=A --name-only master.. -- $BASE_DIR/web/db/postgres/migrations/**/*.sql \
	| xargs --no-run-if-empty -I {} psql -h $POSTGRES_HOST -U $POSTGRES_USER --single-transaction -d $TMP_DATABASE_NAME -v ON_ERROR_STOP=1 -X --file $BASE_DIR/{}

pg_dump --host=$POSTGRES_HOST --port=$POSTGRES_PORT --username=$POSTGRES_USER --no-owner --no-privileges --schema-only --format=plain --file=$MIGRATED_SCHEMA_FILENAME $TMP_DATABASE_NAME

diff $ACTUAL_SCHEMA_FILENAME $MIGRATED_SCHEMA_FILENAME

echo "[OK] Schemas after and before migrations are same."
