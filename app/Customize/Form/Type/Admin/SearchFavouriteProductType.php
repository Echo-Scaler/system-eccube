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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchFavouriteProductType
 *
 * Admin Panel တွင် Favorite ကုန်ပစ္စည်းများကို စစ်ထုတ်ရှာဖွေရန် Form Type ဖြစ်ပါသည်။
 */
class SearchFavouriteProductType extends AbstractType
{
    /**
     * Form Fields များ တည်ဆောက်ခြင်း
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('product_id', IntegerType::class, [
                'required' => false,
                'label' => '商品ID',
                'attr' => [
                    'placeholder' => '商品ID',
                ],
            ])
            ->add('product_name', TextType::class, [
                'required' => false,
                'label' => '商品名（部分一致）',
                'attr' => [
                    'placeholder' => '商品名',
                ],
            ])
            ->add('customer_name', TextType::class, [
                'required' => false,
                'label' => '会員名（部分一致）',
                'attr' => [
                    'placeholder' => '会員名',
                ],
            ]);
    }

    /**
     * Form Options သတ်မှတ်ခြင်း (GET Method & No CSRF for Search URL query)
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }

    /**
     * Form Field Name Prefix ဖယ်ရှားခြင်း (URL query တွင် product_id=... တိုက်ရိုက်ဖြစ်စေရန်)
     *
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
