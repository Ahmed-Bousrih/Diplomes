<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Connexion</title>
		<link rel="icon" href="Assets/favicon.png"> 
		<link rel="stylesheet" href="../CSS/Font.css"> 
		<link rel="stylesheet" href="CSS/Login.css"> 
	</head>
	<body>
		<div class="container">
			<img src="Assets/logo.svg" alt="Logo de l'Institut du Savoir" class="logo"> 
			<h2>Connexion</h2>
			<form method="POST" action="">
				<div class="input-group">
					<label for="email">Adresse e-mail :</label>
					<input type="email" id="email" name="email" required>
				</div>
				<div class="input-group">
					<label for="password">Mot de passe :</label>
					<input type="password" id="password" name="password" required>
				</div>
				<button type="submit" name="login">Se connecter</button>
			</form>

			<?php
				session_start();
				if (isset($_SESSION['user'])) {
					header("Location: ./Pages/Upload.php");
					exit();
				}
				$stored_hashed_password = '$2y$10$qANqmqOuk1JctwBU.CTAROo82MkyQ6hxLsIMmtREA2N/ka8ogHDpG'; 
				$stored_email = 'diplomesids@gmail.com'; 
				
				// Vérifier si l'utilisateur soumet le formulaire
				if (isset($_POST['login'])) {
					$email = $_POST['email'];
					$password = $_POST['password'];
				
					// Vérifier que l'email correspond à celui enregistré
					if ($email === $stored_email) {
						// Vérifier le mot de passe saisi contre le mot de passe chiffré
						if (password_verify($password, $stored_hashed_password)) {
							// Si la vérification du mot de passe est réussie, créer une session et rediriger
							$_SESSION['user'] = $email;
							header("Location: /Pages/Upload.php"); 
							exit();
						} else {
							echo "<p style='color:red;'>Mot de passe incorrect.</p>";
						}
					} else {
						echo "<p style='color:red;'>Adresse e-mail incorrecte.</p>";
					}
				}
			?>
		</div>
	</body>
</html>