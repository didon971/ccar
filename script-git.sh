#!/bin/bash

# Demander un message de commit
echo "Message du commit : "
read commit_message

# Ajouter les changements
git add .

# Commit
git commit -m "$commit_message"

# Pousser sur GitHub (branche main)
git push github main

# Pousser sur GitLab (branche gitlab-main)
git push gitlab gitlab-main

echo "✅ Déploiement terminé sur GitHub (main) et GitLab (gitlab-main) !"

