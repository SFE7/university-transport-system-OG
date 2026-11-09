<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title>Identifiants Chauffeur</title>
</head>
<body>
  <p>Bonjour {{ $name }},</p>

  <p>Un compte chauffeur a été créé pour vous sur <strong>Covoiturage Universitaire</strong>. Voici vos identifiants temporaires :</p>

  <ul>
    <li><strong>Email :</strong> {{ $email }}</li>
    <li><strong>Mot de passe temporaire :</strong> {{ $password }}</li>
  </ul>

  <p>Veuillez vous connecter et changer immédiatement votre mot de passe.</p>

  <p>Si vous n'attendiez pas cet email, contactez l'administrateur.</p>

  <p>Cordialement,<br/>L'équipe Covoiturage Universitaire</p>
</body>
</html>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identifiants chauffeur</title>
</head>
<body>
    <p>Bonjour {{ $name }},</p>
    <p>Votre compte chauffeur de bus a été activé.</p>
    <p>Email: {{ $email }}</p>
    <p>Mot de passe temporaire: {{ $plainPassword }}</p>
    <p>Veuillez vous connecter puis changer ce mot de passe dès votre première connexion.</p>
</body>
</html>
