<?php

add_action(
    'wp_mail_failed',
    fn ($error) =>
    trigger_error(
        message: 'wp_mail_failed: ' . $error->get_error_message(),
        error_level: E_USER_ERROR
    )
);

add_filter(
    'wp_mail_from',
    function (string $email): string {
        if (!defined('SMTP_From') || !SMTP_From) {
            trigger_error(
                message: 'SMTP: SMTP_From missing in wp-config.php',
                error_level: E_USER_ERROR
            );
            return '';
        }
        return (string) SMTP_From;
    }
);


add_action(
    'phpmailer_init',
    'smtp_setup'
);
function smtp_setup(PHPMailer\PHPMailer\PHPMailer &$phpmailer): void
{
    if (!defined('SMTP_Host') || !SMTP_Host) {
        trigger_error(
            message: 'SMTP: SMTP_Host missing in wp-config.php',
            error_level: E_USER_WARNING
        );
        return;
    }
    $phpmailer->isSMTP();
    $phpmailer->From = SMTP_From;
    $phpmailer->FromName = SMTP_FromName;
    $phpmailer->Host = SMTP_Host;
    $phpmailer->Password = SMTP_Password;
    $phpmailer->Port = SMTP_Port;
    $phpmailer->SMTPAuth = SMTP_SecureProtocol;
    $phpmailer->SMTPSecure = SMTP_SMTPAuth;
    $phpmailer->Username = SMTP_Username;
    $phpmailer->SMTPDebug = defined('SMTP_Debug') && SMTP_Debug ? PHPMailer\PHPMailer\SMTP::DEBUG_SERVER : false;
    return;
}

if (is_admin()) {
    add_action(
        'init',
        function () {
            $smtp_section_title = "SMTP SETUP";
            $smtp_section_description = "please add smtp credentials.\nif 'HOST' is set,\nthis custom function in (__FILE__) will be used\ninstead of standart wordpress mail.";
            $smtp_section_values = [
                'From' => '',
                'FromName' => '',
                'Host' => '',
                'Password' => '',
                'Port' => 25,
                'SecureProtocol' => '',
                'SMTPAuth' => true,
                'Username' => '',
                'Debug' => false
            ];
            if (!$wp_config = file_get_contents(filename: ABSPATH . '/wp-config.php')) {
                error_log(
                    message: 'could not find ' . ABSPATH . '/wp-config.php',
                    message_type: E_USER_WARNING
                );
                return false;
            }
            if (!is_int(strpos(
                haystack: $wp_config,
                needle: $smtp_section_title
            ))) {
                $comment_string = "\n/** $smtp_section_title" . "\n *\n * " . str_replace(
                    search: "\n",
                    replace: "\n *",
                    subject: $smtp_section_description
                ) . "\n*/\n";
                $values_string = '';
                foreach ($smtp_section_values as $key => $value) {
                    $values_string .= "define( 'SMTP_$key', " . (is_string($value) ? "'$value'" : $value)  . " );\n";
                }
                if (is_int(file_put_contents(
                    filename: ABSPATH . '/wp-config.php',
                    data: $comment_string . $values_string,
                    flags: FILE_APPEND
                ))) {
                    trigger_error(
                        message: 'SMTP: created fields for SMTP_constants.',
                        error_level: E_USER_NOTICE
                    );
                }
            }
        }
    );
}