#!/bin/bash
sudo docker compose down
find ./logs -name "*.log" -empty -delete