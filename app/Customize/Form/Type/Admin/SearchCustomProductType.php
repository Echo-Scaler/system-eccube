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

use Eccube\Entity\Category;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\ProductStock;
use Eccube\Form\Type\Master\CategoryType as MasterCategoryType;
use Eccube\Form\Type\Master\ProductStatusType;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\Master\ProductStatusRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchCustomProductType
 *
 * Admin Panel တွင် Custom Product List ရှာဖွေစစ်ထုတ်ရန် Form Type ဖြစ်ပါသည်။
 * (Form Type for searching and filtering custom product list in admin panel)
 */
class SearchCustomProductType extends AbstractType
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var ProductStatusRepository
     */
    protected $productStatusRepository;

    /**
     * SearchCustomProductType constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param ProductStatusRepository $productStatusRepository
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductStatusRepository $productStatusRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productStatusRepository = $productStatusRepository;
    }

    /**
     * Form Fields များ တည်ဆောက်ခြင်း
     *
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ကုန်ပစ္စည်း ID, အမည် သို့မဟုတ် ကုန်ပစ္စည်း Code ဖြင့် ရှာဖွေခြင်း
            ->add('id', TextType::class, [
                'label' => 'admin.product.multi_search_label',
                'required' => false,
                'attr' => [
                    'placeholder' => 'admin.product.multi_search_label',
                ],
            ])
            // Category အလိုက် ရွေးချယ်စစ်ထုတ်ခြင်း
            ->add('category_id', MasterCategoryType::class, [
                'choice_label' => 'NameWithLevel',
                'label' => 'admin.product.category',
                'placeholder' => 'common.select__all_products',
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'choices' => $this->categoryRepository->getList(null, true),
                'choice_value' => function (Category $Category = null) {
                    return $Category ? $Category->getId() : null;
                },
            ])
            // ကုန်ပစ္စည်း အခြေအနေ (公開 / 非公開 / 廃止) စစ်ထုတ်ခြင်း
            ->add('status', ProductStatusType::class, [
                'label' => 'admin.product.display_status',
                'multiple' => true,
                'required' => false,
                'expanded' => true,
            ])
            // လက်ကျန် Stock အခြေအနေ (在庫あり / 在庫切れ)
            ->add('stock', ChoiceType::class, [
                'label' => 'admin.product.stock',
                'choices' => [
                    'admin.product.stock__in_stock' => ProductStock::IN_STOCK,
                    'admin.product.stock__out_of_stock' => ProductStock::OUT_OF_STOCK,
                ],
                'expanded' => true,
                'multiple' => true,
                'required' => false,
            ])
            // ထည့်သွင်းခဲ့သည့် နေ့စွဲ (စတင်ရက်)
            ->add('create_date_start', DateType::class, [
                'label' => 'admin.common.create_date__start',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            // ထည့်သွင်းခဲ့သည့် နေ့စွဲ (ကုန်ဆုံးရက်)
            ->add('create_date_end', DateType::class, [
                'label' => 'admin.common.create_date__end',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
            ]);
    }

    /**
     * Form Options သတ်မှတ်ခြင်း
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * Block Prefix သတ်မှတ်ခြင်း
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'admin_search_custom_product';
    }
}
