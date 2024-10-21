<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}


include ('Classes/Tools.php');

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
            <h2>Télécharger un fichier CSV</h2>
            <form action="" method="POST" enctype="multipart/form-data">
                <input type="file" name="file" accept=".csv" required>
                <br><br>
                <input type="submit" name="upload" value="Télécharger">
            </form>
            
            <?php
            if (isset($_POST['upload'])) {
                $delete_csv = true;
                if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
                    $csv_file_tmp = $_FILES['file']['tmp_name'];
                    $csv_file_name = basename($_FILES['file']['name']);
                

                    // Déplacer le fichier vers un répertoire sécurisé
                    $upload_dir = '../Uploads/';
                    $csv_file = $upload_dir . $csv_file_name;

                    // Créer le dossier 'uploads' s'il n'existe pas
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    // Déplacer le fichier du répertoire temporaire vers le répertoire 'uploads'
                    if (move_uploaded_file($csv_file_tmp, $csv_file)) {
                        $_SESSION['uploaded_file'] = $csv_file;

                        //Gestion des erreurs
                        $error_messages = array();
                        //Rendu du tableau
                        $table_output = '';
                        $students = [];

                        // Lire et traiter le fichier CSV
                        if (($handle = fopen($csv_file, "r")) !== FALSE) {
                            $table_output .= "<h3>Aperçu du fichier</h3>";
                            $table_output .= "<table>";
                            $table_output .= "<tr><th>Prénom</th><th>Nom</th><th>Email</th><th>Enseignant</th><th>Matière</th></tr>";

                            fgetcsv($handle); // Ignorer la première ligne (en-têtes)
                            
                            $num_ligne = 1;
                            
                            while (($data = fgetcsv($handle)) !== FALSE) {
                                $num_ligne++;
                                
                                // Vérifier si la ligne est complètement vide (toutes les colonnes sont vides)
                                if (count(array_filter($data)) == 0) {
                                    continue; // Ignorer cette ligne vide et passer à la suivante
                                }

                                // Vérifier si la ligne contient des espaces (données vides)
                                if(isset($data[0]) && isset($data[1]) && isset($data[2]) && isset($data[3]) && isset($data[4])) {
                                    if (trim($data[0]) == '' || trim($data[1]) == '' || trim($data[2]) == '' || trim($data[3]) == '' || trim($data[4]) == '' ) {
                                        $error_messages[] = "Ligne $num_ligne : il manque des données";
                                    }
                                }
                                                 
                                if (count($data) != 5 || in_array("", $data)) {
                                    // Vérifier s'il manque des données dans la ligne (certaines cellules vides)
                                    $error_messages[] = "Ligne $num_ligne : il devrait y avoir 5 colonnes de données";
                                }

                                if( !Tools::validate_email( Tools::sanitize_name_lite( strtolower( $data[2] ) ) ) ) {
                                    // Vérifier si l'adresse mail est valide
                                    $error_messages[] = "Ligne $num_ligne : Format de l'adresse e-mail invalide $data[2]";
                                }
                                
                                $table_output .= "<tr>";
                                foreach ($data as $k=>$cell) {
                                    $table_output .= "<td>";
                                    if($k==2) // mail
                                        $table_output .= Tools::sanitize_name_lite( strtolower( $cell ) );
                                    elseif($k==4) // matière, peut contenir des majuscule sur certains mots donc pas de changement de casse
                                        $table_output .= Tools::sanitize_name_lite( $cell );
                                    else
                                        $table_output .= Tools::sanitize_name($cell);
                                    $table_output .= "</td>";
                                }
                                $table_output .= "</tr>";

                                // Stocker les données dans un tableau
                                $students[] = $data;
                                
                            }

                            $table_output .= "</table>";
                            fclose($handle);

                            // Stocker les étudiants dans la session
                            $_SESSION['students'] = $students;

                            // Redirection vers `process_pre_upload.php`
                            if( !empty($error_messages) ) {
                                echo '<h2>⚠️ Ton fichier comporte des erreurs</h2>';
                                echo '<div class="error-message">';
                                foreach($error_messages as $error_message) {
                                    echo '❗ ' . $error_message . '</br>';
                                }
                                echo '</div>';
                            }elseif ( empty($students) ) {
                                echo '<h2 class="error-message">⚠️ Le fichier est vide</h2>';
                            } else {
                                $delete_csv = false;
                                echo $table_output;
                                echo '<form action="process_pre_upload.php" method="POST">
                                        <input type="hidden" name="csv_file_path" value="' . htmlspecialchars($csv_file) . '">
                                        <button type="submit" class="upload-button">Envoyer les attestations</button>
                                      </form>';
                            
                            }
                        }
                    } else {
                        echo '<h2>⚠️ Erreur : Échec du téléchargement du fichier</h2>';
                    }
                } else {
                    echo '<h2>⚠️ Erreur : Veuillez sélectionner un fichier CSV à uploader</h2>';
                }
            
                if($delete_csv)
                    Tools::remove_files_from_upload();
            }

            ?>
        </main>
    </body>
</html>
