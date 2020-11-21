#!/bin/sh
set -eu

if [ -f "${0%/*}/.env.local" ]; then
	. ${0%/*}/.env.local
else
	. ${0%/*}/.env
fi

psql -h $POSTGRES_HOST -U $POSTGRES_USER --single-transaction -d $POSTGRES_DB -v ON_ERROR_STOP=1 -X --file $1
