FROM nginx:1 as base

RUN apt-get update -y && apt-get install -y certbot python3-certbot-nginx cron

RUN rm -rf /etc/nginx/conf.d/*;
COPY nginx.conf /etc/nginx/nginx.conf

COPY app.conf.template /root/
COPY create_nginx_conf.bash /root/
COPY get_cert.bash /root/

ARG SERVER_NAME
RUN /root/create_nginx_conf.bash "${SERVER_NAME}"

CMD ["bash", "-c", "cron; nginx -g 'daemon off;'"]
