#!/usr/bin/env bash
# Claude Code hook (PostToolUse): runs tests/static-check.php after a PHP file is edited.
# On failure prints the errors to stderr and exits with code 2:
# Claude Code feeds this output back to the model so it fixes the error right away.

input="$(cat)"

# Without PHP the check cannot run — do not block the work.
if ! command -v php >/dev/null 2>&1; then
    echo "static-check: php not found, check skipped" >&2
    exit 0
fi

file="$(printf '%s' "$input" | php -r '$d = json_decode(stream_get_contents(STDIN), true); echo $d["tool_input"]["file_path"] ?? "";')"

# PHP files only.
case "$file" in
    *.php) ;;
    *) exit 0 ;;
esac

cd "${CLAUDE_PROJECT_DIR:-.}" || exit 0
[ -f tests/static-check.php ] || exit 0

out="$(php tests/static-check.php 2>&1)"
code=$?

if [ "$code" -ne 0 ]; then
    {
        echo "static-check failed after editing $file."
        echo "Failed checks (✘) and details:"
        printf '%s\n' "$out" | awk '/✘/{p=1; print; next} p && /^      /{print; next} {p=0}' | head -80
        printf '%s\n' "$out" | tail -1
        echo "Fix the code, not the test (docs/ai/AI_TESTING.md)."
    } >&2
    exit 2
fi
exit 0
