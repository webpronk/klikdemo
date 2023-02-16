<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use App\Entity\Registration;
use App\Events;

use Twig\Environment;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * Notifies post's author about new Registrations.
 *
 * @author Oleg Voronkovich <oleg-voronkovich@yandex.ru>
 */
class RegistrationNotificationSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $translator;
    private $urlGenerator;
    private $sender;

    private $engine;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $urlGenerator, TranslatorInterface $translator, Environment $engine, $sender)
    {
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
        $this->sender = $sender;
        $this->engine = $engine;

    }

    public static function getSubscribedEvents(): array
    {

        return [
            Events::REGISTRATION_CREATED => 'onRegistrationCreated',
        ];
    }

    public function onRegistrationCreated(GenericEvent $event): void
    {
       /* $subject = $this->translator->trans('notification.registration_created');
        $body = $this->translator->trans('notification.registration_created.description', [
            '%title%' => 'welkom op klik2match',
            '%link%' => 'test',
        ]);*/

        $meta = $event->getSubject();
        $username = $meta->getUser()->getUsername();
        $password = $meta->getUser()->getPassword();
        error_log( basename(__FILE__) .  ' - '. __LINE__);
        //$body = '$template';
        //$twigTemplate = new \App_Twig();
        $twig = $this->engine->render('mail/registration.html.twig', array('username' => $username, 'password' => $password));

        // Symfony uses a library called SwiftMailer to send emails. That's why
        // email messages are created instantiating a Swift_Message class.
        // See https://symfony.com/doc/current/email.html#sending-emails
        $message = (new \Swift_Message())
            ->setSubject('registratie succes')
            ->setTo('b.pronk3@gmail.com')
            ->setFrom('info@klik2match.nl')
            ->setBody($twig, 'text/html')
        ;


        // In config/packages/dev/swiftmailer.yaml the 'disable_delivery' option is set to 'true'.
        // That's why in the development environment you won't actually receive any email.
        // However, you can inspect the contents of those unsent emails using the debug toolbar.
        // See https://symfony.com/doc/current/email/dev_environment.html#viewing-from-the-web-debug-toolbar
        $this->mailer->send($message);

    }
}
