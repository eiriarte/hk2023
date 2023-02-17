<?php

namespace App\Controller;

use App\Entity\Suggestion;
use App\Form\SuggestionType;
use App\Repository\SuggestionRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Yaml\Yaml;

class MainController extends AbstractController
{
    private const FROM_EMAIL = 'admin@esperantosevilla.org';
    private const INFO_EMAIL = 'kongreso@esperanto.es';

    public function main(): Response
    {
        return $this->render('main.html.twig');
    }

    public function suggest(
        Request $request,
        SuggestionRepository $suggestionRepository,
        MailerInterface $mailer,
    ): Response {
        $suggestion = new Suggestion();
        $form = $this->createForm(SuggestionType::class, $suggestion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: registros al log (logentries??)
            $suggestionRepository->add($suggestion, true);
            $mail = (new Email())
                ->from(self::FROM_EMAIL)
                ->to(self::INFO_EMAIL)
                ->replyTo($suggestion->getEmail())
                ->bcc('eiriarte@gmail.com')
                ->subject('Hispana Esperanto-Kongreso: Sugerencia de ' . $suggestion->getName())
                ->text($suggestion->getMessage());

            $mail->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

            $mailer->send($mail);

            return $this->redirectToRoute('suggestion_sent', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('suggest.html.twig', [
            'suggestion' => $suggestion,
            'form' => $form,
        ]);
    }

    public function suggestionSent(): Response
    {
        return $this->render('suggestion_sent.html.twig');
    }

    public function program(): Response
    {
        $data = Yaml::parseFile('../info/program.yaml');

        return $this->render('program.html.twig', $data);
    }

    public function accommodation(): Response
    {
        $data = Yaml::parseFile('../info/accommodation.yaml');

        return $this->render('accommodation.html.twig', $data);
    }
}
