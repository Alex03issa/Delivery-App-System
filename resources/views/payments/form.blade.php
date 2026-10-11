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
                <input type="hidden" name="baseAmount" value="{{ $baseAmount }}">
                <input type="hidden" name="platform_fee" value="{{ $platformFee }}">
                <input type="hidden" name="driver_share" value="{{ $driverShare }}">
                @if ($hasCenterShare)
                    <div class="col-md-4">
                        <label class="form-label">Center Share</label>
                        <input type="text" class="form-control" value="{{ number_format($centerShare, 2) }}" readonly>
                        <input type="hidden" name="center_share" value="{{ $centerShare }}">
                    </div>
                @else
                    <input type="hidden" name="center_share" value="0">
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
                        <input type="hidden" name="amount" id="amount">
                        <input type="hidden" name="conversion_rate" id="conversion_rate">

                        <label class="form-label">Converted Amount</label>
                        <input type="text" id="converted_amount" class="form-control" value="{{ number_format($convertedAmount, 10) }}" readonly>

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
const baseAmount = {{ $baseAmount }};
const currencies = @json($currencies);

function updateConvertedAmount() {
    const select = document.getElementById('currency_id');
    const selectedId = parseInt(select.value);
    const selectedCurrency = currencies.find(c => c.id === selectedId);
    

    if (!selectedCurrency) return;

    const rate = parseFloat(selectedCurrency.rate_to_usd);
    const convertedRaw = baseAmount * rate;

    // Set raw amount in hidden input
    document.getElementById('amount').value = convertedRaw.toFixed(10);
    document.getElementById('conversion_rate').value = rate;
    console.log("Rate:", rate, "Converted:", convertedRaw);
    // Set pretty display
    const convertedFormatted = convertedRaw.toLocaleString('en-US', {
        minimumFractionDigits: convertedRaw < 1 ? 10 : 2,
        maximumFractionDigits: 10,
    });

    document.getElementById('converted_amount').value = convertedFormatted;
}


function filterCurrenciesByMethod(method) {
    const options = document.querySelectorAll('#currency_id option');
    options.forEach(opt => {
        const type = opt.dataset.type;
        const code = opt.dataset.code;

        // Hide LBP only if method is card
        const hideLBP = method === 'card' && code === 'LBP';

        if (!type) return;

        const isCrypto = method === 'crypto' && type !== 'crypto';
        const isFiatOrCOD = method !== 'crypto' && type === 'crypto';

        opt.hidden = hideLBP || isCrypto || isFiatOrCOD;
    });

    // If current selected is hidden, reset selection
    if (select.options[select.selectedIndex]?.hidden && fallbackOption) {
        fallbackOption.selected = true;
    }

    updateConvertedAmount();
}

window.onload = () => {
    updateConvertedAmount();
    filterCurrenciesByMethod(document.getElementById('payment_method').value);
};

document.getElementById('currency_id').addEventListener('change', updateConvertedAmount);
document.getElementById('payment_method').addEventListener('change', function () {
    filterCurrenciesByMethod(this.value);
});
</script>

@endsection
