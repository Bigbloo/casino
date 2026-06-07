<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hors ligne - Gaming Platform</title>
    <link rel="stylesheet" href="/minimal/css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #0f172a; font-family: 'Inter', sans-serif; }
        .offline-card { text-align: center; background: #1a2035; border-radius: 16px; padding: 48px 40px; border: 1px solid #2a3a5c; max-width: 420px; }
        .offline-icon { font-size: 72px; margin-bottom: 24px; }
        h1 { color: #f1f5f9; font-size: 26px; margin-bottom: 12px; }
        p { color: #94a3b8; font-size: 15px; line-height: 1.6; margin-bottom: 28px; }
        .btn { display: inline-block; background: #6366f1; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; cursor: pointer; border: none; font-size: 15px; }
        .btn:hover { background: #4f46e5; }
    </style>
</head>
<body>
    <div class="offline-card">
        <div class="offline-icon">📡</div>
        <h1>Vous êtes hors ligne</h1>
        <p>Vérifiez votre connexion Internet et réessayez. Certaines fonctionnalités peuvent être disponibles en mode hors ligne.</p>
        <button class="btn" onclick="window.location.reload()">Réessayer</button>
    </div>
</body>
</html>
