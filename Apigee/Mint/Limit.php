<?php
namespace Apigee\Mint;

use Apigee\Exceptions\ParameterException;
use Apigee\Util\OrgConfig;

class Limit extends Base\BaseObject
{

    /**
     * Limit id
     * @var string
     */
    private $id;

    /**
     * Limit Name
     * @var string
     */
    private $name;

    /**
     * Organization
     * @var \Apigee\Mint\Organization
     */
    private $organization;

    /**
     * Organization
     * @var string
     */
    private $subOrganization;

    private $startDate;

    private $limitKey;

    /**
     * DeveloperCategory // Note: Currently not supported
     * @var string
     */
    private $developerCategory;

    /**
     * Developer
     * @var string
     */
    private $developer;

    /**
     * MonetizationPackage
     * @var  string
     */
    private $monetizationPackage;

    /**
     * Product
     * @var string
     */
    private $product;

    /**
     * Application
     * @var string
     */
    private $application;

    /**
     * User is from product custom attributes
     * This can be any product custom attribute which
     * is unique across an org. For now it is just user...
     * @var string
     */
    private $userId;

    /**
     * com.apigee.mint.model.Developer.BillingType
     * @var string
     */
    private $developerBillingType;

    /**
     * Quota limit (e.g. $100 in USD or 1000 as number of transactions)
     * @var double
     */
    private $quotaLimit;

    /**
     * Currency
     * @var string
     */
    private $currency;

    /**
     * QuotaType
     * @var string
     */
    private $quotaType;

    /**
     * QuotaPeriodType
     * @var string
     */
    private $quotaPeriodType;

    /**
     * Duration
     * @var int
     */
    private $duration;

    /**
     * Duration units
     * @var string
     */
    private $durationType;

    /**
     * If limit is published (i.e. final)
     * @var bool
     */
    private $published;

    /**
     * If limit has reached, halt call execution
     * @var bool
     */
    private $haltExecution;

    public function __construct(OrgConfig $config)
    {
        $base_url = '/mint/organizations/' . rawurlencode($config->orgName) . '/limits';
        $this->init($config, $base_url);

        $this->wrapperTag = 'limit';
        $this->idField = 'id';
        $this->idIsAutogenerated = false;

        $this->initValues();
    }

    public function getDeveloperLimits($developer_id, $package_id = null, $halt = null)
    {
        $query = array('dev' => $developer_id);
        if (isset($package_id)) {
            $query['pkg'] = $package_id;
        }
        if (isset($halt)) {
            $query['halt'] = ($halt ? 'true' : 'false');
        }

        $options = array(
            'query' => $query,
        );
        $url = '/mint/organizations/' . rawurlencode($this->config->orgName) . '/limits';
        $this->setBaseUrl($url);
        $this->get(null, 'application/json; charset=utf-8', array(), $options);
        $this->restoreBaseUrl();

        $response = $this->responseObj;
        $limits = array();
        foreach ($response[$this->wrapperTag] as $limit_item) {
            $limit = new Limit($this->config);
            $limit->loadFromRawData($limit_item);
            $limits[] = $limit;
        }
        return $limits;
    }

    /**
     * Implements BaseObject::instantiateNew()
     *
     * @return \Apigee\Mint\Limit
     */
    public function instantiateNew()
    {
        return new Limit($this->config);
    }

    /**
     * Implements BaseObject::initValues()
     */
    protected function initValues()
    {
        $this->name = null;
        $this->organization = null;
        $this->subOrganization = null;
        $this->developerCategory = null;
        $this->developer = null;
        $this->monetizationPackage = null;
        $this->product = null;
        $this->application = null;
        $this->userId = null;
        $this->developerBillingType = null;
        $this->quotaLimit = 0;
        $this->currency = null;
        $this->quotaType = null;
        $this->quotaPeriodType = null;
        $this->duration = 0;
        $this->durationType = null;
        $this->published = false;
        $this->haltExecution = false;
    }

