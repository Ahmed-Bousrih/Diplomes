<?php

    class CertificateGenerator {

        // Fonction pour centrer les textes dans les certificats
        public function center_text($image, $text, $x_start, $x_end, $y_position, $font_size, $font_path, $text_color) {
            $bbox = imagettfbbox($font_size, 0, $font_path, $text);
            $text_width = $bbox[2] - $bbox[0];
            $available_width = $x_end - $x_start;
            $x_position = intval($x_start + (($available_width - $text_width) / 2));
            imagettftext($image, $font_size, 0, $x_position, $y_position, $text_color, $font_path, $text);
        }

        // Fonction pour générer un certificat
        public function generate_certificate($student_firstname, $student_lastname, $teacher_name, $subject, $template_base, $color_certificate) {
            $font_size_name = 140;
            $font_size_subject = 50;
            $font_size_teacher = 80;
            $font_size_date = 70;
            $font_path_Bold = '../glacial-indifference/GlacialIndifference-Bold.otf';
            $font_path_Regular = '../glacial-indifference/GlacialIndifference-Regular.otf';
        
            // Load the JPG image
            $certificate_image_base = imagecreatefromjpeg($template_base);
        
            // Allocate text color
            $text_color = imagecolorallocate($certificate_image_base, $color_certificate[0], $color_certificate[1], $color_certificate[2]);
        
            // Center and draw text
            $this->center_text($certificate_image_base, "$student_firstname $student_lastname", 713, 2873, 1137, $font_size_name, $font_path_Regular, $text_color);
            $this->center_text($certificate_image_base, $subject . ".", 735, 2765, 1575, $font_size_subject, $font_path_Bold, $text_color);
            $this->center_text($certificate_image_base, $teacher_name, 1108, 1938, 1930, $font_size_teacher, $font_path_Regular, $text_color);
            
            $locale = 'fr_FR'; 
            $formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'MMMM yyyy');
            $nonFormattedDate = new DateTime();
            $date = ucfirst($formatter->format($nonFormattedDate));
            
            $this->center_text($certificate_image_base, $date, 2189, 2817, 1930, $font_size_date, $font_path_Regular, $text_color);
        
            // Save the output as a JPEG
            $temp_image_base = '../temp_certificate_base_' . uniqid() . '.jpg';
            imagejpeg($certificate_image_base, $temp_image_base, 100);

            imagedestroy($certificate_image_base);
            return $temp_image_base;
        }
        
    }


?>