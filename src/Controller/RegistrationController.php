<?php

namespace App\Controller;

use App\Entity\Registration;
use App\Form\RegistrationType;
use App\Repository\RegistrationRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    private const FROM_EMAIL = 'FROM_EMAIL@example.com';
    private const INFO_EMAIL = 'INFO_EMAIL@example.com';
    private const BCC_EMAIL = 'BCC_EMAIL@example.com';

    public function create(
        Request $request,
        RegistrationRepository $registrationRepository,
        TranslatorInterface $translator,
        MailerInterface $mailer,
    ): Response {
        $registration = new Registration();
        $registration->setDate(new \DateTime('now'));
        $form = $this->createForm(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationRepository->add($registration, true);

            $successMessage = '<h5>' . $translator->trans('¡Gracias!') . '</h5>';
            $successMessage .= $translator->trans(
                'Tu inscripción se ha registrado con éxito y te será confirmada por correo electrónico tan pronto recibamos el pago (o en breve si, por edad, el importe total es 0 €).'
            );
            $successMessage .= '<br>' . sprintf(
                $translator->trans('Para cualquier consulta, puedes escribirnos a %s.'),
                '<a href="mailto:' . self::INFO_EMAIL . '">' . self::INFO_EMAIL . '</a>'
            );

            $telegramMessage = '<h5><i class="bi bi-telegram"></i>&nbsp;' . $translator->trans('¡Ven a nuestro grupo de Telegram!') . '</h5>';
            $telegramMessage .=
                $translator->trans('Suscríbete a nuestro grupo Telegram para estar al día de las novedades acerca del congreso');
            $telegramMessage .= ':<ul><li><a target="_blank" href="https://mallonge.net/hispanakongreso">https://mallonge.net/hispanakongreso</a></li></ul>';
            $telegramMessage .= $translator->trans('¿Aún no tienes Telegram?') .
                ' <a target="_blank" href="https://telegramo.org/el%C5%9Duti/">' . $translator->trans('Descárgalo aquí') . '</a>.';

            $this->addFlash('success', $successMessage);
            $this->addFlash('success', $telegramMessage);
            $this->sendMail($mailer, $registration, $translator, $request->getLocale());

            return $this->redirectToRoute('registrations', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('registration/new.html.twig', [
            'registration' => $registration,
            'form' => $form,
        ]);
    }

    public function list(RegistrationRepository $registrationRepository): Response
    {
        return $this->render('registration/list.html.twig', [
            'registrations' => $registrationRepository->findAll(),
        ]);
    }

    public function adminList(RegistrationRepository $registrationRepository): Response
    {
        return $this->render('registration/admin_list.html.twig', [
            'registrations' => $registrationRepository->findAll(),
        ]);
    }

    private function sendMail(MailerInterface $mailer, Registration $registration, TranslatorInterface $translator, string $locale) {
        $price = 70;
        $discount = 0;
        $donation = $registration->getDonation() ?? 0;
        $priceText = $translator->trans('hasta abril');
        $discountText = '';
        if ((int)date('m') >= 7) {
            $price = 90;
            $priceText = $translator->trans('desde julio');
        } elseif ((int)date('m') >= 5) {
            $price = 80;
            $priceText = $translator->trans('desde mayo');
        }
        if ($registration->isMember()) {
            $discount = 10;
            $discountText = $translator->trans('miembro');
            if ($registration->isRelative()) {
                $discount = 20;
                $discountText .= ' + ' . $translator->trans('familiar acompa&ntilde;ante');
            }
        } elseif ($registration->isRelative()) {
            $discount = 15;
            $discountText = $translator->trans('familiar acompa&ntilde;ante');
        }
        if ($registration->getAge() < 17) {
            $discount = $price;
            $discountText = $translator->trans('hasta 16 a&ntilde;os');
        } elseif ($registration->getAge() < 31) {
            $discount += 15;
            $discountText = $translator->trans('joven');
        }
        if (strlen($discountText) > 0) {
            $discountText = '(' . $discountText . ')';
        }
        $total = $price + $donation - $discount;
        $mail = (new TemplatedEmail())
            ->from(new Address(self::FROM_EMAIL, 'Sevila Kongresa Komitato'))
            ->to($registration->getEmail())
            ->bcc(self::BCC_EMAIL)
            ->replyTo(self::INFO_EMAIL)
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Hispana Esperanto-Kongreso: ' . $translator->trans('solicitud recibida'))
            ->htmlTemplate('email/registration.twig')
            ->context([
                'name' => $registration->getName(),
                'price' => $price,
                'priceText' => $priceText,
                'discount' => $discount,
                'discountText' => $discountText,
                'donation' => $donation,
                'total' => $total,
                'locale' => $locale,
            ]);
        $mail->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

        $mailer->send($mail);
    }
}
