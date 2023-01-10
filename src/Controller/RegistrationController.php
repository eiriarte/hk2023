<?php

namespace App\Controller;

use App\Entity\Registration;
use App\Form\RegistrationType;
use App\Repository\RegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    private const FROM_EMAIL = 'admin@esperantosevilla.org';
    private const EMAIL = 'kongreso@esperanto.es';

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
            // TODO: email con copia oculta a info@???
            $registrationRepository->add($registration, true);

            $successMessage = $translator->trans(
                'Tu inscripción se ha registrado con éxito y te será confirmada por correo electrónico tan pronto recibamos el pago.'
            );
            $successMessage .= ' ' .
                sprintf($translator->trans('Para cualquier consulta, puedes escribirnos a %s.'), self::EMAIL);
            $this->addFlash('success', $successMessage);
            $this->sendMail($mailer, $registration);

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

    private function sendMail(MailerInterface $mailer, Registration $registration) {
        $mail = (new Email())
            ->from(self::FROM_EMAIL)
            ->to($registration->getEmail())
            ->replyTo(self::EMAIL)
            ->bcc('eiriarte@gmail.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Hispana Esperanto-Kongreso: solicitud recibida')
            ->text('Hemos recibido tu solicitud, que será confirmada tan pronto recibamos el pago. Un saludo.');

        $mailer->send($mail);
    }
}
