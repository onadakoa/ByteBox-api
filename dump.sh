docker exec -it sql mysqldump --skip-dump-date --no-data bytebox | sed 's/ AUTO_INCREMENT=[0-9]*//g' > dump.sql
