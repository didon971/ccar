#!/bin/bash

# Demander un message de commit
read -p "Message du commit : " message

# Ajouter tous les fichiers
git add .

# Faire le commit
git commit -m "$message"

# Pousser vers GitHub
git push github main

# Pousser vers GitLab
git push gitlab main1

echo "✅ Déploiement terminé sur GitHub et GitLab !"
