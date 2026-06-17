<?php

namespace Webkul\Payment\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Payment\Payment\KuveytTurk;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;
use Webkul\Sales\Transformers\OrderResource;

class KuveytTurkController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
        protected OrderTransactionRepository $orderTransactionRepository,
        protected CartRepository $cartRepository,
        protected KuveytTurk $kuveytTurk
    ) {}

    /**
     * Redirect to Kuveyt Turk card payment input page.
     *
     * @return View|RedirectResponse
     */
    public function redirect()
    {
        if (! $this->kuveytTurk->hasValidCredentials()) {
            session()->flash('error', 'Kuveyt Türk Sanal POS bilgileri eksik veya geçersiz.');

            return redirect()->route('shop.checkout.cart.index');
        }

        $cart = Cart::getCart();

        if (! $cart) {
            session()->flash('error', 'Sepet bulunamadı.');

            return redirect()->route('shop.checkout.cart.index');
        }

        return view('payment::checkout.kuveytturk-card', compact('cart'));
    }

    /**
     * Process 3D Secure payment request.
     *
     * @return RedirectResponse|Response
     */
    public function process()
    {
        if (request()->has('card_number')) {
            request()->merge([
                'card_number' => str_replace(' ', '', request()->input('card_number')),
            ]);
        }

        $validatedData = request()->validate([
            'cardholder_name' => 'required|string|max:100',
            'card_number' => 'required|numeric|digits_between:15,16',
            'expiration_month' => 'required|numeric|digits:2',
            'expiration_year' => 'required|numeric|digits:2',
            'cvv' => 'required|numeric|digits_between:3,4',
        ]);


        $cart = Cart::getCart();

        if (! $cart) {
            session()->flash('error', 'Sepet bulunamadı.');

            return redirect()->route('shop.checkout.cart.index');
        }

        try {
            $merchantId = $this->kuveytTurk->getMerchantId();
            $customerId = $this->kuveytTurk->getCustomerId();
            $username = $this->kuveytTurk->getUsername();
            $password = $this->kuveytTurk->getPassword();

            // Prepare API Credentials
            $hashedPassword = base64_encode(sha1($password, true));

            // Format Amount as kurus (e.g. 100.50 TRY -> 10050)
            $amount = (int) round($cart->base_grand_total * 100);

            // Generate unique Merchant Order ID
            $merchantOrderId = 'KUVEYT_TURK_'.$cart->id.'_'.Str::upper(Str::random(8));

            $okUrl = route('kuveytturk.callback');
            $failUrl = route('kuveytturk.callback');

            // Calculate HashData for ThreeDModelPayGate
            $hashString = $merchantId.$merchantOrderId.$amount.$okUrl.$failUrl.$username.$hashedPassword;
            $hashData = base64_encode(sha1($hashString, true));

            $url = $this->kuveytTurk->getPaymentUrl();

            // Build XML Request body
            $xml = '<?xml version="1.0" encoding="utf-8"?>
<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <APIVersion>TDV2.0.0</APIVersion>
    <OkUrl>'.$okUrl.'</OkUrl>
    <FailUrl>'.$failUrl.'</FailUrl>
    <HashData>'.$hashData.'</HashData>
    <MerchantId>'.$merchantId.'</MerchantId>
    <CustomerId>'.$customerId.'</CustomerId>
    <UserName>'.$username.'</UserName>
    <Amount>'.$amount.'</Amount>
    <DisplayAmount>'.$amount.'</DisplayAmount>
    <CurrencyCode>0949</CurrencyCode>
    <CardNumber>'.$validatedData['card_number'].'</CardNumber>
    <CardHolderName>'.$validatedData['cardholder_name'].'</CardHolderName>
    <CardExpireDateYear>'.$validatedData['expiration_year'].'</CardExpireDateYear>
    <CardExpireDateMonth>'.$validatedData['expiration_month'].'</CardExpireDateMonth>
    <CardCVV2>'.$validatedData['cvv'].'</CardCVV2>
    <TransactionType>Sale</TransactionType>
    <TransactionSecurity>3</TransactionSecurity>
    <InstallmentCount>1</InstallmentCount>
    <MerchantOrderId>'.$merchantOrderId.'</MerchantOrderId>
</KuveytTurkVPosMessage>';

            // Send POST request
            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
            ])->withBody($xml, 'text/xml')->post($url);

            if ($response->failed()) {
                session()->flash('error', 'Banka sunucusuna bağlanılamadı.');

                return redirect()->back()->withInput();
            }

            // Return the bank's auto-submit HTML page which redirects to ACS/3D page
            return response($response->body());
        } catch (\Exception $e) {
            logger()->error('Kuveyt Turk VPOS Request Failed: '.$e->getMessage());
            session()->flash('error', 'Ödeme isteği sırasında bir hata oluştu: '.$e->getMessage());

            return redirect()->back()->withInput();
        }
    }

    /**
     * Handle payment callback from Kuveyt Turk bank.
     *
     * @return RedirectResponse
     */
    public function callback()
    {
        logger()->info('Kuveyt Turk VPOS Callback Params: ', request()->all());

        if (request()->has('AuthenticationResponse')) {
            $xmlContent = html_entity_decode(urldecode(request()->input('AuthenticationResponse')));
            $xml = simplexml_load_string($xmlContent);
            if ($xml !== false) {
                logger()->info('Kuveyt Turk VPOS Decoded XML: ', (array) $xml);
                request()->merge([
                    'ResponseCode'    => (string) $xml->ResponseCode,
                    'MerchantOrderId' => (string) $xml->MerchantOrderId,
                    'OrderId'         => (string) $xml->OrderId,
                    'MD'              => (string) $xml->MD,
                    'ResponseMessage' => (string) $xml->ResponseMessage,
                ]);
            }
        }

        $responseCode = request()->input('ResponseCode');
        $merchantOrderId = request()->input('MerchantOrderId');
        $orderId = request()->input('OrderId');
        $md = request()->input('MD');
        $responseMessage = request()->input('ResponseMessage') ?? '3D doğrulama başarısız oldu.';



        if ($responseCode !== '00' || empty($md)) {
            session()->flash('error', 'Ödeme başarısız: '.$responseMessage);

            return redirect()->route('shop.checkout.cart.index');
        }

        try {
            // Retrieve Cart ID from MerchantOrderId
            $parts = explode('_', $merchantOrderId);
            $cartId = $parts[2] ?? null;

            if (! $cartId) {
                session()->flash('error', 'Sepet bulunamadı.');

                return redirect()->route('shop.checkout.cart.index');
            }

            $cart = $this->cartRepository->find($cartId);

            if (! $cart) {
                session()->flash('error', 'Sepet bulunamadı.');

                return redirect()->route('shop.checkout.cart.index');
            }

            // Capture/Provision payment step via ThreeDModelProvisionGate
            $merchantId = $this->kuveytTurk->getMerchantId();
            $customerId = $this->kuveytTurk->getCustomerId();
            $username = $this->kuveytTurk->getUsername();
            $password = $this->kuveytTurk->getPassword();

            $hashedPassword = base64_encode(sha1($password, true));
            $amount = (int) round($cart->base_grand_total * 100);

            // Calculate HashData for ThreeDModelProvisionGate
            $hashString = $merchantId.$merchantOrderId.$amount.$username.$hashedPassword;
            $hashData = base64_encode(sha1($hashString, true));

            $provisionUrl = $this->kuveytTurk->getProvisionUrl();

            // Build XML Request body for Provision
            $provisionXml = '<?xml version="1.0" encoding="utf-8"?>
<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <APIVersion>TDV2.0.0</APIVersion>
    <HashData>'.$hashData.'</HashData>
    <MerchantId>'.$merchantId.'</MerchantId>
    <CustomerId>'.$customerId.'</CustomerId>
    <UserName>'.$username.'</UserName>
    <TransactionType>Sale</TransactionType>
    <InstallmentCount>1</InstallmentCount>
    <Amount>'.$amount.'</Amount>
    <MerchantOrderId>'.$merchantOrderId.'</MerchantOrderId>
    <TransactionSecurity>3</TransactionSecurity>
    <KuveytTurkVPosAdditionalData>
        <AdditionalData>
            <Key>MD</Key>
            <Data>'.$md.'</Data>
        </AdditionalData>
    </KuveytTurkVPosAdditionalData>
</KuveytTurkVPosMessage>';

            // Send provision POST request
            $response = Http::withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
            ])->withBody($provisionXml, 'text/xml')->post($provisionUrl);

            if ($response->failed()) {
                session()->flash('error', 'Ödeme onaylama işlemi sırasında banka ile bağlantı kurulamadı.');

                return redirect()->route('shop.checkout.cart.index');
            }

            // Parse response XML
            $xmlResponse = simplexml_load_string($response->body());

            if ($xmlResponse === false) {
                session()->flash('error', 'Banka onay cevabı çözümlenemedi.');

                return redirect()->route('shop.checkout.cart.index');
            }

            $provisionCode = (string) $xmlResponse->ResponseCode;
            $provisionMessage = (string) $xmlResponse->ResponseMessage ?? 'Ödeme provizyon aşamasında reddedildi.';

            if ($provisionCode !== '00') {
                session()->flash('error', 'Ödeme onaylanmadı: '.$provisionMessage);

                return redirect()->route('shop.checkout.cart.index');
            }

            // Check if order already exists
            $existingOrder = $this->orderRepository->findOneWhere(['cart_id' => $cartId]);

            if (! $existingOrder) {
                Cart::setCart($cart);
                Cart::collectTotals();

                $data = (new OrderResource($cart))->jsonSerialize();

                $data['payment']['additional'] = [
                    'kuveytturk_merchant_order_id' => $merchantOrderId,
                    'kuveytturk_order_id' => $orderId,
                    'kuveytturk_status' => 'success',
                ];

                $order = $this->orderRepository->create($data);

                $this->orderRepository->update(['status' => 'processing'], $order->id);

                if ($order->canInvoice()) {
                    $invoice = $this->invoiceRepository->create($this->prepareInvoiceData($order));

                    $this->orderTransactionRepository->create([
                        'transaction_id' => $orderId,
                        'status' => 'success',
                        'type' => $order->payment->method,
                        'payment_method' => $order->payment->method,
                        'order_id' => $order->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $order->base_grand_total,
                        'data' => json_encode($xmlResponse),
                    ]);
                }

                Cart::deActivateCart();
            }

            session()->flash('order_id', $existingOrder?->id ?? $order->id);
            session()->flash('success', 'Ödemeniz başarıyla tamamlandı.');

            return redirect()->route('shop.checkout.onepage.success');
        } catch (\Exception $e) {
            logger()->error('Kuveyt Turk VPOS Capture Failed: '.$e->getMessage());
            session()->flash('error', 'Ödeme provizyonu sırasında bir hata oluştu: '.$e->getMessage());

            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Prepare invoice data.
     *
     * @param  object  $order
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }
}
