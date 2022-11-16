FROM node:18.12.1-alpine as node_base
WORKDIR /app

FROM node_base as node_modules
COPY ui/package.json .
COPY ui/yarn.lock .
RUN yarn

FROM node_base as node_build
COPY ui/package.json .
COPY ui/yarn.lock .
COPY --from=node_modules /app/node_modules /app/node_modules
COPY ui/.browserslistrc .
COPY ui/.eslintrc.js .
COPY ui/babel.config.js .
COPY ui/vue.config.js .
COPY ui/public public
COPY ui/src src
ENV NODE_OPTIONS=--openssl-legacy-provider
RUN yarn build

FROM scratch as production
COPY imageroot /imageroot
COPY --from=node_build /app/dist /ui
LABEL org.nethserver.authorizations="traefik@node:routeadm"
LABEL org.nethserver.tcp-ports-demand="1"
LABEL org.nethserver.rootfull="0"
ARG PHONEHOME_TAG=latest
ARG PHONEHOME_SERVER_APP=ghcr.io/nethserver/phonehome-server-app
ARG PHONEHOME_SERVER_WEB=ghcr.io/nethserver/phonehome-server-web
LABEL org.nethserver.images="docker.io/mariadb:10.6.11 \
        docker.io/redis:6.2.7-alpine \
        $PHONEHOME_SERVER_APP:$PHONEHOME_TAG \
        $PHONEHOME_SERVER_WEB:$PHONEHOME_TAG"
ENTRYPOINT ["/"]
