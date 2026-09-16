<?php

namespace App\Form;

use App\Entity\Autor;
use App\Entity\Assunto;
use App\Entity\Livro;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivroType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titulo', TextType::class, [
                'label' => 'Título',
                'attr' => ['maxlength' => 40],
            ])
            ->add('editora', TextType::class, [
                'label' => 'Editora',
                'required' => false,
                'attr' => ['maxlength' => 40],
            ])
            ->add('edicao', NumberType::class, [
                'label' => 'Edição',
                'required' => false,
            ])
            ->add('anoPublicacao', TextType::class, [
                'label' => 'Ano de publicação',
                'required' => false,
                'attr' => ['maxlength' => 4],
            ])
            ->add('valor', MoneyType::class, [
                'label' => 'Valor (R$)',
                'currency' => 'BRL',
            ])
            ->add('autores', EntityType::class, [
                'class' => Autor::class,
                'choice_label' => 'nome',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Autores',
                'attr' => ['size' => 5],
            ])
            ->add('assuntos', EntityType::class, [
                'class' => Assunto::class,
                'choice_label' => 'descricao',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'label' => 'Assuntos',
                'attr' => ['size' => 5],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Livro::class,
        ]);
    }
}