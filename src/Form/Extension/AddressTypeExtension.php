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
        if (!array_key_exists('customerField', $options) || true !== $options['customerField']) {
            return;
        }

        $data = $options['data'] ?? null;
        if ($data instanceof AddressInterface && $data->getId()) {
            return;
        }

        $builder->add(
            'customer',
            CustomerChoiceType::class,
            [
                'choice_value' => 'id',
                'constraints' => [
                    new NotBlank(['groups' => ['sylius']]),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'customerField' => false,
            ])
            ->setAllowedTypes('customerField', 'bool')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedTypes(): array
    {
        return [AddressType::class];
    }
}
