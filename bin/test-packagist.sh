#!/usr/bin/env zsh

# Packagist integration test runner.
# Creates fresh Laravel projects in Testbench/, installs lucidarch/lucid from
# Packagist (not local), then runs bin/test-commands.sh inside each install.
#
# Usage:
#   ./bin/test-packagist.sh            # test all versions: 9.x 10.x 11.x
#   ./bin/test-packagist.sh 11.x       # test a single version
#   ./bin/test-packagist.sh 9.x 10.x   # test specific versions
#   FRESH=1 ./bin/test-packagist.sh    # wipe and recreate installs before testing
#   LUCID_VERSION=^2.0.0 ./bin/test-packagist.sh   # override package version constraint

set -e

autoload -U colors && colors

LUCID_DIR="$(cd "$(dirname "$0")/.." && pwd)"
TESTBENCH_DIR="$(cd "$LUCID_DIR/../Testbench" && pwd)"

# Guard: TESTBENCH_DIR must be a non-empty, non-root, non-home resolved path
if [ -z "$TESTBENCH_DIR" ] || [ "$TESTBENCH_DIR" = "/" ] || [ "$TESTBENCH_DIR" = "$HOME" ]; then
    print "${fg_bold[red]}ERROR:${reset_color} TESTBENCH_DIR resolved to a dangerous path: '$TESTBENCH_DIR'. Aborting."
    exit 1
fi

LUCID_CONSTRAINT="${LUCID_VERSION:-^2.0.0}"

if [[ $# -gt 0 ]]; then
    VERSIONS=($@)
else
    VERSIONS=(9.x 10.x 11.x)
fi

# Safe removal: only removes a direct laravel-packagist-* child of TESTBENCH_DIR
safe_remove() {
    TARGET="$1"
    case "$TARGET" in
        "$TESTBENCH_DIR"/laravel-packagist-?*)
            print "${fg[yellow]}--> Removing $TARGET${reset_color}"
            rm -rf "$TARGET"
            ;;
        *)
            print "${fg_bold[red]}ERROR:${reset_color} refusing to remove '$TARGET' — not a recognised laravel-packagist-* subdirectory of $TESTBENCH_DIR"
            exit 1
            ;;
    esac
}

for VERSION in $VERSIONS; do
    APP_DIR="$TESTBENCH_DIR/laravel-packagist-$VERSION"

    print ""
    print "${fg_bold[cyan]}==========================================${reset_color}"
    print "${fg_bold[cyan]}  Laravel $VERSION (Packagist lucidarch/lucid $LUCID_CONSTRAINT)${reset_color}"
    print "${fg_bold[cyan]}==========================================${reset_color}"

    # Optionally wipe the existing install
    if [ "${FRESH:-0}" = "1" ] && [ -d "$APP_DIR" ]; then
        safe_remove "$APP_DIR"
    fi

    # Create the Laravel project if it doesn't exist yet
    if [ ! -d "$APP_DIR" ]; then
        print "${fg[blue]}--> Creating Laravel $VERSION project...${reset_color}"
        composer create-project --prefer-dist --no-audit --no-install --no-scripts "laravel/laravel=$VERSION" "$APP_DIR" --no-interaction
        composer -d "$APP_DIR" config audit.block-insecure false
        composer -d "$APP_DIR" install --no-interaction
    else
        print "${fg[white]}--> Reusing existing install at $APP_DIR${reset_color}"
    fi

    # Install lucidarch/lucid from Packagist
    print "${fg[blue]}--> Requiring lucidarch/lucid $LUCID_CONSTRAINT from Packagist...${reset_color}"
    composer -d "$APP_DIR" require "lucidarch/lucid:$LUCID_CONSTRAINT" --no-interaction

    # Copy the latest test-commands.sh into the app directory and run it
    print "${fg[blue]}--> Running test-commands.sh...${reset_color}"
    cp "$LUCID_DIR/bin/test-commands.sh" "$APP_DIR/test-commands.sh"
    chmod +x "$APP_DIR/test-commands.sh"
    (cd "$APP_DIR" && ./test-commands.sh)

    print ""
    print "${fg_bold[green]}  Laravel $VERSION: PASSED${reset_color}"
done

print ""
print "${fg_bold[green]}All versions passed.${reset_color}"