    /**
     * Implements BaseObject::loadFromRawData($data, $reset = false)
     *
     * @param array $data
     * @param bool $reset
     */
    public function loadFromRawData($data, $reset = false)
    {
        if ($reset) {
            $this->initValues();
        }

        if (isset($data['organization'])) {
            $organization = new Organization($this->config);
            $organization->loadFromRawData($data['organization']);
            $this->organization = $organization;
        }
        $excluded_properties = array('organization');
        foreach (array_keys($data) as $property) {
            if (in_array($property, $excluded_properties)) {
                continue;
            }

            // form the setter method name to invoke setXxxx
            $setter_method = 'set' . ucfirst($property);

            if (method_exists($this, $setter_method)) {
                $this->$setter_method($data[$property]);
            } else {
                self::$logger->notice('No setter method was found for property "' . $property . '"');
            }
        }
    }

    /**
     * Implements BaseObject::__toString()
     */
    public function __toString()
    {
        // @TODO Verify
        $obj = array();
        $excluded_properties = array_keys(get_class_vars(get_parent_class($this)));
        $properties = array_keys(get_object_vars($this));
        foreach ($properties as $property) {
            if (in_array($property, $excluded_properties)) {
                continue;
            }
            if (isset($this->$property)) {
                if (is_object($this->$property)) {
                    $obj[$property] = json_decode((string)$this->$property, true);
                } else {
                    $obj[$property] = $this->$property;
                }
            }
        }
        return json_encode($obj);
    }

    // accessors(getters/setters)

    /**
     * Get Limit id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Limit Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Organization
     *
     * @return \Apigee\Mint\Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Get SubOrganization name
     *
     * @return string
     */
    public function getSubOrganization()
    {
        return $this->subOrganization;
    }

    /**
     * Get DeveloperCategory // Note: Currently not supported
     *
     * @return string
     */
    public function getDeveloperCategory()
    {
        return $this->developerCategory;
    }

    /**
     * Get Developer
     *
     * @return string
     */
    public function getDeveloper()
    {
        return $this->developer;
    }

    /**
     * Get MonetizationPackage
     *
     * @return  string
     */
    public function getMonetizationPackage()
    {
        return $this->monetizationPackage;
    }

    /**
     * Get Product
     *
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Get Application
     *
     * @return string
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get User id. User is from product custom attributes
     * This can be any product custom attribute which
     * is unique across an org. For now it is just user...
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Get com.apigee.mint.model.Developer.BillingType
     *
     * @return string
     */
    public function getDeveloperBillingType()
    {
        return $this->developerBillingType;
    }

    /**
     * Get Quota limit (e.g. $100 in USD or 1000 as number of transactions)
     *
     * @return double
     */
    public function getQuotaLimit()
    {
        return $this->quotaLimit;
    }

    /**
     * Get Currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Get QuotaType
     *
     * @return string
     */
    public function getQuotaType()
    {
        return $this->quotaType;
    }

    /**
     * Get QuotaPeriodType
     *
     * @return string
     */
    public function getQuotaPeriodType()
    {
        return $this->quotaPeriodType;
    }

    /**
     * Get Duration
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Get Duration units
     *
     * @return string
     */
    public function getDurationType()
    {
        return $this->durationType;
    }

    /**
     * Get if limit is published (i.e. final)
     *
     * @return bool
     */
    public function isPublished()
    {
        return $this->published;
    }

