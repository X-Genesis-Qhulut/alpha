FROM python:3-alpine

RUN apk update && apk --no-cache add php8 php8-mysqli

COPY entry.sh /
RUN chmod +x /entry.sh

CMD /entry.sh
# CMD /bin/sh
