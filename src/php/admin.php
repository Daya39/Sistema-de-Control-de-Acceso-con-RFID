<?php
session_start();
$db = new mysqli('db', 'root', 'example', 'rfid_access');

// Gestión de exportación CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="registros_acceso_'.date('Y-m-d').'.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'UID de la tarjeta', 'Estado', 'Fecha y hora'], ';');
    
    $logs = $db->query("SELECT * FROM logs ORDER BY timestamp DESC");
    while ($row = $logs->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['uid'],
            $row['access_status'] === 'GRANTED' ? 'Accedido' : 'Rechazado',
            $row['timestamp']
        ], ';');
    }
    fclose($output);
    exit;
}

// Autenticación de administrador
if (isset($_POST['login'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
        $_SESSION['loggedin'] = true;
    } else {
        $error = "¡Credenciales incorrectas!";
    }
}

// Cierre de sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// Agregar una tarjeta
if (isset($_POST['add_badge'])) {
    $uid = $db->real_escape_string($_POST['new_uid']);
    $name = $db->real_escape_string($_POST['user_name']);
    $db->query("INSERT INTO users (uid, name) VALUES ('$uid', '$name')");
    $success = "¡Tarjeta agregada con éxito!";
}

// Eliminar una tarjeta
if (isset($_POST['delete_badge'])) {
    $id = intval($_POST['delete_id']);
    $db->query("DELETE FROM users WHERE id = $id");
    $success = "¡Tarjeta eliminada con éxito!";
}

// Control manual de la puerta
if (isset($_POST['open_door'])) {
    file_put_contents('door_command.txt', 'OPEN');
    $success = "¡Comando de apertura enviado!";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administración RFID</title>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }

        .container {
            margin-left: 240px;
            padding: 30px;
        }

        nav.sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 220px;
            height: 100vh;
            background-color: var(--dark);
            color: white;
            padding: 20px;
        }

        nav.sidebar h2 {
            font-size: 20px;
            margin-bottom: 30px;
        }

        nav.sidebar ul {
            list-style: none;
            padding: 0;
        }

        nav.sidebar li {
            margin-bottom: 20px;
        }

        nav.sidebar a {
            color: white;
            text-decoration: none;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        h1 {
            color: var(--primary);
        }

        h2 {
            color: var(--secondary);
            margin-top: 30px;
        }

        h3 {
            margin-top: 20px;
            color: var(--gray);
        }

        .btn {
            background: var(--primary);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background: var(--secondary);
        }

        .btn-danger {
            background: var(--danger);
        }

        .btn-danger:hover {
            background: #d1144a;
        }

        .btn-success {
            background: var(--success);
        }

        .btn-success:hover {
            background: #2ab4d6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: var(--primary);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .section {
            margin-bottom: 50px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        input[type="text"], input[type="password"] {
            padding: 10px;
            width: 300px;
            margin-bottom: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['loggedin'])): ?>
    <div class="login-container">
        <h2>Inicio de Sesión Administrador</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Nombre de usuario" required><br>
            <input type="password" name="password" placeholder="Contraseña" required><br>
            <button type="submit" name="login" class="btn">Iniciar sesión</button>
        </form>
    </div>
<?php else: ?>

    <!-- BARRA LATERAL -->
    <nav class="sidebar">
        <h2>Menú Admin</h2>
        <ul>
            <li><a href="#badges">🎫 Tarjetas</a></li>
            <li><a href="#logs">📋 Historial</a></li>
            <li><a href="#control">🔓 Control</a></li>
            <li><a href="?logout" class="btn btn-danger btn-sm">Cerrar sesión</a></li>
        </ul>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container">
        <header>
            <h1>Control de Acceso RFID</h1>
        </header>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="section" id="badges">
            <h2>Gestión de tarjetas</h2>
            <form method="POST">
                <input type="text" name="new_uid" placeholder="UID de la tarjeta" required>
                <input type="text" name="user_name" placeholder="Nombre del usuario" required>
                <button type="submit" name="add_badge" class="btn btn-success">Agregar tarjeta</button>
            </form>

            <h3>Tarjetas registradas</h3>
            <table>
                <thead>
                    <tr><th>ID</th><th>UID</th><th>Nombre</th><th>Fecha</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php
                    $badges = $db->query("SELECT * FROM users ORDER BY created_at DESC");
                    while ($row = $badges->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['uid'] ?></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('¿Eliminar esta tarjeta?');">
                                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="delete_badge" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="section" id="logs">
            <h2>Historial de accesos</h2>
            <a href="?export" class="btn">📥 Exportar a CSV</a>
            <table>
                <thead>
                    <tr><th>ID</th><th>UID</th><th>Estado</th><th>Fecha/Hora</th></tr>
                </thead>
                <tbody>
                    <?php
                    $logs = $db->query("SELECT * FROM logs ORDER BY timestamp DESC LIMIT 100");
                    while ($row = $logs->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['uid'] ?></td>
                        <td style="color: <?= $row['access_status'] === 'GRANTED' ? 'green' : 'red' ?>;">
                            <?= $row['access_status'] === 'GRANTED' ? 'Acceso concedido' : 'Acceso denegado' ?>
                        </td>
                        <td><?= $row['timestamp'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="section" id="control">
            <h2>Control manual</h2>
            <form method="POST" onsubmit="return confirm('¿Abrir la puerta ahora?');">
                <button type="submit" name="open_door" class="btn">🔓 Abrir la puerta</button>
            </form>
        </div>
    </div>
<?php endif; ?>
</body>
</html>
