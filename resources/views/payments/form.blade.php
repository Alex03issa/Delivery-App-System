@extends('layouts.main')

@section('container')
@include('partials.navbar')

<div class="container mt-5">
    <div class="card shadow" style="margin-top: 150px;">
        <div class="card-header bg-primary text-white">
            <strong>Choose Payment Method</strong>
        </div>
        <div class="card-body">
            <form action="{{ route('payment.store') }}" method="POST">
                @csrf

                <input type="hidden" name="delivery_id" value="{{ $delivery->id }}">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Base Price</label>
                    <input type="text" class="form-control" value="{{ number_format($delivery->price, 2) }}" readonly>
                </div>

                @if ($delivery->extra_charge)
                    <div class="mb-3">
                        <label class="form-label">Extra Charge</label>
                        <input type="text" class="form-control" value="{{ number_format($delivery->extra_charge, 2) }}" readonly>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Total Amount (auto-calculated)</label>
                    <input type="text" class="form-control" value="{{ number_format($baseAmount, 2) }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="form-control" required>
                        <option value="">-- Select Method --</option>
                        <option value="card">Credit/Debit Card</option>
                        <option value="crypto">Cryptocurrency</option>
                        <option value="cod">Cash on Delivery (COD)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Currency</label>
                    <select name="currency_id" id="currency_id" class="form-control" required>
                        <option value="">-- Select Currency --</option>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}"
                                    data-type="{{ in_array($currency->code, ['BTC', 'ETH', 'USDT', 'USDC']) ? 'crypto' : 'fiat' }}"
                                    data-code="{{ $currency->code }}"
                                    {{ $delivery->currency_id == $currency->id ? 'selected' : '' }}>
                                {{ $currency->name }} ({{ $currency->code }})
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="mb-3 row">
                    <div class="col-md-4">
                        <label class="form-label">Platform Fee</label>
                        <input type="text" class="form-control" value="{{ number_format($platformFee, 2) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Driver Share</label>
                        <input type="text" class="form-control" value="{{ number_format($driverShare, 2) }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Center Share</label>
                        <input type="text" class="form-control" value="{{ number_format($centerShare, 2) }}" readonly>
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col-md-6">
                        <label class="form-label">Amount to pay in the selected currency</label>
                        <input type="text" id="converted_amount" class="form-control" value="" readonly>

                    </div>
                </div>

                

                <div class="text-end">
                    <button class="btn btn-success">Proceed to Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
// Display only: the server recomputes every amount when the form is submitted.
const baseAmount = {{ $baseAmount }};
const currencies = @json($currencies);
const currencySelect = document.getElementById('currency_id');

function updateConvertedAmount() {
    const selectedId = parseInt(currencySelect.value);
    const selectedCurrency = currencies.find(c => c.id === selectedId);
    const output = document.getElementById('converted_amount');

    if (!selectedCurrency) { output.value = ''; return; }

    // rate_to_usd = how many USD one unit of the currency is worth
    const rate = parseFloat(selectedCurrency.rate_to_usd);
    const converted = baseAmount / rate;

    output.value = converted.toLocaleString('en-US', {
        minimumFractionDigits: converted < 1 ? 8 : 2,
        maximumFractionDigits: converted < 1 ? 10 : 2,
    }) + ' ' + selectedCurrency.code;
}

function filterCurrenciesByMethod(method) {
    currencySelect.querySelectorAll('option').forEach(opt => {
        const type = opt.dataset.type;
        if (!type) return;

        const hideLBP = method === 'card' && opt.dataset.code === 'LBP';
        const wrongForCrypto = method === 'crypto' && type !== 'crypto';
        const wrongForFiat = method !== 'crypto' && type === 'crypto';

        opt.hidden = hideLBP || wrongForCrypto || wrongForFiat;
    });

    // Reset the selection if the chosen currency is now hidden
    if (currencySelect.options[currencySelect.selectedIndex]?.hidden) {
        currencySelect.value = '';
    }

    updateConvertedAmount();
}

window.onload = () => {
    filterCurrenciesByMethod(document.getElementById('payment_method').value);
};

currencySelect.addEventListener('change', updateConvertedAmount);
document.getElementById('payment_method').addEventListener('change', function () {
    filterCurrenciesByMethod(this.value);
});
</script>

@endsection
