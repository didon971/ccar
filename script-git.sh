#!/bin/bash

# Demander un message de commit
echo "Message du commit : "
read commit_message

# Ajouter les changements
git add .

# Commit
git commit -m "$commit_message"

# Détection de la branche actuelle
current_branch=$(git branch --show-current)

echo "📍 Branche actuelle : $current_branch"

# Déploiement selon la branche
if [ "$current_branch" == "main" ]; then
    echo "🚀 Poussée vers GitHub (main) et GitLab (main)..."
    git push github main
    git push gitlab main
elif [ "$current_branch" == "main" ]; then
    echo "🚀 Poussée uniquement vers GitLab (main)..."
    git push gitlab main
else
    echo "❌ Branche inconnue, aucune poussée effectuée."
    exit 1
fi

echo "✅ Déploiement terminé !"
