#!/usr/bin/env zsh

# Local integration test runner.
# Mirrors the CI workflow: creates a Laravel project in Testbench/, wires up
# the local lucid/ package via a Composer path repository (symlinked), then
# runs bin/test-commands.sh inside the Laravel install.
#
# Usage:
#   ./bin/test-local.sh            # test all versions: 9.x 10.x 11.x
#   ./bin/test-local.sh 11.x       # test a single version
#   ./bin/test-local.sh 9.x 10.x   # test specific versions
#   FRESH=1 ./bin/test-local.sh    # wipe and recreate installs before testing

set -e

autoload -U colors && colors

LUCID_DIR="$(cd "$(dirname "$0")/.." && pwd)"
TESTBENCH_DIR="$(cd "$LUCID_DIR/../Testbench" && pwd)"

# Guard: TESTBENCH_DIR must be a non-empty, non-root, non-home resolved path
if [ -z "$TESTBENCH_DIR" ] || [ "$TESTBENCH_DIR" = "/" ] || [ "$TESTBENCH_DIR" = "$HOME" ]; then
    print "${fg_bold[red]}ERROR:${reset_color} TESTBENCH_DIR resolved to a dangerous path: '$TESTBENCH_DIR'. Aborting."
    exit 1
fi

if [[ $# -gt 0 ]]; then
    VERSIONS=($@)
else
    VERSIONS=(9.x 10.x 11.x 12.x)
fi

# Safe removal: only removes a direct laravel-* child of TESTBENCH_DIR
safe_remove() {
    TARGET="$1"
    case "$TARGET" in
        "$TESTBENCH_DIR"/laravel-?*)
            print "${fg[yellow]}--> Removing $TARGET${reset_color}"
            rm -rf "$TARGET"
            ;;
        *)
            print "${fg_bold[red]}ERROR:${reset_color} refusing to remove '$TARGET' — not a recognised laravel-* subdirectory of $TESTBENCH_DIR"
            exit 1
            ;;
    esac
}

for VERSION in $VERSIONS; do
    APP_DIR="$TESTBENCH_DIR/laravel-$VERSION"

    print ""
    print "${fg_bold[cyan]}==========================================${reset_color}"
    print "${fg_bold[cyan]}  Laravel $VERSION${reset_color}"
    print "${fg_bold[cyan]}==========================================${reset_color}"

    # Optionally wipe the existing install
    if [ "${FRESH:-0}" = "1" ] && [ -d "$APP_DIR" ]; then
        safe_remove "$APP_DIR"
    fi

    # Create the Laravel project if it doesn't exist yet
    if [ ! -d "$APP_DIR" ]; then
        print "${fg[blue]}--> Creating Laravel $VERSION project...${reset_color}"
        # --no-install: skip dependency installation so we can configure audit first.
        # Composer 2.7+ blocks packages with security advisories during resolution
        # (block-insecure=true by default). We disable it project-locally before
        # installing so older Laravel versions (9.x) can be installed without --global hacks.
        composer create-project --prefer-dist --no-audit --no-install --no-scripts "laravel/laravel=$VERSION" "$APP_DIR" --no-interaction
        composer -d "$APP_DIR" config audit.block-insecure false
        composer -d "$APP_DIR" install --no-interaction
    else
        print "${fg[white]}--> Reusing existing install at $APP_DIR${reset_color}"
    fi

    # Register the local lucid/ package as a path repository (symlinked so
    # changes are reflected immediately without reinstalling)
    print "${fg[blue]}--> Configuring local lucid path repository...${reset_color}"
    composer -d "$APP_DIR" config repositories.lucid \
        "{\"type\": \"path\", \"url\": \"$LUCID_DIR\", \"options\": {\"symlink\": true}}"

    # Allow dev stability so the @dev constraint resolves
    composer -d "$APP_DIR" config minimum-stability dev
    composer -d "$APP_DIR" config prefer-stable true

    # Install (or update) lucidarch/lucid from the local path
    print "${fg[blue]}--> Requiring lucidarch/lucid...${reset_color}"
    composer -d "$APP_DIR" require lucidarch/lucid:@dev --no-interaction

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
