#!/bin/bash

SAVE=
while [ $# -gt 0 ]; do
    case "$1" in
        --save) SAVE=1; shift ;;
        *) break ;;
    esac
done

UPLOAD_URL="$1"
FILE="${2:-surf.mp4}"
FILE_PATH="storage/test/$FILE"

if [ -z "$UPLOAD_URL" ]; then
    echo "Usage: ./upload.sh [--save] <presigned-upload-url> [filename]"
    echo "  --save     after upload, POST to api to attach media to social_media_content 1"
    echo "  filename  defaults to surf.mp4 in storage/test/"
    echo "  Run from the host (not inside Sail) so localhost reaches MinIO."
    exit 1
fi

if [ ! -f "$FILE_PATH" ]; then
    echo "File not found: $FILE_PATH"
    exit 1
fi

# Presigned URL was signed with Host: minio:9002 (inside Docker). The controller rewrites
# the URL to localhost:<forwarded_port> so curl can reach MinIO from the host. We must
# send the original Host header or the signature fails.
CURL_OPTS=(-T "$FILE_PATH")
if [[ "$UPLOAD_URL" == *"localhost:"* ]]; then
    CURL_OPTS+=(-H "Host: minio:9000")
fi

echo "Uploading $FILE_PATH to S3/MinIO..."
curl "${CURL_OPTS[@]}" "$UPLOAD_URL" --progress-bar | cat

echo ""

# Wait 2 seconds to ensure the file is uploaded
sleep 2

if [ -n "$SAVE" ]; then
    path="${UPLOAD_URL#*://}"
    path="${path#*/}"
    path="${path%%\?*}"
    if [[ "$path" == local/* ]]; then
        storage_key="${path#local/}"
    else
        storage_key="$path"
    fi
    file_name="$FILE"
    echo "Saving media record (storage_key=$storage_key, file_name=$file_name)..."
    response=$(curl -s -X POST "http://localhost/api/social_media_contents/1/media" \
        -H "Content-Type: application/json" \
        -H "Accept: application/json" \
        -d "{\"storage_key\": \"$storage_key\", \"file_name\": \"$file_name\"}")
    if command -v jq >/dev/null 2>&1; then
        echo "$response" | jq .
    else
        echo "$response"
    fi
fi

echo "Done."
