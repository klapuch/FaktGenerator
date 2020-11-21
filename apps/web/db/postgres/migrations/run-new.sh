#!/bin/sh
set -eu

if [ -f "${0%/*}/.env.local" ]; then
	. ${0%/*}/.env.local
else
	. ${0%/*}/.env
fi

MIGRATION_FILENAMES=$(find db/postgres/migrations/*/*.sql | tr '\n' ',')
MIGRATION_FILENAMES_TO_RUN=$(psql -h $POSTGRES_HOST -U $POSTGRES_USER -d $POSTGRES_DB -tA -X -c "SELECT deploy.migrations_to_run('$MIGRATION_FILENAMES')")

if [ -z "$MIGRATION_FILENAMES_TO_RUN" ]; then
	echo '[OK] Nothing to migrate.';
	exit 0;
fi

for filename in $MIGRATION_FILENAMES_TO_RUN; do
	echo "Migrating $filename...";
	sh ${0%/*}/run.sh $filename;
	if [ $? != 0 ]; then
		echo "FAIL! Migration \"$filename\" was not successful.";
		exit 1;
	fi
	echo "Migration of \"$filename\" was successful.";
done

echo '[OK] All migrations were executed.'
