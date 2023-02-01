#!/bin/sh

set -eu

b64() {
    base64 -w 0 | tr '+/' '-_' | tr -d '='
}

# cd to the script's directory
cd "$(dirname "$(readlink -f "$0" || realpath "$0")")"

mkdir -p data/users data/options data/messages

# create_user login prenom nom default_password role
create_user() (
    cd data/users
    user="$(printf '%s' "$1" | b64)"
    mkdir -p "$user"
    cd "$user"
    echo "$2" > prenom
    echo "$3" > nom
    echo "$4" > default_password
    echo "$5" > role
)

create_user admin Admine Histrateur admin admin
create_user respo Responsable Admission respo responsable


case "$(cat /etc/hostname)" in
    pce*)
        ini=config/ecole.ini
        ;;
    *)
        ini=config/default.ini
        ;;
esac


exec php -S localhost:8080 -c "$ini" -t public
