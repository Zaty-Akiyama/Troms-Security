<?php
if (!class_exists('Image_Authentication')) :

  class Image_Authentication {
    private $width = 100;
    private $height = 50;
    private $fonts_path = [
      '/assets/fonts/ZenMaruGothic-Medium.ttf',
      '/assets/fonts/ZenMaruGothic-Bold.ttf',
      '/assets/fonts/ZenMaruGothic-Black.ttf',
    ];

    public function __construct() {
      $this->hiragana_array = preg_split("//u", "あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわ");

      $this->hooks();
    }

    private function hooks() {
      add_filter( 'authenticate', array($this, 'hiragana_validation'), 20, 2 );
      add_action( 'login_form', array($this, 'add_hiragana_field') );
    }

    private function generate_random_hiragana( $length = 4 ) {
      $random_hiragana = '';
      for ( $i = 0; $i < $length; $i++ ) {
        $random_hiragana .= $this->hiragana_array[ array_rand( $this->hiragana_array ) ];
      }
      return $random_hiragana;
    }

    private function generate_random_color( $image ) {
      return imagecolorallocate( $image, random_int(50, 150), random_int(50, 150), random_int(50, 150) );
    }

    private function draw_random_line( $image, $color, $times ) {
      for ( $i = 0; $i < $times; $i++ ) {
        $x1 = -1 * random_int( 0, $this->width * 3 );
        $y1 = -1 * random_int( 0, $this->height * 3 );
        $x2 = random_int( 0, $this->width * 3 );
        $y2 = random_int( 0, $this->height * 3 );
        imageline( $image, $x1, $y1, $x2, $y2, $color );
      }
    }

    private function generate_random_hiragana_image( $random_hiragana ) {
      $random_string_length = mb_strlen( $random_hiragana );
      $image = imagecreatetruecolor( $this->width, $this->height );
      $background_color = $this->generate_random_color( $image );
      imagefill( $image, 0, 0, $background_color );
 
      $text_color = imagecolorallocate( $image, 0, 0, 0 );

      $this->draw_random_line( $image, $this->generate_random_color( $image ), 46 );
      
      for ( $i = 0; $i < $random_string_length; $i++ ) {
        $font_size = rand( $this->height / 3, $this->height / 2 );
    
        $x = ( $this->width / $random_string_length ) * $i + ( $this->width / $random_string_length - $font_size ) / 2;
    
        $y = $this->height - ( ($this->height - $font_size) / 2 );
    
        $angle = rand( -20, 20 );
        $character = mb_substr( $random_hiragana, $i, 1 );

        $font_path = dirname( __FILE__ ) . $this->fonts_path[ random_int (0, count($this->fonts_path) - 1 ) ];

        imagettftext( $image, $font_size, $angle, $x, $y, $text_color, $font_path, $character );
      }

      $this->draw_random_line( $image, $this->generate_random_color( $image ), 12 );

      ob_start();
      imagepng( $image );
      $image_data = ob_get_contents();
      ob_end_clean();

      imagedestroy( $image );

      return '<img src="data:image/png;base64,' . base64_encode( $image_data ) . '" alt="" />';
    }

    public function add_hiragana_field() {
      $random_hiragana = $this->generate_random_hiragana();
      $hashed_random_hiragana = password_hash( $random_hiragana, PASSWORD_DEFAULT );
      echo '<label for="hiragana">' . $this->generate_random_hiragana_image( $random_hiragana ) . '</label><p><input type="text" name="hiragana" id="hiragana" class="input" value="" size="20" pattern="[\u3041-\u3096]*" /></p>';
      echo '<input type="hidden" name="generated_hiragana_hash" value="' . $hashed_random_hiragana . '">';
    }

    public function hiragana_validation( $user, $password ) {
      if ( isset( $_POST['wp-submit'] ) ) {
        if ( isset( $_POST['hiragana'] ) && isset( $_POST['generated_hiragana_hash'] ) && password_verify( $_POST['hiragana'], $_POST['generated_hiragana_hash'] ) ) {
          return $user;
        } else {
          remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
          return new WP_Error( 'invalid_hiragana', 'ひらがなが正しくありません。' );
        }
      } else {
          return $user;
      }
    }
  }

  new Image_Authentication();
endif;

