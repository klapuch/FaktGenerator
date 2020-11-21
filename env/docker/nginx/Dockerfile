FROM nginx:1.17

COPY nginx.conf /etc/nginx/nginx.conf

COPY sites-enabled/www.conf /etc/nginx/sites-enabled/www.conf
COPY sites-enabled/static.conf /etc/nginx/sites-enabled/static.conf

COPY snippets/www/php.conf /etc/nginx/snippets/www/php.conf
COPY snippets/www/routes.conf /etc/nginx/snippets/www/routes.conf
COPY snippets/www/auth.conf /etc/nginx/snippets/www/auth.conf
COPY snippets/www/.htpasswd.conf /etc/nginx/snippets/www/.htpasswd.conf
COPY snippets/www/security_headers.conf /etc/nginx/snippets/www/security_headers.conf
COPY snippets/static/security_headers.conf /etc/nginx/snippets/static/security_headers.conf
