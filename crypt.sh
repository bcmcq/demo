#!/usr/bin/env bash
set -euo pipefail

ENV_FILE=".env"
VARS=("MUX_TOKEN_ID" "MUX_TOKEN_SECRET" "OPENROUTER_API_KEY" "OPENROUTER_MODEL")

usage() {
    echo "Usage: $0 --key=<passphrase> [--encrypt | --decrypt]"
    echo ""
    echo "  --encrypt   Encrypt MUX_TOKEN_ID & MUX_TOKEN_SECRET & OPENROUTER_API_KEY & OPENROUTER_MODEL in .env"
    echo "  --decrypt   Decrypt MUX_TOKEN_ID & MUX_TOKEN_SECRET & OPENROUTER_API_KEY & OPENROUTER_MODEL in .env"
    echo "  --key=KEY   The passphrase used for encryption/decryption"
    exit 1
}

encrypt_value() {
    local plaintext="$1"
    local key="$2"
    echo -n "$plaintext" | openssl enc -aes-256-cbc -a -A -salt -pbkdf2 -pass "pass:${key}" 2>/dev/null
}

decrypt_value() {
    local ciphertext="$1"
    local key="$2"
    echo -n "$ciphertext" | openssl enc -aes-256-cbc -a -A -d -salt -pbkdf2 -pass "pass:${key}" 2>/dev/null
}

get_env_value() {
    local var_name="$1"
    grep "^${var_name}=" "$ENV_FILE" | head -1 | cut -d'=' -f2-
}

set_env_value() {
    local var_name="$1"
    local new_value="$2"

    if grep -q "^${var_name}=" "$ENV_FILE"; then
        # Use awk to avoid sed delimiter issues with special characters
        awk -v var="$var_name" -v val="$new_value" '
            BEGIN { FS=OFS="=" }
            $1 == var { print var "=" val; next }
            { print }
        ' "$ENV_FILE" > "${ENV_FILE}.tmp" && mv "${ENV_FILE}.tmp" "$ENV_FILE"
    else
        echo "${var_name}=${new_value}" >> "$ENV_FILE"
    fi
}

do_encrypt() {
    local key="$1"
    local encrypted_count=0

    for var in "${VARS[@]}"; do
        value=$(get_env_value "$var")

        if [[ "$value" == ENC\(* ]]; then
            echo "  ⏭  $var is already encrypted, skipping."
            continue
        fi

        if [[ -z "$value" ]]; then
            echo "  ⏭  $var is empty, skipping."
            continue
        fi

        encrypted=$(encrypt_value "$value" "$key")
        set_env_value "$var" "ENC(${encrypted})"
        echo "  ✅ $var encrypted."
        ((encrypted_count++))
    done

    if [[ $encrypted_count -eq 0 ]]; then
        echo ""
        echo "Nothing to encrypt."
    else
        echo ""
        echo "Done. ${encrypted_count} value(s) encrypted in ${ENV_FILE}."
    fi
}

do_decrypt() {
    local key="$1"
    local decrypted_count=0

    for var in "${VARS[@]}"; do
        value=$(get_env_value "$var")

        if [[ "$value" != ENC\(* ]]; then
            echo "  ⏭  $var is not encrypted, skipping."
            continue
        fi

        # Strip the ENC(...) wrapper
        ciphertext="${value#ENC(}"
        ciphertext="${ciphertext%)}"

        plaintext=$(decrypt_value "$ciphertext" "$key") || {
            echo "  ❌ $var decryption failed. Wrong key?"
            exit 1
        }

        if [[ -z "$plaintext" ]]; then
            echo "  ❌ $var decryption produced empty output. Wrong key?"
            exit 1
        fi

        set_env_value "$var" "$plaintext"
        echo "  ✅ $var decrypted."
        ((decrypted_count++))
    done

    if [[ $decrypted_count -eq 0 ]]; then
        echo ""
        echo "Nothing to decrypt."
    else
        echo ""
        echo "Done. ${decrypted_count} value(s) decrypted in ${ENV_FILE}."
    fi
}

# --- Parse arguments ---

KEY=""
ACTION=""

for arg in "$@"; do
    case "$arg" in
        --key=*)
            KEY="${arg#--key=}"
            ;;
        --encrypt)
            ACTION="encrypt"
            ;;
        --decrypt)
            ACTION="decrypt"
            ;;
        *)
            echo "Unknown argument: $arg"
            usage
            ;;
    esac
done

if [[ -z "$KEY" ]]; then
    echo "Error: --key is required."
    echo ""
    usage
fi

if [[ -z "$ACTION" ]]; then
    echo "Error: --encrypt or --decrypt is required."
    echo ""
    usage
fi

if [[ ! -f "$ENV_FILE" ]]; then
    echo "Error: ${ENV_FILE} not found."
    exit 1
fi

echo ""
echo "Action: ${ACTION}"
echo "Target: ${VARS[*]}"
echo ""

if [[ "$ACTION" == "encrypt" ]]; then
    do_encrypt "$KEY"
elif [[ "$ACTION" == "decrypt" ]]; then
    do_decrypt "$KEY"
fi
