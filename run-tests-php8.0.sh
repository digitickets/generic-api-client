#!/bin/bash

docker run --rm \
  -v "$PWD":/app \
  -w /app \
  antriver/php:8.0.12 bash -c "\
    composer install --ignore-platform-reqs && \
    composer test"
