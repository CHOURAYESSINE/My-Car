# My-Car sur Vercel

L'application conserve ses pages PHP, le catalogue de voitures et d'accessoires, les articles, les stories et la galerie. Elle utilise PHP 8.3 avec vercel-php@0.7.4. Le build copie uniquement les ressources publiques ; le routeur protège les sources PHP et les fichiers SQL.

Configurer DATABASE_URL et une APP_KEY aléatoire dans les variables sensibles de Vercel. La base PostgreSQL utilise le schéma isolé my_car. Initialiser une fois depuis PHP avec PDO PostgreSQL : ADMIN_PASSWORD=... php scripts/initialize.php. Le compte administrateur créé est admin@mycar.com. Son mot de passe ne figure pas dans le dépôt.

Les comptes et les paniers sont partagés dans la base. Les sessions sont chiffrées et conservées dans PostgreSQL. Les commandes sont enregistrées dans une transaction avec les prix réels du catalogue et le compte connecté ; elles conservent une copie des articles même si le catalogue est modifié ensuite. Le site enregistre des demandes de commande ; aucun paiement bancaire n'est traité.

Les images ajoutées depuis l'administration sont conservées dans PostgreSQL et accessibles sous /assets. Formats JPEG, PNG, GIF ou WebP, maximum 2 Mo par image. Les images fournies dans le dépôt restent statiques.

Le catalogue et les contenus de démonstration du dépôt sont importés, avec coordonnées de contact de démonstration. Les anciens utilisateurs, mots de passe et commandes du fichier SQL d'origine ne sont pas importés.

Les détails des voitures et accessoires sont consultables sans connexion. L'ajout au panier et les commandes exigent un compte. L'administration exige un rôle administrateur. Les écritures utilisent des formulaires POST avec jetons CSRF et des requêtes préparées ; les tentatives de connexion sont limitées.
