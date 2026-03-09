<?php
/**
 * Script de récupération d'une excuse aléatoire
 * Basé sur une thématique envoyée via formulaire POST
 */

// 1. Paramètres de configuration pour la connexion à la base
$hote = 'localhost';
$bdd  = 'excuses'; 
$utilisateur = 'sio';
$mdp = 'azertysio';

// Initialisation des variables d'affichage pour éviter les erreurs "undefined variable" dans le HTML
$excuse_a_afficher = "";
$titre_affichage = "";

try {
    // 2. Création de la chaîne de connexion et des options de sécurité
    $dsn = "mysql:host=$hote;dbname=$bdd;charset=utf8mb4";
    $config = [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", // Force l'UTF8 pour les accents
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Active les alertes en cas d'erreur SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // Récupère les données sous forme de tableau associatif
    ];
    
    // Tentative de connexion avec l'objet PDO
    $pdo = new PDO($dsn, $utilisateur, $mdp, $config);

    // 3. Vérification : est-ce qu'une thématique a été envoyée par le formulaire ?
    if (isset($_POST['thematique'])) {
        $idTheme = $_POST['thematique'];

        // Préparation de la requête pour éviter les injections SQL (sécurité moteur !)
        // On trie par hasard (RAND) et on ne prend qu'une seule ligne (LIMIT 1)
        $requete = $pdo->prepare("SELECT excuse FROM Excuses WHERE idThematique = ? ORDER BY RAND() LIMIT 1");
        
        // Exécution de la requête en injectant l'ID du thème
        $requete->execute([$idTheme]);
        
        // Récupération du résultat (la ligne de l'excuse)
        $resultat = $requete->fetch();

        $titre_affichage = "Ton excuse :";

        // Si la base a renvoyé quelque chose
        if ($resultat) {
            // On protège le texte contre les failles XSS avec htmlspecialchars
            $excuse_a_afficher = htmlspecialchars($resultat['excuse']);
        } 
        else {
            // Cas où l'ID du thème existe mais n'a pas d'excuses associées
            $excuse_a_afficher = "Navré, aucune excuse n'a été trouvée pour cette catégorie.";
        }
    } 
    else {
        // Cas où l'utilisateur arrive sur la page sans passer par le formulaire
        $titre_affichage = "Erreur";
        $excuse_a_afficher = "Aucune thématique n'a été sélectionnée.";
    }

} catch (PDOException $erreur) {
    // En cas de panne moteur (connexion échouée, table inexistante, etc.)
    // On affiche le détail technique pour débugger
    $message = 'ERREUR PDO dans ' . $erreur->getFile() . ' : ' . $erreur->getLine() . ' : ' . $erreur->getMessage();
    die($message);
}
?>

<!DOCTYPE html>
<html lang="fr-fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur d'Excuses</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --bg-color: #f8fafc;
            --text-color: #1e293b;
            --card-bg: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container_excuse {
            background: var(--card-bg);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 500px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        h1 {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .texte_excuse {
            font-weight: 400;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
            font-style: italic;
        }

        .btn-retour {
            display: inline-block;
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-retour:hover {
            background-color: var(--primary-hover);
            transform: scale(1.02);
        }
    </style>
</head>
<body>

    <div class="container_excuse">
        <h1><?php echo $titre_affichage; ?></h1>
        
        <p class="texte_excuse">
            "<?php echo $excuse_a_afficher; ?>"
        </p>

        <a href="../html/index.html" class="btn-retour">
            Tu veux une nouvelle excuse ?
        </a>
    </div>

</body>
</html>