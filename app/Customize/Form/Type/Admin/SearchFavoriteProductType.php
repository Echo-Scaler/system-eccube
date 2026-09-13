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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class SearchFavoriteProductType
 *
 * Admin Panel တွင် အကြိုက်ဆုံးပစ္စည်းများကို ရှာဖွေစစ်ဆေးသည့် Form Type ဖြစ်ပါသည်။
 * (Form type for searching favorite products in EC-CUBE Admin Panel)
 */
class SearchFavoriteProductType extends AbstractType
{
    /**
     * Build Form Fields
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('multi', TextType::class, [
                'label' => 'admin.common.multi_search_label',
                'required' => false,
                'attr' => [
                    'placeholder' => '商品名・商品コード・会員名・メールアドレス・会員ID',
                ],
            ])
            ->add('create_date_start', DateType::class, [
                'label' => 'admin.common.create_date__start',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' => '#admin_search_favorite_create_date_start',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
            ->add('create_date_end', DateType::class, [
                'label' => 'admin.common.create_date__end',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'datetimepicker-input',
                    'data-target' => '#admin_search_favorite_create_date_end',
                    'data-toggle' => 'datetimepicker',
                ],
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $form = $event->getForm();
                $create_date_start = $form['create_date_start']->getData();
                $create_date_end = $form['create_date_end']->getData();

                if (!empty($create_date_start) && !empty($create_date_end)) {
                    if ($create_date_start > $create_date_end) {
                        $form['create_date_end']->addError(new FormError('開始日は終了日以前の日付を指定してください。'));
                    }
                }
            });
    }

    /**
     * Block Prefix
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'admin_search_favorite';
    }
}
