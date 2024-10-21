
<?php
	session_start();
	if (!isset($_SESSION['user'])) {
		header("Location: ../index.php");
		exit();
	}

	include ('variables.php');
	include ('Classes/Tools.php');

	$pdo = Tools::connect_database();
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Institut du Savoir - Upload de Fichier CSV</title>
        <link rel="icon" href="../Assets/favicon.png"> 
		<link rel="stylesheet" href="../CSS/Font.css"> 
        <link rel="stylesheet" href="../CSS/Upload.css"> 
    </head>
    <body>
        <?php
            include('header.php');
        ?>

        <main>

<?php
	if (isset($_POST['csv_file_path'])) {
		// Récupération du chemin du fichier CSV
		$csv_file = $_POST['csv_file_path'];

		if (($handle = fopen($csv_file, "r")) !== FALSE) {
			// Ignorer la première ligne si c'est une ligne d'en-tête
			fgetcsv($handle);

			// Parcourir les lignes du fichier CSV
			while (($data = fgetcsv($handle)) !== FALSE) {
				if (!empty(array_filter($data))) {
					$first_name =  Tools::sanitize_name($data[0]);
					$last_name =  Tools::sanitize_name($data[1]);
					$email = Tools::sanitize_name_lite( strtolower( $data[2] ) );
					$teacher_name =  Tools::sanitize_name($data[3]);
					$subject = Tools::sanitize_name_lite($data[4]);

					// Stocker dans la base de données
					$query = "INSERT INTO Certificats (First_Name, Last_Name, Mail, Teacher_Name, Subject, Sent_Status) 
							VALUES (?, ?, ?, ?, ?, 0)";
					$stmt = $pdo->prepare($query);
					$stmt->execute([$first_name, $last_name, $email, $teacher_name, $subject]);
				}
			}
			fclose($handle);

			echo '<div style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
					<div style="font-size: 40px; color: green;">✅</div>
					<h2 style="color: green;">Données enregistrées avec succès. Les certificats seront envoyés progressivement.</h2>
					<a href="Upload.php" style="margin-top: 20px; text-decoration: none;">
						<button style="padding: 10px 20px; font-size: 18px; background-color: blue; color: white; border: none; border-radius: 5px; cursor: pointer;">
							Envoyer un autre fichier
						</button>
					</a>
					<br>Ou<br>
					<a href="logout.php" style="margin-top: 20px; text-decoration: none;">
						<button style="padding: 10px 20px; font-size: 18px; background-color: red; color: white; border: none; border-radius: 5px; cursor: pointer;">
							Se déconnecter
						</button>
						
					</a>
				  </div>'; 

		} else {
			echo '<div style="display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;">
					<div style="font-size: 40px; color: red;">🛑</div>
					<h2 style="color: red;">Erreur d\'ouverture du fichier</h2>
					<a href="Upload.php" style="margin-top: 20px; text-decoration: none;">
						<button style="padding: 10px 20px; font-size: 18px; background-color: blue; color: white; border: none; border-radius: 5px; cursor: pointer;">
							Retour
						</button>
					</a>
				 </div>'; 
		}

		Tools::remove_files_from_upload();

	} else {
		echo "<h3></h3>";
	}

	$pdo = null;

?>
        </main>
    </body>
</html>