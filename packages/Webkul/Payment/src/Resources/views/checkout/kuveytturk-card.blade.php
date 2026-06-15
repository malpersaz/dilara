<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Güvenli Ödeme - Kuveyt Türk Sanal POS</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: #0b0f19;
            color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Background Glows */
        .glow-1 {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, rgba(99, 102, 241, 0) 70%);
            top: -10%;
            left: -10%;
            z-index: 0;
            pointer-events: none;
        }

        .glow-2 {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0) 70%);
            bottom: -10%;
            right: -10%;
            z-index: 0;
            pointer-events: none;
        }

        .container {
            width: 100%;
            max-width: 1000px;
            margin: 20px;
            background: rgba(17, 24, 39, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            overflow: hidden;
            z-index: 10;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
            .sidebar {
                order: -1;
            }
        }

        /* Sidebar - Order Summary */
        .sidebar {
            background: rgba(15, 23, 42, 0.6);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        @media (max-width: 768px) {
            .sidebar {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                padding: 30px;
            }
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .brand-logo svg {
            width: 36px;
            height: 36px;
            fill: #10b981;
        }

        .brand-name {
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #fff;
        }

        .order-title {
            font-size: 16px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .order-total-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .order-total-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #10b981;
        }

        .total-label {
            font-size: 14px;
            color: #9ca3af;
            margin-bottom: 6px;
        }

        .total-amount {
            font-size: 32px;
            font-weight: 700;
            color: #fff;
        }

        .cart-items {
            flex-grow: 1;
            margin-bottom: 30px;
            max-height: 200px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .cart-items::-webkit-scrollbar {
            width: 4px;
        }

        .cart-items::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 14px;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-details {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .item-name {
            color: #e5e7eb;
            font-weight: 500;
        }

        .item-qty {
            color: #9ca3af;
            font-size: 12px;
        }

        .item-price {
            color: #fff;
            font-weight: 600;
        }

        .secure-footer {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #9ca3af;
            font-size: 13px;
        }

        .secure-footer svg {
            width: 18px;
            height: 18px;
            fill: #9ca3af;
        }

        /* Main Content - Card & Form */
        .main-content {
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 30px 20px;
            }
        }

        /* 3D Interactive Card Container */
        .card-wrapper {
            perspective: 1000px;
            width: 100%;
            max-width: 360px;
            height: 220px;
            margin-bottom: 35px;
        }

        .credit-card {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
        }

        .credit-card.flipped {
            transform: rotateY(180deg);
        }

        .card-face {
            position: absolute;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            color: #fff;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        /* Front Face Style */
        .card-front {
            background: linear-gradient(135deg, #0d1527 0%, #1e293b 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .card-front::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, rgba(16, 185, 129, 0) 70%);
            pointer-events: none;
        }

        .card-front-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-chip {
            width: 45px;
            height: 35px;
            background: linear-gradient(135deg, #ffd700 0%, #cca100 100%);
            border-radius: 6px;
            position: relative;
            overflow: hidden;
        }

        .card-chip::after {
            content: '';
            position: absolute;
            top: 10%;
            left: 10%;
            width: 80%;
            height: 80%;
            border: 1px solid rgba(0,0,0,0.15);
            border-radius: 4px;
        }

        .card-logo {
            height: 32px;
            display: flex;
            align-items: center;
        }

        .card-logo img {
            height: 100%;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
            transition: all 0.3s ease;
        }

        .card-number {
            font-size: 20px;
            letter-spacing: 2.5px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0,0,0,0.4);
        }

        .card-info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .card-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 4px;
        }

        .card-value {
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Back Face Style */
        .card-back {
            background: linear-gradient(135deg, #0d1527 0%, #0f172a 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transform: rotateY(180deg);
            padding: 24px 0;
            justify-content: flex-start;
            gap: 20px;
        }

        .black-magnetic-stripe {
            width: 100%;
            height: 45px;
            background: #000;
        }

        .signature-strip-container {
            padding: 0 24px;
            width: 100%;
        }

        .signature-strip {
            width: 100%;
            height: 38px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding-right: 12px;
            position: relative;
        }

        .signature-strip::before {
            content: '•••• •••• •••• ••••';
            position: absolute;
            left: 12px;
            color: rgba(255,255,255,0.3);
            font-style: italic;
            font-size: 12px;
            letter-spacing: 1px;
        }

        .card-cvv-val {
            color: #000;
            background: #fff;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .card-back-text {
            padding: 0 24px;
            font-size: 8px;
            color: #64748b;
            line-height: 1.3;
        }

        /* Form Styling */
        .payment-form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #9ca3af;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 14px 16px;
            color: #fff;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            background: rgba(255, 255, 255, 0.05);
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }

        .form-input::placeholder {
            color: #4b5563;
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            width: 100%;
            display: none;
        }

        /* Pay Button */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 12px;
            padding: 16px;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.3);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit:disabled {
            background: #1f2937;
            color: #4b5563;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 0.8s linear infinite;
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="glow-1"></div>
    <div class="glow-2"></div>

    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div>
                <div class="brand-logo">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    </svg>
                    <span class="brand-name">Dilara Kozmetik</span>
                </div>

                <div class="order-title">Sipariş Özeti</div>
                
                <div class="cart-items">
                    @foreach($cart->items as $item)
                        <div class="cart-item">
                            <div class="item-details">
                                <span class="item-name">{{ $item->name }}</span>
                                <span class="item-qty">{{ $item->quantity }} Adet</span>
                            </div>
                            <span class="item-price">{{ core()->formatBasePrice($item->base_total) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="order-total-card">
                    <div class="total-label">Ödenecek Toplam Tutar</div>
                    <div class="total-amount">{{ core()->formatBasePrice($cart->base_grand_total) }}</div>
                </div>

                <div class="secure-footer">
                    <svg viewBox="0 0 24 24">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                    <span>256-Bit SSL ile Güvenli Ödeme Altyapısı</span>
                </div>
            </div>
        </div>

        <!-- Main Content (Form & Card) -->
        <div class="main-content">
            <!-- Credit Card Preview -->
            <div class="card-wrapper">
                <div class="credit-card" id="creditCard">
                    <!-- Front Face -->
                    <div class="card-face card-front">
                        <div class="card-front-header">
                            <div class="card-chip"></div>
                            <div class="card-logo" id="cardLogo">
                                <span style="font-weight: 700; font-size: 16px; letter-spacing: 0.5px; opacity: 0.5;">CARD</span>
                            </div>
                        </div>
                        <div class="card-number" id="cardNoPreview">•••• •••• •••• ••••</div>
                        <div class="card-info-row">
                            <div>
                                <div class="card-label">Kart Sahibi</div>
                                <div class="card-value" id="cardHolderPreview">Ad Soyad</div>
                            </div>
                            <div style="text-align: right;">
                                <div class="card-label">Son Kullanma</div>
                                <div class="card-value" id="cardExpiryPreview">AA/YY</div>
                            </div>
                        </div>
                    </div>
                    <!-- Back Face -->
                    <div class="card-face card-back">
                        <div class="black-magnetic-stripe"></div>
                        <div class="signature-strip-container">
                            <div class="signature-strip">
                                <span class="card-cvv-val" id="cardCvvPreview">•••</span>
                            </div>
                        </div>
                        <div class="card-back-text">
                            Bu kart Kuveyt Türk Sanal POS altyapısı ile güvence altına alınmıştır. Ödeme işleminiz 3D Secure onay adımı ile tamamlanacaktır.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Notification -->
            @if(session()->has('error'))
                <div class="error-message" style="display: block;">
                    {{ session()->get('error') }}
                </div>
            @endif
            <div class="error-message" id="jsError"></div>

            <!-- Payment Form -->
            <form action="{{ route('kuveytturk.process') }}" method="POST" class="payment-form" id="paymentForm" onsubmit="return handleFormSubmit(event)">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="cardholder_name">Kart Sahibi Adı Soyadı</label>
                    <input type="text" id="cardholder_name" name="cardholder_name" class="form-input" placeholder="KART ÜZERİNDEKİ İSİM" required autocomplete="cc-name" maxlength="50" oninput="updateCardHolder(this.value)">
                </div>

                <div class="form-group">
                    <label class="form-label" for="card_number">Kart Numarası</label>
                    <div class="input-wrapper">
                        <input type="text" id="card_number" name="card_number" class="form-input" placeholder="0000 0000 0000 0000" required autocomplete="cc-number" maxlength="19" oninput="updateCardNumber(this.value)" onfocus="showFrontSide()">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Son Kullanma Tarihi</label>
                        <div class="form-row" style="gap: 10px;">
                            <input type="text" id="expiration_month" name="expiration_month" class="form-input" placeholder="Ay (AA)" required maxlength="2" oninput="updateExpiryMonth(this.value)" onfocus="showFrontSide()">
                            <input type="text" id="expiration_year" name="expiration_year" class="form-input" placeholder="Yıl (YY)" required maxlength="2" oninput="updateExpiryYear(this.value)" onfocus="showFrontSide()">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="cvv">Güvenlik Kodu (CVV)</label>
                        <input type="text" id="cvv" name="cvv" class="form-input" placeholder="000" required autocomplete="cc-csc" maxlength="4" oninput="updateCvv(this.value)" onfocus="showBackSide()" onblur="showFrontSide()">
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                    <span>Şimdi Güvenli Öde</span>
                    <div class="spinner" id="btnSpinner"></div>
                </button>
            </form>
        </div>
    </div>

    <!-- JS Logic for Interactive Card -->
    <script>
        const cardElement = document.getElementById('creditCard');
        const cardNoPreview = document.getElementById('cardNoPreview');
        const cardHolderPreview = document.getElementById('cardHolderPreview');
        const cardExpiryPreview = document.getElementById('cardExpiryPreview');
        const cardCvvPreview = document.getElementById('cardCvvPreview');
        const cardLogo = document.getElementById('cardLogo');

        let expMonth = 'AA';
        let expYear = 'YY';

        // Flip handlers
        function showBackSide() {
            cardElement.classList.add('flipped');
        }

        function showFrontSide() {
            cardElement.classList.remove('flipped');
        }

        // Live preview updates
        function updateCardHolder(val) {
            cardHolderPreview.textContent = val.toUpperCase() || 'AD SOYAD';
        }

        function updateCardNumber(val) {
            // Remove non-digits
            let clean = val.replace(/\D/g, '');
            
            // Limit to 16 digits
            clean = clean.substring(0, 16);

            // Format card input with spaces
            let formattedInput = '';
            for (let i = 0; i < clean.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedInput += ' ';
                }
                formattedInput += clean[i];
            }
            document.getElementById('card_number').value = formattedInput;

            // Format card preview with spaces
            let formattedPreview = '';
            for (let i = 0; i < 16; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedPreview += ' ';
                }
                formattedPreview += clean[i] || '•';
            }
            cardNoPreview.textContent = formattedPreview;

            // Detect Card Type Logo
            detectCardType(clean);
        }

        function updateExpiryMonth(val) {
            let clean = val.replace(/\D/g, '').substring(0, 2);
            document.getElementById('expiration_month').value = clean;
            expMonth = clean.padStart(2, '0').substring(0, 2) || 'AA';
            if(clean === '') expMonth = 'AA';
            cardExpiryPreview.textContent = expMonth + '/' + expYear;
        }

        function updateExpiryYear(val) {
            let clean = val.replace(/\D/g, '').substring(0, 2);
            document.getElementById('expiration_year').value = clean;
            expYear = clean || 'YY';
            cardExpiryPreview.textContent = expMonth + '/' + expYear;
        }

        function updateCvv(val) {
            let clean = val.replace(/\D/g, '').substring(0, 4);
            document.getElementById('cvv').value = clean;
            
            let preview = '';
            for (let i = 0; i < clean.length; i++) {
                preview += '•';
            }
            cardCvvPreview.textContent = preview || '•••';
        }

        function detectCardType(number) {
            if (number.startsWith('4')) {
                // Visa
                cardLogo.innerHTML = '<span style="font-weight: 800; font-size: 24px; font-style: italic; color: #3b82f6;">VISA</span>';
            } else if (number.startsWith('5') || number.startsWith('2')) {
                // Mastercard
                cardLogo.innerHTML = '<span style="font-weight: 800; font-size: 20px; font-style: italic; color: #f97316;">mastercard</span>';
            } else if (number.startsWith('9')) {
                // Troy
                cardLogo.innerHTML = '<span style="font-weight: 800; font-size: 22px; color: #10b981;">TROY</span>';
            } else {
                cardLogo.innerHTML = '<span style="font-weight: 700; font-size: 16px; letter-spacing: 0.5px; opacity: 0.5;">CARD</span>';
            }
        }

        function handleFormSubmit(e) {
            const btnSubmit = document.getElementById('btnSubmit');
            const btnSpinner = document.getElementById('btnSpinner');
            const jsError = document.getElementById('jsError');
            
            jsError.style.display = 'none';

            // Custom JS validaton before submit
            const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
            const month = parseInt(document.getElementById('expiration_month').value);
            const year = parseInt(document.getElementById('expiration_year').value);
            const cvv = document.getElementById('cvv').value;

            if (cardNumber.length < 15) {
                jsError.textContent = 'Lütfen geçerli bir kart numarası giriniz.';
                jsError.style.display = 'block';
                e.preventDefault();
                return false;
            }

            if (month < 1 || month > 12) {
                jsError.textContent = 'Son kullanma ayı geçersiz.';
                jsError.style.display = 'block';
                e.preventDefault();
                return false;
            }

            if (cvv.length < 3) {
                jsError.textContent = 'Güvenlik kodu en az 3 haneli olmalıdır.';
                jsError.style.display = 'block';
                e.preventDefault();
                return false;
            }

            // Disable button and show spinner
            btnSubmit.disabled = true;
            btnSpinner.style.display = 'block';
            return true;
        }
    </script>
</body>
</html>
