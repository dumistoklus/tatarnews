<?php

class Registration implements IPlugin {

    public static function name() {
        return __CLASS__;
    }

    public static function load($side = 0, $order = 0 ) {

        $RegistrationUser = new RegistrationUser;

        $inner = '';

        switch ( $RegistrationUser->get_register_status() ) {

            case 'ALREADY_REGISTERED':
                $inner = 'Вы уже зарегистрированы';
                break;

            case 'WRONG_NICKNAME':
            case 'NICKNAME_EXISTS':
            case 'ENTER_NICKNAME':

                if ( $RegistrationUser->get_register_status() == 'WRONG_NICKNAME' ) {

                    $inner = '<div class="warning">Неверно указано имя пользователя. Допустмы только латинские буквы и цифры. Минимальная длина - 2 символа</div>';

                } else if ( $RegistrationUser->get_register_status() == 'NICKNAME_EXISTS' ) {

                     $inner = '<div class="warning">Пользователь с таким именем уже существует. Укажите другое имя</div>';
                }

                $inner .= '<form action="'.$RegistrationUser->get_activation_link().'" method="post">'.
                    '<dl>'.
                        '<dt class="form-bigtitle">Укажите имя пользователя:&nbsp;</dt>'.
                        '<dd style="padding-bottom: 20px;">'.
                            '<input class="typetext cabinet-myname-form" type="text" name="nickname" value="'.htmlspecialchars( $RegistrationUser->get_nickname(), ENT_QUOTES ).'" />'.
                        '</dd>'.
                        '<dt>&nbsp;</dt>'.
                        '<dd><input type="submit" class="button" value="Закончить регистрацию" /></dd>'.
                    '</dl>'.
                '</form>';
                break;

            case 'EMAIL_SENDED':
                $inner = '<div class="warning">На указанную электронную почту было отправлено письмо, с ссылкой проверки'.
                        //  '<br /><br /><a href="'.$RegistrationUser->get_activation_link().'">'.$RegistrationUser->get_activation_link().'</a>'.
                         '</div>';
                break;

            case 'ERROR_:(':
                $inner = '<div class="warning">Произошла ошибка, попробуйте зарегистрироваться позже</div>';
                break;

            case 'KEY_NOT_EXIST':
                $inner = '<div class="warning">Ссылка является устаревшей</div>';
                break;

            case 'USER_ACTIVATED':
                $inner = '<div class="warning">Спасибо за регистрацию!</div>';
                break; 

            case 'MAYBE_NICKNAME':
            case 'MAYBE_EMAIL':
                $inner = '<div class="warning">Такой адрес электронной почты уже используется</div>';
                /* не пиши сюда break */

            default:

                $inner .= '<form method="post" action="/?page=registration">'.
                    '<dl>'.
                        '<dt class="form-bigtitle">E-mail:</dt>'.
                        '<dd style="padding-bottom: 20px;">'.
                            '<input class="typetext cabinet-myname-form big-reg-form" type="text" name="email" value="'.htmlspecialchars( $RegistrationUser->get_email(), ENT_QUOTES ).'" />'.
                        '</dd>'.
                        '<dt class="cabinet-link form-title">Пароль:</dt>'.
                        '<dd class="cabinet-link">'.
                            '<input class="typetext big-reg-form" type="password" name="password" value="" />'.
                        '</dd>'.
                        '<dt class="cabinet-link form-title">Пароль еще раз:</dt>'.
                        '<dd class="cabinet-link">'.
                            '<input class="typetext big-reg-form" type="password" name="password2" value="" />'.
                        '</dd>'.
                        '<dt>&nbsp;</dt>'.
                        '<dd >'.
                            '<input type="submit" class="button" value="Зарегистрироваться" />'.
                        '</dd>'.
                     '</dl>'.
                 '</form>';
        }

        $output = '<div id="endofregistration"><div class="zagol-warp">
            <div class="zagol-line">
                Регистрация
            </div>
        </div>'.$inner.'</div>';

        return $output;
    }
}

class RegistrationUser {
    
    private $UserManager;
    private $validator;
    private $email = '';
    private $password;
    private $passwordagain;

    private $regkey;
    private $hash;
    private $nickname = '';

    private $activation_link;
    private $user_id;
    private $register_status = 'REG_FORM';

    public function __construct() {

        if (User::get()->isAuth()) {

            $this->register_status = 'ALREADY_REGISTERED';
            return;
        }

        DB_Provider::Instance()->loadProvider('Administration.Users');
        DB_Provider::Instance()->loadProvider('Plugins.Registration');

        if ( $this->isset_email_and_password() ) {

            if ( $this->validateUserInfo() ) {

                if ( $this->create_new_user() ) {

                    if ( $this->register_into_db() ) {

                        $this->send_email();
                    }
                }
            }
        }

        if ( $this->isset_registration_key() ) {

            if ( $this->check_registration_key() ) {

                if ( $this->isset_nickname() ) {

                    if ( $this->check_nickname()) {
                        $this->avtivate_user();
                    }
                }
            }
        }
    }

    public function get_register_status() {
        return $this->register_status;
    }

    public function get_nickname() {
        return $this->nickname;
    }

    public function get_activation_link() {
        return $this->activation_link;
    }

    public function get_email() {
        return $this->email;
    }

    private function validateUserInfo() {

        $this->validator = new ValidateRegistrationData;

        $this->validator->email = $this->email;
        $this->validator->password = $this->password;
        $this->validator->passwordagain = $this->passwordagain;
        $this->validator->Validate();
        return ( $this->validator->Validate() === TRUE );
    }

