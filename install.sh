#!/usr/bin/env bash
# ============================================================================
# install.sh — переименовывает заготовку модуля под конкретный проект.
#
# Запускать из корня репозитория (там, где лежит этот install.sh).
# Заменяет:
#   mycompany.emptymodule            → <module.id>          (в именах файлов/папок)
#   Mycompany_EmptyModule            → <ModuleName>          (в legacy class name)
#   Mycompany\EmptyModule            → <Module\Namespace>    (в namespace)
#
# Пример:
#   ./install.sh
#   Enter module ID (mycompany.emptymodule): acme.delivery
#   Enter module namespace (Acme_Delivery): Acme\Delivery
#
# Совместимость: bash 3+ (macOS/Linux/WSL/Git Bash). На чистом cmd.exe
# работать не будет — используйте Git Bash или WSL.
# ============================================================================

set -euo pipefail

if [ -z "${BASH_VERSION:-}" ]; then
    echo "ERROR: требуется bash (Git Bash / WSL / Linux / macOS)." >&2
    exit 1
fi

MODULE_SRC="mycompany.emptymodule"
NAMESPACE_SRC="Mycompany\\EmptyModule"
LEGACY_CLASS_SRC="Mycompany_EmptyModule"

# ---------- defaults (для быстрого прохода) ----------
DEFAULT_MODULE_ID="acme.delymodule"
DEFAULT_NAMESPACE="Acme\\Delymodule"

# ---------- 1. ввод ----------
echo "================================================================"
echo "  Переименование заготовки модуля"
echo "  (все вхождения будут заменены, папка переименована)"
echo "================================================================"

if [ -d "$MODULE_SRC" ]; then
    echo "Найдена исходная папка: $MODULE_SRC"
else
    echo "WARN: папка $MODULE_SRC не найдена в текущей директории." >&2
    echo "      Возможно, модуль уже переименован или скрипт запущен не из корня." >&2
fi

read -r -p "Enter module ID (vendor.module) [${DEFAULT_MODULE_ID}]: " MODULE_ID
MODULE_ID="${MODULE_ID:-$DEFAULT_MODULE_ID}"

read -r -p "Enter module namespace (Vendor_Module) [${DEFAULT_NAMESPACE}]: " NAMESPACE_RAW
NAMESPACE_RAW="${NAMESPACE_RAW:-$DEFAULT_NAMESPACE}"

# Нормализуем namespace:
#   ввод "Acme\Delivery"        → Acme\Delivery (для namespace PHP)
#   ввод "Acme_Delivery"        → Acme\Delivery
#   ввод "Acme\Delivery\Sub"    → Acme\Delivery\Sub
# Легаси-класс: первая точка → подчёркивание, остальные → подчёркивание.
NAMESPACE_PHP=$(printf '%s' "$NAMESPACE_RAW" | sed -E 's#_#\\#g; s#/+#\\#g; s#\\+#\\#g')
# Trim leading backslash
NAMESPACE_PHP=${NAMESPACE_PHP#\\}
# Legacy: "Acme\Delivery" → "Acme_Delivery"
NAMESPACE_LEGACY=$(printf '%s' "$NAMESPACE_RAW" | sed -E 's#\\#_#g; s#_+#_#g')
# Sanity: module_id не может содержать слеши, только точку и a-z0-9_
if ! printf '%s' "$MODULE_ID" | grep -qE '^[a-z0-9_]+\.[a-z0-9_]+$'; then
    echo "ERROR: module ID '$MODULE_ID' не соответствует формату vendor.module (a-z0-9_)." >&2
    exit 2
fi

echo
echo "Будет:"
echo "  Папка:   $MODULE_SRC  →  $MODULE_ID"
echo "  Legacy:  $LEGACY_CLASS_SRC  →  $NAMESPACE_LEGACY"
echo "  PHP ns:  $NAMESPACE_SRC  →  $NAMESPACE_PHP"
echo

read -r -p "Продолжить? [y/N]: " CONFIRM
case "$CONFIRM" in
    [yY]|[yY][eE][sS]) ;;
    *) echo "Отменено."; exit 0 ;;
esac

# ---------- 2. sed-экранирование ----------
# Через |, чтобы не возиться со слешами в namespace.
SEP='|'

# Замены в PHP-файлах: php namespace, legacy class, module_id в строках.
if [ -d "$MODULE_SRC" ]; then
    find "$MODULE_SRC" -type f -name '*.php' -print0 | while IFS= read -r -d '' F; do
        # Под OS X sed -i требует пустой аргумент; под Linux — нет. Делаем универсально.
        sed -i.bak \
            -e "s${SEP}${NAMESPACE_SRC}${SEP}${NAMESPACE_PHP}${SEP}g" \
            -e "s${SEP}${LEGACY_CLASS_SRC}${SEP}${NAMESPACE_LEGACY}${SEP}g" \
            -e "s${SEP}${MODULE_SRC}${SEP}${MODULE_ID}${SEP}g" \
            "$F"
        rm -f "${F}.bak"
    done
fi

# И в не-php файлах (.settings.php, install/index.php и т.п.) тоже.
if [ -d "$MODULE_SRC" ]; then
    find "$MODULE_SRC" -type f ! -name '*.php' -print0 | while IFS= read -r -d '' F; do
        sed -i.bak \
            -e "s${SEP}${MODULE_SRC}${SEP}${MODULE_ID}${SEP}g" \
            "$F"
        rm -f "${F}.bak"
    done
fi

# ---------- 3. переименование папки ----------
if [ -d "$MODULE_SRC" ]; then
    mv "$MODULE_SRC" "$MODULE_ID"
fi

echo
echo "Готово. Папка: $MODULE_ID"
echo "Дальше: установить модуль через админку Битрикса:"
echo "  /bitrix/admin/partner_modules.php?lang=ru"
echo "  или просто положить папку в /local/modules/$MODULE_ID/"
echo "  и установить через Marketplace → Установленные решения."
