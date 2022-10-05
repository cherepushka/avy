<?php

namespace App\Http\Request\AdminCatalogUpload;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file', FileType::class)
            ->add('originFilename', TextType::class)
            ->add('manufacturer', TextType::class)
            ->add('lang', TextType::class)
            ->add('text', TextType::class)
            ->add('categoryIds', TextType::class)
        ;
    }
}
