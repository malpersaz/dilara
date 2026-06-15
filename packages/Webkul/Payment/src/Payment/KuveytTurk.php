<?php

namespace Webkul\Payment\Payment;

class KuveytTurk extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'kuveytturk';

    /**
     * Get redirect URL for Kuveyt Turk payment.
     */
    public function getRedirectUrl(): string
    {
        return route('kuveytturk.redirect');
    }

    /**
     * Check if payment method is available.
     */
    public function isAvailable(): bool
    {
        return parent::isAvailable() && $this->hasValidCredentials();
    }

    /**
     * Validate merchant credentials.
     */
    public function hasValidCredentials(): bool
    {
        return ! empty($this->getMerchantId())
            && ! empty($this->getCustomerId())
            && ! empty($this->getUsername())
            && ! empty($this->getPassword());
    }

    /**
     * Get Merchant ID from configuration.
     */
    public function getMerchantId(): ?string
    {
        return $this->getConfigData('merchant_id');
    }

    /**
     * Get Customer ID from configuration.
     */
    public function getCustomerId(): ?string
    {
        return $this->getConfigData('customer_id');
    }

    /**
     * Get API Username from configuration.
     */
    public function getUsername(): ?string
    {
        return $this->getConfigData('username');
    }

    /**
     * Get API Password from configuration.
     */
    public function getPassword(): ?string
    {
        return $this->getConfigData('password');
    }

    /**
     * Check if sandbox mode is enabled.
     */
    public function isSandbox(): bool
    {
        return (bool) $this->getConfigData('sandbox');
    }

    /**
     * Get payment gateway URL (3D Secure validation) based on environment.
     */
    public function getPaymentUrl(): string
    {
        return $this->isSandbox()
            ? 'https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelPayGate'
            : 'https://sanalpos.kuveytturk.com.tr/ServiceGateWay/Home/ThreeDModelPayGate';
    }

    /**
     * Get provision gateway URL (payment capture) based on environment.
     */
    public function getProvisionUrl(): string
    {
        return $this->isSandbox()
            ? 'https://boa.kuveytturk.com.tr/sanalposservice/Home/ThreeDModelProvisionGate'
            : 'https://sanalpos.kuveytturk.com.tr/ServiceGateWay/Home/ThreeDModelProvisionGate';
    }
}
