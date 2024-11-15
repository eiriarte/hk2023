<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Suggestion;
use App\Form\SuggestionType;
use App\Repository\SuggestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

use function file_get_contents;
use function json_decode;

class MainController extends AbstractController
{
    private const FROM_EMAIL = 'FROM_EMAIL@example.com';
    private const INFO_EMAIL = 'INFO_EMAIL@example.com';

    public function main(): Response
    {
        return $this->render('main.html.twig');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function suggest(
        Request $request,
        SuggestionRepository $suggestionRepository,
        MailerInterface $mailer,
    ): Response {
        $suggestion = new Suggestion();
        $form = $this->createForm(SuggestionType::class, $suggestion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $suggestionRepository->add($suggestion, true);
            $mail = (new Email())
                ->from(self::FROM_EMAIL)
                ->to(self::INFO_EMAIL)
                ->replyTo((string)$suggestion->getEmail())
                ->bcc('BCC@example.com')
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

    public function accommodation(): Response
    {
        /** @var string $json */
        $json = file_get_contents('../info/loghejoj.json');
        $data = json_decode($json, true);

        return $this->render('accommodation.html.twig', [ 'accommodations' => $data ]);
    }
}
