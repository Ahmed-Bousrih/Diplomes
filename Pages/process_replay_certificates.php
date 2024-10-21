<?php
    session_start();

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    // Domaine de confiance (le domaine qui est autorisé à envoyer des requêtes POST)
    // $trusted_domain = 'institut-du-savoir.com';

    // if (isset($_SERVER['HTTP_REFERER'])) {
    //      $referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        
    //     // Vérifie si le domaine référent contient le domaine de confiance
    //      if (strpos($referer, 'institut-du-savoir.com') === false) {
    //         die("Accès refusé : requête non autorisée.");
    //      }
    //  } else {
    //      die("Accès refusé : domaine inconnu.");
    //  }
     
    require '../fpdf/fpdf.php';
    require 'variables.php'; 
    include ('Classes/Mailer.php');
    include ('Classes/CertificateGenerator.php');
    include ('Classes/Tools.php');
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    // Vérifier que toutes les données POST sont présentes
    if (!isset($_POST['student_firstname'], $_POST['student_lastname'], $_POST['student_email'], $_POST['teacher_name'], $_POST['subject'])) {
        echo "Echec: Données manquantes";
        exit;
    }
    
    // Récupérer les données du formulaire POST
    $student_firstname = Tools::sanitize_name($_POST['student_firstname']);
    $student_lastname = Tools::sanitize_name($_POST['student_lastname']);
    $student_email = Tools::sanitize_name_lite( strtolower( $_POST['student_email'] ) );
    $teacher_name = Tools::sanitize_name($_POST['teacher_name']);
    $subject = mb_strtoupper( Tools::sanitize_name($_POST['subject']) , 'UTF-8' );
    $institute_email = INSTITUTE_MAIL;
    $color = [105, 17, 15];
    
    $template_direct_path = '../Assets/Certificates/replay.jpg'; 
    
    // Générer le certificat
    $generator = new CertificateGenerator();
    $temp_image_direct = $generator->generate_certificate($student_firstname, $student_lastname, $teacher_name, $subject, $template_direct_path, $color);
    
    $pdf_direct = new FPDF(); 
    $pdf_direct->AddPage('L');
    $pdf_direct->Image($temp_image_direct, 0, 0, 297, 210);
    
    $directory = '../Certificats_Replay/';
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    // Créer le nom de fichier pour le certificat
    $output_pdf_direct = uniqid('IDS-ATTESTATION-') . '.pdf';
    
    // Créer le PDF
    $pdf_direct->Output('F', $output_pdf_direct);
    unlink($temp_image_direct); // Supprimer l'image temporaire
    
    // Validation de l'email
    if (!filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
        echo "Echec: Email invalide.";
        exit;
    }
    
    // Envoyer le certificat par email
    $mailer = new Mailer();
    $message = "<p>Salam alaykoum wa rahmathullah wa barakatuh</p>
    <p>Ci-joint votre attestation de complétion de séminaire.</p>
    <p>Avec nos plus sincères félicitations, nous vous souhaitons une bonne continuation.</p>
    <p>Respectueusement</p>
    <p>L'équipe Omra & Institut du savoir</p>";
    
    // Envoyer le diplôme
    $mailer->send_certificate($student_email, $student_firstname, $student_lastname, $output_pdf_direct, $message, $subject);
?>
