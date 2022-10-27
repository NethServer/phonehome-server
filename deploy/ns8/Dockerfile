FROM node:16 as node_base
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
RUN yarn build

FROM scratch as production
COPY imageroot /imageroot
COPY --from=node_build /app/dist /ui
LABEL org.nethserver.authorizations="traefik@any:routeadm"
LABEL org.nethserver.tcp-ports-demand="1"
LABEL org.nethserver.rootfull="0"
LABEL org.nethserver.images="docker.io/mariadb:10.6.10 \
        docker.io/redis:6.2.7-alpine \
        ghcr.io/tbaile/phonehome-app:latest \
        ghcr.io/tbaile/phonehome-web:latest"
ENTRYPOINT ["/"]
