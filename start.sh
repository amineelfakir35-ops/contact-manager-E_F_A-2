nano start.sh
#!/bin/bash

# Start PHP built-in server
php -S 0.0.0.0:${PORT:-8080} -t ./

chmod +x start.sh