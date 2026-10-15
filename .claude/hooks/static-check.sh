#!/usr/bin/env bash
# Хук Claude Code (PostToolUse): после правки PHP-файла запускает tests/static-check.php.
# Если проверка не прошла — выводит ошибки в stderr и возвращает код 2:
# Claude Code передаёт этот вывод модели, и она исправляет ошибку сразу.

input="$(cat)"

# Без PHP проверку выполнить нельзя — не мешаем работе.
if ! command -v php >/dev/null 2>&1; then
    echo "static-check: php не найден, проверка пропущена" >&2
    exit 0
fi

file="$(printf '%s' "$input" | php -r '$d = json_decode(stream_get_contents(STDIN), true); echo $d["tool_input"]["file_path"] ?? "";')"

# Только PHP-файлы.
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
        echo "static-check не прошёл после правки $file."
        echo "Проваленные проверки (✘) и подробности:"
        printf '%s\n' "$out" | grep -A30 '✘' | grep -v '^FAILED' | head -80
        printf '%s\n' "$out" | tail -1
        echo "Исправьте код, а не тест (docs/ai/AI_TESTING.md)."
    } >&2
    exit 2
fi
exit 0
