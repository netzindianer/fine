<?php

class f_valid_recaptcha extends f_valid_abstract
{

    const SITE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const CAPTCHA_ERROR = 'CAPTCHA_ERROR';

    protected $_msg = array(
        self::CAPTCHA_ERROR => "Błędna wartość",
    );

    public static function _(array $config = array())
    {
        return new self($config);
    }

    public function isValid($mValue)
    {
        $mValue = $_POST['g-recaptcha-response'];
        $params = array(
            'secret' => f::$c->config->googleApi['reCAPTCHA_Secret_key'],
            'response' => $mValue,
            'remoteip' => $_SERVER['REMOTE_ADDR'],
        );

        $peer_key = version_compare(PHP_VERSION, '5.6.0', '<') ? 'CN_name' : 'peer_name';
        $options = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($params),
                'verify_peer' => true,
                $peer_key => 'www.google.com',
            ),
        );
        $context = stream_context_create($options);

        $data = file_get_contents(self::SITE_VERIFY_URL, false, $context);
        if (!$data) {
            $this->_error(self::CAPTCHA_ERROR);
            return false;
        }
        $data = json_decode($data, true);
        if(!$data['success']) {
            $this->_error(self::CAPTCHA_ERROR);
            return false;
        }

        return true;
    }

}