    /**
     * Get if limit has reached, halt call execution
     *
     * @return bool
     */
    public function getHaltExecution()
    {
        return $this->haltExecution;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getLimitKey()
    {
        return $this->limitKey;
    }

    /**
     * Set Limit id
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set Limit Name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Set Organization
     *
     * @param \Apigee\Mint\Organization $organization
     */
    public function setOrganization($organization)
    {
        $this->organization = $organization;
    }

    /**
     * Set SubOrganization name
     *
     * @param string $sub_organization
     */
    public function setSubOrganization($sub_organization)
    {
        $this->subOrganization = $sub_organization;
    }

    /**
     * Set DeveloperCategory // Note: Currently not supported
     *
     * @param string $developer_category
     */
    public function setDeveloperCategory($developer_category)
    {
        $this->developerCategory = $developer_category;
    }

    /**
     * Set Developer
     *
     * @param string $developer
     */
    public function setDeveloper($developer)
    {
        $this->developer = $developer;
    }

    /**
     * Set MonetizationPackage
     *
     * @param string $monetization_package
     */
    public function setMonetizationPackage($monetization_package)
    {
        $this->monetizationPackage = $monetization_package;
    }

    /**
     * Set Product
     *
     * @param string $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * Set Application
     *
     * @param string $application
     */
    public function setApplication($application)
    {
        $this->application = $application;
    }

    /**
     * Set User id. User is from product custom attributes
     * This can be any product custom attribute which
     * is unique across an org. For now it is just user...
     *
     * @param string $user_id
     */
    public function setUserId($user_id)
    {
        $this->userId = $user_id;
    }

    /**
     * Set com.apigee.mint.model.Developer.BillingType
     *
     * @param string $developer_billing_type
     */
    public function setDeveloperBillingType($developer_billing_type)
    {
        $this->developerBillingType = $developer_billing_type;
    }

    /**
     * Set Quota limit (e.g. $100 in USD or 1000 as number of transactions)
     *
     * @param double $quota_limit
     */
    public function setQuotaLimit($quota_limit)
    {
        $this->quotaLimit = $quota_limit;
    }

    /**
     * Set Currency
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Set QuotaType
     *
     * @param string $quota_type Allowed values:
     * [Transactions|CreditLimit|SpendLimit|FeeExposure|Balance]
     * @throws \Apigee\Exceptions\ParameterException
     */
    public function setQuotaType($quota_type)
    {
        if (!in_array($quota_type, array('Transactions', 'CreditLimit', 'SpendLimit', 'FeeExposure', 'Balance'))) {
            throw new ParameterException('Invalid Quota Type value: ' . $quota_type);
        }
        $this->quotaType = $quota_type;
    }

    /**
     * Set QuotaPeriodType
     *
     * @param string $quota_period_type Allowed values: [CALENDAR|USAGE_START|ROLLING]
     * @throws \Apigee\Exceptions\ParameterException
     */
    public function setQuotaPeriodType($quota_period_type)
    {
        if (!in_array($quota_period_type, array('CALENDAR', 'USAGE_START', 'ROLLING'))) {
            throw new ParameterException('Invalid Quota Period Type value: ' . $quota_period_type);
        }
        $this->quotaPeriodType = $quota_period_type;
    }

    /**
     * Set Duration
     *
     * @param int $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * Set Duration units
     *
     * @param string $duration_type Allowed values [DAY|WEEK|MONTH|QUARTER|YEAR]
     * @throws \Apigee\Exceptions\ParameterException
     */
    public function setDurationType($duration_type)
    {
        $duration_type = strtoupper($duration_type);
        if (!in_array($duration_type, array('DAY', 'WEEK', 'MONTH', 'QUARTER', 'YEAR'))) {
            throw new ParameterException('Invalid duration type: ' . $duration_type);
        }
        $this->durationType = $duration_type;
    }

    /**
     * Set if limit is published (i.e. final)
     *
     * @param bool $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * Set if limit has reached, halt call execution
     *
     * @param bool $halt_execution
     */
    public function setHaltExecution($halt_execution)
    {
        $this->haltExecution = $halt_execution;
    }

    public function setStartDate($start_date)
    {
        $this->startDate = $start_date;
    }

    public function setLimitKey($limit_key)
    {
        $this->limitKey = $limit_key;
    }
}
