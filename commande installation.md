# Procédure de lancement du projet Docker

## Prérequis

Ajouter les entrées suivantes dans le fichier `hosts` de Windows
(`C:\Windows\System32\drivers\etc\hosts`) en l'ouvrant avec un éditeur
de texte **en tant qu'administrateur** :

```
127.0.0.1   site-apache1.com
127.0.0.1   site-apache2.com
```

---

## Lancement standard

```bash
docker compose up --build
```

| Option | Rôle |
|--------|------|
| `up` | Crée et démarre tous les conteneurs définis dans `docker-compose.yml` |
| `--build` | Force la reconstruction des images avant de démarrer — utile si tu as modifié un Dockerfile ou des fichiers copiés dans l'image |

---

## Lancement en arrière-plan

```bash
docker compose up --build -d
```

| Option | Rôle |
|--------|------|
| `-d` | Mode *detached* — les conteneurs tournent en arrière-plan, le terminal reste libre |

Pour voir les logs après un démarrage en arrière-plan :

```bash
docker compose logs -f
```

---

## Lancement avec nettoyage des conteneurs orphelins

```bash
docker compose up --build --remove-orphans
```

| Option | Rôle |
|--------|------|
| `--remove-orphans` | Supprime les conteneurs qui existaient lors d'un lancement précédent mais qui ne sont plus définis dans le `docker-compose.yml` actuel — utile quand tu renommes ou supprimes un service |

---

## Arrêt sans perte de données

```bash
docker compose down
```

Arrête et supprime les conteneurs, mais **conserve les volumes** (données PostgreSQL, Redis).
Au prochain `up`, PostgreSQL retrouvera ses données intactes.

---

## Réinitialisation complète (reset de la base)

```bash
docker compose down -v
docker compose up --build
```

Ces deux commandes doivent être exécutées **l'une après l'autre**.

**Étape 1 — `docker compose down -v`**

| Option | Rôle |
|--------|------|
| `down` | Arrête et supprime les conteneurs |
| `-v` | Supprime aussi les volumes nommés (`pg_data`, `redis_data`) — les données sont effacées définitivement |

**Étape 2 — `docker compose up --build`**

PostgreSQL repart de zéro : il n'y a plus de base existante, donc il exécute le fichier
`init.sql` et recrée les tables avec les données initiales.

> init.sql reste sur ta machine, il n'est pas touché
> Ce qui est supprimé c'est l'espace disque que Docker avait alloué pour stocker les données de PostgreSQL et Redis
> Au prochain démarrage, PostgreSQL voit qu'il n'y a pas de base existante et réexécute init.sql pour tout recréer

> ***⚠️ À utiliser uniquement quand tu veux repartir d'une base vierge***.


---

## Résumé

| Situation | Commande |
|-----------|----------|
| Premier lancement ou modification d'un Dockerfile | `docker compose up --build` |
| Lancement en arrière-plan | `docker compose up --build -d` |
| Services renommés ou supprimés dans le Compose | `docker compose up --build --remove-orphans` |
| Arrêt simple (données conservées) | `docker compose down` |
| Reset complet (données effacées) | `docker compose down -v` puis `docker compose up --build` |
