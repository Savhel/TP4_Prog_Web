# Vegefoods API (Laravel)

Backend Laravel 12 qui alimente le site e-commerce statique situé dans `../programmation_web`.  
Il expose des endpoints REST pour le catalogue, les articles de blog, la création de commandes et le formulaire de contact.

---

## 1. Prérequis

- PHP 8.2+
- Composer
- SQLite (par défaut) ou MySQL/PostgreSQL
- Node 18+ (uniquement si vous souhaitez utiliser Vite)

---

## 2. Installation rapide

```bash
cd vegefoods-backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed   # crée le catalogue/les articles de démonstration
php artisan serve            # API disponible sur http://127.0.0.1:8000
```

> Par défaut, `.env` utilise SQLite : un fichier `database/database.sqlite` sera créé si vous exécutez `touch database/database.sqlite`.

---

## 3. Jeu de données de démo

Le seeder `CatalogSeeder` crée :

- 5 catégories (paniers, fruits, légumes, jus, épicerie)  
- 9 produits avec images, tags, valeurs nutritionnelles et avis clients  
- 4 articles de blog et 3 témoignages utilisés sur la page d’accueil

Les fichiers front (`programmation_web/*.html`) utilisent déjà ces slugs : par exemple `panier-vitamine-8kg`, `fraises-bafoussam`, etc.

---

## 4. API disponible

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/api/health` | Vérification rapide |
| GET | `/api/catalog/categories` | Liste des catégories |
| GET | `/api/catalog/products` | Catalogue (filtres : `category`, `search`, `tags`, `featured`, `paginate=false`, `limit`, `per_page`) |
| GET | `/api/catalog/products/{slug}` | Fiche produit (images, avis, nutrition) |
| GET | `/api/catalog/products/{slug}/related` | 4 produits associés |
| GET | `/api/blog/posts` | Articles (filtres `category`, `search`, `per_page`, `paginate=false`) |
| GET | `/api/blog/posts/{slug}` | Article détaillé |
| GET | `/api/content/highlights` | Données page d’accueil (stats, catégories, best-sellers, témoignages) |
| POST | `/api/orders` | Création d’une commande depuis le panier |
| POST | `/api/contact` | Formulaire “Contact / Devenir partenaire” |

### Exemple : filtrer la boutique

```bash
curl "http://127.0.0.1:8000/api/catalog/products?category=fruits&limit=4"
```

### Exemple : créer une commande

```bash
curl -X POST http://127.0.0.1:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
        "customer": {
          "name": "Claudia",
          "email": "claudia@example.com",
          "phone": "+237600000000",
          "city": "Yaoundé",
          "address": "Quartier Bastos"
        },
        "payment": {"method": "orange-money"},
        "cart": {
          "items": [
            {"product_slug": "panier-vitamine-8kg", "quantity": 1},
            {"product_slug": "jus-green-glow", "quantity": 3}
          ]
        }
      }'
```

La réponse (201) renvoie le récapitulatif (`reference`, `totals`, `items`).

---

## 5. Raccorder le front statique

1. **Catalogue / Boutique (`shop.html`, `index.html`)**
   - Remplacer les données statiques par des appels `fetch('/api/catalog/products?...')`.
   - Pour les cartes catégories/produits en vedette de la home, consommer `/api/content/highlights`.

2. **Fiche produit (`product-single.html`)**
   - Charger `/api/catalog/products/{slug}` pour récupérer description longue, galerie, nutrition et avis.
   - Charger `/api/catalog/products/{slug}/related` pour la section “Vous aimerez aussi”.

3. **Panier & Checkout (`cart.html`, `checkout.html`)**
   - Conserver le panier dans `localStorage`.
   - À la validation, poster le payload au format `POST /api/orders` (voir exemple ci-dessus). Utiliser la réponse pour afficher le numéro de commande.

4. **Blog (`blog.html`, `blog-single.html`)**
   - Liste : `/api/blog/posts?per_page=6`.
   - Détail : `/api/blog/posts/{slug}`.

5. **Contact (`contact.html`)**
   - Poster le formulaire vers `/api/contact`. Les champs du HTML correspondent aux clés `full_name`, `company`, `email`, `phone`, `topic`, `message`, `accepts_marketing`.

Astuce : centraliser l’URL de l’API dans un fichier JS (`const API_BASE = "http://127.0.0.1:8000/api";`) pour basculer facilement en production.

---

## 6. Tests rapides

```bash
# Lancer les migrations en mémoire
php artisan migrate --env=testing

# Exécuter la suite
php artisan test
```

(Les tests d’exemple ne sont pas encore écrits, mais la structure est prête.)

---

## 7. Aller plus loin

- Ajouter l’authentification Laravel Breeze/Jetstream si vous avez besoin d’un back-office.
- Mettre en place un vrai transport d’e-mails (Mailgun, SES) pour notifier les nouvelles commandes et messages.
- Brancher un provider de paiement (Orange Money / MTN) en utilisant la colonne `payment_method`.

## 8. Interface d’administration

Une console Tailwind est disponible sur `http://127.0.0.1:8000/admin` (ou votre domaine). Elle propose :

- un tableau de bord avec les derniers messages et commandes,
- les CRUD pour les produits, catégories et articles de blog,
- la consultation des commandes avec mise à jour du statut,
- la modération des avis et des messages de contact.

Identifiants par défaut créés par le seeder :

```
Email : admin@vegefoods.cm
Mot de passe : admin123
```

## 9. Front statique connecté

Le dossier `programmation_web` consomme désormais l’API Laravel :

- `js/main.js` charge le catalogue, les fiches produits, les posts de blog et envoie les formulaires (contact + commandes) vers `POST /api/contact` et `POST /api/orders`.
- Le formulaire “Suggestions du jeudi” poste désormais vers `POST /api/newsletter` et stocke l’email dans la table `newsletter_subscribers` tout en envoyant un mail de bienvenue (driver `log`).
- La valeur par défaut de l’API est `http://127.0.0.1:8000/api`. Pour la modifier, définissez `window.VEGEFOODS_API_BASE` avant d’inclure `main.js`.

Servez simplement les fichiers HTML (via `php artisan serve` + `npx serve programmation_web`) pour tester la synchronisation en temps réel avec le back-office.
