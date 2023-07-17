Like https://github.com/WPprodigy/memcache-inconsistency-debugging, but with a new flavor 🙃

# Replicate Memcached consistency issue

1) Run `docker compose up` to build up the environment.
2) Run `./page-requester` to start up some threads requesting the test site/script.
3) Run `./memcached-signal-sender` at the same time to immitate resource contention on a mc server.
4) View the PHP logs error logs and see if any anomalies occur.

Issue: https://github.com/php-memcached-dev/php-memcached/issues/531
