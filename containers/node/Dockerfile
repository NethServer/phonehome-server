FROM --platform=$BUILDPLATFORM node:20.16.0 AS base

FROM base AS development
WORKDIR /var/www/html
ARG UID
ARG GID
RUN usermod -u $UID node \
    && groupmod -g $GID node
USER node

FROM base AS build
WORKDIR /app
COPY package.json .
COPY package-lock.json .
RUN npm ci
COPY postcss.config.js .
COPY tailwind.config.js .
COPY vite.config.js .
COPY public public
COPY resources resources
RUN npm run build