    private function isset_email_and_password() {

        if ( isset( $_POST['email'], $_POST['password'], $_POST['password2'] ) ) {

            $this->email = $_POST['email'];
            $this->password = $_POST['password'];
            $this->passwordagain = $_POST['password2'];

            return TRUE;
        }

        return FALSE;
    }

    private function register_into_db() {

        $this->hash = md5( sha1( md5( microtime( TRUE ) ) ) );

        $Registration = new PreCheckRegistration;
        $this->regkey = $Registration->CreateUserOnEmail( $this->user_id, $this->hash );

        if ( !$this->regkey )
            return FALSE;

        $this->set_activation_link();

        return TRUE;
    }

    private function set_activation_link() {
        
        $this->activation_link = 'http://'.env::vars()->SERVER.'/?page=registration&key='.$this->regkey.'&hash='.$this->hash;        
    }

    private function send_email() {

        if ( SendRegistrationMail::sendMail( $this->email, $this->activation_link ) ) {
            $this->register_status = 'EMAIL_SENDED';
            return TRUE;
        }

        $this->register_status = 'ERROR_:(';

        return FALSE;
    }

    private function isset_registration_key() {

        if ( isset( $_GET['key'], $_GET['hash'] ) ) {
            $this->regkey = (int)$_GET['key'];
            $this->hash = $_GET['hash'];

            return TRUE;
        }

        return FALSE;
    }

    private function check_registration_key() {

        if ( isValidMD5( $this->hash ) AND $this->regkey > 0 ) {

            $Registration = new PreCheckRegistration;

            $regData = $Registration->GetRegistrationData( $this->regkey, $this->hash );

            if ( empty( $regData ) )
                return FALSE;

            $this->set_activation_link();
            $this->user_id = $regData[0]['user_id'];
            $this->register_status = 'ENTER_NICKNAME';

            return TRUE;
        }

        return FALSE;
    }

    private function create_new_user() { 

        $this->UserManager = new UsersManager;

        if ( $user_id = $this->UserManager->InsertNewUserInDB( $this->email, $this->email, 1, $this->password, '', 0 ) ) {
            $this->user_id = $user_id;
            return TRUE;
        }

        $this->register_status = $this->UserManager->getError();
        return FALSE;
    }

    private function isset_nickname() {

        if ( isset( $_POST['nickname'] ) ) {

            $validator = new ValidateNickname;
            $validator->nickname = $_POST['nickname'];

            if ( $validator->Validate() ) {

                $this->nickname = $_POST['nickname'];
                return TRUE;
            }

            $this->register_status = 'WRONG_NICKNAME';
        }

        return FALSE;
    }

    private function check_nickname() {

        $this->UserManager = new UsersManager;

        if ( $this->UserManager->CheckNicknameExist( $this->nickname ) == 0 ) {
            return TRUE;
        }

        $this->register_status = 'NICKNAME_EXISTS';

        return FALSE;
    }

    private function avtivate_user(){

        if ( $this->UserManager->updateNickNameAndActivateStatus( $this->nickname, $this->user_id ) ) {

            $Registration = new PreCheckRegistration;
            $Registration->DeleteActivationKey( $this->regkey );

            $this->register_status = 'USER_ACTIVATED';

            $microtime = microtime(true);
            $hash = md5($this->user_id.$_SERVER['HTTP_USER_AGENT'].$microtime);

            $UserProvider = new UserProvider;
            $session_id = $UserProvider->insert_session( $this->user_id, $hash, $microtime );

            if ( $session_id ) {
                new AuthUserByUID( $this->user_id, $hash, $session_id );
                URIManager::redirect_to( '/' );
            }
        }
    }
}

class SendRegistrationMail {

	private static $from = 'robot@finmarketsoyuz.ru';
	private static $fromName = 'Робот TatarNews.ru';
	private static $smtp_host = 'smtp.yandex.ru';
	private static $password = 'a16cf22c53';

    private static $subject = 'Регистрация на сайте tatarnews.ru';
    private static $mailtext = 'Вы зарегистрировались на сайте www.tatarnews.ru

Ваш e-mail будет использован для дальнейшего входа на сайт.

Пройдите пожалуйста по ссылке, чтобы подтвердить Вашу почту

{{LINK}}';

    public static function sendMail( $email, $link ) {

        $emailtext = str_replace( '{{LINK}}', $link, self::$mailtext );

	    require_once('Classes/class.phpmailer.php');

		try {
			$mail = new PHPMailer(true);
			$mail->IsSMTP(); // telling the class to use SMTP

			$mail->Host       = self::$smtp_host;
			$mail->SMTPAuth   = true;
			$mail->Port       = 25;
			$mail->Username   = self::$from;
			$mail->From 	  = self::$from;
			$mail->FromName   = self::$fromName;
			$mail->Password   = self::$password;
			$mail->Encoding   = 'quoted-printable';
			$mail->CharSet    = 'utf-8';

			$mail->AddAddress($email);
			$mail->Subject = self::$subject;
			$mail->Body = $emailtext;

			if ( !$mail->Send() ) {

				Logger::append_error( 'Registration email on '.$email.' not sended' );
				return FALSE;
			}
		}
		catch ( phpmailerException $e ) {
			echo $e->errorMessage(); //Pretty error messages from PHPMailer
		}

        return TRUE;
    }
}