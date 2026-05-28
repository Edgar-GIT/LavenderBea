#!/bin/bash
set -e

# ─────────────────────────────────────────────
# Lavender Bea — Docker entrypoint
# Waits for MySQL, imports SQL files, then
# hands off to Apache.
# ─────────────────────────────────────────────

: "${LAVENDER_DB_HOST:?LAVENDER_DB_HOST is not set}"
: "${LAVENDER_DB_PORT:?LAVENDER_DB_PORT is not set}"
: "${LAVENDER_DB_NAME:?LAVENDER_DB_NAME is not set}"
: "${LAVENDER_DB_USER:?LAVENDER_DB_USER is not set}"
: "${LAVENDER_DB_PASS:?LAVENDER_DB_PASS is not set}"

SQL_DIR="/var/www/html/src/sql"
SQL_FILES=(
    "lavender_bea.sql"
    "views.sql"
    "triggers.sql"
)

MYSQL_OPTS=(
    -h "${LAVENDER_DB_HOST}"
    -P "${LAVENDER_DB_PORT}"
    -u "${LAVENDER_DB_USER}"
    "--password=${LAVENDER_DB_PASS}"
)

# ── 1. Wait for MySQL ──────────────────────────
TIMEOUT=60
ELAPSED=0
INTERVAL=2

echo "[entrypoint] Waiting for MySQL at ${LAVENDER_DB_HOST}:${LAVENDER_DB_PORT} ..."

until mysql "${MYSQL_OPTS[@]}" -e "SELECT 1;" >/dev/null 2>&1; do
    if [ "${ELAPSED}" -ge "${TIMEOUT}" ]; then
        echo "[entrypoint] ERROR: MySQL did not become ready within ${TIMEOUT}s. Aborting." >&2
        exit 1
    fi
    echo "[entrypoint] MySQL not ready yet — retrying in ${INTERVAL}s (${ELAPSED}s elapsed) ..."
    sleep "${INTERVAL}"
    ELAPSED=$(( ELAPSED + INTERVAL ))
done

echo "[entrypoint] MySQL is ready."

# ── 2. Import SQL files ────────────────────────
for SQL_FILE in "${SQL_FILES[@]}"; do
    FULL_PATH="${SQL_DIR}/${SQL_FILE}"

    if [ ! -f "${FULL_PATH}" ]; then
        echo "[entrypoint] ERROR: SQL file not found: ${FULL_PATH}" >&2
        exit 1
    fi

    echo "[entrypoint] Importing ${SQL_FILE} into database '${LAVENDER_DB_NAME}' ..."
    if mysql "${MYSQL_OPTS[@]}" "${LAVENDER_DB_NAME}" < "${FULL_PATH}"; then
        echo "[entrypoint] Successfully imported ${SQL_FILE}."
    else
        echo "[entrypoint] ERROR: Failed to import ${SQL_FILE}. Aborting." >&2
        exit 1
    fi
done

echo "[entrypoint] All SQL files imported successfully."

# ── 3. Start Apache ────────────────────────────
echo "[entrypoint] Starting Apache ..."
exec apache2-foreground
