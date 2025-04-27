#!/bin/bash

# Petit script Git rapide pour sauvegarder et pousser sur GitHub

# Couleurs pour un affichage plus sympa
green='\e[32m'
red='\e[31m'
reset='\e[0m'

# 1. Initialiser Git (si besoin)
echo -e "${green}Initialisation du dépôt Git...${reset}"
git init

# 2. Ajouter tous les fichiers
echo -e "${green}Ajout des fichiers...${reset}"
git add .

# 3. Demander un message de commit
echo -n "${green}Message de commit : ${reset}"
read commit_message

# 4. Faire le commit
echo -e "${green}Création du commit...${reset}"
git commit -m "$commit_message"

# 5. Définir l'origin si besoin
echo -e "${green}Configuration du dépôt distant (si besoin)...${reset}"
git remote set-url origin https://github.com/didon971/ccar.git 2>/dev/null || git remote add origin https://github.com/didon971/ccar.git

# 6. Pousser sur la branche principale
echo -e "${green}Envoi vers GitHub...${reset}"
git push -u origin main || git push -u origin master

# 7. Fin
echo -e "${green}✅ Déploiement terminé avec succès !${reset}"
