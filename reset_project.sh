#!/bin/bash
mkdir -p logs
#tee → affiche les logs dans le terminal ET les écrit dans le fichier en même temps
sudo docker compose down -v && sudo docker compose up --build --remove-orphans | tee ./logs/projet-$(date +%Y.%m.%d-%H-%M-%S).log
find ./logs -name "*.log" -empty -delete

