#!/bin/sh
set -eu

if [ -f "${0%/*}/.env.local" ]; then
	. ${0%/*}/.env.local
else
	. ${0%/*}/.env
fi

MIGRATION_FILENAMES=$(find db/postgres/migrations/*/*.sql | tr '\n' ',');
MIGRATION_FILENAMES_TO_RUN=$(psql -h $POSTGRES_HOST -U $POSTGRES_USER -d $POSTGRES_DB -tA -X -c "SELECT deploy.migrations_to_run('$MIGRATION_FILENAMES')");

if [ -z "$MIGRATION_FILENAMES_TO_RUN" ]; then
	echo '[OK] No migrations.';
	exit 0;
fi

counter=1;
for filename in $MIGRATION_FILENAMES_TO_RUN; do
	echo $counter. $filename;
	counter=$((counter + 1))
done
