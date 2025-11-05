FROM nginx:1.29.3

RUN ln -sf /usr/share/zoneinfo/UTC /etc/localtime

COPY default.conf /etc/nginx/conf.d/default.conf

WORKDIR /app/
