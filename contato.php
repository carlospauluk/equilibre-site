<?php


require 'vendor/autoload.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use ReCaptcha\ReCaptcha;


$props = parse_ini_file('.env');

function doIt($props)
{

    $agora = (new DateTime())->format('Y-m-d-H-i-s');

    if (!isset($_POST['g-recaptcha-response'])) {
        return [
            'status' => 'ERRO',
            'msg' => 'Informe se você é um robô'
        ];
    }


    $secret = $props['GOOGLE_RECAPTCHA_SECRETKEY'];
    $gRecaptchaResponse = $_POST['g-recaptcha-response'];
    $recaptcha = new ReCaptcha($secret);
    $urlSistema = $props['URL_SISTEMA'];
    $resp = $recaptcha->setExpectedHostname($urlSistema)->verify($gRecaptchaResponse, $_SERVER['REMOTE_ADDR']);
    if (!$resp->isSuccess()) {
        return [
            'status' => 'ERRO',
            'msg' => 'Informe se você é um robô!'
        ];
    }


    $msg = [];
    $msg[] = 'Data: ' . $agora;
    $msg[] = 'Nome: ' . $_POST['nome'];
    $msg[] = 'E-mail: ' . $_POST['email'];
    $msg[] = 'Telefone: ' . $_POST['telefone'];
    $msg[] = 'Mensagem: ' . $_POST['msg'];
    $msg = implode(PHP_EOL, $msg);

    file_put_contents('contatos/' . $agora . '.txt', $msg);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.umbler.com';
        $mail->SMTPAuth = true;
        $mail->Port = 587;
        $mail->SMTPSecure = false;
        $mail->SMTPAutoTLS = false;
        $mail->Username = 'mailer@ektplus.com.br';
        $mail->Password = $props['PWDMAILER'];
        $mail->setFrom('contato@equilibrepsicologia.com.br');
        $mail->addAddress('carlospauluk@gmail.com');
        $mail->addAddress('luiz.pauluk@gmail.com');
        //$mail->addAddress('ekt@ekt.com.br');
        $mail->addReplyTo('contato@equilibrepsicologia.com.br');
        $mail->isHTML(true);
        $mail->Subject = 'Contato pelo site';
        $mail->Body = str_replace(PHP_EOL, '<br>', $msg);
        $mail->send();
        unset($_POST);
        return [
            'status' => 'OK',
            'msg' => 'Ocorreu um erro ao enviar sua mensagem. Por favor, envie um e-mail diretamente para ekt@ektplus.com.br'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'ERRO',
            'msg' => 'Ocorreu um erro ao enviar sua mensagem. Por favor, envie um e-mail diretamente para ekt@ektplus.com.br'
        ];
    }
}

$r = [
    'status' => '',
    'msg' => ''
];
if (isset($_POST['btnEnviar'])) {
    $r = doIt($props);
}

