<?php
namespace HootSuite\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use HootSuite\PaymentBundle\Model\PaymentDetails as BasePaymentDetails;

/**
 * @ORM\Table(name="payum_payment_details")
 * @ORM\Entity
 */
class PaymentDetails extends BasePaymentDetails
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
}