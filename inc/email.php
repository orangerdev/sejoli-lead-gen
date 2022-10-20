<?php

class LeadFormEmail {

    /**
     * Attachment for file
     * @var [type]
     */
    public $attachments = false;

    /**
     * Construction
     */
    public function __construct() {
    }

    /**
     * Send the notification
     * @since   1.0.0
     * @return  void
     */
    public function send(array $recipients, $content, $title, $recipient_type = array('admin', 'affiliate', 'buyer'), $attachments = array()) {

        ob_start();
        include SEJOLISA_DIR . '/template/email/template.php';
        $message = ob_get_clean();

        $logo        = '';
        $upload_logo = sejolisa_carbon_get_theme_option('notification_email_logo');

        if($upload_logo) :
            $image = wp_get_attachment_image_src($upload_logo, 'medium');
            if($image) :
                $logo = sprintf('<img src="%s" alt="%s" />', $image[0], get_bloginfo('name'));
            endif;
        endif;

        $is_sent = wp_mail(
            $recipients,
            $title,
            str_replace([
                    '{{logo}}',
                    '{{content}}',
                    '{{footer}}',
                    '{{copyright}}'
                ],[
                    $logo,
                    wpautop($content),
                    sejolisa_carbon_get_theme_option('notification_email_footer'),
                    sejolisa_carbon_get_theme_option('notification_email_copyright')
                ],
                $message
            ),
            [
                'Content-Type: text/html; charset=UTF-8',
                sprintf('From: %s <%s>', sejolisa_carbon_get_theme_option('notification_email_from_name'), sejolisa_carbon_get_theme_option('notification_email_from_address')),
                sprintf('Reply-top: %s <%s>', sejolisa_carbon_get_theme_option('notification_email_reply_name'), sejolisa_carbon_get_theme_option('notification_email_reply_address'))
            ]
        );

    }
}
