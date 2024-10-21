<?php

	include ('variables.php');
	include ('Classes/CertificateGenerator.php');
	include ('Classes/Mailer.php');
	include ('Classes/Tools.php');
	require('../fpdf/fpdf.php');

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	$pdo = Tools::connect_database();

	// Vérifier que la clé d'authentification est présente et correcte
	if (!isset($_GET['secret_key']) || $_GET['secret_key'] !== SECRET_KEY) {
		die("Erreur d'authentification : clé non valide.");
	}

	// Récupérer la dernière exécution
	$query = "SELECT last_execution_time AS last_execution FROM Cron_Execution_Log WHERE id = 3";
	$stmt = $pdo->query($query);
	$last_execution = $stmt->fetch(PDO::FETCH_ASSOC)['last_execution'];

	// Vérifier si 5 minutes se sont écoulées
	if ($last_execution && strtotime($last_execution) > (time() - 300)) {
		exit();
	}

	$query = "SELECT * FROM Certificats WHERE Sent_Status = 0 LIMIT 10";
	$stmt = $pdo->query($query);
	$students_to_process = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if (empty($students_to_process)) {	
		exit();
	}

	// Envoi des mails avec les certificats
	foreach ($students_to_process as $student) {
		$student_firstname = Tools::sanitize_name($student['First_Name']);
		$student_lastname = Tools::sanitize_name($student['Last_Name']);
		$teacher_name = Tools::sanitize_name($student['Teacher_Name']) ;
		$subject = mb_strtoupper( Tools::sanitize_name($student['Subject']) , 'UTF-8' );
		$email = Tools::sanitize_name_lite( strtolower( $student['Mail'] ) );
		$color = [25, 25, 75];
		$student_id = $student['id'];

		$template_base = '../Assets/Certificates/direct.jpg';
		$generator = new CertificateGenerator();
		
		$certificate_path = $generator->generate_certificate($student_firstname, $student_lastname, $teacher_name, $subject, $template_base,$color);

		$directory = '../Certificats/';
		if (!is_dir($directory)) {
			mkdir($directory, 0755, true);
		}

		$certificate_pdf_path = '../Certificats/' . uniqid('IDS-ATTESTATION-') . '.pdf';

		// Générer le PDF à partir de l'image du certificat
		$pdf = new FPDF();
		$pdf->AddPage('L'); // mode Paysage
		$pdf->Image($certificate_path, 0, 0, 297, 210);
		$pdf->Output($certificate_pdf_path, 'F'); 

		// Supprimer l'image temporaire du certificat
		unlink($certificate_path);

		$mailer = new Mailer();
		$message= "<p>Salam alaykoum wa rahmathullah wa barakatuh</p>
		<p>Ci-joint votre attestation de réussite de séminaire.</p>
		<p>Avec nos plus sincères félicitations, nous vous souhaitons une bonne continuation.</p>
		<p>Respectueusement,</p>
		<p>L'équipe Omra & Institut du savoir</p>";

		// Utiliser la méthode send_certificate pour envoyer un email
		$mailer->send_certificate($email, $student_firstname, $student_lastname, $certificate_pdf_path,$message,$subject);
		$sql = "UPDATE Certificats SET Sent_Status = 1 WHERE id = :student_id";
		$stmt = $pdo->prepare($sql); 
		// Lie l'ID de l'étudiant au paramètre de la requête
		$stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
		$stmt->execute();
	}
	$update_query = "UPDATE Cron_Execution_Log SET last_execution_time = NOW() WHERE id = 3";
	$pdo->exec($update_query);

    $pdo = null;
?>
