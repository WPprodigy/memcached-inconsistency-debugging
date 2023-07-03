Like https://github.com/WPprodigy/memcache-inconsistency-debugging, but with a new flavor 🙃

# Attempting to replicate Memcached consistency issue

1) Run `docker compose up` to build up the environment.
2) Run `./page-requester` to start up some threads requesting the test site/script.
3) Run `./memcached-signal-sender` at the same time to immitate resource contention on a mc server.
4) View the PHP logs error logs and see if any anomalies occur.

Unable to replicate so far, so we're still missing some unknown factors. Have tried tweaking various iteration numbers and sleep durations all around, no luck so far. Have also tried fully stopping/starting the memcached container itself.

The following error codes are returned from `Memcached::getResultCode()`:

- When the server has been turned off: 47 = MEMCACHED_SERVER_TEMPORARILY_DISABLED
- When the server has been SIGSTOP'd: 31 = MEMCACHED_TIMEOUT
- Sometimes after the server has been turned off (presumably when it's a fresh php fpm child): 03 = MEMCACHED_CONNECTION_FAILURE
