#image
FROM nginx:latest
#Metadata du container
LABEL authors="Patrick"
LABEL maintainer="Patrick"
LABEL version="1.0"
LABEL description="webserver based image for lab"
#WORKDIR : repertoire de reference
WORKDIR /usr/share/nginx/html

ENV HOME=/root

RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y nano wget git net-tools && \
    apt-get clean
