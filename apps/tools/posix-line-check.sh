#!/bin/sh
set -eu

BASE_DIR=/var/www/faktgenerator

if [ $# -eq 0 ]; then
	echo 'No arg was passed';
	exit 1;
elif [ $1 = 'git' ]; then
	FILES=$(git --no-pager diff --diff-filter=d --name-only | xargs --no-run-if-empty printf "$BASE_DIR/%s\n")
elif [ $1 = 'all' ]; then
	DIRS_TO_CHECK="$BASE_DIR/apps $BASE_DIR/env"
	FILES=$(find $DIRS_TO_CHECK -not -path "$BASE_DIR/apps/*/vendor/*" -not -path "$BASE_DIR/apps/*/node_modules/*" -not -path "*/temp/*" -type f)
else
	echo 'Only options "git" and "all" are allowed.';
	exit 1;
fi

for filename in $FILES; do
	END=$(tail -c -1 "$filename" | cat -e)
	if [ "$END" != '$' ]; then
		MIME_TYPE=$(file --mime-type "$filename" | grep 'text/*' || true)
		if [ -n "$MIME_TYPE" ]; then
			echo "Fixing file \"$filename\"."
			echo >> $filename
		fi
	fi
done;

echo '[OK] All files end with new line!'
exit 0
