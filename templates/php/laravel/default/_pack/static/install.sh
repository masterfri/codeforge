#!/bin/bash

RED="$(tput setaf 1)"
GREEN="$(tput setaf 2)"
RESET="$(tput sgr0)"
COMPOSER="php /home/grisha/Lab/scripts/composer.phar"
ARTISAN="php artisan"
NPM="npm"
PHP="php"
GULP="gulp"
ANSWER=""

function say() {
    echo "$GREEN$1$RESET"
}

function exclaim() {
    echo "$RED$1$RESET"
}

function ask() {
    echo -n "$GREEN$1$RESET"
    read ANSWER
}

function tryln () {
    SUCCESS=0
    while [ $SUCCESS -eq 0 ]
    do
        ask "$2"
        if [ ! -d "$ANSWER" ]
        then
            exclaim "Invalid directory!"
        else
            ln -s "$ANSWER" "$1"
            if [ $? -ne 0 ]
            then
                exclaim "Error: can not make a link"
                exit
            fi
            SUCCESS=1
        fi
    done
}

say ""
say "-------------------------------------"
say "-- Welcome to installation wizard! --"
say "-------------------------------------"

say ""
say "Q:> Would you like to run 'composer install' (i) or just to make link (l) to existing 'vendor' directory?"
ask "A:> "
case "$ANSWER" in
    [iI])
        say ""
        say "Running composer..."
        $COMPOSER "install"
        if [ $? -ne 0 ]
        then
            exclaim "Error: composer has exited with code ($?)"
            exit
        fi
        ;;
    [lL])
        say "Q:> Please provide path to existing 'vendor' directory."
        tryln "vendor" "A:> "
        ;;
esac

say ""
say "Q:> Would you like to run 'npm install' (i) or just to make link (l) to existing 'node_modules' directory?"
ask "A:> "
case "$ANSWER" in
    [iI])
        say ""
        say "Running npm..."
        "$NPM" "install"
        if [ $? -ne 0 ]
        then
            exclaim "Error: npm has exited with code ($?)"
            exit
        fi
        ;;
    [lL])
        say "Q:> Please provide path to existing 'node_modules' directory."
        tryln "node_modules" "A:> "
        ;;
esac

say ""
say "Q:> Please provide MySQL database name"
ask "A:> "
DBNAME="$ANSWER"

say ""
say "Q:> Please provide MySQL user name"
ask "A:> "
USRNAME="$ANSWER"

say ""
say "Q:> Please provide MySQL password"
ask "A:> "
PASSWD="$ANSWER"

say ""
say "Writing '.env' file..."
{
    echo "APP_ENV=local"
    echo "APP_KEY=$($ARTISAN key:generate --show)"
    echo "APP_DEBUG=true"
    echo "APP_LOG_LEVEL=debug"
    echo "APP_URL=http://localhost"

    echo "DB_CONNECTION=mysql"
    echo "DB_HOST=127.0.0.1"
    echo "DB_PORT=3306"
    echo "DB_DATABASE=$DBNAME"
    echo "DB_USERNAME=$USRNAME"
    echo "DB_PASSWORD=$PASSWD"
} > ".env"

say ""
say "Running migrations..."
$ARTISAN "migrate"

say ""
say "Q:> Would you like to run seeders (y or n)?"
ask "A:> "

case "$ANSWER" in
    [yY])
        say ""
        say "Running seeders..."
        $ARTISAN "db:seed"
        ;;
esac

say ""
say "Running gulp..."
"$GULP"
if [ $? -ne 0 ]
then
    exclaim "Error: gulp has exited with code ($?)"
    exit
fi

say ""
say "-------------------------------------"
say "--             DONE!               --"
say "-------------------------------------"
say ""