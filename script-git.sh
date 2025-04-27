#!/bin/bash

# --- CONFIG ---
GITHUB_REMOTE="github"
GITLAB_REMOTE="gitlab"
BRANCH=$(git symbolic-ref --short HEAD) # récupère ta branche actuelle
# ---

echo "Message du commit : "
read commit_message

# 1. Ajouter tous les changements
git add .

# 2. Commit
git commit -m "$commit_message"

# 3. Push GitHub
echo "🚀 Poussée vers GitHub ($GITHUB_REMOTE/$BRANCH)..."
git push $GITHUB_REMOTE $BRANCH

# 4. Push GitLab
echo "🚀 Poussée vers GitLab ($GITLAB_REMOTE/$BRANCH)..."
git push $GITLAB_REMOTE $BRANCH

echo "✅ Déploiement terminé sur GitHub et GitLab ! 🎉"
