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
    private const FROM_EMAIL = 'info@esperantosevilla.org';
    private const INFO_EMAIL = 'kongreso@esperanto.es';
    private const BCC_EMAIL = 'eiriarte@gmail.com';

    public function create(
        Request $request,
        RegistrationRepository $registrationRepository,
        TranslatorInterface $translator,
        MailerInterface $mailer,
    ): Response {
        $registration = new Registration();
        $form = $this->createForm(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: try-catch?
            // TODO: registros al log (logentries??)
            $registrationRepository->add($registration, true);

            $successMessage = $translator->trans(
                'Tu inscripción se ha registrado con éxito y te será confirmada por correo electrónico tan pronto recibamos el pago.'
            );
            $successMessage .= ' ' .
                sprintf($translator->trans('Para cualquier consulta, puedes escribirnos a %s.'), self::INFO_EMAIL);
            $this->addFlash('success', $successMessage);
            $this->sendMail($mailer, $registration, $translator, $request->getLocale());

            return $this->redirectToRoute('registrations', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('registration/new.html.twig', [
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

    private function sendMail(MailerInterface $mailer, Registration $registration, TranslatorInterface $translator, string $locale) {
        $price = 70;
        $discount = 0;
        $donation = $registration->getDonation() ?? 0;
        $priceText = $translator->trans('hasta abril');
        $discountText = '';
        if ((int)date('m') > 5) {
            $price = 80;
            $priceText = $translator->trans('desde mayo');
        }
        if ($registration->isMember()) {
            $discount = 10;
            $discountText = $translator->trans('miembro');
            if ($registration->getAge() < 31) {
                $discount = 20;
                $discountText .= ' + ' . $translator->trans('joven');
            } elseif ($registration->isRelative()) {
                $discount = 20;
                $discountText .= ' + ' . $translator->trans('familiar acompa&ntilde;ante');
            }
        } elseif ($registration->getAge() < 31) {
            $discount = 15;
            $discountText = $translator->trans('joven');
        } elseif ($registration->isRelative()) {
            $discount = 15;
            $discountText = $translator->trans('familiar acompa&ntilde;ante');
        }
        $total = $price + $donation - $discount;
        switch ($registration->getPaymentSystem()) {
            case 'PPAL':
                $paymentSystem = $translator->trans('V&iacute;a PayPal a kasisto@esperantosevilla.org<br/>(selecciona «Enviar a un amigo»)');
                break;
            default:
                $paymentSystem = $translator->trans('Transferencia bancaria') . '<br/>(IBAN ES04 0182 0404 1402 0158 5771)';
                break;
        }
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
                'paymentSystem' => $paymentSystem,
                'locale' => $locale,
            ]);
        $mail->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

        $mailer->send($mail);
    }
}
