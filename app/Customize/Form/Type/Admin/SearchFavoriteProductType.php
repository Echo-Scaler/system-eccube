<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchFavoriteProductType
 *
 * Admin Favorite Products Search Form Type
 */
class SearchFavoriteProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Product ID (Exact or comma-separated)
            ->add('id', TextType::class, [
                'label' => 'admin.product.product_id',
                'required' => false,
                'attr' => [
                    'placeholder' => '商品ID',
                ],
            ])
            // Product Name / Code
            ->add('name', TextType::class, [
                'label' => 'admin.product.name',
                'required' => false,
                'attr' => [
                    'placeholder' => '商品名・商品コード',
                ],
            ])
            // Customer Name / Kana
            ->add('customer_name', TextType::class, [
                'label' => 'admin.customer.name',
                'required' => false,
                'attr' => [
                    'placeholder' => '会員名・カナ・メールアドレス',
                ],
            ])
            // Favorite Count Minimum
            ->add('favorite_count_min', IntegerType::class, [
                'label' => 'お気に入り数(下限)',
                'required' => false,
                'constraints' => [
                    new Assert\PositiveOrZero(),
                ],
                'attr' => [
                    'placeholder' => '0',
                    'min' => 0,
                ],
            ])
            // Favorite Count Maximum
            ->add('favorite_count_max', IntegerType::class, [
                'label' => 'お気に入り数(上限)',
                'required' => false,
                'constraints' => [
                    new Assert\PositiveOrZero(),
                ],
                'attr' => [
                    'placeholder' => '999',
                    'min' => 0,
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $favMin = $form['favorite_count_min']->getData();
                $favMax = $form['favorite_count_max']->getData();

                if ($favMin !== null && $favMax !== null && $favMin > $favMax) {
                    $form['favorite_count_max']->addError(new FormError('上限値は下限値以上の数値を指定してください。'));
                }
            });
    }

    public function getBlockPrefix(): string
    {
        return 'admin_search_favorite';
    }
}
