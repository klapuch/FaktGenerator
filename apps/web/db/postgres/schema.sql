CREATE SCHEMA constructs;

CREATE DOMAIN constructs.https_url AS text CHECK (VALUE ~ '^https://.+$');
CREATE DOMAIN constructs.text_not_empty AS text CHECK (trim(VALUE) != '');

CREATE FUNCTION constructs.array_reverse(anyarray) RETURNS anyarray RETURNS NULL ON NULL INPUT AS $BODY$
	SELECT array_agg(result.value ORDER BY ordinality DESC) FROM unnest($1) WITH ORDINALITY AS result(value);
$BODY$ LANGUAGE sql IMMUTABLE;


CREATE SCHEMA constants;

CREATE FUNCTION constants.source_other() RETURNS smallint AS $BODY$ SELECT 1::smallint; $BODY$ LANGUAGE sql IMMUTABLE;


CREATE TABLE facts (
	id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
	text constructs.text_not_empty NOT NULL,
	created_at timestamp with time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
	visited_count integer NOT NULL DEFAULT 0
);

CREATE FUNCTION facts_trigger_row_biu() RETURNS trigger AS $BODY$
BEGIN
	new.text = trim(new.text);

	RETURN new;
END;
$BODY$ LANGUAGE plpgsql VOLATILE;

CREATE TRIGGER facts_row_biu_trigger
	BEFORE INSERT OR UPDATE
	ON facts
	FOR EACH ROW EXECUTE PROCEDURE facts_trigger_row_biu();


CREATE TABLE fact_tags (
	id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
	name constructs.text_not_empty NOT NULL,
	fact_id integer NOT NULL REFERENCES facts (id) ON DELETE CASCADE ON UPDATE RESTRICT
);

CREATE FUNCTION fact_tags_trigger_row_biu() RETURNS trigger AS $BODY$
BEGIN
	new.name = trim(new.name);

	RETURN new;
END;
$BODY$ LANGUAGE plpgsql VOLATILE;

CREATE TRIGGER fact_tags_row_biu_trigger
	BEFORE INSERT OR UPDATE
	ON fact_tags
	FOR EACH ROW EXECUTE PROCEDURE fact_tags_trigger_row_biu();



CREATE TABLE sources (
	id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
	name constructs.text_not_empty NOT NULL,
	fa_icon constructs.text_not_empty NOT NULL
);

CREATE FUNCTION sources_trigger_row_biu() RETURNS trigger AS $BODY$
BEGIN
	new.name = trim(new.name);

	RETURN new;
END;
$BODY$ LANGUAGE plpgsql VOLATILE;

CREATE TRIGGER sources_row_biu_trigger
	BEFORE INSERT OR UPDATE
	ON sources
	FOR EACH ROW EXECUTE PROCEDURE sources_trigger_row_biu();



CREATE TABLE fact_sources (
	id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
	source_id smallint NOT NULL,
	url constructs.https_url NOT NULL,
	fact_id integer NOT NULL REFERENCES facts (id) ON DELETE CASCADE ON UPDATE RESTRICT
);
CREATE UNIQUE INDEX fact_sources_source_fact_id ON fact_sources (source_id, fact_id) WHERE source_id != constants.source_other();
CREATE UNIQUE INDEX fact_sources_url_fact_id ON fact_sources (url, fact_id);

CREATE FUNCTION fact_sources_trigger_row_biu() RETURNS trigger AS $BODY$
BEGIN
	new.url = trim(new.url);

	RETURN new;
END;
$BODY$ LANGUAGE plpgsql VOLATILE;

CREATE TRIGGER fact_sources_row_biu_trigger
	BEFORE INSERT OR UPDATE
	ON fact_sources
	FOR EACH ROW EXECUTE PROCEDURE fact_sources_trigger_row_biu();



CREATE FUNCTION fact_id(in_actual_id integer, in_visited_ids integer[]) RETURNS integer AS $BODY$
	WITH best AS (
		SELECT id, array_position(constructs.array_reverse(in_visited_ids || in_actual_id), id) AS position
		FROM facts
		ORDER BY 1 DESC, visited_count DESC, random()
		LIMIT 1
	), pad AS (
		SELECT id, 0 AS enough
		FROM facts
		ORDER BY id = in_actual_id, visited_count DESC, random()
		LIMIT 1
	), comparison AS (
		SELECT id, position FROM best
		UNION ALL
		SELECT id, enough FROM pad
		ORDER BY position ASC NULLS FIRST
		LIMIT 1
	)
	SELECT id FROM comparison
$BODY$ LANGUAGE sql STABLE;
