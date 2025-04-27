#!/bin/bash

# 📌 Configuration
GITHUB_REMOTE="github"
GITLAB_REMOTE="gitlab"
BRANCH=$(git rev-parse --abbrev-ref HEAD)

# 🖋️ Demander le message de commit
echo "Message du commit : "
read commit_message

# 📝 Commit uniquement si des modifications existent
if [ -n "$(git status --porcelain)" ]; then
    git add .
    git commit -m "$commit_message"
else
    echo "✅ Rien à commit, dépôt propre."
fi

# 🚀 Pousser vers GitHub
echo "🚀 Poussée vers GitHub ($GITHUB_REMOTE/$BRANCH)..."
if ! git push $GITHUB_REMOTE $BRANCH; then
    echo "⚠️ Échec du push GitHub. Tentative de pull --rebase..."
    git pull --rebase $GITHUB_REMOTE $BRANCH
    echo "🔄 Nouvelle tentative de push GitHub..."
    git push $GITHUB_REMOTE $BRANCH
fi

# 🚀 Pousser vers GitLab
echo "🚀 Poussée vers GitLab ($GITLAB_REMOTE/$BRANCH)..."
if ! git push $GITLAB_REMOTE $BRANCH; then
    echo "⚠️ Échec du push GitLab. Tentative de pull --rebase..."
    git pull --rebase $GITLAB_REMOTE $BRANCH
    echo "🔄 Nouvelle tentative de push GitLab..."
    git push $GITLAB_REMOTE $BRANCH
fi

echo "✅ Déploiement terminé sur GitHub et GitLab ! 🎉"
