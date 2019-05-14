<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) MASSIVE ART WebServices GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\SyliusProducerPlugin\Form\Extension;

use Sylius\Bundle\AddressingBundle\Form\Type\AddressType;
use Sylius\Bundle\CustomerBundle\Form\Type\CustomerChoiceType;
use Sylius\Component\Addressing\Model\AddressInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class AddressTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'] ?? null;
        if ($data instanceof AddressInterface && $data->getId()) {
            return;
        }

        if (array_key_exists('customerField', $options) && true === $options['customerField']) {
            $builder->add(
                    'customer',
                    CustomerChoiceType::class,
                    [
                        'choice_value' => 'id',
                        'constraints' => [
                            new NotBlank(['groups' => ['sylius']]),
                        ],
                    ]
            );
        }

        if (array_key_exists('provinceCodeField', $options) && true === $options['provinceCodeField']) {
            $builder->add(
                'provinceCode',
                TextType::class,
                [
                    'required' => false,
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'customerField' => false,
                'provinceCodeField' => false,
            ])
            ->setAllowedTypes('customerField', 'bool')
            ->setAllowedTypes('provinceCodeField', 'bool')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): array
    {
        return [AddressType::class];
    }
}
