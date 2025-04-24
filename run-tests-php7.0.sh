#!/bin/bash

docker run --rm \
  -v "$PWD":/app \
  -w /app \
  antriver/php:7.0.33 bash -c "\
    composer install && \
    vendor/bin/phpunit tests --colors"
