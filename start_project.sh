#!/bin/bash
mkdir -p logs
sudo docker compose up --build -d
sudo docker compose logs -f --no-log-prefix 2>&1 | tee ./logs/projet-$(date +%Y.%m.%d-%H-%M-%S).log