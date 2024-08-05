FROM node:20 AS base
WORKDIR /var/www/html

FROM base AS development
ARG UID
ARG GID
RUN usermod -u $UID node \
    && groupmod -g $GID node
USER node
