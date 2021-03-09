<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form;

use App\Entity\BeheerStatus;
use App\Entity\BeheerAfkomst;
use App\Entity\BeheerBouw;
use App\Entity\BeheerSport;
use App\Entity\BeheerHaarkleur;
use App\Entity\BeheerReligie;
use App\Entity\BeheerOpleiding;


//use App\Entity\User;
use App\Entity\Meta;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\Provincie;
use App\Entity\Plaats;
use App\Repository\ProvincieRepository;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used to edit an user.
 *
 * @author Romain Monteil <monteil.romain@gmail.com>
 */
class MetaType extends AbstractType
{


    private $provincieRepository;
    private $security;
    private $entityManager;

    public function __construct(ProvincieRepository $provincieRepository, EntityManagerInterface $entityManager, Security $security)
    {
        $this->provincieRepository = $provincieRepository;
        $this->entityManager = $entityManager;
        $this->security = $security;

    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // For the full reference of options defined by each form field type
        // see https://symfony.com/doc/current/reference/forms/types.html

        // By default, form fields include the 'required' attribute, which enables
        // the client-side form validation. This means that you can't test the
        // server-side validation errors from the browser. To temporarily disable
        // this validation, set the 'required' attribute to 'false':
        // $builder->add('title', null, ['required' => false, ...]);

        $user = $this->security->getUser();
        $land_id = $user->getLand()->getId();


        $builder
            ->add('provincie', EntityType::class, [
                    'class' => Provincie::class,
                    'placeholder' => 'Maak een keuze',
                    'choices' => $this->provincieRepository->findProvinceLand($land_id),
                ]
            );


        $formModifier = function (FormInterface $form, Provincie $provincie = null) {
            $plaats = null === $provincie ? [] : $provincie->getPlaatsen();

            $form->add('plaats', EntityType::class, [
                'class' => Plaats::class,
                'placeholder' => 'Maak een keuze',
                'choices' => $plaats,
            ])
                ->add('status', EntityType::class, [
                    'placeholder' => 'Maak een keuze',
                    'class'=> BeheerStatus::class]
                )
                ->add('opzoek', ChoiceType::class, [
                'placeholder' => 'Maak een keuze',
                'choices' => array(
                    'Mannen' => 'Man',
                    'Vrouwen' => 'Vrouw',
                    'Beide' => 'Beide',
                ),
                'label' => 'Ik val op',
                'expanded'=>false,
                 'required' => true
                ])
                ->add('kinderwens', ChoiceType::class, [
                    'placeholder' => 'Maak een keuze',
                    'choices' => array(
                        'Ja' => 'Ja',
                        'Nee' => 'Nee',
                        'Misschien' => 'Misschien',
                    ),
                    'empty_data' => '',
                    'label' => 'Heb je een kinderwens',
                    'expanded'=>false,
                ])
                ->add('kinderen', ChoiceType::class, [
                    'placeholder' => 'Maak een keuze',
                    'choices' => array(
                        'Ja' => 'Ja',
                        'Nee' => 'Nee',
                        'Volwassen' => 'Vol',
                    ),
                    'empty_data' => '',
                    'label' => 'Heb je kinderen nu',
                    'expanded'=>false,
                ])
                ->add('roken', ChoiceType::class, [
                    'placeholder' => 'Maak een keuze',
                    'choices' => array(
                        'Ja' => 'Ja',
                        'Nee' => 'Nee',
                        'Soms' => 'Soms',
                    ),
                    'label' => 'Rook je',
                    'expanded'=>false,
                ])
                ->add('drugs', ChoiceType::class, [
                    'placeholder' => 'Maak een keuze',
                    'choices' => array(
                        'Vaak' => 'Vaak',
                        'Nee' => 'Nee',
                        'Soms' => 'Soms',
                    ),
                    'label' => 'Drugs',
                    'expanded'=>false,
                ])
                ->add('drinken', ChoiceType::class, [
                    'placeholder' => 'Maak een keuze',
                    'choices' => array(
                        'Ja' => 'Ja',
                        'Nee' => 'Nee',
                        'Soms' => 'Soms',
                    ),
                    'label' => 'Drink je',
                    'expanded'=>false,
                ])
                ->add('vegetarisch', ChoiceType::class, [
                    'placeholder' => 'Maak een keuze',
                    'choices' => array(
                        'Nee' => 'Nee',
                        'Ja' => 'Ja',
                        'Soms' => 'Soms',
                    ),
                    'label' => 'Vegetarisch',
                    'expanded'=>false,
                ])
                ->add('lengte', ChoiceType::class, [
                    'placeholder' => 'Maak een keuze',
                    'choices' => array(
                    '<140cm'=> '<140cm',
                    '140-150cm' => '140-150cm',
                    '150-160cm' => '150-160cm',
                    '160-170cm' => '160-170cm',
                    '170-180cm' => '170-180cm',
                    '180-190cm' => '180-190cm',
                    '190-200cm' => '190-200cm',
                    '200-210cm' => '200-210cm',
                    '>210cm' => '>210cm',
                    ),
                    'label' => 'Hoe lang ben je',
                    'expanded'=>false,
                    ])
                ->add('afkomst', EntityType::class, ['class'=> BeheerAfkomst::class])
                ->add('bouw', EntityType::class, ['class'=> BeheerBouw::class])
                ->add('sport', EntityType::class, ['class'=> BeheerSport::class])
                ->add('haarkleur', EntityType::class, ['class'=> BeheerHaarkleur::class])
                ->add('religie', EntityType::class, ['class'=> BeheerReligie::class])
                ->add('opleiding', EntityType::class, ['class'=> BeheerOpleiding::class])
            ;
         };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getProvincie());
            }
        );

        $builder->get('provincie')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $provincie = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $provincie);
            }
        );

        //$builder->add('status', EntityType::class, ['class'=> BeheerStatus::class]);
    }




    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meta::class,
        ]);
    }
}
