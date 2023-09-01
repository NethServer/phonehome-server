FROM nginx:1.25.2-alpine as production
WORKDIR /var/www/html
ADD https://github.com/eficode/wait-for/releases/download/v2.2.4/wait-for /usr/local/bin/
COPY containers/nginx/start.sh /usr/local/bin/start
RUN chmod u+x /usr/local/bin/wait-for \
    && chmod u+x /usr/local/bin/start
COPY containers/nginx/config /etc/nginx
COPY public ./public
ENTRYPOINT ["start"]
CMD ["nginx", "-g", "daemon off;"]
