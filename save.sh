#!/bin/bash

# --- CONFIG ---
DATE=$(date +%Y-%m-%d_%H-%M-%S)
BACKUP_DIR="$HOME/backup-ccar"

# Création dossier de sauvegarde si besoin
mkdir -p "$BACKUP_DIR"

# Archive le projet
tar -czvf "$BACKUP_DIR/ccar_backup_$DATE.tar.gz" /var/www/html/ccar

echo "✅ Backup effectué dans $BACKUP_DIR/ccar_backup_$DATE.tar.gz"
