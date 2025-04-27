#!/bin/bash

# Demande le message du commit
read -p "Message du commit : " message

# Commit si des changements
if [ -n "$(git status --porcelain)" ]; then
    git add .
    git commit -m "$message"
else
    echo "✅ Aucun changement à commit, on continue..."
fi

# Push GitHub
echo "🚀 Poussée vers GitHub (github/gitlab-main)..."
git push github gitlab-main
github_status=$?

if [ $github_status -ne 0 ]; then
    echo "⚠️ Échec du push GitHub. Tentative de pull --rebase..."
    git pull github gitlab-main --rebase
    echo "🔄 Nouvelle tentative de push GitHub..."
    git push github gitlab-main
fi

# Push GitLab
echo "🚀 Poussée vers GitLab (gitlab/gitlab-main)..."
git push gitlab gitlab-main
gitlab_status=$?

if [ $gitlab_status -ne 0 ]; then
    echo "⚠️ Échec du push GitLab. Tentative de pull --rebase..."
    git pull gitlab gitlab-main --rebase
    echo "🔄 Nouvelle tentative de push GitLab..."
    git push gitlab gitlab-main
fi

echo "✅ Déploiement terminé sur GitHub et GitLab ! 🎉"
