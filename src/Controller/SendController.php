<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SendController extends AbstractController
{
    private const FROM_EMAIL = 'info@esperantosevilla.org';
    private const INFO_EMAIL = 'kongreso@esperanto.es';
    private const BCC_EMAIL = 'eiriarte@gmail.com';

    public function send(Request $request, MailerInterface $mailer): Response {
        $json = file_get_contents('../info/mails.json');
        $data = json_decode($json, true);
        $list = explode(',', $request->get('ids'));

        $output = '';
        foreach ($data as $person) {
            if (in_array($person['number'], $list)) {
                $output .= '<li><b>' . $person['number'] . '</b> - ' . $person['fullName'] . ' &lt;' . $person['emailAddress'] . '&gt; — ' . $person['paid'] . '</li>';
                $mail = (new TemplatedEmail())
                    ->from(new Address(self::FROM_EMAIL, 'Sevila Kongresa Komitato'))
                    ->to($person['emailAddress'])
                    ->bcc(self::BCC_EMAIL)
                    ->replyTo(self::INFO_EMAIL)
                    ->priority(Email::PRIORITY_HIGH)
                    ->subject('Hispana Esperanto-Kongreso: ')
                    ->htmlTemplate('email/last.twig')
                    ->context($person);
                $mail->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

                $mailer->send($mail);
            }
        }

        return new Response('<ul>' . $output . '</ul>');
    }
}