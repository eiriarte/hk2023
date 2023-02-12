<?php

namespace App\Form;

use App\Entity\Registration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationType extends AbstractType
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('surname')
            ->add('idCard')
            ->add('email', EmailType::class)
            ->add('phoneNumber', TelType::class)
            ->add('country', CountryType::class, [ 'placeholder' => $this->translator->trans('Seleccione un país') ])
            ->add('city')
            ->add('age', NumberType::class)
            ->add('member', CheckboxType::class, [ 'required' => false ])
            ->add('relative', CheckboxType::class, [ 'required' => false ])
            ->add('donation', MoneyType::class, [ 'required' => false ])
            ->add('reducedMobility', CheckboxType::class, [ 'required' => false ])
            ->add('public', CheckboxType::class, [ 'required' => false ])
            ->add('consent', CheckboxType::class, [ 'required' => true ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Registration::class,
        ]);
    }
}
