CREATE DATABASE IF NOT EXISTS app_job_statistics;
CREATE DATABASE IF NOT EXISTS app_age_statistics;

GRANT ALL PRIVILEGES ON app_job_statistics.* TO 'app'@'%';
GRANT ALL PRIVILEGES ON app_age_statistics.* TO 'app'@'%';
FLUSH PRIVILEGES;
