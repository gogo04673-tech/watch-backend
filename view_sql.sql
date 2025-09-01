
# this of remove the user from the database
DROP USER IF EXISTS 'watch_backend'@'%';

# this create user can by connect any host
CREATE USER 'watch_backend'@'%' IDENTIFIED BY 'WatchBackend2004';
GRANT ALL PRIVILEGES ON `watch-backend`.* TO 'watch_backend'@'%';
FLUSH PRIVILEGES;

