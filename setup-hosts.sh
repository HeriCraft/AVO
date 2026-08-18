#!/usr/bin/env bash

# Définition des couleurs pour l'affichage
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # Pas de couleur

echo -e "${YELLOW}Vérification des privilèges sudo/root...${NC}"

# Vérification des privilèges
if [ "$EUID" -ne 0 ]; then
  echo -e "${RED}Erreur : Ce script doit être exécuté en tant que root.${NC}"
  echo -e "Veuillez relancer la commande avec sudo : ${CYAN}sudo ./setup-hosts.sh${NC}"
  exit 1
fi

HOSTS_FILE="/etc/hosts"
DOMAINS=("app.avo.local" "api.avo.local" "inspector.avo.local")
IP="127.0.0.1"

echo -e "${YELLOW}Mise à jour du fichier $HOSTS_FILE...${NC}"

ADDED_COUNT=0

# Boucle d'ajout idempotent
for DOMAIN in "${DOMAINS[@]}"; do
    # On cherche précisément l'IP suivie du domaine (en ignorant les espaces)
    if grep -q -E "^\s*$IP\s+$DOMAIN" "$HOSTS_FILE"; then
        echo -e "${GREEN}✓ Le domaine $DOMAIN est déjà configuré.${NC}"
    else
        echo -e "$IP\t$DOMAIN" >> "$HOSTS_FILE"
        echo -e "${CYAN}➕ Le domaine $DOMAIN a été ajouté.${NC}"
        ADDED_COUNT=$((ADDED_COUNT+1))
    fi
done

echo ""
if [ $ADDED_COUNT -gt 0 ]; then
    echo -e "${GREEN}🚀 Opération terminée ! $ADDED_COUNT domaine(s) ajouté(s).${NC}"
else
    echo -e "${GREEN}🚀 Opération terminée ! Aucune modification nécessaire, vos DNS locaux sont déjà à jour.${NC}"
fi
