<?php

namespace RockLab\CheckoutEditAddress\Controller\Index;

use Magento\Customer\Api\AddressRepositoryInterface;
use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Checkout\Model\Session;
use \Magento\Customer\Model\Data\RegionFactory;


class SaveEditForm extends Action
{
    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * SaveEditForm constructor.
     * @param Context $context
     * @param Session $checkoutSession
     */
    public function __construct(Context $context,
                                Session $checkoutSession,
                                \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
                                RegionFactory $regionFactory)
    {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
        $this->regionFactory = $regionFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $quote = $this->checkoutSession->getQuote();
        $resultPage = $this->resultFactory->create($this->resultFactory::TYPE_JSON);
        $formData = explode('&', $params['form']);
        $addressId = $params['customerAddressId'];
        $data = [];
        foreach ($formData as $key => $value) {

            $tmp = explode('=', $value);
            $tmp2 = explode('%', $tmp[0]);
            if (count($tmp2) > 1) {
                $data['street'][]= $tmp[1];
                continue;
            }
            $data[$tmp[0]] = $tmp[1];
        }

        /** @var \Magento\Customer\Api\Data\AddressInterface $address */
        $address = $this->addressRepository->getById($addressId);
        $address->setFirstname($data['firstname']);
        $address->setLastname($data['lastname']);
        $address->setCompany($data['company']);
        $address->setStreet($data['street']);
        $address->setCity($data['city']); // Update city
        $address->setCountryId($data['country_id']); // Update country id
        $address->setRegionId($data['region_id']);

        $region = $this->regionFactory->create();
        if ($data['region_id'] != 0) {
            $region->setRegionId($data['region_id']);
        } else $region->setRegion($data['region']);

        $address->setRegion($region);
        $address->setPostcode($data['postcode']);
        $address->setTelephone($data['telephone']);
        $this->addressRepository->save($address);
        return $resultPage;
    }
}
