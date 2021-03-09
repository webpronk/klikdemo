<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Messages;
use App\Form\MessageType;
use App\Repository\MessagesRepository;
use App\Service\PictureHelper;

use App\Service\ProfileReadyHelper;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class MessageController
 * @package App\Controller
 *
 * Controller used to manage messages.
 *
 * @Route("/message")
 * @IsGranted("ROLE_USER")
 */
class MessageController extends RedirectController
{

    protected $em;
    protected $userRepo;
    protected $user;
    protected $receiverEntity;
    protected $chatId;


    /**
     * Message Controller constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepo)
    {
        $this->em = $entityManager;
        $this->userRepo = $userRepo;
    }

    /**
     * @Route("/newlist", methods={"GET"}, name="message_new_list")
     */
    public function NewlistMessages(ProfileReadyHelper $profileReadyHelper)
    {
        $redirect = parent::switchRoute($profileReadyHelper);
        if(!empty($redirect && array_key_exists('route', $redirect))) {
            return $this->redirectToRoute( $redirect['route'] );
        }

        $messages = $this->getDoctrine()
              ->getRepository(Messages::class)
              ->findBy(['receiver' => $this->getUser()], ['creationDate'=> 'DESC']);

        return $this->render('message/inbox_list.html.twig', ['messages' => $messages]);
    }

    /**
     * @Route("/sendlist", methods={"GET"}, name="message_send_list")
     */
    public function sendListMessages()
    {
        $messages = $this->getDoctrine()
            ->getRepository(Messages::class)
            ->findBy(['sender' => $this->getUser()], ['creationDate'=> 'DESC']);

        return $this->render('message/outbox_list.html.twig', ['messages' => $messages]);
    }

    /**
     * @Route("/sendnew", methods={"GET", "POST"}, name="message_send_new")
     */
    public function sendNewMessage(MessagesRepository $messagesRepo, Request $request): Response
    {
        $this->receiverEntity = $this->userRepo->findOneBy(['id' => $request->query->get('user')]);

        $this->chatId = $messagesRepo->makeChatId();
        $mainPicture = $this->getMainPicture($this->receiverEntity->getId());
        $messageNew = $this->setMessageEntity();

        $form = $this->createForm(MessageType::class, $messageNew);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($messageNew);
            $this->em->flush();

            $this->addFlash('success', 'messages.message_send_success');

            return $this->redirectToRoute('message_send_list');
        }

        return $this->render('message/new.html.twig', [
            'receiver' => $this->receiverEntity,
            'mainPicture' => $mainPicture,
            'messageForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/show", methods={"GET", "POST"}, name="message_show")
     */
    public function ShowMessage(MessagesRepository $messagesRepo, Request $request): Response
    {
        $currentMessageId = $request->query->get('id');
        $messagesEntity = $messagesRepo->findOneBy(['id' => $currentMessageId]);
        $messagesEntity->setOpened(1);
        $this->em->persist($messagesEntity);
        $this->em->flush();

        $profilePicsOfChat = $this->getHistoryProfilePics($messagesEntity);

        $chatId = $messagesEntity->getChatId();
        $compExpr = 'lt';

        $messageHistoryList = $messagesRepo->getMessageHistory($chatId, $currentMessageId, $compExpr);

        $form = $this->createForm(MessageType::class, $messagesEntity);
        $form->handleRequest($request);

        return $this->render('message/show_message.html.twig', [
            'messageHistoryList' => $messageHistoryList,
            'profilePicsOfChat' => $profilePicsOfChat,
            'messageForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/answer", methods={"GET", "POST"}, name="message_answer")
     */
    public function AnswerMessage(MessagesRepository $messagesRepo, Request $request): Response
    {
        $messageId = $request->query->get('id');
        $messagesEntity = $messagesRepo->findOneBy(['id' => $messageId]);

        $this->receiverEntity = $messagesEntity->getSender();
        $this->chatId = $messagesEntity->getChatId();

        $profilePicsOfChat = $this->getHistoryProfilePics($messagesEntity);

        $mainPicture = $this->getMainPicture($this->receiverEntity->getId());
        $messageAnswer = $this->setMessageEntity();

        $compExpr = 'lte';

        $messageHistoryList = $messagesRepo->getMessageHistory($this->chatId, $messageId, $compExpr);

        $form = $this->createForm(MessageType::class, $messageAnswer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->em->persist($messageAnswer);
            $this->em->flush();

            $this->addFlash('success', 'messages.message_send_success');

            return $this->redirectToRoute('message_send_success');
        }

        return $this->render('message/answer.html.twig', [
            'receiver' => $this->receiverEntity,
            'messageHistoryList' => $messageHistoryList,
            'profilePicsOfChat' => $profilePicsOfChat,
            'mainPicture' => $mainPicture,
            'messageForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/showsend", methods={"GET", "POST"}, name="message_showsend")
     */
    public function showSendMessage(MessagesRepository $messagesRepo, Request $request): Response
    {
        $currentMessageId = $request->query->get('id');
        $messagesEntity = $messagesRepo->findOneBy(['id' => $currentMessageId]);
        $messagesEntity->setOpened(1);
        $this->em->persist($messagesEntity);
        $this->em->flush();

        $profilePicsOfChat = $this->getHistoryProfilePics($messagesEntity);

        $chatId = $messagesEntity->getChatId();
        $compExpr = 'lt';

        $messageHistoryList = $messagesRepo->getMessageHistory($chatId, $currentMessageId, $compExpr);

        $form = $this->createForm(MessageType::class, $messagesEntity);
        $form->handleRequest($request);

        return $this->render('message/showsend.html.twig', [
            'messageHistoryList' => $messageHistoryList,
            'profilePicsOfChat' => $profilePicsOfChat,
            'messageForm' => $form->createView(),
        ]);
    }

    /**
     *
     * @Route("/message_send_success", methods={"GET", "POST"}, name="message_send_success")
     * @return Response
     */
    public function messageSendSuccess(): Response
    {
        return $this->render('message/send_success.html.twig', [

        ]);
    }

    /**
     * @return bool|mixed
     */
    protected function getMainPicture($userId)
    {
        $PictureHelper = new PictureHelper($this->em, $userId);
        $mainPicture = $PictureHelper->getMainPicture();

        return $mainPicture;
    }

    /**
     * @param $receiverEntity
     * @param $chatId
     * @return Messages
     */
    protected function setMessageEntity()
    {
        $messageNew = new Messages();
        $messageNew->setChatId($this->chatId);
        $messageNew->setReceiver($this->receiverEntity);
        $messageNew->setSender($this->getUser());
        $messageNew->setChatId($this->chatId);
        $messageNew->setOpened(0);
        $messageNew->setDeleted(0);
        $messageNew->setCreationDate(new \DateTime());

        return $messageNew;
    }

    protected function getHistoryProfilePics($messagesEntity)
    {
        $senderId = $messagesEntity->getSender()->getId();
        $receiverId = $messagesEntity->getReceiver()->getId();
        $mainPictureSender = $this->getMainPicture($senderId);
        $mainPictureReceiver = $this->getMainPicture($receiverId);
        $profilePicArray = [
            $senderId => $mainPictureSender,
            $receiverId => $mainPictureReceiver
        ];

        return $profilePicArray;
    }



}

?>