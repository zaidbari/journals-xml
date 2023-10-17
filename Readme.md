## Requirements
- Download and install composer (https://getcomposer.org/download/)
- Download and install NodeJS (https://nodejs.org/en/)
- Check NPM version ```npm -v```
- Check composer version ```composer -v```
- Check NodeJS version ```node -v```

## Setup and Installation
#### 1: Run the following command to install all the dependencies:
```bash
composer install
npm install
```

```bash
php -S localhost:8000
```

### .env file
```.env
# General Config
ANALYTICS="null"

# App Config
JOURNAL_ID="72"
JOURNAL_TITLE="Saudi Journal of Emergency Medicine"
JOURNAL_ABBREV="SJEMed"
JOURNAL_DOMAIN="https://sjemed.com/"
JOURNAL_ISSN=""
JOURNAL_EISSN="2520-5002"
JOURNAL_PUBLISHER="Waraqa Scientific Publishing House / Discover STM Publishing Ltd"

# Database Config
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_USERNAME=
DB_DATABASE=
DB_PASSWORD=


# Debug Config
APP_DEBUG=0
```