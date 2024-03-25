FROM node:20.11.1-alpine as node_build
WORKDIR /app
COPY ui/package.json .
COPY ui/yarn.lock .
RUN --mount=type=cache,target=/usr/local/share/.cache \
    yarn
COPY ui/.browserslistrc .
COPY ui/.eslintrc.js .
COPY ui/babel.config.js .
COPY ui/vue.config.js .
COPY ui/public public
COPY ui/src src
ENV NODE_OPTIONS=--openssl-legacy-provider
RUN yarn build

FROM scratch as production
COPY --link imageroot /imageroot
COPY --from=node_build /app/dist /ui
LABEL org.nethserver.authorizations="traefik@node:routeadm"
LABEL org.nethserver.tcp-ports-demand="1"
LABEL org.nethserver.rootfull="0"
ENTRYPOINT ["/"]
