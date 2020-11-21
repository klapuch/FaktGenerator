INSERT INTO sources (name, fa_icon) VALUES ('Other', 'fas fa-globe');
INSERT INTO sources (name, fa_icon) VALUES ('Wikipedie', 'fab fa-wikipedia-w');

INSERT INTO facts (text) VALUES ('First');
INSERT INTO facts (text) VALUES ('Second');
INSERT INTO facts (text) VALUES ('Third');

INSERT INTO fact_sources (source_id, url, fact_id) VALUES (1, 'https://wikipedie.com/first', 1);
INSERT INTO fact_sources (source_id, url, fact_id) VALUES (1, 'https://google.com', 1);

INSERT INTO fact_tags (name, fact_id) VALUES ('cool', 1);
INSERT INTO fact_tags (name, fact_id) VALUES ('good', 1);
