<?php
// install.php - Script d'instal·lació automàtica

$message = '';
$status = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = 'gestio_explotacio';

    try {
        // 1. Connectar sense seleccionar BD per poder crear-la si no existeix
        $dsn_no_db = "mysql:host=$db_host;charset=utf8mb4";
        $pdo = new PDO($dsn_no_db, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // 2. Crear la base de dades si no existeix
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");

        // 3. Llegir i executar l'esquema SQL
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("No s'ha trobat el fitxer database/schema.sql");
        }
        $sql = file_get_contents($schemaFile);
        
        // Executar consultes múltiples
        $pdo->exec($sql);

        // 4. Crear l'usuari Administrador
        $adminEmail = 'admin@example.com';
        $adminPass = 'admin123';
        $adminHash = password_hash($adminPass, PASSWORD_DEFAULT);

        // Verificar si ja existeix (per si es re-executa)
        $stmt = $pdo->prepare("SELECT id_usuari FROM usuaris WHERE email = ?");
        $stmt->execute([$adminEmail]);
        
        if (!$stmt->fetch()) {
            $stmtInsert = $pdo->prepare("INSERT INTO usuaris (nom, cognoms, email, contrasenya_hash, rol) VALUES (?, ?, ?, ?, ?)");
            $stmtInsert->execute(['Administrador', 'Sistema', $adminEmail, $adminHash, 'admin']);
        }

        $message = "Instal·lació completada amb èxit! La base de dades s'ha creat i l'usuari administrador està llest.";
        $status = 'success';

    } catch (PDOException $e) {
        $message = "Error de Base de Dades: " . $e->getMessage();
        $status = 'error';
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $status = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instal·lació Gestió Explotació</title>
    <link rel="stylesheet" href="css/styles.css?v=3">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: var(--bg-body);
        }
        .install-card {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .logo-install {
            height: 60px;
            margin-bottom: 1.5rem;
        }
        .status-message {
            margin: 1rem 0;
            padding: 1rem;
            border-radius: 0.5rem;
        }
        .success { background-color: #d1fae5; color: #065f46; }
        .error { background-color: #fee2e2; color: #991b1b; }
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body>
    <div class="install-card">
        <img src="img/logo.png" alt="Logo" class="logo-install">
        <h1>Instal·lador</h1>
        <p>Configuració automàtica de la base de dades.</p>

        <?php if ($message): ?>
            <div class="status-message <?php echo $status; ?>">
                <?php echo $message; ?>
            </div>
            <?php if ($status === 'success'): ?>
                <a href="login.html" class="btn btn-primary" style="display: inline-block; margin-top: 1rem;">Anar al Login</a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($status !== 'success'): ?>
        <form method="POST" style="margin-top: 2rem;">
            <div class="form-group">
                <label>Servidor Base de Dades</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            <div class="form-group">
                <label>Usuari BD (XAMPP per defecte: root)</label>
                <input type="text" name="db_user" value="root" required>
            </div>
            <div class="form-group">
                <label>Contrasenya BD (XAMPP per defecte: buit)</label>
                <input type="password" name="db_pass" placeholder="Deixa buit si no en té">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Instal·lar Ara</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
