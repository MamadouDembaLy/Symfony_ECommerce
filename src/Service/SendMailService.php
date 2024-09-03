<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
//composant nous permettant de creer des mails
use Symfony\Component\Mailer\MailerInterface;

//cette classe permet d'envoyer des mail
class SendMailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send(string $from, string $to, string $subject, string $template, array $context): void
    {
        //creation du mail

        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            ->subject($subject)
            ->htmlTemplate("emails/$template.html.twig")
            ->context($context);

            //envoie du mail
            $this->mailer->send($email);  
    }
}
