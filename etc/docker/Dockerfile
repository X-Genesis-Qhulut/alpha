FROM alpine:latest

RUN apk update && apk --no-cache add php php-mysqli
COPY --chmod=755 entry.sh /

ENTRYPOINT ["/entry.sh"]
