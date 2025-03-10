#!/bin/bash

cd /var/www/html

chown -R www-data:www-data /var/www/html

apache2-foreground
