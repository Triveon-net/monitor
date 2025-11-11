#!/bin/bash
# Uptime Checker Wrapper - Runs 6 times per minute (every 10 seconds)

for i in {1..6}; do
    php /var/www/html/scripts/uptime_checker.php >> /var/log/uptime_checker.log 2>&1
    
    # Sleep 10 seconds unless it's the last iteration
    if [ $i -lt 6 ]; then
        sleep 10
    fi
done
