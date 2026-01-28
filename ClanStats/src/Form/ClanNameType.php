<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClanNameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("nomClan", TextType::class, [
                "label" => "Nom Clan : ",
                "attr" => [
                    "placeholder" => "Entrez nom du clan",
                    "class" => "form-control",
                    "data-ajax-field" => "true"
                ],
                "constraints" => [
                    new NotBlank([
                        "message" => "Veuillez entrer un nom",
                    ])
                ],
                "trim" => true,
                "error_bubbling" => false
            ])
            ->add("minMembers", IntegerType::class, [
                "label" => "Membres minimum :",
                "attr" => [
                    "placeholder" => "Nombre de membres",
                    "class" => "form-control",
                    "data-ajax-field" => "true"
                ],
                "constraints" => [
                    new GreaterThanOrEqual([
                        "value" => 2,
                        "message" => "Le nombre minimum doit être au moins 2"
                    ]),
                ],
                "required" => false,
                "error_bubbling" => false
            ])
            ->add("maxMembers", IntegerType::class, [
                "label" => "Membres maximum :",
                "attr" => [
                    "placeholder" => "Nombre de membres",
                    "class" => "form-control",
                    "data-ajax-field" => "true"
                ],
                "constraints" => [
                    new LessThanOrEqual([
                        "value" => 50,
                        "message" => "Le nombre maximum doit être au plus 50"
                    ]),
                ],
                "required" => false,
                "error_bubbling" => false
            ])
            ->add("minScore", IntegerType::class, [
                "label" => "Score minimum :",
                "attr" => [
                    "placeholder" => "Score du clan",
                    "class" => "form-control",
                    "data-ajax-field" => "true"
                ],
                "constraints" => [
                    new GreaterThanOrEqual([
                        "value" => 1,
                        "message" => "Le score minimum doit être positif ou nul"
                    ]),
                ],
                "required" => false,
                "error_bubbling" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
            "csrf_field_name" => "_token",
            "csrf_protection" => true,
            "attr" => [
                "id" => "nom-clan-form",
                "data-ajax-form" => "true"
            ],
            // Options importantes pour fetch API
            "method" => "POST",
            "action" => "",
        ]);
    }
}
