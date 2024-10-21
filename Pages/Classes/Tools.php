<?php
	
	class Tools {

		public static function sanitize_filename($name) {
			$name = preg_replace('/[<>:"\/\\\|\?*\!\']/u', '_', $name);
			$name = preg_replace('/[^\p{Arabic}\p{L}\p{N}_.\-]/u', '_', $name); 
			return $name;
		}

		public static function sanitize_name($name) {
			//Règle le problème avec certaines lettres avec accents (uft-8 MAC)
			$name = normalizer_normalize($name);

			//Met en majuscule les initiales
			$name = ucwords(strtolower($name));

			// Eliminer les \ si existants
			$name = stripcslashes($name);

			//Majuscules pour les noms spéciaux avec ' ou - etc...
			//https://forum.phpfrance.com/php-4-deprecated/modifier-casse-prenom-compose-t6606.html 
			//https://stackoverflow.com/q/79090742/1041229
			$name = preg_replace_callback('/(?<![^\'`-])\w/u', function($matches) {
				return mb_strtoupper($matches[0]);
			}, $name);

			return $name;
		}

		public static function sanitize_name_lite($name) {
			//Règle le problème avec certaines lettres avec accents (uft-8 MAC)
			$name = normalizer_normalize($name);

			//Retire les espaces inutiles
			$name = trim($name);
			$name = str_replace('   ', ' ', $name);
			$name = str_replace('  ', ' ', $name);

			return $name;
		}

		public static function validate_email($email) {
			return filter_var($email, FILTER_VALIDATE_EMAIL);
		}

		public static function remove_files_from_upload() {
			$directory = '../Uploads/';
			$files = glob($directory . '*');
			if (!empty($files)) {
				foreach ($files as $file) {
					// Supprimer chaque fichier s'il s'agit bien d'un fichier
					if (is_file($file)) {
						unlink($file);
					}
				}
			}
		}

		public static function connect_database() {
			// Connexion à la base de données
			try {
				$pdo = new PDO(DB_DSN, DB_USER, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
				$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) {
				die("Erreur de connexion : " . $e->getMessage());
			}

			return $pdo;
		}
	
	}

?>