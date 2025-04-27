#!/bin/bash

# 📂 Dossier de sauvegarde
BACKUP_DIR="save"

# 🕒 Timestamp
TIMESTAMP=$(date +"%Y%m%d-%H%M%S")

# 📦 Archive name
ARCHIVE_NAME="$BACKUP_DIR/backup-$TIMESTAMP.tar.gz"

# 🏗️ Créer le dossier de sauvegarde s'il n'existe pas
mkdir -p $BACKUP_DIR

# 🗜️ Créer l'archive
tar --exclude="$BACKUP_DIR" --exclude=".git" -czf $ARCHIVE_NAME .

echo "✅ Projet sauvegardé dans $ARCHIVE_NAME"
