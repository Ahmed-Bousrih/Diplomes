<?php
	// Inclusion de PHPMailer
	require '../PHPMailer/src/PHPMailer.php';
	require '../PHPMailer/src/SMTP.php';
	require '../PHPMailer/src/Exception.php';
	// require 'variables.php';
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	class Mailer {
		public $mail_host;
		public $mail_username;
		public $mail_password;
		public $mail_port;
		public $mail_charset = 'UTF-8';
		public $mail_from;
		public $mail_from_name;
		public $mail_bcc;

		// Constructeur pour initialiser les variables
		public function __construct() {
			$this->mail_host = MAIL_HOST;
			$this->mail_username = MAIL_USERNAME;
			$this->mail_password = MAIL_PASSWORD;
			$this->mail_port = MAIL_PORT;
			$this->mail_from = MAIL_FROM;
			$this->mail_from_name = MAIL_FROM_NAME;
			$this->mail_bcc = INSTITUTE_MAIL;
		}

		// Fonction pour envoyer le certificat à l'étudiant
		public function send_certificate($email, $student_firstname, $student_lastname, $certificate_pdf_path,$message,$subject) {
			$mail = new PHPMailer(true);
			try {
				// Configuration de PHPMailer
				$mail->isSMTP();
				$mail->Host = $this->mail_host;
				$mail->SMTPAuth = true;
				$mail->Username = $this->mail_username;
				$mail->Password = $this->mail_password;
				$mail->SMTPSecure = 'tls';
				$mail->Port = $this->mail_port;

				// Expéditeur et destinataires
				$mail->setFrom($this->mail_from, $this->mail_from_name);
				$mail->addAddress($email);
				$mail->addBCC($this->mail_bcc);
				$mail->CharSet = $this->mail_charset;

				// Contenu de l'email
				$mail->Subject = 'Votre diplôme !';
				$mail->Body = $message;
				$mail->isHTML(true);

				// Pièce jointe
				$mail->addAttachment($certificate_pdf_path);

				// Envoyer l'email
				$mail->send();
				unlink($certificate_pdf_path);

			} catch (Exception $e) {
				// Gestion des erreurs lors de l'envoi du mail
				$this->send_error_notification($student_firstname, $student_lastname, $e->getMessage(),$subject,$email);
			}
		}

		// Fonction pour envoyer un mail d'erreur
		private function send_error_notification($student_firstname, $student_lastname, $error_message,$subject,$email) {
			$error_mail = new PHPMailer(true);

			try {
				// Configuration de PHPMailer pour l'email d'erreur
				$error_mail->isSMTP();
				$error_mail->Host = $this->mail_host;
				$error_mail->SMTPAuth = true;
				$error_mail->Username = $this->mail_username;
				$error_mail->Password = $this->mail_password;
				$error_mail->SMTPSecure = 'tls';
				$error_mail->Port = $this->mail_port;

				// Expéditeur et destinataire du mail d'erreur
				$error_mail->setFrom($this->mail_from, $this->mail_from_name);
				$error_mail->addAddress($this->mail_bcc);
				$error_mail->CharSet = $this->mail_charset;

				// Contenu de l'email d'erreur
				$error_mail->Subject = 'Erreur d\'envoi de certificat';
				$error_mail->Body = "Erreur lors de l'envoi du certificat pour {$student_firstname} {$student_lastname}<br> séminaire: {$subject}<br>E-mail: {$email}  <br>Détails: {$error_message}";
				$error_mail->isHTML(true);

				// Envoyer l'email d'erreur
				$error_mail->send();
			} catch (Exception $e) {
				// Si l'envoi de l'email d'erreur échoue, on peut loguer ou ignorer selon le besoin
			}
		}
	}
?>