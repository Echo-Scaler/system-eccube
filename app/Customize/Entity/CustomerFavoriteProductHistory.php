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

namespace Customize\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\AbstractEntity;
use Eccube\Entity\Customer;
use Eccube\Entity\Product;

/**
 * CustomerFavoriteProductHistory
 *
 * အကြိုက်ဆုံးပစ္စည်း ထည့်သွင်းခြင်း/ဖယ်ရှားခြင်း သမိုင်းမှတ်တမ်း Entity
 *
 * @ORM\Table(name="dtb_customer_favorite_product_history")
 * @ORM\InheritanceType("NONE")
 * @ORM\Entity(repositoryClass="Customize\Repository\CustomerFavoriteProductHistoryRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CustomerFavoriteProductHistory extends AbstractEntity
{
    // Action Constants
    public const ACTION_REGISTER = 'register'; // အကြိုက်ဆုံးအဖြစ် ထည့်သွင်းခြင်း (Add Favorite)
    public const ACTION_REMOVE = 'remove';     // အကြိုက်ဆုံးမှ ဖျက်ထုတ်ခြင်း (Remove Favorite)

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Customer|null
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * })
     */
    private $Customer;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Product")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $Product;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=20)
     */
    private $action;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $create_date;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->create_date = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Customer.
     *
     * @param Customer|null $customer
     * @return CustomerFavoriteProductHistory
     */
    public function setCustomer(?Customer $customer = null)
    {
        $this->Customer = $customer;

        return $this;
    }

    /**
     * Get Customer.
     *
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * Set Product.
     *
     * @param Product $product
     * @return CustomerFavoriteProductHistory
     */
    public function setProduct(Product $product)
    {
        $this->Product = $product;

        return $this;
    }

    /**
     * Get Product.
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * Set action.
     *
     * @param string $action
     * @return CustomerFavoriteProductHistory
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     * @return CustomerFavoriteProductHistory
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }
}
