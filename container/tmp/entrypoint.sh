#!/bin/bash
php-fpm -D && \
supervisord -c /etc/supervisord.conf