#!/bin/bash
DIR="/home/asaancapital/public_html/public/uploads/business-documents"
if [ ! -d "$DIR" ]; then
  mkdir -p "$DIR"
  echo "Created $DIR"
else
  echo "$DIR already exists"
fi
REF="/home/asaancapital/public_html/public/uploads/business-photos"
if [ -d "$REF" ]; then
  chmod --reference="$REF" "$DIR"
  chown --reference="$REF" "$DIR"
  echo "Set permissions matching business-photos"
else
  chmod 755 "$DIR"
  echo "Set permissions to 755 (reference directory not found)"
fi
